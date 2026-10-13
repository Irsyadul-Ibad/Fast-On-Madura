<?php

namespace App\Http\Controllers;

use App\Models\PelangganPbpd;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VendorKonstruksiController extends Controller
{
    private function authorizeVendor(): void
    {
        abort_unless(in_array($this->currentUser()->role?->role_code, ['VENDOR_KONSTRUKSI', 'VENDOR_KONSTRUKSI2', '5180K1', '5180K2'], true), 403);
    }

    public function index()
    {
        $this->authorizeVendor();
        $data = PelangganPbpd::with(['ulp', 'pengirimanKonstruksi.vendor', 'pengirimanKonstruksi.dikirimOleh.role'])
            ->statusTampil()
            ->where('tahap', 'VENDOR_KONSTRUKSI')
            ->whereHas('pengirimanKonstruksi', fn ($q) => $q
                ->whereHas('vendor', fn ($v) => $v->where('jenis', 'KONSTRUKSI')->where('user_id', Auth::id()))
                ->whereHas('dikirimOleh.role', fn ($r) => $r->where('role_code', '5180KON'))
            )
            ->orderByDesc('id')->get();

        return view('vendor.konstruksi', ['data' => $data, 'riwayat' => collect(), 'showHistory' => false]);
    }

    public function history()
    {
        $this->authorizeVendor();
        $riwayat = PelangganPbpd::with(['ulp', 'pengirimanKonstruksi.vendor', 'pengirimanKonstruksi.dikirimOleh.role'])
            ->statusTampil()
            ->whereHas('pengirimanKonstruksi', fn ($q) => $q
                ->whereHas('vendor', fn ($v) => $v->where('jenis', 'KONSTRUKSI')->where('user_id', Auth::id()))
                ->whereHas('dikirimOleh.role', fn ($r) => $r->where('role_code', '5180KON'))
                ->whereNotNull('laporan_at')
            )
            ->whereIn('tahap', ['VENDOR_KONSTRUKSI', 'PERENCANAAN', 'KONSTRUKSI'])
            ->orderByDesc('id')->get();

        return view('vendor.konstruksi', ['data' => collect(), 'riwayat' => $riwayat, 'showHistory' => true]);
    }

    public function kirimLaporan(Request $request, PelangganPbpd $pelanggan)
    {
        $this->authorizeVendor();
        $data = $request->validate([
            'pekerjaan_lengkap' => ['nullable', 'boolean'],
            'pekerjaan_sesuai_wo' => ['nullable', 'boolean'],
            'foto_terlampir' => ['nullable', 'boolean'],
            'siap_dilanjutkan' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'berkas_laporan.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $pengiriman = $pelanggan->pengirimanKonstruksi()->with('vendor')->firstOrFail();
        abort_unless($pengiriman->vendor?->jenis === 'KONSTRUKSI', 403);
        abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        $paths = is_array($pengiriman->laporan_paths) ? $pengiriman->laporan_paths : [];
        foreach ($request->file('berkas_laporan', []) as $file) {
            $paths[] = $file->store('laporan-konstruksi', 'public');
        }

        $pengiriman->update([
            'pekerjaan_lengkap' => $request->boolean('pekerjaan_lengkap'),
            'pekerjaan_sesuai_wo' => $request->boolean('pekerjaan_sesuai_wo'),
            'foto_terlampir' => $request->boolean('foto_terlampir'),
            'siap_dilanjutkan' => $request->boolean('siap_dilanjutkan'),
            'catatan' => $data['catatan'] ?? null,
            'laporan_paths' => $paths ?: null,
            'laporan_at' => now(),
        ]);

        $pelanggan->update(['tahap' => 'PERENCANAAN']);
        NotifikasiService::untukData($pelanggan, 'Laporan vendor konstruksi masuk', 'Vendor konstruksi telah mengirim laporan untuk ' . $pelanggan->no_agenda . '.', route('laporan'));
        return back()->with('success', 'Laporan vendor konstruksi berhasil dikirim.');
    }

    public function hapusLaporan(PelangganPbpd $pelanggan)
    {
        $this->authorizeVendor();
        $pengiriman = $pelanggan->pengirimanKonstruksi()->with('vendor')->firstOrFail();
        abort_unless($pengiriman->vendor?->jenis === 'KONSTRUKSI', 403);
        abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);

        foreach ($this->paths($pengiriman->laporan_paths) as $path) {
            Storage::disk('public')->delete($path);
        }
        $pengiriman->update([
            'pekerjaan_lengkap' => null,
            'pekerjaan_sesuai_wo' => null,
            'foto_terlampir' => null,
            'siap_dilanjutkan' => null,
            'catatan' => null,
            'laporan_paths' => null,
            'laporan_at' => null,
        ]);
        $pelanggan->update(['tahap' => 'VENDOR_KONSTRUKSI']);

        return back()->with('success', 'Laporan vendor konstruksi berhasil dihapus.');
    }

    public function uploadHasil(Request $request, PelangganPbpd $pelanggan)
    {
        abort_unless($this->currentUser()->role?->role_code === '5180REN', 403);
        $request->validate([
            'berkas_hasil.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $pengiriman = $pelanggan->pengirimanKonstruksi()->firstOrFail();
        $paths = is_array($pengiriman->hasil_paths) ? $pengiriman->hasil_paths : [];
        foreach ($request->file('berkas_hasil', []) as $file) {
            $paths[] = $file->store('hasil-konstruksi', 'public');
        }
        $pengiriman->update(['hasil_paths' => $paths, 'hasil_perencanaan_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil perencanaan diupload', 'Berkas hasil perencanaan untuk konstruksi ' . $pelanggan->no_agenda . ' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil Perencanaan berhasil disimpan.');
    }

    public function laporanFile(PelangganPbpd $pelanggan, int $index)
    {
        $roleCode = $this->currentUser()->role?->role_code;
        $isPlanning = $roleCode === '5180REN';
        $isConstructionUp3 = $roleCode === '5180KON';
        $isVendor = in_array($roleCode, ['VENDOR_KONSTRUKSI', 'VENDOR_KONSTRUKSI2', '5180K1', '5180K2'], true);
        abort_unless($isPlanning || $isConstructionUp3 || $isVendor, 403);

        $pengiriman = $pelanggan->pengirimanKonstruksi()->with('vendor')->firstOrFail();
        abort_unless($pengiriman->vendor?->jenis === 'KONSTRUKSI', 403);
        if ($isVendor) {
            abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        }

        $path = $this->paths($pengiriman->laporan_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function deliveryFile(PelangganPbpd $pelanggan, int $index)
    {
        $this->authorizeVendor();
        $pengiriman = $pelanggan->pengirimanKonstruksi()->with('vendor')->firstOrFail();
        abort_unless($pengiriman->vendor?->jenis === 'KONSTRUKSI', 403);
        abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);

        $path = $this->paths($pengiriman->berkas_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function uploadHasilKonstruksi(Request $request, PelangganPbpd $pelanggan)
    {
        abort_unless($this->currentUser()->role?->role_code === '5180KON', 403);
        $request->validate([
            'berkas_hasil_konstruksi.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $paths = is_array($pelanggan->hasil_konstruksi_paths) ? $pelanggan->hasil_konstruksi_paths : [];
        foreach ($request->file('berkas_hasil_konstruksi', []) as $file) {
            $paths[] = $file->store('hasil-konstruksi', 'public');
        }
        $pelanggan->update(['hasil_konstruksi_paths' => $paths, 'hasil_konstruksi_at' => now()]);
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil konstruksi diupload', 'Berkas hasil konstruksi untuk ' . $pelanggan->no_agenda . ' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil konstruksi berhasil disimpan.');
    }

    public function hasilKonstruksiFile(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->type === 'UP3', 403);
        $path = (is_array($pelanggan->hasil_konstruksi_paths) ? $pelanggan->hasil_konstruksi_paths : [])[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function hapusHasilKonstruksi(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->role_code === '5180KON', 403);

        $paths = is_array($pelanggan->hasil_konstruksi_paths) ? $pelanggan->hasil_konstruksi_paths : [];
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        $pelanggan->update(['hasil_konstruksi_paths' => $paths ?: null]);

        return back()->with('success', 'Berkas BA Checklist berhasil dihapus.');
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
}
