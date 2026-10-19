<?php

namespace App\Imports;

use App\Models\PelangganPbpd;
use App\Models\Ulp;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PelangganPbpdImport implements ToCollection, WithChunkReading
{
    use RemembersChunkOffset;

    public int $baru = 0;
    public int $diperbarui = 0;
    public array $gagal = [];

    private ?array $kolom = null;
    private int $headerRows = 1;
    private ?Collection $ulps = null;

    public function __construct(private int $importId, private ?int $ulpDefault = null) {}

    public function collection(Collection $rows): void
    {
        $rows = $rows->map(fn($r) => $r->toArray())->values()->all();

        $chunkOffset = $this->getChunkOffset() ?? 0;

        // Header hanya dicari pada chunk pertama. Chunk berikutnya langsung
        // memakai susunan kolom yang sama.
        if ($this->kolom === null) {
            $h = null;
            foreach (array_slice($rows, 0, 20, true) as $i => $r) {
                foreach ($r as $c) {
                    if (str_contains($this->k($c), 'agenda')) {
                        $h = $i;
                        break 2;
                    }
                }
            }
            if ($h === null) {
                $this->gagal[] = 'Header "NO AGENDA" tidak ditemukan di 20 baris pertama file';
                return;
            }

            // Susun nama kolom (mendukung header 2 baris: LAMA/BARU + TARIF/DAYA).
            $head = $rows[$h];
            $sub  = $rows[$h + 1] ?? [];
            $dua  = in_array('tarif', array_map(fn($c) => $this->k($c), $sub), true);

            $this->kolom = [];
            $grup = '';
            foreach ($head as $c => $nama) {
                $n = $this->k($nama);
                if ($n !== '') {
                    $grup = in_array($n, ['lama', 'baru'], true) ? $n : '';
                }
                $s = $this->k($sub[$c] ?? '');
                $this->kolom[$c] = ($dua && $grup && $s) ? $grup . $s : $n;
            }
            $this->headerRows = $h + ($dua ? 2 : 1);
        }

        if ($this->ulps === null) {
            $this->ulps = Ulp::all();
        }

        $mulai = $chunkOffset === 0 ? $this->headerRows : 0;
        $records = [];

        foreach (array_slice($rows, $mulai, null, true) as $i => $r) {
            $d = [];
            foreach ($this->kolom as $c => $key) {
                if ($key !== '') $d[$key] = $r[$c] ?? null;
            }
            if (! array_filter($d, fn($v) => $v !== null && $v !== '')) continue; // baris kosong

            $baris = $chunkOffset + $i + 1; // nomor baris di Excel

            $noAgenda = $this->angka($this->ambil($d, ['noagenda', 'agenda', 'nomoragenda']));
            if (! $noAgenda) {
                $this->gagal[] = "Baris $baris: no agenda kosong";
                continue;
            }

            // NAMAUP biasanya berisi nama ULP/UP3 yang paling jelas.
            // UNITTUJUAN tetap dipakai sebagai cadangan kode ULP.
            $calonUlps = [
                $this->ambil($d, ['namaup']),
                $this->ambil($d, ['unittujuan', 'ulp']),
            ];
            $ulp = null;
            foreach ($calonUlps as $calon) {
                $teksCalon = strtoupper(trim((string) $calon));
                if ($teksCalon === '') continue;
                $kodeCalon = preg_replace('/\.0$/', '', $teksCalon);
                $namaCalon = $this->normalUlp($teksCalon);
                $ulp = $this->ulps->first(function ($u) use ($kodeCalon, $namaCalon) {
                    $namaUlp = $this->normalUlp($u->nama);
                    return $u->kode === $kodeCalon || $namaUlp === $namaCalon;
                });
                if ($ulp) break;
            }
            $teks = strtoupper(trim((string) ($calonUlps[0] ?? $calonUlps[1] ?? '')));
            $ulpId = $ulp?->id ?? $this->ulpDefault;

            if (! $ulpId) {
                $this->gagal[] = "Baris $baris: ULP '$teks' tidak dikenali";
                continue;
            }

            $bayar = $this->ambil($d, ['tglbayar']);
            $jenis = $this->jenis($this->ambil($d, [
                'jenistransaksi', 'jenis', 'transaksi', 'jenislayanan',
            ]));
            $statusPermohonan = strtoupper(preg_replace(
                '/\s+/',
                ' ',
                trim((string) $this->ambil($d, ['statuspermohonan']))
            ));
            // TGLBAYAR hanya tanggal pembayaran. Status diambil dari
            // STATUS_PERMOHONAN (mis. CETAK PK, PENGESAHAN PDL, PDL AWAL).
            // Jangan membuat status sintetis BAYAR/MOHON karena nilai itu
            // tidak terdapat sebagai kolom/status pada file sumber.
            $statusKhusus = ['CETAK PK', 'PENGESAHAN PDL', 'PDL AWAL'];
            $status = $statusPermohonan !== ''
                ? $statusPermohonan
                : (in_array($jenis, $statusKhusus, true) ? $jenis : null);
            $transaksi = match ($jenis) {
                'PASANG BARU', 'PERUBAHAN DAYA', 'PS', 'BN' => $jenis,
                'PENERANGAN SEMENTARA' => 'PS',
                default => null,
            };

            $data = [
                'import_id'       => $this->importId,
                'ulp_id'          => $ulpId,
                'id_pelanggan'    => $this->angka($this->ambil($d, ['idpel'])),
                'bp'              => $this->angka($this->ambil($d, ['totalbiaya'])),
                'total_biaya'     => $this->angka($this->ambil($d, ['totalbiaya'])),
                'tgl_mohon'       => $this->tanggal($this->ambil($d, ['tglmohon'])),
                'tgl_bayar'       => $this->tanggal($bayar),
                'asal_ulp'        => preg_replace('/^ULP\s+/i', '', trim((string) $this->ambil($d, ['namaup']))) ?: null,
                'jenis_transaksi' => $transaksi,
                'status'          => $status,
                'nama_pelanggan'  => $this->ambil($d, ['nama', 'namapelanggan']),
                'alamat'          => preg_replace('/\s+/', ' ', trim((string) $this->ambil($d, ['alamat']))) ?: null,
                'tarif_lama'      => $this->ambil($d, ['tariflama', 'lamatarif']),
                'daya_lama'       => $this->daya($this->ambil($d, ['dayalama', 'lamadaya'])),
                'tarif_baru'      => $this->ambil($d, ['tarif', 'tarifbaru', 'barutarif']),
                'daya_baru'       => $this->daya($this->ambil($d, ['daya', 'dayabaru', 'barudaya'])),
                'keterangan'      => $this->ambil($d, ['durasiharikerja', 'keterangan', 'ket']),
            ];

            $records[] = [
                'no_agenda' => $noAgenda,
                'data' => $data,
            ];
        }

        $this->simpanChunk($records);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function simpanChunk(array $records): void
    {
        if ($records === []) return;

        $agenda = array_values(array_unique(array_column($records, 'no_agenda')));
        $ulpIds = array_values(array_unique(array_map(
            fn ($record) => $record['data']['ulp_id'],
            $records
        )));

        // Satu query untuk mencari data lama dalam satu chunk, bukan satu
        // query pencarian untuk setiap baris Excel.
        $existing = PelangganPbpd::query()
            ->whereIn('no_agenda', $agenda)
            ->whereIn('ulp_id', $ulpIds)
            ->get();

        $existingByKey = [];
        foreach ($existing as $row) {
            $existingByKey[$this->kunciData($row->no_agenda, $row->ulp_id, $row->id_pelanggan, $row->nama_pelanggan, $row->alamat)] ??= $row;
        }

        $insertRows = [];
        $updateRows = [];
        $pendingInsertByKey = [];
        $createdAt = now()->toDateTimeString();
        foreach ($records as $record) {
            $data = $record['data'];
            $key = $this->kunciData(
                $record['no_agenda'],
                $data['ulp_id'],
                $data['id_pelanggan'],
                $data['nama_pelanggan'],
                $data['alamat']
            );
            $ada = $existingByKey[$key] ?? null;

            if ($ada) {
                $updateRows[] = $data + ['id' => (int) $ada->id];
                $this->diperbarui++;
                continue;
            }

            // Jika kunci yang sama muncul dua kali dalam chunk ini, perlakukan
            // baris kedua sebagai pembaruan seperti perilaku import sebelumnya.
            if (array_key_exists($key, $pendingInsertByKey)) {
                $insertRows[$pendingInsertByKey[$key]] = array_merge($insertRows[$pendingInsertByKey[$key]], $data);
                $this->diperbarui++;
                continue;
            }

            $pendingInsertByKey[$key] = count($insertRows);
            $insertRows[] = $data + [
                'no_agenda' => $record['no_agenda'],
                'tahap' => 'ULP',
                'created_at' => $createdAt,
            ];
            $this->baru++;
        }

        $this->updateExistingChunk($updateRows);

        if ($insertRows !== []) {
            DB::table('pelanggan_pbpd')->insert($insertRows);
        }
    }

    private function updateExistingChunk(array $rows): void
    {
        if ($rows === []) return;

        // PostgreSQL dapat memperbarui seluruh baris dalam satu chunk melalui
        // jsonb_to_recordset. Ini menghindari satu query UPDATE untuk setiap
        // baris lama.
        if (DB::getDriverName() !== 'pgsql') {
            foreach ($rows as $row) {
                $id = $row['id'];
                unset($row['id']);
                DB::table('pelanggan_pbpd')->where('id', $id)->update($row);
            }
            return;
        }

        $json = json_encode($rows, JSON_THROW_ON_ERROR);
        DB::statement(
            <<<'SQL'
UPDATE pelanggan_pbpd AS p
SET import_id = v.import_id,
    ulp_id = v.ulp_id,
    id_pelanggan = v.id_pelanggan,
    bp = v.bp,
    total_biaya = v.total_biaya,
    tgl_mohon = v.tgl_mohon,
    tgl_bayar = v.tgl_bayar,
    asal_ulp = v.asal_ulp,
    jenis_transaksi = v.jenis_transaksi,
    status = v.status,
    nama_pelanggan = v.nama_pelanggan,
    alamat = v.alamat,
    tarif_lama = v.tarif_lama,
    daya_lama = v.daya_lama,
    tarif_baru = v.tarif_baru,
    daya_baru = v.daya_baru,
    keterangan = v.keterangan
FROM jsonb_to_recordset(CAST(? AS jsonb)) AS v(
    id bigint,
    import_id bigint,
    ulp_id bigint,
    id_pelanggan numeric,
    bp numeric,
    total_biaya numeric,
    tgl_mohon date,
    tgl_bayar date,
    asal_ulp text,
    jenis_transaksi text,
    status text,
    nama_pelanggan text,
    alamat text,
    tarif_lama text,
    daya_lama text,
    tarif_baru text,
    daya_baru text,
    keterangan text
)
WHERE p.id = v.id
SQL,
            [$json]
        );
    }

    private function kunciData($noAgenda, $ulpId, $idPelanggan, $nama, $alamat): string
    {
        $identitas = $idPelanggan !== null && $idPelanggan !== ''
            ? 'id:' . (string) $idPelanggan
            : 'nama:' . (string) $nama . '|alamat:' . (string) $alamat;

        return (string) $noAgenda . '|ulp:' . (string) $ulpId . '|' . $identitas;
    }

    /** "No. Agenda" -> "noagenda" */
    private function k($v): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower((string) $v));
    }

    private function ambil(array $d, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($d[$key]) && $d[$key] !== '') return $d[$key];
        }
        return null;
    }

    private function angka($v): ?string
    {
        if ($v === null || $v === '') return null;
        // Nomor agenda dan IDPEL dapat memiliki 18-20 digit. Jangan
        // mengubahnya ke float karena akan membulatkan digit terakhir dan
        // membuat beberapa baris Excel menimpa agenda yang berbeda.
        $sumber = trim((string) $v);
        if (preg_match('/^([+-]?)(\d+)(?:\.(\d+))?[eE]([+-]?\d+)$/', $sumber, $m)) {
            $digits = ltrim($m[2] . ($m[3] ?? ''), '0') ?: '0';
            $scale = (int) $m[4] - strlen($m[3] ?? '');
            if ($scale >= 0) {
                $s = $digits . str_repeat('0', $scale);
            } else {
                $cut = strlen($digits) + $scale;
                $s = $cut > 0
                    ? substr($digits, 0, $cut) . '.' . substr($digits, $cut)
                    : '0.' . str_repeat('0', -$cut) . $digits;
            }
            $s = preg_replace('/\D/', '', $s);
        } elseif (preg_match('/^\d+(?:\.0+)?$/', $sumber, $m)) {
            $s = explode('.', $m[0], 2)[0];
        } else {
            $s = preg_replace('/\D/', '', $sumber);
        }
        return $s === '' ? null : $s;
    }

    private function jenis($v): ?string
    {
        $v = strtoupper(preg_replace('/\s+/', ' ', trim((string) $v)));
        return match (true) {
            str_contains($v, 'BALIK') => 'BN',
            str_contains($v, 'PENGESAHAN') => 'PENGESAHAN PDL',
            str_contains($v, 'PDL AWAL') => 'PDL AWAL',
            str_contains($v, 'CETAK PK') => 'CETAK PK',
            str_contains($v, 'DAYA'), $v === 'PD' => 'PERUBAHAN DAYA',
            str_contains($v, 'SEMENTARA') => 'PS',
            str_contains($v, 'BARU'), $v === 'PB' => 'PASANG BARU',
            default => $v ?: null,
        };
    }

    private function daya($v): ?string
    {
        $v = preg_replace('/\D/', '', (string) $v);
        return $v === '' ? null : $v;
    }

    private function normalUlp($value): string
    {
        $value = strtoupper(trim((string) $value));
        $value = preg_replace('/^(ULP|UP3)\s+/i', '', $value);
        $value = preg_replace('/\s+/', '', $value);

        // Penulisan pada sumber data kadang "SUMBEREJO", sedangkan master memakai "SUMBERREJO".
        return str_replace(['SUMBEREJO', 'BRONDONG'], ['SUMBERREJO', 'BRONDON'], $value);
    }

    private function tanggal($v): ?string
    {
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
        if ($v === null || trim((string) $v) === '') return null;

        $v = trim((string) $v);
        try {
            if (is_numeric($v)) {
                return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
            }

            return Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
        } catch (\Throwable) {
            try {
                return Carbon::parse($v)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
