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
            const parseEncodedJson = (value) => {
                if (!value) return [];

                try {
                    return JSON.parse(atob(value));
                } catch (error) {
                    return [];
                }
            };
            const fileRow = (url, fileName, uploadDate, deleteForm = '') => {
                const safeName = escapeHtml(fileName);
                const safeDate = uploadDate ? `Diunggah ${escapeHtml(date(uploadDate))}` : 'Tanggal tidak tersedia';
                return `<div class="result-file-row"><div class="result-file-info"><a href="${url}" target="_blank" rel="noopener"><span class="result-file-icon" aria-hidden="true">▤</span>${safeName}</a><span class="result-file-date">${safeDate}</span></div>${deleteForm}</div>`;
            };
            const renderFileList = (element, files, names, urlTemplate, uploadDate, fallbackLabel, deleteFormForIndex = null) => {
                if (!element) return;

                if (!files.length) {
                    element.innerHTML = '<span class="result-empty">Belum ada berkas</span>';
                    return;
                }

                element.innerHTML = files.map((path, index) => {
                    const fileName = names[index] || String(path).split('/').pop() || `${fallbackLabel} ${index + 1}`;
                    const fileUrl = urlTemplate.replace(/\/0$/, `/${index}`);
                    const deleteForm = deleteFormForIndex ? deleteFormForIndex(index) : '';

                    return fileRow(fileUrl, fileName, uploadDate, deleteForm);
                }).join('');
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
                    const canDecideEligibility = button.dataset.tahap === 'VENDOR_TIANG' && Boolean(button.dataset.vendorId);
                    if (planningEligibilityForm) {
                        planningEligibilityForm.hidden = !canDecideEligibility;
                        planningEligibilityForm.action = button.dataset.kelayakanUrl;
                    }
                    if (resultUploadForm) resultUploadForm.hidden = qualificationStatus !== 'LAYAK' || !canDecideEligibility;
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
                        vendorForm.hidden = !stageAllowsTiang;
                        vendorForm.classList.toggle('vendor-form', stageAllowsTiang);
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
                        constructionForm.classList.toggle('vendor-form', stageAllowsConstruction);
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
                    const constructionResultFiles = parseEncodedJson(button.dataset.konstruksiHasilKonstruksiFiles);
                    renderFileList(
                        document.getElementById('constructionResultFiles'),
                        constructionResultFiles,
                        [],
                        button.dataset.konstruksiHasilFileUrl,
                        button.dataset.prosesKonstruksiDate,
                        'Berkas hasil konstruksi',
                        constructionResultUploadForm ? (index) => `<form method="POST" action="{{ url('/vendor/konstruksi') }}/${button.dataset.id}/hasil-konstruksi/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : null
                    );
                    if (constructionResultUploadForm) {
                        constructionResultUploadForm.action = `{{ url('/vendor/konstruksi') }}/${button.dataset.id}/hasil-konstruksi`;
                    }

                    const npsWoFiles = parseEncodedJson(button.dataset.npsWoFiles);
                    renderFileList(
                        document.getElementById('npsWoFiles'),
                        npsWoFiles,
                        [],
                        button.dataset.npsWoFileUrl,
                        button.dataset.prosesNpsWoDate,
                        'Berkas WO NPS',
                        npsWoUploadForm ? (index) => `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/nps-wo/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas WO NPS ini?')">Hapus</button></form>` : null
                    );
                    if (npsWoUploadForm) {
                        npsWoUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/nps-wo`;
                    }

                    const transaksiResultFiles = parseEncodedJson(button.dataset.transaksiHasilFiles);
                    renderFileList(
                        document.getElementById('transaksiResultFiles'),
                        transaksiResultFiles,
                        [],
                        button.dataset.transaksiHasilFileUrl,
                        button.dataset.prosesTransaksiDate,
                        'Berkas hasil transaksi',
                        transaksiResultUploadForm ? (index) => `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/hasil-transaksi/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : null
                    );
                    if (transaksiResultUploadForm) {
                        transaksiResultUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/hasil-transaksi`;
                    }

                    const jaringanResultFiles = parseEncodedJson(button.dataset.jaringanHasilFiles);
                    renderFileList(
                        document.getElementById('jaringanResultFiles'),
                        jaringanResultFiles,
                        [],
                        button.dataset.jaringanHasilFileUrl,
                        button.dataset.prosesJaringanDate,
                        'Berkas hasil jaringan',
                        jaringanResultUploadForm ? (index) => `<form method="POST" action="{{ url('/pbpd') }}/${button.dataset.id}/hasil-jaringan/${index}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : null
                    );
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

                    const constructionVendorReportFiles = parseEncodedJson(button.dataset.konstruksiLaporanFiles);
                    const constructionVendorReportNames = parseEncodedJson(button.dataset.konstruksiLaporanNames);
                    renderFileList(
                        document.getElementById('constructionVendorReportFiles'),
                        constructionVendorReportFiles,
                        constructionVendorReportNames,
                        button.dataset.konstruksiLaporanFileUrl,
                        button.dataset.konstruksiLaporanDate,
                        'Berkas laporan konstruksi'
                    );

                    const reportResult = document.getElementById('vendorReportResult');
                    if (reportResult) {
                        setText('laporanLengkap', button.dataset.laporanLengkap);
                        setText('laporanSesuai', button.dataset.laporanSesuai);
                        setText('laporanFoto', button.dataset.laporanFoto);
                        setText('laporanSiap', button.dataset.laporanSiap);
                        setText('laporanCatatan', button.dataset.laporanCatatan);
                    }

                    const vendorReportFiles = parseEncodedJson(button.dataset.laporanFiles);
                    const vendorReportNames = parseEncodedJson(button.dataset.laporanNames);
                    renderFileList(
                        document.getElementById('vendorReportFiles'),
                        vendorReportFiles,
                        vendorReportNames,
                        button.dataset.reportFileUrl,
                        button.dataset.laporanDate,
                        'Berkas laporan vendor'
                    );

                    const resultFiles = parseEncodedJson(button.dataset.resultFiles);
                    const resultNames = parseEncodedJson(button.dataset.resultNames);
                    renderFileList(
                        document.getElementById('resultFiles'),
                        resultFiles,
                        resultNames,
                        button.dataset.resultFileUrl,
                        button.dataset.prosesPerencanaanDate,
                        'Berkas hasil',
                        resultUploadForm ? (index) => `<form method="POST" action="${button.dataset.resultDeleteUrl.replace(/\/0$/, '/' + index)}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button type="submit" class="result-delete" onclick="return confirm('Hapus berkas ini?')">Hapus</button></form>` : null
                    );                    updateFileStatuses();
                });
            });

            const closeDetailModal = () => { modal.hidden = true; };
            document.getElementById('closeDetailPerluasan').addEventListener('click', closeDetailModal);
            document.getElementById('dismissDetailPerluasan').addEventListener('click', closeDetailModal);
            modal.addEventListener('click', (event) => { if (event.target === modal) closeDetailModal(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeDetailModal(); });
        })();
    </script>
