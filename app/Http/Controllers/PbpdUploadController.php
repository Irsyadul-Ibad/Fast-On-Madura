<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPbpdImport;
use App\Models\ImportExcel;
use App\Models\Ulp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PbpdUploadController extends Controller
{
    public function index()
    {
        return view('pbpd.upload', [
            'riwayat' => ImportExcel::orderByDesc('id')->limit(15)->get(),
            'ulps'    => Ulp::orderBy('kode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $file = $request->file('file');
        $extension = strtolower((string) $file?->getClientOriginalExtension());

        // Beberapa CSV yang diekspor dari Excel terbaca sebagai text/plain,
        // walaupun ekstensi dan isinya benar-benar CSV. Validasi CSV memakai
        // MIME text umum yang lazim, sedangkan file Excel tetap divalidasi
        // berdasarkan MIME Excel dan ekstensi aslinya.
        $fileRules = ['required', 'file', 'extensions:xlsx,xls,csv', 'max:20480'];
        $fileRules[] = $extension === 'csv'
            ? 'mimetypes:text/plain,text/csv,application/csv,text/x-csv,text/x-comma-separated-values'
            : 'mimes:xlsx,xls';

        $request->validate([
            'file'   => $fileRules,
            'ulp_id' => ['nullable', 'exists:ulp,id'],
        ]);

        $csvDelimiter = $extension === 'csv'
            ? $this->detectCsvDelimiter($file->getRealPath())
            : ',';

        $path = $file->store('pbpd-imports', 'local');

        if (! $path) {
            return back()->withErrors(['file' => 'File gagal disimpan. Silakan coba lagi.']);
        }

        $import = ImportExcel::create([
            'file_name'   => $file->getClientOriginalName(),
            'uploaded_by' => Auth::id(),
            'status'      => 'queued',
            'file_path'   => $path,
        ]);

        try {
            ProcessPbpdImport::dispatch(
                $import->id,
                $request->integer('ulp_id') ?: null,
                $csvDelimiter,
            );
        } catch (Throwable $exception) {
            $import->update([
                'status' => 'failed',
                'error_message' => mb_substr($exception->getMessage(), 0, 2000),
            ]);
            Storage::disk('local')->delete($path);

            return back()->withErrors(['file' => 'File tersimpan, tetapi gagal masuk antrean. Silakan coba lagi.']);
        }

        return redirect()->route('pbpd.upload')
            ->with('queued', 'File berhasil diunggah dan sedang menunggu diproses. Anda tidak perlu menunggu di halaman ini.');
    }

    private function detectCsvDelimiter(?string $path): string
    {
        if (! $path || ! is_readable($path)) {
            return ',';
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ',';
        }

        $header = fgets($handle);
        fclose($handle);

        if ($header === false) {
            return ',';
        }

        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ([';', ',', "\t", '|'] as $delimiter) {
            $count = substr_count($header, $delimiter);
            if ($count > $bestCount) {
                $bestDelimiter = $delimiter;
                $bestCount = $count;
            }
        }

        return $bestDelimiter;
    }
}
