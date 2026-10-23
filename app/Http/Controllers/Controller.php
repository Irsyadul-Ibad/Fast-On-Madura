<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Controller
{
    protected const VENDOR_TIANG_ROLE_CODES = [
        'VENDOR_TIANG', 'VENDOR_TIANG2', '5170T1', '5170T2', '5170T3', '5170T4',
        '5170T5', '5170T6', '5170T7', '5170T8', '5170T9',
    ];

    protected const VENDOR_KONSTRUKSI_ROLE_CODES = [
        'VENDOR_KONSTRUKSI', 'VENDOR_KONSTRUKSI2', '5170K1', '5170K2', '5170K3',
        '5170K4', '5170K5', '5170K6', '5170K7',
    ];

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    protected function publicFileResponse(string $path): StreamedResponse
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->response($path);
    }

    protected function arrayPaths(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function storedPaths(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [$value];
        }

        return [];
    }

    /** @param iterable<UploadedFile> $files */
    protected function appendUploadedFiles(array $paths, iterable $files, string $directory): array
    {
        foreach ($files as $file) {
            $paths[] = $file->store($directory, 'public');
        }

        return $paths;
    }
}
