<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\PelangganPbpd;
use App\Models\Ulp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    /** Jenis transaksi yang ditampilkan pada menu Laporan. */
    private const JENIS_TRANSAKSI_LAPORAN = ['PASANG BARU', 'PERUBAHAN DAYA'];

    public function index(Request $request)
    {
        $role = $this->currentUser()->role;
        $isUp3 = $role?->type === 'UP3';
        $base = $this->laporanQuery($request);

        $data = (clone $base)->with(['ulp', 'permintaan', 'pengirimanVendor', 'pengirimanKonstruksi', 'laporanVendor'])
            ->orderByDesc('id')->paginate(30)->withQueryString();

        return view('laporan.index', [
            'data' => $data,
            'total' => (clone $base)->count(),
            'perTahap' => (clone $base)->selectRaw('tahap, COUNT(*) as jumlah')->groupBy('tahap')->orderBy('tahap')->pluck('jumlah', 'tahap'),
            'perJenis' => (clone $base)->selectRaw('jenis_transaksi, COUNT(*) as jumlah')->groupBy('jenis_transaksi')->orderBy('jenis_transaksi')->pluck('jumlah', 'jenis_transaksi'),
            'tahapPilihan' => collect([
                'ULP' => 'DIKIRIM ULP',
                'PERENCANAAN' => 'PERENCANAAN',
                'KONSTRUKSI' => 'KONSTRUKSI',
                'TRANSAKSI' => 'TRANSAKSI',
                'JARINGAN' => 'JARINGAN',
            ]),
            'jenisPilihan' => (clone $base)->whereNotNull('jenis_transaksi')->distinct()->orderBy('jenis_transaksi')->pluck('jenis_transaksi'),
            'statusPilihan' => collect([
                'CETAK PK',
                'BAYAR',
                'PENGESAHAN PDL',
                'PDL AWAL',
                'PEREMAJAAN',
            ]),
            'showUlpFilter' => $isUp3,
            'ulps' => $isUp3 ? Ulp::orderBy('nama')->get(['id', 'nama']) : collect(),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->laporanQuery($request)->with(['ulp', 'permintaan', 'pengirimanVendor', 'pengirimanKonstruksi', 'laporanVendor'])->get();

        return Excel::download(new LaporanExport($rows), 'laporan-pbpd-' . now()->format('Ymd-His') . '.xlsx');
    }

    private function laporanQuery(Request $request)
    {
        $role = $this->currentUser()->role;
        $ulpId = $role?->type === 'UP3'
            ? $request->input('ulp')
            : $this->currentUser()->ulpId();
        $tahap = $request->input('tahap');

        return PelangganPbpd::query()
            ->whereIn('jenis_transaksi', self::JENIS_TRANSAKSI_LAPORAN)
            ->where(function ($query) {
                // Data PEREMAJAAN langsung masuk laporan walaupun belum dikirim ULP.
                $query->whereRaw("UPPER(TRIM(status)) = 'PEREMAJAAN'")
                    // Data selain PEREMAJAAN masuk setelah dikirim ULP.
                    ->orWhere(function ($sent) {
                        $sent->whereNotNull('tahap')
                            ->whereRaw("UPPER(TRIM(tahap)) <> 'ULP'");
                    });
            })
            ->when(filled($ulpId), fn ($q) => $q->where('ulp_id', $ulpId))
            ->when($tahap === 'ULP', fn ($q) => $q->where(function ($query) {
                $query->where('tahap', '!=', 'ULP')
                    ->orWhere(function ($nested) {
                        $nested->whereNotNull('tujuan_perluasan')->whereRaw("TRIM(tujuan_perluasan) <> ''");
                    });
            }))
            ->when($tahap === 'PERENCANAAN', fn ($q) => $q->where(function ($query) {
                $query->whereHas('laporanVendor', fn ($vendor) => $vendor->whereRaw("jsonb_array_length(COALESCE(berkas_hasil_paths::jsonb, '[]'::jsonb)) > 0"))
                    ->orWhereHas('pengirimanKonstruksi', fn ($konstruksi) => $konstruksi->whereRaw("jsonb_array_length(COALESCE(hasil_paths::jsonb, '[]'::jsonb)) > 0"))
                    ->orWhereRaw("jsonb_array_length(COALESCE(nps_wo_paths::jsonb, '[]'::jsonb)) > 0");
            }))
            ->when($tahap === 'KONSTRUKSI', fn ($q) => $q->where(function ($query) {
                $query->whereRaw("jsonb_array_length(COALESCE(hasil_konstruksi_paths::jsonb, '[]'::jsonb)) > 0")
                    ->orWhereHas('pengirimanKonstruksi', fn ($konstruksi) => $konstruksi->whereRaw("jsonb_array_length(COALESCE(hasil_konstruksi_paths::jsonb, '[]'::jsonb)) > 0"));
            }))
            ->when($tahap === 'TRANSAKSI', fn ($q) => $q->whereRaw("jsonb_array_length(COALESCE(hasil_transaksi_paths::jsonb, '[]'::jsonb)) > 0"))
            ->when($tahap === 'JARINGAN', fn ($q) => $q->whereRaw("jsonb_array_length(COALESCE(hasil_jaringan_paths::jsonb, '[]'::jsonb)) > 0"))
            ->when($request->filled('no_agenda'), function ($q) use ($request) {
                $noAgenda = trim((string) $request->input('no_agenda'));
                $q->whereRaw('CAST(no_agenda AS TEXT) LIKE ?', ['%' . $noAgenda . '%']);
            })
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_transaksi', $request->jenis))
            ->when($request->filled('status'), fn ($q) => $q->whereRaw('UPPER(TRIM(status)) = ?', [strtoupper(trim((string) $request->status))]));
    }
}
