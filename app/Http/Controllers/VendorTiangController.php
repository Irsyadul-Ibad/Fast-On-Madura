<?php

namespace App\Http\Controllers;

use App\Models\LaporanVendor;
use App\Models\PelangganPbpd;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VendorTiangController extends Controller
{
    private function authorizeVendor(): void
    {
        abort_unless(in_array($this->currentUser()->role?->role_code, ['VENDOR_TIANG', 'VENDOR_TIANG2', '5180T1', '5180T2', '5180T3', '5180T4', '5180T5', '5180T6', '5180T7', '5180T8', '5180T9'], true), 403);
    }

    public function index()
    {
        $this->authorizeVendor();

        $data = PelangganPbpd::with(['ulp', 'pengirimanVendor.vendor', 'laporanVendor'])
            ->statusTampil()
            ->where('tahap', 'VENDOR_TIANG')
            ->whereHas('pengirimanVendor', fn ($q) => $q->whereHas('vendor', fn ($v) => $v
                ->where('jenis', 'TIANG')
                ->where('user_id', Auth::id())
            ))
            ->orderByDesc('id')
            ->get();

        $riwayat = PelangganPbpd::with(['ulp', 'pengirimanVendor.vendor', 'laporanVendor'])
            ->statusTampil()
            ->whereHas('laporanVendor')
            ->whereHas('pengirimanVendor.vendor', fn ($q) => $q
                ->where('jenis', 'TIANG')
                ->where('user_id', Auth::id())
            )
            ->orderByDesc('id')
            ->get();

        return view('vendor.tiang', ['data' => $data, 'riwayat' => $riwayat, 'showHistory' => false]);
    }

    public function history()
    {
        $this->authorizeVendor();

        $riwayat = PelangganPbpd::with(['ulp', 'pengirimanVendor.vendor', 'laporanVendor'])
            ->statusTampil()
            ->whereHas('laporanVendor')
            ->whereHas('pengirimanVendor.vendor', fn ($q) => $q
                ->where('jenis', 'TIANG')
                ->where('user_id', Auth::id())
            )
            ->orderByDesc('id')
            ->get();

        return view('vendor.tiang', ['data' => collect(), 'riwayat' => $riwayat, 'showHistory' => true]);
    }

    public function kirimLaporan(Request $request, PelangganPbpd $pelanggan)
    {
        $this->authorizeVendor();
        abort_unless(in_array($pelanggan->tahap, ['VENDOR_TIANG', 'PERENCANAAN'], true), 422);
        $pengiriman = $pelanggan->pengirimanVendor()->with('vendor')->firstOrFail();
        abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);

        $data = $request->validate([
            'pekerjaan_lengkap' => ['nullable', 'boolean'],
            'pekerjaan_sesuai_wo' => ['nullable', 'boolean'],
            'foto_terlampir' => ['nullable', 'boolean'],
            'siap_dilanjutkan' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'berkas.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $laporan = LaporanVendor::firstOrNew(['pelanggan_id' => $pelanggan->id]);
        $paths = $this->paths($laporan->berkas_paths);
        foreach ($request->file('berkas', []) as $file) {
            $paths[] = $file->store('laporan-vendor', 'public');
        }

        $laporan->fill([
            'vendor_id' => $pelanggan->pengirimanVendor?->vendor_id,
            'pekerjaan_lengkap' => $request->boolean('pekerjaan_lengkap'),
            'pekerjaan_sesuai_wo' => $request->boolean('pekerjaan_sesuai_wo'),
            'foto_terlampir' => $request->boolean('foto_terlampir'),
            'siap_dilanjutkan' => $request->boolean('siap_dilanjutkan'),
            'catatan' => $data['catatan'] ?? null,
            'berkas_paths' => $paths,
            'dikirim_oleh' => Auth::id(),
            'dikirim_at' => now(),
        ])->save();

        if ($pelanggan->tahap === 'VENDOR_TIANG') {
            $pelanggan->update(['tahap' => 'PERENCANAAN']);
        }
        NotifikasiService::untukData($pelanggan, 'Laporan vendor tiang masuk', 'Vendor tiang telah mengirim laporan untuk ' . $pelanggan->no_agenda . '.', route('laporan'));

        return back()->with('success', 'Laporan berhasil dikirim ke Perencanaan.');
    }

    public function wo(PelangganPbpd $pelanggan)
    {
        $roleCode = $this->currentUser()->role?->role_code;
        $isPlanning = $roleCode === '5180REN';
        $isVendor = in_array($roleCode, ['VENDOR_TIANG', 'VENDOR_TIANG2', '5180T1', '5180T2', '5180T3', '5180T4', '5180T5', '5180T6', '5180T7', '5180T8', '5180T9'], true);

        abort_unless($isPlanning || $isVendor, 403);

        $pengiriman = $pelanggan->pengirimanVendor()->with('vendor')->firstOrFail();
        if ($isVendor) {
            abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        }

        abort_unless($pengiriman->wo_tiang_path && Storage::disk('public')->exists($pengiriman->wo_tiang_path), 404);

        return $this->publicFileResponse($pengiriman->wo_tiang_path);
    }

    public function laporanFile(PelangganPbpd $pelanggan, int $index)
    {
        $roleCode = $this->currentUser()->role?->role_code;
        $isPlanning = $roleCode === '5180REN';
        $isUp3 = $this->currentUser()->role?->type === 'UP3';
        $isVendor = in_array($roleCode, ['VENDOR_TIANG', 'VENDOR_TIANG2', '5180T1', '5180T2', '5180T3', '5180T4', '5180T5', '5180T6', '5180T7', '5180T8', '5180T9'], true);
        abort_unless($isPlanning || $isUp3 || $isVendor, 403);

        $pengiriman = $pelanggan->pengirimanVendor()->with('vendor')->firstOrFail();
        if ($isVendor) {
            abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        }

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $path = $this->paths($laporan->berkas_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function uploadHasil(Request $request, PelangganPbpd $pelanggan)
    {
        abort_unless($this->currentUser()->role?->role_code === '5180REN', 403);

        $data = $request->validate([
            'berkas_hasil.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $paths = $this->paths($laporan->berkas_hasil_paths);
        foreach ($request->file('berkas_hasil', []) as $file) {
            $paths[] = $file->store('hasil-perencanaan', 'public');
        }

        $laporan->update(['berkas_hasil_paths' => $paths, 'berkas_hasil_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil perencanaan diupload', 'Berkas hasil perencanaan untuk ' . $pelanggan->no_agenda . ' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil perencanaan berhasil disimpan.');
    }

    private function paths(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [$value];
        }
        return [];
    }

    public function uploadHasilFromExpansion(Request $request)
    {
        abort_unless($this->currentUser()->role?->role_code === '5180REN', 403);
        $request->validate(['pelanggan_id' => ['required', 'integer', 'exists:pelanggan_pbpd,id']]);

        return $this->uploadHasil($request, PelangganPbpd::findOrFail($request->integer('pelanggan_id')));
    }

    public function hasilFile(PelangganPbpd $pelanggan, int $index)
    {
        $roleCode = $this->currentUser()->role?->role_code;
        $isPlanning = $roleCode === '5180REN';
        $isUp3 = $this->currentUser()->role?->type === 'UP3';
        $isVendor = in_array($roleCode, ['VENDOR_TIANG', 'VENDOR_TIANG2', '5180T1', '5180T2', '5180T3', '5180T4', '5180T5', '5180T6', '5180T7', '5180T8', '5180T9'], true);
        abort_unless($isPlanning || $isUp3 || $isVendor, 403);

        $pengiriman = $pelanggan->pengirimanVendor()->with('vendor')->firstOrFail();
        if ($isVendor) {
            abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        }

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $path = $this->paths($laporan->berkas_hasil_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusHasil(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->role_code === '5180REN', 403);

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $paths = $this->paths($laporan->berkas_hasil_paths);
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $laporan->update(['berkas_hasil_paths' => $paths ?: null]);

        return back()->with('success', 'Berkas hasil berhasil dihapus.');
    }
}
