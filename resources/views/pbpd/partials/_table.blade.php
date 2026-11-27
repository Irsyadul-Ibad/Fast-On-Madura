    <div class="box kartu">
        <div class="kartu-judul">
            <span class="flex flex-col gap-1">Daftar transaksi<small class="font-normal leading-5">Record dan jumlah transaksi PB/PD</small></span>
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
