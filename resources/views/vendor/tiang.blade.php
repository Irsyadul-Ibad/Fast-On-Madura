@extends('layouts.app')

@section('title', 'Agenda Vendor Tiang')
@section('judul', 'Agenda Vendor Tiang')

@section('isi')
    @if (session('success'))
        <div class="box" style="color:#176b35">{{ session('success') }}</div>
    @endif

    @if (!$showHistory)
    <div class="box kartu">
        <div class="kartu-judul">
            <span>AGENDA VENDOR TIANG</span>
            <span class="pill">{{ $data->count() }} data</span>
        </div>

        @forelse ($data as $row)
            @php $kirim = $row->pengirimanVendor; @endphp
            <div class="vendor-card">
                <div class="vendor-card-head">
                    <div>
                        <b>{{ $row->no_agenda }}</b>
                        <strong>{{ $row->nama_pelanggan }}</strong>
                        <span>ULP: {{ $row->ulp?->nama }} - {{ $row->jenis_transaksi }}</span>
                    </div>
                    <div class="vendor-card-badges">
                        <span class="pill pill-ungu">{{ $row->jenis_transaksi }}</span>
                        @if (filled($kirim?->status_kelayakan))
                            <span class="pill vendor-eligibility {{ $kirim->status_kelayakan === 'LAYAK' ? 'is-eligible' : 'is-not-eligible' }}">
                                {{ $kirim->status_kelayakan }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="vendor-berkas">
                    <b>Berkas Pekerjaan</b>
                    @if ($kirim?->wo_tiang_path)
                        <a href="{{ route('vendor.tiang.wo', $row->id) }}" target="_blank">📄 {{ $kirim->wo_tiang_name ?: basename($kirim->wo_tiang_path) }}</a>
                    @else
                        <span>Belum ada berkas WO tiang.</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('vendor.tiang.report', $row->id) }}" enctype="multipart/form-data" class="vendor-report">
                    @csrf
                    <b>Kirim Laporan</b>
                    <label><input type="checkbox" name="pekerjaan_lengkap" value="1"> Foto Pengiriman tiang</label>
                    <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1"> Foto penanaman tiang</label>
                    <label><input type="checkbox" name="foto_terlampir" value="1"> Foto tiang berdiri</label>
                    <label><input type="checkbox" name="siap_dilanjutkan" value="1"> Foto pekerjaan selesai</label>
                    <textarea name="catatan" placeholder="Catatan (opsional)"></textarea>
                    <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <button type="submit" class="btn">Kirim ke Perencanaan</button>
                </form>
            </div>
        @empty
            <div style="text-align:center;padding:30px">Belum ada agenda vendor tiang.</div>
        @endforelse
    </div>
    @endif

    <div id="historyPanel" class="box history-panel" @if (!$showHistory) hidden @endif>
        <div class="kartu-judul">
            <span>RIWAYAT PENGIRIMAN</span>
        </div>
        @forelse ($riwayat as $row)
            @php $laporan = $row->laporanVendor; @endphp
            <div class="history-item">
                <div>
                    <b>{{ $row->no_agenda }}</b>
                    <strong>{{ $row->nama_pelanggan }}</strong>
                    <span>ULP: {{ $row->ulp?->nama }} · {{ $laporan?->dikirim_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="history-badges">
                    <span class="pill pill-hijau">Terkirim ke Perencanaan</span>
                    @if (filled($row->pengirimanVendor?->status_kelayakan))
                        <span class="pill vendor-eligibility {{ $row->pengirimanVendor->status_kelayakan === 'LAYAK' ? 'is-eligible' : 'is-not-eligible' }}">
                            {{ $row->pengirimanVendor->status_kelayakan }}
                        </span>
                    @endif
                </div>
            </div>
            <form id="edit-laporan-tiang-{{ $row->id }}" method="POST" action="{{ route('vendor.tiang.report', $row->id) }}" enctype="multipart/form-data" class="history-edit-form">
                @csrf
                <b>Edit Laporan</b>
                <label><input type="checkbox" name="pekerjaan_lengkap" value="1" @checked($laporan?->pekerjaan_lengkap)> Dokumen pekerjaan lengkap</label>
                <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1" @checked($laporan?->pekerjaan_sesuai_wo)> Pekerjaan sesuai WO</label>
                <label><input type="checkbox" name="foto_terlampir" value="1" @checked($laporan?->foto_terlampir)> Foto dokumentasi terlampir</label>
                <label><input type="checkbox" name="siap_dilanjutkan" value="1" @checked($laporan?->siap_dilanjutkan)> Siap ditindaklanjuti Perencanaan</label>
                <textarea name="catatan" placeholder="Catatan (opsional)">{{ $laporan?->catatan }}</textarea>
                <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
            </form>
            <div class="history-actions-card">
                <button type="submit" form="edit-laporan-tiang-{{ $row->id }}" class="btn">Simpan Perubahan</button>
                <form method="POST" action="{{ route('vendor.tiang.report.delete', $row->id) }}" class="history-delete-form" onsubmit="return confirm('Hapus laporan Vendor Tiang ini? Data hasil Perencanaan tidak ikut dihapus.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-hapus">Hapus Laporan</button>
                </form>
            </div>
        @empty
            <div style="text-align:center;padding:24px">Belum ada riwayat pengiriman.</div>
        @endforelse
    </div>

    @push('page-styles')
    <style>
        .vendor-card { margin-top:16px; padding:18px; border:1px solid #ccd8e5; border-radius:10px; background:#f8fbfe; }
        .vendor-card-head { display:flex; justify-content:space-between; gap:16px; padding-bottom:14px; border-bottom:1px solid #dce5ee; }
        .vendor-card-head b, .vendor-card-head strong, .vendor-card-head span { display:block; }
        .vendor-card-head strong { margin:5px 0; color:#0b3d6b; }
        .vendor-card-head span { color:#536477; font-size:13px; }
        .vendor-berkas, .vendor-report { display:grid; gap:10px; margin-top:14px; padding:16px; border:1px solid #ccd8e5; border-radius:8px; background:#fff; }
        .vendor-berkas a { padding:10px; border-radius:6px; background:#eef3f8; color:#0b3d6b; text-decoration:none; }
        .vendor-berkas span { color:#718096; font-size:13px; }
        .vendor-report label { color:#536477; font-size:13px; }
        .vendor-report textarea { min-height:70px; padding:9px; border:1px solid #b9c7d5; border-radius:6px; }
        .vendor-report input[type=file] { padding:8px; border:1px solid #b9c7d5; border-radius:6px; }
        .history-panel { margin-top:16px; }
        .history-panel[hidden] { display:none; }
        .history-item { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:14px 0; border-bottom:1px solid #dce5ee; }
        .history-item:last-child { border-bottom:0; }
        .history-item b, .history-item strong, .history-item span { display:block; }
        .history-item strong { margin:4px 0; color:#0b3d6b; }
        .history-item div span { color:#637487; font-size:13px; }
        .history-edit-form { display:grid; gap:9px; margin:0 0 16px; padding:14px; border:1px solid #dce5ee; border-radius:8px; background:#fff; }
        .history-edit-form label { color:#536477; font-size:13px; }
        .history-edit-form textarea { min-height:60px; padding:9px; border:1px solid #b9c7d5; border-radius:6px; }
        .history-edit-form input[type=file] { padding:8px; border:1px solid #b9c7d5; border-radius:6px; }
        .vendor-card{margin-top:16px;padding:0;overflow:hidden;border:1px solid #d6e2ee;border-radius:14px;background:#fff;box-shadow:0 6px 18px rgba(13,27,140,.07)}
        .vendor-card-head{align-items:flex-start;padding:16px 18px;background:#0d1b8c;border-bottom:0;color:#fff}
        .vendor-card-head b,.vendor-card-head strong{color:#fff!important}
        .vendor-card-head b{font-size:13px;letter-spacing:.01em}
        .vendor-card-head strong{margin:5px 0;font-size:14px}
        .vendor-card-head span{color:#dbeafe;font-size:12px}
        .vendor-card-head>.pill,.vendor-card-badges>.pill-ungu{display:inline-flex!important;align-items:center;justify-content:center;min-width:118px;min-height:34px;padding:7px 12px!important;border:1px solid rgba(255,255,255,.35)!important;border-radius:999px!important;background:#eaf3ff!important;color:#0d1b8c!important;font-size:11px!important;font-weight:800;white-space:nowrap}
        .vendor-card-badges,.history-badges{display:flex;align-items:flex-end;flex-direction:column;gap:8px}
        .vendor-card-badges .pill,.history-badges .pill{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:6px 11px;border-radius:999px;font-size:10px;font-weight:800;white-space:nowrap}
        .history-badges .pill{box-sizing:border-box;width:156px}
        .vendor-card-badges .vendor-eligibility,.history-badges .vendor-eligibility{border:1px solid transparent}
        .vendor-eligibility.is-eligible{background:#e8f7ef!important;color:#237a53!important;border-color:#b9e4ca!important}
        .vendor-eligibility.is-not-eligible{background:#fff0ef!important;color:#c23f3a!important;border-color:#f1c1bd!important}
        .vendor-berkas,.vendor-report{margin:14px 16px;padding:15px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff;box-shadow:none}
        .vendor-berkas b,.vendor-report>b{color:#123b5d;font-size:13px}
        .vendor-berkas a{display:flex;align-items:center;gap:7px;width:100%;min-width:0;box-sizing:border-box;padding:10px 12px;border:1px solid #c8def5;border-radius:8px;background:#eaf3ff;color:#1e6fa8;font-size:12px;font-weight:700;line-height:1.45;text-decoration:none;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
        .vendor-berkas a:hover{background:#1e6fa8;color:#fff}
        .vendor-berkas span{color:#64748b;font-size:12px}
        .vendor-report label{display:flex;align-items:center;gap:8px;color:#334155;font-size:12px;line-height:1.4}
        .vendor-report label input[type=checkbox]{width:15px;height:15px;accent-color:#0d1b8c}
        .vendor-report textarea,.history-edit-form textarea{min-height:72px;padding:10px;border:1px solid #b8c9da;border-radius:8px;background:#fff;color:#334155;font:inherit;resize:vertical}
        .vendor-report textarea::placeholder,.history-edit-form textarea::placeholder{color:#94a3b8}
        .vendor-report input[type=file],.history-edit-form input[type=file]{padding:8px;border:1px solid #b8c9da;border-radius:8px;background:#fff;color:#475569;font-size:12px}
        .vendor-report textarea,.history-edit-form textarea,.vendor-report input[type=file],.history-edit-form input[type=file]{background:#fff!important;color:#334155!important;border:1px solid #b8c9da!important}
        .vendor-report label,.history-edit-form label{color:#334155!important}
        .vendor-report input[type=file]::file-selector-button,.history-edit-form input[type=file]::file-selector-button{margin-right:8px;padding:5px 9px;border:1px solid #b8c9da;border-radius:6px;background:#f1f5f9;color:#334155;cursor:pointer}
        .vendor-report .btn,.history-edit-form .btn{min-height:36px;border:0;border-radius:8px;background:#0d1b8c;color:#fff;font-weight:700;box-shadow:0 4px 10px rgba(13,27,140,.18)}
        .vendor-report .btn:hover,.history-edit-form .btn:hover{background:#091267}
        .history-item{padding:14px 16px;background:#f8fbff;border:1px solid #d6e2ee;border-radius:10px}
        .history-edit-form{margin:10px 16px 16px;padding:15px;border-color:#d6e2ee;border-radius:10px;background:#f8fbff}
        .history-panel{overflow:hidden;padding:0!important;border:1px solid #d6e2ee!important;border-radius:14px!important;background:#fff!important;box-shadow:0 6px 18px rgba(13,27,140,.07)!important}
        .history-panel>.kartu-judul{display:flex;align-items:center;justify-content:space-between;min-height:45px;margin:0!important;padding:12px 16px!important;background:#0d1b8c!important;color:#fff!important;border-radius:12px 12px 0 0!important;font-size:13px;font-weight:800;letter-spacing:.03em}
        .history-panel>.kartu-judul span{color:#fff!important}
        .history-item{margin:14px 16px 0;padding:14px 16px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff}
        .history-item b,.history-item strong{color:#123b5d!important}
        .history-item div span{color:#64748b!important;font-size:12px}
        .history-item>.pill{display:inline-flex;align-items:center;min-height:28px;padding:6px 10px!important;border-radius:999px!important;background:#eaf3ff!important;color:#1e6fa8!important;font-size:10px!important;font-weight:800}
        .history-edit-form{gap:10px;margin:10px 16px 16px;padding:15px;border:1px solid #d6e2ee!important;border-radius:10px;background:#f8fbff!important}
        .history-edit-form>b{color:#123b5d;font-size:13px}
        .history-edit-form label{display:flex;align-items:center;gap:8px;color:#334155!important;font-size:12px}
        .history-edit-form label input[type=checkbox]{width:15px;height:15px;accent-color:#0d1b8c}
        .history-panel,.history-panel *{box-sizing:border-box}
        .history-panel{width:100%;max-width:100%;min-width:0}
        .history-item,.history-edit-form,.history-actions-card{min-width:0;max-width:100%}
        .history-edit-form textarea,.history-edit-form input[type=file]{display:block;width:100%;min-width:0;max-width:100%;background:#fff!important;color:#334155!important;border:1px solid #b8c9da!important;border-radius:8px}
        .history-edit-form textarea::placeholder{color:#94a3b8}
        .history-actions-card{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0 16px 16px;padding:12px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff}
        .history-actions-card>.btn,.history-delete-form .btn-hapus{box-sizing:border-box;width:100%;min-height:38px;margin:0;border:0;border-radius:8px;font-weight:700}
        .history-actions-card>.btn{background:#0d1b8c!important;color:#fff!important}.history-actions-card>.btn:hover{background:#091267!important}
        .history-delete-form{min-width:0;margin:0}.history-delete-form .btn-hapus{background:#c23f3a!important;color:#fff!important}.history-delete-form .btn-hapus:hover{background:#a9322e!important}
        @media(max-width:600px){.history-item{gap:10px;padding:12px}.history-actions-card{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin:0 12px 12px;padding:10px}.history-actions-card>.btn,.history-delete-form .btn-hapus{min-width:0;padding:8px 5px;font-size:11px;line-height:1.25;white-space:normal}}
        @media (max-width:600px) { .history-item { align-items:flex-start; flex-direction:column; }.history-badges{align-items:flex-start;width:100%}.history-badges .pill{max-width:100%} }
    </style>
    @endpush
@endsection
