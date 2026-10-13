@extends('layouts.app')
@section('title', 'Agenda Vendor Konstruksi')
@section('judul', 'Agenda Vendor Konstruksi')
@section('isi')
    @if (!$showHistory)
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
                                📄 {{ basename($path) ?: 'Berkas ' . ($i + 1) }}
                            </a>
                        @endforeach
                    </div>
                    <form class="vendor-report-form" method="POST" action="{{ route('vendor.konstruksi.report', $row->id) }}" enctype="multipart/form-data">
                        @csrf
                        <b>Kirim Laporan</b>
                        <label><input type="checkbox" name="pekerjaan_lengkap" value="1"> Dokumen pekerjaan lengkap</label>
                        <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1"> Pekerjaan sesuai WO</label>
                        <label><input type="checkbox" name="foto_terlampir" value="1"> Foto dokumentasi terlampir</label>
                        <label><input type="checkbox" name="siap_dilanjutkan" value="1"> Siap dilanjutkan</label>
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
    <div class="box kartu" @if (!$showHistory) hidden @endif>
        <div class="kartu-judul"><span>RIWAYAT PENGIRIMAN</span></div>
        @forelse ($riwayat as $row)
            @php($pengiriman = $row->pengirimanKonstruksi)
            <div class="history-item">
                <div>
                    <b>{{ $row->no_agenda }}</b>
                    <strong>{{ $row->nama_pelanggan }}</strong>
                    <span>ULP: {{ $row->ulp?->nama }}</span>
                    <form class="history-edit-form" method="POST" action="{{ route('vendor.konstruksi.report', $row->id) }}" enctype="multipart/form-data">
                        @csrf
                        <b>Edit Laporan</b>
                        <label><input type="checkbox" name="pekerjaan_lengkap" value="1" @checked($pengiriman?->pekerjaan_lengkap)> Dokumen lengkap</label>
                        <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1" @checked($pengiriman?->pekerjaan_sesuai_wo)> Pekerjaan sesuai WO</label>
                        <label><input type="checkbox" name="foto_terlampir" value="1" @checked($pengiriman?->foto_terlampir)> Foto terlampir</label>
                        <label><input type="checkbox" name="siap_dilanjutkan" value="1" @checked($pengiriman?->siap_dilanjutkan)> Siap dilanjutkan</label>
                        <textarea name="catatan" placeholder="Catatan">{{ $pengiriman?->catatan }}</textarea>
                        <input type="file" name="berkas_laporan[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Simpan Perubahan</button>
                    </form>
                    <form method="POST" action="{{ route('vendor.konstruksi.report.delete', $row->id) }}" onsubmit="return confirm('Hapus laporan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-hapus">Hapus Laporan</button>
                    </form>
                </div>
                <span class="pill pill-hijau">Terkirim</span>
            </div>
        @empty
            <div class="empty">Belum ada riwayat pengiriman.</div>
        @endforelse
    </div>
    <style>
        .kartu{overflow:hidden;padding:0!important;border:1px solid #d8e3ef!important;border-radius:14px!important;background:#fff!important;box-shadow:0 6px 18px rgba(13,27,140,.07)!important}
        .kartu-judul{display:flex;align-items:center;justify-content:space-between;min-height:48px;margin:0!important;padding:12px 18px!important;background:#0d1b8c!important;color:#fff!important;border-radius:13px 13px 0 0!important;font-size:14px;font-weight:800;letter-spacing:.03em}
        .kartu-judul span{color:#fff!important}.vendor-card{margin:16px;padding:18px;border:1px solid #d8e3ef;border-radius:12px;background:#fff;box-shadow:0 4px 12px rgba(13,27,140,.05)}
        .vendor-card b,.vendor-card strong,.vendor-card>span{display:block}.vendor-card>b{color:#263b53;font-size:14px}.vendor-card strong{margin:6px 0;color:#123b5d;font-size:16px}.vendor-card>span{color:#64748b;font-size:13px}
        .vendor-berkas{display:grid;gap:8px;margin-top:16px;padding:14px;border:1px solid #d8e3ef;border-radius:10px;background:#f8fbff}.vendor-berkas b{color:#334155}.vendor-file-link{display:block;width:max-content;max-width:100%;overflow:hidden;color:#2563eb;font-size:13px;font-weight:600;text-decoration:none;text-overflow:ellipsis;white-space:nowrap}.vendor-file-link:hover{color:#1d4ed8;text-decoration:underline}.empty{text-align:center;padding:32px;color:#64748b}
        .vendor-report-form,.history-edit-form{display:grid;gap:10px;margin-top:16px;padding:16px;border:1px solid #d8e3ef;border-radius:10px;background:#f8fbff}.vendor-report-form>b,.history-edit-form>b{color:#1e293b!important;font-size:14px}.vendor-report-form label,.history-edit-form label{display:flex;align-items:center;gap:8px;color:#334155!important;font-size:13px;font-weight:600}.vendor-report-form input[type=checkbox],.history-edit-form input[type=checkbox]{accent-color:#2563eb}
        .vendor-report-form textarea,.history-edit-form textarea{width:100%;min-height:80px;padding:10px;border:1px solid #cbd5e1!important;border-radius:8px;background:#fff!important;color:#1e293b!important;resize:vertical}.vendor-report-form textarea::placeholder,.history-edit-form textarea::placeholder{color:#94a3b8!important}.vendor-report-form input[type=file],.history-edit-form input[type=file]{width:100%;padding:9px;border:1px solid #cbd5e1!important;border-radius:8px;background:#fff!important;color:#475569!important}
        .vendor-report-form .btn,.history-edit-form .btn{min-height:38px;border:0;border-radius:8px;background:#2563eb!important;color:#fff!important;font-weight:700;box-shadow:0 4px 10px rgba(37,99,235,.2)}.vendor-report-form .btn:hover,.history-edit-form .btn:hover{background:#1d4ed8!important}
        .history-item{display:flex;justify-content:space-between;gap:16px;margin:16px;padding:16px;border:1px solid #d8e3ef;border-radius:12px;background:#fff}.history-item b,.history-item strong,.history-item div span{display:block}.history-item strong{margin:5px 0;color:#123b5d}.history-item div span{color:#64748b;font-size:13px}.history-item .pill{align-self:flex-start;background:#dcfce7;color:#15803d;border:0}.btn-hapus{margin-top:8px;background:#dc2626!important}.btn-hapus:hover{background:#b91c1c!important}
    </style>
@endsection
