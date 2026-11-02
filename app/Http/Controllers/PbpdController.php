<?php

namespace App\Http\Controllers;

use App\Models\PelangganPbpd;
use App\Models\PengirimanKonstruksi;
use App\Models\PengirimanVendor;
use App\Models\PermintaanMaterial;
use App\Models\StokTiang;
use App\Models\Ulp;
use App\Models\Vendor;
use App\Services\NotifikasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PbpdController extends Controller
{
    public function index(Request $request)
    {
        $role = $this->currentUser()->role;
        $isUlpRole = $role?->type === 'ULP' && (bool) $role?->ulp_id;
        $ulpUser = $isUlpRole ? $this->currentUser()->ulpId() : null;
        $canKirim = $isUlpRole;
        $canEditRab = $canKirim || $role?->role_code === '5170REN';

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
                $kata = '%'.$request->cari.'%';
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
            ->where('status', '!=', 'MOHON');

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
            'data' => $data,
            'canKirim' => $canKirim,
            'canEditRab' => $canEditRab,
            'canUploadSyarat' => $canKirim,
            'showSyarat' => $role?->type !== 'UP3',
            'ulps' => $ulpUser ? collect() : Ulp::orderBy('kode')->get(),
            'statuss' => PelangganPbpd::whereIn('status', PelangganPbpd::STATUS_TAMPIL)
                ->distinct()->orderBy('status')->pluck('status'),
            'tahaps' => [
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

    public function updateRab(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        $role = $this->currentUser()->role;
        $canEditRab = (bool) $role?->ulp_id || $role?->role_code === '5170REN';
        abort_unless($canEditRab, 403);

        $ulpUser = $this->currentUser()->ulpId();
        abort_if($ulpUser && (int) $pelanggan->ulp_id !== (int) $ulpUser, 403);

        $data = $request->validate([
            'rab' => ['nullable', 'numeric', 'min:0'],
        ]);

        $pelanggan->update(['rab' => $data['rab'] ?? null]);
        NotifikasiService::untukData($pelanggan, 'RAB diperbarui', 'RAB data pelanggan '.$pelanggan->no_agenda.' telah diperbarui.', route('laporan'));

        return back()->with('success', 'RAB berhasil disimpan.');
    }

    public function kirim(Request $request): RedirectResponse
    {
        abort_unless((bool) $this->currentUser()->role?->ulp_id, 403);

        $data = $request->validate([
            'tujuan' => ['required', 'in:JTM,JTR,TANPA_PERLUASAN'],
            'pelanggan' => ['required', 'array', 'min:1'],
            'pelanggan.*' => ['integer', 'distinct', 'exists:pelanggan_pbpd,id'],
            'jenis_tiang_tm' => ['nullable', 'string', 'max:30'],
            'jml_tiang_tm' => ['nullable', 'integer', 'min:0'],
            'jenis_tiang_tr' => ['nullable', 'string', 'max:30'],
            'jml_tiang_tr' => ['nullable', 'integer', 'min:0'],
            'jenis_tiang' => ['nullable', 'string', 'max:30'],
            'jml_tiang' => ['nullable', 'integer', 'min:0'],
            'jenis_konduktor_tm' => ['nullable', 'string', 'max:30'],
            'jml_konduktor_tm' => ['nullable', 'integer', 'min:0'],
            'jenis_konduktor_tr' => ['nullable', 'string', 'max:30'],
            'jml_konduktor_tr' => ['nullable', 'integer', 'min:0'],
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
                        'jenis_tiang_tm' => $data['jenis_tiang_tm'] ?? $data['jenis_tiang'] ?? null,
                        'jml_tiang_tm' => $data['jml_tiang_tm'] ?? $data['jml_tiang'] ?? null,
                        'jenis_tiang_tr' => $data['jenis_tiang_tr'] ?? null,
                        'jml_tiang_tr' => $data['jml_tiang_tr'] ?? null,
                        'jenis_tiang' => $data['jenis_tiang_tm'] ?? $data['jenis_tiang'] ?? $data['jenis_tiang_tr'] ?? null,
                        'jml_tiang' => $data['jml_tiang_tm'] ?? $data['jml_tiang'] ?? $data['jml_tiang_tr'] ?? null,
                        'jenis_konduktor_tm' => $data['jenis_konduktor_tm'] ?? null,
                        'jml_konduktor_tm' => $data['jml_konduktor_tm'] ?? null,
                        'jenis_konduktor_tr' => $data['jenis_konduktor_tr'] ?? null,
                        'jml_konduktor_tr' => $data['jml_konduktor_tr'] ?? null,
                        'jenis_konduktor' => $data['jenis_konduktor_tm'] ?? null,
                        'jml_konduktor' => $data['jml_konduktor_tm'] ?? null,
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

        $pelanggan->each(fn ($row) => NotifikasiService::untukData($row, 'Data dikirim ULP', 'Data '.$row->no_agenda.' dikirim ke '.$data['tujuan'].'.', route('laporan')));

        return back()->with('success', $pelanggan->count().' data berhasil dikirim ke '.$data['tujuan'].'.');
    }

    public function updateDetail(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        $role = $this->currentUser()->role;
        $isUlp = (bool) $role?->ulp_id;
        $isPerencanaan = $role?->role_code === '5170REN';
        abort_unless($isUlp || $isPerencanaan, 403);

        $ulpUser = $this->currentUser()->ulpId();
        abort_if($ulpUser && (int) $pelanggan->ulp_id !== (int) $ulpUser, 403);

        abort_unless(in_array($pelanggan->tujuan_perluasan, ['JTM', 'JTR'], true), 422);

        $rules = ['rab' => ['nullable', 'numeric', 'min:0']];
        if ($isUlp) {
            $rules += [
                'jenis_tiang_tm' => ['nullable', 'string', 'max:30'],
                'jml_tiang_tm' => ['nullable', 'integer', 'min:0'],
                'jenis_tiang_tr' => ['nullable', 'string', 'max:30'],
                'jml_tiang_tr' => ['nullable', 'integer', 'min:0'],
                'jenis_tiang' => ['nullable', 'string', 'max:30'],
                'jml_tiang' => ['nullable', 'integer', 'min:0'],
                'jenis_konduktor_tm' => ['nullable', 'string', 'max:30'],
                'jml_konduktor_tm' => ['nullable', 'integer', 'min:0'],
                'jenis_konduktor_tr' => ['nullable', 'string', 'max:30'],
                'jml_konduktor_tr' => ['nullable', 'integer', 'min:0'],
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
                        'jenis_tiang_tm' => $data['jenis_tiang_tm'] ?? $data['jenis_tiang'] ?? null,
                        'jml_tiang_tm' => $data['jml_tiang_tm'] ?? $data['jml_tiang'] ?? null,
                        'jenis_tiang_tr' => $data['jenis_tiang_tr'] ?? null,
                        'jml_tiang_tr' => $data['jml_tiang_tr'] ?? null,
                        'jenis_tiang' => $data['jenis_tiang_tm'] ?? $data['jenis_tiang'] ?? $data['jenis_tiang_tr'] ?? null,
                        'jml_tiang' => $data['jml_tiang_tm'] ?? $data['jml_tiang'] ?? $data['jml_tiang_tr'] ?? null,
                        'jenis_konduktor_tm' => $data['jenis_konduktor_tm'] ?? null,
                        'jml_konduktor_tm' => $data['jml_konduktor_tm'] ?? null,
                        'jenis_konduktor_tr' => $data['jenis_konduktor_tr'] ?? null,
                        'jml_konduktor_tr' => $data['jml_konduktor_tr'] ?? null,
                        'jenis_konduktor' => $data['jenis_konduktor_tm'] ?? null,
                        'jml_konduktor' => $data['jml_konduktor_tm'] ?? null,
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

        NotifikasiService::untukData($pelanggan, 'Detail/RAB diperbarui', 'Detail material atau RAB '.$pelanggan->no_agenda.' telah diperbarui.', route('laporan'));

        return back()->with('success', 'RAB dan kebutuhan perluasan berhasil diperbarui.');
    }

    public function kirimVendor(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170REN', 403);

        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'status_kelayakan' => ['required', 'in:LAYAK,TIDAK LAYAK'],
            'wo_tiang' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $vendor = Vendor::whereKey($data['vendor_id'])->where('jenis', 'TIANG')->firstOrFail();
        $pelanggan->loadMissing('permintaan');

        $kebutuhanTr = (int) ($pelanggan->permintaan?->jml_tiang_tr ?? 0);
        $kebutuhanTm = (int) ($pelanggan->permintaan?->jml_tiang_tm ?? $pelanggan->permintaan?->jml_tiang ?? 0);
        $woFile = $request->file('wo_tiang');

        $result = DB::transaction(function () use ($data, $pelanggan, $vendor, $kebutuhanTr, $kebutuhanTm, $woFile) {
            $pengiriman = PengirimanVendor::query()
                ->where('pelanggan_id', $pelanggan->id)
                ->lockForUpdate()
                ->first();
            $laporanSaatIni = $pelanggan->laporanVendor()->lockForUpdate()->first();

            if ($pengiriman && filled($laporanSaatIni?->vendor_report_at)) {
                return ['error' => 'Pengiriman terkunci karena vendor tiang sudah mengirim laporan.'];
            }

            $pengirimanLamaVendorId = $pengiriman?->vendor_id;
            $vendorBerubah = $pengiriman
                && (int) $pengirimanLamaVendorId !== (int) $vendor->id;
            $stokTercatat = $pengiriman
                && $pengiriman->stok_tr_dipakai !== null
                && $pengiriman->stok_tm_dipakai !== null;
            $perluPotongStokBaru = ! $pengiriman || $vendorBerubah || ! $stokTercatat;

            $vendorIds = array_values(array_unique(array_filter([
                (int) $vendor->id,
                $pengirimanLamaVendorId ? (int) $pengirimanLamaVendorId : null,
            ])));
            $stokRows = StokTiang::query()
                ->whereIn('vendor_id', $vendorIds)
                ->orderBy('vendor_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('vendor_id');

            $stokVendorBaru = $stokRows->get($vendor->id);
            if ($perluPotongStokBaru && (! $stokVendorBaru || ((int) $stokVendorBaru->stok_tr === 0 && (int) $stokVendorBaru->stok_tm === 0))) {
                return ['error' => 'Pengiriman tidak dapat dilakukan karena stok tiang vendor belum diisi atau sedang kosong.'];
            }

            $perubahanStok = [];
            if (! $pengiriman) {
                $perubahanStok[$vendor->id] = ['tr' => $kebutuhanTr, 'tm' => $kebutuhanTm];
            } elseif ($vendorBerubah) {
                if ($stokTercatat) {
                    $perubahanStok[$pengirimanLamaVendorId] = [
                        'tr' => -((int) $pengiriman->stok_tr_dipakai),
                        'tm' => -((int) $pengiriman->stok_tm_dipakai),
                    ];
                }
                $perubahanStok[$vendor->id] = ['tr' => $kebutuhanTr, 'tm' => $kebutuhanTm];
            } elseif ($stokTercatat) {
                $perubahanStok[$vendor->id] = [
                    'tr' => $kebutuhanTr - (int) $pengiriman->stok_tr_dipakai,
                    'tm' => $kebutuhanTm - (int) $pengiriman->stok_tm_dipakai,
                ];
            } else {
                // Pengiriman lama belum memiliki catatan pemakaian stok.
                // Potong sekali saat pengiriman tersebut disimpan kembali.
                $perubahanStok[$vendor->id] = ['tr' => $kebutuhanTr, 'tm' => $kebutuhanTm];
            }

            foreach ($perubahanStok as $vendorId => $perubahan) {
                $stok = $stokRows->get($vendorId);
                if (! $stok) {
                    if ($perubahan['tr'] > 0 || $perubahan['tm'] > 0) {
                        return ['error' => 'Stok tiang vendor belum diisi.'];
                    }
                    continue;
                }

                $kurang = [];
                if ($perubahan['tr'] > 0 && (int) $stok->stok_tr < $perubahan['tr']) {
                    $kurang[] = 'TR membutuhkan '.($perubahan['tr']).', tersedia '.$stok->stok_tr;
                }
                if ($perubahan['tm'] > 0 && (int) $stok->stok_tm < $perubahan['tm']) {
                    $kurang[] = 'TM membutuhkan '.($perubahan['tm']).', tersedia '.$stok->stok_tm;
                }
                if ($kurang !== []) {
                    return ['error' => 'Stok vendor tidak mencukupi: '.implode('; ', $kurang).'.'];
                }
            }

            foreach ($perubahanStok as $vendorId => $perubahan) {
                $stok = $stokRows->get($vendorId);
                if (! $stok) {
                    continue;
                }

                $stok->update([
                    'stok_tr' => (int) $stok->stok_tr - $perubahan['tr'],
                    'stok_tm' => (int) $stok->stok_tm - $perubahan['tm'],
                ]);
            }

            if (! $pengiriman) {
                $pengiriman = new PengirimanVendor(['pelanggan_id' => $pelanggan->id]);
            }
            if ($vendorBerubah) {
                $this->clearLaporanVendorTiang($pelanggan);
            }

            if ($woFile) {
                if ($pengiriman->wo_tiang_path) {
                    Storage::disk('public')->delete($pengiriman->wo_tiang_path);
                }
                $pengiriman->wo_tiang_path = $woFile->store('wo-tiang', 'public');
                $pengiriman->wo_tiang_name = $woFile->getClientOriginalName();
            }

            $pengiriman->fill([
                'vendor_id' => $vendor->id,
                'status_kelayakan' => $data['status_kelayakan'],
                'dikirim_oleh' => Auth::id(),
                'dikirim_at' => now(),
            ]);
            $pengiriman->stok_tr_dipakai = $kebutuhanTr;
            $pengiriman->stok_tm_dipakai = $kebutuhanTm;
            $pengiriman->save();

            $laporan = $pelanggan->laporanVendor()->first();
            if ($laporan && (int) $laporan->vendor_id !== (int) $vendor->id) {
                $laporan->update(['vendor_id' => $vendor->id]);
            }

            $pelanggan->update(['tahap' => 'VENDOR_TIANG']);

            return ['success' => true];
        });

        if (! empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        NotifikasiService::untukData($pelanggan, 'Data dikirim ke vendor tiang', 'Data '.$pelanggan->no_agenda.' dikirim ke vendor tiang.', route('laporan'), [$vendor->user_id]);

        return back()->with('success', 'Data berhasil dikirim ke vendor tiang.');
    }

    public function hapusKirimVendor(PelangganPbpd $pelanggan): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170REN', 403);

        $result = DB::transaction(function () use ($pelanggan) {
            $pengiriman = $pelanggan->pengirimanVendor()
                ->lockForUpdate()
                ->firstOrFail();
            if (filled($pelanggan->laporanVendor()->value('vendor_report_at'))) {
                return ['error' => 'Pengiriman tidak dapat dihapus karena vendor tiang sudah mengirim laporan.'];
            }

            if ($pengiriman->stok_tr_dipakai !== null || $pengiriman->stok_tm_dipakai !== null) {
                $stok = StokTiang::firstOrCreate(
                    ['vendor_id' => $pengiriman->vendor_id],
                    ['stok_tr' => 0, 'stok_tm' => 0],
                );
                $stok = StokTiang::whereKey($stok->id)->lockForUpdate()->first();
                $stok->update([
                    'stok_tr' => (int) $stok->stok_tr + (int) ($pengiriman->stok_tr_dipakai ?? 0),
                    'stok_tm' => (int) $stok->stok_tm + (int) ($pengiriman->stok_tm_dipakai ?? 0),
                ]);
            }

            if ($pengiriman->wo_tiang_path) {
                Storage::disk('public')->delete($pengiriman->wo_tiang_path);
            }

            $this->clearLaporanVendorTiang($pelanggan);
            $pengiriman->delete();

            if ($pelanggan->tahap === 'VENDOR_TIANG') {
                $pelanggan->update(['tahap' => 'PERENCANAAN']);
            }

            return ['success' => true];
        });

        if (! empty($result['error'])) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Pengiriman ke vendor tiang berhasil dihapus.');
    }

    private function clearLaporanVendorTiang(PelangganPbpd $pelanggan): void
    {
        $laporan = $pelanggan->laporanVendor()->first();
        if (! $laporan) {
            return;
        }

        foreach ($this->storedPaths($laporan->berkas_paths) as $path) {
            Storage::disk('public')->delete($path);
        }

        $laporan->update([
            'vendor_id' => null,
            'pekerjaan_lengkap' => false,
            'pekerjaan_sesuai_wo' => false,
            'foto_terlampir' => false,
            'siap_dilanjutkan' => false,
            'catatan' => null,
            'berkas_paths' => null,
            'berkas_names' => null,
            'vendor_report_at' => null,
        ]);
    }

    public function kirimKonstruksi(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170KON', 403);

        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'berkas.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $vendor = Vendor::whereKey($data['vendor_id'])->where('jenis', 'KONSTRUKSI')->firstOrFail();
        $pengiriman = PengirimanKonstruksi::firstOrNew(['pelanggan_id' => $pelanggan->id]);
        if ($pengiriman->exists && filled($pengiriman->laporan_at)) {
            return back()->with('error', 'Pengiriman terkunci karena vendor konstruksi sudah mengirim laporan.');
        }

        $vendorBerubah = $pengiriman->exists && (int) $pengiriman->vendor_id !== (int) $vendor->id;
        if ($vendorBerubah) {
            $this->clearLaporanVendorKonstruksi($pengiriman);
        }
        $uploadedFiles = $request->file('berkas', []);
        $existingPaths = $this->arrayPaths($pengiriman->berkas_paths);
        if ($existingPaths === [] && $uploadedFiles === []) {
            throw ValidationException::withMessages([
                'berkas' => 'Unggah minimal satu berkas untuk mengirim data ke vendor konstruksi.',
            ]);
        }
        $paths = $this->appendUploadedFiles(
            $existingPaths,
            $uploadedFiles,
            'pengiriman-konstruksi',
        );
        $names = array_slice(
            array_pad($this->arrayPaths($pengiriman->berkas_names), count($existingPaths), null),
            0,
            count($existingPaths),
        );
        foreach ($uploadedFiles as $file) {
            $names[] = $file->getClientOriginalName();
        }

        $pengiriman->fill([
            'vendor_id' => $vendor->id,
            'berkas_paths' => $paths,
            'berkas_names' => $names,
            'dikirim_oleh' => Auth::id(),
            'dikirim_at' => now(),
        ])->save();
        $pelanggan->update(['tahap' => 'VENDOR_KONSTRUKSI']);
        NotifikasiService::untukData($pelanggan, 'Data dikirim ke vendor konstruksi', 'Data '.$pelanggan->no_agenda.' dikirim ke vendor konstruksi.', route('laporan'), [$vendor->user_id]);

        return back()->with('success', 'Data berhasil dikirim ke vendor konstruksi.');
    }

    public function hapusKirimKonstruksi(PelangganPbpd $pelanggan): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170KON', 403);

        $pengiriman = $pelanggan->pengirimanKonstruksi()->firstOrFail();
        if (filled($pengiriman->laporan_at)) {
            return back()->with('error', 'Pengiriman tidak dapat dihapus karena vendor konstruksi sudah mengirim laporan.');
        }

        foreach ([...$this->arrayPaths($pengiriman->berkas_paths), ...$this->arrayPaths($pengiriman->laporan_paths)] as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->clearLaporanVendorKonstruksi($pengiriman, false);
        $pengiriman->update([
            'berkas_paths' => null,
            'berkas_names' => null,
        ]);

        if ($pelanggan->tahap === 'VENDOR_KONSTRUKSI') {
            $pelanggan->update(['tahap' => 'PERENCANAAN']);
        }

        return back()->with('success', 'Pengiriman ke vendor konstruksi berhasil dihapus.');
    }

    private function clearLaporanVendorKonstruksi(PengirimanKonstruksi $pengiriman, bool $deleteFiles = true): void
    {
        if ($deleteFiles) {
            foreach ($this->arrayPaths($pengiriman->laporan_paths) as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $pengiriman->update([
            'pekerjaan_lengkap' => null,
            'pekerjaan_sesuai_wo' => null,
            'foto_terlampir' => null,
            'siap_dilanjutkan' => null,
            'catatan' => null,
            'laporan_paths' => null,
            'laporan_names' => null,
            'laporan_at' => null,
        ]);
    }

    public function uploadNpsWo(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        $this->authorizeNpsUpload($pelanggan);

        $request->validate([
            'berkas_nps_wo' => ['required', 'array', 'min:1'],
            'berkas_nps_wo.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $paths = $this->appendUploadedFiles(
            $this->arrayPaths($pelanggan->nps_wo_paths),
            $request->file('berkas_nps_wo', []),
            'nps/wo',
        );

        $pelanggan->update(['nps_wo_paths' => $paths, 'nps_wo_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas WO NPS diupload', 'Berkas WO NPS untuk '.$pelanggan->no_agenda.' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas WO NPS berhasil disimpan.');
    }

    public function npsWoFile(PelangganPbpd $pelanggan, int $index)
    {
        $this->authorizeNpsFileView();
        $path = $this->arrayPaths($pelanggan->nps_wo_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusNpsWo(PelangganPbpd $pelanggan, int $index): RedirectResponse
    {
        $this->authorizeNpsUpload($pelanggan);
        $paths = $this->arrayPaths($pelanggan->nps_wo_paths);
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $pelanggan->update(['nps_wo_paths' => $paths ?: null, 'nps_wo_at' => $paths ? $pelanggan->nps_wo_at : null]);

        return back()->with('success', 'Berkas WO NPS berhasil dihapus.');
    }

    private function authorizeNpsUpload(PelangganPbpd $pelanggan): void
    {
        abort_unless($this->currentUser()->role?->role_code === '5170NPS', 403);
    }

    private function authorizeNpsFileView(): void
    {
        abort_unless($this->currentUser()->role?->type === 'UP3', 403);
    }

    public function uploadHasilTransaksi(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170TE', 403);
        $request->validate([
            'berkas_hasil_transaksi.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $paths = $this->appendUploadedFiles(
            $this->arrayPaths($pelanggan->hasil_transaksi_paths),
            $request->file('berkas_hasil_transaksi', []),
            'hasil-transaksi',
        );
        $pelanggan->update(['hasil_transaksi_paths' => $paths, 'hasil_transaksi_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil transaksi diupload', 'Berkas hasil transaksi untuk '.$pelanggan->no_agenda.' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil transaksi berhasil disimpan.');
    }

    public function hasilTransaksiFile(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->type === 'UP3', 403);
        $path = $this->arrayPaths($pelanggan->hasil_transaksi_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusHasilTransaksi(PelangganPbpd $pelanggan, int $index): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170TE', 403);

        $paths = $this->arrayPaths($pelanggan->hasil_transaksi_paths);
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $pelanggan->update(['hasil_transaksi_paths' => $paths ?: null]);

        return back()->with('success', 'Foto Cek KWH Meter berhasil dihapus.');
    }

    public function uploadHasilJaringan(Request $request, PelangganPbpd $pelanggan): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170JAR', 403);
        $request->validate([
            'berkas_hasil_jaringan.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $paths = $this->appendUploadedFiles(
            $this->arrayPaths($pelanggan->hasil_jaringan_paths),
            $request->file('berkas_hasil_jaringan', []),
            'hasil-jaringan',
        );
        $pelanggan->update(['hasil_jaringan_paths' => $paths, 'hasil_jaringan_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil jaringan diupload', 'Berkas hasil jaringan untuk '.$pelanggan->no_agenda.' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil jaringan berhasil disimpan.');
    }

    public function hasilJaringanFile(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->type === 'UP3', 403);
        $path = $this->arrayPaths($pelanggan->hasil_jaringan_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusHasilJaringan(PelangganPbpd $pelanggan, int $index): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->role_code === '5170JAR', 403);

        $paths = $this->arrayPaths($pelanggan->hasil_jaringan_paths);
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $pelanggan->update(['hasil_jaringan_paths' => $paths ?: null]);

        return back()->with('success', 'Berkas BA Operasi berhasil dihapus.');
    }

    public function uploadSyarat(Request $request, PelangganPbpd $pelanggan, string $jenis): RedirectResponse
    {
        abort_unless($this->currentUser()->role?->ulp_id, 403);
        abort_unless(in_array($jenis, ['pendukung', 'ijin'], true), 404);
        abort_if((int) $pelanggan->ulp_id !== (int) $this->currentUser()->role?->ulp_id, 403);

        $request->validate([
            'berkas.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);
        $column = $jenis === 'pendukung' ? 'berkas_pendukung_paths' : 'berkas_ijin_paths';
        $namesColumn = $jenis === 'pendukung' ? 'berkas_pendukung_names' : 'berkas_ijin_names';
        $uploadedFiles = $request->file('berkas', []);
        $existingPaths = $this->arrayPaths($pelanggan->{$column});
        $paths = $this->appendUploadedFiles(
            $existingPaths,
            $uploadedFiles,
            'syarat/'.$jenis,
        );
        $names = array_slice(
            array_pad($this->arrayPaths($pelanggan->{$namesColumn}), count($existingPaths), null),
            0,
            count($existingPaths),
        );
        foreach ($uploadedFiles as $file) {
            $names[] = $file->getClientOriginalName();
        }

        $pelanggan->update([
            $column => $paths,
            $namesColumn => $names,
        ]);
        NotifikasiService::untukData($pelanggan, 'Berkas syarat diupload', 'Berkas '.$jenis.' untuk '.$pelanggan->no_agenda.' telah diupload oleh ULP.', route('laporan'));

        return back()->with('success', 'Berkas syarat berhasil disimpan.');
    }

    public function syaratFile(PelangganPbpd $pelanggan, string $jenis, int $index)
    {
        abort_unless(in_array($jenis, ['pendukung', 'ijin'], true), 404);
        $column = $jenis === 'pendukung' ? 'berkas_pendukung_paths' : 'berkas_ijin_paths';
        $namesColumn = $jenis === 'pendukung' ? 'berkas_pendukung_names' : 'berkas_ijin_names';
        $path = $this->arrayPaths($pelanggan->{$column})[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $name = $this->arrayPaths($pelanggan->{$namesColumn})[$index] ?? basename($path);

        return $this->publicFileResponse($path, basename($name));
    }
}
