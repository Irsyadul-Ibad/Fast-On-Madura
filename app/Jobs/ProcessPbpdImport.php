<?php

namespace App\Jobs;

use App\Imports\PelangganPbpdImport;
use App\Models\ImportExcel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessPbpdImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 1;

    public function __construct(
        public int $importId,
        public ?int $ulpDefault,
        public string $csvDelimiter = ',',
    ) {}

    public function handle(): void
    {
        $import = ImportExcel::findOrFail($this->importId);
        $path = $import->file_path;

        $import->update(['status' => 'processing', 'error_message' => null]);

        try {
            if (! $path || ! Storage::disk('local')->exists($path)) {
                throw new \RuntimeException('File upload tidak ditemukan di penyimpanan server.');
            }

            $process = new PelangganPbpdImport(
                $import->id,
                $this->ulpDefault,
                $this->csvDelimiter,
            );

            Excel::import($process, Storage::disk('local')->path($path));

            $import->update([
                'status' => 'completed',
                'baru' => $process->baru,
                'diperbarui' => $process->diperbarui,
                'jml_gagal' => count($process->gagal),
                'failed_rows' => array_slice($process->gagal, 0, 100),
                'error_message' => null,
            ]);

            Storage::disk('local')->delete($path);
        } catch (Throwable $exception) {
            $import->update([
                'status' => 'failed',
                'error_message' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        ImportExcel::whereKey($this->importId)->update([
            'status' => 'failed',
            'error_message' => mb_substr($exception?->getMessage() ?? 'Proses import gagal.', 0, 2000),
        ]);
    }
}
