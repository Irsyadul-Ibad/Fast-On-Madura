<?php

namespace App\Imports;

use App\Models\PelangganPbpd;
use App\Models\Ulp;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PelangganPbpdImport implements ToCollection
{
    public int $baru = 0;
    public int $diperbarui = 0;
    public array $gagal = [];

    public function __construct(private int $importId, private ?int $ulpDefault = null) {}

    public function collection(Collection $rows): void
    {
        $rows = $rows->map(fn($r) => $r->toArray())->values()->all();

        // 1. Cari baris header: baris pertama yang punya sel berisi "agenda"
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

        // 2. Susun nama kolom (mendukung header 2 baris: LAMA/BARU + TARIF/DAYA)
        $head = $rows[$h];
        $sub  = $rows[$h + 1] ?? [];
        $dua  = in_array('tarif', array_map(fn($c) => $this->k($c), $sub), true);

        $kolom = [];
        $grup  = '';
        foreach ($head as $c => $nama) {
            $n = $this->k($nama);
            if ($n !== '') {
                $grup = in_array($n, ['lama', 'baru'], true) ? $n : '';
            }
            $s = $this->k($sub[$c] ?? '');
            $kolom[$c] = ($dua && $grup && $s) ? $grup . $s : $n;
        }
        $mulai = $h + ($dua ? 2 : 1);

        $ulps = Ulp::all();

        // 3. Baca data
        foreach (array_slice($rows, $mulai, null, true) as $i => $r) {
            $d = [];
            foreach ($kolom as $c => $key) {
                if ($key !== '') $d[$key] = $r[$c] ?? null;
            }
            if (! array_filter($d, fn($v) => $v !== null && $v !== '')) continue; // baris kosong

            $baris = $i + 1; // nomor baris di Excel

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
                $ulp = $ulps->first(function ($u) use ($kodeCalon, $namaCalon) {
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

            // Nomor agenda dapat sama pada ULP berbeda. Kunci update harus memakai
            // kombinasi nomor agenda dan ULP agar data antar-ULP tidak tertimpa.
            // Nomor agenda dari beberapa file Excel tersimpan sebagai angka
            // ilmiah dan dapat sama untuk beberapa pelanggan berbeda. IDPEL
            // menjadi bagian kunci agar baris pelanggan tidak saling timpa.
            $idPelanggan = $data['id_pelanggan'];
            $ada = PelangganPbpd::where('no_agenda', $noAgenda)
                ->where('ulp_id', $ulpId)
                ->when($idPelanggan, fn ($q) => $q->where('id_pelanggan', $idPelanggan), function ($q) use ($data) {
                    $q->where('nama_pelanggan', $data['nama_pelanggan'])
                        ->where('alamat', $data['alamat']);
                })
                ->first();
            if ($ada) {
                $ada->update($data); // tahap tidak diubah
                $this->diperbarui++;
            } else {
                PelangganPbpd::create($data + ['no_agenda' => $noAgenda, 'tahap' => 'ULP']);
                $this->baru++;
            }
        }
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
