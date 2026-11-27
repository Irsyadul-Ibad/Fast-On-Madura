    <div id="detailModalPerluasan" class="detail-modal" hidden>
        <div class="detail-modal-box" role="dialog" aria-modal="true" aria-labelledby="detailModalPerluasanTitle">
            <header class="detail-modal-header">
                <div>
                    <h3 id="detailModalPerluasanTitle">Detail Data PB/PD</h3>
                    <p id="perluasanSummary" class="detail-modal-summary"></p>
                </div>
                <button type="button" class="detail-modal-close" id="closeDetailPerluasan" aria-label="Tutup detail">&times;</button>
            </header>

            <div class="detail-modal-body">
                <section class="detail-section" aria-labelledby="perluasanCustomerHeading">
                    <h4 id="perluasanCustomerHeading" class="detail-section-title">Informasi Pelanggan</h4>
                    <div class="detail-grid detail-customer-grid">
                        <div><small>No Agenda</small><b id="perluasanNoAgenda"></b></div>
                        <div><small>IDPEL</small><b id="perluasanIdpel"></b></div>
                        <div><small>Nama Pelanggan</small><b id="perluasanNama"></b></div>
                        <div><small>Transaksi</small><b id="perluasanTransaksi" class="detail-value-pill"></b></div>
                        <div><small>Status</small><b id="perluasanStatus" class="detail-value-pill"></b></div>
                        <div><small>Total Biaya</small><b id="perluasanTotalBiaya"></b></div>
                        <div class="detail-full"><small>Alamat</small><b id="perluasanAlamat"></b></div>
                    </div>
                </section>

                <section class="detail-section" aria-labelledby="perluasanCostHeading">
                    <h4 id="perluasanCostHeading" class="detail-section-title">Biaya dan Jadwal</h4>
                    <div class="detail-cost-grid">
                        <div><small>BP (Total Biaya)</small><b id="perluasanBp"></b></div>
                        <div><small>RAB</small><b id="perluasanRabValue" class="detail-rab-status"></b></div>
                        <div><small>Tanggal Mohon</small><b id="perluasanTglMohon"></b></div>
                        <div><small>Tanggal Bayar</small><b id="perluasanTglBayar"></b></div>
                    </div>
                </section>

                <section class="detail-section" aria-labelledby="perluasanProcessHeading">
                    <h4 id="perluasanProcessHeading" class="detail-section-title">Proses</h4>
                    <div class="proses-list">
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Pengiriman ULP</span><span class="proses-status proses-belum" id="prosesUlpStatus">Belum diunggah</span><time id="prosesUlpDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas Kajian Kelayakan</span><span class="proses-status proses-belum" id="prosesPerencanaanStatus">Belum diunggah</span><time id="prosesPerencanaanDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas WO NPS</span><span class="proses-status proses-belum" id="prosesNpsWoStatus">Belum diunggah</span><time id="prosesNpsWoDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas BA Checklist</span><span class="proses-status proses-belum" id="prosesKonstruksiStatus">Belum diunggah</span><time id="prosesKonstruksiDate">-</time></div>
                        <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Foto Cek KWH Meter</span><span class="proses-status proses-belum" id="prosesTransaksiStatus">Belum diunggah</span><time id="prosesTransaksiDate">-</time></div>
                        @if ($tujuan !== 'TANPA_PERLUASAN')
                            <div class="proses-row"><span class="proses-icon" aria-hidden="true">×</span><span class="proses-label">Berkas BA Operasi</span><span class="proses-status proses-belum" id="prosesJaringanStatus">Belum diunggah</span><time id="prosesJaringanDate">-</time></div>
                        @endif
                    </div>
                </section>

                @if ($canViewMaterial || ($canEditRab && $tujuan === 'TANPA_PERLUASAN'))
                <section class="detail-section detail-technical-section" aria-labelledby="perluasanTechnicalHeading">
                    <h4 id="perluasanTechnicalHeading" class="detail-section-title">Data Teknis dan RAB</h4>
                @if ($canViewMaterial)
            @if ($tujuan !== 'TANPA_PERLUASAN')
                <form id="detailFormPerluasan" method="POST">
                    @csrf
                    <div class="detail-edit-table">
                        <label class="detail-field-rab">RAB (Rp) <input id="rabInputPerluasan" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB" @disabled(!$canEditRab)><input id="rabValuePerluasan" name="rab" type="hidden"></label>
                        <label>Jenis Tiang TM<select id="jenisTiangTmPerluasan" name="jenis_tiang_tm" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>11</option><option>13</option></select></label>
                        <label>Jumlah Tiang TM <input id="jmlTiangTmPerluasan" name="jml_tiang_tm" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Tiang TR<select id="jenisTiangTrPerluasan" name="jenis_tiang_tr" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>9</option></select></label>
                        <label>Jumlah Tiang TR<input id="jmlTiangTrPerluasan" name="jml_tiang_tr" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Konduktor TM<select id="jenisKonduktorTmPerluasan" name="jenis_konduktor_tm" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>AAAC-S 240</option><option>AAAC-S 150</option><option>AAAC-S 70</option><option>AAAC 240</option><option>AAAC 150</option><option>AAAC 70</option></select></label>
                        <label>Jumlah Konduktor TM<input id="jmlKonduktorTmPerluasan" name="jml_konduktor_tm" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Konduktor TR<select id="jenisKonduktorTrPerluasan" name="jenis_konduktor_tr" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>LVTC 3x70+1x70</option><option>LVTC 3x70+1x50</option></select></label>
                        <label>Jumlah Konduktor TR<input id="jmlKonduktorTrPerluasan" name="jml_konduktor_tr" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Trafo <select id="jenisTrafoPerluasan" name="jenis_trafo" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>0</option><option>50</option><option>100</option><option>160</option><option>200</option><option>250</option></select></label>
                        <label>Jumlah Trafo <input id="jmlTrafoPerluasan" name="jml_trafo" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis KWH Meter <select id="jenisKwhPerluasan" name="jenis_kwh_meter" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>Prabayar</option><option>Pascabayar</option></select></label>
                        <label>Jumlah KWH Meter <input id="jmlKwhPerluasan" name="jml_kwh_meter" type="number" min="0" @disabled(!$canEditMaterial)></label>
                        <label>Jenis Panel Meter <select id="jenisPanelPerluasan" name="jenis_panel_meter" @disabled(!$canEditMaterial)><option value="">Pilih</option><option>Umum</option><option>53 kva</option><option>66 kva</option><option>105 kva</option><option>164 kva</option><option>197 kva</option></select></label>
                        <label>Jumlah Panel Meter <input id="jmlPanelPerluasan" name="jml_panel_meter" type="number" min="0" @disabled(!$canEditMaterial)></label>
                    </div>
                    @if ($canEditRab)
                        <button type="submit" class="btn">Simpan</button>
                    @endif
                </form>
            @endif

            @if ($canSendVendor)
                <form id="vendorFormPerluasan" method="POST" action="#" enctype="multipart/form-data" class="vendor-form">
                    @csrf
                    <h4>Kirim ke Vendor Tiang</h4>
                    <label>Tujuan kirim ke PT
                        <select id="vendorIdPerluasan" name="vendor_id" required>
                            <option value="">-- Pilih PT --</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    data-stock-tr="{{ $vendor->stokTiang?->stok_tr ?? 0 }}"
                                    data-stock-tm11="{{ $vendor->stokTiang?->stok_tm_11 ?? 0 }}"
                                    data-stock-tm13="{{ $vendor->stokTiang?->stok_tm_13 ?? 0 }}">
                                    {{ $vendor->nama }} — TR: {{ $vendor->stokTiang?->stok_tr ?? 0 }} | TM 11: {{ $vendor->stokTiang?->stok_tm_11 ?? 0 }} | TM 13: {{ $vendor->stokTiang?->stok_tm_13 ?? 0 }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <div id="vendorStockInfoPerluasan" class="vendor-stock-info" hidden>
                        <div class="vendor-stock-heading">
                            <span>Stok vendor terpilih</span>
                            <span id="vendorStockAvailabilityPerluasan" class="vendor-stock-availability"></span>
                        </div>
                        <div class="vendor-stock-values vendor-stock-values-three">
                            <span><b>TR</b><strong id="vendorStockTrPerluasan">0</strong><small> tiang</small></span>
                            <span><b>TM 11</b><strong id="vendorStockTm11Perluasan">0</strong><small> tiang</small></span>
                            <span><b>TM 13</b><strong id="vendorStockTm13Perluasan">0</strong><small> tiang</small></span>
                        </div>
                    </div>
                    <div id="vendorStockWarningPerluasan" class="vendor-stock-warning" hidden></div>
                    <label>Upload berkas WO tiang
                        <input type="file" name="wo_tiang" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </label>
                    <div id="vendorLockedNotice" class="vendor-locked-notice" hidden>Laporan vendor sudah diterima. Pengiriman ini terkunci dan tidak dapat diedit atau dihapus.</div>
                    <button type="submit" class="btn" id="vendorSubmitButton">Kirim ke Vendor</button>
                </form>
                <div id="vendorDeleteCard" class="vendor-delete-card" hidden>
                    <h4>Pengiriman Vendor Tiang</h4>
                    <p>Hapus pengiriman ini jika ingin mengganti atau membatalkan vendor.</p>
                    <form id="vendorDeleteForm" method="POST" action="#" class="vendor-delete-form" onsubmit="return confirm('Hapus pengiriman ke vendor tiang? Berkas laporan vendor terkait juga akan dihapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn">Hapus Pengiriman Vendor Tiang</button>
                    </form>
                </div>
            @endif

            @if ($canSendKonstruksi)
                <form id="constructionForm" method="POST" action="#" enctype="multipart/form-data" class="vendor-form">
                    @csrf
                    <h4>Kirim ke Vendor Konstruksi</h4>
                    <label>Tujuan kirim ke PT
                        <select id="constructionVendorId" name="vendor_id" required>
                            <option value="">-- Pilih PT --</option>
                            @foreach ($vendorsKonstruksi as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->nama }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Upload berkas pengiriman
                        <input type="file" name="berkas[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </label>
                    <div id="constructionLockedNotice" class="vendor-locked-notice" hidden>Laporan vendor konstruksi sudah diterima. Pengiriman ini terkunci dan tidak dapat diedit atau dihapus.</div>
                    <button type="submit" class="btn" id="constructionSubmitButton">Kirim ke Vendor Konstruksi</button>
                </form>
                <div id="constructionDeleteCard" class="vendor-delete-card" hidden>
                    <h4>Pengiriman Vendor Konstruksi (KHS)</h4>
                    <p>Hapus pengiriman ini jika ingin mengganti atau membatalkan vendor.</p>
                    <form id="constructionDeleteForm" method="POST" action="#" class="vendor-delete-form" onsubmit="return confirm('Hapus pengiriman ke vendor konstruksi? Berkas laporan vendor terkait juga akan dihapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn">Hapus Pengiriman Vendor Konstruksi</button>
                    </form>
                </div>
            @endif

            @if ($canSendVendor)
                <div class="vendor-report-result" id="vendorReportResult">
                    <h4>Laporan dari Vendor</h4>
                    <div><span>Foto Pengiriman tiang</span><b id="laporanLengkap">-</b></div>
                    <div><span>Foto penanaman tiang</span><b id="laporanSesuai">-</b></div>
                    <div><span>Foto tiang berdiri</span><b id="laporanFoto">-</b></div>
                    <div><span>Foto pekerjaan selesai</span><b id="laporanSiap">-</b></div>
                    <div><span>Catatan</span><b id="laporanCatatan">-</b></div>
                </div>
            @endif
            @endif
            @if ($canEditRab && $tujuan === 'TANPA_PERLUASAN')
                <form id="tanpaRabForm" method="POST" class="detail-rab-form">
                    @csrf
                    <label for="tanpaRabInput">RAB (Rp)
                        <input id="tanpaRabInput" name="rab_display" type="text" inputmode="numeric" autocomplete="off" placeholder="Masukkan RAB">
                        <input id="tanpaRabValue" name="rab" type="hidden">
                    </label>
                    <button type="submit" class="btn">✓ &nbsp;Simpan RAB</button>
                </form>
            @endif

                </section>
                @endif

                <section class="detail-section detail-files-section" aria-labelledby="perluasanFilesHeading">
                    <h4 id="perluasanFilesHeading" class="detail-section-title">Berkas Pendukung</h4>
                    <div class="detail-file-list">
            <div class="planning-result-box">
                <h4>Berkas Kajian Kelayakan <span class="file-status" data-file-status-for="resultFiles">Belum diunggah</span></h4>
                @if ($canSendVendor)
                    <div class="vendor-kelayakan">
                        <span>Status kelayakan: <strong id="planningEligibilityStatus">Belum ditentukan</strong></span>
                        <form id="planningEligibilityForm" method="POST" action="#">
                            @csrf
                            <button type="submit" name="status_kelayakan" value="LAYAK" class="btn">Layak</button>
                            <button type="submit" name="status_kelayakan" value="TIDAK LAYAK" class="btn btn-abu" onclick="return confirm('Tolak data ini? Semua RAB, material, dan berkas yang sudah diunggah akan dihapus. Data dikembalikan ke PB/PD untuk diproses ulang.')">Tidak Layak</button>
                        </form>
                    </div>
                @endif
                <div id="resultFiles">-</div>
                @if ($canSendVendor)
                    <form id="resultUploadForm" method="POST" action="{{ request()->url() }}" enctype="multipart/form-data" hidden>
                        @csrf
                        <input type="hidden" name="pelanggan_id" id="resultPelangganId">
                        <input type="file" name="berkas_hasil[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas Kelayakan</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box vendor-upload-result-box">
                <h4>Berkas Laporan Vendor <span class="file-status" data-file-status-for="vendorReportFiles">Belum diunggah</span></h4>
                <div id="vendorReportFiles">-</div>
            </div>
            <div class="planning-result-box nps-result-upload-box">
                <h4>Berkas WO NPS <span class="file-status" data-file-status-for="npsWoFiles">Belum diunggah</span></h4>
                <div id="npsWoFiles">-</div>
                @if ($canUploadNps)
                    <form id="npsWoUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_nps_wo[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas WO</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box construction-result-upload-box">
                <h4>Berkas BA Checklist <span class="file-status" data-file-status-for="constructionResultFiles">Belum diunggah</span></h4>
                <div id="constructionResultFiles">-</div>
                @if ($canSendKonstruksi)
                    <form id="constructionResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_konstruksi[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Konstruksi</button>
                    </form>
                @endif
            </div>
            <div class="planning-result-box construction-vendor-report-box">
                <h4>Berkas Laporan Vendor KHS <span class="file-status" data-file-status-for="constructionVendorReportFiles">Belum diunggah</span></h4>
                <div id="constructionVendorReportFiles">-</div>
            </div>
            <div class="planning-result-box transaksi-result-upload-box">
                <h4>Foto Cek KWH Meter <span class="file-status" data-file-status-for="transaksiResultFiles">Belum diunggah</span></h4>
                <div id="transaksiResultFiles">-</div>
                @if ($canUploadTransaksi)
                    <form id="transaksiResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="berkas_hasil_transaksi[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <button type="submit" class="btn">Upload Berkas/Foto Transaksi</button>
                    </form>
                @endif
            </div>
            @if ($tujuan !== 'TANPA_PERLUASAN')
                <div class="planning-result-box jaringan-result-upload-box">
                    <h4>Berkas BA Operasi <span class="file-status" data-file-status-for="jaringanResultFiles">Belum diunggah</span></h4>
                    <div id="jaringanResultFiles">-</div>
                    @if ($canUploadJaringan)
                        <form id="jaringanResultUploadForm" method="POST" action="#" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="berkas_hasil_jaringan[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <button type="submit" class="btn">Upload Berkas/Foto Jaringan</button>
                        </form>
                    @endif
                </div>
            @endif
                    </div>
                </section>
            @if ($canSendKonstruksi)
            <div class="construction-report-box" id="constructionReportBox">
                <h4>Laporan Hasil Pekerjaan Vendor Konstruksi</h4>
                <div class="detail-grid">
                    <div><small>Vendor</small><b id="constructionReportVendor">-</b></div>
                    <div><small>User Vendor</small><b id="constructionReportUser">-</b></div>
                    <div><small>Material Lengkap</small><b id="constructionReportLengkap">-</b></div>
                    <div><small>Pekerjaan Dimulai</small><b id="constructionReportSesuai">-</b></div>
                    <div><small>Pekerjaan Selesai</small><b id="constructionReportFoto">-</b></div>
                    <div><small>Pekerjaan Siap Untuk Dioperasikan</small><b id="constructionReportSiap">-</b></div>
                    <div class="detail-full"><small>Catatan</small><b id="constructionReportCatatan">-</b></div>
                </div>
            </div>
            @endif
            </div>
            <footer class="detail-modal-footer">
                <button type="button" class="detail-modal-dismiss" id="dismissDetailPerluasan">Tutup</button>
            </footer>
        </div>
    </div>

