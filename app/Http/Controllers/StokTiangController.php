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
            'stok.*.tm_11' => ['nullable', 'integer', 'min:0'],
            'stok.*.tm_13' => ['nullable', 'integer', 'min:0'],
        ], [
            'stok.*.tr.integer' => 'Stok tiang TR harus berupa angka bulat.',
            'stok.*.tr.min' => 'Stok tiang TR tidak boleh kurang dari 0.',
            'stok.*.tm_11.integer' => 'Stok tiang TM jenis 11 harus berupa angka bulat.',
            'stok.*.tm_11.min' => 'Stok tiang TM jenis 11 tidak boleh kurang dari 0.',
            'stok.*.tm_13.integer' => 'Stok tiang TM jenis 13 harus berupa angka bulat.',
            'stok.*.tm_13.min' => 'Stok tiang TM jenis 13 tidak boleh kurang dari 0.',
        ]);

        $vendors = Vendor::query()->where('jenis', 'TIANG')->get();
        $stokByVendor = collect($data['stok']);

        DB::transaction(function () use ($vendors, $stokByVendor) {
            foreach ($vendors as $vendor) {
                $stok = $stokByVendor->get((string) $vendor->id, $stokByVendor->get($vendor->id, []));
                $stokTm11 = (int) ($stok['tm_11'] ?? 0);
                $stokTm13 = (int) ($stok['tm_13'] ?? 0);

                StokTiang::updateOrCreate(
                    ['vendor_id' => $vendor->id],
                    [
                        'stok_tr' => (int) ($stok['tr'] ?? 0),
                        'stok_tm' => $stokTm11 + $stokTm13,
                        'stok_tm_11' => $stokTm11,
                        'stok_tm_13' => $stokTm13,
                    ],
                );
            }
        });

        return to_route('stok.tiang')->with('success', 'Stok tiang berhasil disimpan.');
    }
}
