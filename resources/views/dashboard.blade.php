@extends('layouts.app')
@section('title', 'Dashboard')
@section('judul', 'Dashboard')
@section('isi')
@php
    $namaScope = auth()->user()->role?->type === 'ULP'
        ? (auth()->user()->role?->name ?? 'ULP')
        : 'UP3 Madura';
    $tujuanColors = [
        'BELUM DIKIRIM' => '#6fa2d4',
        'JTR' => '#f4c95d',
        'TANPA PERLUASAN' => '#64b9d6',
        'JTM' => '#1e6fa8',
    ];
    $tujuanSegments = [];
    $tujuanOffset = 0;
    $tujuanTotal = max((int) $tujuan->sum(), 1);
    foreach ($tujuan as $nama => $jumlah) {
        $tujuanLabel = strtoupper(str_replace('_', ' ', $nama));
        $tujuanNext = $tujuanOffset + ((int) $jumlah / $tujuanTotal * 100);
        $tujuanSegments[] = ($tujuanColors[$tujuanLabel] ?? '#94a3b8') . ' ' . $tujuanOffset . '% ' . $tujuanNext . '%';
        $tujuanOffset = $tujuanNext;
    }
    $tujuanGradient = $tujuanSegments ? implode(', ', $tujuanSegments) : '#dbe5f0 0 100%';
