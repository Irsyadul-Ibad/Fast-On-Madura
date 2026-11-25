    <div id="laporanDetailModal" class="laporan-modal" hidden>
        <div class="laporan-modal-box" role="dialog" aria-modal="true" aria-labelledby="laporanModalTitle">
            <header class="laporan-modal-header">
                <div>
                    <h3 id="laporanModalTitle">Detail Data Laporan</h3>
                    <p id="ldSummary"></p>
                </div>
                <button type="button" class="laporan-close" aria-label="Tutup detail laporan">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </header>

            <div class="laporan-modal-content">
                <section class="laporan-modal-section" aria-labelledby="laporanCustomerHeading">
                    <h4 id="laporanCustomerHeading">Informasi Pelanggan</h4>
                    <div class="laporan-detail-grid">
                        <div><small>No Agenda</small><b id="ldNoAgenda"></b></div>
                        <div><small>IDPEL</small><b id="ldIdpel"></b></div>
                        <div><small>Nama Pelanggan</small><b id="ldNama"></b></div>
                        <div><small>Transaksi</small><b id="ldTransaksi" class="laporan-value-pill"></b></div>
                        <div><small>Status</small><b id="ldStatus" class="laporan-value-pill"></b></div>
                        <div><small>Jenis Perluasan</small><b id="ldTujuan" class="laporan-value-pill is-neutral"></b></div>
                        <div class="laporan-full"><small>Alamat</small><b id="ldAlamat"></b></div>
                    </div>
                </section>

                <section class="laporan-modal-section laporan-proses-box" aria-labelledby="laporanProcessHeading">
                    <h4 id="laporanProcessHeading">Proses</h4>
                    <div id="ldProcessRows" class="laporan-process-list"></div>
                </section>

                <section class="laporan-modal-section" aria-labelledby="laporanMaterialHeading">
                    <h4 id="laporanMaterialHeading">Detail Material</h4>
                    <div class="laporan-material-grid">
                        <div><small>RAB</small><b id="ldRab"></b></div>
                        <div><small>Tiang TM</small><b id="ldTiangTm"></b></div>
                        <div><small>Tiang TR</small><b id="ldTiangTr"></b></div>
                        <div><small>Konduktor TM</small><b id="ldKonduktorTm"></b></div>
                        <div><small>Konduktor TR</small><b id="ldKonduktorTr"></b></div>
                        <div><small>Trafo</small><b id="ldTrafo"></b></div>
                        <div><small>KWH Meter</small><b id="ldKwh"></b></div>
                        <div><small>Panel Meter</small><b id="ldPanelMeter"></b></div>
                    </div>
                </section>

                <section class="laporan-modal-section" aria-labelledby="laporanFilesHeading">
                    <h4 id="laporanFilesHeading">Berkas</h4>
                    <div class="laporan-file-groups">
                        <div class="laporan-file-group">
                            <h5>Berkas Pendukung</h5>
                            <div id="ldPendukung" class="laporan-files">-</div>
                        </div>
                        <div class="laporan-file-group">
                            <h5>Berkas Ijin</h5>
                            <div id="ldIjin" class="laporan-files">-</div>
                        </div>
                    </div>
                </section>
            </div>

            <footer class="laporan-modal-footer">
                <button type="button" class="laporan-modal-dismiss">Tutup</button>
            </footer>
        </div>
    </div>
