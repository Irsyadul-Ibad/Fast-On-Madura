<?php

namespace App\Http\Controllers;

use App\Models\StokTiang;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokTiangController extends Controller
{
    private function authorizePlanning(): void
    {
        abort_unless($this->currentUser()->role?->role_code === '5170REN', 403);
    }

    public function index()
    {
        $this->authorizePlanning();

        $vendors = Vendor::query()
            ->where('jenis', 'TIANG')
            ->with('stokTiang')
            ->orderBy('nama')
            ->get();

        return view('stok.tiang', compact('vendors'));
    }

    public function update(Request $request)
    {
        $this->authorizePlanning();

        $data = $request->validate([
            'stok' => ['required', 'array'],
            'stok.*' => ['required', 'array'],
            'stok.*.tr' => ['nullable', 'integer', 'min:0'],
            'stok.*.tm' => ['nullable', 'integer', 'min:0'],
        ], [
            'stok.*.tr.integer' => 'Stok tiang TR harus berupa angka bulat.',
            'stok.*.tr.min' => 'Stok tiang TR tidak boleh kurang dari 0.',
            'stok.*.tm.integer' => 'Stok tiang TM harus berupa angka bulat.',
            'stok.*.tm.min' => 'Stok tiang TM tidak boleh kurang dari 0.',
        ]);

        $vendors = Vendor::query()->where('jenis', 'TIANG')->get();
        $stokByVendor = collect($data['stok']);

        DB::transaction(function () use ($vendors, $stokByVendor) {
            foreach ($vendors as $vendor) {
                $stok = $stokByVendor->get((string) $vendor->id, $stokByVendor->get($vendor->id, []));

                StokTiang::updateOrCreate(
                    ['vendor_id' => $vendor->id],
                    [
                        'stok_tr' => (int) ($stok['tr'] ?? 0),
                        'stok_tm' => (int) ($stok['tm'] ?? 0),
                    ],
                );
            }
        });

        return to_route('stok.tiang')->with('success', 'Stok tiang berhasil disimpan.');
    }
}
