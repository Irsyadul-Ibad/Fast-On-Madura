@extends('layouts.app')

@section('title', $showHistory ? 'Riwayat Pengiriman' : 'Agenda Vendor Konstruksi')
@section('judul', $showHistory ? 'Riwayat Pengiriman' : 'Agenda Vendor Konstruksi')
@section('deskripsi', $showHistory ? 'Laporan yang sudah dikirim ke Perencanaan' : 'Daftar agenda konstruksi yang ditugaskan kepada mitra vendor')
@section('header-actions')
    <span class="vendor-count-header">{{ $showHistory ? $riwayat->count() : $data->count() }} data</span>
@endsection

@section('isi')
    <div id="vendorKonstruksiPage" class="vendor-page">
        @if (session('success'))
            <div class="vendor-flash" role="status">{{ session('success') }}</div>
        @endif

        @if (!$showHistory)
            <section class="vendor-shell">
                <header class="vendor-section-heading">
                    <div>
                        <h2>Agenda Vendor Konstruksi</h2>
                        <p>Agenda konstruksi yang menunggu pengerjaan vendor</p>
                    </div>
                    <span class="vendor-count">{{ $data->count() }} data</span>
                </header>

                @forelse ($data as $row)
                    @php $pengiriman = $row->pengirimanKonstruksi; @endphp
                    <article class="vendor-record">
                        <header class="record-head">
                            <div class="record-identity">
                                <b>{{ $row->no_agenda }}</b>
                                <strong>{{ $row->nama_pelanggan }}</strong>
                                <span>ULP: {{ $row->ulp?->nama }} · {{ $row->jenis_transaksi }}</span>
                            </div>
                            <div class="record-badges">
                                <span class="vendor-badge badge-transaction">{{ $row->jenis_transaksi }}</span>
                            </div>
                        </header>

                        <div class="record-content">
                            <section class="record-panel work-files">
                                <h3>Berkas Pekerjaan</h3>
                                @forelse (($pengiriman?->berkas_paths ?? []) as $i => $path)
                                    <a class="vendor-file-link" href="{{ route('vendor.konstruksi.delivery.file', ['pelanggan' => $row->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                        <span class="file-icon" aria-hidden="true">▤</span>
                                        <span>{{ $pengiriman?->berkas_names[$i] ?? (basename($path) ?: 'Berkas ' . ($i + 1)) }}</span>
                                        <span class="download-icon" aria-hidden="true">↓</span>
                                    </a>
                                @empty
                                    <p class="muted-note">Belum ada berkas pekerjaan.</p>
                                @endforelse
                            </section>

                            <form class="record-panel vendor-report-form" method="POST" action="{{ route('vendor.konstruksi.report', $row->id) }}" enctype="multipart/form-data">
                                @csrf
                                <h3>Kirim Laporan</h3>
                                <div class="report-checklist">
                                    <label><input type="checkbox" name="pekerjaan_lengkap" value="1"> <span>Material lengkap</span></label>
                                    <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1"> <span>Pekerjaan dimulai</span></label>
                                    <label><input type="checkbox" name="foto_terlampir" value="1"> <span>Pekerjaan selesai</span></label>
                                    <label><input type="checkbox" name="siap_dilanjutkan" value="1"> <span>Pekerjaan siap untuk dioperasikan</span></label>
                                </div>
                                <label class="field-label" for="catatan-konstruksi-{{ $row->id }}">Catatan (opsional)</label>
                                <textarea id="catatan-konstruksi-{{ $row->id }}" name="catatan" placeholder="Tulis catatan bila diperlukan"></textarea>
                                <label class="file-drop">
                                    <span class="file-picker">↑ &nbsp; Pilih file</span>
                                    <span class="file-name">Belum ada file dipilih</span>
                                    <input type="file" name="berkas_laporan[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                </label>
                                <button type="submit" class="vendor-primary-button">➤ &nbsp; Kirim ke Perencanaan</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="vendor-empty-state">
                        <span class="empty-icon" aria-hidden="true">⌂</span>
                        <h3>Belum ada agenda vendor konstruksi</h3>
                        <p>Agenda baru akan muncul di sini setelah ditugaskan oleh ULP.</p>
                        <a class="vendor-outline-button" href="{{ route('vendor.konstruksi.history') }}">◷ &nbsp; Lihat riwayat pengiriman</a>
                    </div>
                @endforelse
            </section>
        @else
            <section class="vendor-shell history-shell">
                <header class="vendor-section-heading">
                    <div>
                        <h2>Riwayat Pengiriman</h2>
                        <p>Laporan yang sudah dikirim ke Perencanaan</p>
                    </div>
                    <span class="vendor-count">{{ $riwayat->count() }} data</span>
                </header>

                @forelse ($riwayat as $row)
                    @php $pengiriman = $row->pengirimanKonstruksi; @endphp
                    <article class="history-entry">
                        <header class="record-head history-head">
                            <div class="record-identity">
                                <b>{{ $row->no_agenda }}</b>
                                <strong>{{ $row->nama_pelanggan }}</strong>
                                <span>ULP: {{ $row->ulp?->nama }} · {{ $pengiriman?->laporan_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="record-badges">
                                <span class="vendor-badge badge-transaction">➤ &nbsp; Terkirim ke Perencanaan</span>
                                <span class="vendor-badge badge-success">Terkirim</span>
                            </div>
                        </header>

                        @if (count($pengiriman?->laporan_paths ?? []) > 0)
                            <section class="history-files">
                                <h3>Berkas Laporan</h3>
                                <div class="history-file-list">
                                    @foreach (($pengiriman?->laporan_paths ?? []) as $i => $path)
                                        <a class="vendor-file-link" href="{{ route('vendor.konstruksi.report.file', ['pelanggan' => $row->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                            <span class="file-icon" aria-hidden="true">▤</span>
                                            <span>{{ $pengiriman?->laporan_names[$i] ?? (basename($path) ?: 'Berkas laporan ' . ($i + 1)) }}</span>
                                            <span class="download-icon" aria-hidden="true">↓</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <form id="edit-laporan-konstruksi-{{ $row->id }}" class="history-edit-form" method="POST" action="{{ route('vendor.konstruksi.report', $row->id) }}" enctype="multipart/form-data">
                            @csrf
                            <h3>Edit Laporan</h3>
                            <div class="report-checklist">
                                <label><input type="checkbox" name="pekerjaan_lengkap" value="1" @checked($pengiriman?->pekerjaan_lengkap)> <span>Material lengkap</span></label>
                                <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1" @checked($pengiriman?->pekerjaan_sesuai_wo)> <span>Pekerjaan dimulai</span></label>
                                <label><input type="checkbox" name="foto_terlampir" value="1" @checked($pengiriman?->foto_terlampir)> <span>Pekerjaan selesai</span></label>
                                <label><input type="checkbox" name="siap_dilanjutkan" value="1" @checked($pengiriman?->siap_dilanjutkan)> <span>Pekerjaan siap untuk dioperasikan</span></label>
                            </div>
                            <label class="field-label" for="catatan-edit-konstruksi-{{ $row->id }}">Catatan (opsional)</label>
                            <textarea id="catatan-edit-konstruksi-{{ $row->id }}" name="catatan" placeholder="Tulis catatan bila diperlukan">{{ $pengiriman?->catatan }}</textarea>
                            <label class="file-drop">
                                <span class="file-picker">↑ &nbsp; Pilih file</span>
                                <span class="file-name">Pilih berkas baru untuk ditambahkan</span>
                                <input id="berkas-laporan-{{ $row->id }}" type="file" name="berkas_laporan[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            </label>
                        </form>
                        <footer class="history-actions">
                            <form class="history-delete-form" method="POST" action="{{ route('vendor.konstruksi.report.delete', $row->id) }}" onsubmit="return confirm('Hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vendor-danger-button">♲ &nbsp; Hapus laporan</button>
                            </form>
                            <button type="submit" form="edit-laporan-konstruksi-{{ $row->id }}" class="vendor-primary-button">✓ &nbsp; Simpan perubahan</button>
                        </footer>
                    </article>
                @empty
                    <div class="vendor-empty-state compact-empty">
                        <span class="empty-icon" aria-hidden="true">◷</span>
                        <h3>Belum ada riwayat pengiriman</h3>
                        <p>Laporan yang dikirim ke Perencanaan akan muncul di sini.</p>
                    </div>
                @endforelse
            </section>
        @endif
    </div>

    <script>
        document.querySelectorAll('#vendorKonstruksiPage .file-drop input[type="file"]').forEach((input) => {
            input.addEventListener('change', () => {
                const name = input.closest('.file-drop')?.querySelector('.file-name');
                if (name) name.textContent = Array.from(input.files || []).map((file) => file.name).join(', ') || 'Belum ada file dipilih';
            });
        });
    </script>

    @push('page-styles')
    <style>
        #vendorKonstruksiPage{--vendor-blue:#1739b7;--vendor-bright:#2450e6;--vendor-ink:#202b55;--vendor-muted:#60719f;--vendor-line:#dce5fb;--vendor-soft:#f5f7ff;box-sizing:border-box;width:100%;min-width:0;color:var(--vendor-ink)}
        #vendorKonstruksiPage *,#vendorKonstruksiPage *:before,#vendorKonstruksiPage *:after{box-sizing:border-box}
        #vendorKonstruksiPage .vendor-flash{margin:0 0 16px;padding:14px 18px;border:1px solid #b8e5c9;border-radius:14px;background:#e9f8ef;color:#176b3b;font-weight:700}
        .page-topbar .page-actions:has(.vendor-count-header){align-self:flex-start;padding-top:4px}
        .vendor-count-header{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 16px;border-radius:999px;background:#e4ebff;color:#1b43c4;font-size:14px;font-weight:800;white-space:nowrap}
        #vendorKonstruksiPage .vendor-shell{min-width:0;padding:24px;border:1px solid #e2e9fb;border-radius:28px;background:#fff;box-shadow:0 16px 40px rgba(28,55,132,.07)}
        #vendorKonstruksiPage .vendor-section-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:0 0 20px}
        #vendorKonstruksiPage .vendor-section-heading h2{margin:0;color:var(--vendor-blue);font-size:22px;font-weight:850;line-height:1.25}
        #vendorKonstruksiPage .vendor-section-heading p{margin:4px 0 0;color:var(--vendor-muted);font-size:15px}
        #vendorKonstruksiPage .vendor-count{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 16px;border-radius:999px;background:#e7edff;color:#1b43c4;font-size:14px;font-weight:800;white-space:nowrap}
        #vendorKonstruksiPage .vendor-record,#vendorKonstruksiPage .history-entry{min-width:0;margin:0 0 18px;overflow:hidden;border:1px solid var(--vendor-line);border-radius:22px;background:#fff}
        #vendorKonstruksiPage .vendor-record:last-child,#vendorKonstruksiPage .history-entry:last-child{margin-bottom:0}
        #vendorKonstruksiPage .record-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;padding:20px 24px;background:#f2f5ff;border-bottom:1px solid var(--vendor-line)}
        #vendorKonstruksiPage .record-identity{min-width:0}
        #vendorKonstruksiPage .record-identity b,#vendorKonstruksiPage .record-identity strong,#vendorKonstruksiPage .record-identity span{display:block}
        #vendorKonstruksiPage .record-identity b{color:#2449c0;font-size:15px;font-weight:800;overflow-wrap:anywhere}
        #vendorKonstruksiPage .record-identity strong{margin:4px 0;color:var(--vendor-blue);font-size:21px;font-weight:850;line-height:1.25;overflow-wrap:anywhere}
        #vendorKonstruksiPage .record-identity span{color:var(--vendor-muted);font-size:14px}
        #vendorKonstruksiPage .record-badges{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;flex:0 0 auto}
        #vendorKonstruksiPage .vendor-badge{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:7px 13px;border:1px solid transparent;border-radius:999px;font-size:12px;font-weight:850;line-height:1.2;white-space:nowrap}
        #vendorKonstruksiPage .badge-transaction{background:#e6edff;color:#2148be}
        #vendorKonstruksiPage .badge-success{background:#dcf5e8;color:#187344}
        #vendorKonstruksiPage .badge-danger{background:#ffe5e5;color:#b92d25}
        #vendorKonstruksiPage .record-content{display:grid;grid-template-columns:minmax(230px,.85fr) minmax(0,1.8fr);gap:18px;padding:20px 24px 24px}
        #vendorKonstruksiPage .record-panel{display:grid;align-content:start;gap:13px;min-width:0;margin:0;padding:18px;border:1px solid var(--vendor-line);border-radius:18px;background:#fff}
        #vendorKonstruksiPage .record-panel h3,#vendorKonstruksiPage .history-edit-form h3,#vendorKonstruksiPage .history-files h3{margin:0 0 3px;color:#5b6d9b;font-size:14px;font-weight:900;letter-spacing:.09em;text-transform:uppercase}
        #vendorKonstruksiPage .work-files{gap:10px}
        #vendorKonstruksiPage .vendor-file-link{display:flex;align-items:center;gap:11px;min-width:0;padding:13px;border-radius:14px;background:#eaf0ff;color:#1c45be;text-decoration:none;font-weight:750}
        #vendorKonstruksiPage .vendor-file-link>span:nth-child(2){min-width:0;overflow-wrap:anywhere}
        #vendorKonstruksiPage .file-icon,#vendorKonstruksiPage .download-icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 42px;width:42px;height:42px;border-radius:12px;background:#fff;color:#2450d7;font-size:24px;font-weight:800}
        #vendorKonstruksiPage .download-icon{flex-basis:38px;width:38px;height:38px;margin-left:auto;color:#334672;font-size:23px}
        #vendorKonstruksiPage .muted-note{margin:0;color:var(--vendor-muted);font-size:14px}
        #vendorKonstruksiPage .report-checklist{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
        #vendorKonstruksiPage .report-checklist label{display:flex;align-items:center;gap:11px;min-width:0;min-height:52px;margin:0;padding:12px 14px;border:1px solid #d8e2fa;border-radius:14px;background:#fff;color:#2d3c69;font-size:14px;font-weight:650;line-height:1.35;cursor:pointer}
        #vendorKonstruksiPage .report-checklist label:has(input:checked){border-color:#2852e8;background:#edf2ff}
        #vendorKonstruksiPage .report-checklist input[type=checkbox]{flex:0 0 20px;width:20px;height:20px;margin:0;accent-color:#2450df}
        #vendorKonstruksiPage .field-label{margin:0 0 -7px;color:#53648f;font-size:13px;font-weight:750}
        #vendorKonstruksiPage textarea{display:block;width:100%;min-width:0;min-height:88px;padding:12px 14px;border:1px solid #d2def8!important;border-radius:14px;background:#f7f9ff!important;color:var(--vendor-ink)!important;font:inherit;resize:vertical}
        #vendorKonstruksiPage textarea::placeholder{color:#7180a7}
        #vendorKonstruksiPage .file-drop{position:relative;display:flex;align-items:center;gap:14px;min-width:0;min-height:64px;padding:10px 13px;border:1px dashed #b8caff;border-radius:15px;background:#f8faff;cursor:pointer}
        #vendorKonstruksiPage .file-drop input[type=file]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
        #vendorKonstruksiPage .file-picker{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:8px 14px;border:1px solid #c5d3ff;border-radius:12px;background:#fff;color:#173db6;font-size:14px;font-weight:800;white-space:nowrap}
        #vendorKonstruksiPage .file-name{min-width:0;color:var(--vendor-muted);font-size:14px;overflow-wrap:anywhere}
        #vendorKonstruksiPage .vendor-primary-button,#vendorKonstruksiPage .vendor-outline-button,#vendorKonstruksiPage .vendor-danger-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:11px 18px;border:1px solid transparent;border-radius:14px;font:inherit;font-size:15px;font-weight:800;text-decoration:none;cursor:pointer}
        #vendorKonstruksiPage .vendor-primary-button{background:linear-gradient(120deg,#2452e5,#1234a8);color:#fff;box-shadow:0 10px 22px rgba(26,65,188,.22)}
        #vendorKonstruksiPage .vendor-primary-button:hover{filter:brightness(.96)}
        #vendorKonstruksiPage .vendor-outline-button{border-color:#c4d1ff;background:#fff;color:#183cae}
        #vendorKonstruksiPage .vendor-danger-button{border-color:#f2b9b6;background:#fff;color:#b92d25}
        #vendorKonstruksiPage .history-shell .history-entry{margin-bottom:18px}
        #vendorKonstruksiPage .history-shell .history-entry:last-child{margin-bottom:0}
        #vendorKonstruksiPage .history-files{display:grid;gap:12px;margin:18px 24px 0;padding:17px;border:1px solid var(--vendor-line);border-radius:17px;background:#f8faff}
        #vendorKonstruksiPage .history-file-list{display:grid;gap:9px}
        #vendorKonstruksiPage .history-edit-form{display:grid;gap:13px;padding:20px 24px 8px;border:0;background:#fff}
        #vendorKonstruksiPage .history-edit-form h3{margin-bottom:0}
        #vendorKonstruksiPage .history-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 24px 22px}
        #vendorKonstruksiPage .history-delete-form{margin:0}
        #vendorKonstruksiPage .vendor-empty-state{display:flex;min-height:380px;flex-direction:column;align-items:center;justify-content:center;padding:36px 20px;border:1px dashed #cbd8fa;border-radius:22px;background:#f8faff;text-align:center}
        #vendorKonstruksiPage .empty-icon{display:flex;align-items:center;justify-content:center;width:80px;height:80px;margin-bottom:20px;border-radius:24px;background:#e7edff;color:#2450dc;font-size:44px;line-height:1}
        #vendorKonstruksiPage .vendor-empty-state h3{margin:0 0 8px;color:var(--vendor-blue);font-size:20px;font-weight:850}
        #vendorKonstruksiPage .vendor-empty-state p{margin:0 0 20px;color:var(--vendor-muted);font-size:15px}
        #vendorKonstruksiPage .compact-empty{min-height:260px}
        @media(max-width:900px){#vendorKonstruksiPage .record-content{grid-template-columns:minmax(0,1fr)}#vendorKonstruksiPage .record-head{padding:18px 20px}}
        @media(max-width:600px){#vendorKonstruksiPage .vendor-shell{padding:14px;border-radius:20px}#vendorKonstruksiPage .vendor-section-heading{align-items:flex-start;flex-direction:column}#vendorKonstruksiPage .vendor-section-heading h2{font-size:19px}#vendorKonstruksiPage .record-head{flex-direction:column;gap:12px;padding:16px}#vendorKonstruksiPage .record-identity strong{font-size:18px}#vendorKonstruksiPage .record-badges{justify-content:flex-start}#vendorKonstruksiPage .record-content{gap:12px;padding:12px}#vendorKonstruksiPage .record-panel{padding:14px}#vendorKonstruksiPage .report-checklist{grid-template-columns:minmax(0,1fr)}#vendorKonstruksiPage .history-files{margin:12px 12px 0;padding:13px}#vendorKonstruksiPage .history-edit-form{padding:16px 14px 4px}#vendorKonstruksiPage .history-actions{align-items:stretch;flex-direction:column-reverse;padding:14px}#vendorKonstruksiPage .history-actions>*{width:100%}#vendorKonstruksiPage .history-actions .vendor-primary-button,#vendorKonstruksiPage .history-actions .vendor-danger-button{width:100%}#vendorKonstruksiPage .vendor-empty-state{min-height:300px;padding:24px 16px}}
    </style>
    @endpush
@endsection
