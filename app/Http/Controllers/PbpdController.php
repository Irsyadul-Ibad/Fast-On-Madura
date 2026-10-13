<?php

namespace App\Http\Controllers;

use App\Models\PelangganPbpd;
use App\Models\PermintaanMaterial;
use App\Models\PengirimanVendor;
use App\Models\PengirimanKonstruksi;
use App\Models\Ulp;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PbpdController extends Controller
{
    public function index(Request $request)
    {
        $role = $this->currentUser()->role;
        $isUlpRole = $role?->type === 'ULP' && (bool) $role?->ulp_id;
        $ulpUser = $isUlpRole ? $this->currentUser()->ulpId() : null;
        $canKirim = $isUlpRole;
        $canEditRab = $canKirim || $role?->role_code === '5180REN';

        $data = PelangganPbpd::with('ulp')
            ->statusTampil()
            ->when($ulpUser, fn ($q) => $q->where('ulp_id', $ulpUser))
            ->when(! $ulpUser && $request->filled('ulp'), fn ($q) => $q->where('ulp_id', $request->ulp))
            ->when($isUlpRole, fn ($q) => $q->where('tahap', 'ULP'))
            ->where('status', '!=', 'MOHON')
            ->when($request->filled('import'), fn ($q) => $q->where('import_id', $request->import))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_transaksi', $request->jenis))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('tahap'), fn ($q) => $q->where('tahap', $request->tahap))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $kata = '%' . $request->cari . '%';
                $q->where(function ($w) use ($kata) {
                    $w->where('nama_pelanggan', 'ilike', $kata)
                      ->orWhere('alamat', 'ilike', $kata)
                      ->orWhereRaw('CAST(no_agenda AS TEXT) LIKE ?', [$kata]);
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $summaryBase = PelangganPbpd::query()
            ->statusTampil()
            ->when($ulpUser, fn ($q) => $q->where('ulp_id', $ulpUser))
            ->when($isUlpRole, fn ($q) => $q->where('tahap', 'ULP'))
            ->when($request->filled('import'), fn ($q) => $q->where('import_id', $request->import))
            ->where('status', '!=', 'MOHON')
            ;

        $summaryByJenisRaw = (clone $summaryBase)
            ->selectRaw('UPPER(TRIM(jenis_transaksi)) AS jenis, COUNT(*) AS jumlah')
            ->whereNotNull('jenis_transaksi')
            ->groupByRaw('UPPER(TRIM(jenis_transaksi))')
            ->pluck('jumlah', 'jenis');
        $summaryByStatus = PelangganPbpd::query()
            ->when($ulpUser, fn ($q) => $q->where('ulp_id', $ulpUser))
            ->when($isUlpRole, fn ($q) => $q->where('tahap', 'ULP'))
            ->when($request->filled('import'), fn ($q) => $q->where('import_id', $request->import))
            ->statusTampil()
            ->selectRaw('UPPER(TRIM(status)) AS status, COUNT(*) AS jumlah')
            ->groupByRaw('UPPER(TRIM(status))')->pluck('jumlah', 'status');
        $summaryByJenis = collect([
            'PASANG BARU' => (int) (($summaryByJenisRaw['PASANG BARU'] ?? 0) + ($summaryByJenisRaw['PB'] ?? 0)),
            'PERUBAHAN DAYA' => (int) (($summaryByJenisRaw['PERUBAHAN DAYA'] ?? 0) + ($summaryByJenisRaw['PD'] ?? 0)),
        ]);
        $isUlpSummary = $isUlpRole;
        $showSummaryCards = in_array($role?->type, ['ULP', 'UP3'], true);
        $summaryTotal = (clone $summaryBase)->count();
        $summarySentByUlp = (clone $summaryBase)
            ->whereNotNull('tahap')->where('tahap', '!=', 'ULP')
            ->selectRaw('ulp_id, COUNT(*) AS jumlah')
            ->whereNotNull('ulp_id')->groupBy('ulp_id')->orderByDesc('jumlah')->get();
        $summarySentCounts = $summarySentByUlp->pluck('jumlah', 'ulp_id');
        $summaryUlpNames = Ulp::orderBy('nama')->get()->keyBy('id');
        $summarySentByUlp = $summaryUlpNames->map(function ($ulp) use ($summarySentCounts) {
            return (object) ['ulp_id' => $ulp->id, 'jumlah' => (int) ($summarySentCounts[$ulp->id] ?? 0)];
        })->values();

        return view('pbpd.index', [
            'data'    => $data,
            'canKirim' => $canKirim,
            'canEditRab' => $canEditRab,
            'canUploadSyarat' => $canKirim,
            'showSyarat' => $role?->type !== 'UP3',
            'ulps'    => $ulpUser ? collect() : Ulp::orderBy('kode')->get(),
            'statuss' => PelangganPbpd::whereIn('status', PelangganPbpd::STATUS_TAMPIL)
                ->distinct()->orderBy('status')->pluck('status'),
            'tahaps'  => [
                'ULP' => 'ULP',
                'PERENCANAAN' => 'PERENCANAAN',
                'VENDOR_TIANG' => 'KONSTRUKSI (TIANG)',
                'VENDOR_KONSTRUKSI' => 'KONSTRUKSI',
                'PENGOPERASIAN' => 'TRANSAKSI',
                'SELESAI' => 'JARINGAN',
            ],
            'showSummaryCards' => $showSummaryCards,
            'isUlpSummary' => $isUlpSummary,
            'summaryTotal' => $summaryTotal,
            'summarySentByUlp' => $summarySentByUlp,
            'summaryUlpNames' => $summaryUlpNames,
            'summaryByJenis' => $summaryByJenis,
            'summaryByStatus' => collect([
                'CETAK PK' => (int) ($summaryByStatus['CETAK PK'] ?? 0),
                'BAYAR' => (int) ($summaryByStatus['BAYAR'] ?? 0),
                'PENGESAHAN PDL' => (int) ($summaryByStatus['PENGESAHAN PDL'] ?? 0),
                'PDL AWAL' => (int) ($summaryByStatus['PDL AWAL'] ?? 0),
            ]),
        ]);
    }

    public function updateRab(Request $request, PelangganPbpd $pelanggan): \Illuminate\Http\RedirectResponse
    {
        $role = $this->currentUser()->role;
        $canEditRab = (bool) $role?->ulp_id || $role?->role_code === '5180REN';
        abort_unless($canEditRab, 403);

        $ulpUser = $this->currentUser()->ulpId();
        abort_if($ulpUser && (int) $pelanggan->ulp_id !== (int) $ulpUser, 403);

        $data = $request->validate([
            'rab' => ['nullable', 'numeric', 'min:0'],
        ]);

        $pelanggan->update(['rab' => $data['rab'] ?? null]);
        NotifikasiService::untukData($pelanggan, 'RAB diperbarui', 'RAB data pelanggan ' . $pelanggan->no_agenda . ' telah diperbarui.', route('laporan'));

        return back()->with('success', 'RAB berhasil disimpan.');
    }

    public function kirim(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort_unless((bool) $this->currentUser()->role?->ulp_id, 403);

        $data = $request->validate([
            'tujuan' => ['required', 'in:JTM,JTR,TANPA_PERLUASAN'],
            'pelanggan' => ['required', 'array', 'min:1'],
            'pelanggan.*' => ['integer', 'distinct', 'exists:pelanggan_pbpd,id'],
            'jenis_tiang' => ['nullable', 'string', 'max:30'],
            'jml_tiang' => ['nullable', 'integer', 'min:0'],
            'jml_konduktor' => ['nullable', 'integer', 'min:0'],
            'jenis_konduktor' => ['nullable', 'string', 'max:30'],
            'jenis_trafo' => ['nullable', 'string', 'max:30'],
            'jml_trafo' => ['nullable', 'integer', 'min:0'],
            'jml_kwh_meter' => ['nullable', 'integer', 'min:0'],
            'jenis_kwh_meter' => ['nullable', 'string', 'max:30'],
            'jenis_panel_meter' => ['nullable', 'string', 'max:30'],
            'jml_panel_meter' => ['nullable', 'integer', 'min:0'],
        ]);

        $pelanggan = PelangganPbpd::whereIn('id', $data['pelanggan'])
            ->where('tahap', 'ULP')
            ->get();

        if ($pelanggan->count() !== count($data['pelanggan'])) {
            return back()->with('error', 'Sebagian data tidak lagi berada pada tahap ULP.');
        }

        if ($pelanggan->contains(fn ($row) => $row->rab === null)) {
            return back()->with('error', 'RAB wajib diisi sebelum data dikirim.');
        }

        if ($pelanggan->contains(fn ($row) => empty($row->berkas_pendukung_paths) || empty($row->berkas_ijin_paths))) {
            return back()->with('error', 'Berkas pendukung dan berkas ijin wajib dilengkapi sebelum data dikirim.');
        }

        DB::transaction(function () use ($pelanggan, $data) {
            $pelanggan->each(function ($row) use ($data) {
                $row->update([
                    'tahap' => 'PERENCANAAN',
                    'tujuan_perluasan' => $data['tujuan'],
                ]);

                if ($data['tujuan'] === 'TANPA_PERLUASAN') {
                    $row->permintaan()->delete();
                    return;
                }

                PermintaanMaterial::updateOrCreate(
                    ['pelanggan_id' => $row->id],
                    [
                        'jenis_perluasan' => $data['tujuan'],
                        'jenis_tiang' => $data['jenis_tiang'] ?? null,
                        'jml_tiang' => $data['jml_tiang'] ?? null,
                        'jml_konduktor' => $data['jml_konduktor'] ?? null,
                        'jenis_konduktor' => $data['jenis_konduktor'] ?? null,
                        'jenis_trafo' => $data['jenis_trafo'] ?? null,
                        'jml_trafo' => $data['jml_trafo'] ?? null,
                        'jml_kwh_meter' => $data['jml_kwh_meter'] ?? null,
                        'jenis_kwh_meter' => $data['jenis_kwh_meter'] ?? null,
                        'jenis_panel_meter' => $data['jenis_panel_meter'] ?? null,
                        'jml_panel_meter' => $data['jml_panel_meter'] ?? null,
                        'dikirim_oleh' => Auth::id(),
                        'dikirim_at' => now(),
                    ]
                );
            });
        });

        $pelanggan->each(fn ($row) => NotifikasiService::untukData($row, 'Data dikirim ULP', 'Data ' . $row->no_agenda . ' dikirim ke ' . $data['tujuan'] . '.', route('laporan')));

        return back()->with('success', $pelanggan->count() . ' data berhasil dikirim ke ' . $data['tujuan'] . '.');
    }

    public function updateDetail(Request $request, PelangganPbpd $pelanggan): \Illuminate\Http\RedirectResponse
    {
        $role = $this->currentUser()->role;
        $isUlp = (bool) $role?->ulp_id;
        $isPerencanaan = $role?->role_code === '5180REN';
        abort_unless($isUlp || $isPerencanaan, 403);

        $ulpUser = $this->currentUser()->ulpId();
        abort_if($ulpUser && (int) $pelanggan->ulp_id !== (int) $ulpUser, 403);

        abort_unless(in_array($pelanggan->tujuan_perluasan, ['JTM', 'JTR'], true), 422);

        $rules = ['rab' => ['nullable', 'numeric', 'min:0']];
        if ($isUlp) {
            $rules += [
                'jenis_tiang' => ['nullable', 'string', 'max:30'],
                'jml_tiang' => ['nullable', 'integer', 'min:0'],
                'jenis_konduktor' => ['nullable', 'string', 'max:30'],
                'jml_konduktor' => ['nullable', 'integer', 'min:0'],
                'jenis_trafo' => ['nullable', 'string', 'max:30'],
                'jml_trafo' => ['nullable', 'integer', 'min:0'],
                'jenis_kwh_meter' => ['nullable', 'string', 'max:30'],
                'jml_kwh_meter' => ['nullable', 'integer', 'min:0'],
                'jenis_panel_meter' => ['nullable', 'string', 'max:30'],
                'jml_panel_meter' => ['nullable', 'integer', 'min:0'],
            ];
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($pelanggan, $data, $isUlp) {
            $pelanggan->update(['rab' => $data['rab'] ?? null]);
            if ($isUlp) {
                PermintaanMaterial::updateOrCreate(
                    ['pelanggan_id' => $pelanggan->id],
                    [
                        'jenis_perluasan' => $pelanggan->tujuan_perluasan,
                        'jenis_tiang' => $data['jenis_tiang'] ?? null,
                        'jml_tiang' => $data['jml_tiang'] ?? null,
                        'jenis_konduktor' => $data['jenis_konduktor'] ?? null,
                        'jml_konduktor' => $data['jml_konduktor'] ?? null,
                        'jenis_trafo' => $data['jenis_trafo'] ?? null,
                        'jml_trafo' => $data['jml_trafo'] ?? null,
                        'jenis_kwh_meter' => $data['jenis_kwh_meter'] ?? null,
                        'jml_kwh_meter' => $data['jml_kwh_meter'] ?? null,
                        'jenis_panel_meter' => $data['jenis_panel_meter'] ?? null,
                        'jml_panel_meter' => $data['jml_panel_meter'] ?? null,
                        'dikirim_oleh' => Auth::id(),
                        'dikirim_at' => now(),
                    ]
                );
            }
        });

        NotifikasiService::untukData($pelanggan, 'Detail/RAB diperbarui', 'Detail material atau RAB ' . $pelanggan->no_agenda . ' telah diperbarui.', route('laporan'));

        return back()->with('success', 'RAB dan kebutuhan perluasan berhasil diperbarui.');
    }

    public function kirimVendor(Request $request, PelangganPbpd $pelanggan): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5180REN', 403);

        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'status_kelayakan' => ['required', 'in:LAYAK,TIDAK LAYAK'],
            'wo_tiang' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $vendor = \App\Models\Vendor::whereKey($data['vendor_id'])->where('jenis', 'TIANG')->firstOrFail();
        $pengiriman = PengirimanVendor::firstOrNew(['pelanggan_id' => $pelanggan->id]);

        if ($request->hasFile('wo_tiang')) {
            if ($pengiriman->wo_tiang_path) {
                Storage::disk('public')->delete($pengiriman->wo_tiang_path);
            }
            $pengiriman->wo_tiang_path = $request->file('wo_tiang')->store('wo-tiang', 'public');
        }

        $pengiriman->fill([
            'vendor_id' => $vendor->id,
            'status_kelayakan' => $data['status_kelayakan'],
            'dikirim_oleh' => Auth::id(),
            'dikirim_at' => now(),
        ])->save();

        $pelanggan->update(['tahap' => 'VENDOR_TIANG']);
        NotifikasiService::untukData($pelanggan, 'Data dikirim ke vendor tiang', 'Data ' . $pelanggan->no_agenda . ' dikirim ke vendor tiang.', route('laporan'), [$vendor->user_id]);

        return back()->with('success', 'Data berhasil dikirim ke vendor tiang.');
    }

    public function kirimKonstruksi(Request $request, PelangganPbpd $pelanggan): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5180KON', 403);

        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'berkas.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $vendor = \App\Models\Vendor::whereKey($data['vendor_id'])->where('jenis', 'KONSTRUKSI')->firstOrFail();
        $pengiriman = PengirimanKonstruksi::firstOrNew(['pelanggan_id' => $pelanggan->id]);
        $paths = is_array($pengiriman->berkas_paths) ? $pengiriman->berkas_paths : [];
        foreach ($request->file('berkas', []) as $file) {
            $paths[] = $file->store('pengiriman-konstruksi', 'public');
        }

        $pengiriman->fill([
            'vendor_id' => $vendor->id,
            'berkas_paths' => $paths,
            'dikirim_oleh' => Auth::id(),
            'dikirim_at' => now(),
        ])->save();
        $pelanggan->update(['tahap' => 'VENDOR_KONSTRUKSI']);
        NotifikasiService::untukData($pelanggan, 'Data dikirim ke vendor konstruksi', 'Data ' . $pelanggan->no_agenda . ' dikirim ke vendor konstruksi.', route('laporan'), [$vendor->user_id]);

        return back()->with('success', 'Data berhasil dikirim ke vendor konstruksi.');
    }

    public function uploadHasilTransaksi(Request $request, PelangganPbpd $pelanggan): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5180TEL', 403);
        $request->validate([
            'berkas_hasil_transaksi.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $paths = is_array($pelanggan->hasil_transaksi_paths) ? $pelanggan->hasil_transaksi_paths : [];
        foreach ($request->file('berkas_hasil_transaksi', []) as $file) {
            $paths[] = $file->store('hasil-transaksi', 'public');
        }
        $pelanggan->update(['hasil_transaksi_paths' => $paths, 'hasil_transaksi_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil transaksi diupload', 'Berkas hasil transaksi untuk ' . $pelanggan->no_agenda . ' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil transaksi berhasil disimpan.');
    }

    public function hasilTransaksiFile(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->type === 'UP3', 403);
        $path = (is_array($pelanggan->hasil_transaksi_paths) ? $pelanggan->hasil_transaksi_paths : [])[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusHasilTransaksi(PelangganPbpd $pelanggan, int $index): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5180TEL', 403);

        $paths = is_array($pelanggan->hasil_transaksi_paths) ? $pelanggan->hasil_transaksi_paths : [];
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $pelanggan->update(['hasil_transaksi_paths' => $paths ?: null]);

        return back()->with('success', 'Foto Cek KWH Meter berhasil dihapus.');
    }

    public function uploadHasilJaringan(Request $request, PelangganPbpd $pelanggan): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5180JAR', 403);
        $request->validate([
            'berkas_hasil_jaringan.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $paths = is_array($pelanggan->hasil_jaringan_paths) ? $pelanggan->hasil_jaringan_paths : [];
        foreach ($request->file('berkas_hasil_jaringan', []) as $file) {
            $paths[] = $file->store('hasil-jaringan', 'public');
        }
        $pelanggan->update(['hasil_jaringan_paths' => $paths, 'hasil_jaringan_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil jaringan diupload', 'Berkas hasil jaringan untuk ' . $pelanggan->no_agenda . ' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil jaringan berhasil disimpan.');
    }

    public function hasilJaringanFile(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->type === 'UP3', 403);
        $path = (is_array($pelanggan->hasil_jaringan_paths) ? $pelanggan->hasil_jaringan_paths : [])[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusHasilJaringan(PelangganPbpd $pelanggan, int $index): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5180JAR', 403);

        $paths = is_array($pelanggan->hasil_jaringan_paths) ? $pelanggan->hasil_jaringan_paths : [];
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $pelanggan->update(['hasil_jaringan_paths' => $paths ?: null]);

        return back()->with('success', 'Berkas BA Operasi berhasil dihapus.');
    }

    public function uploadSyarat(Request $request, PelangganPbpd $pelanggan, string $jenis): \Illuminate\Http\RedirectResponse
    {
        abort_unless($this->currentUser()->role?->ulp_id, 403);
        abort_unless(in_array($jenis, ['pendukung', 'ijin'], true), 404);
        abort_if((int) $pelanggan->ulp_id !== (int) $this->currentUser()->role?->ulp_id, 403);

        $request->validate([
            'berkas.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);
        $column = $jenis === 'pendukung' ? 'berkas_pendukung_paths' : 'berkas_ijin_paths';
        $paths = is_array($pelanggan->{$column}) ? $pelanggan->{$column} : [];
        foreach ($request->file('berkas', []) as $file) {
            $paths[] = $file->store('syarat/' . $jenis, 'public');
        }
        $pelanggan->update([$column => $paths]);
        NotifikasiService::untukData($pelanggan, 'Berkas syarat diupload', 'Berkas ' . $jenis . ' untuk ' . $pelanggan->no_agenda . ' telah diupload oleh ULP.', route('laporan'));

        return back()->with('success', 'Berkas syarat berhasil disimpan.');
    }

    public function syaratFile(PelangganPbpd $pelanggan, string $jenis, int $index)
    {
        abort_unless(in_array($jenis, ['pendukung', 'ijin'], true), 404);
        $column = $jenis === 'pendukung' ? 'berkas_pendukung_paths' : 'berkas_ijin_paths';
        $path = (is_array($pelanggan->{$column}) ? $pelanggan->{$column} : [])[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }
}
