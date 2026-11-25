@extends('layouts.app')

@section('title', $showHistory ? 'Riwayat Pengiriman' : 'Agenda Vendor Tiang')
@section('judul', $showHistory ? 'Riwayat Pengiriman' : 'Agenda Vendor Tiang')
@section('deskripsi', $showHistory ? 'Laporan yang sudah dikirim ke Perencanaan' : 'Daftar agenda tiang yang ditugaskan kepada mitra vendor')
@section('header-actions')
    <span class="vendor-count-header">{{ $showHistory ? $riwayat->count() : $data->count() }} data</span>
@endsection

@section('isi')
    <div id="vendorTiangPage" class="vendor-page">
        @if (session('success'))
            <div class="vendor-flash" role="status">{{ session('success') }}</div>
        @endif

        @if (!$showHistory)
            <section class="vendor-shell">
                @forelse ($data as $row)
                    @php $kirim = $row->pengirimanVendor; @endphp
                    <article class="vendor-record">
                        <header class="record-head">
                            <div class="record-identity">
                                <b>{{ $row->no_agenda }}</b>
                                <strong>{{ $row->nama_pelanggan }}</strong>
                                <span>ULP: {{ $row->ulp?->nama }} · {{ $row->jenis_transaksi }}</span>
                            </div>
                            <div class="record-badges">
                                <span class="vendor-badge badge-transaction">{{ $row->jenis_transaksi }}</span>
                                @if (filled($kirim?->status_kelayakan))
                                    <span class="vendor-badge {{ $kirim->status_kelayakan === 'LAYAK' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $kirim->status_kelayakan }}
                                    </span>
                                @endif
                            </div>
                        </header>

                        <div class="record-content">
                            <section class="record-panel work-files">
                                <h3>Berkas Pekerjaan</h3>
                                @if ($kirim?->wo_tiang_path)
                                    <a class="vendor-file-link" href="{{ route('vendor.tiang.wo', $row->id) }}" target="_blank" rel="noopener">
                                        <span class="file-icon" aria-hidden="true">▤</span>
                                        <span>{{ $kirim->wo_tiang_name ?: basename($kirim->wo_tiang_path) }}</span>
                                        <span class="download-icon" aria-hidden="true">↓</span>
                                    </a>
                                @else
                                    <p class="muted-note">Belum ada berkas WO tiang.</p>
                                @endif
                            </section>

                            <form method="POST" action="{{ route('vendor.tiang.report', $row->id) }}" enctype="multipart/form-data" class="record-panel vendor-report-form">
                                @csrf
                                <h3>Kirim Laporan</h3>
                                <div class="report-checklist">
                                    <label><input type="checkbox" name="pekerjaan_lengkap" value="1"> <span>Foto pengiriman tiang</span></label>
                                    <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1"> <span>Foto penanaman tiang</span></label>
                                    <label><input type="checkbox" name="foto_terlampir" value="1"> <span>Foto tiang berdiri</span></label>
                                    <label><input type="checkbox" name="siap_dilanjutkan" value="1"> <span>Foto pekerjaan selesai</span></label>
                                </div>
                                <label class="field-label" for="catatan-tiang-{{ $row->id }}">Catatan (opsional)</label>
                                <textarea id="catatan-tiang-{{ $row->id }}" name="catatan" placeholder="Tulis catatan bila diperlukan"></textarea>
                                <label class="file-drop">
                                    <span class="file-picker">↑ &nbsp; Pilih file</span>
                                    <span class="file-name">Belum ada file dipilih</span>
                                    <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                </label>
                                <button type="submit" class="vendor-primary-button">➤ &nbsp; Kirim ke Perencanaan</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="vendor-empty-state">
                        <span class="empty-icon" aria-hidden="true">⌂</span>
                        <h3>Belum ada agenda vendor tiang</h3>
                        <p>Agenda baru akan muncul di sini setelah ditugaskan oleh ULP.</p>
                        <a class="vendor-outline-button" href="{{ route('vendor.tiang.history') }}">◷ &nbsp; Lihat riwayat pengiriman</a>
                    </div>
                @endforelse
            </section>
        @else
            <section class="vendor-shell history-shell">
                @forelse ($riwayat as $row)
                    @php $laporan = $row->laporanVendor; @endphp
                    <article class="history-entry">
                        <header class="record-head history-head">
                            <div class="record-identity">
                                <b>{{ $row->no_agenda }}</b>
                                <strong>{{ $row->nama_pelanggan }}</strong>
                                <span>ULP: {{ $row->ulp?->nama }} · {{ $laporan?->dikirim_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="record-badges">
                                <span class="vendor-badge badge-transaction">➤ &nbsp; Terkirim ke Perencanaan</span>
                                @if (filled($row->pengirimanVendor?->status_kelayakan))
                                    <span class="vendor-badge {{ $row->pengirimanVendor->status_kelayakan === 'LAYAK' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $row->pengirimanVendor->status_kelayakan }}
                                    </span>
                                @endif
                            </div>
                        </header>

                        <form id="edit-laporan-tiang-{{ $row->id }}" method="POST" action="{{ route('vendor.tiang.report', $row->id) }}" enctype="multipart/form-data" class="history-edit-form">
                            @csrf
                            <h3>Edit Laporan</h3>
                            <div class="report-checklist">
                                <label><input type="checkbox" name="pekerjaan_lengkap" value="1" @checked($laporan?->pekerjaan_lengkap)> <span>Dokumen pekerjaan lengkap</span></label>
                                <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1" @checked($laporan?->pekerjaan_sesuai_wo)> <span>Pekerjaan sesuai WO</span></label>
                                <label><input type="checkbox" name="foto_terlampir" value="1" @checked($laporan?->foto_terlampir)> <span>Foto dokumentasi terlampir</span></label>
                                <label><input type="checkbox" name="siap_dilanjutkan" value="1" @checked($laporan?->siap_dilanjutkan)> <span>Siap ditindaklanjuti Perencanaan</span></label>
                            </div>
                            <label class="field-label" for="catatan-edit-tiang-{{ $row->id }}">Catatan (opsional)</label>
                            <textarea id="catatan-edit-tiang-{{ $row->id }}" name="catatan" placeholder="Tulis catatan bila diperlukan">{{ $laporan?->catatan }}</textarea>
                            <label class="file-drop">
                                <span class="file-picker">↑ &nbsp; Pilih file</span>
                                <span class="file-name">Pilih berkas baru untuk ditambahkan</span>
                                <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            </label>
                        </form>
                        <footer class="history-actions">
                            <form method="POST" action="{{ route('vendor.tiang.report.delete', $row->id) }}" class="history-delete-form" onsubmit="return confirm('Hapus laporan Vendor Tiang ini? Data hasil Perencanaan tidak ikut dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="vendor-danger-button">♲ &nbsp; Hapus laporan</button>
                            </form>
                            <button type="submit" form="edit-laporan-tiang-{{ $row->id }}" class="vendor-primary-button">✓ &nbsp; Simpan perubahan</button>
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

    @include('vendor.partials._file-picker-script')

    @push('page-styles')
    <style>
        #vendorTiangPage{--vendor-blue:#1739b7;--vendor-bright:#2450e6;--vendor-ink:#202b55;--vendor-muted:#60719f;--vendor-line:#dce5fb;--vendor-soft:#f5f7ff;box-sizing:border-box;width:100%;min-width:0;color:var(--vendor-ink)}
        #vendorTiangPage *,#vendorTiangPage *:before,#vendorTiangPage *:after{box-sizing:border-box}
        #vendorTiangPage .vendor-flash{margin:0 0 16px;padding:14px 18px;border:1px solid #b8e5c9;border-radius:14px;background:#e9f8ef;color:#176b3b;font-weight:700}
        .page-topbar .page-actions:has(.vendor-count-header){align-self:flex-start;padding-top:4px}
        #vendorTiangPage~*{box-sizing:border-box}
        .vendor-count-header{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 16px;border-radius:999px;background:#e4ebff;color:#1b43c4;font-size:14px;font-weight:800;white-space:nowrap}
        #vendorTiangPage .vendor-shell{min-width:0;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
        #vendorTiangPage .vendor-section-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:0 0 20px}
        #vendorTiangPage .vendor-section-heading h2{margin:0;color:var(--vendor-blue);font-size:22px;font-weight:850;line-height:1.25}
        #vendorTiangPage .vendor-section-heading p{margin:4px 0 0;color:var(--vendor-muted);font-size:15px}
        #vendorTiangPage .vendor-count{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 16px;border-radius:999px;background:#e7edff;color:#1b43c4;font-size:14px;font-weight:800;white-space:nowrap}
        #vendorTiangPage .vendor-record,#vendorTiangPage .history-entry{min-width:0;margin:0 0 18px;overflow:hidden;border:1px solid var(--vendor-line);border-radius:22px;background:#fff}
        #vendorTiangPage .vendor-record:last-child,#vendorTiangPage .history-entry:last-child{margin-bottom:0}
        #vendorTiangPage .record-head{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;padding:20px 24px;background:#f2f5ff;border-bottom:1px solid var(--vendor-line)}
        #vendorTiangPage .record-identity{min-width:0}
        #vendorTiangPage .record-identity b,#vendorTiangPage .record-identity strong,#vendorTiangPage .record-identity span{display:block}
        #vendorTiangPage .record-identity b{color:#2449c0;font-size:15px;font-weight:800;overflow-wrap:anywhere}
        #vendorTiangPage .record-identity strong{margin:4px 0;color:var(--vendor-blue);font-size:21px;font-weight:850;line-height:1.25;overflow-wrap:anywhere}
        #vendorTiangPage .record-identity span{color:var(--vendor-muted);font-size:14px}
        #vendorTiangPage .record-badges{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;flex:0 0 auto}
        #vendorTiangPage .vendor-badge{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:7px 13px;border:1px solid transparent;border-radius:999px;font-size:12px;font-weight:850;line-height:1.2;white-space:nowrap}
        #vendorTiangPage .badge-transaction{background:#e6edff;color:#2148be}
        #vendorTiangPage .badge-success{background:#dcf5e8;color:#187344}
        #vendorTiangPage .badge-danger{background:#ffe5e5;color:#b92d25}
        #vendorTiangPage .record-content{display:grid;grid-template-columns:minmax(230px,.85fr) minmax(0,1.8fr);gap:18px;padding:20px 24px 24px}
        #vendorTiangPage .record-panel{min-width:0;margin:0;padding:18px;border:1px solid var(--vendor-line);border-radius:18px;background:#fff}
        #vendorTiangPage .record-panel h3,#vendorTiangPage .history-edit-form h3{margin:0 0 14px;color:#5b6d9b;font-size:14px;font-weight:900;letter-spacing:.09em;text-transform:uppercase}
        #vendorTiangPage .work-files{align-content:start}
        #vendorTiangPage .vendor-file-link{display:flex;align-items:center;gap:11px;min-width:0;padding:13px;border-radius:14px;background:#eaf0ff;color:#1c45be;text-decoration:none;font-weight:750}
        #vendorTiangPage .vendor-file-link>span:nth-child(2){min-width:0;overflow-wrap:anywhere}
        #vendorTiangPage .file-icon,#vendorTiangPage .download-icon{display:inline-flex;align-items:center;justify-content:center;flex:0 0 42px;width:42px;height:42px;border-radius:12px;background:#fff;color:#2450d7;font-size:24px;font-weight:800}
        #vendorTiangPage .download-icon{flex-basis:38px;width:38px;height:38px;margin-left:auto;color:#334672;font-size:23px}
        #vendorTiangPage .muted-note{margin:0;color:var(--vendor-muted);font-size:14px}
        #vendorTiangPage .vendor-report-form,#vendorTiangPage .history-edit-form{display:grid;gap:13px}
        #vendorTiangPage .report-checklist{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
        #vendorTiangPage .report-checklist label{display:flex;align-items:center;gap:11px;min-width:0;min-height:52px;margin:0;padding:12px 14px;border:1px solid #d8e2fa;border-radius:14px;background:#fff;color:#2d3c69;font-size:14px;font-weight:650;line-height:1.35;cursor:pointer}
        #vendorTiangPage .report-checklist label:has(input:checked){border-color:#2852e8;background:#edf2ff}
        #vendorTiangPage .report-checklist input[type=checkbox]{flex:0 0 20px;width:20px;height:20px;margin:0;accent-color:#2450df}
        #vendorTiangPage .field-label{margin:0 0 -7px;color:#53648f;font-size:13px;font-weight:750}
        #vendorTiangPage textarea{display:block;width:100%;min-width:0;min-height:88px;padding:12px 14px;border:1px solid #d2def8!important;border-radius:14px;background:#f7f9ff!important;color:var(--vendor-ink)!important;font:inherit;resize:vertical}
        #vendorTiangPage textarea::placeholder{color:#7180a7}
        #vendorTiangPage .file-drop{position:relative;display:flex;align-items:center;gap:14px;min-width:0;min-height:64px;padding:10px 13px;border:1px dashed #b8caff;border-radius:15px;background:#f8faff;cursor:pointer}
        #vendorTiangPage .file-drop input[type=file]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
        #vendorTiangPage .file-picker{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:8px 14px;border:1px solid #c5d3ff;border-radius:12px;background:#fff;color:#173db6;font-size:14px;font-weight:800;white-space:nowrap}
        #vendorTiangPage .file-name{min-width:0;color:var(--vendor-muted);font-size:14px;overflow-wrap:anywhere}
        #vendorTiangPage .vendor-primary-button,#vendorTiangPage .vendor-outline-button,#vendorTiangPage .vendor-danger-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:11px 18px;border:1px solid transparent;border-radius:14px;font:inherit;font-size:15px;font-weight:800;text-decoration:none;cursor:pointer}
        #vendorTiangPage .vendor-primary-button{background:linear-gradient(120deg,#2452e5,#1234a8);color:#fff;box-shadow:0 10px 22px rgba(26,65,188,.22)}
        #vendorTiangPage .vendor-primary-button:hover{filter:brightness(.96)}
        #vendorTiangPage .vendor-outline-button{border-color:#c4d1ff;background:#fff;color:#183cae}
        #vendorTiangPage .vendor-danger-button{border-color:#f2b9b6;background:#fff;color:#b92d25}
        #vendorTiangPage .history-shell .history-entry{margin-bottom:18px}
        #vendorTiangPage .history-shell .history-entry:last-child{margin-bottom:0}
        #vendorTiangPage .history-edit-form{padding:20px 24px 8px;border:0;background:#fff}
        #vendorTiangPage .history-edit-form h3{margin-bottom:0}
        #vendorTiangPage .history-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 24px 22px}
        #vendorTiangPage .history-delete-form{margin:0}
        #vendorTiangPage .vendor-empty-state{display:flex;min-height:380px;flex-direction:column;align-items:center;justify-content:center;padding:36px 20px;border:1px dashed #cbd8fa;border-radius:22px;background:#f8faff;text-align:center}
        #vendorTiangPage .empty-icon{display:flex;align-items:center;justify-content:center;width:80px;height:80px;margin-bottom:20px;border-radius:24px;background:#e7edff;color:#2450dc;font-size:44px;line-height:1}
        #vendorTiangPage .vendor-empty-state h3{margin:0 0 8px;color:var(--vendor-blue);font-size:20px;font-weight:850}
        #vendorTiangPage .vendor-empty-state p{margin:0 0 20px;color:var(--vendor-muted);font-size:15px}
        #vendorTiangPage .compact-empty{min-height:260px}
        @media(max-width:900px){#vendorTiangPage .record-content{grid-template-columns:minmax(0,1fr)}#vendorTiangPage .record-head{padding:18px 20px}}
        @media(max-width:600px){#vendorTiangPage .vendor-section-heading{align-items:flex-start;flex-direction:column}#vendorTiangPage .vendor-section-heading h2{font-size:19px}#vendorTiangPage .record-head{flex-direction:column;gap:12px;padding:16px}#vendorTiangPage .record-identity strong{font-size:18px}#vendorTiangPage .record-badges{justify-content:flex-start}#vendorTiangPage .record-content{gap:12px;padding:12px}#vendorTiangPage .record-panel{padding:14px}#vendorTiangPage .report-checklist{grid-template-columns:minmax(0,1fr)}#vendorTiangPage .history-edit-form{padding:16px 14px 4px}#vendorTiangPage .history-actions{align-items:stretch;flex-direction:column-reverse;padding:14px}#vendorTiangPage .history-actions>*{width:100%}#vendorTiangPage .history-actions .vendor-primary-button,#vendorTiangPage .history-actions .vendor-danger-button{width:100%}#vendorTiangPage .vendor-empty-state{min-height:300px;padding:24px 16px}}
    </style>
    @endpush
@endsection
