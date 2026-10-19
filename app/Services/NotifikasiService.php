<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\PelangganPbpd;
use App\Models\User;

class NotifikasiService
{
    public static function untukData(PelangganPbpd $pelanggan, string $title, string $message, ?string $url = null, array $tambahanUserIds = []): void
    {
        $query = User::where('id', '!=', auth()->id())->whereHas('role', function ($role) use ($pelanggan) {
            $role->where(function ($scope) use ($pelanggan) {
                $scope->where('type', 'UP3')->orWhere('ulp_id', $pelanggan->ulp_id);
            });
        });

        self::kirim($query->pluck('id')->merge($tambahanUserIds)->unique()->all(), $pelanggan, $title, $message, $url);
    }

    public static function untukRole(PelangganPbpd $pelanggan, array $roleCodes, string $title, string $message, ?string $url = null): void
    {
        $ids = User::where('id', '!=', auth()->id())->whereHas('role', fn ($role) => $role->whereIn('role_code', $roleCodes))->pluck('id')->all();
        self::kirim($ids, $pelanggan, $title, $message, $url);
    }

    private static function kirim(array $userIds, PelangganPbpd $pelanggan, string $title, string $message, ?string $url): void
    {
        $url = self::relativeUrl($url);

        foreach (array_unique(array_filter(array_map('intval', $userIds))) as $userId) {
            AppNotification::create([
                'user_id' => $userId,
                'pelanggan_id' => $pelanggan->id,
                'title' => $title,
                'message' => $message,
                'url' => $url,
            ]);
        }
    }

    private static function relativeUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        $path = is_array($parts) ? ($parts['path'] ?? '/') : '/';

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return '/laporan';
        }

        return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }
}
