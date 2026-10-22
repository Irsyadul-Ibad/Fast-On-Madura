@extends('layouts.app')
@section('title', 'Notifikasi')
@section('judul', 'Notifikasi')
@section('isi')
<div class="box notif-box">
    <div class="notif-head"><h2>Notifikasi</h2><form method="POST" action="{{ route('notifikasi.read-all') }}">@csrf<button type="submit">Tandai semua dibaca</button></form></div>
    @forelse ($notifications as $notification)
        <form method="POST" action="{{ route('notifikasi.read', $notification) }}">@csrf<button type="submit" class="notif-item {{ $notification->read_at ? 'read' : 'unread' }}">
            <strong>{{ $notification->title }}</strong><span>{{ $notification->message }}</span><small>{{ $notification->created_at?->format('d/m/Y H:i') }}</small>
        </button></form>
    @empty <p>Belum ada notifikasi.</p> @endforelse
    <div class="halaman">{{ $notifications->links() }}</div>
</div>
@push('page-styles')
<style>
    .notif-box{padding:18px!important;background:#f8fbff!important}
    .notif-head{display:flex;justify-content:space-between;align-items:center;gap:14px;margin:-18px -18px 16px;padding:14px 16px;background:#111c91!important;border-radius:14px 14px 0 0!important}
    .notif-head h2{margin:0;color:#fff!important;font-size:18px;line-height:1.3}
    .notif-head button{min-height:36px;padding:8px 14px;background:#1e6fa8!important;color:#fff!important;border:0;border-radius:8px!important;font-size:12px;font-weight:700;white-space:nowrap}
    .notif-head button:hover{background:#123b5d!important;color:#fff!important}
    .notif-box form:has(.notif-item){margin:0}
    .notif-item{display:grid;gap:5px;width:100%;margin-top:10px;padding:14px 16px;border:1px solid #d9e2ec!important;border-left:4px solid #cbd5e1!important;border-radius:10px!important;color:#243b53!important;text-align:left;background:#fff!important;box-shadow:0 2px 8px rgba(18,59,93,.06)!important;transition:transform .18s,box-shadow .18s,border-color .18s}
    .notif-item:hover{transform:translateY(-1px);border-color:#9fb4ca!important;box-shadow:0 5px 14px rgba(18,59,93,.1)!important}
    .notif-item.unread{border-left-color:#facc15!important;background:#fffaf0!important}
    .notif-item.read{opacity:.78;background:#f8fafc!important}
    .notif-item strong{color:#123b5d!important;font-size:13px;line-height:1.35}
    .notif-item span{color:#243b53!important;font-size:13px;line-height:1.5}
    .notif-item small{color:#627d98!important;font-size:11px;line-height:1.3}
    .halaman{margin-top:16px}
    @media(max-width:600px){.notif-head{align-items:flex-start;flex-direction:column}.notif-head button{width:100%}.notif-item{padding:12px}}
</style>
@endpush
@endsection
