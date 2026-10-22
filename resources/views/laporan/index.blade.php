@extends('layouts.app')
@section('title', 'Laporan')
@section('judul', 'Laporan')
@section('isi')
    <div class="grid statistik">
        <div class="stat-card"><small>Total Data</small><strong>{{ number_format($total, 0, ',', '.') }}</strong></div>
        @foreach ($perJenis as $jenis => $jumlah)<div class="stat-card"><small>{{ $jenis ?: 'LAINNYA' }}</small><strong>{{ number_format($jumlah, 0, ',', '.') }}</strong></div>@endforeach
    </div>
    <div class="box">
        <form class="filter" method="GET"><input type="text" name="no_agenda" value="{{ request('no_agenda') }}" placeholder="No agenda">@if ($showUlpFilter)<select name="ulp"><option value="">Semua ULP</option>@foreach ($ulps as $ulp)<option value="{{ $ulp->id }}" @selected((string) request('ulp') === (string) $ulp->id)>{{ $ulp->nama }}</option>@endforeach</select>@endif @if (in_array(auth()->user()->role?->type, ['ULP', 'UP3'], true))<select name="tahap"><option value="">Semua tahap</option>@foreach ($tahapPilihan as $tahap => $label)<option value="{{ $tahap }}" @selected(request('tahap') === $tahap)>{{ $label }}</option>@endforeach</select>@endif<select name="jenis"><option value="">Semua transaksi</option>@foreach ($jenisPilihan as $jenis)<option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>@endforeach</select><select name="status"><option value="">Semua status</option>@foreach ($statusPilihan as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select><button type="submit" class="btn">Filter</button><a class="btn btn-excel" href="{{ route('laporan.export', request()->only(['no_agenda', 'ulp', 'tahap', 'jenis', 'status'])) }}">Export Excel</a></form>
        @php($showSyarat = ! in_array(auth()->user()->role?->type, ['UP3', 'ULP'], true))
        @php($processMap = $data->getCollection()->mapWithKeys(function ($row) {
            $material = $row->permintaan;
            $konstruksi = $row->pengirimanKonstruksi;
            $npsWoDone = count($row->nps_wo_paths ?? []) > 0 || filled($row->nps_wo_at);
            $npsChecklistDone = count($row->nps_checklist_paths ?? []) > 0 || filled($row->nps_checklist_at);
            $planningDone = count($row->laporanVendor?->berkas_hasil_paths ?? []) > 0 || count($konstruksi?->hasil_paths ?? []) > 0 || $npsWoDone;
            $constructionDone = count($row->hasil_konstruksi_paths ?? []) > 0 || count($konstruksi?->hasil_konstruksi_paths ?? []) > 0;
            return [$row->no_agenda => [
                'ulp' => $row->tahap !== 'ULP' || $row->tujuan_perluasan,
                'ulpDate' => $material?->dikirim_at?->format('Y-m-d H:i:s') ?? $row->created_at,
                'planning' => $planningDone,
                'planningDate' => $row->laporanVendor?->berkas_hasil_at?->format('Y-m-d H:i:s') ?? $konstruksi?->hasil_perencanaan_at?->format('Y-m-d H:i:s') ?? $row->nps_wo_at?->format('Y-m-d H:i:s'),
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
        <div class="scroll laporan-scroll"><table class="tabel tabel-biru laporan-table"><thead>
            <tr><th rowspan="2">NO.</th><th rowspan="2">ASAL ULP</th><th rowspan="2">DETAIL</th>@if($showSyarat)<th colspan="2" class="grup">SYARAT</th>@endif<th colspan="15" class="grup">DETAIL MATERIAL</th><th rowspan="2">JENIS PERLUASAN</th><th rowspan="2">TRANSAKSI</th><th rowspan="2">STATUS</th><th rowspan="2">NO AGENDA</th><th rowspan="2">NAMA PELANGGAN</th><th rowspan="2">IDPEL</th><th rowspan="2">TOTAL BIAYA</th><th rowspan="2">TANGGAL MOHON</th><th rowspan="2">TANGGAL BAYAR</th><th colspan="2" class="grup">LAMA</th><th colspan="2" class="grup">BARU</th><th rowspan="2">DURASI HARI KERJA</th></tr>
            <tr>@if($showSyarat)<th class="sub">BERKAS PENDUKUNG</th><th class="sub">BERKAS IJIN</th>@endif<th class="sub">RAB</th><th class="sub">JENIS TIANG TM</th><th class="sub">JUMLAH TIANG TM</th><th class="sub">JENIS TIANG TR</th><th class="sub">JUMLAH TIANG TR</th><th class="sub">JENIS KONDUKTOR TM</th><th class="sub">JUMLAH KONDUKTOR TM</th><th class="sub">JENIS KONDUKTOR TR</th><th class="sub">JUMLAH KONDUKTOR TR</th><th class="sub">JENIS TRAFO</th><th class="sub">JUMLAH TRAFO</th><th class="sub">JENIS KWH METER</th><th class="sub">JUMLAH KWH METER</th><th class="sub">JENIS PANEL</th><th class="sub">JUMLAH PANEL</th><th class="sub">TARIF</th><th class="sub">DAYA</th><th class="sub">TARIF</th><th class="sub">DAYA</th></tr>
        </thead><tbody>
            @forelse ($data as $row) @php($material = $row->permintaan) <tr><td>{{ $data->firstItem() + $loop->index }}.</td><td><b>{{ $row->asal_ulp }}</b></td><td><button type="button" class="detail-link laporan-detail" title="Lihat detail" data-no-agenda="{{ $row->no_agenda }}" data-idpel="{{ $row->id_pelanggan }}" data-nama="{{ $row->nama_pelanggan }}" data-alamat="{{ $row->alamat }}" data-transaksi="{{ $row->jenis_transaksi }}" data-status="{{ $row->status }}" data-tujuan="{{ $row->tujuan_perluasan ?: ($material?->jenis_perluasan ?? '-') }}" data-rab="{{ $row->rab }}" data-pendukung="{{ base64_encode(json_encode($row->berkas_pendukung_paths ?? [])) }}" data-ijin="{{ base64_encode(json_encode($row->berkas_ijin_paths ?? [])) }}" data-file-url="{{ route('pbpd.syarat.file', ['pelanggan' => $row->id, 'jenis' => 'pendukung', 'index' => 0]) }}" data-jenis-tiang-tm="{{ $material?->jenis_tiang_tm ?? $material?->jenis_tiang }}" data-jml-tiang-tm="{{ $material?->jml_tiang_tm ?? $material?->jml_tiang }}" data-jenis-tiang-tr="{{ $material?->jenis_tiang_tr }}" data-jml-tiang-tr="{{ $material?->jml_tiang_tr }}" data-jenis-konduktor-tm="{{ $material?->jenis_konduktor_tm }}" data-jml-konduktor-tm="{{ $material?->jml_konduktor_tm }}" data-jenis-konduktor-tr="{{ $material?->jenis_konduktor_tr }}" data-jml-konduktor-tr="{{ $material?->jml_konduktor_tr }}" data-jenis-trafo="{{ $material?->jenis_trafo }}" data-jml-trafo="{{ $material?->jml_trafo }}" data-jenis-kwh="{{ $material?->jenis_kwh_meter }}" data-jml-kwh="{{ $material?->jml_kwh_meter }}">📋</button></td>@if($showSyarat)<td>{{ count($row->berkas_pendukung_paths ?? []) ? '📎' : '-' }}</td><td>{{ count($row->berkas_ijin_paths ?? []) ? '📎' : '-' }}</td>@endif<td>{{ $row->rab !== null ? number_format($row->rab, 0, ',', '.') : '-' }}</td><td>{{ $material?->jenis_tiang_tm ?? $material?->jenis_tiang ?? '-' }}</td><td>{{ $material?->jml_tiang_tm ?? $material?->jml_tiang ?? '-' }}</td><td>{{ $material?->jenis_tiang_tr ?? '-' }}</td><td>{{ $material?->jml_tiang_tr ?? '-' }}</td><td>{{ $material?->jenis_konduktor_tm ?? '-' }}</td><td>{{ $material?->jml_konduktor_tm ?? '-' }}</td><td>{{ $material?->jenis_konduktor_tr ?? '-' }}</td><td>{{ $material?->jml_konduktor_tr ?? '-' }}</td><td>{{ $material?->jenis_trafo ?? '-' }}</td><td>{{ $material?->jml_trafo ?? '-' }}</td><td>{{ $material?->jenis_kwh_meter ?? '-' }}</td><td>{{ $material?->jml_kwh_meter ?? '-' }}</td><td>{{ $material?->jenis_panel_meter ?? '-' }}</td><td>{{ $material?->jml_panel_meter ?? '-' }}</td><td>{{ $row->tujuan_perluasan ?: ($material?->jenis_perluasan ?? '-') }}</td><td>{{ $row->jenis_transaksi }}</td><td>{{ $row->status }}</td><td>{{ $row->no_agenda }}</td><td>{{ $row->nama_pelanggan }}</td><td>{{ $row->id_pelanggan }}</td><td>{{ $row->total_biaya !== null ? number_format($row->total_biaya, 0, ',', '.') : '-' }}</td><td>{{ $row->tgl_mohon?->format('d/m/Y') ?? '-' }}</td><td>{{ $row->tgl_bayar?->format('d/m/Y') ?? '-' }}</td><td>{{ $row->tarif_lama }}</td><td>{{ filled($row->daya_lama) ? $row->daya_lama . ' VA' : '' }}</td><td><b>{{ $row->tarif_baru }}</b></td><td><b>{{ filled($row->daya_baru) ? $row->daya_baru . ' VA' : '' }}</b></td><td>{{ $row->keterangan }}</td></tr>@empty<tr><td colspan="{{ $showSyarat ? 32 : 30 }}" style="text-align:center">Belum ada data laporan.</td></tr>@endforelse
        </tbody></table></div><div class="halaman">{{ $data->links() }}</div>
    </div>
    <div id="laporanDetailModal" class="laporan-modal" hidden><div class="laporan-modal-box" role="dialog" aria-modal="true"><button type="button" class="laporan-close" aria-label="Tutup">&times;</button><h3>Detail Data Laporan</h3><div class="laporan-detail-grid"><div><small>No Agenda</small><b id="ldNoAgenda"></b></div><div><small>IDPEL</small><b id="ldIdpel"></b></div><div><small>Nama Pelanggan</small><b id="ldNama"></b></div><div><small>Transaksi</small><b id="ldTransaksi"></b></div><div><small>Status</small><b id="ldStatus"></b></div><div><small>Jenis Perluasan</small><b id="ldTujuan"></b></div><div class="laporan-full"><small>Alamat</small><b id="ldAlamat"></b></div></div><h4>Detail Material</h4><div class="laporan-material-grid"><div><small>RAB</small><b id="ldRab"></b></div><div><small>Tiang TM</small><b id="ldTiangTm"></b></div><div><small>Tiang TR</small><b id="ldTiangTr"></b></div><div><small>Konduktor TM</small><b id="ldKonduktorTm"></b></div><div><small>Konduktor TR</small><b id="ldKonduktorTr"></b></div><div><small>Trafo</small><b id="ldTrafo"></b></div><div><small>KWH Meter</small><b id="ldKwh"></b></div><div><small>Panel Meter</small><b id="ldPanelMeter"></b></div></div><h4>Berkas Pendukung</h4><div id="ldPendukung" class="laporan-files">-</div><h4>Berkas Ijin</h4><div id="ldIjin" class="laporan-files">-</div></div></div>
    <style>.statistik{margin-bottom:16px}.stat-card{padding:18px;border:1px solid #d6e2ee;border-radius:10px;background:#fff}.stat-card small,.stat-card strong{display:block}.stat-card small{color:#637487}.stat-card strong{margin-top:6px;font-size:24px;color:#0b3d6b}.ringkasan{display:flex;gap:10px;flex-wrap:wrap}.ringkasan span{padding:8px 12px;border-radius:6px;background:#eef5fb;color:#536477}.filter{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}.filter select{padding:8px;border:1px solid #b9c7d5;border-radius:5px;background:#fff}.btn{background:#0b3d6b}.halaman{margin-top:16px}.laporan-table{width:100%;min-width:0;table-layout:fixed;font-size:10px}.laporan-table th,.laporan-table td{padding:7px 5px;white-space:normal;overflow-wrap:anywhere;word-break:normal;line-height:1.25}.laporan-table th{font-size:9px}.laporan-table td:nth-child(1){width:30px;text-align:center}.laporan-table td:nth-child(2){width:75px}.laporan-table td:nth-child(3){width:45px}.laporan-table td:nth-child(4),.laporan-table td:nth-child(5),.laporan-table td:nth-child(6){width:58px}.laporan-table td:nth-child(7),.laporan-table td:nth-child(8){width:82px}.laporan-table td:nth-child(9){width:65px}.laporan-table td:nth-child(10){width:70px}.laporan-table td:nth-child(11),.laporan-table td:nth-child(12){width:55px}.laporan-table td:nth-child(13){width:70px}.laporan-table td:nth-child(14){width:58px}.laporan-table td:nth-child(15){width:70px}.laporan-table td:nth-child(16){width:58px}.laporan-table td:nth-child(17){width:75px}.laporan-table td:nth-child(18){width:58px}.laporan-table td:nth-child(19){width:72px}.laporan-table td:nth-child(20),.laporan-table td:nth-child(21){width:70px}.laporan-table td:nth-child(n+22){width:55px}.laporan-detail{border:0;cursor:pointer}.laporan-modal{position:fixed;inset:0;z-index:30;display:grid;place-items:center;padding:20px;background:rgba(0,0,0,.45)}.laporan-modal[hidden]{display:none}.laporan-modal-box{position:relative;width:min(700px,100%);max-height:90vh;overflow:auto;padding:24px;border-radius:10px;background:#fff;box-shadow:0 12px 40px rgba(0,0,0,.25)}.laporan-modal-box h3{margin:0 0 18px;color:#0b3d6b}.laporan-modal-box h4{margin:18px 0 8px;color:#0b3d6b}.laporan-close{position:absolute;top:10px;right:10px;padding:2px 9px;background:#e8eef5;font-size:20px}.laporan-detail-grid,.laporan-material-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.laporan-detail-grid>div,.laporan-material-grid>div{padding:9px;border:1px solid #d6e2ee;border-radius:6px}.laporan-detail-grid small,.laporan-detail-grid b,.laporan-material-grid small,.laporan-material-grid b{display:block}.laporan-detail-grid small,.laporan-material-grid small{color:#637487;margin-bottom:3px}.laporan-full{grid-column:1/-1}.laporan-files{display:grid;gap:6px;padding:10px;border:1px solid #d6e2ee;border-radius:6px}.laporan-files a{color:#0b3d6b;overflow-wrap:anywhere}@media(max-width:1100px){.laporan-table{font-size:9px}.laporan-table th{font-size:8px}.laporan-table th,.laporan-table td{padding:5px 3px}}@media(max-width:600px){.laporan-detail-grid,.laporan-material-grid{grid-template-columns:1fr}.laporan-full{grid-column:auto}}
    </style>
    <script>
        (() => { const modal=document.getElementById('laporanDetailModal'); const text=(id,value)=>{document.getElementById(id).textContent=value||'-';}; const files=(id,encoded,url,jenis)=>{let list=[];try{list=JSON.parse(atob(encoded||''))}catch(e){};document.getElementById(id).innerHTML=list.length?list.map((path,i)=>`<a href="${url.replace(/pendukung\/0$/,jenis+'/'+i)}" target="_blank" rel="noopener">📄 ${String(path).split('/').pop()||'Berkas '+(i+1)}</a>`).join(''):'-';}; document.querySelectorAll('.laporan-detail').forEach((button)=>button.addEventListener('click',()=>{text('ldNoAgenda',button.dataset.noAgenda);text('ldIdpel',button.dataset.idpel);text('ldNama',button.dataset.nama);text('ldTransaksi',button.dataset.transaksi);text('ldStatus',button.dataset.status);text('ldTujuan',button.dataset.tujuan);text('ldAlamat',button.dataset.alamat);text('ldRab',button.dataset.rab?Number(button.dataset.rab).toLocaleString('id-ID'):'-');text('ldTiangTm',button.dataset.jenisTiangTm?`${button.dataset.jenisTiangTm} (${button.dataset.jmlTiangTm||0})`:'-');text('ldTiangTr',button.dataset.jenisTiangTr?`${button.dataset.jenisTiangTr} (${button.dataset.jmlTiangTr||0})`:'-');text('ldKonduktorTm',button.dataset.jenisKonduktorTm?`${button.dataset.jenisKonduktorTm} (${button.dataset.jmlKonduktorTm||0})`:'-');text('ldKonduktorTr',button.dataset.jenisKonduktorTr?`${button.dataset.jenisKonduktorTr} (${button.dataset.jmlKonduktorTr||0})`:'-');text('ldTrafo',button.dataset.jenisTrafo?`${button.dataset.jenisTrafo} (${button.dataset.jmlTrafo||0})`:'-');text('ldKwh',button.dataset.jenisKwh?`${button.dataset.jenisKwh} (${button.dataset.jmlKwh||0})`:'-');files('ldPendukung',button.dataset.pendukung,button.dataset.fileUrl,'pendukung');files('ldIjin',button.dataset.ijin,button.dataset.fileUrl,'ijin');modal.hidden=false;})); const close=()=>{modal.hidden=true};modal.querySelector('.laporan-close').addEventListener('click',close);modal.addEventListener('click',e=>{if(e.target===modal)close()});document.addEventListener('keydown',e=>{if(e.key==='Escape')close()}); })();
    </script>
    <script>
        (() => {
            let panelMap = {};
            try { panelMap = JSON.parse(document.getElementById('laporanPanelData')?.textContent || '{}'); } catch (error) { panelMap = {}; }
            document.querySelectorAll('.laporan-detail').forEach((button) => button.addEventListener('click', () => {
                const panel = panelMap[button.dataset.noAgenda] || {};
                const target = document.getElementById('ldPanelMeter');
                if (target) target.textContent = panel.jenis ? `${panel.jenis} (${panel.jumlah ?? 0})` : '-';
            }));
        })();
    </script>
    <style>.statistik{margin-bottom:16px}.stat-card{padding:18px;border:1px solid #d6e2ee;border-radius:10px;background:#fff}.stat-card small,.stat-card strong{display:block}.stat-card small{color:#637487}.stat-card strong{margin-top:6px;font-size:24px;color:#0b3d6b}.ringkasan{display:flex;gap:10px;flex-wrap:wrap}.ringkasan span{padding:8px 12px;border-radius:6px;background:#eef5fb;color:#536477}.filter{display:flex;gap:10px;margin-bottom:16px}.filter select{padding:8px;border:1px solid #b9c7d5;border-radius:5px;background:#fff}.btn{background:#0b3d6b}.halaman{margin-top:16px}</style>
    <script>
        (() => {
            const source = document.getElementById('laporanProsesData');
            if (!source) return;
            let processMap = {};
            try { processMap = JSON.parse(source.textContent || '{}'); } catch (error) { processMap = {}; }
            const formatDate = (value) => value ? new Date(value.replace(' ', 'T')).toLocaleDateString('id-ID') : '-';
            const statusCell = (done, label) => `<span class="${done ? 'proses-selesai' : 'proses-belum'}">${done ? '✓ ' + label : '× Belum diunggah'}</span>`;
            const processMarkup = (item) => `<div class="proses-box laporan-proses-box"><h4>Proses</h4><table class="proses-tabel"><thead><tr><th>Proses</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>
                <tr><td>Pengiriman ULP</td><td>${statusCell(item.ulp, 'Sudah dikirim ULP')}</td><td>${formatDate(item.ulpDate)}</td></tr>
                <tr><td>Berkas Kajian Kelayakan</td><td>${statusCell(item.planning, 'Sudah lengkap')}</td><td>${formatDate(item.planningDate)}</td></tr>
                <tr><td>Berkas WO NPS</td><td>${statusCell(item.npsWo, 'Sudah lengkap')}</td><td>${formatDate(item.npsWoDate)}</td></tr>
                <tr><td>Berkas BA Checklist</td><td>${statusCell(item.construction, 'Sudah lengkap')}</td><td>${formatDate(item.constructionDate)}</td></tr>
                <tr><td>Foto Cek KWH Meter</td><td>${statusCell(item.transaction, 'Sudah lengkap')}</td><td>${formatDate(item.transactionDate)}</td></tr>
                <tr><td>Berkas BA Operasi</td><td>${statusCell(item.network, 'Sudah lengkap')}</td><td>${formatDate(item.networkDate)}</td></tr>
            </tbody></table></div>`;
            const modal = document.getElementById('laporanDetailModal');
            document.querySelectorAll('.laporan-detail').forEach((button) => button.addEventListener('click', () => {
                const item = processMap[button.dataset.noAgenda];
                const materialHeading = [...modal.querySelectorAll('h4')].find((heading) => heading.textContent.trim() === 'Detail Material');
                modal.querySelector('.laporan-proses-box')?.remove();
                if (item && materialHeading) materialHeading.insertAdjacentHTML('beforebegin', processMarkup(item));
            }));
        })();
    </script>
    <script>
        (() => {
            const source = document.getElementById('laporanProsesData');
            const table = document.querySelector('.laporan-table');
            if (!source || !table) return;
            let processMap = {};
            try { processMap = JSON.parse(source.textContent || '{}'); } catch (error) { processMap = {}; }
            const firstHeaderRow = table.tHead?.rows?.[0];
            const detailHeader = firstHeaderRow ? [...firstHeaderRow.cells].find((cell) => cell.textContent.trim() === 'DETAIL') : null;
            if (detailHeader && !table.querySelector('.laporan-process-header')) {
                const header = document.createElement('th');
                header.className = 'grup laporan-process-header';
                header.rowSpan = 2;
                header.textContent = 'PROSES';
                detailHeader.after(header);
            }
            const labels = [['ulp', 'ULP'], ['planning', 'PERENCANAAN'], ['nps', 'NPS'], ['construction', 'KONSTRUKSI'], ['transaction', 'TRANSAKSI'], ['network', 'JARINGAN']];
            const cellMarkup = (item) => `<div class="laporan-process-stack">${labels.map(([key, label]) => `<span class="laporan-process-chip ${item[key] ? 'is-done' : 'is-pending'}" title="${label}">${item[key] ? '✓' : '×'} ${label}</span>`).join('')}</div>`;
            table.tBodies[0]?.querySelectorAll('tr').forEach((row) => {
                const agendaCell = [...row.cells].find((cell) => processMap[cell.textContent.trim()]);
                if (!agendaCell || row.querySelector('.laporan-process-cell')) return;
                const item = processMap[agendaCell.textContent.trim()];
                const detailCell = [...row.cells].find((cell) => cell.querySelector('.laporan-detail'));
                const processCell = document.createElement('td');
                processCell.className = 'laporan-process-cell';
                processCell.innerHTML = cellMarkup(item);
                (detailCell || row.cells[2])?.after(processCell);
            });
        })();
    </script>
    <script>
        (() => {
            const table = document.querySelector('.laporan-table');
            if (!table || table.dataset.columnsReordered === '1') return;
            const showSyarat = table.tHead?.textContent.includes('SYARAT');
            const materialStart = 4 + (showSyarat ? 2 : 0);
            const processIndex = 3;
            const columns = [
                [0, 'NO.'], [2, 'DETAIL'], [materialStart + 16, 'NO AGENDA'], [materialStart + 18, 'IDPEL'],
                [materialStart + 17, 'NAMA PELANGGAN'], [materialStart + 13, 'JENIS PERLUASAN'], [materialStart + 14, 'TRANSAKSI'],
                [materialStart + 15, 'STATUS'], [materialStart + 22, 'TARIF LAMA'], [materialStart + 23, 'DAYA LAMA'],
                [materialStart + 24, 'TARIF BARU'], [materialStart + 25, 'DAYA BARU'],
                [materialStart + 0, 'RAB'], [materialStart + 1, 'JENIS TIANG'], [materialStart + 2, 'JUMLAH TIANG'],
                [materialStart + 3, 'JENIS KONDUKTOR TM'], [materialStart + 4, 'JUMLAH KONDUKTOR TM'], [materialStart + 5, 'JENIS KONDUKTOR TR'],
                [materialStart + 6, 'JUMLAH KONDUKTOR TR'], [materialStart + 7, 'JENIS TRAFO'], [materialStart + 8, 'JUMLAH TRAFO'],
                [materialStart + 9, 'JENIS KWH METER'], [materialStart + 10, 'JUMLAH KWH METER'], [materialStart + 11, 'JENIS PANEL'], [materialStart + 12, 'JUMLAH PANEL'],
                [materialStart + 20, 'TANGGAL MOHON'], [materialStart + 21, 'TANGGAL BAYAR'], [materialStart + 19, 'TOTAL BIAYA'],
                [materialStart + 26, 'DURASI HARI KERJA'], [1, 'ASAL ULP'], [processIndex, 'PROSES'],
            ];
            if (showSyarat) columns.push([4, 'BERKAS PENDUKUNG'], [5, 'BERKAS IJIN']);
            const header = table.tHead?.rows?.[0];
            if (!header) return;
            header.innerHTML = '';
            columns.forEach(([, label], index) => {
                const th = document.createElement('th');
                th.textContent = label;
                th.className = index >= 10 && index <= 18 ? 'sub' : '';
                header.appendChild(th);
            });
            table.tHead.querySelector('tr:nth-child(2)')?.remove();
            table.tBodies[0]?.querySelectorAll('tr').forEach((row) => {
                if (row.cells.length <= 1) return;
                const cells = [...row.cells];
                row.innerHTML = '';
                columns.forEach(([sourceIndex]) => { if (cells[sourceIndex]) row.appendChild(cells[sourceIndex]); });
            });
            table.dataset.columnsReordered = '1';
        })();
    </script>
    <script>
        (() => {
            let panelMap = {};
            try { panelMap = JSON.parse(document.getElementById('laporanPanelData')?.textContent || '{}'); } catch (error) { panelMap = {}; }
            const addPanelColumns = () => {
                const table = document.querySelector('.laporan-table');
                if (!table || table.dataset.panelColumnsAdded === '1') return;
                const header = table.tHead?.rows?.[0];
                if (!header) return;
                if ([...table.tHead.rows].some((row) => [...row.cells].some((cell) => cell.textContent.trim() === 'JENIS PANEL'))) {
                    table.dataset.panelColumnsAdded = '1';
                    return;
                }
                const kwhHeader = [...header.cells].findIndex((cell) => cell.textContent.trim() === 'JUMLAH KWH METER');
                const agendaIndex = [...header.cells].findIndex((cell) => cell.textContent.trim() === 'NO AGENDA');
                if (kwhHeader < 0 || agendaIndex < 0) return;
                const panelHeaders = ['JENIS PANEL', 'JUMLAH PANEL'];
                panelHeaders.forEach((label, offset) => {
                    const th = document.createElement('th');
                    th.textContent = label;
                    th.className = 'sub';
                    header.insertBefore(th, header.cells[kwhHeader + 1 + offset] || null);
                });
                table.tBodies[0]?.querySelectorAll('tr').forEach((row) => {
                    if (row.cells.length <= agendaIndex) return;
                    const agenda = row.cells[agendaIndex].textContent.trim();
                    const item = panelMap[agenda] || {};
                    const cells = [item.jenis || '-', item.jumlah ?? '-'];
                    const afterKwh = [...row.cells][kwhHeader + 1] || null;
                    cells.forEach((value) => {
                        const td = document.createElement('td');
                        td.textContent = value;
                        row.insertBefore(td, afterKwh);
                    });
                });
                table.dataset.panelColumnsAdded = '1';
            };
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', addPanelColumns); else addPanelColumns();
        })();
    </script>
    <style>
        .laporan-detail,.laporan-table .detail-link{font-size:0;display:inline-grid;place-items:center}
        .laporan-table .file-icon{display:inline-flex;vertical-align:middle}
        .laporan-table .file-icon svg,.laporan-files .file-icon svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
        .laporan-process-cell{min-width:220px!important;vertical-align:top!important}.laporan-process-stack{display:flex;flex-direction:column;align-items:flex-start;gap:5px;min-width:205px}.laporan-process-chip{display:flex;align-items:center;width:max-content;min-width:150px;padding:4px 7px;border-radius:999px;font-size:10px;font-weight:700;line-height:1.1;white-space:nowrap}.laporan-process-chip.is-done{background:#e8f7ef;color:#237a53;border:1px solid #b9e4ca}.laporan-process-chip.is-pending{background:#fff0ef;color:#c23f3a;border:1px solid #f1c1bd}.laporan-proses-box{margin:18px 0;padding:14px;border:1px solid #dbe5f0;border-radius:12px;background:#f8fbff;box-shadow:0 3px 12px rgba(13,27,140,.05)}
        .laporan-proses-box h4{margin:0 0 10px;color:#123b5d;font-size:15px}.laporan-proses-box .proses-tabel{width:100%;border:1px solid #dbe5f0;border-radius:10px;overflow:hidden;background:#fff;font-size:12px}.laporan-proses-box .proses-tabel th,.laporan-proses-box .proses-tabel td{padding:9px 10px;border-bottom:1px solid #e2e8f0;text-align:left}.laporan-proses-box .proses-tabel th{background:#edf4ff;color:#123b5d;font-weight:700}.laporan-proses-box .proses-tabel tr:last-child td{border-bottom:0}.laporan-proses-box .proses-selesai{color:#2e9b68;font-weight:650}.laporan-proses-box .proses-belum{color:#d9534f;font-weight:650}
    </style>
    <script>
        (() => {
            const documentIcon = '<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5h9l3 3V20.5H6z"/><path d="M15 3.5v4h3M9 12h6M9 16h6"/></svg>';
            const paperclipIcon = '<span class="file-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m8.5 12.5 5.8-5.8a3 3 0 0 1 4.2 4.2l-7.4 7.4a4.5 4.5 0 0 1-6.4-6.4l7.1-7.1a2 2 0 0 1 2.8 2.8l-6.6 6.6"/></svg></span>';
            document.querySelectorAll('.laporan-detail').forEach((button) => { if (button.textContent.includes('📋')) button.innerHTML = documentIcon; });
            document.querySelectorAll('.laporan-table td').forEach((cell) => { if (cell.textContent.trim() === '📎') cell.innerHTML = paperclipIcon; });
            document.querySelectorAll('.laporan-files a').forEach((link) => { if (link.textContent.trim().startsWith('📄')) link.innerHTML = paperclipIcon + link.textContent.trim().replace(/^📄\s*/, ''); });
        })();
    </script>
    <style>
        .tahap-filter{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0;padding:8px 10px;border:1px solid #cbd9e8;border-radius:9px;background:#f8fafc}.tahap-filter legend{padding:0 4px;color:#243b53;font-size:11px;font-weight:700}.tahap-filter label{display:inline-flex;align-items:center;gap:5px;color:#334e68;font-size:11px;font-weight:600;white-space:nowrap}.tahap-filter input{accent-color:#111c91}
        /* Keep report columns readable; allow horizontal scrolling instead of squeezing them. */
        .laporan-scroll{width:100%;max-width:100%;overflow-x:auto!important;overflow-y:visible!important;border-radius:12px!important;scrollbar-width:thin;scrollbar-color:#8aaed1 #edf3f9}
        .laporan-scroll::-webkit-scrollbar{height:10px}.laporan-scroll::-webkit-scrollbar-track{background:#edf3f9;border-radius:10px}.laporan-scroll::-webkit-scrollbar-thumb{background:#8aaed1;border-radius:10px}.laporan-scroll::-webkit-scrollbar-thumb:hover{background:#1e6fa8}
        .laporan-table{width:max-content!important;min-width:1900px!important;table-layout:auto!important;font-size:12px!important}
        .laporan-table th,.laporan-table td{white-space:nowrap!important;overflow-wrap:normal!important;word-break:normal!important;line-height:1.35!important;padding:9px 10px!important}
        .laporan-table th{position:relative;z-index:1;font-size:11px!important;letter-spacing:.035em!important}
        .laporan-table td{min-width:72px}.laporan-table td:nth-child(1){min-width:42px;width:42px}.laporan-table td:nth-child(2){min-width:105px}.laporan-table td:nth-child(3){min-width:64px}.laporan-table td:nth-child(6){min-width:120px}.laporan-table td:nth-child(7){min-width:190px}.laporan-table td:nth-child(8){min-width:125px}.laporan-table td:nth-child(15){min-width:135px}.laporan-table td:nth-child(16){min-width:115px}.laporan-table td:nth-child(17){min-width:90px}.laporan-table td:nth-child(18){min-width:145px}
        .laporan-table .detail-link{width:32px;height:32px;padding:0!important}
        @media(max-width:700px){.laporan-table{min-width:1750px!important}.laporan-table th,.laporan-table td{padding:8px!important}}
        /* Report controls aligned with the current navy/cyan theme. */
        .statistik{grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px!important;margin-bottom:16px!important}
        .stat-card{min-height:78px!important;padding:14px 16px!important;border:1px solid #dbe5f0!important;border-top:3px solid #2b73fe!important;border-radius:12px!important;background:#fff!important;box-shadow:0 4px 14px rgba(13,27,140,.06)!important}
        .stat-card:nth-child(2){border-top-color:#27a9d6!important}.stat-card:nth-child(3){border-top-color:#f2a541!important}.stat-card:nth-child(4){border-top-color:#2e9b68!important}.stat-card:nth-child(5){border-top-color:#d9534f!important}
        .stat-card small{font-size:11px!important;font-weight:700!important;color:#627d98!important;text-transform:uppercase;letter-spacing:.035em}.stat-card strong{margin-top:7px!important;font-size:23px!important;color:#123b5d!important}
        .laporan-scroll+.halaman{color:#627d98}
        /* Pagination Laravel memakai class Tailwind; batasi SVG agar tidak memakai ukuran default 300x150. */
        .laporan-scroll+.halaman nav{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:16px!important;min-height:38px!important;color:#627d98!important;font-size:12px!important}
        .laporan-scroll+.halaman nav>div:first-child{display:none!important}
        .laporan-scroll+.halaman nav>div:nth-child(2){display:flex!important;align-items:center!important;justify-content:space-between!important;gap:16px!important;flex:1!important}
        .laporan-scroll+.halaman nav>div:nth-child(2)>div:first-child{display:block!important;color:#627d98!important;white-space:nowrap!important}
        .laporan-scroll+.halaman nav>div:nth-child(2)>div:last-child{display:flex!important;align-items:center!important;justify-content:flex-end!important}
        .laporan-scroll+.halaman nav>div:nth-child(2)>div:last-child>span{display:flex!important;align-items:center!important;gap:4px!important;box-shadow:none!important;border-radius:8px!important;overflow:visible!important}
        .laporan-scroll+.halaman nav a,.laporan-scroll+.halaman nav span[aria-current]>span,.laporan-scroll+.halaman nav span[aria-disabled]>span{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-width:32px!important;height:32px!important;padding:6px 9px!important;border:1px solid #cbd9e8!important;border-radius:8px!important;background:#fff!important;color:#123b5d!important;line-height:1!important;text-decoration:none!important;box-shadow:none!important}
        .laporan-scroll+.halaman nav span[aria-current]>span{background:#111c91!important;border-color:#111c91!important;color:#fff!important;font-weight:700!important}
        .laporan-scroll+.halaman nav span[aria-disabled]>span{background:#f8fafc!important;color:#94a3b8!important;cursor:not-allowed!important}
        .laporan-scroll+.halaman nav a:hover{background:#111c91!important;border-color:#111c91!important;color:#fff!important}
        .laporan-scroll+.halaman nav svg{display:block!important;width:16px!important;height:16px!important;max-width:16px!important;max-height:16px!important;fill:currentColor!important}
        @media(max-width:650px){.laporan-scroll+.halaman nav{align-items:flex-start!important;flex-direction:column!important;gap:9px!important}.laporan-scroll+.halaman nav>div:nth-child(2){align-items:flex-start!important;flex-direction:column!important;gap:9px!important}.laporan-scroll+.halaman nav>div:nth-child(2)>div:last-child{justify-content:flex-start!important}}
        .box:has(>.filter){padding:14px!important;border-radius:14px!important}
        .box>.filter{display:flex;align-items:center;gap:10px!important;margin:0 0 16px!important;padding:0!important}
        .box>.filter select,.box>.filter input[type=text]{min-height:38px!important;padding:8px 11px!important;border:1px solid #cbd9e8!important;border-radius:9px!important;background:#f8fafc!important;color:#243b53!important;font-size:12px!important}.box>.filter select{padding-right:30px!important}
        .box>.filter select:focus,.box>.filter input[type=text]:focus{border-color:#1e6fa8!important;box-shadow:0 0 0 3px rgba(30,111,168,.13)!important;outline:0}
        .box>.filter button.btn{min-height:38px!important;border:0!important;border-radius:9px!important;background:#111c91!important;color:#fff!important;font-weight:700!important;box-shadow:0 4px 10px rgba(17,28,145,.2)!important}
        .box>.filter button.btn:hover{background:#0d166f!important;color:#fff!important}.box>.filter .btn-excel{background:#111c91!important;color:#fff!important;box-shadow:0 4px 10px rgba(17,28,145,.2)!important}.box>.filter .btn-excel:hover{background:#0d166f!important;color:#fff!important}
        @media(max-width:700px){.box>.filter{align-items:stretch;flex-wrap:wrap}.box>.filter select,.box>.filter input[type=text],.box>.filter .btn{flex:1 1 145px}}
    </style>
@endsection
