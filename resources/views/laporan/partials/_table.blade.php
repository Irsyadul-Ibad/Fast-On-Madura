    <div class="box kartu">
        <div class="main-report-table-title">Daftar transaksi</div>
        @php($showSyarat = ! in_array(auth()->user()->role?->type, ['UP3', 'ULP'], true))
        @php($processMap = $data->getCollection()->mapWithKeys(function ($row) {
            $material = $row->permintaan;
            $konstruksi = $row->pengirimanKonstruksi;
            $npsWoDone = count($row->nps_wo_paths ?? []) > 0 || filled($row->nps_wo_at);
            $npsChecklistDone = count($row->nps_checklist_paths ?? []) > 0 || filled($row->nps_checklist_at);
            $planningDone = count($row->laporanVendor?->berkas_hasil_paths ?? []) > 0 || count($konstruksi?->hasil_paths ?? []) > 0;
            $constructionDone = count($row->hasil_konstruksi_paths ?? []) > 0 || count($konstruksi?->hasil_konstruksi_paths ?? []) > 0;
            return [$row->no_agenda => [
                'ulp' => $row->tahap !== 'ULP' || $row->tujuan_perluasan,
                'ulpDate' => $material?->dikirim_at?->format('Y-m-d H:i:s') ?? $row->created_at,
                'planning' => $planningDone,
                'planningDate' => $row->laporanVendor?->berkas_hasil_at?->format('Y-m-d H:i:s') ?? $konstruksi?->hasil_perencanaan_at?->format('Y-m-d H:i:s'),
                'nps' => $npsWoDone,
                'npsWo' => $npsWoDone,
                'npsWoDate' => $row->nps_wo_at?->format('Y-m-d H:i:s'),
                'npsChecklist' => $npsChecklistDone,
                'npsChecklistDate' => $row->nps_checklist_at?->format('Y-m-d H:i:s'),
                'construction' => $constructionDone,
                'constructionDate' => $row->hasil_konstruksi_at?->format('Y-m-d H:i:s') ?? $konstruksi?->hasil_konstruksi_at?->format('Y-m-d H:i:s'),
                'transaction' => count($row->hasil_transaksi_paths ?? []) > 0,
                'transactionDate' => $row->hasil_transaksi_at?->format('Y-m-d H:i:s'),
                'network' => count($row->hasil_jaringan_paths ?? []) > 0,
                'networkDate' => $row->hasil_jaringan_at?->format('Y-m-d H:i:s'),
            ]];
        })->all())
        @php($panelMap = $data->getCollection()->mapWithKeys(fn ($row) => [$row->no_agenda => ['jenis' => $row->permintaan?->jenis_panel_meter, 'jumlah' => $row->permintaan?->jml_panel_meter]])->all())
        <script id="laporanPanelData" type="application/json">@json($panelMap)</script>
        <script id="laporanProsesData" type="application/json">@json($processMap)</script>
        <div class="scroll laporan-scroll">
            <table class="tabel tabel-biru laporan-table">
                <thead>
                    <tr>
                        <th rowspan="2">NO.</th>
                        <th rowspan="2">ASAL ULP</th>
                        <th rowspan="2">DETAIL</th>
                        @if ($showSyarat)
                            <th colspan="2" class="grup">SYARAT</th>
                        @endif
                        <th colspan="15" class="grup">DETAIL MATERIAL</th>
                        <th rowspan="2">JENIS PERLUASAN</th>
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
                        <th class="sub">RAB</th>
                        <th class="sub">JENIS TIANG TM</th>
                        <th class="sub">JUMLAH TIANG TM</th>
                        <th class="sub">JENIS TIANG TR</th>
                        <th class="sub">JUMLAH TIANG TR</th>
                        <th class="sub">JENIS KONDUKTOR TM</th>
                        <th class="sub">JUMLAH KONDUKTOR TM</th>
                        <th class="sub">JENIS KONDUKTOR TR</th>
                        <th class="sub">JUMLAH KONDUKTOR TR</th>
                        <th class="sub">JENIS TRAFO</th>
                        <th class="sub">JUMLAH TRAFO</th>
                        <th class="sub">JENIS KWH METER</th>
                        <th class="sub">JUMLAH KWH METER</th>
                        <th class="sub">JENIS PANEL</th>
                        <th class="sub">JUMLAH PANEL</th>
                        <th class="sub">TARIF</th>
                        <th class="sub">DAYA</th>
                        <th class="sub">TARIF</th>
                        <th class="sub">DAYA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $row)
                        @php($material = $row->permintaan)
                        <tr>
                            <td>{{ $data->firstItem() + $loop->index }}.</td>
                            <td><b>{{ $row->asal_ulp }}</b></td>
                            <td>
                                <button
                                    type="button"
                                    class="detail-link laporan-detail"
                                    title="Lihat detail"
                                    data-no-agenda="{{ $row->no_agenda }}"
                                    data-idpel="{{ $row->id_pelanggan }}"
                                    data-nama="{{ $row->nama_pelanggan }}"
                                    data-alamat="{{ $row->alamat }}"
                                    data-transaksi="{{ $row->jenis_transaksi }}"
                                    data-status="{{ $row->status }}"
                                    data-tujuan="{{ $row->tujuan_perluasan ?: ($material?->jenis_perluasan ?? '-') }}"
                                    data-rab="{{ $row->rab }}"
                                    data-pendukung="{{ base64_encode(json_encode($row->berkas_pendukung_paths ?? [])) }}"
                                    data-pendukung-names="{{ base64_encode(json_encode($row->berkas_pendukung_names ?? [])) }}"
                                    data-ijin="{{ base64_encode(json_encode($row->berkas_ijin_paths ?? [])) }}"
                                    data-ijin-names="{{ base64_encode(json_encode($row->berkas_ijin_names ?? [])) }}"
                                    data-file-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => 0]) }}"
                                    data-jenis-tiang-tm="{{ $material?->jenis_tiang_tm ?? $material?->jenis_tiang }}"
                                    data-jml-tiang-tm="{{ $material?->jml_tiang_tm ?? $material?->jml_tiang }}"
                                    data-jenis-tiang-tr="{{ $material?->jenis_tiang_tr }}"
                                    data-jml-tiang-tr="{{ $material?->jml_tiang_tr }}"
                                    data-jenis-konduktor-tm="{{ $material?->jenis_konduktor_tm }}"
                                    data-jml-konduktor-tm="{{ $material?->jml_konduktor_tm }}"
                                    data-jenis-konduktor-tr="{{ $material?->jenis_konduktor_tr }}"
                                    data-jml-konduktor-tr="{{ $material?->jml_konduktor_tr }}"
                                    data-jenis-trafo="{{ $material?->jenis_trafo }}"
                                    data-jml-trafo="{{ $material?->jml_trafo }}"
                                    data-jenis-kwh="{{ $material?->jenis_kwh_meter }}"
                                    data-jml-kwh="{{ $material?->jml_kwh_meter }}"
                                >📋</button>
                            </td>
                            @if ($showSyarat)
                                <td>{{ count($row->berkas_pendukung_paths ?? []) ? '📎' : '-' }}</td>
                                <td>{{ count($row->berkas_ijin_paths ?? []) ? '📎' : '-' }}</td>
                            @endif
                            <td>{{ $row->rab !== null ? number_format($row->rab, 0, ',', '.') : '-' }}</td>
                            <td>{{ $material?->jenis_tiang_tm ?? $material?->jenis_tiang ?? '-' }}</td>
                            <td>{{ $material?->jml_tiang_tm ?? $material?->jml_tiang ?? '-' }}</td>
                            <td>{{ $material?->jenis_tiang_tr ?? '-' }}</td>
                            <td>{{ $material?->jml_tiang_tr ?? '-' }}</td>
                            <td>{{ $material?->jenis_konduktor_tm ?? '-' }}</td>
                            <td>{{ $material?->jml_konduktor_tm ?? '-' }}</td>
                            <td>{{ $material?->jenis_konduktor_tr ?? '-' }}</td>
                            <td>{{ $material?->jml_konduktor_tr ?? '-' }}</td>
                            <td>{{ $material?->jenis_trafo ?? '-' }}</td>
                            <td>{{ $material?->jml_trafo ?? '-' }}</td>
                            <td>{{ $material?->jenis_kwh_meter ?? '-' }}</td>
                            <td>{{ $material?->jml_kwh_meter ?? '-' }}</td>
                            <td>{{ $material?->jenis_panel_meter ?? '-' }}</td>
                            <td>{{ $material?->jml_panel_meter ?? '-' }}</td>
                            <td>{{ $row->tujuan_perluasan ?: ($material?->jenis_perluasan ?? '-') }}</td>
                            <td>{{ $row->jenis_transaksi }}</td>
                            <td>{{ $row->status }}</td>
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
                        <tr>
                            <td colspan="{{ $showSyarat ? 32 : 30 }}" style="text-align:center">Belum ada data laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $data->links('pagination.faston') }}
    </div>
