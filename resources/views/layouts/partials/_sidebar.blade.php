    <aside id="mainSidebar" class="fixed inset-y-4 left-4 z-50 flex h-auto max-h-[calc(100vh-32px)] w-[280px] -translate-x-[calc(100%+24px)] flex-col overflow-x-hidden overflow-y-auto rounded-[28px] border border-[#edf1fb] bg-white p-[20px_14px_14px] text-[#16336f] shadow-[0_14px_38px_rgba(44,65,127,0.12)] transition-transform duration-300 ease-in-out min-[651px]:translate-x-0 min-[651px]:max-[900px]:inset-y-3 min-[651px]:max-[900px]:left-3 min-[651px]:max-[900px]:max-h-[calc(100vh-24px)] min-[651px]:max-[900px]:w-[254px] min-[651px]:max-[900px]:p-[14px_11px_12px] max-[650px]:inset-y-3 max-[650px]:left-3 max-[650px]:max-h-[calc(100vh-24px)] max-[650px]:w-[min(300px,calc(100vw-24px))] max-[650px]:rounded-[22px] max-[650px]:p-[12px_11px_11px]">
        @php
            $sidebarUser = auth()->user();
            $sidebarUser->loadMissing(['role.permissions', 'role.ulp']);
            $sidebarRole = $sidebarUser->role;
            $sidebarRoleCode = (string) $sidebarRole?->role_code;
            $sidebarIsVendorRole = $sidebarRole?->type === 'VENDOR' || str_starts_with($sidebarRoleCode, 'VENDOR');
            $sidebarPermissions = $sidebarUser->effectivePermissions();
            $sidebarUnreadCount = \App\Models\AppNotification::where('user_id', $sidebarUser->id)->whereNull('read_at')->count();
            $sidebarMenuByRoute = collect(config('menu'))
                ->filter(fn ($item) => in_array($item['permission'], $sidebarPermissions, true)
                    && ! ($sidebarIsVendorRole && $item['route'] === 'laporan'))
                ->keyBy('route');
            $sidebarGroups = [
                ['title' => 'UTAMA', 'routes' => ['dashboard', 'laporan', 'pbpd.index', 'pbpd.upload']],
                ['title' => 'PERLUASAN', 'routes' => ['perluasan.jtm', 'perluasan.jtr', 'tanpa.perluasan']],
                ['title' => 'OPERASIONAL', 'routes' => ['vendor.tiang', 'stok.tiang', 'vendor.tiang.history', 'vendor.konstruksi', 'vendor.konstruksi.history', 'pengoperasian']],
                ['title' => 'ALAT BANTU', 'routes' => ['pencarian', 'notifikasi']],
            ];
            $sidebarUlpName = $sidebarRole?->ulp?->nama;
            $sidebarUnitLabel = $sidebarUlpName
                ? 'ULP '.$sidebarUlpName
                : ($sidebarIsVendorRole ? 'MITRA VENDOR' : 'UP3 MADURA');
            $sidebarProfileName = $sidebarUlpName
                ? 'ULP '.$sidebarUlpName
                : ($sidebarRole?->name ?? $sidebarUser->user_id);
            $sidebarInitials = strtoupper(substr(trim($sidebarProfileName), 0, 2));

            $sidebarProgressQuery = \App\Models\PelangganPbpd::query()->statusTampil();
            if ($sidebarRole?->ulp_id) {
                $sidebarProgressQuery->where('ulp_id', $sidebarRole->ulp_id);
            } elseif ($sidebarIsVendorRole) {
                $sidebarProgressQuery->where(function ($query) use ($sidebarUser) {
                    $query->whereHas('pengirimanVendor.vendor', fn ($vendor) => $vendor->where('user_id', $sidebarUser->id))
                        ->orWhereHas('pengirimanKonstruksi.vendor', fn ($vendor) => $vendor->where('user_id', $sidebarUser->id));
                });
            } elseif (! in_array('pbpd.view', $sidebarPermissions, true)) {
                $sidebarProgressQuery->whereRaw('1 = 0');
            }
            $sidebarTotal = (clone $sidebarProgressQuery)->count();
            if ($sidebarIsVendorRole) {
                $sidebarProgressCount = (clone $sidebarProgressQuery)->where(function ($query) {
                    $query->whereHas('laporanVendor', fn ($report) => $report->whereNotNull('vendor_report_at'))
                        ->orWhereHas('pengirimanKonstruksi', fn ($report) => $report->whereNotNull('laporan_at'));
                })->count();
                $sidebarProgressTitle = 'Agenda vendor';
                $sidebarProgressCaption = 'dari '.$sidebarTotal.' agenda selesai';
            } else {
                $sidebarProgressCount = (clone $sidebarProgressQuery)->whereNotNull('tahap')->where('tahap', '!=', 'ULP')->count();
                $sidebarProgressTitle = $sidebarRole?->ulp_id ? 'Progres pengiriman ULP' : 'Data dalam alur proses';
                $sidebarProgressCaption = 'dari '.$sidebarTotal.' data '.($sidebarRole?->ulp_id ? 'terkirim' : 'diproses');
            }
            $sidebarProgressPercent = $sidebarTotal > 0
                ? min(100, (int) round(($sidebarProgressCount / $sidebarTotal) * 100))
                : 0;
            $sidebarCanTrackProgress = in_array('pbpd.view', $sidebarPermissions, true) || $sidebarIsVendorRole;
        @endphp

        <div class="sidebar-brand mb-[30px] ml-[7px] flex items-center gap-[13px] px-0.5">
            <img class="h-[52px] w-8 shrink-0 object-contain" src="{{ asset('images/faston-favicon.png') }}" alt="Logo FAST ON">
            <div class="sidebar-brand-copy flex min-w-0 flex-col gap-0.5">
                <span class="sidebar-brand-title text-[19px] font-extrabold tracking-[-.035em] text-[#102cae]">FAST ON 360</span>
                <span class="sidebar-brand-unit text-[11px] font-bold uppercase tracking-[.13em] text-[#5e719e]">{{ $sidebarUnitLabel }}</span>
            </div>
        </div>

        <nav class="sidebar-nav flex min-h-20 flex-1 flex-col gap-[22px] px-px" aria-label="Navigasi utama">
            @foreach ($sidebarGroups as $group)
                @php
                    $groupItems = collect($group['routes'])
                        ->map(fn ($routeName) => $sidebarMenuByRoute->get($routeName))
                        ->filter();
                @endphp
                @if ($groupItems->isNotEmpty())
                    <div class="sidebar-group flex flex-col gap-1.5">
                        <div class="sidebar-group-title mx-[10px] mb-[7px] text-[11px] font-extrabold uppercase leading-[1.2] tracking-[.16em] text-[#5e719e]">{{ $group['title'] }}</div>
                        @foreach ($groupItems as $m)
                            @php($sidebarItemIsActive = request()->routeIs($m['route']))
                            <a href="{{ route($m['route']) }}"
                               @class([
                                   'active' => $sidebarItemIsActive,
                                   'group relative flex min-h-12 w-full items-center gap-3 rounded-[14px] border px-[10px] py-[7px] text-sm leading-[1.25] no-underline transition-colors duration-200',
                                   'border-transparent bg-transparent font-semibold text-[#29436f] hover:border-[#e8edfb] hover:bg-[#f3f6ff] hover:text-[#133b9a] focus-visible:border-[#e8edfb] focus-visible:bg-[#f3f6ff] focus-visible:text-[#133b9a]' => ! $sidebarItemIsActive,
                                   'border-transparent bg-[linear-gradient(110deg,#214ee0,#1236b6)] font-bold !text-white shadow-[0_8px_16px_rgba(28,64,181,0.22)]' => $sidebarItemIsActive,
                               ])
                               @if ($sidebarItemIsActive) aria-current="page" @endif>
                        <span @class([
                            'sidebar-icon flex size-8 shrink-0 items-center justify-center rounded-[10px] transition-colors',
                            'bg-white/20 !text-white' => $sidebarItemIsActive,
                            'bg-[#eef2ff] text-[#2853c5]' => ! $sidebarItemIsActive,
                        ]) aria-hidden="true">
                            @switch($m['route'])
                                @case('dashboard')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m3 10 9-7 9 7"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/></svg>
                                    @break
                                @case('pbpd.index')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3.5" y="3.5" width="7" height="7" rx="1.2"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.2"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.2"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.2"/></svg>
                                    @break
                                @case('laporan')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></svg>
                                    @break
                                @case('perluasan.jtm')
                                @case('perluasan.jtr')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 19 10 13l4 4 6-7"/><path d="M15 10h5v5"/></svg>
                                    @break
                                @case('tanpa.perluasan')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8"/><path d="m7 7 10 10"/></svg>
                                    @break
                                @case('pbpd.upload')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 14v6h14v-6"/></svg>
                                    @break
                                @case('stok.tiang')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 20V9M10 20V4M16 20v-7M22 20H2"/><path d="M3 6h2M9 2h2M15 10h2"/></svg>
                                    @break
                                @case('vendor.tiang.history')
                                @case('vendor.konstruksi.history')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2M5 5l-2 3"/></svg>
                                    @break
                                @case('vendor.tiang')
                                @case('vendor.konstruksi')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 21h18M5 21V9l7-5 7 5v12"/><path d="M9 21v-5h6v5M8 10h.01M12 10h.01M16 10h.01"/></svg>
                                    @break
                                @case('pengoperasian')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16.5 9"/></svg>
                                    @break
                                @case('pencarian')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="10.8" cy="10.8" r="6.3"/><path d="m16 16 4.5 4.5"/></svg>
                                    @break
                                @case('notifikasi')
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                                    @break
                                @default
                                    <svg class="sidebar-modern-icon block size-[19px] shrink-0 fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8"/></svg>
                            @endswitch
                        </span>
                        <span class="min-w-0 flex-1">{{ $m['label'] }}</span>
                        @if ($m['route'] === 'notifikasi' && $sidebarUnreadCount > 0)
                            <span class="sidebar-notification-badge ml-auto inline-grid h-[18px] min-w-[18px] place-items-center rounded-full bg-[#ffc400] px-[5px] text-[10px] font-extrabold text-[#322800]">{{ $sidebarUnreadCount > 99 ? '99+' : $sidebarUnreadCount }}</span>
                        @endif
                    </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="sidebar-bottom mt-[22px] flex shrink-0 flex-col gap-4">
            @if ($sidebarCanTrackProgress)
                <section class="sidebar-progress rounded-[18px] bg-linear-to-br from-[#214bd7] to-[#1539b8] p-[16px_15px_14px] text-white shadow-[0_7px_16px_rgba(28,64,181,0.17)]" aria-label="Ringkasan progres data">
                    <span class="sidebar-progress-title mb-1.5 block text-xs font-bold leading-[1.35] text-white/85">{{ $sidebarProgressTitle }}</span>
                    <div class="sidebar-progress-count mb-[9px] flex items-baseline gap-[5px] text-xs font-semibold">
                        <strong class="text-[28px] font-extrabold leading-none tracking-[-.04em]">{{ $sidebarProgressCount }}</strong>
                        <span>{{ $sidebarProgressCaption }}</span>
                    </div>
                    <progress class="h-[5px] w-full overflow-hidden rounded-full accent-[#ffc900]" aria-label="Progres data" value="{{ $sidebarProgressPercent }}" max="100">{{ $sidebarProgressPercent }}%</progress>
                </section>
            @endif

            <div class="sidebar-profile flex w-full min-w-0 items-center gap-2.5 px-1">
                <span class="sidebar-avatar flex size-[38px] shrink-0 items-center justify-center rounded-full bg-[#edf2ff] text-[13px] font-extrabold !text-[#1642b8]" aria-hidden="true">{{ $sidebarInitials }}</span>
                <div class="sidebar-profile-copy flex min-w-0 flex-1 flex-col gap-0.5">
                    <span class="sidebar-profile-name block truncate whitespace-nowrap text-xs font-bold leading-[1.2] text-[#17366f]">{{ $sidebarProfileName }}</span>
                    <span class="sidebar-profile-status block truncate whitespace-nowrap text-[11px] leading-[1.2] text-[#7182a3]">{{ $sidebarUser->aktif ? 'Pengguna aktif' : 'Pengguna nonaktif' }}</span>
                </div>
                <form class="m-0" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-logout flex size-[38px] shrink-0 items-center justify-center rounded-[9px] border border-[#e5eafa] !bg-[#f0f3ff] p-0 !text-[#294fba] shadow-none transition-colors hover:transform-none hover:!bg-[#e3eaff] hover:!text-[#133b9a] hover:shadow-none" aria-label="Keluar" title="Keluar">
                        <svg class="block size-[15px] fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:1.8]" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                    </button>
                    </button>
                </form>
            </div>
        </div>
    </aside>
