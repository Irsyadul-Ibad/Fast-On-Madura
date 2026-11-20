<?php

namespace App\Http\Controllers;

use App\Models\PelangganPbpd;

class DashboardController extends Controller
{
    public function index()
    {
        $ulpId = $this->currentUser()->ulpId();
        $base = PelangganPbpd::query()
            ->statusTampil()
            ->when($ulpId, fn ($q) => $q->where('ulp_id', $ulpId))
            ->where('status', '!=', 'MOHON');

        $rows = (clone $base)->with('ulp')->orderByDesc('id')->get();
        $transaksiTerkirim = $rows
            ->filter(fn ($row) => filled($row->tahap) && ! in_array($row->tahap, ['ULP', 'SELESAI'], true))
            ->groupBy(fn ($row) => $row->jenis_transaksi ?: 'LAINNYA')
            ->map->count()
            ->sortDesc();
        $ulp = $rows->groupBy(fn ($row) => $row->asal_ulp ?: 'TANPA ULP')->map->count()->sortDesc();

        return view('dashboard', [
            'total' => $rows->count(),
            'belumDiproses' => $rows->filter(fn ($row) => ! $row->tahap || $row->tahap === 'ULP')->count(),
            'selesai' => $rows->where('tahap', 'SELESAI')->count(),
            'transaksiTerkirim' => $transaksiTerkirim,
            'ulp' => $ulp,
            'terbaru' => $rows->take(4),
        ]);
    }
}
