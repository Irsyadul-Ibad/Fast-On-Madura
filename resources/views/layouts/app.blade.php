<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FAST ON')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/faston-favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials._styles')
</head>
<body @class([
    'min-h-screen bg-[#eaf0fc] font-[Inter] text-[#1e315f]',
    'dashboard-page' => request()->routeIs('dashboard'),
    'notification-screen' => request()->routeIs('notifikasi'),
])>
<div class="min-h-screen">
    <button type="button" id="sidebarBackdrop" class="fixed inset-0 z-40 hidden cursor-default border-0 bg-[#172554]/35 p-0 backdrop-blur-sm hover:bg-[#172554]/35 hover:shadow-none hover:transform-none" aria-label="Tutup menu" aria-hidden="true" tabindex="-1"></button>
    @include('layouts.partials._sidebar')

    <div class="content ml-[312px] flex w-[calc(100%-312px)] min-w-0 flex-col min-[651px]:max-[900px]:ml-[278px] min-[651px]:max-[900px]:w-[calc(100%-278px)] max-[650px]:ml-0 max-[650px]:w-full">
        <header class="page-topbar">
            <div class="header-title">
                @unless (request()->routeIs('dashboard'))
                    <button type="button" class="menu-toggle hidden h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[10px] border border-[#0d1b8c] bg-[#0d1b8c] p-0 text-white shadow-[0_3px_8px_rgba(13,27,140,0.18)] hover:transform-none hover:border-[#091267] hover:bg-[#091267] hover:shadow-[0_4px_10px_rgba(13,27,140,0.24)] focus-visible:outline focus-visible:outline-3 focus-visible:outline-[#2b73fe]/30 focus-visible:outline-offset-2 max-[650px]:inline-flex" id="menuToggle" aria-label="Buka menu" aria-controls="mainSidebar" aria-expanded="false"><svg class="size-[18px] fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:2]" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                @endunless
                <div class="page-heading-copy">
                    <strong>@yield('judul', 'FAST ON')</strong>
                    @hasSection('deskripsi')
                        <span class="page-description">@yield('deskripsi')</span>
                    @endif
                </div>
            </div>
            <div class="page-actions">@yield('header-actions')</div>
        </header>
        <main @class([
            'main-content',
            'main-dashboard' => request()->routeIs('dashboard'),
            'main-pbpd' => request()->routeIs('pbpd.index'),
            'main-laporan' => request()->routeIs('laporan'),
            'main-notification' => request()->routeIs('notifikasi'),
            'main-expansion' => request()->routeIs('perluasan.jtm', 'perluasan.jtr', 'tanpa.perluasan'),
        ])>
            @if (session('error'))
                <div class="global-flash-error" role="alert">{{ session('error') }}</div>
            @endif
            @yield('isi')
        </main>
    </div>
</div>

@include('layouts.partials._scripts')
</body>
</html>
