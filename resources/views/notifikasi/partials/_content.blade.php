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

            @if ($notifications->total() > 0)
                <div class="notification-pagination">
                    <p class="notification-results">
                        Menampilkan <strong>{{ $notifications->firstItem() }}–{{ $notifications->lastItem() }}</strong>
                        dari <strong>{{ number_format($notifications->total(), 0, ',', '.') }}</strong> notifikasi
                    </p>
                    @if ($notifications->hasPages())
                        @php
                            $notificationPageStart = max(1, $notifications->currentPage() - 1);
                            $notificationPageEnd = min($notifications->lastPage(), $notifications->currentPage() + 1);
                        @endphp
                        <nav class="notification-page-links" aria-label="Halaman notifikasi">
                            @if ($notifications->onFirstPage())
                                <span class="notification-page-arrow is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                                </span>
                            @else
                                <a class="notification-page-arrow" href="{{ $notifications->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                                </a>
                            @endif

                            @foreach (range($notificationPageStart, $notificationPageEnd) as $page)
                                <a href="{{ $notifications->url($page) }}"
                                   class="notification-page-number {{ $page === $notifications->currentPage() ? 'is-current' : '' }}"
                                   @if ($page === $notifications->currentPage()) aria-current="page" @endif>
                                    {{ $page }}
                                </a>
                            @endforeach

                            @if ($notifications->hasMorePages())
                                <a class="notification-page-arrow" href="{{ $notifications->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                </a>
                            @else
                                <span class="notification-page-arrow is-disabled" aria-disabled="true" aria-label="Halaman berikutnya">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            @endif
        </section>
    </div>
