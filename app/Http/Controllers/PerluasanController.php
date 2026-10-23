<?php

namespace App\Http\Controllers;

use App\Models\PelangganPbpd;
use App\Models\Ulp;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PerluasanController extends Controller
{
    public function index(Request $request, string $tujuan)
    {
        abort_unless(in_array($tujuan, ['JTM', 'JTR', 'TANPA_PERLUASAN'], true), 404);

        $ulpUser = $this->currentUser()->ulpId();
        $role = $this->currentUser()->role;
        $canEditRab = (bool) $role?->ulp_id || $role?->role_code === '5170REN';
        $canEditMaterial = (bool) $role?->ulp_id;
        $canSendVendor = $role?->role_code === '5170REN';
        $canSendKonstruksi = $role?->role_code === '5170KON';
        $canUploadTransaksi = $role?->role_code === '5170TE';
        $canUploadJaringan = $role?->role_code === '5170JAR';
        $canUploadNps = $role?->role_code === '5170NPS';
        $canViewMaterial = $canEditRab || in_array($role?->role_code, ['5170KON', '5170TE', '5170JAR'], true);
        $showBerkasUlp = $role?->type === 'UP3';
        // ULP dan UP3 dapat memfilter seluruh alur proses. Tahap SELESAI
        // merepresentasikan jaringan dan tidak berlaku untuk Tanpa Perluasan.
        $tahapLabels = [
            'ULP' => 'ULP',
            'PERENCANAAN' => 'PERENCANAAN',
            'VENDOR_TIANG' => 'KONSTRUKSI (TIANG)',
            'VENDOR_KONSTRUKSI' => 'KONSTRUKSI',
            'PENGOPERASIAN' => 'TRANSAKSI',
            'SELESAI' => 'JARINGAN',
        ];
        $visibleTahaps = match ($role?->role_code) {
            '5170REN' => ['PERENCANAAN', 'VENDOR_TIANG'],
            '5170KON' => ['PERENCANAAN', 'VENDOR_KONSTRUKSI'],
            default => in_array($role?->type, ['UP3', 'ULP'], true)
                ? array_keys($tahapLabels)
                : ['PERENCANAAN'],
        };
        if ($tujuan === 'TANPA_PERLUASAN') {
            unset($tahapLabels['SELESAI']);
            $visibleTahaps = array_values(array_diff($visibleTahaps, ['SELESAI']));
        }
        $judul = match ($tujuan) {
            'JTM' => 'Perluasan JTM',
            'JTR' => 'Perluasan JTR',
            default => 'Tanpa Perluasan',
        };

        $query = PelangganPbpd::with(['ulp', 'permintaan', 'pengirimanVendor.vendor', 'laporanVendor', 'pengirimanKonstruksi.vendor'])
            ->statusTampil()
            ->whereIn('tahap', $visibleTahaps)
            // Data lama bisa tersimpan dengan spasi, sedangkan kiriman baru
            // menggunakan underscore. Samakan format saat ditampilkan.
            ->whereRaw("UPPER(REPLACE(tujuan_perluasan, ' ', '_')) = ?", [$tujuan])
            ->when($ulpUser, fn ($q) => $q->where('ulp_id', $ulpUser))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $term = '%'.$request->input('cari').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('no_agenda', 'like', $term)
                        ->orWhere('id_pelanggan', 'like', $term)
                        ->orWhere('nama_pelanggan', 'like', $term)
                        ->orWhere('alamat', 'like', $term);
                });
            })
            ->when($request->filled('ulp'), fn ($q) => $q->where('ulp_id', $request->input('ulp')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_transaksi', $request->input('jenis')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('tahap'), fn ($q) => $q->where('tahap', $request->input('tahap')));

        $data = (clone $query)->orderByDesc('id')->paginate(20)
            ->withQueryString();

        $summaryBase = PelangganPbpd::query()
            ->statusTampil()
            ->whereIn('tahap', $visibleTahaps)
            ->whereRaw("UPPER(REPLACE(tujuan_perluasan, ' ', '_')) = ?", [$tujuan])
            ->when($ulpUser, fn ($q) => $q->where('ulp_id', $ulpUser));
        $summaryByJenisRaw = (clone $summaryBase)
            ->selectRaw('UPPER(TRIM(jenis_transaksi)) AS jenis, COUNT(*) AS jumlah')
            ->whereNotNull('jenis_transaksi')
            ->groupByRaw('UPPER(TRIM(jenis_transaksi))')
            ->pluck('jumlah', 'jenis');
        $summaryByStatusRaw = (clone $summaryBase)
            ->selectRaw('UPPER(TRIM(status)) AS status, COUNT(*) AS jumlah')
            ->statusTampil()
            ->groupByRaw('UPPER(TRIM(status))')->pluck('jumlah', 'status');
        $summaryByJenis = collect([
            'PASANG BARU' => (int) (($summaryByJenisRaw['PASANG BARU'] ?? 0) + ($summaryByJenisRaw['PB'] ?? 0)),
            'PERUBAHAN DAYA' => (int) (($summaryByJenisRaw['PERUBAHAN DAYA'] ?? 0) + ($summaryByJenisRaw['PD'] ?? 0)),
        ]);
        $summaryByStatus = collect([
            'CETAK PK' => (int) ($summaryByStatusRaw['CETAK PK'] ?? 0),
            'BAYAR' => (int) ($summaryByStatusRaw['BAYAR'] ?? 0),
            'PENGESAHAN PDL' => (int) ($summaryByStatusRaw['PENGESAHAN PDL'] ?? 0),
            'PDL AWAL' => (int) ($summaryByStatusRaw['PDL AWAL'] ?? 0),
        ]);
        $isUlpSummary = $role?->type === 'ULP' && (bool) $role?->ulp_id;
        $showSummaryCards = in_array($role?->type, ['ULP', 'UP3'], true);
        $summaryTotal = (clone $summaryBase)->count();
        $summarySentBase = PelangganPbpd::query()
            ->statusTampil()
            ->whereRaw("UPPER(REPLACE(tujuan_perluasan, ' ', '_')) = ?", [$tujuan])
            ->whereNotNull('tahap')
            ->where('tahap', '!=', 'ULP')
            ->when($ulpUser, fn ($q) => $q->where('ulp_id', $ulpUser));
        $summarySentByUlp = (clone $summarySentBase)
            ->selectRaw('ulp_id, COUNT(*) AS jumlah')
            ->whereNotNull('ulp_id')->groupBy('ulp_id')->orderByDesc('jumlah')->get();
        $summarySentCounts = $summarySentByUlp->pluck('jumlah', 'ulp_id');
        $summaryUlpNames = Ulp::orderBy('nama')->get()->keyBy('id');
        $summarySentByUlp = $summaryUlpNames->map(function ($ulp) use ($summarySentCounts) {
            return (object) ['ulp_id' => $ulp->id, 'jumlah' => (int) ($summarySentCounts[$ulp->id] ?? 0)];
        })->values();

        $ulps = $ulpUser
            ? Ulp::whereKey($ulpUser)->get()
            : Ulp::orderBy('nama')->get();
        $jenisPilihan = (clone $query)->select('jenis_transaksi')->whereNotNull('jenis_transaksi')->distinct()->orderBy('jenis_transaksi')->pluck('jenis_transaksi');
        $statusPilihan = (clone $query)->select('status')->whereNotNull('status')->distinct()->orderBy('status')->pluck('status');
        $tahapPilihan = collect($tahapLabels)->only($visibleTahaps);

        $vendors = Vendor::with('user')->where('jenis', 'TIANG')->orderBy('nama')->get();
        $vendorsKonstruksi = Vendor::with('user')->where('jenis', 'KONSTRUKSI')->orderBy('nama')->get();

        return view('perluasan.index', compact('data', 'judul', 'tujuan', 'canEditRab', 'canEditMaterial', 'canViewMaterial', 'canSendVendor', 'canSendKonstruksi', 'canUploadNps', 'canUploadTransaksi', 'canUploadJaringan', 'vendors', 'vendorsKonstruksi', 'ulps', 'jenisPilihan', 'statusPilihan', 'tahapPilihan', 'showSummaryCards', 'isUlpSummary', 'summaryTotal', 'summarySentByUlp', 'summaryUlpNames', 'summaryByJenis', 'summaryByStatus', 'showBerkasUlp'));
    }
}
