<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        $query = AppNotification::where('user_id', Auth::id());
        $unread = (clone $query)->whereNull('read_at')->count();

        return view('notifikasi.index', [
            'notifications' => (clone $query)
                ->when($filter === 'unread', fn ($notifications) => $notifications->whereNull('read_at'))
                ->when($filter === 'read', fn ($notifications) => $notifications->whereNotNull('read_at'))
                ->orderByDesc('id')
                ->paginate(30)
                ->withQueryString(),
            'filter' => $filter,
            'unread' => $unread,
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
