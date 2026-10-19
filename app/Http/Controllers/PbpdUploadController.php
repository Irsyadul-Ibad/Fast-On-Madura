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
        $request->validate([
    'file'   => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
    'ulp_id' => ['nullable', 'exists:ulp,id'],
]);

        $file = $request->file('file');

        $import = ImportExcel::create([
            'file_name'   => $file->getClientOriginalName(),
            'uploaded_by' => Auth::id(),
        ]);

        // Gunakan ULP pilihan form sebagai cadangan ketika kolom ULP di Excel kosong.
        $proses = new PelangganPbpdImport($import->id, $request->integer('ulp_id') ?: null);
        Excel::import($proses, $file);

        return redirect()->route('pbpd.upload')->with('hasil', [
            'import_id'  => $import->id,
            'baru'       => $proses->baru,
            'diperbarui' => $proses->diperbarui,
            'gagal'      => array_slice($proses->gagal, 0, 20),
            'jml_gagal'  => count($proses->gagal),
        ]);
    }
}
