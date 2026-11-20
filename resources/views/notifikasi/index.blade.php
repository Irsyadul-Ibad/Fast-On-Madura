@extends('layouts.app')

@php
    $notificationRole = auth()->user()->role;
    $notificationScope = match ($notificationRole?->type) {
        'ULP' => 'ULP '.($notificationRole->ulp?->nama ?? 'Anda'),
        'UP3' => 'UP3 Madura',
        default => 'wilayah kerja Anda',
    };
    $notificationGroups = $notifications->getCollection()->groupBy(
        fn ($notification) => $notification->created_at?->toDateString() ?? 'unknown'
    );
@endphp

@section('title', 'Notifikasi')
@section('judul', 'Notifikasi')
@section('deskripsi', 'Pembaruan proses PB/PD di '.$notificationScope)
@section('header-actions')
    <form method="POST" action="{{ route('notifikasi.read-all') }}" class="notification-mark-all-form">
        @csrf
        <button type="submit" class="notification-mark-all">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 12 5 5L20 5"/><path d="m10 12 5 5L24 8" transform="translate(-2 -1)"/></svg>
            Tandai semua dibaca
        </button>
    </form>
@endsection

@section('isi')
    <div class="notification-page">
        @if (session('success'))
            <div class="notification-success" role="status">{{ session('success') }}</div>
        @endif

        <nav class="notification-tabs" aria-label="Filter notifikasi">
            <a href="{{ route('notifikasi', ['filter' => 'all']) }}"
               class="notification-tab {{ $filter === 'all' ? 'is-active' : '' }}"
               @if ($filter === 'all') aria-current="page" @endif>
                Semua
            </a>
            <a href="{{ route('notifikasi', ['filter' => 'unread']) }}"
               class="notification-tab {{ $filter === 'unread' ? 'is-active' : '' }}"
               @if ($filter === 'unread') aria-current="page" @endif>
                Belum dibaca <span>· {{ number_format($unread, 0, ',', '.') }}</span>
            </a>
            <a href="{{ route('notifikasi', ['filter' => 'read']) }}"
               class="notification-tab {{ $filter === 'read' ? 'is-active' : '' }}"
               @if ($filter === 'read') aria-current="page" @endif>
                Sudah dibaca
            </a>
        </nav>

        <section class="notification-board" aria-label="Daftar notifikasi">
            @forelse ($notificationGroups as $date => $group)
                <div class="notification-group">
                    <h2 class="notification-date">
                        {{ $date === 'unknown' ? 'Tanggal tidak tersedia' : \Illuminate\Support\Carbon::parse($date)->locale('id')->translatedFormat('d F Y') }}
                    </h2>

                    <div class="notification-list">
                        @foreach ($group as $notification)
                            @php
                                $titleForIcon = strtolower($notification->title);
                                $messageSegments = preg_split('/(\d{8,})/', $notification->message, -1, PREG_SPLIT_DELIM_CAPTURE)
                                    ?: [$notification->message];
                                $notificationType = match (true) {
                                    str_contains($titleForIcon, 'layak') => 'approved',
                                    str_contains($titleForIcon, 'dikembalikan') => 'returned',
                                    str_contains($titleForIcon, 'kirim') => 'sent',
                                    str_contains($titleForIcon, 'laporan'), str_contains($titleForIcon, 'berkas') => 'document',
                                    default => 'default',
                                };
                            @endphp

                            <form method="POST" action="{{ route('notifikasi.read', $notification) }}" class="notification-form">
                                @csrf
                                <button type="submit" class="notification-card {{ $notification->read_at ? 'is-read' : 'is-unread' }}">
                                    <span class="notification-icon notification-icon--{{ $notificationType }}" aria-hidden="true">
                                        @switch($notificationType)
                                            @case('sent')
                                                <svg viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>
                                                @break
                                            @case('approved')
                                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                                                @break
                                            @case('returned')
                                                <svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M4 9h9a7 7 0 0 1 0 14h-2"/></svg>
                                                @break
                                            @case('document')
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></svg>
                                                @break
                                            @default
                                                <svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                        @endswitch
                                    </span>
                                    <span class="notification-copy">
                                        <strong>{{ $notification->title }}</strong>
                                        <span>
                                            @foreach ($messageSegments as $segment)
                                                @if (ctype_digit($segment) && strlen($segment) >= 8)
                                                    <strong class="notification-agenda">{{ $segment }}</strong>
                                                @else
                                                    {{ $segment }}
                                                @endif
                                            @endforeach
                                        </span>
                                    </span>
                                    <time class="notification-time" datetime="{{ $notification->created_at?->toIso8601String() }}">
                                        {{ $notification->created_at?->format('H:i') ?? '—' }}
                                    </time>
                                    @if (! $notification->read_at)
                                        <span class="notification-unread-dot" role="img" aria-label="Belum dibaca"></span>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="notification-empty">
                    <span class="notification-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                    </span>
                    <strong>
                        {{ $filter === 'unread' ? 'Semua notifikasi sudah dibaca' : ($filter === 'read' ? 'Belum ada notifikasi yang dibaca' : 'Belum ada notifikasi') }}
                    </strong>
                    <span>Pembaruan proses PB/PD akan muncul di halaman ini.</span>
                </div>
            @endforelse

            @if ($notifications->hasPages())
                <div class="notification-pagination">{{ $notifications->links() }}</div>
            @endif
        </section>
    </div>
@endsection

