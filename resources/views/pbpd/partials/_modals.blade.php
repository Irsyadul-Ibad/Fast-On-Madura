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

    <div id="syaratModal" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="syaratModalTitle">
            <button type="button" class="detail-modal-close" id="closeSyaratModal" aria-label="Tutup">&times;</button>
            <h3 id="syaratModalTitle">Berkas Syarat</h3>
            <div id="syaratFiles" class="syarat-files">-</div>
            @if ($canUploadSyarat)
                <form id="syaratUploadForm" method="POST" action="#" enctype="multipart/form-data" class="syarat-upload-form">
                    @csrf
                    <input type="file" name="berkas[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <button type="submit" class="btn">Upload Berkas/Foto</button>
                </form>
            @endif
        </div>
    </div>
