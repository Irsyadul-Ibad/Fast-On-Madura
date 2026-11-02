@extends('layouts.app')

@section('title', 'Stok Tiang')
@section('judul', 'Stok Tiang')

@section('isi')
    @php
        $totalTr = $vendors->sum(fn ($vendor) => (int) ($vendor->stokTiang?->stok_tr ?? 0));
        $totalTm = $vendors->sum(fn ($vendor) => (int) ($vendor->stokTiang?->stok_tm ?? 0));
    @endphp

    @if (session('success'))
        <div class="stok-alert stok-alert-success" role="status">
            <span class="stok-alert-icon" aria-hidden="true">✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="stok-alert stok-alert-error" role="alert">
            <strong>Input belum dapat disimpan.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

{{-- <div class="stok-intro">
        <div>
            <span class="stok-eyebrow">PERENCANAAN MATERIAL</span>
            <h1>Stok Tiang</h1>
            <p>Atur jumlah stok tiang yang tersedia untuk setiap vendor tiang. Stok dibedakan menjadi jenis TR dan TM.</p>
        </div>
        <div class="stok-intro-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M4 21h16"/><path d="M7 21V5l5-2 5 2v16"/><path d="M10 8h4M10 12h4M10 16h4"/></svg>
        </div>
    </div>  --}}

    <div class="stok-summary-grid">
        <div class="stok-summary-card">
            <span class="stok-summary-label">VENDOR TIANG</span>
            <strong>{{ $vendors->count() }}</strong>
            <small>vendor terdaftar</small>
        </div>
        <div class="stok-summary-card stok-summary-tr">
            <span class="stok-summary-label">TOTAL STOK TR</span>
            <strong id="totalStokTr">{{ number_format($totalTr, 0, ',', '.') }}</strong>
            <small>tiang tegangan rendah</small>
        </div>
        <div class="stok-summary-card stok-summary-tm">
            <span class="stok-summary-label">TOTAL STOK TM</span>
            <strong id="totalStokTm">{{ number_format($totalTm, 0, ',', '.') }}</strong>
            <small>tiang tegangan menengah</small>
        </div>
    </div>

    <form class="box stok-form" method="POST" action="{{ route('stok.tiang.update') }}">
        @csrf
        <div class="stok-form-heading">
            <div>
                <h2>Daftar Stok per Vendor</h2>
                <p>Masukkan jumlah stok terbaru pada kolom yang sesuai, lalu simpan perubahan.</p>
            </div>
            <span class="pill">{{ $vendors->count() }} vendor</span>
        </div>

        @if ($vendors->isEmpty())
            <div class="stok-empty">Belum ada vendor tiang yang terdaftar.</div>
        @else
            <div class="scroll stok-table-scroll">
                <table class="tabel stok-table">
                    <thead>
                        <tr>
                            <th class="stok-number">NO.</th>
                            <th>NAMA VENDOR TIANG</th>
                            <th class="stok-column stok-column-tr">STOK TIANG TR</th>
                            <th class="stok-column stok-column-tm">STOK TIANG TM</th>
                            <th class="stok-total-column">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $index => $vendor)
                            @php
                                $stokTr = old("stok.{$vendor->id}.tr", $vendor->stokTiang?->stok_tr ?? 0);
                                $stokTm = old("stok.{$vendor->id}.tm", $vendor->stokTiang?->stok_tm ?? 0);
                            @endphp
                            <tr>
                                <td class="stok-number">{{ $index + 1 }}</td>
                                <td>
                                    <div class="stok-vendor-name">
                                        <span class="stok-vendor-mark" aria-hidden="true">{{ strtoupper(substr($vendor->nama, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $vendor->nama }}</strong>
                                            <small>Vendor tiang</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="stok-input-cell stok-input-tr">
                                    <label class="sr-only" for="stok-{{ $vendor->id }}-tr">Stok tiang TR {{ $vendor->nama }}</label>
                                    <input id="stok-{{ $vendor->id }}-tr" class="stok-input stok-tr-input" type="number" name="stok[{{ $vendor->id }}][tr]" min="0" step="1" value="{{ $stokTr }}" inputmode="numeric" required>
                                    <span>tiang</span>
                                </td>
                                <td class="stok-input-cell stok-input-tm">
                                    <label class="sr-only" for="stok-{{ $vendor->id }}-tm">Stok tiang TM {{ $vendor->nama }}</label>
                                    <input id="stok-{{ $vendor->id }}-tm" class="stok-input stok-tm-input" type="number" name="stok[{{ $vendor->id }}][tm]" min="0" step="1" value="{{ $stokTm }}" inputmode="numeric" required>
                                    <span>tiang</span>
                                </td>
                                <td class="stok-row-total"><strong>{{ (int) $stokTr + (int) $stokTm }}</strong> tiang</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="stok-form-footer">
                <p><span class="stok-info-icon">i</span> Perubahan stok berlaku untuk vendor yang dipilih setelah disimpan.</p>
                <button type="submit" class="btn stok-save-button">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12l2 2v14H5z"/><path d="M8 4v6h8V4M8 20v-6h8v6"/></svg>
                    Simpan Stok
                </button>
            </div>
        @endif
    </form>

    @push('page-styles')
        <style>
            .stok-intro{display:flex;justify-content:space-between;align-items:center;gap:24px;margin-bottom:16px;padding:24px 28px;border:1px solid #dce5f0;border-radius:14px;background:linear-gradient(120deg,#fff 0%,#f1f7ff 100%);box-shadow:0 5px 18px rgba(15,23,42,.06)}
            .stok-eyebrow{display:block;margin-bottom:7px;color:#2b73fe;font-size:11px;font-weight:800;letter-spacing:.12em}
            .stok-intro h1{margin:0;color:#0d1b8c;font-size:25px;letter-spacing:-.02em}
            .stok-intro p{max-width:680px;margin:8px 0 0;color:#64748b;font-size:13px;line-height:1.6}
            .stok-intro-icon{display:grid;place-items:center;flex:0 0 64px;width:64px;height:64px;border-radius:18px;background:#e7f0ff;color:#2b73fe}
            .stok-intro-icon svg{width:32px;height:32px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
            .stok-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:16px}
            .stok-summary-card{min-height:116px;padding:18px 20px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;box-shadow:0 2px 8px rgba(15,23,42,.07)}
            .stok-summary-label{display:block;color:#64748b;font-size:11px;font-weight:800;letter-spacing:.08em}
            .stok-summary-card strong{display:block;margin-top:8px;color:#0d1b8c;font-size:28px;letter-spacing:-.04em;line-height:1}
            .stok-summary-card small{display:block;margin-top:8px;color:#94a3b8;font-size:12px}
            .stok-summary-tr{border-top:3px solid #2b73fe}.stok-summary-tm{border-top:3px solid #27a9d6}
            .stok-summary-tr strong{color:#1d4ed8}.stok-summary-tm strong{color:#087ea4}
            .stok-alert{display:flex;align-items:flex-start;gap:9px;margin-bottom:14px;padding:12px 15px;border-radius:10px;font-size:13px;line-height:1.45}
            .stok-alert-success{border:1px solid #bce3cb;border-left:4px solid #2e9b68;background:#f1fbf5;color:#176b35;font-weight:600}
            .stok-alert-error{display:block;border:1px solid #f1c1bd;border-left:4px solid #d9534f;background:#fff5f4;color:#a33b36}
            .stok-alert-error ul{margin:5px 0 0 18px;padding:0;font-weight:500}
            .stok-alert-icon{display:inline-grid;place-items:center;width:18px;height:18px;border-radius:50%;background:#2e9b68;color:#fff;font-size:12px}
            .stok-form{padding:0;overflow:hidden}
            .stok-form-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:21px 22px 17px;border-bottom:1px solid #e2e8f0}
            .stok-form-heading h2{margin:0;color:#1e293b;font-size:17px}.stok-form-heading p{margin:5px 0 0;color:#64748b;font-size:12px}
            .stok-table-scroll{border:0;border-radius:0}.stok-table{width:100%;min-width:690px}.stok-table th{font-size:10px;letter-spacing:.06em}.stok-table th,.stok-table td{padding:13px 16px}.stok-number{width:65px;text-align:center!important;color:#64748b}.stok-column{width:185px;text-align:center!important}.stok-total-column{width:130px;text-align:center!important}
            .stok-column-tr{background:#1d4ed8!important}.stok-column-tm{background:#087ea4!important}
            .stok-vendor-name{display:flex;align-items:center;gap:11px;min-width:250px}.stok-vendor-mark{display:grid;place-items:center;flex:0 0 32px;width:32px;height:32px;border-radius:9px;background:#eff6ff;color:#2b73fe;font-size:13px;font-weight:800}.stok-vendor-name strong{display:block;color:#1e3a5f;font-size:13px;white-space:normal}.stok-vendor-name small{display:block;margin-top:3px;color:#94a3b8;font-size:11px}
            .stok-input-cell{text-align:center!important}.stok-input{width:105px;min-height:37px;padding:7px 9px;border:1px solid #cbd5e1;border-radius:7px;text-align:right;font-size:14px;font-weight:700}.stok-input-tr{background:#f8fbff}.stok-input-tm{background:#f4fbfd}.stok-input-tr .stok-input{border-color:#b9d0fc;color:#1d4ed8}.stok-input-tm .stok-input{border-color:#b6dfeb;color:#087ea4}.stok-input-cell>span{margin-left:5px;color:#94a3b8;font-size:11px}.stok-row-total{color:#64748b;text-align:center!important;font-size:12px}.stok-row-total strong{color:#1e293b;font-size:14px}
            .stok-form-footer{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 22px;border-top:1px solid #e2e8f0;background:#fbfdff}.stok-form-footer p{margin:0;color:#64748b;font-size:12px}.stok-info-icon{display:inline-grid;place-items:center;width:16px;height:16px;margin-right:4px;border:1px solid #94a3b8;border-radius:50%;font-size:10px;font-weight:700}.stok-save-button{display:inline-flex;align-items:center;gap:7px;padding:9px 15px}.stok-save-button svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.stok-empty{padding:28px 22px;color:#64748b;font-size:13px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            @media(max-width:700px){.stok-intro{align-items:flex-start;padding:19px}.stok-intro-icon{display:none}.stok-summary-grid{grid-template-columns:1fr}.stok-form-heading,.stok-form-footer{align-items:flex-start;flex-direction:column}.stok-save-button{width:100%;justify-content:center}.stok-form-footer p{line-height:1.5}}
        </style>
    @endpush

    <script>
        (() => {
            const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value);
            const updateStockSummary = () => {
                let totalTr = 0;
                let totalTm = 0;

                document.querySelectorAll('.stok-tr-input').forEach((input) => {
                    totalTr += Number(input.value) || 0;
                });
                document.querySelectorAll('.stok-tm-input').forEach((input) => {
                    totalTm += Number(input.value) || 0;
                });

                document.getElementById('totalStokTr').textContent = formatNumber(totalTr);
                document.getElementById('totalStokTm').textContent = formatNumber(totalTm);
            };

            document.querySelectorAll('.stok-input').forEach((input) => {
                input.addEventListener('input', (event) => {
                    const row = event.target.closest('tr');
                    const tr = Number(row.querySelector('.stok-tr-input')?.value) || 0;
                    const tm = Number(row.querySelector('.stok-tm-input')?.value) || 0;
                    row.querySelector('.stok-row-total strong').textContent = formatNumber(tr + tm);
                    updateStockSummary();
                });
            });
        })();
    </script>
@endsection
