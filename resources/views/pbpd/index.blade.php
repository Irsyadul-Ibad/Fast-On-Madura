@extends('layouts.app')

@php
    $unitCakupanPbpd = match (auth()->user()->role?->type) {
        'ULP' => auth()->user()->role?->ulp?->nama ?? 'ULP Anda',
        'UP3' => 'seluruh ULP',
        default => 'Anda',
    };
@endphp
@section('title', 'Data PB/PD')
@section('judul', 'Data PB/PD')
@section('deskripsi', 'Daftar transaksi PB/PD dalam cakupan '.$unitCakupanPbpd)
@section('header-actions')
    @if ($canKirim)
        <button type="button" class="btn table-send-button" id="openSendModal" disabled>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>
            Kirim data terpilih (<span id="selectedPelangganCount">0</span>)
        </button>
    @endif
@endsection

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

        @if (request('import'))
            <input type="hidden" name="import" value="{{ request('import') }}">
            <span class="pill">File upload #{{ request('import') }}</span>
        @endif

        <button type="submit" class="btn">Filter</button>
        <a href="{{ route('pbpd.index') }}" class="btn btn-abu">Reset</a>
    </form>

    <div class="box kartu">
        <div class="kartu-judul">
            <span>Daftar transaksi<small>Record dan jumlah transaksi PB/PD</small></span>
            <span class="pill">{{ $data->total() }} data</span>
        </div>
        <div class="scroll">
            <table class="tabel tabel-biru">
                <thead>
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
                                <td><button type="button" class="ikon-syarat tombol-syarat {{ count($row->berkas_pendukung_paths ?? []) ? 'berkas-lengkap' : 'berkas-belum' }}" data-id="{{ $row->id }}" data-jenis="pendukung" data-files="{{ base64_encode(json_encode($row->berkas_pendukung_paths ?? [])) }}" data-file-names="{{ base64_encode(json_encode($row->berkas_pendukung_names ?? [])) }}" data-file-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => 0]) }}" title="Berkas Pendukung">@if(count($row->berkas_pendukung_paths ?? []))<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>@else<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4M12 16h.01"/><circle cx="12" cy="12" r="9"/></svg>@endif</button></td>
                                <td><button type="button" class="ikon-syarat tombol-syarat {{ count($row->berkas_ijin_paths ?? []) ? 'berkas-lengkap' : 'berkas-belum' }}" data-id="{{ $row->id }}" data-jenis="ijin" data-files="{{ base64_encode(json_encode($row->berkas_ijin_paths ?? [])) }}" data-file-names="{{ base64_encode(json_encode($row->berkas_ijin_names ?? [])) }}" data-file-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'ijin', 'index' => 0]) }}" title="Berkas Ijin">@if(count($row->berkas_ijin_paths ?? []))<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>@else<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v4M12 16h.01"/><circle cx="12" cy="12" r="9"/></svg>@endif</button></td>
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
                            <td>{{ filled($row->daya_lama) ? $row->daya_lama . ' VA' : '' }}</td>
                            <td><b>{{ $row->tarif_baru }}</b></td>
                            <td><b>{{ filled($row->daya_baru) ? $row->daya_baru . ' VA' : '' }}</b></td>
                            <td>{{ $row->keterangan }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ ($canKirim ? 1 : 0) + 16 + ($showSyarat ? 2 : 0) }}" style="text-align:center">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $data->links('pagination.faston') }}
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
                                    <th>JUMLAH TIANG TM</th>
                                    <td><select name="jenis_tiang_tm"><option value="">Pilih</option><option>11</option><option>13</option></select></td>
                                    <td><input name="jml_tiang_tm" type="number" min="0" placeholder="Isi jumlah sendiri"></td>
                                    <td>BUAH</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH TIANG TR</th>
                                    <td><select name="jenis_tiang_tr"><option value="">Pilih</option><option>9</option></select></td>
                                    <td><input name="jml_tiang_tr" type="number" min="0" placeholder="Isi jumlah sendiri"></td>
                                    <td>BUAH</td>
                                </tr>
                                <tr>
                                    <th>JUMLAH KONDUKTOR TM</th>
                                    <td><select name="jenis_konduktor_tm"><option value="">Pilih</option><option>AAAC-S 240</option><option>AAAC-S 150</option><option>AAAC-S 70</option><option>AAAC 240</option><option>AAAC 150</option><option>AAAC 70</option></select></td>
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
            <header class="detail-modal-header">
                <div>
                    <h3 id="detailModalTitle">Detail Data PB/PD</h3>
                    <p id="detailSummary" class="detail-modal-summary"></p>
                </div>
                <button type="button" class="detail-modal-close" aria-label="Tutup detail">&times;</button>
            </header>

            <div class="detail-modal-content">
                <section class="detail-section" aria-labelledby="detailCustomerHeading">
                    <h4 id="detailCustomerHeading" class="detail-section-title">Informasi Pelanggan</h4>
                    <div class="detail-grid detail-customer-grid">
                        <div><small>No Agenda</small><b id="detailNoAgenda"></b></div>
                        <div><small>IDPEL</small><b id="detailIdpel"></b></div>
                        <div><small>Nama Pelanggan</small><b id="detailNama"></b></div>
                        <div><small>Transaksi</small><b id="detailTransaksi" class="detail-value-pill"></b></div>
                        <div><small>Status</small><b id="detailStatus" class="detail-value-pill"></b></div>
                        <div><small>Total Biaya</small><b id="detailTotalBiaya"></b></div>
                        <div class="detail-full"><small>Alamat</small><b id="detailAlamat"></b></div>
                    </div>
                </section>

                <section class="detail-section" aria-labelledby="detailCostHeading">
                    <h4 id="detailCostHeading" class="detail-section-title">Biaya dan Jadwal</h4>
                    <div class="detail-cost-grid">
                        <div><small>BP (Total Biaya)</small><b id="detailBp"></b></div>
                        <div><small>RAB</small><b id="detailRabValue" class="detail-rab-status"></b></div>
                        <div><small>Tanggal Mohon</small><b id="detailTglMohon"></b></div>
                        <div><small>Tanggal Bayar</small><b id="detailTglBayar"></b></div>
                    </div>
                </section>

                @if ($canEditRab)
                    <form id="rabForm" method="POST" class="detail-rab-form">
                        @csrf
                        <div class="detail-rab-heading">
                            <h4>Isi RAB</h4>
                            <p id="rabFormHelp">RAB belum diisi. Masukkan nilainya lalu simpan.</p>
                        </div>
                        <label for="rabInput">Nilai RAB</label>
                        <div class="detail-rab-controls">
                            <div class="detail-rab-input">
                                <span>Rp</span>
                                <input id="rabInput" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB">
                                <input id="rabValue" name="rab" type="hidden">
                            </div>
                            <button type="submit" class="btn"><span aria-hidden="true">✓</span> Simpan RAB</button>
                        </div>
                    </form>
                @endif
            </div>

            <footer class="detail-modal-footer">
                <button type="button" class="detail-modal-dismiss">Tutup</button>
            </footer>
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

    @push('page-styles')
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
        #detailModal .detail-modal-box{display:flex!important;flex-direction:column!important;width:min(680px,100%)!important;max-height:calc(100vh - 48px)!important;padding:0!important;border:1px solid #dfe6fb!important;border-radius:24px!important;overflow:hidden!important;background:#fff!important;color:#1d2a55!important;box-shadow:0 24px 64px rgba(15,35,104,.25)!important}
        #detailModal .detail-modal-header{position:relative!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:20px!important;padding:22px 24px 18px!important;border-bottom:1px solid #e0e7f7!important;background:#fff!important}
        #detailModal .detail-modal-header h3{margin:0!important;padding:0 54px 0 0!important;background:transparent!important;color:#1236a5!important;font-size:22px!important;font-weight:800!important;line-height:1.25!important}
        #detailModal .detail-modal-summary{margin:5px 0 0!important;color:#60719b!important;font-size:14px!important;font-weight:500!important;line-height:1.45!important;overflow-wrap:anywhere}
        #detailModal .detail-modal-close{position:absolute!important;top:18px!important;right:20px!important;z-index:2!important;display:grid!important;place-items:center!important;width:40px!important;height:40px!important;min-width:40px!important;min-height:40px!important;padding:0!important;border:0!important;border-radius:13px!important;background:#eef2ff!important;color:#394b7e!important;font-size:25px!important;font-family:Arial,sans-serif!important;font-weight:400!important;line-height:1!important;box-shadow:none!important;cursor:pointer!important}
        #detailModal .detail-modal-close:hover{background:#dfe7ff!important;color:#173daf!important}
        #detailModal .detail-modal-content{display:grid!important;gap:20px!important;overflow-y:auto!important;overscroll-behavior:contain!important;padding:20px 24px!important}
        #detailModal .detail-section{min-width:0}
        #detailModal .detail-section-title{margin:0 0 10px!important;padding:0!important;background:transparent!important;color:#60719b!important;font-size:12px!important;font-weight:800!important;letter-spacing:.12em!important;line-height:1.4!important;text-transform:uppercase!important}
        #detailModal .detail-customer-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;padding:0!important}
        #detailModal .detail-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#fff!important}
        #detailModal .detail-grid small,#detailModal .detail-cost-grid small{display:block!important;margin:0 0 5px!important;color:#60719b!important;font-size:11px!important;font-weight:700!important;letter-spacing:.045em!important;line-height:1.35!important;text-transform:uppercase!important}
        #detailModal .detail-grid b,#detailModal .detail-cost-grid b{display:block!important;color:#202d5a!important;font-size:14px!important;font-weight:700!important;line-height:1.45!important;overflow-wrap:anywhere!important}
        #detailModal .detail-grid .detail-full{grid-column:1/-1!important}
        #detailModal .detail-grid .detail-value-pill{display:inline-flex!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#e8efff!important;color:#2147bd!important;font-size:12px!important;font-weight:700!important}
        #detailModal .detail-cost-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:10px!important}
        #detailModal .detail-cost-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#f2f5ff!important}
        #detailModal .detail-cost-grid b{color:#1236a5!important;font-size:17px!important}
        #detailModal .detail-cost-grid .detail-rab-status{display:inline-flex!important;width:max-content!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#fff1c9!important;color:#916000!important;font-size:12px!important;white-space:normal!important}
        #detailModal .detail-cost-grid .detail-rab-status.is-filled{background:#dcf5e8!important;color:#176d43!important}
        #detailModal .detail-rab-form{display:grid!important;gap:10px!important;margin:0!important;padding:18px!important;border:1px solid #c5d2ff!important;border-radius:18px!important;background:#f2f5ff!important}
        #detailModal .detail-rab-heading h4{margin:0 0 3px!important;padding:0!important;background:transparent!important;color:#1236a5!important;font-size:16px!important;font-weight:800!important;line-height:1.35!important}
        #detailModal .detail-rab-heading p{margin:0!important;color:#60719b!important;font-size:13px!important;line-height:1.45!important}
        #detailModal .detail-rab-form>label{margin:0!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important;line-height:1.35!important}
        #detailModal .detail-rab-controls{display:flex!important;align-items:stretch!important;gap:10px!important;min-width:0!important}
        #detailModal .detail-rab-input{display:flex!important;align-items:center!important;flex:1 1 auto!important;min-width:0!important;margin:0!important;border:1px solid #d1dcfa!important;border-radius:12px!important;background:#fff!important;overflow:hidden!important}
        #detailModal .detail-rab-input span{display:grid!important;place-items:center!important;align-self:stretch!important;min-width:48px!important;padding:0 12px!important;background:#2451dc!important;color:#fff!important;font-size:14px!important;font-weight:800!important}
        #detailModal .detail-rab-input input#rabInput{width:100%!important;min-width:0!important;min-height:48px!important;padding:10px 14px!important;border:0!important;border-radius:0!important;background:#fff!important;color:#243b53!important;font-size:15px!important;outline:0!important}
        #detailModal .detail-rab-input input#rabInput:focus{outline:0!important;box-shadow:none!important}
        #detailModal .detail-rab-input:focus-within{border-color:#2451dc!important;box-shadow:0 0 0 3px rgba(36,81,220,.13)!important}
        #detailModal .detail-rab-form .btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:9px!important;min-height:48px!important;padding:0 18px!important;border:0!important;border-radius:12px!important;background:#1941bd!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 8px 18px rgba(25,65,189,.2)!important;white-space:nowrap!important;cursor:pointer!important}
        #detailModal .detail-rab-form .btn:hover{background:#12349e!important}
        #detailModal .detail-modal-footer{display:flex!important;justify-content:flex-end!important;padding:14px 24px!important;border-top:1px solid #e0e7f7!important;background:#fff!important}
        #detailModal .detail-modal-dismiss{min-height:42px!important;padding:0 18px!important;border:1px solid #c8d5ff!important;border-radius:12px!important;background:#fff!important;color:#1236a5!important;font-size:14px!important;font-weight:700!important;box-shadow:none!important;cursor:pointer!important}
        #detailModal .detail-modal-dismiss:hover{background:#f2f5ff!important}
        @media (max-width:700px){#detailModal .detail-modal-box{max-height:calc(100vh - 24px)!important;border-radius:20px!important}#detailModal .detail-modal-header{padding:18px 18px 15px!important}#detailModal .detail-modal-close{top:14px!important;right:14px!important}#detailModal .detail-modal-content{gap:17px!important;padding:17px 18px!important}#detailModal .detail-cost-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#detailModal .detail-modal-footer{padding:12px 18px!important}}
        @media (max-width:480px){#detailModal .detail-customer-grid{grid-template-columns:1fr!important}#detailModal .detail-grid .detail-full{grid-column:auto!important}#detailModal .detail-rab-controls{align-items:stretch!important;flex-direction:column!important}#detailModal .detail-rab-form .btn{width:100%!important}#detailModal .detail-modal-header h3{font-size:19px!important}#detailModal .detail-modal-summary{max-width:calc(100% - 34px)!important;font-size:12px!important}}
        .ikon-syarat { padding:4px 8px; background:#e8eef5; color:#0b3d6b; }
        .syarat-files { display:grid; gap:8px; padding:12px; border:1px solid #d6e2ee; border-radius:6px; }
        .syarat-files a { color:#0b3d6b; overflow-wrap:anywhere; }
        .syarat-upload-form { display:flex; gap:10px; align-items:center; margin-top:16px; flex-wrap:wrap; }
        .syarat-upload-form input[type=file] { flex:1; min-width:220px; padding:8px; border:1px solid #b9c7d5; border-radius:6px; }
        @media (max-width:600px) { .tujuan-cards{grid-template-columns:1fr}.tujuan-card{min-height:72px!important}.detail-grid { grid-template-columns:1fr; } .detail-full { grid-column:auto; } .detail-rab-form { align-items:stretch; flex-direction:column; } }
    </style>
    @endpush

    <script>
        (() => {
            const modal = document.getElementById('detailModal');
            const rabForm = document.getElementById('rabForm');
            const setText = (id, value) => { document.getElementById(id).textContent = value || '-'; };
            const formatCurrency = (value) => {
                const amount = Number(value);
                return value !== '' && value !== null && Number.isFinite(amount) ? `Rp ${amount.toLocaleString('id-ID')}` : '-';
            };
            const formatRupiah = (value) => {
                const number = Number(value);
                return Number.isFinite(number) && number > 0 ? Math.round(number).toLocaleString('id-ID') : '';
            };
            const formatRupiahInput = (value) => {
                const digits = String(value ?? '').replace(/\D/g, '');
                return digits ? Number(digits).toLocaleString('id-ID') : '';
            };
            const rabInput = document.getElementById('rabInput');
            const rabValue = document.getElementById('rabValue');
            rabInput?.addEventListener('input', () => { rabInput.value = formatRupiahInput(rabInput.value); });
            rabForm?.addEventListener('submit', () => {
                if (rabValue) rabValue.value = (rabInput?.value || '').replace(/\./g, '').replace(/,/g, '.');
            });

            document.querySelectorAll('.tombol-detail').forEach((button) => {
                button.addEventListener('click', () => {
                    setText('detailNoAgenda', button.dataset.noAgenda);
                    setText('detailIdpel', button.dataset.idpel);
                    setText('detailNama', button.dataset.nama);
                    document.getElementById('detailSummary').textContent = `Agenda ${button.dataset.noAgenda || '-'} · ${button.dataset.nama || '-'}`;
                    setText('detailAlamat', button.dataset.alamat);
                    setText('detailTransaksi', button.dataset.transaksi);
                    setText('detailStatus', button.dataset.status);
                    setText('detailBp', formatCurrency(button.dataset.bp ?? ''));
                    setText('detailTotalBiaya', formatCurrency(button.dataset.totalBiaya ?? ''));
                    setText('detailTglMohon', button.dataset.tglMohon ? new Date(button.dataset.tglMohon).toLocaleDateString('id-ID') : '-');
                    setText('detailTglBayar', button.dataset.tglBayar ? new Date(button.dataset.tglBayar).toLocaleDateString('id-ID') : '-');
                    const rabDisplay = formatRupiah(button.dataset.rab || '');
                    const rabStatus = document.getElementById('detailRabValue');
                    rabStatus.textContent = rabDisplay ? `Rp ${rabDisplay}` : 'Belum diisi';
                    rabStatus.classList.toggle('is-filled', Boolean(rabDisplay));
                    if (rabForm) {
                        rabInput.value = formatRupiah(button.dataset.rab || '');
                        rabValue.value = button.dataset.rab || '';
                        rabForm.action = button.dataset.rabUrl;
                        document.getElementById('rabFormHelp').textContent = rabDisplay
                            ? 'Nilai RAB sudah tersimpan. Ubah nilainya jika ada pembaruan.'
                            : 'RAB belum diisi. Masukkan nilainya lalu simpan.';
                    }
                    modal.hidden = false;
                });
            });

            const closeModal = () => { modal.hidden = true; };
            document.querySelectorAll('#detailModal .detail-modal-close, #detailModal .detail-modal-dismiss').forEach((button) => {
                button.addEventListener('click', closeModal);
            });
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
                    let fileNames = [];
                    try { fileNames = JSON.parse(button.dataset.fileNames ? atob(button.dataset.fileNames) : '[]'); } catch (error) { fileNames = []; }
                    const filesBox = document.getElementById('syaratFiles');
                    filesBox.innerHTML = files.length
                        ? files.map((path, index) => {
                            const fileName = fileNames[index] || String(path).split('/').pop() || `Berkas ${index + 1}`;
                            const safeFileName = String(fileName).replace(/[&<>"']/g, (character) => ({
                                '&': '&amp;',
                                '<': '&lt;',
                                '>': '&gt;',
                                '"': '&quot;',
                                "'": '&#039;',
                            }[character]));

                            return `<a href="${button.dataset.fileUrl.replace(/\/0$/, '/' + index)}" target="_blank" rel="noopener">📄 ${safeFileName}</a>`;
                        }).join('')
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
                const selectedCount = checks.filter((check) => check.checked).length;
                if (sendButton) sendButton.disabled = selectedCount === 0;
                const selectedCountLabel = document.getElementById('selectedPelangganCount');
                if (selectedCountLabel) selectedCountLabel.textContent = String(selectedCount);
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
    @push('page-styles')
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
        .kartu-judul>span:first-child small{display:block;margin-top:5px;color:#dbe6ff;font-size:11px;font-weight:500;letter-spacing:0}
        .kartu-judul>span:first-child{color:#fff!important}.kartu-judul .pill{background:#1e40af!important;color:#fff!important;border:1px solid rgba(255,255,255,.28)!important;box-shadow:none!important}
        .kartu>#openSendModal,.kartu>div:has(>#openSendModal){padding:12px 18px 12px;background:#0d1b8c!important}
        .kartu>#openSendModal{background:#2b73fe!important;color:#fff!important}
        .kartu .scroll{margin:0 18px 16px;border:1px solid #dbe5f0!important;border-radius:11px!important;box-shadow:none;overflow:auto;background:#fff!important}
        .tabel{border:0!important;border-radius:10px!important;overflow:hidden;font-size:12px!important}
        .tabel thead th{background:#0d1b8c!important;border-color:#263aa8!important;color:#fff!important;font-size:11px!important;letter-spacing:.035em;white-space:nowrap}
        .tabel thead .table-title-row th{height:52px!important;padding:8px 16px!important;text-align:left!important;font-size:12px!important;font-weight:800!important;letter-spacing:.035em!important;background:#0d1b8c!important;border-color:#0d1b8c!important}
        .table-title-content{display:flex;align-items:center;justify-content:flex-start;gap:16px;width:100%}.table-send-button{min-height:32px!important;padding:7px 14px!important;background:#2b73fe!important;color:#fff!important;border:0!important;border-radius:8px!important;font-size:11px!important;font-weight:700!important;letter-spacing:0!important;white-space:nowrap}.table-send-button:hover{background:#1e5ed8!important}.table-send-button:disabled{opacity:.65;cursor:not-allowed}
        .tabel thead th.grup,.tabel thead th.sub{background:#0d1b8c!important}
        .tabel tbody tr{transition:background .16s,box-shadow .16s}.tabel tbody tr:nth-child(even){background:#f8fbff!important}.tabel tbody tr:hover{background:#eef6ff!important;box-shadow:inset 3px 0 #f4c300}
        .tabel tbody td{border-color:#e2e8f0!important;color:#475569!important;vertical-align:middle}.tabel tbody td b{color:#123b5d!important}.tabel tbody td:nth-child(2),.tabel tbody td:nth-child(3){font-weight:650}
        .tabel .ikon-dtl,.tabel .detail-link{width:31px;height:31px;border-radius:8px!important;background:#eaf3ff!important;color:#1e6fa8!important;border:1px solid #c8def5!important;box-shadow:none!important}
        .tabel .ikon-dtl:hover,.tabel .detail-link:hover{background:#1e6fa8!important;color:#fff!important;transform:translateY(-1px)!important}
        .tabel .ikon-syarat{width:28px;height:28px;border-radius:8px!important;background:#f8fbff!important;color:#1e6fa8!important;border:1px solid #d5e5f5!important}.tabel .ikon-syarat.berkas-lengkap{background:#e8f7ef!important;color:#2e9b68!important;border-color:#b9e4ca!important}.tabel .ikon-syarat.berkas-belum{background:#fff7ed!important;color:#d97706!important;border-color:#f5d7a1!important}
        .tabel .pill{font-size:11px!important;font-weight:700;border-radius:999px!important;padding:5px 9px!important}
        @media(max-width:760px){.filter{padding:64px 12px 14px!important}.filter>*{flex:1 1 100%}.filter::before{padding-left:12px}.filter::after{left:12px}.kartu-judul{align-items:flex-start;flex-direction:column}.kartu .scroll{margin:0 10px 10px}.table-title-content{align-items:flex-start;flex-direction:column;gap:8px}.table-send-button{align-self:flex-start}}
    </style>
    @endpush
@endsection
