@extends('layouts.app')

@section('title', $judul)
@section('judul', $judul)

@section('isi')
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

    <form class="box filter perluasan-filter" method="GET">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / no agenda / alamat">
        <select name="ulp">
            <option value="">Semua ULP</option>
            @foreach ($ulps as $ulp)
                <option value="{{ $ulp->id }}" @selected((string) request('ulp') === (string) $ulp->id)>{{ $ulp->nama }}</option>
            @endforeach
        </select>
        <select name="jenis">
            <option value="">Semua jenis transaksi</option>
            @foreach ($jenisPilihan as $jenis)
                <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">Semua status</option>
            @foreach ($statusPilihan as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @if (auth()->user()->role?->type !== 'UP3')
            <select name="tahap">
                <option value="">Semua tahap</option>
                    @foreach ($tahapPilihan as $tahapOption => $tahapLabel)
                        <option value="{{ $tahapOption }}" @selected(request('tahap') === $tahapOption)>{{ $tahapLabel }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit" class="btn">Filter</button>
        <a href="{{ url()->current() }}" class="btn btn-abu">Reset</a>
    </form>

    <div class="box kartu">
        <div class="kartu-judul">
            <span>DATA {{ strtoupper($judul) }}</span>
            <span class="pill">{{ $data->total() }} data</span>
        </div>

        <div class="scroll">
            <table class="tabel tabel-biru">
                <thead>
                    <tr>
                        <th rowspan="2"><input type="checkbox" title="Pilih semua"></th>
                        <th rowspan="2">NO.</th>
                        <th rowspan="2">{{ $showBerkasUlp ? 'ULP PENGIRIM' : 'ASAL ULP' }}</th>
                        <th rowspan="2">DETAIL</th>
                        @if ($showBerkasUlp)<th rowspan="2">BERKAS</th>@endif
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
                        <th class="sub">TARIF</th>
                        <th class="sub">DAYA</th>
                        <th class="sub">TARIF</th>
                        <th class="sub">DAYA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $row)
                        @php
                            $m = $row->permintaan; $pv = $row->pengirimanVendor; $pk = $row->pengirimanKonstruksi;
                            $ulpSent = $row->tahap !== 'ULP' || $row->tujuan_perluasan;
                            $planningDone = count($row->laporanVendor?->berkas_hasil_paths ?? []) > 0 || count($pk?->hasil_paths ?? []) > 0;
                            $constructionDone = count($row->hasil_konstruksi_paths ?? []) > 0 || count($pk?->hasil_konstruksi_paths ?? []) > 0;
                            $transactionDone = count($row->hasil_transaksi_paths ?? []) > 0;
                            $networkDone = count($row->hasil_jaringan_paths ?? []) > 0;
                        @endphp
                        <tr>
                            <td><input type="checkbox" value="{{ $row->id }}"></td>
                            <td>{{ $data->firstItem() + $loop->index }}.</td>
                            <td><b>{{ $row->asal_ulp }}</b></td>
                            <td>
                                <button
                                    type="button"
                                    class="ikon-dtl tombol-detail-perluasan"
                                    title="Detail"
                                    data-id="{{ $row->id }}"
                                    data-rab-url="{{ route('pbpd.rab.update', $row->id) }}"
                                    data-vendor-url="{{ route('pbpd.vendor.send', $row->id) }}"
                                    data-report-file-url="{{ route('vendor.tiang.report.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-laporan-files="{{ base64_encode(json_encode($row->laporanVendor?->berkas_paths ?? [])) }}"
                                    data-result-file-url="{{ route('vendor.tiang.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-result-delete-url="{{ route('vendor.tiang.result.delete', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-result-files="{{ base64_encode(json_encode($row->laporanVendor?->berkas_hasil_paths ?? [])) }}"
                                    data-no-agenda="{{ $row->no_agenda }}"
                                    data-idpel="{{ $row->id_pelanggan }}"
                                    data-nama="{{ $row->nama_pelanggan }}"
                                    data-alamat="{{ $row->alamat }}"
                                    data-transaksi="{{ $row->jenis_transaksi }}"
                                    data-status="{{ $row->status }}"
                                    data-tahap="{{ $row->tahap }}"
                                    data-bp="{{ $row->bp }}"
                                    data-total-biaya="{{ $row->total_biaya }}"
                                    data-tgl-mohon="{{ $row->tgl_mohon }}"
                                    data-tgl-bayar="{{ $row->tgl_bayar }}"
                                    data-rab="{{ $row->rab }}"
                                    data-detail-url="{{ route('pbpd.detail.update', $row->id) }}"
                                    data-jenis-tiang="{{ $m?->jenis_tiang }}"
                                    data-jml-tiang="{{ $m?->jml_tiang }}"
                                    data-jenis-konduktor-tm="{{ $m?->jenis_konduktor_tm }}"
                                    data-jml-konduktor-tm="{{ $m?->jml_konduktor_tm }}"
                                    data-jenis-konduktor-tr="{{ $m?->jenis_konduktor_tr }}"
                                    data-jml-konduktor-tr="{{ $m?->jml_konduktor_tr }}"
                                    data-jenis-trafo="{{ $m?->jenis_trafo }}"
                                    data-jml-trafo="{{ $m?->jml_trafo }}"
                                    data-jenis-kwh-meter="{{ $m?->jenis_kwh_meter }}"
                                    data-jml-kwh-meter="{{ $m?->jml_kwh_meter }}"
                                    data-jenis-panel-meter="{{ $m?->jenis_panel_meter }}"
                                    data-jml-panel-meter="{{ $m?->jml_panel_meter }}"
                                    data-vendor-id="{{ $pv?->vendor_id }}"
                                    data-kelayakan="{{ $pv?->status_kelayakan }}"
                                    data-konstruksi-vendor="{{ $pk?->vendor?->nama }}"
                                    data-konstruksi-user="{{ $pk?->vendor?->user?->user_id }}"
                                    data-konstruksi-lengkap="{{ $pk?->pekerjaan_lengkap ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-sesuai="{{ $pk?->pekerjaan_sesuai_wo ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-foto="{{ $pk?->foto_terlampir ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-siap="{{ $pk?->siap_dilanjutkan ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-catatan="{{ $pk?->catatan }}"
                                    data-konstruksi-laporan-files="{{ base64_encode(json_encode($pk?->laporan_paths ?? [])) }}"
                                    data-konstruksi-laporan-file-url="{{ route('vendor.konstruksi.report.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-konstruksi-hasil-file-url="{{ route('vendor.konstruksi.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-konstruksi-hasil-konstruksi-files="{{ base64_encode(json_encode($row->hasil_konstruksi_paths ?? [])) }}"
                                    data-transaksi-hasil-file-url="{{ route('pbpd.transaksi.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-transaksi-hasil-files="{{ base64_encode(json_encode($row->hasil_transaksi_paths ?? [])) }}"
                                    data-jaringan-hasil-file-url="{{ route('pbpd.jaringan.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-jaringan-hasil-files="{{ base64_encode(json_encode($row->hasil_jaringan_paths ?? [])) }}"
                                    data-proses-ulp="{{ $ulpSent ? '1' : '0' }}"
                                    data-proses-ulp-date="{{ $m?->dikirim_at?->format('Y-m-d H:i:s') ?? $row->created_at }}"
                                    data-proses-perencanaan="{{ $planningDone ? '1' : '0' }}"
                                    data-proses-perencanaan-date="{{ $row->laporanVendor?->berkas_hasil_at?->format('Y-m-d H:i:s') ?? $pk?->hasil_perencanaan_at?->format('Y-m-d H:i:s') }}"
                                    data-proses-konstruksi="{{ $constructionDone ? '1' : '0' }}"
                                    data-proses-konstruksi-date="{{ $row->hasil_konstruksi_at?->format('Y-m-d H:i:s') ?? $pk?->hasil_konstruksi_at?->format('Y-m-d H:i:s') }}"
                                    data-proses-transaksi="{{ $transactionDone ? '1' : '0' }}"
                                    data-proses-transaksi-date="{{ $row->hasil_transaksi_at?->format('Y-m-d H:i:s') }}"
                                    data-proses-jaringan="{{ $networkDone ? '1' : '0' }}"
                                    data-proses-jaringan-date="{{ $row->hasil_jaringan_at?->format('Y-m-d H:i:s') }}"
                                    data-laporan-lengkap="{{ $row->laporanVendor?->pekerjaan_lengkap ? 'Ya' : 'Tidak' }}"
                                    data-laporan-sesuai="{{ $row->laporanVendor?->pekerjaan_sesuai_wo ? 'Ya' : 'Tidak' }}"
                                    data-laporan-foto="{{ $row->laporanVendor?->foto_terlampir ? 'Ya' : 'Tidak' }}"
                                    data-laporan-siap="{{ $row->laporanVendor?->siap_dilanjutkan ? 'Ya' : 'Tidak' }}"
                                    data-laporan-catatan="{{ $row->laporanVendor?->catatan }}"
                                    data-laporan-exists="{{ $row->laporanVendor ? '1' : '0' }}"
                                ><svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5h9l3 3V20.5H6z"/><path d="M15 3.5v4h3M9 12h6M9 16h6"/></svg></button>
                            </td>
                            @if ($showBerkasUlp)
                                <td>
                                    <div class="berkas-ulp-cell">
                                        @forelse (($row->berkas_pendukung_paths ?? []) as $index => $path)
                                            <a href="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => $index]) }}" target="_blank" rel="noopener" title="Buka berkas pendukung">Pendukung {{ $index + 1 }}</a>
                                        @empty
                                            <span class="berkas-ulp-empty">Pendukung -</span>
                                        @endforelse
                                        @forelse (($row->berkas_ijin_paths ?? []) as $index => $path)
                                            <a href="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'ijin', 'index' => $index]) }}" target="_blank" rel="noopener" title="Buka berkas ijin">Ijin {{ $index + 1 }}</a>
                                        @empty
                                            <span class="berkas-ulp-empty">Ijin -</span>
                                        @endforelse
                                    </div>
                                </td>
                            @endif
                            <td>
                                <span class="pill pill-ungu">
                                    {{ [
                                        'PB' => 'Pasang Baru',
                                        'PD' => 'Perubahan Daya',
                                        'PASANG BARU' => 'Pasang Baru',
                                        'PERUBAHAN DAYA' => 'Perubahan Daya',
                                    ][$row->jenis_transaksi] ?? $row->jenis_transaksi }}
                                </span>
                            </td>
                            <td><span class="pill pill-hijau">{{ ucfirst(strtolower($row->status)) }}</span></td>
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
                        <tr><td colspan="{{ 17 + ($showBerkasUlp ? 1 : 0) }}" style="text-align:center">Belum ada data dikirim.</td></tr>
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

    <div id="detailModalPerluasan" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="detailModalPerluasanTitle">
            <button type="button" class="detail-modal-close" id="closeDetailPerluasan" aria-label="Tutup">&times;</button>
            <h3 id="detailModalPerluasanTitle">Detail Data PB/PD</h3>
            <div class="detail-grid">
                <div><small>No Agenda</small><b id="perluasanNoAgenda"></b></div>
                <div><small>IDPEL</small><b id="perluasanIdpel"></b></div>
                <div><small>Nama Pelanggan</small><b id="perluasanNama"></b></div>
                <div><small>Transaksi</small><b id="perluasanTransaksi"></b></div>
                <div><small>Status</small><b id="perluasanStatus"></b></div>
                <div><small>BP (Total Biaya)</small><b id="perluasanBp"></b></div>
                <div><small>Total Biaya</small><b id="perluasanTotalBiaya"></b></div>
                <div><small>Tanggal Mohon</small><b id="perluasanTglMohon"></b></div>
                <div><small>Tanggal Bayar</small><b id="perluasanTglBayar"></b></div>
                <div><small>RAB</small><b id="perluasanRabValue"></b></div>
                <div class="detail-full"><small>Alamat</small><b id="perluasanAlamat"></b></div>
            </div>
            <div class="proses-box">
                <h4>Proses</h4>
                <table class="proses-tabel"><thead><tr><th>Proses</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>
                    <tr><td>Pengiriman ULP</td><td id="prosesUlpStatus"></td><td id="prosesUlpDate">-</td></tr>
                    <tr><td>Berkas WO</td><td id="prosesPerencanaanStatus"></td><td id="prosesPerencanaanDate">-</td></tr>
                    <tr><td>Berkas BA Checklist</td><td id="prosesKonstruksiStatus"></td><td id="prosesKonstruksiDate">-</td></tr>
                    <tr><td>Foto Cek KWH Meter</td><td id="prosesTransaksiStatus"></td><td id="prosesTransaksiDate">-</td></tr>
                    <tr><td>Berkas BA Operasi</td><td id="prosesJaringanStatus"></td><td id="prosesJaringanDate">-</td></tr>
                </tbody></table>
            </div>
            @if ($canViewMaterial)
            @if ($canViewMaterial && $tujuan !== 'TANPA_PERLUASAN')
                <form id="detailFormPerluasan" method="POST">
                    @csrf
                    <div class="detail-edit-table">
                        <label>RAB <input id="rabInputPerluasan" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB" @disabled(!$canEditRab)><input id="rabValuePerluasan" name="rab" type="hidden"></label>
                        <label>Jenis Tiang <select id="jenisTiangPerluasan" name="jenis_tiang" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>9</option><option>11</option><option>13</option></select></label>
                        <label>Jumlah Tiang <input id="jmlTiangPerluasan" name="jml_tiang" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Konduktor TM<select id="jenisKonduktorTmPerluasan" name="jenis_konduktor_tm" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>AAAC-S 240</option><option>AAAC-S 150</option><option>AAAC-S 70</option><option>AAAC 240</option><option>AAAC 150</option><option>AAAC 70</option></select></label>
                        <label>Jumlah Konduktor TM<input id="jmlKonduktorTmPerluasan" name="jml_konduktor_tm" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Konduktor TR<select id="jenisKonduktorTrPerluasan" name="jenis_konduktor_tr" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>LVTC 3x70+1x70</option><option>LVTC 3x70+1x50</option></select></label>
                        <label>Jumlah Konduktor TR<input id="jmlKonduktorTrPerluasan" name="jml_konduktor_tr" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Trafo <select id="jenisTrafoPerluasan" name="jenis_trafo" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>0</option><option>50</option><option>100</option><option>160</option><option>200</option><option>250</option></select></label>
                        <label>Jumlah Trafo <input id="jmlTrafoPerluasan" name="jml_trafo" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis KWH Meter <select id="jenisKwhPerluasan" name="jenis_kwh_meter" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>Prabayar</option><option>Pascabayar</option></select></label>
                        <label>Jumlah KWH Meter <input id="jmlKwhPerluasan" name="jml_kwh_meter" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Panel Meter <select id="jenisPanelPerluasan" name="jenis_panel_meter" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>Umum</option><option>53 kva</option><option>66 kva</option><option>105 kva</option><option>164 kva</option><option>197 kva</option></select></label>
                        <label>Jumlah Panel Meter <input id="jmlPanelPerluasan" name="jml_panel_meter" type="number" min="0" @disabled(!$canEditMaterial)></label>
                    </div>
                    @if ($canEditRab)
                        <button type="submit" class="btn">Simpan</button>
                    @endif
                </form>
            @endif

            @if ($canSendVendor)
                <form id="vendorFormPerluasan" method="POST" action="#" enctype="multipart/form-data" class="vendor-form">
                    @csrf
                    <h4>Kirim ke Vendor Tiang</h4>
                    <label>Tujuan kirim ke PT
                        <select id="vendorIdPerluasan" name="vendor_id" required>
                            <option value="">-- Pilih PT --</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->nama }} ({{ $vendor->user?->user_id }})</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="vendor-kelayakan">
                        <span>Status kelayakan</span>
                        <label><input type="radio" name="status_kelayakan" value="LAYAK" required> LAYAK</label>
                        <label><input type="radio" name="status_kelayakan" value="TIDAK LAYAK"> TIDAK LAYAK</label>
                    </div>
                    <label>Upload berkas WO tiang
                        <input type="file" name="wo_tiang" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </label>
                    <button type="submit" class="btn">Kirim ke Vendor</button>
                </form>
            @endif

            @if ($canSendKonstruksi)
                <form id="constructionForm" method="POST" action="#" enctype="multipart/form-data" class="vendor-form">
                    @csrf
                    <h4>Kirim ke Vendor Konstruksi</h4>
                    <label>Tujuan kirim ke PT
                        <select id="constructionVendorId" name="vendor_id" required>
                            <option value="">-- Pilih PT --</option>
                            @foreach ($vendorsKonstruksi as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->nama }} ({{ $vendor->user?->user_id }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Upload berkas pengiriman
                        <input type="file" name="berkas[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </label>
                    <button type="submit" class="btn">Kirim ke Vendor Konstruksi</button>
                </form>
            @endif

            @if ($canSendVendor)
                <div class="vendor-report-result" id="vendorReportResult">
                    <h4>Laporan dari Vendor</h4>
                    <div><span>Foto Pengiriman tiang</span><b id="laporanLengkap">-</b></div>
                    <div><span>Foto penanaman tiang</span><b id="laporanSesuai">-</b></div>
                    <div><span>Foto tiang berdiri</span><b id="laporanFoto">-</b></div>
                    <div><span>Foto pekerjaan selesai</span><b id="laporanSiap">-</b></div>
                    <div><span>Catatan</span><b id="laporanCatatan">-</b></div>
                    <div><span>Berkas laporan</span><b id="laporanFiles">-</b></div>
                </div>
            @endif
            @endif
            @if ($canEditRab && $tujuan === 'TANPA_PERLUASAN')
                <form id="tanpaRabForm" method="POST" class="detail-rab-form">
                    @csrf
                    <label for="tanpaRabInput">RAB</label>
                    <div class="detail-rab-input">
                        <span>Rp</span>
                        <input id="tanpaRabInput" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB">
                        <input id="tanpaRabValue" name="rab" type="hidden">
                    </div>
                    <button type="submit" class="btn">Simpan RAB</button>
                </form>
            @endif
            <div class="planning-result-box">
                <h4>Berkas WO</h4>
                <div id="resultFiles">-</div>
                @if ($canSendVendor)
                    <div id="resultWaitingMessage" class="result-waiting-message" hidden>Menunggu laporan dari vendor terlebih dahulu.</div>
                    <form id="resultUploadForm" method="POST" action="{{ request()->url() }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pelanggan_id" id="resultPelangganId">
                        <input type="file" name="berkas_hasil[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas Hasil</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box construction-result-upload-box">
                <h4>Berkas BA Checklist</h4>
                <div id="constructionResultFiles">-</div>
                @if ($canSendKonstruksi)
                    <form id="constructionResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_konstruksi[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Konstruksi</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box transaksi-result-upload-box">
                <h4>Foto Cek KWH Meter</h4>
                <div id="transaksiResultFiles">-</div>
                @if ($canUploadTransaksi)
                    <form id="transaksiResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_transaksi[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Transaksi</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box jaringan-result-upload-box">
                <h4>Berkas BA Operasi</h4>
                <div id="jaringanResultFiles">-</div>
                @if ($canUploadJaringan && $tujuan !== 'TANPA_PERLUASAN')
                    <form id="jaringanResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_jaringan[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Jaringan</button>
                    </form>
                @endif
            </div>
            @if ($canSendKonstruksi)
            <div class="construction-report-box" id="constructionReportBox">
                <h4>Laporan Hasil Pekerjaan Vendor Konstruksi</h4>
                <div class="detail-grid">
                    <div><small>Vendor</small><b id="constructionReportVendor">-</b></div>
                    <div><small>User Vendor</small><b id="constructionReportUser">-</b></div>
                    <div><small>Pekerjaan lengkap</small><b id="constructionReportLengkap">-</b></div>
                    <div><small>Pekerjaan sesuai WO</small><b id="constructionReportSesuai">-</b></div>
                    <div><small>Foto dokumentasi</small><b id="constructionReportFoto">-</b></div>
                    <div><small>Siap dilanjutkan</small><b id="constructionReportSiap">-</b></div>
                    <div class="detail-full"><small>Catatan</small><b id="constructionReportCatatan">-</b></div>
                    <div class="detail-full"><small>Berkas laporan</small><div id="constructionReportFiles">-</div></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        .perluasan-filter{position:relative;display:flex;align-items:center;column-gap:9px!important;row-gap:10px!important;flex-wrap:wrap;padding:70px 16px 16px!important;margin-bottom:16px!important;background:#fff!important;border:1px solid #dbe5f0!important;border-radius:14px!important;box-shadow:0 6px 18px rgba(13,27,140,.06)!important}
        .perluasan-filter::before{content:'DAFTAR TRANSAKSI';position:absolute;inset:0 0 auto;height:50px;display:flex;align-items:flex-start;padding:9px 16px 0;border-radius:14px 14px 0 0;background:#0d1b8c;color:#fff;font-size:15px;font-weight:800;letter-spacing:.035em;line-height:1.1}
        .perluasan-filter::after{content:'Daftar Transaksi {{ strtoupper($judul) }}';position:absolute;top:31px;left:16px;color:#dbeafe;font-size:11px;font-weight:600;line-height:1.1}
        .content .kartu .kartu-judul>span:first-child{font-size:16px!important;font-weight:800!important;letter-spacing:.015em!important}
        .content .kartu .kartu-judul .pill{font-size:12px!important}
        .perluasan-filter input,.perluasan-filter select{min-height:36px;padding:8px 11px;border:1px solid #9fb4ca;border-radius:9px;background:#fff;color:#243b53;font-size:12px;box-shadow:0 1px 3px rgba(18,59,93,.06)}
        .perluasan-filter input::placeholder{color:#627d98;opacity:1}.perluasan-filter select option{background:#fff;color:#243b53}
        .perluasan-filter input{width:165px;min-width:165px;flex:0 1 165px}.perluasan-filter select{min-width:0;flex:0 0 auto}.perluasan-filter select[name="ulp"]{width:125px}.perluasan-filter select[name="jenis"]{width:155px}.perluasan-filter select[name="status"]{width:115px}.perluasan-filter select[name="tahap"]{width:155px}.perluasan-filter input:focus,.perluasan-filter select:focus{outline:0;border-color:#1e6fa8;box-shadow:0 0 0 3px rgba(30,111,168,.13);background:#fff}
        .perluasan-filter .btn{min-height:36px;padding:8px 13px;border:0;border-radius:9px;background:#123b5d;color:#fff;font-weight:700;box-shadow:0 4px 10px rgba(18,59,93,.2)}.perluasan-filter .btn:hover{background:#0d2e49}.perluasan-filter .btn-abu{background:#eef3f8;color:#243b53;border:1px solid #b8c9da;box-shadow:none}.perluasan-filter .btn-abu:hover{background:#dfe9f2;color:#123b5d}
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
        .detail-edit-table { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; margin:18px 0; }
        .detail-edit-table label { font-weight:600; color:#536477; }
        .detail-edit-table input, .detail-edit-table select { display:block; width:100%; margin-top:5px; padding:8px; border:1px solid #b9c7d5; border-radius:5px; background:#fff8ee; }
        .vendor-form { margin-top:20px; padding-top:16px; border-top:1px solid #d6e2ee; display:grid; gap:10px; }
        .vendor-form h4 { margin:0; color:#0b3d6b; }
        .vendor-form label { display:grid; gap:5px; font-weight:600; color:#536477; }
        .vendor-form select, .vendor-form input[type=file] { width:100%; padding:8px; border:1px solid #b9c7d5; border-radius:5px; background:#fff; }
        .vendor-kelayakan { display:flex; gap:12px; align-items:center; flex-wrap:wrap; color:#536477; }
        .vendor-kelayakan label { display:inline-flex; align-items:center; gap:4px; }
        .vendor-report-result { display:grid; gap:8px; margin-top:16px; padding:14px; border:1px solid #c8d6e5; border-radius:8px; background:#eef7ff; }
        .vendor-report-result h4 { margin:0 0 4px; color:#0b3d6b; }
        .vendor-report-result div { display:flex; justify-content:space-between; gap:12px; color:#536477; }
        .vendor-report-result b { color:#223; }
        .planning-result-box { display:grid; gap:10px; margin-top:16px; padding:14px; border:1px solid #c8d6e5; border-radius:8px; background:#f7fbff; }
        .planning-result-box h4 { margin:0; color:#0b3d6b; }
        .planning-result-box a { color:#0b3d6b; }
        .construction-report-box { display:grid; gap:10px; margin-top:16px; padding:14px; border:1px solid #c8d6e5; border-radius:8px; background:#f3f8ff; }
        .construction-report-box h4 { margin:0; color:#0b3d6b; }
        .construction-report-box a { color:#0b3d6b; }
        .berkas-ulp-cell{display:flex;flex-direction:column;align-items:flex-start;gap:5px;min-width:105px}
        .berkas-ulp-cell a{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border:1px solid #c8def5;border-radius:7px;background:#f3f8ff;color:#1e6fa8!important;font-size:10px;font-weight:700;text-decoration:none;white-space:nowrap}
        .berkas-ulp-cell a::before{content:'↗';font-size:10px}
        .berkas-ulp-cell a:hover{background:#1e6fa8;color:#fff!important;border-color:#1e6fa8}
        .berkas-ulp-empty{color:#94a3b8;font-size:10px}
        .planning-result-box input[type=file] { padding:8px; border:1px solid #b9c7d5; border-radius:6px; background:#fff; }
        .detail-modal-box .planning-result-box{display:grid!important;gap:11px!important;margin-top:12px!important;padding:15px!important;border:1px solid #d9e2ec!important;border-radius:12px!important;background:#f8fbff!important;box-shadow:0 2px 8px rgba(18,59,93,.05)!important}
        .detail-modal-box .planning-result-box h4{margin:0!important;padding-bottom:8px!important;border-bottom:1px solid #e2e8f0!important;color:#123b5d!important;font-size:14px!important;line-height:1.35!important}
        .detail-modal-box .planning-result-box>div[id$="Files"]{min-height:32px;padding:8px 10px;border:1px solid #d9e2ec;border-radius:8px;background:#fff;color:#627d98;font-size:12px;line-height:1.4;overflow-wrap:anywhere}
        .detail-modal-box .planning-result-box>div[id$="Files"] a{color:#1e6fa8!important;font-weight:600;text-decoration:underline;overflow-wrap:anywhere}
        .detail-modal-box .planning-result-box form{display:flex!important;align-items:center!important;gap:9px!important;flex-wrap:wrap!important;margin:0!important}
        .detail-modal-box .planning-result-box form[hidden]{display:none!important}
        .detail-modal-box .planning-result-box form input[type=file]{flex:1 1 220px;min-height:36px;padding:6px 8px!important;border:1px solid #b8c9da!important;border-radius:8px!important;background:#fff!important;color:#627d98!important;font-size:12px!important}
        .detail-modal-box .planning-result-box form button{min-height:36px!important;padding:8px 12px!important;background:#111c91!important;color:#fff!important;border:0!important;border-radius:8px!important;font-size:12px!important;white-space:nowrap}
        .detail-modal-box .planning-result-box form button:hover{background:#0d166f!important}
        .detail-modal-box .result-waiting-message{padding:9px 10px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px}
        @media(max-width:600px){.detail-modal-box .planning-result-box form{align-items:stretch!important;flex-direction:column!important}.detail-modal-box .planning-result-box form input[type=file],.detail-modal-box .planning-result-box form button{width:100%!important;flex-basis:auto}}
        .result-file-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:4px 0; }
        .result-delete { padding:4px 8px; border:0; border-radius:4px; background:#a61b1b; color:#fff; cursor:pointer; font-size:12px; }
        .result-waiting-message { padding:10px; border-radius:6px; background:#fff5d6; color:#795900; }
        .proses-box { margin-top:18px; padding:12px; border:1px solid #c8d6e5; border-radius:8px; background:#f8fbff; }
        .proses-box h4 { margin:0 0 8px; color:#0b3d6b; }
        .proses-tabel { width:100%; border:1px solid #d6e2ee; border-radius:6px; overflow:hidden; font-size:12px; }
        .proses-tabel th, .proses-tabel td { padding:8px 10px; border-bottom:1px solid #e1e8ef; text-align:left; }
        .proses-tabel th { background:#eef3f8; color:#536477; }
        .proses-tabel tr:last-child td { border-bottom:0; }
        .proses-selesai { color:#159447; font-weight:600; }
        .proses-belum { color:#d33; font-weight:600; }
        /* Tema warna baru untuk modal Tanpa Perluasan; ukuran dan susunan tetap. */
        #detailModalPerluasan .detail-modal-box{background:#f8fafc!important;border-color:#d8e3ef!important}
        #detailModalPerluasan .detail-modal-box h3{background:#0d1b8c!important;color:#fff!important}
        #detailModalPerluasan .detail-modal-close{background:#fff!important;color:#0d1b8c!important}
        #detailModalPerluasan .detail-grid div{background:#fff!important;border-color:#d8e3ef!important}
        #detailModalPerluasan .detail-grid small{color:#6b87a4!important}
        #detailModalPerluasan .detail-grid b{color:#243b53!important}
        #detailModalPerluasan .proses-box{background:#fff!important;border-color:#d8e3ef!important}
        #detailModalPerluasan .proses-box h4{color:#123b5d!important}
        #detailModalPerluasan .proses-tabel{border-color:#d8e3ef!important;background:#fff!important}
        #detailModalPerluasan .proses-tabel th{background:#edf4ff!important;color:#123b5d!important}
        #detailModalPerluasan .detail-rab-form{background:#fff!important;border-color:#d8e3ef!important}
        #detailModalPerluasan .detail-rab-form label{color:#56718d!important}
        #detailModalPerluasan .detail-rab-input{background:#fff!important;border-color:#cbd9e8!important}
        #detailModalPerluasan .detail-rab-input span{background:#0d1b8c!important;color:#fff!important}
        #detailModalPerluasan .detail-rab-input input{background:#fff!important;color:#243b53!important}
        #detailModalPerluasan .detail-rab-form .btn{background:#2b73fe!important;color:#fff!important}
        #detailModalPerluasan .detail-rab-form .btn:hover{background:#1f5ed8!important}
        @media (max-width:600px) { .perluasan-filter{padding:64px 12px 14px!important}.perluasan-filter::before{padding-left:12px}.perluasan-filter::after{left:12px}.perluasan-filter>*{flex:1 1 100%}.detail-grid { grid-template-columns:1fr; } .detail-full { grid-column:auto; } .detail-rab-form { align-items:stretch; flex-direction:column; } }
    </style>

    <script>
        (() => {
            const modal = document.getElementById('detailModalPerluasan');
            const form = document.getElementById('detailFormPerluasan');
            const tanpaRabForm = document.getElementById('tanpaRabForm');
            const vendorForm = document.getElementById('vendorFormPerluasan');
            const constructionForm = document.getElementById('constructionForm');
            const constructionResultUploadForm = document.getElementById('constructionResultUploadForm');
            const transaksiResultUploadForm = document.getElementById('transaksiResultUploadForm');
            const jaringanResultUploadForm = document.getElementById('jaringanResultUploadForm');
            const constructionReportBox = document.getElementById('constructionReportBox');
            const resultUploadForm = document.getElementById('resultUploadForm');
            const resultWaitingMessage = document.getElementById('resultWaitingMessage');
            const setText = (id, value) => { document.getElementById(id).textContent = value || '-'; };
            const money = (value) => value ? Number(value).toLocaleString('id-ID') : '-';
            const formatRupiah = (value) => {
                const number = Number(value);
                return Number.isFinite(number) && number > 0 ? Math.round(number).toLocaleString('id-ID') : '';
            };
            const formatRupiahInput = (value) => {
                const digits = String(value ?? '').replace(/\D/g, '');
                return digits ? Number(digits).toLocaleString('id-ID') : '';
            };
            const rabInputPerluasan = document.getElementById('rabInputPerluasan');
            const rabValuePerluasan = document.getElementById('rabValuePerluasan');
            const tanpaRabInput = document.getElementById('tanpaRabInput');
            const tanpaRabValue = document.getElementById('tanpaRabValue');
            rabInputPerluasan?.addEventListener('input', () => { rabInputPerluasan.value = formatRupiahInput(rabInputPerluasan.value); });
            tanpaRabInput?.addEventListener('input', () => { tanpaRabInput.value = formatRupiahInput(tanpaRabInput.value); });
            form?.addEventListener('submit', () => { if (rabValuePerluasan) rabValuePerluasan.value = (rabInputPerluasan?.value || '').replace(/\./g, '').replace(/,/g, '.'); });
            tanpaRabForm?.addEventListener('submit', () => { if (tanpaRabValue) tanpaRabValue.value = (tanpaRabInput?.value || '').replace(/\./g, '').replace(/,/g, '.'); });
            const date = (value) => value ? new Date(value).toLocaleDateString('id-ID') : '-';
            const setProcess = (name, done, value) => {
                const status = document.getElementById(`proses${name}Status`);
                const tanggal = document.getElementById(`proses${name}Date`);
                status.textContent = done === '1' ? '✓ Sudah lengkap' : '✕ Belum diunggah';
                status.className = done === '1' ? 'proses-selesai' : 'proses-belum';
                tanggal.textContent = done === '1' ? date(value) : '-';
            };

            document.querySelectorAll('.tombol-detail-perluasan').forEach((button) => {
                button.addEventListener('click', () => {
                    setText('perluasanNoAgenda', button.dataset.noAgenda);
                    setText('perluasanIdpel', button.dataset.idpel);
                    setText('perluasanNama', button.dataset.nama);
                    setText('perluasanAlamat', button.dataset.alamat);
                    setText('perluasanTransaksi', button.dataset.transaksi);
                    setText('perluasanStatus', button.dataset.status);
                    setText('perluasanBp', money(button.dataset.bp));
                    setText('perluasanTotalBiaya', money(button.dataset.totalBiaya));
                    setText('perluasanTglMohon', date(button.dataset.tglMohon));
                    setText('perluasanTglBayar', date(button.dataset.tglBayar));
                    setText('perluasanRabValue', money(button.dataset.rab));
                    setProcess('Ulp', button.dataset.prosesUlp, button.dataset.prosesUlpDate);
                    setProcess('Perencanaan', button.dataset.prosesPerencanaan, button.dataset.prosesPerencanaanDate);
                    setProcess('Konstruksi', button.dataset.prosesKonstruksi, button.dataset.prosesKonstruksiDate);
                    setProcess('Transaksi', button.dataset.prosesTransaksi, button.dataset.prosesTransaksiDate);
                    setProcess('Jaringan', button.dataset.prosesJaringan, button.dataset.prosesJaringanDate);
                    modal.hidden = false;
                    if (resultUploadForm) document.getElementById('resultPelangganId').value = button.dataset.id;
                    if (form) {
                        rabInputPerluasan.value = formatRupiah(button.dataset.rab || '');
                        rabValuePerluasan.value = button.dataset.rab || '';
                        form.action = button.dataset.detailUrl;
                        document.getElementById('jenisTiangPerluasan').value = button.dataset.jenisTiang || '';
                        document.getElementById('jmlTiangPerluasan').value = button.dataset.jmlTiang || '';
                        document.getElementById('jenisKonduktorTmPerluasan').value = button.dataset.jenisKonduktorTm || '';
                        document.getElementById('jmlKonduktorTmPerluasan').value = button.dataset.jmlKonduktorTm || '';
                        document.getElementById('jenisKonduktorTrPerluasan').value = button.dataset.jenisKonduktorTr || '';
                        document.getElementById('jmlKonduktorTrPerluasan').value = button.dataset.jmlKonduktorTr || '';
                        document.getElementById('jenisTrafoPerluasan').value = button.dataset.jenisTrafo || '';
                        document.getElementById('jmlTrafoPerluasan').value = button.dataset.jmlTrafo || '';
                        document.getElementById('jenisKwhPerluasan').value = button.dataset.jenisKwhMeter || '';
                        document.getElementById('jmlKwhPerluasan').value = button.dataset.jmlKwhMeter || '';
                        document.getElementById('jenisPanelPerluasan').value = button.dataset.jenisPanelMeter || '';
                        document.getElementById('jmlPanelPerluasan').value = button.dataset.jmlPanelMeter || '';
                    }
                    if (tanpaRabForm) {
                        tanpaRabInput.value = formatRupiah(button.dataset.rab || '');
                        tanpaRabValue.value = button.dataset.rab || '';
                        tanpaRabForm.action = button.dataset.rabUrl;
                    }
                    if (vendorForm) {
                        vendorForm.hidden = button.dataset.tahap !== 'PERENCANAAN';
                        vendorForm.action = button.dataset.vendorUrl;
                        document.getElementById('vendorIdPerluasan').value = button.dataset.vendorId || '';
                        vendorForm.querySelectorAll('input[name="status_kelayakan"]').forEach((radio) => {
                            radio.checked = radio.value === button.dataset.kelayakan;
                        });
                    }
                    if (constructionForm) {
                        constructionForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/kirim-konstruksi`;
                    }
                    let constructionResultFiles = [];
                    try { constructionResultFiles = JSON.parse(button.dataset.konstruksiHasilKonstruksiFiles ? atob(button.dataset.konstruksiHasilKonstruksiFiles) : '[]'); } catch (error) { constructionResultFiles = []; }
                    const constructionResultFilesBox = document.getElementById('constructionResultFiles');
                    if (constructionResultFilesBox) constructionResultFilesBox.innerHTML = constructionResultFiles.length
                        ? constructionResultFiles.map((path, index) => `<div class="result-file-row"><a href="${button.dataset.konstruksiHasilFileUrl.replace(/\/0$/, '/' + index)}" target="_blank" rel="noopener">📄 ${String(path).split('/').pop() || 'Berkas hasil konstruksi ' + (index + 1)}</a>${constructionResultUploadForm ? `<form method="POST" action="{{ url('/vendor/konstruksi') }}/${button.dataset.id}/hasil-konstruksi/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : ''}</div>`).join('')
                        : '-';
                    if (constructionResultUploadForm) {
                        constructionResultUploadForm.action = `{{ url('/vendor/konstruksi') }}/${button.dataset.id}/hasil-konstruksi`;
                    }
                    let transaksiResultFiles = [];
                    try { transaksiResultFiles = JSON.parse(button.dataset.transaksiHasilFiles ? atob(button.dataset.transaksiHasilFiles) : '[]'); } catch (error) { transaksiResultFiles = []; }
                    const transaksiResultFilesBox = document.getElementById('transaksiResultFiles');
                    if (transaksiResultFilesBox) transaksiResultFilesBox.innerHTML = transaksiResultFiles.length
                        ? transaksiResultFiles.map((path, index) => `<div class="result-file-row"><a href="${button.dataset.transaksiHasilFileUrl.replace(/\/0$/, '/' + index)}" target="_blank" rel="noopener">📄 ${String(path).split('/').pop() || 'Berkas hasil transaksi ' + (index + 1)}</a>${transaksiResultUploadForm ? `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/hasil-transaksi/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : ''}</div>`).join('')
                        : '-';
                    if (transaksiResultUploadForm) {
                        transaksiResultUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/hasil-transaksi`;
                    }
                    let jaringanResultFiles = [];
                    try { jaringanResultFiles = JSON.parse(button.dataset.jaringanHasilFiles ? atob(button.dataset.jaringanHasilFiles) : '[]'); } catch (error) { jaringanResultFiles = []; }
                    const jaringanResultFilesBox = document.getElementById('jaringanResultFiles');
                    if (jaringanResultFilesBox) jaringanResultFilesBox.innerHTML = jaringanResultFiles.length
                        ? jaringanResultFiles.map((path, index) => `<div class="result-file-row"><a href="${button.dataset.jaringanHasilFileUrl.replace(/\/0$/, '/' + index)}" target="_blank" rel="noopener">📄 ${String(path).split('/').pop() || 'Berkas hasil jaringan ' + (index + 1)}</a>${jaringanResultUploadForm ? `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/hasil-jaringan/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : ''}</div>`).join('')
                        : '-';
                    if (jaringanResultUploadForm) {
                        jaringanResultUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/hasil-jaringan`;
                    }
                    if (constructionReportBox) {
                        setText('constructionReportVendor', button.dataset.konstruksiVendor);
                        setText('constructionReportUser', button.dataset.konstruksiUser);
                        setText('constructionReportLengkap', button.dataset.konstruksiLengkap);
                        setText('constructionReportSesuai', button.dataset.konstruksiSesuai);
                        setText('constructionReportFoto', button.dataset.konstruksiFoto);
                        setText('constructionReportSiap', button.dataset.konstruksiSiap);
                        setText('constructionReportCatatan', button.dataset.konstruksiCatatan);
                        let constructionReportFiles = [];
                        try { constructionReportFiles = JSON.parse(button.dataset.konstruksiLaporanFiles ? atob(button.dataset.konstruksiLaporanFiles) : '[]'); } catch (error) { constructionReportFiles = []; }
                        const constructionReportFilesBox = document.getElementById('constructionReportFiles');
                        if (constructionReportFilesBox) constructionReportFilesBox.innerHTML = constructionReportFiles.length
                            ? constructionReportFiles.map((path, index) => `<a href="${button.dataset.konstruksiLaporanFileUrl.replace(/\/0$/, '/' + index)}" target="_blank" rel="noopener">📄 ${String(path).split('/').pop() || 'Berkas laporan ' + (index + 1)}</a>`).join('<br>')
                            : '-';
                    }
                    const reportResult = document.getElementById('vendorReportResult');
                    if (reportResult) {
                        setText('laporanLengkap', button.dataset.laporanLengkap);
                        setText('laporanSesuai', button.dataset.laporanSesuai);
                        setText('laporanFoto', button.dataset.laporanFoto);
                        setText('laporanSiap', button.dataset.laporanSiap);
                        setText('laporanCatatan', button.dataset.laporanCatatan);
                        let files = [];
                        try { files = JSON.parse(button.dataset.laporanFiles ? atob(button.dataset.laporanFiles) : '[]'); } catch (error) { files = []; }
                        const filesBox = document.getElementById('laporanFiles');
                        if (filesBox) filesBox.innerHTML = files.length
                            ? files.map((path, index) => `<a href="${button.dataset.reportFileUrl.replace(/\/0$/, '/' + index)}" target="_blank">📄 Berkas laporan ${index + 1}</a>`).join('<br>')
                            : '-';
                    }
                    const resultFilesBox = document.getElementById('resultFiles');
                    let resultFiles = [];
                    try { resultFiles = JSON.parse(button.dataset.resultFiles ? atob(button.dataset.resultFiles) : '[]'); } catch (error) { resultFiles = []; }
                    if (resultFilesBox) resultFilesBox.innerHTML = resultFiles.length
                            ? resultFiles.map((path, index) => `<div class="result-file-row"><a href="${button.dataset.resultFileUrl.replace(/\/0$/, '/' + index)}" target="_blank">📄 Berkas hasil ${index + 1}</a>${resultUploadForm ? `<form method="POST" action="${button.dataset.resultDeleteUrl.replace(/\/0$/, '/' + index)}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : ''}</div>`).join('')
                            : '-';
                    if (resultUploadForm) {
                        const hasVendorReport = button.dataset.laporanExists === '1';
                        resultUploadForm.hidden = !hasVendorReport;
                        resultUploadForm.action = `{{ url('/vendor/tiang') }}/${button.dataset.id}/hasil`;
                        if (resultWaitingMessage) resultWaitingMessage.hidden = hasVendorReport;
                    }
                });
            });

            document.getElementById('closeDetailPerluasan').addEventListener('click', () => { modal.hidden = true; });
            modal.addEventListener('click', (event) => { if (event.target === modal) modal.hidden = true; });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') modal.hidden = true; });
        })();
    </script>
@endsection
