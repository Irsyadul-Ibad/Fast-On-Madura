    <script>
        (() => {
            const modal = document.getElementById('laporanDetailModal');
            const text = (id, value) => {
                document.getElementById(id).textContent = value || '-';
            };
            const files = (id, encoded, encodedNames, url, jenis) => {
                let list = [];
                let names = [];
                try {
                    list = JSON.parse(atob(encoded || ''));
                } catch (e) {}
                try {
                    names = JSON.parse(atob(encodedNames || ''));
                } catch (e) {}

                document.getElementById(id).innerHTML = list.length
                    ? list.map((path, i) => {
                        const fileName = names[i] || String(path).split('/').pop() || `Berkas ${i + 1}`;
                        const safeFileName = String(fileName).replace(/[&<>"']/g, (character) => ({
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#039;',
                        }[character]));

                        return `<a href="${url.replace(/pendukung\/0$/, jenis + '/' + i)}" target="_blank" rel="noopener"><span class="file-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m8.5 12.5 5.8-5.8a3 3 0 0 1 4.2 4.2l-7.4 7.4a4.5 4.5 0 0 1-6.4-6.4l7.1-7.1a2 2 0 0 1 2.8 2.8l-6.6 6.6"/></svg></span><span>${safeFileName}</span></a>`;
                    }).join('')
                    : '<span class="laporan-file-empty">Belum ada berkas.</span>';
            };

            document.querySelectorAll('.laporan-detail').forEach((button) => button.addEventListener('click', () => {
                text('ldNoAgenda', button.dataset.noAgenda);
                text('ldIdpel', button.dataset.idpel);
                text('ldNama', button.dataset.nama);
                document.getElementById('ldSummary').textContent = `Agenda ${button.dataset.noAgenda || '-'} · ${button.dataset.nama || '-'}`;
                text('ldTransaksi', button.dataset.transaksi);
                text('ldStatus', button.dataset.status);
                text('ldTujuan', button.dataset.tujuan);
                text('ldAlamat', button.dataset.alamat);
                text('ldRab', button.dataset.rab ? `Rp ${Number(button.dataset.rab).toLocaleString('id-ID')}` : '-');
                text('ldTiangTm', button.dataset.jenisTiangTm ? `${button.dataset.jenisTiangTm} (${button.dataset.jmlTiangTm || 0})` : '-');
                text('ldTiangTr', button.dataset.jenisTiangTr ? `${button.dataset.jenisTiangTr} (${button.dataset.jmlTiangTr || 0})` : '-');
                text('ldKonduktorTm', button.dataset.jenisKonduktorTm ? `${button.dataset.jenisKonduktorTm} (${button.dataset.jmlKonduktorTm || 0})` : '-');
                text('ldKonduktorTr', button.dataset.jenisKonduktorTr ? `${button.dataset.jenisKonduktorTr} (${button.dataset.jmlKonduktorTr || 0})` : '-');
                text('ldTrafo', button.dataset.jenisTrafo ? `${button.dataset.jenisTrafo} (${button.dataset.jmlTrafo || 0})` : '-');
                text('ldKwh', button.dataset.jenisKwh ? `${button.dataset.jenisKwh} (${button.dataset.jmlKwh || 0})` : '-');
                files('ldPendukung', button.dataset.pendukung, button.dataset.pendukungNames, button.dataset.fileUrl, 'pendukung');
                files('ldIjin', button.dataset.ijin, button.dataset.ijinNames, button.dataset.fileUrl, 'ijin');
                modal.hidden = false;
            }));

            const close = () => {
                modal.hidden = true;
            };
            modal.querySelectorAll('.laporan-close, .laporan-modal-dismiss').forEach((button) => button.addEventListener('click', close));
            modal.addEventListener('click', (e) => {
                if (e.target === modal) close();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });
        })();
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

    <script>
        (() => {
            const source = document.getElementById('laporanProsesData');
            if (!source) return;
            let processMap = {};
            try { processMap = JSON.parse(source.textContent || '{}'); } catch (error) { processMap = {}; }
            const formatDate = (value) => value ? new Date(value.replace(' ', 'T')).toLocaleDateString('id-ID') : '—';
            const processMarkup = (item) => {
                const stages = [
                    ['ulp', 'Pengiriman ULP', 'ulpDate', 'Sudah dikirim ULP'],
                    ['planning', 'Berkas Kajian Kelayakan', 'planningDate', 'Sudah lengkap'],
                    ['npsWo', 'Berkas WO NPS', 'npsWoDate', 'Sudah lengkap'],
                    ['construction', 'Berkas BA Checklist', 'constructionDate', 'Sudah lengkap'],
                    ['transaction', 'Foto Cek KWH Meter', 'transactionDate', 'Sudah lengkap'],
                    ['network', 'Berkas BA Operasi', 'networkDate', 'Sudah lengkap'],
                ];
                return stages.map(([key, label, dateKey, doneLabel]) => {
                    const done = Boolean(item[key]);
                    return `<div class="laporan-process-row">
                        <span class="laporan-process-mark ${done ? 'is-done' : 'is-pending'}" aria-hidden="true">${done ? '✓' : '×'}</span>
                        <strong>${label}</strong>
                        <span class="laporan-process-status ${done ? 'is-done' : 'is-pending'}">${done ? doneLabel : 'Belum diunggah'}</span>
                        <time>${formatDate(item[dateKey])}</time>
                    </div>`;
                }).join('');
            };
            const modal = document.getElementById('laporanDetailModal');
            document.querySelectorAll('.laporan-detail').forEach((button) => button.addEventListener('click', () => {
                const item = processMap[button.dataset.noAgenda] || {};
                document.getElementById('ldProcessRows').innerHTML = processMarkup(item);
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
                [0, 'NO.'], [2, 'DETAIL'], [materialStart + 18, 'NO AGENDA'], [materialStart + 20, 'IDPEL'],
                [materialStart + 19, 'NAMA PELANGGAN'], [materialStart + 15, 'JENIS PERLUASAN'], [materialStart + 16, 'TRANSAKSI'],
                [materialStart + 17, 'STATUS'], [materialStart + 24, 'TARIF LAMA'], [materialStart + 25, 'DAYA LAMA'],
                [materialStart + 26, 'TARIF BARU'], [materialStart + 27, 'DAYA BARU'],
                [materialStart + 0, 'RAB'], [materialStart + 1, 'JENIS TIANG TM'], [materialStart + 2, 'JUMLAH TIANG TM'],
                [materialStart + 3, 'JENIS TIANG TR'], [materialStart + 4, 'JUMLAH TIANG TR'], [materialStart + 5, 'JENIS KONDUKTOR TM'],
                [materialStart + 6, 'JUMLAH KONDUKTOR TM'], [materialStart + 7, 'JENIS KONDUKTOR TR'], [materialStart + 8, 'JUMLAH KONDUKTOR TR'],
                [materialStart + 9, 'JENIS TRAFO'], [materialStart + 10, 'JUMLAH TRAFO'],
                [materialStart + 11, 'JENIS KWH METER'], [materialStart + 12, 'JUMLAH KWH METER'],
                [materialStart + 13, 'JENIS PANEL'], [materialStart + 14, 'JUMLAH PANEL'],
                [materialStart + 22, 'TANGGAL MOHON'], [materialStart + 23, 'TANGGAL BAYAR'], [materialStart + 21, 'TOTAL BIAYA'],
                [materialStart + 28, 'DURASI HARI KERJA'], [1, 'ASAL ULP'], [processIndex, 'PROSES'],
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

    <script>
        (() => {
            const documentIcon = '<svg class="icon-inline" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3.5h9l3 3V20.5H6z"/><path d="M15 3.5v4h3M9 12h6M9 16h6"/></svg>';
            const paperclipIcon = '<span class="file-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m8.5 12.5 5.8-5.8a3 3 0 0 1 4.2 4.2l-7.4 7.4a4.5 4.5 0 0 1-6.4-6.4l7.1-7.1a2 2 0 0 1 2.8 2.8l-6.6 6.6"/></svg></span>';
            document.querySelectorAll('.laporan-detail').forEach((button) => { if (button.textContent.includes('📋')) button.innerHTML = documentIcon; });
            document.querySelectorAll('.laporan-table td').forEach((cell) => { if (cell.textContent.trim() === '📎') cell.innerHTML = paperclipIcon; });
            document.querySelectorAll('.laporan-files a').forEach((link) => { if (link.textContent.trim().startsWith('📄')) link.innerHTML = paperclipIcon + link.textContent.trim().replace(/^📄\s*/, ''); });
        })();
    </script>