@endphp
<div class="modern-dashboard">
    <div class="dashboard-topline"><div><div class="eyebrow">FAST ON / OVERVIEW</div><h1>Selamat datang, {{ $namaScope }} <span>✦</span></h1><p>Berikut ringkasan proses PB/PD yang sedang berjalan.</p></div><a href="{{ route('laporan') }}" class="dashboard-primary">Buka laporan <span>→</span></a></div>

    <div class="metric-grid">
        <div class="metric-card metric-blue"><div class="metric-label"><span class="metric-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span><span>Total data</span></div><strong>{{ number_format($total, 0, ',', '.') }}</strong><small>Dalam cakupan Anda</small><div class="metric-line"><i style="width:78%"></i></div></div>
        <div class="metric-card metric-yellow"><div class="metric-label"><span class="metric-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg></span><span>Belum diproses</span></div><strong>{{ number_format($belumDiproses, 0, ',', '.') }}</strong><small>Menunggu pengiriman ULP</small><div class="metric-line"><i style="width:{{ $total ? round($belumDiproses / $total * 100) : 0 }}%"></i></div></div>
        <div class="metric-card metric-cyan"><div class="metric-label"><span class="metric-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/></svg></span><span>Sedang diproses</span></div><strong>{{ number_format($perencanaan, 0, ',', '.') }}</strong><small>Sedang diproses</small><div class="metric-line"><i style="width:{{ $total ? round($perencanaan / $total * 100) : 0 }}%"></i></div></div>
        <div class="metric-card metric-green"><div class="metric-label"><span class="metric-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="m8.5 12 2.3 2.3 4.7-5"/></svg></span><span>Selesai</span></div><strong>{{ number_format($selesai, 0, ',', '.') }}</strong><small>Proses selesai</small><div class="metric-line"><i style="width:{{ $total ? round($selesai / $total * 100) : 0 }}%"></i></div></div>
    </div>

    <div class="dashboard-layout">
        <div class="dashboard-main-column">
            <section class="modern-panel"><div class="panel-heading"><div><h2>Aktivitas terbaru</h2><p>Data PB/PD terakhir yang masuk dalam sistem</p></div><a href="{{ route('pbpd.index') }}">Lihat semua</a></div><div class="modern-table-wrap"><table class="modern-table"><thead><tr><th>AGENDA</th><th>PELANGGAN</th><th>TRANSAKSI</th><th>TAHAP</th><th>STATUS</th></tr></thead><tbody>@forelse($terbaru as $row) @php $tahapTampilan = ! $row->tahap || $row->tahap === 'ULP' ? 'BELUM DIPROSES' : ($row->tahap === 'SELESAI' ? 'SELESAI' : 'SEDANG DIPROSES'); @endphp<tr><td class="agenda">{{ $row->no_agenda }}</td><td><b>{{ $row->nama_pelanggan }}</b><small>{{ $row->asal_ulp }}</small></td><td>{{ $row->jenis_transaksi }}</td><td><span class="status-tag stage">{{ $tahapTampilan }}</span></td><td><span class="status-dot"></span>{{ $row->status ?: '-' }}</td></tr>@empty<tr><td colspan="5" class="empty">Belum ada data.</td></tr>@endforelse</tbody></table></div></section>
            <section class="modern-panel"><div class="panel-heading"><div><h2>Distribusi tahap proses</h2><p>Perbandingan status data saat ini</p></div></div><div class="stage-chart">@forelse($tahap as $nama => $jumlah)<div class="stage-row"><span>{{ $nama }}</span><div class="stage-track"><i style="width:{{ $total ? round($jumlah / $total * 100) : 0 }}%"></i></div><b>{{ $jumlah }}</b></div>@empty<p class="empty">Belum ada data.</p>@endforelse</div></section>
        </div>
        <aside class="dashboard-side-column">
            <section class="modern-panel compact-panel"><div class="panel-heading"><div><h2>Tujuan proses</h2><p>Distribusi pengiriman</p></div></div><div class="donut-wrap"><div class="donut" style="background:conic-gradient({{ $tujuanGradient }})"><div><b>{{ $total }}</b><small>data</small></div></div><div class="legend">@forelse($tujuan as $nama => $jumlah) @php($tujuanLabel = strtoupper(str_replace('_', ' ', $nama)))<div><i class="legend-dot" style="background:{{ $tujuanColors[$tujuanLabel] ?? '#94a3b8' }}"></i><span>{{ str_replace('_', ' ', $nama) }}</span><b>{{ $jumlah }}</b></div>@empty<p class="empty">-</p>@endforelse</div></div></section>
            <section class="modern-panel compact-panel"><div class="panel-heading"><div><h2>Jenis transaksi</h2><p>Ringkasan permohonan</p></div></div><div class="transaction-list">@forelse($transaksi as $nama => $jumlah)<div><span>{{ $nama }}</span><b>{{ $jumlah }}</b></div>@empty<p class="empty">Belum ada data.</p>@endforelse</div></section>
            @if(auth()->user()->role?->type === 'UP3')<section class="modern-panel compact-panel"><div class="panel-heading"><div><h2>Per ULP</h2><p>Data pada seluruh ULP</p></div></div><div class="transaction-list">@forelse($ulp->take(5) as $nama => $jumlah)<div><span>{{ $nama }}</span><b>{{ $jumlah }}</b></div>@empty<p class="empty">Belum ada data.</p>@endforelse</div></section>@endif
        </aside>
    </div>
