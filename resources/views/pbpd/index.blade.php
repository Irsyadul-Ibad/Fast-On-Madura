@extends('layouts.app')

@section('title', 'Data PB/PD')
@section('judul', 'Data PB/PD')

@section('isi')
    @if (session('success'))
        <div class="box" style="color:#176b35">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="box" style="color:#a61b1b">{{ session('error') }}</div>
    @endif

    @if ($showSummaryCards)
        <div class="summary-cards">
            <div class="summary-card summary-card-total">
                <span class="summary-card-label">TOTAL DATA</span>
                <strong>{{ $summaryTotal }}</strong>
            </div>
            @if ($isUlpSummary)
                @foreach ([
                    'PASANG BARU' => 'Pasang Baru',
                    'PERUBAHAN DAYA' => 'Perubahan Daya',
                ] as $key => $label)
                    <div class="summary-card">
                        <span class="summary-card-label">{{ $label }}</span>
                        <strong>{{ $summaryByJenis[$key] ?? 0 }}</strong>
                    </div>
                @endforeach
                @foreach (['CETAK PK' => 'Cetak PK', 'BAYAR' => 'Bayar', 'PENGESAHAN PDL' => 'Pengesahan PDL', 'PDL AWAL' => 'PDL Awal'] as $key => $label)
                    <div class="summary-card"><span class="summary-card-label">{{ $label }}</span><strong>{{ $summaryByStatus[$key] ?? 0 }}</strong></div>
                @endforeach
            @else
                @forelse ($summarySentByUlp as $sentUlp)
                    <div class="summary-card summary-card-sent">
                        <span class="summary-card-label">{{ $summaryUlpNames[$sentUlp->ulp_id]->nama ?? 'ULP tidak diketahui' }}</span>
                        <strong>{{ $sentUlp->jumlah }}</strong>
                        <small class="summary-card-caption">Data dikirim ULP</small>
                    </div>
                @empty
                    <div class="summary-card summary-card-sent">
                        <span class="summary-card-label">DATA DIKIRIM ULP</span>
                        <strong>0</strong>
                    </div>
                @endforelse
            @endif
        </div>
    @endif

    <form class="box filter" method="GET" action="{{ route('pbpd.index') }}">
        <input type="text" name="cari" value="{{ request('cari') }}"
               placeholder="Cari nama / no agenda / alamat">

        @if ($ulps->isNotEmpty())
            <select name="ulp">
                <option value="">Semua ULP</option>
                @foreach ($ulps as $u)
                    <option value="{{ $u->id }}" @selected(request('ulp') == $u->id)>{{ $u->nama }}</option>
                @endforeach
            </select>
        @endif

        <select name="jenis">
            <option value="">Semua jenis transaksi</option>
            <option value="PERUBAHAN DAYA" @selected(request('jenis') == 'PERUBAHAN DAYA')>Perubahan Daya</option>
            <option value="PASANG BARU" @selected(request('jenis') == 'PASANG BARU')>Pasang Baru</option>
        </select>

        <select name="status">
            <option value="">Semua status</option>
            @foreach ($statuss as $s)
                <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
            @endforeach
        </select>

        {{-- @if (in_array(auth()->user()->role?->type, ['ULP', 'UP3'], true))
            <select name="tahap">
                <option value="">Semua tahap</option>
                @foreach ($tahaps as $value => $label)
                    <option value="{{ $value }}" @selected(request('tahap') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        @endif  --}}

        @if (request('import'))
            <input type="hidden" name="import" value="{{ request('import') }}">
            <span class="pill">File upload #{{ request('import') }}</span>
        @endif

        <button type="submit" class="btn">Filter</button>
        <a href="{{ route('pbpd.index') }}" class="btn btn-abu">Reset</a>
    </form>

    <div class="box kartu">
        <div class="scroll">
            <table class="tabel tabel-biru">
                <thead>
                    <tr class="table-title-row">
                        <th colspan="{{ ($canKirim ? 1 : 0) + 16 + ($showSyarat ? 2 : 0) }}">
                            <div class="table-title-content">
                                <span>RECORD, JUMLAH TRANSAKSI PB/PD</span>
                                @if ($canKirim)
                                    <button type="button" class="btn table-send-button" id="openSendModal" disabled>Kirim data terpilih</button>
                                @endif
                            </div>
                        </th>
                    </tr>
                    <tr>
                        @if ($canKirim)
                            <th rowspan="2"><input type="checkbox" id="checkAll" title="Pilih semua"></th>
                        @endif
                        <th rowspan="2">NO.</th>
                        <th rowspan="2">ASAL ULP</th>
                        <th rowspan="2">DETAIL</th>
                        @if ($showSyarat)<th colspan="2" class="grup">SYARAT</th>@endif
                        <th rowspan="2">TRANSAKSI</th>
                        <th rowspan="2">STATUS</th>
                        <th rowspan="2">NO AGENDA</th>
                        <th rowspan="2">NAMA PELANGGAN</th>
                        <th rowspan="2">IDPEL</th>
                        <th rowspan="2">TOTAL BIAYA</th>
                        <th rowspan="2">TANGGAL MOHON</th>
                        <th rowspan="2">TANGGAL BAYAR</th>
                        <th colspan="2" class="grup">LAMA</th>
                        <th colspan="2" class="grup">BARU</th>
                        <th rowspan="2">DURASI HARI KERJA</th>
                    </tr>
                    <tr>
                        @if ($showSyarat)
                            <th class="sub">BERKAS PENDUKUNG</th>
                            <th class="sub">BERKAS IJIN</th>
                        @endif
                        <th class="sub">TARIF</th>
                        <th class="sub">DAYA</th>
                        <th class="sub">TARIF</th>
                        <th class="sub">DAYA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $row)
                        <tr>
                            @if ($canKirim)
                                <td>
                                    <input type="checkbox" class="pilih-pbpd" value="{{ $row->id }}"
                                           @disabled($row->tahap !== 'ULP' || empty($row->berkas_pendukung_paths) || empty($row->berkas_ijin_paths))
                                </td>
                            @endif
                            <td>{{ $data->firstItem() + $loop->index }}.</td>
                            <td><b>{{ $row->asal_ulp }}</b></td>
                            <td>
                                <button
                                    type="button"
                                    class="ikon-dtl tombol-detail"
                                    title="Detail"
                                    data-id="{{ $row->id }}"
                                    data-rab-url="{{ route('pbpd.rab.update', $row->id) }}"
                                    data-no-agenda="{{ $row->no_agenda }}"
                                    data-idpel="{{ $row->id_pelanggan }}"
                                    data-nama="{{ $row->nama_pelanggan }}"
                                    data-alamat="{{ $row->alamat }}"
                                    data-transaksi="{{ $row->jenis_transaksi }}"
                                    data-status="{{ $row->status }}"
                                    data-bp="{{ $row->bp }}"
                                    data-total-biaya="{{ $row->total_biaya }}"
                                    data-tgl-mohon="{{ $row->tgl_mohon }}"
                                    data-tgl-bayar="{{ $row->tgl_bayar }}"
                                    data-rab="{{ $row->rab }}"
                                    data-pendukung-files="{{ base64_encode(json_encode($row->berkas_pendukung_paths ?? [])) }}"
                                    data-ijin-files="{{ base64_encode(json_encode($row->berkas_ijin_paths ?? [])) }}"
                                    data-pendukung-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => 0]) }}"
                                    data-ijin-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'ijin', 'index' => 0]) }}"
                                ><svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5h9l3 3V20.5H6z"/><path d="M15 3.5v4h3M9 12h6M9 16h6"/></svg></button>
                            </td>
                            @if ($showSyarat)
                                <td><button type="button" class="ikon-syarat tombol-syarat {{ count($row->berkas_pendukung_paths ?? []) ? 'berkas-lengkap' : 'berkas-belum' }}" data-id="{{ $row->id }}" data-jenis="pendukung" data-files="{{ base64_encode(json_encode($row->berkas_pendukung_paths ?? [])) }}" data-file-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => 0]) }}" title="Berkas Pendukung">@if(count($row->berkas_pendukung_paths ?? []))<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>@else<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4M12 16h.01"/><circle cx="12" cy="12" r="9"/></svg>@endif</button></td>
                                <td><button type="button" class="ikon-syarat tombol-syarat {{ count($row->berkas_ijin_paths ?? []) ? 'berkas-lengkap' : 'berkas-belum' }}" data-id="{{ $row->id }}" data-jenis="ijin" data-files="{{ base64_encode(json_encode($row->berkas_ijin_paths ?? [])) }}" data-file-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'ijin', 'index' => 0]) }}" title="Berkas Ijin">@if(count($row->berkas_ijin_paths ?? []))<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>@else<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4M12 16h.01"/><circle cx="12" cy="12" r="9"/></svg>@endif</button></td>
                            @endif
                            <td>
                                <span class="pill pill-ungu">
                                    {{ [
                                        'PB' => 'Pasang Baru',
                                        'PD' => 'Perubahan Daya',
                                        'BN' => 'Balik Nama',
                                        'PASANG BARU' => 'Pasang Baru',
                                        'PERUBAHAN DAYA' => 'Perubahan Daya',
                                    ][$row->jenis_transaksi] ?? $row->jenis_transaksi }}
                                </span>
                            </td>
                            <td>
                                <span class="pill {{ $row->status === 'BAYAR' ? 'pill-hijau' : 'pill-kuning' }}">
                                    {{ ucfirst(strtolower($row->status)) }}
                                </span>
                            </td>
                            <td>{{ $row->no_agenda }}</td>
                            <td>{{ $row->nama_pelanggan }}</td>
                            <td>{{ $row->id_pelanggan }}</td>
                            <td>{{ $row->total_biaya !== null ? number_format($row->total_biaya, 0, ',', '.') : '-' }}</td>
                            <td>{{ $row->tgl_mohon?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $row->tgl_bayar?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $row->tarif_lama }}</td>
                            <td>{{ $row->daya_lama }} VA</td>
                            <td><b>{{ $row->tarif_baru }}</b></td>
                            <td><b>{{ $row->daya_baru }} VA</b></td>
                            <td>{{ $row->keterangan }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ ($canKirim ? 1 : 0) + 16 + ($showSyarat ? 2 : 0) }}" style="text-align:center">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-footer">
            <div class="kartu-kaki">
                Records {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }}
            </div>
            <div class="halaman">
            @if ($data->previousPageUrl())
                <a class="btn btn-abu" href="{{ $data->previousPageUrl() }}">« Sebelumnya</a>
            @endif
            <span>Halaman {{ $data->currentPage() }} dari {{ $data->lastPage() }}</span>
            @if ($data->nextPageUrl())
                <a class="btn btn-abu" href="{{ $data->nextPageUrl() }}">Berikutnya »</a>
            @endif
            </div>
        </div>
    </div>

    @if ($canKirim)
        <div id="sendModal" class="detail-modal" hidden>
            <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="sendModalTitle">
                <button type="button" class="detail-modal-close" id="closeSendModal" aria-label="Tutup">&times;</button>
                <h3 id="sendModalTitle">Kirim data terpilih ke...</h3>
                <form method="POST" action="{{ route('pbpd.kirim') }}" id="sendForm">
                    @csrf
                    <div id="selectedPelanggan"></div>
                    <label>Tujuan pengiriman</label>
                    <select name="tujuan" id="tujuanKirim" required class="tujuan-hidden-select" aria-hidden="true" tabindex="-1">
                        <option value="">Pilih tujuan</option><option value="JTM">Perluasan JTM</option><option value="JTR">Perluasan JTR</option><option value="TANPA_PERLUASAN">Tanpa Perluasan</option>
                    </select>
                    <div class="tujuan-cards" role="radiogroup" aria-label="Pilih tujuan pengiriman">
                        <button type="button" class="tujuan-card" data-tujuan="JTM"><span class="tujuan-card-icon">ϟ</span><strong>Perluasan JTM</strong><small>Jaringan Tegangan Menengah</small></button>
                        <button type="button" class="tujuan-card" data-tujuan="JTR"><span class="tujuan-card-icon">ϟ</span><strong>Perluasan JTR</strong><small>Jaringan Tegangan Rendah</small></button>
                        <button type="button" class="tujuan-card" data-tujuan="TANPA_PERLUASAN"><span class="tujuan-card-icon tujuan-card-icon-green">▣</span><strong>Tanpa Perluasan</strong><small>Langsung Sampai Tujuan</small></button>
                    </div>
                    <div id="expansionNeeds" hidden>
                        <table class="kebutuhan-tabel">
                            <thead>
                                <tr><th>KETERANGAN</th><th>JENIS</th><th>JUMLAH</th><th>SATUAN</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>JUMLAH TIANG</th>
                                    <td><select name="jenis_tiang"><option value="">Pilih</option><option>9</option><option>11</option><option>13</option></select></td>
                                    <td><input name="jml_tiang" type="number" min="0" placeholder="Isi jumlah sendiri"></td>
                                    <td>BUAH</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH KONDUKTOR TM</th>
                                    <td><select name="jenis_konduktor_tm"><option value="">Pilih</option><option>AAAC-S 240</option><option>AAAC-S 150</option><option>AAAC-S 70</option><option>AAAC 40</option><option>AAAC 150</option><option>AAAC 70</option></select></td>
                                    <td><input name="jml_konduktor_tm" type="number" min="0" placeholder="Isi jumlah meter"></td>
                                    <td>METER</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH KONDUKTOR TR</th>
                                    <td><select name="jenis_konduktor_tr"><option value="">Pilih</option><option>LVTC 3x70+1x70</option><option>LVTC 3x70+1x50</option></select></td>
                                    <td><input name="jml_konduktor_tr" type="number" min="0" placeholder="Isi jumlah meter"></td>
                                    <td>METER</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH TRAFO</th>
                                    <td><select name="jenis_trafo"><option value="">Pilih</option><option>0</option><option>50</option><option>100</option><option>160</option><option>200</option><option>250</option></select></td>
                                    <td><input name="jml_trafo" type="number" min="0" placeholder="Isi jumlah sendiri"></td>
                                    <td>BUAH</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH KWH METER</th>
                                    <td><select name="jenis_kwh_meter"><option value="">Pilih</option><option>Prabayar</option><option>Pascabayar</option><option>Lainnya</option></select></td>
                                    <td><input name="jml_kwh_meter" type="number" min="0" placeholder="Isi jumlah meter"></td>
                                    <td>METER</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH PANEL METER</th>
                                    <td><select name="jenis_panel_meter"><option value="">Pilih</option><option>Umum</option><option>53 kva</option><option>66 kva</option><option>105 kva</option><option>164 kva</option><option>197 kva</option></select></td>
                                    <td><input name="jml_panel_meter" type="number" min="0" placeholder="Isi jumlah buah"></td>
                                    <td>BUAH</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn">Kirim sekarang</button>
                </form>
            </div>
        </div>
    @endif

    <div id="detailModal" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
            <button type="button" class="detail-modal-close" aria-label="Tutup">&times;</button>
            <h3 id="detailModalTitle">Detail Data PB/PD</h3>

            <div class="detail-grid">
                <div><small>No Agenda</small><b id="detailNoAgenda"></b></div>
                <div><small>IDPEL</small><b id="detailIdpel"></b></div>
                <div><small>Nama Pelanggan</small><b id="detailNama"></b></div>
                <div><small>Transaksi</small><b id="detailTransaksi"></b></div>
                <div><small>Status</small><b id="detailStatus"></b></div>
                <div><small>BP (Total Biaya)</small><b id="detailBp"></b></div>
                <div><small>Total Biaya</small><b id="detailTotalBiaya"></b></div>
                <div><small>Tanggal Mohon</small><b id="detailTglMohon"></b></div>
                <div><small>Tanggal Bayar</small><b id="detailTglBayar"></b></div>
                <div><small>RAB</small><b id="detailRabValue"></b></div>
                <div class="detail-full"><small>Alamat</small><b id="detailAlamat"></b></div>
            </div>

            @if ($canEditRab)
                <form id="rabForm" method="POST" class="detail-rab-form">
                    @csrf
                    <label for="rabInput">RAB</label>
                    <div class="detail-rab-input">
                        <span>Rp</span>
                        <input id="rabInput" name="rab" type="number" min="0" step="0.01" placeholder="Masukkan RAB">
                    </div>
                    <button type="submit" class="btn">Simpan RAB</button>
                </form>
            @endif
        </div>
    </div>

    <div id="syaratModal" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="syaratModalTitle">
            <button type="button" class="detail-modal-close" id="closeSyaratModal" aria-label="Tutup">&times;</button>
            <h3 id="syaratModalTitle">Berkas Syarat</h3>
            <div id="syaratFiles" class="syarat-files">-</div>
            @if ($canUploadSyarat)
                <form id="syaratUploadForm" method="POST" action="#" enctype="multipart/form-data" class="syarat-upload-form">
                    @csrf
                    <input type="file" name="berkas[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <button type="submit" class="btn">Upload Berkas/Foto</button>
                </form>
            @endif
        </div>
    </div>

    <style>
        .detail-modal { position:fixed; inset:0; z-index:20; display:grid; place-items:center; padding:20px; background:rgba(0,0,0,.45); }
        .detail-modal[hidden] { display:none; }
        .detail-modal-box { position:relative; width:min(620px, 100%); max-height:90vh; overflow:auto; padding:24px; border-radius:10px; background:#fff; box-shadow:0 12px 40px rgba(0,0,0,.25); }
        .detail-modal-box h3 { margin:0 0 18px; color:#0b3d6b; }
        .detail-modal-close { position:absolute; top:10px; right:10px; padding:2px 9px; background:#e8eef5; color:#223; font-size:20px; }
        .detail-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
        .detail-grid div { padding:10px; border:1px solid #d6e2ee; border-radius:6px; }
        .detail-grid small, .detail-grid b { display:block; }
        .detail-grid small { margin-bottom:4px; color:#637487; }
        .detail-grid b { overflow-wrap:anywhere; }
        .detail-full { grid-column:1 / -1; }
        .detail-rab-form { display:flex; align-items:end; gap:10px; margin-top:18px; }
        .detail-rab-form label { display:block; flex:1; font-weight:600; }
        .detail-rab-input { display:flex; align-items:center; margin-top:5px; border:1px solid #b9c7d5; border-radius:6px; overflow:hidden; }
        .detail-rab-input span { padding:8px; background:#eef3f8; }
        .detail-rab-input input { width:150px; padding:8px; border:0; outline:0; }
        .kebutuhan-tabel { width:100%; margin:0 0 16px; border:1px solid #c8d6e5; border-radius:6px; overflow:hidden; font-size:12px; }
        .kebutuhan-tabel th, .kebutuhan-tabel td { padding:8px; border-bottom:1px solid #dce5ee; text-align:left; }
        .kebutuhan-tabel thead th { background:#eef3f8; color:#536477; }
        .kebutuhan-tabel tbody tr:last-child th, .kebutuhan-tabel tbody tr:last-child td { border-bottom:0; }
        .kebutuhan-tabel select, .kebutuhan-tabel input { width:100%; padding:6px; border:1px solid #c8d6e5; border-radius:4px; background:#fff8ee; }
        .tujuan-hidden-select{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
        #sendModal .detail-modal-box{width:min(560px,100%)!important;padding:0!important;border-radius:14px!important;overflow:auto!important;background:#f8fafc!important}
        #sendModal .detail-modal-box h3{margin:0!important;padding:16px 20px!important;background:#0d1b8c!important;color:#fff!important;font-size:15px!important;line-height:1.3!important}
        #sendModal .detail-modal-box form{padding:16px 20px 18px!important}#sendModal .detail-modal-box form>label{display:block;margin-bottom:9px;color:#243b53;font-size:12px;font-weight:700}
        .tujuan-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:0 0 16px}
        .tujuan-card{display:flex!important;align-items:center;flex-direction:column;justify-content:center;gap:4px!important;min-height:102px!important;padding:12px 8px!important;border:1px solid #d9e2ec!important;border-radius:10px!important;background:#fff!important;color:#243b53!important;box-shadow:none!important;text-align:center;cursor:pointer;transition:border-color .18s,background .18s,transform .18s!important}
        .tujuan-card:hover{border-color:#2b73fe!important;background:#f4f8ff!important;transform:translateY(-1px)!important}.tujuan-card.is-selected{border-color:#2b73fe!important;background:#eef4ff!important;box-shadow:inset 0 0 0 2px rgba(43,115,254,.16)!important}
        .tujuan-card-icon{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:#e9edff;color:#293bc0;font-size:20px;font-weight:800;line-height:1}.tujuan-card-icon-green{background:#d9f8e9;color:#159447}.tujuan-card strong{font-size:11px;line-height:1.2}.tujuan-card small{max-width:120px;color:#8191aa;font-size:9px;line-height:1.2}
        #sendModal #expansionNeeds{margin-top:4px;padding:12px;border:1px solid #d9e2ec;border-radius:10px;background:#fff}#sendModal .kebutuhan-tabel{margin:0!important;border-radius:8px!important;background:#fff!important}#sendModal .kebutuhan-tabel th,#sendModal .kebutuhan-tabel td{padding:9px!important}#sendModal .kebutuhan-tabel select,#sendModal .kebutuhan-tabel input{min-height:32px!important;background:#fff8ee!important;border-radius:6px!important}
        #sendModal #sendForm>button[type="submit"]{width:100%;min-height:38px;margin-top:4px;background:#0d1b8c!important;color:#fff!important;border-radius:8px!important;font-weight:700!important}
        .ikon-syarat { padding:4px 8px; background:#e8eef5; color:#0b3d6b; }
        .syarat-files { display:grid; gap:8px; padding:12px; border:1px solid #d6e2ee; border-radius:6px; }
        .syarat-files a { color:#0b3d6b; overflow-wrap:anywhere; }
        .syarat-upload-form { display:flex; gap:10px; align-items:center; margin-top:16px; flex-wrap:wrap; }
        .syarat-upload-form input[type=file] { flex:1; min-width:220px; padding:8px; border:1px solid #b9c7d5; border-radius:6px; }
        @media (max-width:600px) { .tujuan-cards{grid-template-columns:1fr}.tujuan-card{min-height:72px!important}.detail-grid { grid-template-columns:1fr; } .detail-full { grid-column:auto; } .detail-rab-form { align-items:stretch; flex-direction:column; } }
    </style>

    <script>
        (() => {
            const modal = document.getElementById('detailModal');
            const rabForm = document.getElementById('rabForm');
            const setText = (id, value) => { document.getElementById(id).textContent = value || '-'; };

            document.querySelectorAll('.tombol-detail').forEach((button) => {
                button.addEventListener('click', () => {
                    setText('detailNoAgenda', button.dataset.noAgenda);
                    setText('detailIdpel', button.dataset.idpel);
                    setText('detailNama', button.dataset.nama);
                    setText('detailAlamat', button.dataset.alamat);
                    setText('detailTransaksi', button.dataset.transaksi);
                    setText('detailStatus', button.dataset.status);
                    setText('detailBp', button.dataset.bp ? Number(button.dataset.bp).toLocaleString('id-ID') : '-');
                    setText('detailTotalBiaya', button.dataset.totalBiaya ? Number(button.dataset.totalBiaya).toLocaleString('id-ID') : '-');
                    setText('detailTglMohon', button.dataset.tglMohon ? new Date(button.dataset.tglMohon).toLocaleDateString('id-ID') : '-');
                    setText('detailTglBayar', button.dataset.tglBayar ? new Date(button.dataset.tglBayar).toLocaleDateString('id-ID') : '-');
                    setText('detailRabValue', button.dataset.rab ? Number(button.dataset.rab).toLocaleString('id-ID') : '-');
                    if (rabForm) {
                        document.getElementById('rabInput').value = button.dataset.rab || '';
                        rabForm.action = button.dataset.rabUrl;
                    }
                    modal.hidden = false;
                });
            });

            const closeModal = () => { modal.hidden = true; };
            document.querySelector('#detailModal .detail-modal-close').addEventListener('click', closeModal);
            modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });

            const syaratModal = document.getElementById('syaratModal');
            const syaratUploadForm = document.getElementById('syaratUploadForm');
            document.querySelectorAll('.tombol-syarat').forEach((button) => {
                button.addEventListener('click', () => {
                    const jenis = button.dataset.jenis;
                    document.getElementById('syaratModalTitle').textContent = jenis === 'ijin' ? 'Berkas Ijin' : 'Berkas Pendukung';
                    let files = [];
                    try { files = JSON.parse(button.dataset.files ? atob(button.dataset.files) : '[]'); } catch (error) { files = []; }
                    const filesBox = document.getElementById('syaratFiles');
                    filesBox.innerHTML = files.length
                        ? files.map((path, index) => `<a href="${button.dataset.fileUrl.replace(/\/0$/, '/' + index)}" target="_blank" rel="noopener">📄 ${String(path).split('/').pop() || 'Berkas ' + (index + 1)}</a>`).join('')
                        : '<span>Belum ada berkas.</span>';
                    if (syaratUploadForm) syaratUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/syarat/${jenis}`;
                    syaratModal.hidden = false;
                });
            });
            const closeSyaratModal = () => { syaratModal.hidden = true; };
            document.getElementById('closeSyaratModal').addEventListener('click', closeSyaratModal);
            syaratModal.addEventListener('click', (event) => { if (event.target === syaratModal) closeSyaratModal(); });

            const sendModal = document.getElementById('sendModal');
            const sendButton = document.getElementById('openSendModal');
            const checkAll = document.getElementById('checkAll');
            const checks = [...document.querySelectorAll('.pilih-pbpd')];
            const selectedPelanggan = document.getElementById('selectedPelanggan');
            const tujuanKirim = document.getElementById('tujuanKirim');
            const expansionNeeds = document.getElementById('expansionNeeds');
            const tujuanCards = document.querySelectorAll('.tujuan-card');
            const updateExpansionNeeds = () => {
                if (tujuanKirim && expansionNeeds) expansionNeeds.hidden = !['JTM', 'JTR'].includes(tujuanKirim.value);
                tujuanCards.forEach((card) => card.classList.toggle('is-selected', card.dataset.tujuan === tujuanKirim?.value));
            };
            if (tujuanKirim) tujuanKirim.addEventListener('change', updateExpansionNeeds);
            tujuanCards.forEach((card) => card.addEventListener('click', () => { tujuanKirim.value = card.dataset.tujuan; updateExpansionNeeds(); }));
            const updateSendButton = () => {
                if (sendButton) sendButton.disabled = !checks.some((check) => check.checked);
            };
            checks.forEach((check) => check.addEventListener('change', updateSendButton));
            if (checkAll) {
                checkAll.addEventListener('change', () => {
                    checks.filter((check) => !check.disabled).forEach((check) => { check.checked = checkAll.checked; });
                    updateSendButton();
                });
            }
            if (sendButton) {
                sendButton.addEventListener('click', () => {
                    selectedPelanggan.innerHTML = checks.filter((check) => check.checked)
                        .map((check) => `<input type="hidden" name="pelanggan[]" value="${check.value}">`).join('');
                    sendModal.hidden = false;
                });
                document.getElementById('closeSendModal').addEventListener('click', () => { sendModal.hidden = true; });
                sendModal.addEventListener('click', (event) => { if (event.target === sendModal) sendModal.hidden = true; });
            }
        })();
    </script>
    <style>
        /* Modern dashboard treatment for the existing PB/PD screen. */
        .filter{position:relative;display:flex;align-items:center;column-gap:10px!important;row-gap:12px!important;flex-wrap:wrap;padding:70px 18px 20px!important;background:#fff!important;border:1px solid #dbe5f0!important;border-radius:16px!important;box-shadow:0 8px 24px rgba(13,27,140,.07)!important}
        .filter::before{content:'DAFTAR TRANSAKSI';position:absolute;inset:0 0 auto;height:50px;display:flex;align-items:flex-start;padding:9px 18px 0;border-radius:16px 16px 0 0;background:#0d1b8c;color:#fff;font-size:15px;font-weight:800;letter-spacing:.035em;line-height:1.1}
        .filter::after{content:'Daftar Transaksi PB/PD';position:absolute;top:31px;left:18px;color:#dbeafe;font-size:11px;font-weight:600;letter-spacing:.01em;line-height:1.1}
        .filter input,.filter select{min-height:36px;border-radius:9px!important;border-color:#cbd9e8!important;background:#f8fafc!important;color:#475569!important;opacity:1!important;box-shadow:none!important;transition:border-color .18s,box-shadow .18s,background .18s}
        .filter select option{color:#334155;background:#fff}
        .filter input:focus,.filter select:focus{background:#fff!important;border-color:#2b73fe!important;box-shadow:0 0 0 3px rgba(43,115,254,.13)!important}
        .filter .btn{min-height:38px;border:0;background:#0d1b8c;box-shadow:0 5px 12px rgba(13,27,140,.2);transition:transform .18s,box-shadow .18s,background .18s}
        .filter .btn:hover{background:#091267;transform:translateY(-1px);box-shadow:0 8px 16px rgba(13,27,140,.25)}
        .filter .btn.btn-abu{background:#f1f5f9!important;color:#475569!important;border:1px solid #dbe5f0!important;box-shadow:none!important}
        .filter .btn.btn-abu:hover{background:#e2e8f0!important;color:#123b5d!important;transform:none}
        .kartu{overflow:hidden;padding:0!important;margin-top:14px!important;border:1px solid #0d1b8c!important;border-radius:16px!important;background:#0d1b8c!important;box-shadow:0 8px 24px rgba(13,27,140,.14)!important}
        .kartu-judul{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:45px;margin:0!important;padding:12px 16px;color:#fff!important;background:#0d1b8c!important;border-radius:12px 12px 0 0!important;font-size:12px;font-weight:800;letter-spacing:.035em}
        .kartu-judul>span:first-child{color:#fff!important}.kartu-judul .pill{background:#1e40af!important;color:#fff!important;border:1px solid rgba(255,255,255,.28)!important;box-shadow:none!important}
        .kartu>#openSendModal,.kartu>div:has(>#openSendModal){padding:12px 18px 12px;background:#0d1b8c!important}
        .kartu>#openSendModal{background:#2b73fe!important;color:#fff!important}
        .kartu .scroll{margin:0 18px 16px;border:1px solid #dbe5f0!important;border-radius:11px!important;box-shadow:none;overflow:auto;background:#fff!important}
        .tabel{border:0!important;border-radius:10px!important;overflow:hidden;font-size:12px!important}
        .tabel thead th{background:#0d1b8c!important;border-color:#263aa8!important;color:#fff!important;font-size:11px!important;letter-spacing:.035em;white-space:nowrap}
        .tabel thead .table-title-row th{height:52px!important;padding:8px 16px!important;text-align:left!important;font-size:12px!important;font-weight:800!important;letter-spacing:.035em!important;background:#0d1b8c!important;border-color:#0d1b8c!important}
        .table-title-content{display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%}.table-send-button{min-height:32px!important;padding:7px 14px!important;background:#2b73fe!important;color:#fff!important;border:0!important;border-radius:8px!important;font-size:11px!important;font-weight:700!important;letter-spacing:0!important;white-space:nowrap}.table-send-button:hover{background:#1e5ed8!important}.table-send-button:disabled{opacity:.65;cursor:not-allowed}
        .tabel thead th.grup,.tabel thead th.sub{background:#0d1b8c!important}
        .tabel tbody tr{transition:background .16s,box-shadow .16s}.tabel tbody tr:nth-child(even){background:#f8fbff!important}.tabel tbody tr:hover{background:#eef6ff!important;box-shadow:inset 3px 0 #f4c300}
        .tabel tbody td{border-color:#e2e8f0!important;color:#475569!important;vertical-align:middle}.tabel tbody td b{color:#123b5d!important}.tabel tbody td:nth-child(2),.tabel tbody td:nth-child(3){font-weight:650}
        .tabel .ikon-dtl,.tabel .detail-link{width:31px;height:31px;border-radius:8px!important;background:#eaf3ff!important;color:#1e6fa8!important;border:1px solid #c8def5!important;box-shadow:none!important}
        .tabel .ikon-dtl:hover,.tabel .detail-link:hover{background:#1e6fa8!important;color:#fff!important;transform:translateY(-1px)!important}
        .tabel .ikon-syarat{width:28px;height:28px;border-radius:8px!important;background:#f8fbff!important;color:#1e6fa8!important;border:1px solid #d5e5f5!important}.tabel .ikon-syarat.berkas-lengkap{background:#e8f7ef!important;color:#2e9b68!important;border-color:#b9e4ca!important}.tabel .ikon-syarat.berkas-belum{background:#fff7ed!important;color:#d97706!important;border-color:#f5d7a1!important}
        .tabel .pill{font-size:11px!important;font-weight:700;border-radius:999px!important;padding:5px 9px!important}
        @media(max-width:760px){.filter{padding:64px 12px 14px!important}.filter>*{flex:1 1 100%}.filter::before{padding-left:12px}.filter::after{left:12px}.kartu-judul{align-items:flex-start;flex-direction:column}.kartu .scroll{margin:0 10px 10px}.table-title-content{align-items:flex-start;flex-direction:column;gap:8px}.table-send-button{align-self:flex-end}}
    </style>
@endsection