@push('page-styles')
<style>
    .page-actions .notification-mark-all-form{margin:0}
    .page-actions .notification-mark-all{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:44px;padding:9px 18px!important;border:1px solid #cbd8ff!important;border-radius:14px!important;background:#fff!important;color:#102cae!important;font-size:14px!important;font-weight:750!important;box-shadow:none!important;white-space:nowrap}
    .page-actions .notification-mark-all:hover{background:#f5f7ff!important;border-color:#aebfff!important;color:#102cae!important;box-shadow:none!important;transform:none!important}
    .page-actions .notification-mark-all svg{display:block;width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .main-content.main-notification{padding-top:0!important}
    .notification-page{display:grid;gap:16px;min-width:0}
    .notification-tabs{display:flex;align-items:center;flex-wrap:wrap;gap:8px}
    .notification-tab{display:inline-flex;align-items:center;gap:4px;min-height:40px;padding:8px 16px;border:1px solid transparent;border-radius:14px;background:#fff;color:#344a81;font-size:13px;font-weight:700;line-height:1.25;text-decoration:none;transition:background-color .18s ease,color .18s ease,box-shadow .18s ease}
    .notification-tab:hover{background:#f5f7ff;color:#214fe0}
    .notification-tab.is-active{background:#214fe0;color:#fff;box-shadow:0 8px 18px rgba(33,78,224,.18)}
    .notification-board{min-width:0;padding:24px 20px;border:0;border-radius:26px;background:#fff;box-shadow:0 12px 30px rgba(50,72,134,.07)}
    .notification-group+.notification-group{margin-top:24px}
    .notification-date{margin:0 0 10px 4px;color:#5e719e;font-size:12px;font-weight:800;letter-spacing:.14em;line-height:1.4;text-transform:uppercase}
    .notification-list{display:grid;gap:8px}
    .notification-form{margin:0}
    .notification-card{display:grid;grid-template-columns:46px minmax(0,1fr) auto 8px;align-items:center;gap:16px;width:100%;min-height:76px;margin:0;padding:14px 16px;border:1px solid #e0e7f8!important;border-radius:17px!important;background:#f8faff!important;color:#263867!important;text-align:left;box-shadow:none!important;transition:background-color .18s ease,border-color .18s ease,transform .18s ease}
    .notification-card:hover{background:#f3f6ff!important;border-color:#cbd8ff!important;box-shadow:0 5px 14px rgba(50,72,134,.08)!important;transform:translateY(-1px)}
    .notification-card:focus-visible{outline:3px solid rgba(33,78,224,.24);outline-offset:2px}
    .notification-card.is-read{background:#fff!important}
    .notification-icon{display:grid;place-items:center;width:46px;height:46px;border-radius:14px;background:#e4ebff;color:#214fe0}
    .notification-icon svg,.notification-empty-icon svg{display:block;width:21px;height:21px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
    .notification-icon--approved{background:#dcf4e8;color:#197847}
    .notification-icon--returned{background:#fde6e5;color:#c1352b}
    .notification-icon--document{background:#fff1c9;color:#9a6500}
    .notification-copy{display:grid;gap:3px;min-width:0}
    .notification-copy strong{color:#102cae;font-size:14px;font-weight:800;line-height:1.35}
    .notification-copy>span{color:#344a81;font-size:13px;font-weight:500;line-height:1.45;overflow-wrap:anywhere}
    .notification-copy .notification-agenda{color:#102cae;font-weight:800}
    .notification-time{color:#5e719e;font-size:12px;font-weight:600;white-space:nowrap}
    .notification-unread-dot{display:block;width:9px;height:9px;border-radius:50%;background:#214fe0}
    .notification-empty{display:grid;justify-items:center;gap:8px;padding:48px 16px;color:#5e719e;text-align:center}
    .notification-empty-icon{display:grid;place-items:center;width:48px;height:48px;margin-bottom:4px;border-radius:15px;background:#eef2ff;color:#214fe0}
    .notification-empty strong{color:#102cae;font-size:15px}
    .notification-empty>span:last-child{font-size:13px}
    .notification-success{padding:12px 16px;border:1px solid #bfe8d0;border-radius:14px;background:#edf9f2;color:#176b3d;font-size:13px;font-weight:650}
    .notification-pagination{display:flex;justify-content:flex-end;margin-top:18px;padding-top:16px;border-top:1px solid #e8edf8}
    .notification-pagination nav{margin:0}
    @media(max-width:650px){
        body.notification-screen .content>header.page-topbar{align-items:flex-start;flex-wrap:wrap;gap:10px;min-height:0}
        body.notification-screen .content>header.page-topbar .header-title{flex:1 1 100%;align-items:center}
        body.notification-screen .content>header.page-topbar .page-actions{width:100%;justify-content:flex-start}
        body.notification-screen .notification-mark-all-form,body.notification-screen .page-actions .notification-mark-all{width:100%}
        .page-actions .notification-mark-all{min-height:40px;padding:8px 11px!important;font-size:12px!important}
        .notification-page{gap:12px}
        .notification-tabs{gap:6px}
        .notification-tab{min-height:38px;padding:8px 11px;font-size:12px}
        .notification-board{padding:16px 12px;border-radius:20px}
        .notification-group+.notification-group{margin-top:20px}
        .notification-card{grid-template-columns:42px minmax(0,1fr) 8px;gap:8px 11px;min-height:0;padding:12px}
        .notification-icon{grid-column:1;grid-row:1 / span 2;width:42px;height:42px;border-radius:13px}
        .notification-copy{grid-column:2;grid-row:1}
        .notification-copy strong{font-size:13px}
        .notification-copy>span{font-size:12px}
        .notification-time{grid-column:2;grid-row:2}
        .notification-unread-dot{grid-column:3;grid-row:1;align-self:start;margin-top:4px}
        .notification-pagination{justify-content:flex-start}
    }
</style>
@endpush