</div>
<style>
    .modern-dashboard{color:#263746}.dashboard-topline{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin:4px 0 24px}.eyebrow{color:#0072ce;font-size:11px;font-weight:800;letter-spacing:.12em}.dashboard-topline h1{margin:7px 0 5px;color:#173b5c;font-size:26px;letter-spacing:-.03em}.dashboard-topline h1 span{color:#ffc900}.dashboard-topline p,.panel-heading p{margin:0;color:#8294a3;font-size:13px}.dashboard-primary{padding:11px 16px;border-radius:9px;background:#075ca8;color:#fff;text-decoration:none;font-size:13px;font-weight:700;box-shadow:0 8px 18px rgba(0,91,170,.18);transition:transform .2s,box-shadow .2s}.dashboard-primary:hover{color:#fff;transform:translateY(-1px);box-shadow:0 10px 22px rgba(0,91,170,.24)}.dashboard-primary span{margin-left:8px;font-size:16px}.metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}.metric-card{position:relative;overflow:hidden;padding:18px 18px 16px;border:1px solid #e3e9f1;border-radius:14px;background:#fff;box-shadow:0 6px 18px rgba(38,60,85,.045);transition:transform .2s,box-shadow .2s}.metric-card:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(38,60,85,.09)}.metric-card::after{content:"";position:absolute;right:-28px;bottom:-35px;width:105px;height:105px;border-radius:50%;background:var(--metric-color);opacity:.075}.metric-blue{--metric-color:#0072ce}.metric-yellow{--metric-color:#e1a900}.metric-cyan{--metric-color:#0099c8}.metric-green{--metric-color:#159447}.metric-label{display:flex;align-items:center;gap:8px;color:#718696;font-size:12px}.metric-icon{display:grid;place-items:center;width:29px;height:29px;border-radius:9px;background:color-mix(in srgb,var(--metric-color) 12%,white);color:var(--metric-color);font-weight:800}.metric-card strong{display:block;margin:15px 0 2px;color:#173b5c;font-size:29px;letter-spacing:-.045em}.metric-card small{color:#92a2af;font-size:11px}.metric-line{height:5px;margin-top:15px;border-radius:5px;background:#edf2f6;overflow:hidden}.metric-line i{display:block;height:100%;border-radius:5px;background:var(--metric-color)}.dashboard-layout{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(280px,.9fr);gap:16px}.dashboard-main-column,.dashboard-side-column{display:grid;align-content:start;gap:16px}.modern-panel{min-width:0;padding:19px;border:1px solid #e3e9f1;border-radius:14px;background:#fff;box-shadow:0 6px 18px rgba(38,60,85,.04)}.compact-panel{padding:18px}.panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:15px}.panel-heading h2{margin:0 0 4px;color:#173b5c;font-size:15px}.panel-heading a{color:#0072ce;font-size:12px;font-weight:700;text-decoration:none}.modern-table-wrap{overflow-x:auto}.modern-table{width:100%;border-collapse:collapse;font-size:12px}.modern-table th{padding:9px 8px;border-bottom:1px solid #e6edf2;color:#8a9aa7;font-size:10px;text-align:left;letter-spacing:.05em}.modern-table td{padding:11px 8px;border-bottom:1px solid #eef2f5;color:#536b7b;white-space:nowrap}.modern-table tr:last-child td{border-bottom:0}.modern-table tbody tr{transition:background .15s}.modern-table tbody tr:hover{background:#f6fbfe}.modern-table td b,.modern-table td small{display:block}.modern-table td b{color:#294a63;font-weight:650}.modern-table td small{margin-top:3px;color:#96a6b2;font-size:10px}.modern-table .agenda{color:#0072ce;font-size:11px}.status-tag{display:inline-block;padding:4px 7px;border-radius:6px;font-size:10px;font-weight:700}.status-tag.stage{background:#edf6fc;color:#0064a9}.status-dot{display:inline-block;width:6px;height:6px;margin-right:6px;border-radius:50%;background:#17a15c}.stage-chart{display:grid;gap:12px}.stage-row{display:grid;grid-template-columns:125px minmax(60px,1fr) 25px;align-items:center;gap:10px;color:#627989;font-size:12px}.stage-row>b{text-align:right;color:#173b5c}.stage-track{height:7px;border-radius:8px;background:#edf2f6;overflow:hidden}.stage-track i{display:block;height:100%;border-radius:8px;background:linear-gradient(90deg,#0072ce,#53b3e6)}.donut-wrap{display:flex;align-items:center;gap:18px}.donut{display:grid;place-items:center;width:116px;height:116px;border-radius:50%;background:conic-gradient(#0072ce 0 42%,#ffc900 42% 70%,#66bddc 70% 100%)}.donut>div{display:grid;place-items:center;width:76px;height:76px;border-radius:50%;background:#fff}.donut b{color:#173b5c;font-size:21px}.donut small{color:#8a9aa7;font-size:10px}.legend,.transaction-list{display:grid;flex:1;gap:10px}.legend>div,.transaction-list>div{display:flex;align-items:center;gap:7px;color:#627989;font-size:11px}.legend b,.transaction-list b{margin-left:auto;color:#173b5c}.legend-dot{width:8px;height:8px;border-radius:50%;background:#0072ce}.dot-1{background:#ffc900}.dot-2{background:#66bddc}.empty{color:#91a1ad;font-size:12px}@media(max-width:1000px){.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.dashboard-layout{grid-template-columns:1fr}.dashboard-side-column{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.dashboard-topline{align-items:flex-start;flex-direction:column}.dashboard-topline h1{font-size:22px}.dashboard-primary{width:100%;text-align:center}.metric-grid,.dashboard-side-column{grid-template-columns:1fr}.stage-row{grid-template-columns:105px minmax(40px,1fr) 22px}.modern-panel{padding:14px}}
</style>
<style>
    /* Visual layer only: dashboard remains backed by the existing queries and routes. */
    .modern-dashboard{max-width:1440px;margin:0 auto;padding:4px 2px 18px;color:#27313d}
    .dashboard-topline{padding:8px 3px 4px}
    .dashboard-topline h1{color:#202b36;font-size:25px;font-weight:750}
    .dashboard-topline p,.panel-heading p{color:#9aa3ad}
    .eyebrow{color:#8b96a2;font-size:10px;letter-spacing:.16em}
    .dashboard-primary{background:#202833;color:#fff;border-radius:8px;box-shadow:0 8px 18px rgba(32,40,51,.15)}
    .dashboard-primary:hover{background:#111923;color:#fff}
    .metric-card{border:0;border-radius:12px;background:#fff;box-shadow:0 5px 18px rgba(36,47,61,.055);padding:17px 18px}
    .metric-card:nth-child(1){background:linear-gradient(145deg,#fff 0%,#f3f8ff 100%)}
    .metric-card:nth-child(2){background:linear-gradient(145deg,#fff 0%,#fffaf0 100%)}
    .metric-card:nth-child(3){background:linear-gradient(145deg,#fff 0%,#f2fbfc 100%)}
    .metric-card:nth-child(4){background:linear-gradient(145deg,#fff 0%,#f3fbf6 100%)}
    .metric-card strong{color:#202b36;font-size:28px}
    .metric-label{color:#7e8995}
    .metric-line{background:rgba(150,164,177,.16)}
    .modern-panel{border:0;border-radius:13px;box-shadow:0 5px 18px rgba(36,47,61,.05);padding:18px}
    .compact-panel{padding:17px}
    .panel-heading h2{color:#263442;font-size:14px}
    .modern-table th{color:#9aa3ad;font-size:9px;border-bottom-color:#edf0f3}
    .modern-table td{border-bottom-color:#f0f2f4;color:#687481}
    .modern-table td b{color:#394957}
    .modern-table tbody tr:hover{background:#fafbfd}
    .status-tag.stage{background:#edf5ff;color:#3878ad}
    .donut{background:conic-gradient(#6fa2d4 0 42%,#f1c76a 42% 70%,#88c6ab 70% 100%)}
    .stage-track{background:#f0f2f4}
    .stage-track i{background:linear-gradient(90deg,#7aaed5,#9bd3c0)}
    .dashboard-side-column .modern-panel{background:rgba(255,255,255,.94)}
    @media(max-width:650px){.modern-dashboard{padding:0}.dashboard-topline{padding-top:2px}}
</style>
<style>
    /* Dark visual theme only. No data, route, permission, or interaction logic is changed. */
    body:has(.modern-dashboard){background:#0a1020;color:#d8e2f0}
    body:has(.modern-dashboard) aside{background:#101a2e;color:#c9d6e8;border-right:1px solid #202d45;box-shadow:8px 0 25px rgba(0,0,0,.15)}
    body:has(.modern-dashboard) aside h2{color:#f4f8ff}
    body:has(.modern-dashboard) aside .role{color:#8091ae}
    body:has(.modern-dashboard) aside a{color:#91a2bd;border-color:transparent}
    body:has(.modern-dashboard) aside a:hover{background:#17243c;color:#fff;border-color:#243858}
    body:has(.modern-dashboard) aside a.active{background:#1b2b49;color:#fff;box-shadow:inset 3px 0 #20b8cf}
    body:has(.modern-dashboard) aside a.active::before{display:none}
    body:has(.modern-dashboard) aside a.active::after{background:#20b8cf}
    body:has(.modern-dashboard) header{background:rgba(11,18,34,.92);border-bottom-color:#202d45;box-shadow:0 1px 16px rgba(0,0,0,.18)}
    body:has(.modern-dashboard) header strong{color:#f2f6fc}
    body:has(.modern-dashboard) header form button{background:#17243c;color:#dbe7f6;border-color:#2b3c5b}
    body:has(.modern-dashboard) header form button:hover{background:#213452;color:#fff}
    body:has(.modern-dashboard) main{background:radial-gradient(circle at 80% 0%,#172445 0,#0d1629 38%,#0a1020 78%)}
    body:has(.modern-dashboard) .modern-dashboard{color:#d8e2f0}
    body:has(.modern-dashboard) .dashboard-topline h1{color:#f1f6fd}
    body:has(.modern-dashboard) .dashboard-topline p,
    body:has(.modern-dashboard) .panel-heading p{color:#8191aa}
    body:has(.modern-dashboard) .eyebrow{color:#55c7dc}
    body:has(.modern-dashboard) .dashboard-primary{background:#20aeca;color:#061522;box-shadow:0 8px 20px rgba(32,174,202,.2)}
    body:has(.modern-dashboard) .dashboard-primary:hover{background:#47c7df;color:#061522}
    body:has(.modern-dashboard) .metric-card,
    body:has(.modern-dashboard) .modern-panel{background:linear-gradient(145deg,#17233a,#131e33);border:1px solid #263753;box-shadow:0 10px 24px rgba(0,0,0,.16)}
    body:has(.modern-dashboard) .metric-card:nth-child(1){background:linear-gradient(145deg,#172b49,#14233c)}
    body:has(.modern-dashboard) .metric-card:nth-child(2){background:linear-gradient(145deg,#282b40,#1c2237)}
    body:has(.modern-dashboard) .metric-card:nth-child(3){background:linear-gradient(145deg,#123444,#14263c)}
    body:has(.modern-dashboard) .metric-card:nth-child(4){background:linear-gradient(145deg,#153b3b,#152d39)}
    body:has(.modern-dashboard) .metric-label{color:#91a2bd}
    body:has(.modern-dashboard) .metric-card strong,
    body:has(.modern-dashboard) .panel-heading h2,
    body:has(.modern-dashboard) .metric-card small{color:#eef5ff}
    body:has(.modern-dashboard) .metric-card small{color:#879ab5}
    body:has(.modern-dashboard) .metric-line,
    body:has(.modern-dashboard) .stage-track{background:#26344d}
    body:has(.modern-dashboard) .panel-heading a{color:#59c9dd}
    body:has(.modern-dashboard) .modern-table th{color:#7f91ac;border-bottom-color:#293852}
    body:has(.modern-dashboard) .modern-table td{color:#aebdd0;border-bottom-color:#25344d}
    body:has(.modern-dashboard) .modern-table td b{color:#edf4ff}
    body:has(.modern-dashboard) .modern-table td small{color:#8191aa}
    body:has(.modern-dashboard) .modern-table .agenda{color:#60cce0}
    body:has(.modern-dashboard) .modern-table tbody tr:hover{background:#1d2c47}
    body:has(.modern-dashboard) .status-tag.stage{background:#193d5a;color:#71d2e4}
    body:has(.modern-dashboard) .status-dot{background:#51d19a}
    body:has(.modern-dashboard) .stage-row,
    body:has(.modern-dashboard) .legend>div,
    body:has(.modern-dashboard) .transaction-list>div{color:#9aabc1}
    body:has(.modern-dashboard) .stage-row>b,
    body:has(.modern-dashboard) .legend b,
    body:has(.modern-dashboard) .transaction-list b{color:#edf4ff}
    body:has(.modern-dashboard) .stage-track i{background:linear-gradient(90deg,#21b7d0,#7c7ded)}
    body:has(.modern-dashboard) .donut>div{background:#17233a}
    body:has(.modern-dashboard) .donut b{color:#f1f6fd}
    body:has(.modern-dashboard) .donut small{color:#879ab5}
    body:has(.modern-dashboard) .empty{color:#8191aa}
    body:has(.modern-dashboard) .menu-toggle{background:#17243c;color:#71d2e4}
    body:has(.modern-dashboard) main{padding-left:24px;padding-right:24px}
    body:has(.modern-dashboard) .modern-dashboard{width:100%;max-width:none;margin:0;padding-left:0;padding-right:0}
    body:has(.modern-dashboard) .dashboard-layout{grid-template-columns:minmax(0,2.2fr) minmax(330px,1fr)}
    body:has(.modern-dashboard) .dashboard-layout,
    body:has(.modern-dashboard) .dashboard-main-column,
    body:has(.modern-dashboard) .dashboard-side-column{width:100%;min-width:0}
    body:has(.modern-dashboard) .dashboard-side-column .modern-panel{width:100%}
    @media(max-width:650px){body:has(.modern-dashboard) aside{box-shadow:9px 0 28px rgba(0,0,0,.45)}}
    @media(max-width:1000px){body:has(.modern-dashboard) .dashboard-layout{grid-template-columns:1fr}}
    @media(max-width:650px){body:has(.modern-dashboard) main{padding-left:14px;padding-right:14px}}
</style>
<style>
    /* Final dashboard alignment with the modern PB/PD pages. */
    body:has(.modern-dashboard){background:#f5f8fc!important;color:#334155!important}
    body:has(.modern-dashboard) main{background:linear-gradient(180deg,#f8fbff 0%,#f1f6fb 100%)!important;padding-left:24px;padding-right:24px}
    body:has(.modern-dashboard) aside{background:#0d1b8c!important;border-right:0;box-shadow:2px 0 14px rgba(13,27,140,.12)}
    body:has(.modern-dashboard) aside h2{color:#fff!important}body:has(.modern-dashboard) aside .role{color:#bfdbfe!important}body:has(.modern-dashboard) aside a{color:#dbeafe!important}body:has(.modern-dashboard) aside a:hover{background:rgba(43,115,254,.25)!important;color:#fff!important}body:has(.modern-dashboard) aside a.active{background:#1e40af!important;color:#fff!important;box-shadow:inset 3px 0 #f4c300!important}body:has(.modern-dashboard) aside a.active::after{background:#f4c300!important}
    body:has(.modern-dashboard) header{background:#fff!important;border-bottom-color:#dbe5f0!important;box-shadow:0 2px 10px rgba(13,27,140,.06)!important}body:has(.modern-dashboard) header strong{color:#123b5d!important}body:has(.modern-dashboard) header form button{background:#f1f5f9!important;color:#123b5d!important;border-color:#dbe5f0!important}
    body:has(.modern-dashboard) .modern-dashboard{width:100%;max-width:none;margin:0;padding:8px 0 28px;color:#334155!important}
    body:has(.modern-dashboard) .dashboard-side-column{display:grid!important;align-content:start!important;align-self:start!important;gap:16px!important;width:100%!important;min-width:0!important;padding:0!important;background:transparent!important;color:#334155!important;border:0!important;box-shadow:none!important}
    body:has(.modern-dashboard) .dashboard-side-column .modern-panel{width:100%!important;margin:0!important;background:#fff!important;color:#334155!important}
    body:has(.modern-dashboard) .dashboard-topline{padding:10px 2px 20px;margin:0;align-items:center}body:has(.modern-dashboard) .eyebrow{color:#1e6fa8!important;letter-spacing:.14em}body:has(.modern-dashboard) .dashboard-topline h1{color:#123b5d!important;font-size:27px}body:has(.modern-dashboard) .dashboard-topline h1 span{color:#f4c300!important}body:has(.modern-dashboard) .dashboard-topline p{color:#64748b!important}body:has(.modern-dashboard) .dashboard-primary{background:linear-gradient(135deg,#1e6fa8,#2b73fe)!important;color:#fff!important;box-shadow:0 8px 18px rgba(30,111,168,.2)}body:has(.modern-dashboard) .dashboard-primary:hover{background:linear-gradient(135deg,#123b5d,#1d4ed8)!important;color:#fff!important}
    body:has(.modern-dashboard) .metric-card{background:#fff!important;border:1px solid #dbe5f0!important;border-radius:14px!important;box-shadow:0 7px 20px rgba(13,27,140,.07)!important}body:has(.modern-dashboard) .metric-card:nth-child(1){border-top:3px solid #2b73fe!important}body:has(.modern-dashboard) .metric-card:nth-child(2){border-top:3px solid #f2a541!important}body:has(.modern-dashboard) .metric-card:nth-child(3){border-top:3px solid #27a9d6!important}body:has(.modern-dashboard) .metric-card:nth-child(4){border-top:3px solid #2e9b68!important}body:has(.modern-dashboard) .metric-label{color:#64748b!important}body:has(.modern-dashboard) .metric-card strong{color:#123b5d!important}body:has(.modern-dashboard) .metric-card small{color:#7c8da0!important}body:has(.modern-dashboard) .metric-line{background:#e8eef5!important}
    body:has(.modern-dashboard) .modern-panel{background:#fff!important;border:1px solid #dbe5f0!important;border-radius:14px!important;box-shadow:0 7px 20px rgba(13,27,140,.06)!important}body:has(.modern-dashboard) .panel-heading h2{color:#123b5d!important}body:has(.modern-dashboard) .panel-heading p{color:#64748b!important}body:has(.modern-dashboard) .panel-heading a{color:#1e6fa8!important}body:has(.modern-dashboard) .modern-table th{color:#64748b!important;border-bottom-color:#dbe5f0!important}body:has(.modern-dashboard) .modern-table td{color:#475569!important;border-bottom-color:#edf2f7!important}body:has(.modern-dashboard) .modern-table td b{color:#123b5d!important}body:has(.modern-dashboard) .modern-table td small{color:#94a3b8!important}body:has(.modern-dashboard) .modern-table .agenda{color:#1e6fa8!important}body:has(.modern-dashboard) .modern-table tbody tr:hover{background:#f1f7ff!important}
    body:has(.modern-dashboard) .status-tag.stage{background:#eaf3ff!important;color:#1e6fa8!important}body:has(.modern-dashboard) .status-dot{background:#2e9b68!important}body:has(.modern-dashboard) .stage-row,body:has(.modern-dashboard) .legend>div,body:has(.modern-dashboard) .transaction-list>div{color:#64748b!important}body:has(.modern-dashboard) .stage-row>b,body:has(.modern-dashboard) .legend b,body:has(.modern-dashboard) .transaction-list b{color:#123b5d!important}body:has(.modern-dashboard) .stage-track{background:#e5edf5!important}body:has(.modern-dashboard) .stage-track i{background:linear-gradient(90deg,#1e6fa8,#2b73fe)!important}body:has(.modern-dashboard) .donut>div{background:#fff!important}body:has(.modern-dashboard) .donut b{color:#123b5d!important}body:has(.modern-dashboard) .donut small{color:#64748b!important}body:has(.modern-dashboard) .empty{color:#94a3b8!important}
    body:has(.modern-dashboard) .legend-dot{background:#6fa2d4}
    @media(max-width:650px){body:has(.modern-dashboard) main{padding-left:14px;padding-right:14px}body:has(.modern-dashboard) .dashboard-topline{align-items:flex-start}body:has(.modern-dashboard) .dashboard-topline h1{font-size:22px}}
</style>
@endsection
