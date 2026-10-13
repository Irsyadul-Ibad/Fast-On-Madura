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
                    <span class="pill pill-ungu">{{ $row->jenis_transaksi }}</span>
                </div>

                <div class="vendor-berkas">
                    <b>Berkas Pekerjaan</b>
                    @if ($kirim?->wo_tiang_path)
                        <a href="{{ route('vendor.tiang.wo', $row->id) }}" target="_blank">📄 WO Tiang</a>
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
                <span class="pill pill-hijau">Terkirim ke Perencanaan</span>
            </div>
            <form method="POST" action="{{ route('vendor.tiang.report', $row->id) }}" enctype="multipart/form-data" class="history-edit-form">
                @csrf
                <b>Edit Laporan</b>
                <label><input type="checkbox" name="pekerjaan_lengkap" value="1" @checked($laporan?->pekerjaan_lengkap)> Dokumen pekerjaan lengkap</label>
                <label><input type="checkbox" name="pekerjaan_sesuai_wo" value="1" @checked($laporan?->pekerjaan_sesuai_wo)> Pekerjaan sesuai WO</label>
                <label><input type="checkbox" name="foto_terlampir" value="1" @checked($laporan?->foto_terlampir)> Foto dokumentasi terlampir</label>
                <label><input type="checkbox" name="siap_dilanjutkan" value="1" @checked($laporan?->siap_dilanjutkan)> Siap ditindaklanjuti Perencanaan</label>
                <textarea name="catatan" placeholder="Catatan (opsional)">{{ $laporan?->catatan }}</textarea>
                <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                <button type="submit" class="btn">Simpan Perubahan</button>
            </form>
        @empty
            <div style="text-align:center;padding:24px">Belum ada riwayat pengiriman.</div>
        @endforelse
    </div>

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
        .history-button { float:right; margin-top:-48px; padding:9px 14px; border:0; border-radius:8px; background:#2947a8; color:#fff; cursor:pointer; }
        .history-panel { margin-top:16px; }
        .history-panel[hidden] { display:none; }
        .history-close { padding:6px 10px; border:0; border-radius:6px; background:#e8eef5; color:#223; cursor:pointer; }
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
        .vendor-card-head>.pill{display:inline-flex!important;align-items:center;justify-content:center;min-width:118px;min-height:34px;padding:7px 12px!important;border:1px solid rgba(255,255,255,.35)!important;border-radius:999px!important;background:#eaf3ff!important;color:#0d1b8c!important;font-size:11px!important;font-weight:800;white-space:nowrap}
        .vendor-berkas,.vendor-report{margin:14px 16px;padding:15px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff;box-shadow:none}
        .vendor-berkas b,.vendor-report>b{color:#123b5d;font-size:13px}
        .vendor-berkas a{display:flex;align-items:center;gap:7px;padding:10px 12px;border:1px solid #c8def5;border-radius:8px;background:#eaf3ff;color:#1e6fa8;font-size:12px;font-weight:700;text-decoration:none}
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
        .history-close{background:#0d1b8c;color:#fff}
        .history-close:hover{background:#091267}
        .history-edit-form{margin:10px 16px 16px;padding:15px;border-color:#d6e2ee;border-radius:10px;background:#f8fbff}
        .history-panel{overflow:hidden;padding:0!important;border:1px solid #d6e2ee!important;border-radius:14px!important;background:#fff!important;box-shadow:0 6px 18px rgba(13,27,140,.07)!important}
        .history-panel>.kartu-judul{display:flex;align-items:center;justify-content:space-between;min-height:45px;margin:0!important;padding:12px 16px!important;background:#0d1b8c!important;color:#fff!important;border-radius:12px 12px 0 0!important;font-size:13px;font-weight:800;letter-spacing:.03em}
        .history-panel>.kartu-judul span{color:#fff!important}
        .history-close{min-height:30px;padding:6px 12px;border:1px solid rgba(255,255,255,.35);border-radius:8px;background:#fff;color:#0d1b8c;font-size:11px;font-weight:800;cursor:pointer}
        .history-close:hover{background:#eaf3ff;color:#091267}
        .history-item{margin:14px 16px 0;padding:14px 16px;border:1px solid #d6e2ee;border-radius:10px;background:#f8fbff}
        .history-item b,.history-item strong{color:#123b5d!important}
        .history-item div span{color:#64748b!important;font-size:12px}
        .history-item>.pill{display:inline-flex;align-items:center;min-height:28px;padding:6px 10px!important;border-radius:999px!important;background:#eaf3ff!important;color:#1e6fa8!important;font-size:10px!important;font-weight:800}
        .history-edit-form{gap:10px;margin:10px 16px 16px;padding:15px;border:1px solid #d6e2ee!important;border-radius:10px;background:#f8fbff!important}
        .history-edit-form>b{color:#123b5d;font-size:13px}
        .history-edit-form label{display:flex;align-items:center;gap:8px;color:#334155!important;font-size:12px}
        .history-edit-form label input[type=checkbox]{width:15px;height:15px;accent-color:#0d1b8c}
        .history-edit-form textarea,.history-edit-form input[type=file]{background:#fff!important;color:#334155!important;border:1px solid #b8c9da!important;border-radius:8px}
        .history-edit-form textarea::placeholder{color:#94a3b8}
        @media (max-width:600px) { .history-button { float:none; margin:12px 0 0; } .history-item { align-items:flex-start; flex-direction:column; } }
    </style>
    <script>
        (() => {
            const panel = document.getElementById('historyPanel');
            const openHistory = document.getElementById('openHistory');
            if (openHistory) openHistory.addEventListener('click', () => { panel.hidden = false; panel.scrollIntoView({ behavior: 'smooth' }); });
        })();
    </script>
@endsection
