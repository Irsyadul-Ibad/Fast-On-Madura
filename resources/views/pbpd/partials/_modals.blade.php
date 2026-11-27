    @if ($canKirim)
        <div id="sendModal" class="detail-modal" hidden>
            <div class="send-modal-box" role="dialog" aria-modal="true" aria-labelledby="sendModalTitle" aria-describedby="sendModalSummary">
                <form method="POST" action="{{ route('pbpd.kirim') }}" id="sendForm" class="send-form">
                    @csrf
                    <div id="selectedPelanggan"></div>
                    <header class="send-modal-header">
                        <div>
                            <h3 id="sendModalTitle">Kirim data terpilih</h3>
                            <p id="sendModalSummary">Pilih data dan tujuan pengiriman</p>
                        </div>
                        <button type="button" class="send-modal-close" id="closeSendModal" aria-label="Tutup">&times;</button>
                    </header>
                    <div class="send-modal-body">
                        <section class="send-modal-section" aria-labelledby="sendDestinationHeading">
                            <h4 id="sendDestinationHeading" class="send-section-title">Tujuan Pengiriman</h4>
                            <select name="tujuan" id="tujuanKirim" required class="tujuan-hidden-select" aria-label="Pilih tujuan pengiriman">
                                <option value="">Pilih tujuan</option><option value="JTM">Perluasan JTM</option><option value="JTR">Perluasan JTR</option><option value="TANPA_PERLUASAN">Tanpa Perluasan</option>
                            </select>
                            <div class="tujuan-cards" role="radiogroup" aria-label="Pilih tujuan pengiriman">
                                <button type="button" class="tujuan-card" data-tujuan="JTM" role="radio" aria-checked="false"><span class="tujuan-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m13.3 2.5-8 11h5.8l-.6 8 8.2-12h-5.9l.5-7Z"/></svg></span><strong>Perluasan JTM</strong><small>Jaringan Tegangan Menengah</small><span class="tujuan-card-check" aria-hidden="true">✓</span></button>
                                <button type="button" class="tujuan-card" data-tujuan="JTR" role="radio" aria-checked="false"><span class="tujuan-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m13.3 2.5-8 11h5.8l-.6 8 8.2-12h-5.9l.5-7Z"/></svg></span><strong>Perluasan JTR</strong><small>Jaringan Tegangan Rendah</small><span class="tujuan-card-check" aria-hidden="true">✓</span></button>
                                <button type="button" class="tujuan-card" data-tujuan="TANPA_PERLUASAN" role="radio" aria-checked="false"><span class="tujuan-card-icon tujuan-card-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="3"/><path d="M9 9h6v6H9z"/></svg></span><strong>Tanpa Perluasan</strong><small>Langsung sampai tujuan</small><span class="tujuan-card-check" aria-hidden="true">✓</span></button>
                            </div>
                        </section>
                        <section id="expansionNeeds" class="send-modal-section" aria-labelledby="sendMaterialHeading" hidden>
                            <h4 id="sendMaterialHeading" class="send-section-title">Kebutuhan Material</h4>
                            <div class="send-material-table-wrap">
                                <table class="kebutuhan-tabel">
                                    <thead><tr><th>Keterangan</th><th>Jenis</th><th>Jumlah</th><th>Satuan</th></tr></thead>
                                    <tbody>
                                        <tr><th scope="row">Tiang TM</th><td><select name="jenis_tiang_tm"><option value="">Pilih</option><option>11</option><option>13</option></select></td><td><input name="jml_tiang_tm" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Buah</span></td></tr>
                                        <tr><th scope="row">Tiang TR</th><td><select name="jenis_tiang_tr"><option value="">Pilih</option><option>9</option></select></td><td><input name="jml_tiang_tr" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Buah</span></td></tr>
                                        <tr><th scope="row">Konduktor TM</th><td><select name="jenis_konduktor_tm"><option value="">Pilih</option><option>AAAC-S 240</option><option>AAAC-S 150</option><option>AAAC-S 70</option><option>AAAC 240</option><option>AAAC 150</option><option>AAAC 70</option></select></td><td><input name="jml_konduktor_tm" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Meter</span></td></tr>
                                        <tr><th scope="row">Konduktor TR</th><td><select name="jenis_konduktor_tr"><option value="">Pilih</option><option>LVTC 3x70+1x70</option><option>LVTC 3x70+1x50</option></select></td><td><input name="jml_konduktor_tr" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Meter</span></td></tr>
                                        <tr><th scope="row">Trafo</th><td><select name="jenis_trafo"><option value="">Pilih</option><option>0</option><option>50</option><option>100</option><option>160</option><option>200</option><option>250</option></select></td><td><input name="jml_trafo" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Buah</span></td></tr>
                                        <tr><th scope="row">KWH Meter</th><td><select name="jenis_kwh_meter"><option value="">Pilih</option><option>Prabayar</option><option>Pascabayar</option><option>Lainnya</option></select></td><td><input name="jml_kwh_meter" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Meter</span></td></tr>
                                        <tr><th scope="row">Panel Meter</th><td><select name="jenis_panel_meter"><option value="">Pilih</option><option>Umum</option><option>53 kva</option><option>66 kva</option><option>105 kva</option><option>164 kva</option><option>197 kva</option></select></td><td><input name="jml_panel_meter" type="number" min="0" placeholder="Jumlah"></td><td><span class="send-unit">Buah</span></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                    <footer class="send-modal-footer">
                        <p id="sendModalFooterSummary">Pilih data untuk dikirim</p>
                        <div class="send-modal-actions">
                            <button type="button" class="send-modal-cancel" id="cancelSendModal">Batal</button>
                            <button type="submit" class="send-modal-submit" id="submitSendModal" disabled><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>Kirim sekarang</button>
                        </div>
                    </footer>
                </form>
            </div>
        </div>
    @endif

    <div id="detailModal" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
            <header class="detail-modal-header">
                <div>
                    <h3 id="detailModalTitle">Detail Data PB/PD</h3>
                    <p id="detailSummary" class="detail-modal-summary"></p>
                </div>
                <button type="button" class="detail-modal-close" aria-label="Tutup detail">&times;</button>
            </header>

            <div class="detail-modal-content">
                <section class="detail-section" aria-labelledby="detailCustomerHeading">
                    <h4 id="detailCustomerHeading" class="detail-section-title">Informasi Pelanggan</h4>
                    <div class="detail-grid detail-customer-grid">
                        <div><small>No Agenda</small><b id="detailNoAgenda"></b></div>
                        <div><small>IDPEL</small><b id="detailIdpel"></b></div>
                        <div><small>Nama Pelanggan</small><b id="detailNama"></b></div>
                        <div><small>Transaksi</small><b id="detailTransaksi" class="detail-value-pill"></b></div>
                        <div><small>Status</small><b id="detailStatus" class="detail-value-pill"></b></div>
                        <div><small>Total Biaya</small><b id="detailTotalBiaya"></b></div>
                        <div class="detail-full"><small>Alamat</small><b id="detailAlamat"></b></div>
                    </div>
                </section>

                <section class="detail-section" aria-labelledby="detailCostHeading">
                    <h4 id="detailCostHeading" class="detail-section-title">Biaya dan Jadwal</h4>
                    <div class="detail-cost-grid">
                        <div><small>BP (Total Biaya)</small><b id="detailBp"></b></div>
                        <div><small>RAB</small><b id="detailRabValue" class="detail-rab-status"></b></div>
                        <div><small>Tanggal Mohon</small><b id="detailTglMohon"></b></div>
                        <div><small>Tanggal Bayar</small><b id="detailTglBayar"></b></div>
                    </div>
                </section>

                @if ($canEditRab)
                    <form id="rabForm" method="POST" class="detail-rab-form">
                        @csrf
                        <div class="detail-rab-heading">
                            <h4>Isi RAB</h4>
                            <p id="rabFormHelp">RAB belum diisi. Masukkan nilainya lalu simpan.</p>
                        </div>
                        <label for="rabInput">Nilai RAB</label>
                        <div class="detail-rab-controls">
                            <div class="detail-rab-input">
                                <span>Rp</span>
                                <input id="rabInput" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB">
                                <input id="rabValue" name="rab" type="hidden">
                            </div>
                            <button type="submit" class="btn"><span aria-hidden="true">✓</span> Simpan RAB</button>
                        </div>
                    </form>
                @endif
            </div>

            <footer class="detail-modal-footer">
                <button type="button" class="detail-modal-dismiss">Tutup</button>
            </footer>
        </div>
    </div>

    <div id="syaratModal" hidden class="fixed inset-0 z-[130] flex items-center justify-center overflow-hidden bg-[#020737]/60 p-2 backdrop-blur-sm [hidden]:!hidden sm:p-6">
        <section class="flex min-h-0 max-h-[calc(100dvh-16px)] w-[calc(100vw-16px)] max-w-[700px] min-w-0 flex-col overflow-hidden rounded-[24px] bg-white text-[#1d2a55] shadow-2xl sm:max-h-[calc(100dvh-48px)] sm:w-full sm:rounded-[28px]" role="dialog" aria-modal="true" aria-labelledby="syaratModalTitle" aria-describedby="syaratModalDescription">
            <div class="relative border-b border-[#e0e7f7] px-4 py-4 sm:flex sm:items-start sm:justify-between sm:gap-5 sm:px-7 sm:py-5">
                <div class="min-w-0 pr-12 sm:pr-0">
                    <h3 id="syaratModalTitle" class="!m-0 !bg-transparent !p-0 !text-xl !font-extrabold !leading-tight !text-[#1236a5] sm:!text-2xl">Berkas Pendukung</h3>
                    <p id="syaratModalDescription" class="mt-2 text-sm leading-5 text-[#5e719e] sm:text-base">Unggah dokumen atau foto pendukung untuk data ini</p>
                </div>
                <button type="button" class="absolute right-4 top-4 flex h-10 w-10 shrink-0 items-center justify-center !rounded-2xl !border-0 !bg-[#eef2ff] !p-0 text-2xl !text-[#344a81] !shadow-none hover:!bg-[#e0e7ff] hover:!shadow-none sm:static sm:h-12 sm:w-12" id="closeSyaratModal" aria-label="Tutup">&times;</button>
            </div>
            <div class="min-h-0 min-w-0 space-y-5 overflow-y-auto overflow-x-hidden p-4 sm:space-y-6 sm:p-7">
                <section>
                    <h4 class="mb-3 text-xs font-extrabold tracking-[.12em] text-[#5e719e]" id="syaratFilesHeading">BERKAS TERUNGGAH</h4>
                    <div id="syaratFiles" class="flex min-w-0 flex-col gap-3" aria-labelledby="syaratFilesHeading" data-csrf-token="{{ csrf_token() }}" data-delete-url-template="{{ url('/pbpd') }}/__ID__/syarat/__JENIS__/__INDEX__"></div>
                </section>
                @if ($canUploadSyarat)
                    <form id="syaratUploadForm" method="POST" action="#" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <h4 class="text-xs font-extrabold tracking-[.12em] text-[#5e719e]">TAMBAH BERKAS</h4>
                        <label for="syaratFileInput" class="flex min-h-52 cursor-pointer flex-col items-center justify-center rounded-[22px] border-2 border-dashed border-[#9aafff] bg-[#f3f5ff] px-3 py-6 text-center transition hover:bg-[#eef2ff] sm:min-h-[280px] sm:rounded-[24px] sm:px-4 sm:py-8">
                            <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[#214fe0] shadow-lg shadow-indigo-100 sm:h-16 sm:w-16">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5M5 16v4h14v-4"/></svg>
                            </span>
                            <strong class="text-base font-extrabold text-[#1236a5] sm:text-lg">Pilih berkas atau foto</strong>
                            <span class="mt-1 text-sm text-[#5e719e] sm:text-base">Anda bisa memilih lebih dari satu file</span>
                            <span class="mt-5 inline-flex max-w-full flex-wrap items-center justify-center gap-2 rounded-2xl border border-[#d5dffc] bg-white px-2 py-2 text-sm text-[#5e719e] sm:gap-3 sm:px-3 sm:py-2.5 sm:text-base">
                                <span class="rounded-xl border-2 border-[#c5d2ff] px-3 py-2 font-bold text-[#1236a5] sm:px-4">Pilih file</span>
                                <span id="syaratFileNames" class="max-w-36 truncate sm:max-w-56">Belum ada file dipilih</span>
                            </span>
                            <input id="syaratFileInput" class="sr-only" type="file" name="berkas[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        </label>
                    </form>
                @endif
            </div>
            <footer class="flex flex-col-reverse gap-2 border-t border-[#e0e7f7] bg-white px-4 py-3 sm:flex-row sm:justify-end sm:gap-3 sm:px-7 sm:py-4">
                <button type="button" id="closeSyaratModalFooter" class="min-h-11 w-full !rounded-xl !border !border-[#c8d5ff] !bg-white px-5 !text-[#1236a5] !shadow-none hover:!bg-[#f2f5ff] hover:!shadow-none sm:w-auto">Tutup</button>
                @if ($canUploadSyarat)
                    <button type="submit" form="syaratUploadForm" class="inline-flex min-h-11 w-full items-center justify-center gap-2 !rounded-xl !border-0 !bg-[#214ee0] px-4 font-bold !text-white !shadow-lg !shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-50 hover:!bg-[#1739b8] hover:!shadow-lg sm:w-auto sm:px-5" id="syaratUploadButton" disabled>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5M5 16v4h14v-4"/></svg>Upload berkas/foto
                    </button>
                @endif
            </footer>
        </section>
    </div>
