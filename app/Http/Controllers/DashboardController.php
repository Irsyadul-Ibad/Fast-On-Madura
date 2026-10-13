<?php

namespace App\Http\Controllers;

use App\Models\PelangganPbpd;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $ulpId = $this->currentUser()->ulpId();
        $base = PelangganPbpd::query()
            ->statusTampil()
            ->when($ulpId, fn ($q) => $q->where('ulp_id', $ulpId))
            ->where('status', '!=', 'MOHON')
            ;

        $rows = (clone $base)->with('ulp')->orderByDesc('id')->get();
        $tahapLabel = function ($value): string {
            if (! $value || $value === 'ULP') {
                return 'BELUM DIPROSES';
            }
            if ($value === 'SELESAI') {
                return 'SELESAI';
            }
            return 'SEDANG DIPROSES';
        };
        $tahap = $rows->groupBy(fn ($row) => $tahapLabel($row->tahap))->map->count()
            ->sortBy(fn ($jumlah, $nama) => array_search($nama, ['BELUM DIPROSES', 'SEDANG DIPROSES', 'SELESAI'], true))
            ->sortKeysUsing(fn ($a, $b) => array_search($a, ['BELUM DIPROSES', 'SEDANG DIPROSES', 'SELESAI'], true) <=> array_search($b, ['BELUM DIPROSES', 'SEDANG DIPROSES', 'SELESAI'], true));
        $tujuan = $rows->groupBy(fn ($row) => $row->tujuan_perluasan ?: 'BELUM DIKIRIM')->map->count()->sortDesc();
        $transaksi = $rows->groupBy(fn ($row) => $row->jenis_transaksi ?: 'LAINNYA')->map->count()->sortDesc();
        $ulp = $rows->groupBy(fn ($row) => $row->asal_ulp ?: 'TANPA ULP')->map->count()->sortDesc();

        return view('dashboard', [
            'total' => $rows->count(),
            'belumDiproses' => $rows->filter(fn ($row) => ! $row->tahap || $row->tahap === 'ULP')->count(),
            'perencanaan' => $rows->filter(fn ($row) => $row->tahap && $row->tahap !== 'ULP' && $row->tahap !== 'SELESAI')->count(),
            'vendor' => $rows->whereIn('tahap', ['VENDOR_TIANG', 'VENDOR_KONSTRUKSI'])->count(),
            'selesai' => $rows->where('tahap', 'SELESAI')->count(),
            'tahap' => $tahap,
            'tujuan' => $tujuan,
            'transaksi' => $transaksi,
            'ulp' => $ulp,
            'terbaru' => $rows->take(8),
        ]);
    }
}
