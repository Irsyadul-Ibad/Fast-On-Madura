<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\PelangganPbpd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $base = $this->laporanQuery($request);

        $data = (clone $base)->with(['ulp', 'permintaan', 'pengirimanVendor', 'pengirimanKonstruksi', 'laporanVendor'])
            ->when($request->filled('tahap'), fn ($q) => $q->where('tahap', $request->tahap))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_transaksi', $request->jenis))
            ->orderByDesc('id')->paginate(30)->withQueryString();

        return view('laporan.index', [
            'data' => $data,
            'total' => (clone $base)->count(),
            'perTahap' => (clone $base)->selectRaw('tahap, COUNT(*) as jumlah')->groupBy('tahap')->orderBy('tahap')->pluck('jumlah', 'tahap'),
            'perJenis' => (clone $base)->selectRaw('jenis_transaksi, COUNT(*) as jumlah')->groupBy('jenis_transaksi')->orderBy('jenis_transaksi')->pluck('jumlah', 'jenis_transaksi'),
            'tahapPilihan' => collect([
                'PERENCANAAN' => 'PERENCANAAN',
                'VENDOR_TIANG' => 'KONSTRUKSI (TIANG)',
                'VENDOR_KONSTRUKSI' => 'KONSTRUKSI',
                'PENGOPERASIAN' => 'TRANSAKSI',
                'SELESAI' => 'JARINGAN',
            ]),
            'jenisPilihan' => (clone $base)->whereNotNull('jenis_transaksi')->distinct()->orderBy('jenis_transaksi')->pluck('jenis_transaksi'),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->laporanQuery($request)->with(['ulp', 'permintaan', 'pengirimanVendor', 'pengirimanKonstruksi', 'laporanVendor'])->get();

        return Excel::download(new LaporanExport($rows), 'laporan-pbpd-' . now()->format('Ymd-His') . '.xlsx');
    }

    private function laporanQuery(Request $request)
    {
        $ulpId = $this->currentUser()->ulpId();

        return PelangganPbpd::query()
            ->statusTampil()
            ->when($ulpId, fn ($q) => $q->where('ulp_id', $ulpId))
            ->whereNotNull('tujuan_perluasan')
            ->whereRaw("TRIM(tujuan_perluasan) <> ''")
            ->whereNotNull('tahap')
            ->where('tahap', '!=', 'ULP')
            ->when($request->filled('tahap'), fn ($q) => $q->where('tahap', $request->tahap))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_transaksi', $request->jenis));
    }
}
