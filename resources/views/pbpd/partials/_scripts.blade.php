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
                    document.getElementById('syaratModalDescription').textContent = jenis === 'ijin'
                        ? 'Unggah dokumen atau foto perizinan untuk data ini'
                        : 'Unggah dokumen atau foto pendukung untuk data ini';
                    let files = [];
                    try { files = JSON.parse(button.dataset.files ? atob(button.dataset.files) : '[]'); } catch (error) { files = []; }
                    let fileNames = [];
                    try { fileNames = JSON.parse(button.dataset.fileNames ? atob(button.dataset.fileNames) : '[]'); } catch (error) { fileNames = []; }
                    const filesBox = document.getElementById('syaratFiles');
                    document.getElementById('syaratFilesHeading').textContent = `BERKAS TERUNGGAH${files.length ? ` · ${files.length}` : ''}`;
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

                            const fileUrl = button.dataset.fileUrl.replace(/\/0$/, '/' + index);
                            const deleteUrl = filesBox.dataset.deleteUrlTemplate
                                .replace('__ID__', button.dataset.id)
                                .replace('__JENIS__', jenis)
                                .replace('__INDEX__', index);
                            const removeAction = syaratUploadForm ? `<form class="min-w-0" method="POST" action="${deleteUrl}" onsubmit="return confirm('Hapus berkas ini?')"><input type="hidden" name="_token" value="${filesBox.dataset.csrfToken}"><input type="hidden" name="_method" value="DELETE"><button type="submit" aria-label="Hapus ${safeFileName}" class="flex h-9 w-9 items-center justify-center !rounded-xl !border-0 !bg-[#eef2ff] !p-0 !text-[#344a81] !shadow-none hover:!bg-red-50 hover:!text-red-600 hover:!shadow-none sm:h-11 sm:w-11 sm:!rounded-2xl"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg></button></form>` : '';
                            return `<div class="flex min-w-0 flex-col gap-2 rounded-[20px] border border-[#d5dffc] bg-[#f8faff] p-2 sm:flex-row sm:items-center sm:gap-4 sm:px-4"><div class="flex min-w-0 items-center gap-2 sm:flex-1"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-[#214fe0] sm:h-14 sm:w-14"><svg class="h-5 w-5 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 13h6m-6 4h6"/></svg></span><a class="min-w-0 flex-1 truncate font-bold !text-[#2451dc] underline" href="${fileUrl}" target="_blank" rel="noopener">${safeFileName}</a></div><div class="flex items-center justify-end gap-2"><a class="flex h-9 w-9 shrink-0 items-center justify-center !rounded-xl !bg-[#eef2ff] !text-[#344a81] sm:h-11 sm:w-11 sm:!rounded-2xl" href="${fileUrl}" download aria-label="Unduh ${safeFileName}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v12m0 0 5-5m-5 5-5-5M5 20h14"/></svg></a>${removeAction}</div></div>`;
                        }).join('')
                        : '<div class="flex min-w-0 flex-col items-start gap-3 rounded-[20px] border border-[#e0e7f7] bg-[#f8faff] p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#e7edff] text-[#2451dc] sm:h-16 sm:w-16"><svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h7l2 2h9v10H3z"/></svg></span><span class="min-w-0"><strong class="block text-base font-extrabold text-[#1236a5]">Belum ada berkas</strong><span class="mt-1 block text-sm text-[#5e719e]">Berkas yang diunggah akan tampil di sini.</span></span></div>';
                    if (syaratUploadForm) syaratUploadForm.action = `{{ url('/pbpd') }}/${button.dataset.id}/syarat/${jenis}`;
                    const fileInput = document.getElementById('syaratFileInput');
                    const fileNameDisplay = document.getElementById('syaratFileNames');
                    const uploadButton = document.getElementById('syaratUploadButton');
                    if (fileInput) fileInput.value = '';
                    if (fileNameDisplay) fileNameDisplay.textContent = 'Belum ada file dipilih';
                    if (uploadButton) uploadButton.disabled = true;
                    syaratModal.hidden = false;
                });
            });
            const closeSyaratModal = () => { syaratModal.hidden = true; };
            document.getElementById('closeSyaratModal').addEventListener('click', closeSyaratModal);
            document.getElementById('closeSyaratModalFooter').addEventListener('click', closeSyaratModal);
            syaratModal.addEventListener('click', (event) => { if (event.target === syaratModal) closeSyaratModal(); });
            document.getElementById('syaratFileInput')?.addEventListener('change', (event) => {
                const files = [...event.target.files];
                document.getElementById('syaratFileNames').textContent = files.length
                    ? files.length === 1 ? files[0].name : `${files.length} file dipilih`
                    : 'Belum ada file dipilih';
                document.getElementById('syaratUploadButton').disabled = !files.length;
            });

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
