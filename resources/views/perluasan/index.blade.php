@extends('layouts.app')

@php
    $deskripsiPerluasan = match ($tujuan) {
        'JTM' => 'Daftar transaksi PB/PD dengan perluasan jaringan tegangan menengah',
        'JTR' => 'Daftar transaksi PB/PD dengan perluasan jaringan tegangan rendah',
        default => 'Daftar transaksi PB/PD yang tidak memerlukan perluasan jaringan',
    };
@endphp
@section('title', $judul)
@section('judul', $judul)
@section('deskripsi', $deskripsiPerluasan)

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
                            $vendorReportDone = filled($row->laporanVendor?->vendor_report_at);
                            $npsWoDone = count($row->nps_wo_paths ?? []) > 0 || filled($row->nps_wo_at);
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
                                    data-kelayakan-url="{{ route('pbpd.kelayakan.update', $row->id) }}"
                                    data-report-file-url="{{ route('vendor.tiang.report.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-laporan-files="{{ base64_encode(json_encode($row->laporanVendor?->berkas_paths ?? [])) }}"
                                    data-laporan-names="{{ base64_encode(json_encode($row->laporanVendor?->berkas_names ?? [])) }}"
                                    data-result-file-url="{{ route('vendor.tiang.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-result-delete-url="{{ route('vendor.tiang.result.delete', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-result-files="{{ base64_encode(json_encode($row->laporanVendor?->berkas_hasil_paths ?? [])) }}"
                                    data-result-names="{{ base64_encode(json_encode($row->laporanVendor?->berkas_hasil_names ?? [])) }}"
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
                                    data-jenis-tiang-tm="{{ $m?->jenis_tiang_tm ?? $m?->jenis_tiang }}"
                                    data-jml-tiang-tm="{{ $m?->jml_tiang_tm ?? $m?->jml_tiang }}"
                                    data-jenis-tiang-tr="{{ $m?->jenis_tiang_tr }}"
                                    data-jml-tiang-tr="{{ $m?->jml_tiang_tr }}"
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
                                    data-kelayakan="{{ $row->status_kelayakan ?? $pv?->status_kelayakan }}"
                                    data-konstruksi-vendor="{{ $pk?->vendor?->nama }}"
                                    data-konstruksi-vendor-id="{{ count($pk?->berkas_paths ?? []) > 0 ? $pk?->vendor_id : '' }}"
                                    data-konstruksi-has-delivery="{{ count($pk?->berkas_paths ?? []) > 0 ? '1' : '0' }}"
                                    data-konstruksi-user="{{ $pk?->vendor?->user?->user_id }}"
                                    data-konstruksi-lengkap="{{ $pk?->pekerjaan_lengkap ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-sesuai="{{ $pk?->pekerjaan_sesuai_wo ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-foto="{{ $pk?->foto_terlampir ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-siap="{{ $pk?->siap_dilanjutkan ? 'Ya' : 'Tidak' }}"
                                    data-konstruksi-catatan="{{ $pk?->catatan }}"
                                    data-konstruksi-laporan-files="{{ base64_encode(json_encode($pk?->laporan_paths ?? [])) }}"
                                    data-konstruksi-laporan-names="{{ base64_encode(json_encode($pk?->laporan_names ?? [])) }}"
                                    data-konstruksi-laporan-date="{{ $pk?->laporan_at?->format('Y-m-d H:i:s') }}"
                                    data-konstruksi-laporan-file-url="{{ route('vendor.konstruksi.report.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-konstruksi-hasil-file-url="{{ route('vendor.konstruksi.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-konstruksi-hasil-konstruksi-files="{{ base64_encode(json_encode($row->hasil_konstruksi_paths ?? [])) }}"
                                    data-nps-wo-file-url="{{ route('pbpd.nps-wo.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-nps-wo-files="{{ base64_encode(json_encode($row->nps_wo_paths ?? [])) }}"
                                    data-transaksi-hasil-file-url="{{ route('pbpd.transaksi.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-transaksi-hasil-files="{{ base64_encode(json_encode($row->hasil_transaksi_paths ?? [])) }}"
                                    data-jaringan-hasil-file-url="{{ route('pbpd.jaringan.result.file', ['pelanggan' => $row->id, 'index' => 0]) }}"
                                    data-jaringan-hasil-files="{{ base64_encode(json_encode($row->hasil_jaringan_paths ?? [])) }}"
                                    data-proses-ulp="{{ $ulpSent ? '1' : '0' }}"
                                    data-proses-ulp-date="{{ $m?->dikirim_at?->format('Y-m-d H:i:s') ?? $row->created_at }}"
                                    data-proses-perencanaan="{{ $planningDone ? '1' : '0' }}"
                                    data-proses-perencanaan-date="{{ $row->laporanVendor?->berkas_hasil_at?->format('Y-m-d H:i:s') ?? $pk?->hasil_perencanaan_at?->format('Y-m-d H:i:s') }}"
                                    data-proses-nps-wo="{{ $npsWoDone ? '1' : '0' }}"
                                    data-proses-nps-wo-date="{{ $row->nps_wo_at?->format('Y-m-d H:i:s') }}"
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
                                    data-laporan-date="{{ $row->laporanVendor?->vendor_report_at?->format('Y-m-d H:i:s') }}"
                                ><svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5h9l3 3V20.5H6z"/><path d="M15 3.5v4h3M9 12h6M9 16h6"/></svg></button>
                            </td>
                            @if ($showBerkasUlp)
                                <td>
                                    <div class="berkas-ulp-cell">
                                        @forelse (($row->berkas_pendukung_paths ?? []) as $index => $path)
                                            <a href="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => $index]) }}" target="_blank" rel="noopener" title="Buka berkas pendukung">{{ $row->berkas_pendukung_names[$index] ?? basename($path) }}</a>
                                        @empty
                                            <span class="berkas-ulp-empty">Pendukung -</span>
                                        @endforelse
                                        @forelse (($row->berkas_ijin_paths ?? []) as $index => $path)
                                            <a href="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'ijin', 'index' => $index]) }}" target="_blank" rel="noopener" title="Buka berkas ijin">{{ $row->berkas_ijin_names[$index] ?? basename($path) }}</a>
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

        {{ $data->links('pagination.faston') }}
    </div>

    <div id="detailModalPerluasan" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="detailModalPerluasanTitle">
            <header class="detail-modal-header">
                <div>
                    <h3 id="detailModalPerluasanTitle">Detail Data PB/PD</h3>
                    <p id="perluasanSummary" class="detail-modal-summary"></p>
                </div>
                <button type="button" class="detail-modal-close" id="closeDetailPerluasan" aria-label="Tutup detail">&times;</button>
            </header>

            <div class="detail-modal-body">
                <section class="detail-section" aria-labelledby="perluasanCustomerHeading">
                    <h4 id="perluasanCustomerHeading" class="detail-section-title">Informasi Pelanggan</h4>
                    <div class="detail-grid detail-customer-grid">
                        <div><small>No Agenda</small><b id="perluasanNoAgenda"></b></div>
                        <div><small>IDPEL</small><b id="perluasanIdpel"></b></div>
                        <div><small>Nama Pelanggan</small><b id="perluasanNama"></b></div>
                        <div><small>Transaksi</small><b id="perluasanTransaksi" class="detail-value-pill"></b></div>
                        <div><small>Status</small><b id="perluasanStatus" class="detail-value-pill"></b></div>
                        <div><small>Total Biaya</small><b id="perluasanTotalBiaya"></b></div>
                        <div class="detail-full"><small>Alamat</small><b id="perluasanAlamat"></b></div>
                    </div>
                </section>

                <section class="detail-section" aria-labelledby="perluasanCostHeading">
                    <h4 id="perluasanCostHeading" class="detail-section-title">Biaya dan Jadwal</h4>
                    <div class="detail-cost-grid">
                        <div><small>BP (Total Biaya)</small><b id="perluasanBp"></b></div>
                        <div><small>RAB</small><b id="perluasanRabValue" class="detail-rab-status"></b></div>
                        <div><small>Tanggal Mohon</small><b id="perluasanTglMohon"></b></div>
                        <div><small>Tanggal Bayar</small><b id="perluasanTglBayar"></b></div>
                    </div>
                </section>

                <section class="detail-section" aria-labelledby="perluasanProcessHeading">
                    <h4 id="perluasanProcessHeading" class="detail-section-title">Proses</h4>
                    <div class="proses-list">
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Pengiriman ULP</span><span class="proses-status proses-belum" id="prosesUlpStatus">Belum diunggah</span><time id="prosesUlpDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas Kajian Kelayakan</span><span class="proses-status proses-belum" id="prosesPerencanaanStatus">Belum diunggah</span><time id="prosesPerencanaanDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas WO NPS</span><span class="proses-status proses-belum" id="prosesNpsWoStatus">Belum diunggah</span><time id="prosesNpsWoDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas BA Checklist</span><span class="proses-status proses-belum" id="prosesKonstruksiStatus">Belum diunggah</span><time id="prosesKonstruksiDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Foto Cek KWH Meter</span><span class="proses-status proses-belum" id="prosesTransaksiStatus">Belum diunggah</span><time id="prosesTransaksiDate">-</time></div>
                        @if ($tujuan !== 'TANPA_PERLUASAN')
                            <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas BA Operasi</span><span class="proses-status proses-belum" id="prosesJaringanStatus">Belum diunggah</span><time id="prosesJaringanDate">-</time></div>
                        @endif
                    </div>
                </section>

                @if ($canViewMaterial || ($canEditRab && $tujuan === 'TANPA_PERLUASAN'))
                <section class="detail-section detail-technical-section" aria-labelledby="perluasanTechnicalHeading">
                    <h4 id="perluasanTechnicalHeading" class="detail-section-title">Data Teknis dan RAB</h4>
                @if ($canViewMaterial)
            @if ($tujuan !== 'TANPA_PERLUASAN')
                <form id="detailFormPerluasan" method="POST">
                    @csrf
                    <div class="detail-edit-table">
                        <label class="detail-field-rab">RAB (Rp) <input id="rabInputPerluasan" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB" @disabled(!$canEditRab)><input id="rabValuePerluasan" name="rab" type="hidden"></label>
                        <label>Jenis Tiang TM<select id="jenisTiangTmPerluasan" name="jenis_tiang_tm" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>11</option><option>13</option></select></label>
                        <label>Jumlah Tiang TM <input id="jmlTiangTmPerluasan" name="jml_tiang_tm" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Tiang TR<select id="jenisTiangTrPerluasan" name="jenis_tiang_tr" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>9</option></select></label>
                        <label>Jumlah Tiang TR<input id="jmlTiangTrPerluasan" name="jml_tiang_tr" type="number" min="0" @disabled(!$canEditMaterial)></label>
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
                                <option value="{{ $vendor->id }}"
                                    data-stock-tr="{{ $vendor->stokTiang?->stok_tr ?? 0 }}"
                                    data-stock-tm11="{{ $vendor->stokTiang?->stok_tm_11 ?? 0 }}"
                                    data-stock-tm13="{{ $vendor->stokTiang?->stok_tm_13 ?? 0 }}">
                                    {{ $vendor->nama }} — TR: {{ $vendor->stokTiang?->stok_tr ?? 0 }} | TM 11: {{ $vendor->stokTiang?->stok_tm_11 ?? 0 }} | TM 13: {{ $vendor->stokTiang?->stok_tm_13 ?? 0 }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <div id="vendorStockInfoPerluasan" class="vendor-stock-info" hidden>
                        <div class="vendor-stock-heading">
                            <span>Stok vendor terpilih</span>
                            <span id="vendorStockAvailabilityPerluasan" class="vendor-stock-availability"></span>
                        </div>
                        <div class="vendor-stock-values vendor-stock-values-three">
                            <span><b>TR</b><strong id="vendorStockTrPerluasan">0</strong><small> tiang</small></span>
                            <span><b>TM 11</b><strong id="vendorStockTm11Perluasan">0</strong><small> tiang</small></span>
                            <span><b>TM 13</b><strong id="vendorStockTm13Perluasan">0</strong><small> tiang</small></span>
                        </div>
                    </div>
                    <div id="vendorStockWarningPerluasan" class="vendor-stock-warning" hidden></div>
                    <label>Upload berkas WO tiang
                        <input type="file" name="wo_tiang" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </label>
                    <div id="vendorLockedNotice" class="vendor-locked-notice" hidden>Laporan vendor sudah diterima. Pengiriman ini terkunci dan tidak dapat diedit atau dihapus.</div>
                    <button type="submit" class="btn" id="vendorSubmitButton">Kirim ke Vendor</button>
                </form>
                <div id="vendorDeleteCard" class="vendor-delete-card" hidden>
                    <h4>Pengiriman Vendor Tiang</h4>
                    <p>Hapus pengiriman ini jika ingin mengganti atau membatalkan vendor.</p>
                    <form id="vendorDeleteForm" method="POST" action="#" class="vendor-delete-form" onsubmit="return confirm('Hapus pengiriman ke vendor tiang? Berkas laporan vendor terkait juga akan dihapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn">Hapus Pengiriman Vendor Tiang</button>
                    </form>
                </div>
            @endif

            @if ($canSendKonstruksi)
                <form id="constructionForm" method="POST" action="#" enctype="multipart/form-data" class="vendor-form">
                    @csrf
                    <h4>Kirim ke Vendor Konstruksi</h4>
                    <label>Tujuan kirim ke PT
                        <select id="constructionVendorId" name="vendor_id" required>
                            <option value="">-- Pilih PT --</option>
                            @foreach ($vendorsKonstruksi as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Upload berkas pengiriman
                        <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </label>
                    <div id="constructionLockedNotice" class="vendor-locked-notice" hidden>Laporan vendor konstruksi sudah diterima. Pengiriman ini terkunci dan tidak dapat diedit atau dihapus.</div>
                    <button type="submit" class="btn" id="constructionSubmitButton">Kirim ke Vendor Konstruksi</button>
                </form>
                <div id="constructionDeleteCard" class="vendor-delete-card" hidden>
                    <h4>Pengiriman Vendor Konstruksi (KHS)</h4>
                    <p>Hapus pengiriman ini jika ingin mengganti atau membatalkan vendor.</p>
                    <form id="constructionDeleteForm" method="POST" action="#" class="vendor-delete-form" onsubmit="return confirm('Hapus pengiriman ke vendor konstruksi? Berkas laporan vendor terkait juga akan dihapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn">Hapus Pengiriman Vendor Konstruksi</button>
                    </form>
                </div>
            @endif

            @if ($canSendVendor)
                <div class="vendor-report-result" id="vendorReportResult">
                    <h4>Laporan dari Vendor</h4>
                    <div><span>Foto Pengiriman tiang</span><b id="laporanLengkap">-</b></div>
                    <div><span>Foto penanaman tiang</span><b id="laporanSesuai">-</b></div>
                    <div><span>Foto tiang berdiri</span><b id="laporanFoto">-</b></div>
                    <div><span>Foto pekerjaan selesai</span><b id="laporanSiap">-</b></div>
                    <div><span>Catatan</span><b id="laporanCatatan">-</b></div>
                </div>
            @endif
            @endif
            @if ($canEditRab && $tujuan === 'TANPA_PERLUASAN')
                <form id="tanpaRabForm" method="POST" class="detail-rab-form">
                    @csrf
                    <label for="tanpaRabInput">RAB (Rp)
                        <input id="tanpaRabInput" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB">
                        <input id="tanpaRabValue" name="rab" type="hidden">
                    </label>
                    <button type="submit" class="btn">✓ &nbsp;Simpan RAB</button>
                </form>
            @endif

                </section>
                @endif

                <section class="detail-section detail-files-section" aria-labelledby="perluasanFilesHeading">
                    <h4 id="perluasanFilesHeading" class="detail-section-title">Berkas Pendukung</h4>
                    <div class="detail-file-list">
            <div class="planning-result-box">
                <h4>Berkas Kajian Kelayakan <span class="file-status" data-file-status-for="resultFiles">Belum diunggah</span></h4>
                @if ($canSendVendor)
                    <div class="vendor-kelayakan">
                        <span>Status kelayakan: <strong id="planningEligibilityStatus">Belum ditentukan</strong></span>
                        <form id="planningEligibilityForm" method="POST" action="#">
                            @csrf
                            <button type="submit" name="status_kelayakan" value="LAYAK" class="btn">Layak</button>
                            <button type="submit" name="status_kelayakan" value="TIDAK LAYAK" class="btn btn-abu" onclick="return confirm('Tolak data ini? Semua RAB, material, dan berkas yang sudah diunggah akan dihapus. Data dikembalikan ke PB/PD untuk diproses ulang.')">Tidak Layak</button>
                        </form>
                    </div>
                @endif
                <div id="resultFiles">-</div>
                @if ($canSendVendor)
                    <form id="resultUploadForm" method="POST" action="{{ request()->url() }}" enctype="multipart/form-data" hidden>
                        @csrf
                        <input type="hidden" name="pelanggan_id" id="resultPelangganId">
                        <input type="hidden" name="status_kelayakan" value="LAYAK">
                        <input type="file" name="berkas_hasil[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas Kelayakan</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box vendor-upload-result-box">
                <h4>Berkas Laporan Vendor <span class="file-status" data-file-status-for="vendorReportFiles">Belum diunggah</span></h4>
                <div id="vendorReportFiles">-</div>
            </div>
            <div class="planning-result-box nps-result-upload-box">
                <h4>Berkas WO <small class="upload-owner-label">NPS</small><span class="file-status" data-file-status-for="npsWoFiles">Belum diunggah</span></h4>
                <div id="npsWoFiles">-</div>
                @if ($canUploadNps)
                    <form id="npsWoUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_nps_wo[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas WO</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box construction-result-upload-box">
                <h4>Berkas BA Checklist <span class="file-status" data-file-status-for="constructionResultFiles">Belum diunggah</span></h4>
                <div id="constructionResultFiles">-</div>
                @if ($canSendKonstruksi)
                    <form id="constructionResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_konstruksi[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Konstruksi</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box construction-vendor-report-box">
                <h4>Berkas Laporan Vendor KHS <span class="file-status" data-file-status-for="constructionVendorReportFiles">Belum diunggah</span></h4>
                <div id="constructionVendorReportFiles">-</div>
            </div>
            <div class="planning-result-box transaksi-result-upload-box">
                <h4>Foto Cek KWH Meter <span class="file-status" data-file-status-for="transaksiResultFiles">Belum diunggah</span></h4>
                <div id="transaksiResultFiles">-</div>
                @if ($canUploadTransaksi)
                    <form id="transaksiResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_transaksi[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Transaksi</button>
                    </form>
                @endif
            </div>
            @if ($tujuan !== 'TANPA_PERLUASAN')
                <div class="planning-result-box jaringan-result-upload-box">
                    <h4>Berkas BA Operasi <span class="file-status" data-file-status-for="jaringanResultFiles">Belum diunggah</span></h4>
                    <div id="jaringanResultFiles">-</div>
                    @if ($canUploadJaringan)
                        <form id="jaringanResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="berkas_hasil_jaringan[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <button type="submit" class="btn">Upload Berkas/Foto Jaringan</button>
                        </form>
                    @endif
                </div>
            @endif
                    </div>
                </section>
            @if ($canSendKonstruksi)
            <div class="construction-report-box" id="constructionReportBox">
                <h4>Laporan Hasil Pekerjaan Vendor Konstruksi</h4>
                <div class="detail-grid">
                    <div><small>Vendor</small><b id="constructionReportVendor">-</b></div>
                    <div><small>User Vendor</small><b id="constructionReportUser">-</b></div>
                    <div><small>Material Lengkap</small><b id="constructionReportLengkap">-</b></div>
                    <div><small>Pekerjaan Dimulai</small><b id="constructionReportSesuai">-</b></div>
                    <div><small>Pekerjaan Selesai</small><b id="constructionReportFoto">-</b></div>
                    <div><small>Pekerjaan Siap Untuk Dioperasikan</small><b id="constructionReportSiap">-</b></div>
                    <div class="detail-full"><small>Catatan</small><b id="constructionReportCatatan">-</b></div>
                </div>
            </div>
            @endif
            </div>
            <footer class="detail-modal-footer">
                <button type="button" class="detail-modal-dismiss" id="dismissDetailPerluasan">Tutup</button>
            </footer>
        </div>
    </div>

    @push('page-styles')
    <style>
        .content .kartu .kartu-judul>span:first-child{font-size:16px!important;font-weight:800!important;letter-spacing:.015em!important}
        .content .kartu .kartu-judul .pill{font-size:12px!important}
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
        .vendor-stock-info{display:grid;gap:8px;padding:10px 12px;border:1px solid #c8ddef;border-radius:8px;background:#f3f8ff;color:#536477;font-size:12px}
        .vendor-stock-heading{display:flex;align-items:center;justify-content:space-between;gap:10px}.vendor-stock-heading>span:first-child{font-weight:700;color:#123b5d}.vendor-stock-availability{font-size:11px;font-weight:700}.vendor-stock-availability.is-ready{color:#16834b}.vendor-stock-availability.is-empty{color:#c23f3a}
        .vendor-stock-values{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.vendor-stock-values-three{grid-template-columns:repeat(3,minmax(0,1fr))}.vendor-stock-values span{display:flex;align-items:baseline;gap:4px;padding:7px 9px;border-radius:6px;background:#fff;border:1px solid #d9e6f2}.vendor-stock-values b{color:#1e6fa8}.vendor-stock-values strong{margin-left:auto;color:#123b5d;font-size:15px}.vendor-stock-values small{color:#718096}
        .vendor-stock-warning{padding:10px 12px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px;line-height:1.45}.vendor-stock-warning[hidden],.vendor-stock-info[hidden]{display:none!important}
        .vendor-kelayakan { display:flex; gap:12px; align-items:center; flex-wrap:wrap; color:#536477; }
        .vendor-kelayakan label { display:inline-flex; align-items:center; gap:4px; }
        .vendor-delete-card{margin-top:12px;padding:14px;border:1px solid #e2caca;border-radius:9px;background:#fff8f7}
        .vendor-delete-card[hidden]{display:none!important}
        .vendor-delete-card h4{margin:0 0 5px;color:#8d3030}
        .vendor-delete-card p{margin:0;color:#6b5353;font-size:12px;line-height:1.45}
        .vendor-delete-form{margin-top:10px}
        .vendor-delete-form button{width:100%;background:#c23f3a!important;color:#fff!important}
        .vendor-delete-form button:hover{background:#a9322e!important}
        .vendor-locked-notice{padding:10px 12px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px;line-height:1.45}
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
        .detail-modal-box .result-file-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0;padding:7px 0}
        .detail-modal-box .result-file-row+.result-file-row{border-top:1px solid #edf1f6}
        .detail-modal-box .result-file-info{display:flex;align-items:center;justify-content:space-between;gap:12px;min-width:0;flex:1}
        .detail-modal-box .result-file-info a{min-width:0;overflow-wrap:anywhere}
        .detail-modal-box .result-file-date{flex:0 0 auto;color:#718096;font-size:11px;white-space:nowrap}
        .detail-modal-box .planning-result-box form{display:flex!important;align-items:center!important;gap:9px!important;flex-wrap:wrap!important;margin:0!important}
        .detail-modal-box .planning-result-box form[hidden]{display:none!important}
        .detail-modal-box .planning-result-box form input[type=file]{flex:1 1 220px;min-height:36px;padding:6px 8px!important;border:1px solid #b8c9da!important;border-radius:8px!important;background:#fff!important;color:#627d98!important;font-size:12px!important}
        .detail-modal-box .planning-result-box form button{min-height:36px!important;padding:8px 12px!important;background:#111c91!important;color:#fff!important;border:0!important;border-radius:8px!important;font-size:12px!important;white-space:nowrap}
        .detail-modal-box .planning-result-box form button:hover{background:#0d166f!important}
        .detail-modal-box .result-waiting-message{padding:9px 10px;border:1px solid #f1d28a;border-radius:8px;background:#fffaf0;color:#8a6417;font-size:12px}
        @media(max-width:600px){.detail-modal-box .planning-result-box form{align-items:stretch!important;flex-direction:column!important}.detail-modal-box .planning-result-box form input[type=file],.detail-modal-box .planning-result-box form button{width:100%!important;flex-basis:auto}.detail-modal-box .result-file-row{align-items:flex-start}.detail-modal-box .result-file-info{align-items:flex-start;flex-direction:column;gap:3px}.detail-modal-box .result-file-date{white-space:normal}}
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
        /* Detail perluasan follows the same light, scroll-safe modal system as PB/PD. */
        #detailModalPerluasan{display:grid!important;place-items:center!important;overflow:hidden!important;padding:20px!important;background:rgba(7,20,69,.6)!important;backdrop-filter:blur(3px)}
        #detailModalPerluasan[hidden]{display:none!important}
        #detailModalPerluasan .detail-modal-box{display:flex!important;flex-direction:column!important;width:min(680px,100%)!important;max-height:calc(100vh - 40px)!important;min-height:0!important;padding:0!important;border:1px solid #dfe6fb!important;border-radius:24px!important;overflow:hidden!important;background:#fff!important;color:#202d5a!important;box-shadow:0 24px 64px rgba(15,35,104,.25)!important}
        #detailModalPerluasan .detail-modal-header{position:relative!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:20px!important;padding:22px 24px 18px!important;border-bottom:1px solid #e0e7f7!important;background:#fff!important}
        #detailModalPerluasan .detail-modal-header h3{margin:0!important;padding:0 54px 0 0!important;border-radius:0!important;background:transparent!important;color:#1236a5!important;font-size:22px!important;font-weight:800!important;line-height:1.25!important}
        #detailModalPerluasan .detail-modal-summary{margin:5px 0 0!important;color:#60719b!important;font-size:14px!important;font-weight:500!important;line-height:1.45!important;overflow-wrap:anywhere}
        #detailModalPerluasan .detail-modal-close{position:absolute!important;top:18px!important;right:20px!important;z-index:2!important;display:grid!important;place-items:center!important;width:40px!important;height:40px!important;min-width:40px!important;min-height:40px!important;padding:0!important;border:0!important;border-radius:13px!important;background:#eef2ff!important;color:#394b7e!important;font-size:25px!important;font-family:Arial,sans-serif!important;font-weight:400!important;line-height:1!important;box-shadow:none!important;cursor:pointer!important}
        #detailModalPerluasan .detail-modal-close:hover{background:#dfe7ff!important;color:#173daf!important}
        #detailModalPerluasan .detail-modal-body{display:grid!important;gap:20px!important;min-height:0!important;overflow-y:auto!important;overscroll-behavior:contain!important;-webkit-overflow-scrolling:touch!important;padding:20px 24px!important}
        #detailModalPerluasan .detail-section{min-width:0!important}
        #detailModalPerluasan .detail-section-title{margin:0 0 10px!important;padding:0!important;border:0!important;background:transparent!important;color:#60719b!important;font-size:12px!important;font-weight:800!important;letter-spacing:.12em!important;line-height:1.4!important;text-transform:uppercase!important}
        #detailModalPerluasan .detail-customer-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;padding:0!important}
        #detailModalPerluasan .detail-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#fff!important}
        #detailModalPerluasan .detail-grid small,#detailModalPerluasan .detail-cost-grid small{display:block!important;margin:0 0 5px!important;color:#60719b!important;font-size:11px!important;font-weight:700!important;letter-spacing:.045em!important;line-height:1.35!important;text-transform:uppercase!important}
        #detailModalPerluasan .detail-grid b,#detailModalPerluasan .detail-cost-grid b{display:block!important;color:#202d5a!important;font-size:14px!important;font-weight:700!important;line-height:1.45!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .detail-grid .detail-full{grid-column:1/-1!important}
        #detailModalPerluasan .detail-grid .detail-value-pill{display:inline-flex!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#e8efff!important;color:#2147bd!important;font-size:12px!important;font-weight:700!important}
        #detailModalPerluasan .detail-cost-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:10px!important}
        #detailModalPerluasan .detail-cost-grid>div{min-width:0!important;padding:13px 14px!important;border:1px solid #dfe6fb!important;border-radius:15px!important;background:#f2f5ff!important}
        #detailModalPerluasan .detail-cost-grid b{color:#1236a5!important;font-size:17px!important}
        #detailModalPerluasan .detail-cost-grid .detail-rab-status{display:inline-flex!important;width:max-content!important;max-width:100%!important;align-items:center!important;padding:5px 10px!important;border-radius:999px!important;background:#fff1c9!important;color:#916000!important;font-size:12px!important;white-space:normal!important}
        #detailModalPerluasan .detail-cost-grid .detail-rab-status.is-filled{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .proses-list{overflow:hidden!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#fff!important}
        #detailModalPerluasan .proses-row{display:grid!important;grid-template-columns:28px minmax(0,1fr) auto 104px!important;align-items:center!important;gap:12px!important;min-height:52px!important;padding:9px 14px!important;border-bottom:1px solid #e7ecf8!important;color:#26365e!important}
        #detailModalPerluasan .proses-row:last-child{border-bottom:0!important}
        #detailModalPerluasan .proses-icon{display:grid!important;place-items:center!important;width:24px!important;height:24px!important;border-radius:50%!important;background:#ffe5e5!important;color:#c12d26!important;font-size:16px!important;font-weight:800!important}
        #detailModalPerluasan .proses-row.is-complete .proses-icon{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .proses-label{min-width:0!important;font-size:13px!important;font-weight:600!important;line-height:1.4!important}
        #detailModalPerluasan .proses-status{display:inline-flex!important;justify-content:center!important;padding:5px 10px!important;border-radius:999px!important;font-size:11px!important;font-weight:700!important;line-height:1.35!important;white-space:nowrap!important}
        #detailModalPerluasan .proses-selesai{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .proses-belum{background:#ffe5e5!important;color:#bd2f27!important}
        #detailModalPerluasan .proses-row time{color:#60719b!important;font-size:12px!important;text-align:right!important;white-space:nowrap!important}
        #detailModalPerluasan .detail-technical-section{display:grid!important;gap:10px!important}
        #detailModalPerluasan #detailFormPerluasan{display:grid!important;gap:12px!important;margin:0!important}
        #detailModalPerluasan .detail-edit-table{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:12px 10px!important;margin:0!important}
        #detailModalPerluasan .detail-edit-table label{display:flex!important;flex-direction:column!important;gap:6px!important;min-width:0!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important;line-height:1.35!important}
        #detailModalPerluasan .detail-edit-table .detail-field-rab{grid-column:1/-1!important}
        #detailModalPerluasan .detail-edit-table input,#detailModalPerluasan .detail-edit-table select{width:100%!important;min-width:0!important;min-height:44px!important;margin:0!important;padding:10px 13px!important;border:1px solid #d5def7!important;border-radius:13px!important;background:#f7f9ff!important;color:#26365e!important;font-size:14px!important;box-shadow:none!important}
        #detailModalPerluasan .detail-edit-table input:focus,#detailModalPerluasan .detail-edit-table select:focus{outline:0!important;border-color:#2451dc!important;box-shadow:0 0 0 3px rgba(36,81,220,.12)!important}
        #detailModalPerluasan #detailFormPerluasan>button[type="submit"]{justify-self:end!important;min-height:46px!important;padding:0 20px!important;border:0!important;border-radius:12px!important;background:#1941bd!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 8px 18px rgba(25,65,189,.2)!important}
        #detailModalPerluasan .detail-rab-form{display:grid!important;gap:12px!important;margin:0!important;padding:16px!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#f7f9ff!important}
        #detailModalPerluasan .detail-rab-form label{display:flex!important;flex-direction:column!important;gap:6px!important;margin:0!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important}
        #detailModalPerluasan .detail-rab-form input{width:100%!important;min-height:44px!important;padding:10px 13px!important;border:1px solid #d5def7!important;border-radius:13px!important;background:#f7f9ff!important;color:#26365e!important;font-size:14px!important}
        #detailModalPerluasan .detail-rab-form .btn{justify-self:end!important;min-height:46px!important;padding:0 20px!important;border:0!important;border-radius:12px!important;background:#1941bd!important;color:#fff!important;font-size:14px!important;font-weight:800!important;box-shadow:0 8px 18px rgba(25,65,189,.2)!important}
        #detailModalPerluasan .detail-rab-form .btn:hover,#detailModalPerluasan #detailFormPerluasan>button[type="submit"]:hover{background:#12349e!important}
        #detailModalPerluasan .detail-file-list{display:grid!important;gap:10px!important}
        #detailModalPerluasan .planning-result-box{display:grid!important;gap:10px!important;margin:0!important;padding:14px!important;border:1px solid #dfe6fb!important;border-radius:16px!important;background:#f7f9ff!important;box-shadow:none!important}
        #detailModalPerluasan .planning-result-box h4{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;margin:0!important;padding:0 0 9px!important;border-bottom:1px solid #e1e8fa!important;color:#1236a5!important;font-size:14px!important;line-height:1.4!important}
        #detailModalPerluasan .file-status{flex:0 0 auto!important;padding:5px 10px!important;border-radius:999px!important;background:#ffe5e5!important;color:#bd2f27!important;font-size:11px!important;font-weight:700!important;white-space:nowrap!important}
        #detailModalPerluasan .file-status.is-complete{background:#dcf5e8!important;color:#176d43!important}
        #detailModalPerluasan .upload-owner-label{color:#60719b!important;font-size:11px!important}
        #detailModalPerluasan .planning-result-box>div[id$="Files"]{display:block!important;min-height:42px!important;padding:10px 12px!important;border:1px dashed #ccd8f7!important;border-radius:12px!important;background:#fff!important;color:#60719b!important;font-size:13px!important;line-height:1.45!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .result-empty{display:block!important;padding:1px 0!important;color:#60719b!important}
        #detailModalPerluasan .planning-result-box>div[id$="Files"] a{color:#2451dc!important;font-weight:700!important;text-decoration:underline!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .result-file-row{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;margin:0!important;padding:4px 0!important}
        #detailModalPerluasan .result-file-row+.result-file-row{border-top:1px solid #edf1f8!important;padding-top:10px!important;margin-top:7px!important}
        #detailModalPerluasan .result-file-info{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important;min-width:0!important;flex:1!important}
        #detailModalPerluasan .result-file-info a{display:inline-flex!important;align-items:center!important;gap:9px!important;min-width:0!important;overflow-wrap:anywhere!important}
        #detailModalPerluasan .result-file-icon{display:grid!important;place-items:center!important;flex:0 0 30px!important;width:30px!important;height:30px!important;border-radius:9px!important;background:#e8efff!important;color:#2451dc!important}
        #detailModalPerluasan .result-file-date{flex:0 0 auto!important;color:#60719b!important;font-size:11px!important;white-space:nowrap!important}
        #detailModalPerluasan .result-delete{flex:0 0 auto!important;padding:5px 8px!important;border:0!important;border-radius:7px!important;background:#c23f3a!important;color:#fff!important;cursor:pointer!important;font-size:11px!important}
        #detailModalPerluasan .result-waiting-message{padding:9px 10px!important;border:1px solid #f1d28a!important;border-radius:9px!important;background:#fffaf0!important;color:#8a6417!important;font-size:12px!important}
        #detailModalPerluasan .planning-result-box form{display:flex!important;align-items:center!important;gap:9px!important;flex-wrap:wrap!important;margin:0!important}
        #detailModalPerluasan .planning-result-box form[hidden]{display:none!important}
        #detailModalPerluasan .planning-result-box form input[type=file]{flex:1 1 220px!important;min-width:0!important;min-height:38px!important;padding:6px 8px!important;border:1px solid #d5def7!important;border-radius:10px!important;background:#fff!important;color:#60719b!important;font-size:11px!important}
        #detailModalPerluasan .planning-result-box form input[type=file]::file-selector-button{margin-right:8px!important;padding:6px 9px!important;border:0!important;border-radius:7px!important;background:#eef2ff!important;color:#1236a5!important;font-weight:700!important;cursor:pointer!important}
        #detailModalPerluasan .planning-result-box form button{min-height:38px!important;padding:0 12px!important;border:1px solid #c8d5ff!important;border-radius:10px!important;background:#fff!important;color:#1236a5!important;font-size:12px!important;font-weight:700!important;white-space:nowrap!important;box-shadow:none!important}
        #detailModalPerluasan .planning-result-box form button:hover{background:#edf2ff!important}
        #detailModalPerluasan .detail-modal-footer{display:flex!important;justify-content:flex-end!important;padding:14px 24px!important;border-top:1px solid #e0e7f7!important;background:#fff!important}
        #detailModalPerluasan .detail-modal-dismiss{min-height:42px!important;padding:0 18px!important;border:1px solid #c8d5ff!important;border-radius:12px!important;background:#fff!important;color:#1236a5!important;font-size:14px!important;font-weight:700!important;box-shadow:none!important;cursor:pointer!important}
        #detailModalPerluasan .detail-modal-dismiss:hover{background:#f2f5ff!important}
        #detailModalPerluasan .vendor-form,#detailModalPerluasan .vendor-delete-card,#detailModalPerluasan .vendor-report-result,#detailModalPerluasan .construction-report-box{border-color:#dfe6fb!important;border-radius:15px!important;background:#f7f9ff!important}
        #detailModalPerluasan .vendor-form{display:grid!important;gap:10px!important;margin:0!important;padding:15px!important}
        #detailModalPerluasan .vendor-form label{display:grid!important;gap:6px!important;color:#4f608b!important;font-size:12px!important;font-weight:700!important}
        #detailModalPerluasan .vendor-form select,#detailModalPerluasan .vendor-form input[type=file]{min-height:42px!important;padding:9px 11px!important;border:1px solid #d5def7!important;border-radius:11px!important;background:#fff!important;color:#26365e!important}
        #detailModalPerluasan .vendor-delete-card{margin:0!important;padding:14px!important}
        #detailModalPerluasan .vendor-report-result{gap:9px!important;margin:0!important;padding:15px!important}
        #detailModalPerluasan .construction-report-box{display:grid!important;gap:10px!important;margin:0!important;padding:15px!important}
        #detailModalPerluasan .construction-report-box h4,#detailModalPerluasan .vendor-report-result h4,#detailModalPerluasan .vendor-form h4{margin:0!important;color:#1236a5!important}
        @media(max-width:700px){#detailModalPerluasan{padding:12px!important}#detailModalPerluasan .detail-modal-box{max-height:calc(100vh - 24px)!important;border-radius:20px!important}#detailModalPerluasan .detail-modal-header{padding:18px 18px 15px!important}#detailModalPerluasan .detail-modal-close{top:14px!important;right:14px!important}#detailModalPerluasan .detail-modal-body{gap:17px!important;padding:17px 18px!important}#detailModalPerluasan .detail-cost-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#detailModalPerluasan .proses-row{grid-template-columns:26px minmax(0,1fr) auto!important;gap:8px!important;padding:9px 10px!important}#detailModalPerluasan .proses-row time{grid-column:2!important;text-align:left!important}#detailModalPerluasan .detail-modal-footer{padding:12px 18px!important}}
        @media(max-width:480px){#detailModalPerluasan .detail-modal-header h3{font-size:19px!important}#detailModalPerluasan .detail-modal-summary{max-width:calc(100% - 34px)!important;font-size:12px!important}#detailModalPerluasan .detail-customer-grid,#detailModalPerluasan .detail-edit-table{grid-template-columns:1fr!important}#detailModalPerluasan .detail-grid .detail-full,#detailModalPerluasan .detail-edit-table .detail-field-rab{grid-column:auto!important}#detailModalPerluasan .proses-row{grid-template-columns:24px minmax(0,1fr)!important}#detailModalPerluasan .proses-status,#detailModalPerluasan .proses-row time{grid-column:2!important;justify-self:start!important}#detailModalPerluasan .planning-result-box h4{align-items:flex-start!important;flex-direction:column!important;gap:6px!important}#detailModalPerluasan .result-file-row,#detailModalPerluasan .result-file-info{align-items:flex-start!important;flex-direction:column!important;gap:6px!important}#detailModalPerluasan .result-file-date{white-space:normal!important}#detailModalPerluasan .planning-result-box form{align-items:stretch!important;flex-direction:column!important}#detailModalPerluasan .planning-result-box form input[type=file],#detailModalPerluasan .planning-result-box form button{width:100%!important;flex-basis:auto!important}}
    </style>
    @endpush

    <script>
        (() => {
            const modal = document.getElementById('detailModalPerluasan');
            const form = document.getElementById('detailFormPerluasan');
            const tanpaRabForm = document.getElementById('tanpaRabForm');
            const vendorForm = document.getElementById('vendorFormPerluasan');
            const vendorDeleteForm = document.getElementById('vendorDeleteForm');
            const constructionForm = document.getElementById('constructionForm');
            const constructionDeleteForm = document.getElementById('constructionDeleteForm');
            const constructionResultUploadForm = document.getElementById('constructionResultUploadForm');
            const npsWoUploadForm = document.getElementById('npsWoUploadForm');
            const transaksiResultUploadForm = document.getElementById('transaksiResultUploadForm');
            const jaringanResultUploadForm = document.getElementById('jaringanResultUploadForm');
            const constructionReportBox = document.getElementById('constructionReportBox');
            const resultUploadForm = document.getElementById('resultUploadForm');
            const planningEligibilityForm = document.getElementById('planningEligibilityForm');
            const vendorSelect = document.getElementById('vendorIdPerluasan');
            const vendorStockInfo = document.getElementById('vendorStockInfoPerluasan');
            const vendorStockWarning = document.getElementById('vendorStockWarningPerluasan');
            const vendorStockTr = document.getElementById('vendorStockTrPerluasan');
            const vendorStockTm11 = document.getElementById('vendorStockTm11Perluasan');
            const vendorStockTm13 = document.getElementById('vendorStockTm13Perluasan');
            const vendorStockAvailability = document.getElementById('vendorStockAvailabilityPerluasan');
            const vendorSubmitButton = document.getElementById('vendorSubmitButton');
            const setText = (id, value) => { const element = document.getElementById(id); if (element) element.textContent = value || '-'; };
            const money = (value) => value !== null && value !== undefined && String(value).trim() !== '' && Number.isFinite(Number(value))
                ? `Rp ${Number(value).toLocaleString('id-ID')}`
                : '-';
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
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
            }[character]));
            const fileRow = (url, fileName, uploadDate, deleteForm = '') => {
                const safeName = escapeHtml(fileName);
                const safeDate = uploadDate ? `Diunggah ${escapeHtml(date(uploadDate))}` : 'Tanggal tidak tersedia';
                return `<div class="result-file-row"><div class="result-file-info"><a href="${url}" target="_blank" rel="noopener"><span class="result-file-icon" aria-hidden="true">▤</span>${safeName}</a><span class="result-file-date">${safeDate}</span></div>${deleteForm}</div>`;
            };
            const setProcess = (name, done, value) => {
                const status = document.getElementById(`proses${name}Status`);
                const tanggal = document.getElementById(`proses${name}Date`);
                if (!status || !tanggal) return;
                const complete = done === '1';
                const row = status.closest('.proses-row');
                const icon = row?.querySelector('.proses-icon');
                status.textContent = complete ? 'Sudah lengkap' : 'Belum diunggah';
                status.className = `proses-status ${complete ? 'proses-selesai' : 'proses-belum'}`;
                row?.classList.toggle('is-complete', complete);
                if (icon) icon.textContent = complete ? '✓' : '×';
                tanggal.textContent = done === '1' ? date(value) : '-';
            };
            const updateFileStatuses = () => {
                document.querySelectorAll('#detailModalPerluasan [data-file-status-for]').forEach((badge) => {
                    const fileBox = document.getElementById(badge.dataset.fileStatusFor);
                    const complete = Boolean(fileBox?.querySelector('a[href]'));
                    badge.textContent = complete ? 'Sudah lengkap' : 'Belum diunggah';
                    badge.classList.toggle('is-complete', complete);
                });
            };

            const updateVendorStock = () => {
                if (!vendorSelect || !vendorStockInfo || !vendorStockWarning || !vendorSubmitButton) return true;

                const option = vendorSelect.selectedOptions[0];
                const stockTr = Number(option?.dataset.stockTr || 0);
                const stockTm11 = Number(option?.dataset.stockTm11 || 0);
                const stockTm13 = Number(option?.dataset.stockTm13 || 0);
                const requiredTr = Number(vendorForm?.dataset.requiredTr || 0);
                const requiredTm = Number(vendorForm?.dataset.requiredTm || 0);
                const requiredTmType = vendorForm?.dataset.requiredTmType === '13' ? '13' : '11';
                const availableTm = requiredTmType === '13' ? stockTm13 : stockTm11;
                const availableTmLabel = `TM ${requiredTmType}`;
                const hasRequirement = requiredTr > 0 || requiredTm > 0;
                const stockEmpty = stockTr === 0 && stockTm11 === 0 && stockTm13 === 0;
                const stockEnough = !stockEmpty && (!hasRequirement || (stockTr >= requiredTr && availableTm >= requiredTm));

                vendorStockInfo.hidden = !option?.value;
                vendorStockTr.textContent = stockTr.toLocaleString('id-ID');
                vendorStockTm11.textContent = stockTm11.toLocaleString('id-ID');
                vendorStockTm13.textContent = stockTm13.toLocaleString('id-ID');
                vendorStockAvailability.textContent = stockEnough ? 'Stok tersedia' : 'Stok tidak mencukupi';
                vendorStockAvailability.className = `vendor-stock-availability ${stockEnough ? 'is-ready' : 'is-empty'}`;
                vendorStockWarning.hidden = !option?.value || stockEnough;
                if (option?.value && stockEmpty) {
                    vendorStockWarning.textContent = 'Vendor ini belum memiliki stok tiang. Isi stok TR/TM terlebih dahulu sebelum mengirim.';
                } else if (option?.value && !stockEnough) {
                    const kurang = [];
                    if (requiredTr > stockTr) kurang.push(`TR membutuhkan ${requiredTr}, tersedia ${stockTr}`);
                    if (requiredTm > availableTm) kurang.push(`${availableTmLabel} membutuhkan ${requiredTm}, tersedia ${availableTm}`);
                    vendorStockWarning.textContent = `Stok tidak cukup untuk kebutuhan ini: ${kurang.join('; ')}.`;
                } else {
                    vendorStockWarning.textContent = '';
                }
                vendorSubmitButton.disabled = Boolean(vendorForm?.dataset.reportReceived === '1') || !stockEnough;

                return stockEnough;
            };

            vendorSelect?.addEventListener('change', updateVendorStock);
            vendorForm?.addEventListener('submit', (event) => {
                if (!updateVendorStock()) {
                    event.preventDefault();
                    vendorStockWarning?.removeAttribute('hidden');
                }
            });

            document.querySelectorAll('.tombol-detail-perluasan').forEach((button) => {
                button.addEventListener('click', () => {
                    setText('perluasanNoAgenda', button.dataset.noAgenda);
                    setText('perluasanIdpel', button.dataset.idpel);
                    setText('perluasanNama', button.dataset.nama);
                    document.getElementById('perluasanSummary').textContent = `Agenda ${button.dataset.noAgenda || '-'} · ${button.dataset.nama || '-'}`;
                    setText('perluasanAlamat', button.dataset.alamat);
                    setText('perluasanTransaksi', button.dataset.transaksi);
                    setText('perluasanStatus', button.dataset.status);
                    setText('perluasanBp', money(button.dataset.bp));
                    setText('perluasanTotalBiaya', money(button.dataset.totalBiaya));
                    setText('perluasanTglMohon', date(button.dataset.tglMohon));
                    setText('perluasanTglBayar', date(button.dataset.tglBayar));
                    const rabDisplay = formatRupiah(button.dataset.rab || '');
                    const rabStatus = document.getElementById('perluasanRabValue');
                    rabStatus.textContent = rabDisplay ? `Rp ${rabDisplay}` : 'Belum diisi';
                    rabStatus.classList.toggle('is-filled', Boolean(rabDisplay));
                    setProcess('Ulp', button.dataset.prosesUlp, button.dataset.prosesUlpDate);
                    setProcess('Perencanaan', button.dataset.prosesPerencanaan, button.dataset.prosesPerencanaanDate);
                    setProcess('NpsWo', button.dataset.prosesNpsWo, button.dataset.prosesNpsWoDate);
                    setProcess('Konstruksi', button.dataset.prosesKonstruksi, button.dataset.prosesKonstruksiDate);
                    setProcess('Transaksi', button.dataset.prosesTransaksi, button.dataset.prosesTransaksiDate);
                    setProcess('Jaringan', button.dataset.prosesJaringan, button.dataset.prosesJaringanDate);
                    const qualificationStatus = button.dataset.kelayakan || '';
                    setText('planningEligibilityStatus', qualificationStatus || 'Belum ditentukan');
                    if (planningEligibilityForm) {
                        planningEligibilityForm.hidden = !['PERENCANAAN', 'VENDOR_TIANG'].includes(button.dataset.tahap);
                        planningEligibilityForm.action = button.dataset.kelayakanUrl;
                    }
                    if (resultUploadForm) resultUploadForm.hidden = qualificationStatus !== 'LAYAK';
                    modal.hidden = false;
                    if (resultUploadForm) document.getElementById('resultPelangganId').value = button.dataset.id;
                    if (form) {
                        rabInputPerluasan.value = formatRupiah(button.dataset.rab || '');
                        rabValuePerluasan.value = button.dataset.rab || '';
                        form.action = button.dataset.detailUrl;
                        document.getElementById('jenisTiangTmPerluasan').value = button.dataset.jenisTiangTm || button.dataset.jenisTiang || '';
                        document.getElementById('jmlTiangTmPerluasan').value = button.dataset.jmlTiangTm || button.dataset.jmlTiang || '';
                        document.getElementById('jenisTiangTrPerluasan').value = button.dataset.jenisTiangTr || '';
                        document.getElementById('jmlTiangTrPerluasan').value = button.dataset.jmlTiangTr || '';
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
                        const stageAllowsTiang = ['PERENCANAAN', 'VENDOR_TIANG'].includes(button.dataset.tahap);
                        const hasVendorDelivery = Boolean(button.dataset.vendorId);
                        const vendorReportReceived = hasVendorDelivery && Boolean(button.dataset.laporanDate);
                        vendorForm.hidden = !stageAllowsTiang || qualificationStatus !== 'LAYAK';
                        vendorForm.action = button.dataset.vendorUrl;
                        vendorForm.dataset.requiredTr = button.dataset.jmlTiangTr || '0';
                        vendorForm.dataset.requiredTm = button.dataset.jmlTiangTm || button.dataset.jmlTiang || '0';
                        vendorForm.dataset.requiredTmType = button.dataset.jenisTiangTm || '11';
                        vendorForm.dataset.reportReceived = vendorReportReceived ? '1' : '0';
                        document.getElementById('vendorIdPerluasan').value = button.dataset.vendorId || '';
                        document.getElementById('vendorSubmitButton').textContent = vendorReportReceived ? 'Laporan sudah diterima' : (hasVendorDelivery ? 'Simpan Perubahan Pengiriman' : 'Kirim ke Vendor');
                        document.getElementById('vendorLockedNotice').hidden = !vendorReportReceived;
                        vendorForm.querySelectorAll('select,input:not([type="hidden"]),button[type="submit"]').forEach((control) => { control.disabled = vendorReportReceived; });
                        updateVendorStock();
                        if (vendorDeleteForm) {
                            document.getElementById('vendorDeleteCard').hidden = !hasVendorDelivery || vendorReportReceived;
                            vendorDeleteForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/kirim-vendor`;
                        }
                    }
                    if (constructionForm) {
                        const stageAllowsConstruction = ['PERENCANAAN', 'VENDOR_KONSTRUKSI'].includes(button.dataset.tahap);
                        const hasConstructionDelivery = button.dataset.konstruksiHasDelivery === '1';
                        const constructionReportReceived = Boolean(button.dataset.konstruksiLaporanDate);
                        constructionForm.hidden = !stageAllowsConstruction;
                        constructionForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/kirim-konstruksi`;
                        document.getElementById('constructionVendorId').value = button.dataset.konstruksiVendorId || '';
                        document.getElementById('constructionForm').querySelector('input[name="berkas[]"]').required = !hasConstructionDelivery;
                        document.getElementById('constructionSubmitButton').textContent = constructionReportReceived ? 'Laporan sudah diterima' : (hasConstructionDelivery ? 'Simpan Perubahan Pengiriman' : 'Kirim ke Vendor Konstruksi');
                        document.getElementById('constructionLockedNotice').hidden = !constructionReportReceived;
                        constructionForm.querySelectorAll('select,input:not([type="hidden"]),button[type="submit"]').forEach((control) => { control.disabled = constructionReportReceived; });
                        if (constructionDeleteForm) {
                            document.getElementById('constructionDeleteCard').hidden = !hasConstructionDelivery || constructionReportReceived;
                            constructionDeleteForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/kirim-konstruksi`;
                        }
                    }
                    let constructionResultFiles = [];
                    try { constructionResultFiles = JSON.parse(button.dataset.konstruksiHasilKonstruksiFiles ? atob(button.dataset.konstruksiHasilKonstruksiFiles) : '[]'); } catch (error) { constructionResultFiles = []; }
                    const constructionResultFilesBox = document.getElementById('constructionResultFiles');
                    if (constructionResultFilesBox) constructionResultFilesBox.innerHTML = constructionResultFiles.length
                        ? constructionResultFiles.map((path, index) => fileRow(button.dataset.konstruksiHasilFileUrl.replace(/\/0$/, '/' + index), String(path).split('/').pop() || 'Berkas hasil konstruksi ' + (index + 1), button.dataset.prosesKonstruksiDate, constructionResultUploadForm ? `<form method="POST" action="{{ url('/vendor/konstruksi') }}/${button.dataset.id}/hasil-konstruksi/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : '')).join('')
                        : '<span class="result-empty">Belum ada berkas</span>';
                    if (constructionResultUploadForm) {
                        constructionResultUploadForm.action = `{{ url('/vendor/konstruksi') }}/${button.dataset.id}/hasil-konstruksi`;
                    }
                    let npsWoFiles = [];
                    try { npsWoFiles = JSON.parse(button.dataset.npsWoFiles ? atob(button.dataset.npsWoFiles) : '[]'); } catch (error) { npsWoFiles = []; }
                    const npsWoFilesBox = document.getElementById('npsWoFiles');
                    if (npsWoFilesBox) npsWoFilesBox.innerHTML = npsWoFiles.length
                        ? npsWoFiles.map((path, index) => fileRow(button.dataset.npsWoFileUrl.replace(/\/0$/, '/' + index), String(path).split('/').pop() || 'Berkas WO NPS ' + (index + 1), button.dataset.prosesNpsWoDate, npsWoUploadForm ? `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/nps-wo/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas WO NPS ini?')">Hapus</button></form>` : '')).join('')
                        : '<span class="result-empty">Belum ada berkas</span>';
                    if (npsWoUploadForm) {
                        npsWoUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/nps-wo`;
                    }
                    let transaksiResultFiles = [];
                    try { transaksiResultFiles = JSON.parse(button.dataset.transaksiHasilFiles ? atob(button.dataset.transaksiHasilFiles) : '[]'); } catch (error) { transaksiResultFiles = []; }
                    const transaksiResultFilesBox = document.getElementById('transaksiResultFiles');
                    if (transaksiResultFilesBox) transaksiResultFilesBox.innerHTML = transaksiResultFiles.length
                        ? transaksiResultFiles.map((path, index) => fileRow(button.dataset.transaksiHasilFileUrl.replace(/\/0$/, '/' + index), String(path).split('/').pop() || 'Berkas hasil transaksi ' + (index + 1), button.dataset.prosesTransaksiDate, transaksiResultUploadForm ? `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/hasil-transaksi/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : '')).join('')
                        : '<span class="result-empty">Belum ada berkas</span>';
                    if (transaksiResultUploadForm) {
                        transaksiResultUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/hasil-transaksi`;
                    }
                    let jaringanResultFiles = [];
                    try { jaringanResultFiles = JSON.parse(button.dataset.jaringanHasilFiles ? atob(button.dataset.jaringanHasilFiles) : '[]'); } catch (error) { jaringanResultFiles = []; }
                    const jaringanResultFilesBox = document.getElementById('jaringanResultFiles');
                    if (jaringanResultFilesBox) jaringanResultFilesBox.innerHTML = jaringanResultFiles.length
                        ? jaringanResultFiles.map((path, index) => fileRow(button.dataset.jaringanHasilFileUrl.replace(/\/0$/, '/' + index), String(path).split('/').pop() || 'Berkas hasil jaringan ' + (index + 1), button.dataset.prosesJaringanDate, jaringanResultUploadForm ? `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/hasil-jaringan/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : '')).join('')
                        : '<span class="result-empty">Belum ada berkas</span>';
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
                    }
                    let constructionVendorReportFiles = [];
                    try { constructionVendorReportFiles = JSON.parse(button.dataset.konstruksiLaporanFiles ? atob(button.dataset.konstruksiLaporanFiles) : '[]'); } catch (error) { constructionVendorReportFiles = []; }
                    let constructionVendorReportNames = [];
                    try { constructionVendorReportNames = JSON.parse(button.dataset.konstruksiLaporanNames ? atob(button.dataset.konstruksiLaporanNames) : '[]'); } catch (error) { constructionVendorReportNames = []; }
                    const constructionVendorReportFilesBox = document.getElementById('constructionVendorReportFiles');
                    if (constructionVendorReportFilesBox) constructionVendorReportFilesBox.innerHTML = constructionVendorReportFiles.length
                        ? constructionVendorReportFiles.map((path, index) => fileRow(button.dataset.konstruksiLaporanFileUrl.replace(/\/0$/, '/' + index), constructionVendorReportNames[index] || String(path).split('/').pop() || `Berkas laporan konstruksi ${index + 1}`, button.dataset.konstruksiLaporanDate)).join('')
                        : '<span class="result-empty">Belum ada berkas</span>';
                    const reportResult = document.getElementById('vendorReportResult');
                    if (reportResult) {
                        setText('laporanLengkap', button.dataset.laporanLengkap);
                        setText('laporanSesuai', button.dataset.laporanSesuai);
                        setText('laporanFoto', button.dataset.laporanFoto);
                        setText('laporanSiap', button.dataset.laporanSiap);
                        setText('laporanCatatan', button.dataset.laporanCatatan);
                    }
                    let vendorReportFiles = [];
                    try { vendorReportFiles = JSON.parse(button.dataset.laporanFiles ? atob(button.dataset.laporanFiles) : '[]'); } catch (error) { vendorReportFiles = []; }
                    let vendorReportNames = [];
                    try { vendorReportNames = JSON.parse(button.dataset.laporanNames ? atob(button.dataset.laporanNames) : '[]'); } catch (error) { vendorReportNames = []; }
                    const vendorReportFilesBox = document.getElementById('vendorReportFiles');
                    if (vendorReportFilesBox) vendorReportFilesBox.innerHTML = vendorReportFiles.length
                        ? vendorReportFiles.map((path, index) => fileRow(button.dataset.reportFileUrl.replace(/\/0$/, '/' + index), vendorReportNames[index] || String(path).split('/').pop() || `Berkas laporan vendor ${index + 1}`, button.dataset.laporanDate)).join('')
                        : '<span class="result-empty">Belum ada berkas</span>';
                    const resultFilesBox = document.getElementById('resultFiles');
                    let resultFiles = [];
                    try { resultFiles = JSON.parse(button.dataset.resultFiles ? atob(button.dataset.resultFiles) : '[]'); } catch (error) { resultFiles = []; }
                    let resultNames = [];
                    try { resultNames = JSON.parse(button.dataset.resultNames ? atob(button.dataset.resultNames) : '[]'); } catch (error) { resultNames = []; }
                    if (resultFilesBox) resultFilesBox.innerHTML = resultFiles.length
                            ? resultFiles.map((path, index) => {
                                const fileName = resultNames[index] || String(path).split('/').pop() || `Berkas hasil ${index + 1}`;
                                return fileRow(button.dataset.resultFileUrl.replace(/\/0$/, '/' + index), fileName, button.dataset.prosesPerencanaanDate, resultUploadForm ? `<form method="POST" action="${button.dataset.resultDeleteUrl.replace(/\/0$/, '/' + index)}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : '');
                            }).join('')
                            : '<span class="result-empty">Belum ada berkas</span>';
                    if (resultUploadForm) {
                        resultUploadForm.hidden = qualificationStatus !== 'LAYAK';
                        resultUploadForm.action = `{{ url('/vendor/tiang') }}/${button.dataset.id}/hasil`;
                    }
                    updateFileStatuses();
                });
            });

            const closeDetailModal = () => { modal.hidden = true; };
            document.getElementById('closeDetailPerluasan').addEventListener('click', closeDetailModal);
            document.getElementById('dismissDetailPerluasan').addEventListener('click', closeDetailModal);
            modal.addEventListener('click', (event) => { if (event.target === modal) closeDetailModal(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeDetailModal(); });
        })();
    </script>
@endsection
