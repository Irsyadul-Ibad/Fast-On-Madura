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
        abort_unless(in_array($this->currentUser()->role?->role_code, self::VENDOR_TIANG_ROLE_CODES, true), 403);
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
            ->whereHas('laporanVendor', fn ($q) => $q->whereNotNull('vendor_report_at'))
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
            ->whereHas('laporanVendor', fn ($q) => $q->whereNotNull('vendor_report_at'))
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
        $existingPaths = $this->storedPaths($laporan->berkas_paths);
        $uploadedFiles = $request->file('berkas', []);
        $paths = $this->appendUploadedFiles(
            $existingPaths,
            $uploadedFiles,
            'laporan-vendor',
        );
        $names = array_slice(
            array_pad($this->storedPaths($laporan->berkas_names), count($existingPaths), null),
            0,
            count($existingPaths),
        );
        foreach ($uploadedFiles as $file) {
            $names[] = $file->getClientOriginalName();
        }

        $laporan->fill([
            'vendor_id' => $pelanggan->pengirimanVendor?->vendor_id,
            'pekerjaan_lengkap' => $request->boolean('pekerjaan_lengkap'),
            'pekerjaan_sesuai_wo' => $request->boolean('pekerjaan_sesuai_wo'),
            'foto_terlampir' => $request->boolean('foto_terlampir'),
            'siap_dilanjutkan' => $request->boolean('siap_dilanjutkan'),
            'catatan' => $data['catatan'] ?? null,
            'berkas_paths' => $paths,
            'berkas_names' => $names,
            'dikirim_oleh' => Auth::id(),
            'dikirim_at' => now(),
            'vendor_report_at' => now(),
        ])->save();

        if ($pelanggan->tahap === 'VENDOR_TIANG') {
            $pelanggan->update(['tahap' => 'PERENCANAAN']);
        }
        NotifikasiService::untukData($pelanggan, 'Laporan vendor tiang masuk', 'Vendor tiang telah mengirim laporan untuk '.$pelanggan->no_agenda.'.', route('laporan'));

        return back()->with('success', 'Laporan berhasil dikirim ke Perencanaan.');
    }

    public function wo(PelangganPbpd $pelanggan)
    {
        $roleCode = $this->currentUser()->role?->role_code;
        $isPlanning = $roleCode === '5170REN';
        $isVendor = in_array($roleCode, self::VENDOR_TIANG_ROLE_CODES, true);

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
        $isPlanning = $roleCode === '5170REN';
        $isUp3 = $this->currentUser()->role?->type === 'UP3';
        $isVendor = in_array($roleCode, self::VENDOR_TIANG_ROLE_CODES, true);
        abort_unless($isPlanning || $isUp3 || $isVendor, 403);

        $pengiriman = $pelanggan->pengirimanVendor()->with('vendor')->firstOrFail();
        if ($isVendor) {
            abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        }

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $path = $this->storedPaths($laporan->berkas_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return $this->publicFileResponse($path);
    }

    public function uploadHasil(Request $request, PelangganPbpd $pelanggan)
    {
        abort_unless($this->currentUser()->role?->role_code === '5170REN', 403);

        $data = $request->validate([
            'berkas_hasil.*' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        // Upload hasil Perencanaan tidak perlu menunggu laporan vendor atau
        // record pengiriman vendor. Jika pengiriman sudah ada, gunakan vendor
        // tersebut; jika belum ada, laporan tetap boleh disimpan dengan
        // vendor_id null.
        $pengiriman = $pelanggan->pengirimanVendor()->first();
        $laporan = $pelanggan->laporanVendor()->firstOrNew(['pelanggan_id' => $pelanggan->id]);
        $uploadedFiles = $request->file('berkas_hasil', []);
        $existingPaths = $this->storedPaths($laporan->berkas_hasil_paths);
        $paths = $this->appendUploadedFiles(
            $existingPaths,
            $uploadedFiles,
            'hasil-perencanaan',
        );
        $names = array_slice(
            array_pad($this->storedPaths($laporan->berkas_hasil_names), count($existingPaths), null),
            0,
            count($existingPaths),
        );
        foreach ($uploadedFiles as $file) {
            $names[] = $file->getClientOriginalName();
        }

        $laporan->fill([
            'vendor_id' => $laporan->vendor_id ?? $pengiriman?->vendor_id,
            'berkas_hasil_paths' => $paths,
            'berkas_hasil_names' => $names,
            'berkas_hasil_at' => now(),
        ]);
        if (! $laporan->exists) {
            // Simpan hasil Perencanaan lebih dulu; laporan vendor dapat masuk belakangan.
            $laporan->dikirim_oleh = Auth::id();
            $laporan->dikirim_at = now();
        }
        $laporan->save();
        $pelanggan->tandaiSelesaiJikaLengkap(Auth::id());
        NotifikasiService::untukData($pelanggan, 'Berkas hasil perencanaan diupload', 'Berkas hasil perencanaan untuk '.$pelanggan->no_agenda.' telah diupload.', route('laporan'));

        return back()->with('success', 'Berkas hasil perencanaan berhasil disimpan.');
    }

    public function uploadHasilFromExpansion(Request $request)
    {
        abort_unless($this->currentUser()->role?->role_code === '5170REN', 403);
        $request->validate(['pelanggan_id' => ['required', 'integer', 'exists:pelanggan_pbpd,id']]);

        return $this->uploadHasil($request, PelangganPbpd::findOrFail($request->integer('pelanggan_id')));
    }

    public function hasilFile(PelangganPbpd $pelanggan, int $index)
    {
        $roleCode = $this->currentUser()->role?->role_code;
        $isPlanning = $roleCode === '5170REN';
        $isUp3 = $this->currentUser()->role?->type === 'UP3';
        $isVendor = in_array($roleCode, self::VENDOR_TIANG_ROLE_CODES, true);
        abort_unless($isPlanning || $isUp3 || $isVendor, 403);

        $pengiriman = $pelanggan->pengirimanVendor()->with('vendor')->firstOrFail();
        if ($isVendor) {
            abort_unless($pengiriman->vendor?->user_id === Auth::id(), 403);
        }

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $path = $this->storedPaths($laporan->berkas_hasil_paths)[$index] ?? null;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $name = $this->storedPaths($laporan->berkas_hasil_names)[$index] ?? basename($path);

        return Storage::disk('public')->response($path, basename($name));
    }

    public function hapusHasil(PelangganPbpd $pelanggan, int $index)
    {
        abort_unless($this->currentUser()->role?->role_code === '5170REN', 403);

        $laporan = $pelanggan->laporanVendor()->firstOrFail();
        $paths = $this->storedPaths($laporan->berkas_hasil_paths);
        $names = $this->storedPaths($laporan->berkas_hasil_names);
        $path = $paths[$index] ?? null;
        abort_unless($path, 404);

        Storage::disk('public')->delete($path);
        array_splice($paths, $index, 1);
        array_splice($names, $index, 1);
        $laporan->update([
            'berkas_hasil_paths' => $paths ?: null,
            'berkas_hasil_names' => $names ?: null,
        ]);

        return back()->with('success', 'Berkas hasil berhasil dihapus.');
    }
}
