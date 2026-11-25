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
                        @if ($showBerkasUlp)
                            <th rowspan="2">BERKAS</th>
                        @endif
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
                            $m = $row->permintaan;
                            $pv = $row->pengirimanVendor;
                            $pk = $row->pengirimanKonstruksi;
                            $ulpSent = $row->tahap !== 'ULP' || $row->tujuan_perluasan;
                            $npsWoDone = count($row->nps_wo_paths ?? []) > 0 || filled($row->nps_wo_at);
                            $planningDone = count($row->laporanVendor?->berkas_hasil_paths ?? []) > 0
                                || count($pk?->hasil_paths ?? []) > 0;
                            $constructionDone = count($row->hasil_konstruksi_paths ?? []) > 0
                                || count($pk?->hasil_konstruksi_paths ?? []) > 0;
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
                                    data-kelayakan="{{ $row->status_kelayakan }}"
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

