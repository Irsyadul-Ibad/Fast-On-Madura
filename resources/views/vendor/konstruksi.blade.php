@extends('layouts.app')
@section('title', 'Agenda Vendor Konstruksi')
@section('judul', 'Agenda Vendor Konstruksi')
@section('isi')
                    @if (! $showHistory)
        <div class="box kartu">
            <div class="kartu-judul"><span>AGENDA VENDOR KONSTRUKSI</span><span class="pill">{{ $data->count() }} data</span></div>
            @forelse ($data as $row)
                <div class="vendor-card">
                    <b>{{ $row->no_agenda }}</b>
                    <strong>{{ $row->nama_pelanggan }}</strong>
                    <span>ULP: {{ $row->ulp?->nama }} · {{ $row->jenis_transaksi }}</span>
                    <div class="vendor-berkas">
                        <b>Berkas Pengiriman</b>
                        @foreach (($row->pengirimanKonstruksi?->berkas_paths ?? []) as $i => $path)
                            <a class="vendor-file-link" href="{{ route('vendor.konstruksi.delivery.file', ['pelanggan' => $row->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                📄 {{ $row->pengirimanKonstruksi?->berkas_names[$i] ?? (basename($path) ?: 'Berkas ' . ($i + 1)) }}
                            </a>
                        @endforeach
                    </div>
                    <form class="vendor-report-form" method="POST" action="{{ route('vendor.konstruksi.report', $row->id) }}" enctype="multipart/form-data">
                        @csrf
                        <b>Kirim Laporan</b>
                        <label><input type="checkbox" name="pekerjaan_lengkap" value="1"> Material Lengkap</label>
                        <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1"> Pekerjaan Dimulai</label>
                        <label><input type="checkbox" name="foto_terlampir" value="1"> Pekerjaan Selesai</label>
                        <label><input type="checkbox" name="siap_dilanjutkan" value="1"> Pekerjaan Siap Untuk Dioperasikan</label>
                        <textarea name="catatan" placeholder="Catatan (opsional)"></textarea>
                        <input type="file" name="berkas_laporan[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Kirim Laporan</button>
                    </form>
                </div>
            @empty
                <div class="empty">Belum ada agenda vendor konstruksi.</div>
            @endforelse
        </div>
    @endif
    <div class="box kartu history-panel" @if (! $showHistory) hidden @endif>
        <div class="kartu-judul"><span>RIWAYAT PENGIRIMAN</span></div>
        <div class="history-list">
        @forelse ($riwayat as $row)
            @php($pengiriman = $row->pengirimanKonstruksi)
            <div class="history-entry">
                <div class="history-item">
                    <div class="history-customer">
                        <b>{{ $row->no_agenda }}</b>
                        <strong>{{ $row->nama_pelanggan }}</strong>
                        <span>ULP: {{ $row->ulp?->nama }} · {{ $pengiriman?->laporan_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="history-badges">
                        <span class="pill pill-ungu">{{ $row->jenis_transaksi }}</span>
                        <span class="pill pill-hijau">Terkirim ke Perencanaan</span>
                    </div>
                </div>
                @if (count($pengiriman?->laporan_paths ?? []) > 0)
                    <div class="vendor-berkas history-files">
                        <b>Berkas Laporan</b>
                        @foreach (($pengiriman?->laporan_paths ?? []) as $i => $path)
                            <a class="vendor-file-link" href="{{ route('vendor.konstruksi.report.file', ['pelanggan' => $row->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                📄 {{ $pengiriman?->laporan_names[$i] ?? (basename($path) ?: 'Berkas laporan ' . ($i + 1)) }}
                            </a>
                        @endforeach
                    </div>
                @endif
                <form id="edit-laporan-{{ $row->id }}" class="history-edit-form" method="POST" action="{{ route('vendor.konstruksi.report', $row->id) }}" enctype="multipart/form-data">
                    @csrf
                    <b>Edit Laporan</b>
                    <label><input type="checkbox" name="pekerjaan_lengkap" value="1" @checked($pengiriman?->pekerjaan_lengkap)> Dokumen lengkap</label>
                    <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1" @checked($pengiriman?->pekerjaan_sesuai_wo)> Pekerjaan sesuai WO</label>
                    <label><input type="checkbox" name="foto_terlampir" value="1" @checked($pengiriman?->foto_terlampir)> Foto terlampir</label>
                    <label><input type="checkbox" name="siap_dilanjutkan" value="1" @checked($pengiriman?->siap_dilanjutkan)> Siap dilanjutkan</label>
                    <textarea name="catatan" placeholder="Catatan (opsional)">{{ $pengiriman?->catatan }}</textarea>
                    <input id="berkas-laporan-{{ $row->id }}" type="file" name="berkas_laporan[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                </form>
                <div class="history-actions-card">
                    <button type="submit" form="edit-laporan-{{ $row->id }}" class="btn">Simpan Perubahan</button>
                    <form class="history-delete-form" method="POST" action="{{ route('vendor.konstruksi.report.delete', $row->id) }}" onsubmit="return confirm('Hapus laporan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-hapus">Hapus Laporan</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty">Belum ada riwayat pengiriman.</div>
        @endforelse
        </div>
    </div>
    @push('page-styles')
    <style>
        .kartu{overflow:hidden;padding:0!important;border:1px solid #d8e3ef!important;border-radius:14px!important;background:#fff!important;box-shadow:0 6px 18px rgba(13,27,140,.07)!important}
        .kartu-judul{display:flex;align-items:center;justify-content:space-between;min-height:48px;margin:0!important;padding:12px 18px!important;background:#0d1b8c!important;color:#fff!important;border-radius:13px 13px 0 0!important;font-size:14px;font-weight:800;letter-spacing:.03em}
        .kartu-judul span{color:#fff!important}.vendor-card{margin:16px;padding:18px;border:1px solid #d8e3ef;border-radius:12px;background:#fff;box-shadow:0 4px 12px rgba(13,27,140,.05)}
        .vendor-card b,.vendor-card strong,.vendor-card>span{display:block}.vendor-card>b{color:#263b53;font-size:14px}.vendor-card strong{margin:6px 0;color:#123b5d;font-size:16px}.vendor-card>span{color:#64748b;font-size:13px}
        .vendor-berkas{display:grid;gap:8px;margin-top:16px;padding:14px;border:1px solid #d8e3ef;border-radius:10px;background:#f8fbff}.vendor-berkas b{color:#334155}.vendor-file-link{display:block;width:max-content;max-width:100%;overflow:hidden;color:#2563eb;font-size:13px;font-weight:600;text-decoration:none;text-overflow:ellipsis;white-space:nowrap}.vendor-file-link:hover{color:#1d4ed8;text-decoration:underline}.empty{text-align:center;padding:32px;color:#64748b}
        .vendor-report-form,.history-edit-form{display:grid;gap:10px;margin-top:16px;padding:16px;border:1px solid #d8e3ef;border-radius:10px;background:#f8fbff}.vendor-report-form>b,.history-edit-form>b{color:#1e293b!important;font-size:14px}.vendor-report-form label,.history-edit-form label{display:flex;align-items:center;gap:8px;color:#334155!important;font-size:13px;font-weight:600}.vendor-report-form input[type=checkbox],.history-edit-form input[type=checkbox]{accent-color:#2563eb}
        .vendor-report-form textarea,.history-edit-form textarea{width:100%;min-height:80px;padding:10px;border:1px solid #cbd5e1!important;border-radius:8px;background:#fff!important;color:#1e293b!important;resize:vertical}.vendor-report-form textarea::placeholder,.history-edit-form textarea::placeholder{color:#94a3b8!important}.vendor-report-form input[type=file],.history-edit-form input[type=file]{width:100%;padding:9px;border:1px solid #cbd5e1!important;border-radius:8px;background:#fff!important;color:#475569!important}
        .vendor-report-form .btn,.history-edit-form .btn{min-height:38px;border:0;border-radius:8px;background:#2563eb!important;color:#fff!important;font-weight:700;box-shadow:0 4px 10px rgba(37,99,235,.2)}.vendor-report-form .btn:hover,.history-edit-form .btn:hover{background:#1d4ed8!important}
        .history-panel{margin-top:16px;overflow:hidden;padding:0!important;border:1px solid #d6e2ee!important;border-radius:14px!important;background:#fff!important;box-shadow:0 6px 18px rgba(13,27,140,.07)!important}
        .history-panel>.kartu-judul{display:flex;align-items:center;justify-content:space-between;min-height:45px;margin:0!important;padding:12px 16px!important;background:#0d1b8c!important;color:#fff!important;border-radius:12px 12px 0 0!important;font-size:13px;font-weight:800;letter-spacing:.03em}
        .history-panel>.kartu-judul span{color:#fff!important}
        .history-list{display:grid;gap:14px;padding:14px 16px 16px}
        .history-entry{display:grid;gap:10px;min-width:0}
        .history-item{display:flex;justify-content:space-between;align-items:center;gap:16px;min-width:0;margin:0;padding:14px 16px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff}
        .history-customer{min-width:0}.history-customer b,.history-customer strong,.history-customer span{display:block}
        .history-customer b,.history-customer strong{color:#123b5d!important}.history-customer b{font-size:13px}.history-customer strong{margin:4px 0;font-size:14px;overflow-wrap:anywhere}.history-customer span{color:#64748b!important;font-size:12px}
        .history-badges{display:flex;align-items:flex-end;flex-direction:column;gap:8px;flex:0 0 auto}
        .history-badges .pill{display:inline-flex;align-items:center;justify-content:center;min-height:28px;padding:6px 10px!important;border:0;border-radius:999px!important;font-size:10px!important;font-weight:800;white-space:nowrap}
        .history-badges .pill-ungu{background:#eaf3ff!important;color:#0d1b8c!important}.history-badges .pill-hijau{background:#e8f7ef!important;color:#237a53!important}
        .history-files{margin:0;padding:12px 14px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff}
        .history-edit-form{display:grid;gap:10px;min-width:0;margin:0;padding:15px;border:1px solid #d6e2ee!important;border-radius:10px;background:#f8fbff!important}
        .history-edit-form>b{color:#123b5d!important;font-size:13px}.history-edit-form label{display:flex;align-items:center;gap:8px;min-width:0;color:#334155!important;font-size:12px;line-height:1.4}
        .history-edit-form label input[type=checkbox]{flex:0 0 auto;width:15px;height:15px;margin:0;accent-color:#0d1b8c}
        .history-edit-form textarea,.history-edit-form input[type=file]{box-sizing:border-box;width:100%;min-width:0;padding:9px;border:1px solid #b8c9da!important;border-radius:8px;background:#fff!important;color:#334155!important}
        .history-edit-form textarea{min-height:70px;resize:vertical}.history-edit-form textarea::placeholder{color:#94a3b8!important}
        .history-edit-form input[type=file]::file-selector-button{margin-right:8px;padding:5px 9px;border:1px solid #b8c9da;border-radius:6px;background:#f1f5f9;color:#334155;cursor:pointer}
        .history-edit-form .btn{min-height:36px;border:0;border-radius:8px;background:#0d1b8c!important;color:#fff!important;font-weight:700;box-shadow:0 4px 10px rgba(13,27,140,.18)}.history-edit-form .btn:hover{background:#091267!important}
        .history-actions-card{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:12px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff}
        .history-actions-card>.btn,.history-delete-form .btn-hapus{box-sizing:border-box;width:100%;min-height:38px;margin:0;border:0;border-radius:8px;font-weight:700}
        .history-actions-card>.btn{background:#0d1b8c!important;color:#fff!important}.history-actions-card>.btn:hover{background:#091267!important}
        .history-delete-form{min-width:0;margin:0}.history-delete-form .btn-hapus{background:#c23f3a!important;color:#fff!important}.history-delete-form .btn-hapus:hover{background:#a9322e!important}
        @media(max-width:600px){.history-list{gap:12px;padding:12px}.history-item{align-items:flex-start;flex-direction:column;gap:10px;padding:12px}.history-badges{align-items:flex-start;flex-direction:row;flex-wrap:wrap}.history-edit-form{padding:12px}.history-actions-card{grid-template-columns:minmax(0,1fr);gap:10px;padding:10px}}
    </style>
    @endpush
@endsection
