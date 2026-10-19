<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\PengirimanVendor;
use App\Models\User;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;

class CheckVendorTiangDeadlines extends Command
{
    protected $signature = 'vendor-tiang:check-deadlines';

    protected $description = 'Kirim pengingat ke Perencanaan untuk laporan vendor tiang yang terlambat';

    public function handle(): int
    {
        $deadlineSeconds = max(1, (int) config('services.vendor_tiang_deadline_seconds', 259200));
        $cutoff = now()->subSeconds($deadlineSeconds);
        $planningRoleCodes = ['5180REN'];
        $sent = 0;

        PengirimanVendor::query()
            ->with('pelanggan')
            ->whereNotNull('dikirim_at')
            ->where('dikirim_at', '<=', $cutoff)
            ->whereHas('pelanggan', fn ($query) => $query->where('tahap', 'VENDOR_TIANG'))
            ->whereDoesntHave('pelanggan.laporanVendor')
            ->orderBy('dikirim_at')
            ->chunkById(100, function ($deliveries) use ($planningRoleCodes, &$sent) {
                foreach ($deliveries as $delivery) {
                    $pelanggan = $delivery->pelanggan;
                    if (! $pelanggan) {
                        continue;
                    }

                    $title = 'Vendor tiang terlambat mengirim laporan';
                    $message = sprintf(
                        'Vendor tiang belum mengirim laporan untuk agenda %s sejak pengiriman %s dan telah melewati batas waktu.',
                        $pelanggan->no_agenda,
                        $delivery->dikirim_at->format('Y-m-d H:i:s')
                    );

                    $alreadyNotified = AppNotification::query()
                        ->where('pelanggan_id', $pelanggan->id)
                        ->where('title', $title)
                        ->where('message', $message)
                        ->exists();

                    if ($alreadyNotified || ! User::whereHas('role', fn ($query) => $query->whereIn('role_code', $planningRoleCodes))->exists()) {
                        continue;
                    }

                    NotifikasiService::untukRole(
                        $pelanggan,
                        $planningRoleCodes,
                        $title,
                        $message,
                        route('laporan')
                    );
                    $sent++;
                }
            });

        $this->info("Pengingat keterlambatan dikirim: {$sent}.");

        return self::SUCCESS;
    }
}
