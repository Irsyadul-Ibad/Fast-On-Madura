<?php

namespace App\Http\Controllers;

use App\Imports\PelangganPbpdImport;
use App\Models\ImportExcel;
use App\Models\Ulp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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

        $import = ImportExcel::create([
            'file_name'   => $file->getClientOriginalName(),
            'uploaded_by' => Auth::id(),
        ]);

        // Gunakan ULP pilihan form sebagai cadangan ketika kolom ULP di Excel kosong.
        $proses = new PelangganPbpdImport(
            $import->id,
            $request->integer('ulp_id') ?: null,
            $csvDelimiter,
        );
        // File gabungan seluruh ULP dapat berisi ribuan baris. Beri waktu
        // cukup untuk proses import setelah pembacaan sudah dibuat chunked.
        set_time_limit(900);
        Excel::import($proses, $file);

        return redirect()->route('pbpd.upload')->with('hasil', [
            'import_id'  => $import->id,
            'baru'       => $proses->baru,
            'diperbarui' => $proses->diperbarui,
            'gagal'      => array_slice($proses->gagal, 0, 20),
            'jml_gagal'  => count($proses->gagal),
        ]);
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
