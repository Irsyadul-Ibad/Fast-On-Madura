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
            const submitSendButton = document.getElementById('submitSendModal');
            const sendModalSummary = document.getElementById('sendModalSummary');
            const sendModalFooterSummary = document.getElementById('sendModalFooterSummary');
            const tujuanLabels = { JTM: 'Perluasan JTM', JTR: 'Perluasan JTR', TANPA_PERLUASAN: 'Tanpa Perluasan' };
            const updateSendModalSummary = () => {
                const selectedCount = checks.filter((check) => check.checked).length;
                const selectedDestination = tujuanLabels[tujuanKirim?.value];
                if (sendModalSummary) sendModalSummary.textContent = `${selectedCount} data dipilih · pilih tujuan dan isi kebutuhan material`;
                if (sendModalFooterSummary) {
                    sendModalFooterSummary.textContent = selectedDestination
                        ? `${selectedCount} data akan dikirim ke ${selectedDestination}`
                        : `${selectedCount} data dipilih · pilih tujuan pengiriman`;
                }
                if (submitSendButton) submitSendButton.disabled = selectedCount === 0 || !tujuanKirim?.value;
            };
            const updateExpansionNeeds = () => {
                if (tujuanKirim && expansionNeeds) expansionNeeds.hidden = !['JTM', 'JTR'].includes(tujuanKirim.value);
                tujuanCards.forEach((card) => {
                    const selected = card.dataset.tujuan === tujuanKirim?.value;
                    card.classList.toggle('is-selected', selected);
                    card.setAttribute('aria-checked', selected ? 'true' : 'false');
                });
                updateSendModalSummary();
            };
            if (tujuanKirim) tujuanKirim.addEventListener('change', updateExpansionNeeds);
            tujuanCards.forEach((card) => card.addEventListener('click', () => { tujuanKirim.value = card.dataset.tujuan; updateExpansionNeeds(); }));
            const updateSendButton = () => {
                const selectedCount = checks.filter((check) => check.checked).length;
                if (sendButton) sendButton.disabled = selectedCount === 0;
                const selectedCountLabel = document.getElementById('selectedPelangganCount');
                if (selectedCountLabel) selectedCountLabel.textContent = String(selectedCount);
                updateSendModalSummary();
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
                    updateSendModalSummary();
                    sendModal.hidden = false;
                });
                const closeSendModal = () => { sendModal.hidden = true; };
                document.getElementById('closeSendModal').addEventListener('click', closeSendModal);
                document.getElementById('cancelSendModal').addEventListener('click', closeSendModal);
                sendModal.addEventListener('click', (event) => { if (event.target === sendModal) sendModal.hidden = true; });
                document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !sendModal.hidden) closeSendModal(); });
            }
            updateSendButton();
            updateExpansionNeeds();
        })();
    </script>
