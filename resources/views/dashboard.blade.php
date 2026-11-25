@extends('layouts.app')
@section('title', 'Dashboard')
@section('judul', 'Dashboard')
@section('isi')
@php
    $namaScope = auth()->user()->role?->type === 'ULP'
        ? (auth()->user()->role?->name ?? 'ULP')
        : 'UP3 Madura';
    $terkirim = max($total - $belumDiproses - $selesai, 0);
    $batasBelumDikirim = $total > 0 ? $belumDiproses / $total * 100 : 0;
    $batasTerkirim = $total > 0 ? ($belumDiproses + $terkirim) / $total * 100 : 0;
    $latarDonat = $total > 0
        ? "conic-gradient(var(--app-waiting) 0 {$batasBelumDikirim}%, var(--app-blue) {$batasBelumDikirim}% {$batasTerkirim}%, var(--app-green) {$batasTerkirim}% 100%)"
        : 'var(--app-soft)';
@endphp
<div class="modern-dashboard">
    <div class="dashboard-topline">
        <div>
            <button type="button" class="menu-toggle hidden h-[38px] w-[38px] shrink-0 items-center justify-center rounded-[10px] border border-[#0d1b8c] bg-[#0d1b8c] p-0 text-white shadow-[0_3px_8px_rgba(13,27,140,0.18)] hover:transform-none hover:border-[#091267] hover:bg-[#091267] hover:shadow-[0_4px_10px_rgba(13,27,140,0.24)] focus-visible:outline focus-visible:outline-3 focus-visible:outline-[#2b73fe]/30 focus-visible:outline-offset-2 max-[650px]:inline-flex" id="menuToggle" aria-label="Buka menu" aria-controls="mainSidebar" aria-expanded="false"><svg class="size-[18px] fill-none stroke-current [stroke-linecap:round] [stroke-linejoin:round] [stroke-width:2]" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            <div class="dashboard-heading-copy">
                <h1>Selamat datang, {{ $namaScope }}</h1>
                <p>Ringkasan proses PB/PD yang sedang berjalan</p>
            </div>
        </div>
        <a href="{{ route('laporan') }}" class="dashboard-primary">Buka laporan <span>→</span></a>
    </div>

    <div class="metric-grid">
        <div class="metric-card metric-blue">
            <div class="metric-label">
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                </span>
                <span>Total data</span>
            </div>
            <strong>{{ number_format($total, 0, ',', '.') }}</strong>
            <small>Dalam cakupan Anda</small>
        </div>
        <div class="metric-card metric-yellow">
            <div class="metric-label">
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="8"/>
                        <path d="M12 8v5l3 2"/>
                    </svg>
                </span>
                <span>Belum dikirim</span>
            </div>
            <strong>{{ number_format($belumDiproses, 0, ',', '.') }}</strong>
            <small>Menunggu pengiriman ULP</small>
        </div>
        <div class="metric-card metric-cyan">
            <div class="metric-label">
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m13 2-9 12h7l-1 8 10-13h-7l1-7Z"/>
                    </svg>
                </span>
                <span>Terkirim</span>
            </div>
            <strong>{{ number_format($terkirim, 0, ',', '.') }}</strong>
            <small>Sudah masuk proses</small>
        </div>
        <div class="metric-card metric-green">
            <div class="metric-label">
                <span class="metric-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="8"/>
                        <path d="m8.5 12 2.3 2.3 4.7-5"/>
                    </svg>
                </span>
                <span>Selesai</span>
            </div>
            <strong>{{ number_format($selesai, 0, ',', '.') }}</strong>
            <small>Proses selesai</small>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="dashboard-main-column">
            <section class="modern-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Aktivitas terbaru</h2>
                        <p>Data PB/PD terakhir yang masuk dalam sistem</p>
                    </div>
                    <a href="{{ route('pbpd.index') }}">Lihat semua</a>
                </div>
                <div class="modern-table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>AGENDA</th>
                                <th>PELANGGAN</th>
                                <th>TRANSAKSI</th>
                                <th>TAHAP</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($terbaru as $row)
                                @php
                                    $tahapTampilan = ! $row->tahap || $row->tahap === 'ULP'
                                        ? 'BELUM DIKIRIM'
                                        : ($row->tahap === 'SELESAI' ? 'SELESAI' : 'TERKIRIM');
                                @endphp
                                <tr>
                                    <td class="agenda">{{ $row->no_agenda }}</td>
                                    <td><b>{{ $row->nama_pelanggan }}</b><small>{{ $row->asal_ulp }}</small></td>
                                    <td>{{ $row->jenis_transaksi }}</td>
                                    <td><span class="status-tag stage">{{ $tahapTampilan }}</span></td>
                                    <td><span class="status-dot"></span>{{ $row->status ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <aside class="dashboard-side-column">
            <section class="modern-panel compact-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Status proses</h2>
                        <p>Distribusi data berdasarkan tahap</p>
                    </div>
                </div>
                <div class="donut-wrap">
                    <div class="donut" style="background:{{ $latarDonat }}">
                        <div><b>{{ $total }}</b><small>data</small></div>
                    </div>
                    <div class="legend">
                        <div><i class="legend-dot legend-dot-waiting"></i><span>Belum dikirim</span><b>{{ $belumDiproses }}</b></div>
                        <div><i class="legend-dot legend-dot-sent"></i><span>Terkirim</span><b>{{ $terkirim }}</b></div>
                        <div><i class="legend-dot legend-dot-complete"></i><span>Selesai</span><b>{{ $selesai }}</b></div>
                    </div>
                </div>
            </section>
            <section class="modern-panel compact-panel">
                <div class="panel-heading">
                    <div>
                        <h2>Jenis transaksi</h2>
                        <p>Pada data terkirim</p>
                    </div>
                </div>
                <div class="transaction-list">
                    @forelse ($transaksiTerkirim as $nama => $jumlah)
                        <div>
                            <span>{{ $nama }}</span><b>{{ $jumlah }}</b>
                            <i><span style="width:{{ $terkirim ? round($jumlah / $terkirim * 100) : 0 }}%"></span></i>
                        </div>
                    @empty
                        <p class="empty">Belum ada data.</p>
                    @endforelse
                </div>
            </section>
            @if (auth()->user()->role?->type === 'UP3')
                <section class="modern-panel compact-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Per ULP</h2>
                            <p>Data pada seluruh ULP</p>
                        </div>
                    </div>
                    <div class="transaction-list">
                        @forelse ($ulp->take(5) as $nama => $jumlah)
                            <div><span>{{ $nama }}</span><b>{{ $jumlah }}</b></div>
                        @empty
                            <p class="empty">Belum ada data.</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
