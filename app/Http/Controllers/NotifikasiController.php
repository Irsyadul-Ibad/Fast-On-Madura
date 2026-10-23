<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        return view('notifikasi.index', [
            'notifications' => AppNotification::where('user_id', Auth::id())->orderByDesc('id')->paginate(30),
            'unread' => AppNotification::where('user_id', Auth::id())->whereNull('read_at')->count(),
        ]);
    }

    public function read(AppNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) Auth::id(), 403);
        $notification->update(['read_at' => now()]);

        if (! $notification->url) {
            return back();
        }

        // Older notifications may contain absolute localhost/tunnel URLs.
        // Keep only the local path so navigation stays on the current host.
        $parts = parse_url($notification->url);
        $path = is_array($parts) ? ($parts['path'] ?? null) : null;

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            $path = route('laporan', [], false);
        }

        $relativeUrl = $path.(isset($parts['query']) ? '?'.$parts['query'] : '');

        return redirect($relativeUrl);
    }

    public function readAll()
    {
        AppNotification::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi sudah dibaca.');
    }
}
