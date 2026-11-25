    @php
        $totalTr = $vendors->sum(fn ($vendor) => (int) ($vendor->stokTiang?->stok_tr ?? 0));
        $totalTm11 = $vendors->sum(fn ($vendor) => (int) ($vendor->stokTiang?->stok_tm_11 ?? 0));
        $totalTm13 = $vendors->sum(fn ($vendor) => (int) ($vendor->stokTiang?->stok_tm_13 ?? 0));
    @endphp

    @if (session('success'))
        <div class="stok-alert stok-alert-success" role="status">
            <span class="stok-alert-icon" aria-hidden="true">✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="stok-alert stok-alert-error" role="alert">
            <strong>Input belum dapat disimpan.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="stokTiangPage" class="stok-page">

    <div class="stok-summary-grid">
        <div class="stok-summary-card stok-summary-vendors">
            <span class="stok-summary-label">VENDOR TIANG</span>
            <strong>{{ $vendors->count() }}</strong>
            <small>vendor terdaftar</small>
        </div>
        <div class="stok-summary-card stok-summary-tr">
            <span class="stok-summary-label">TOTAL STOK TR</span>
            <strong id="totalStokTr">{{ number_format($totalTr, 0, ',', '.') }}</strong>
            <small>tiang tegangan rendah</small>
        </div>
        <div class="stok-summary-card stok-summary-tm stok-summary-tm-11">
            <span class="stok-summary-label">TOTAL STOK TM 11</span>
            <strong id="totalStokTm11">{{ number_format($totalTm11, 0, ',', '.') }}</strong>
            <small>tiang TM jenis 11</small>
        </div>
        <div class="stok-summary-card stok-summary-tm stok-summary-tm-13">
            <span class="stok-summary-label">TOTAL STOK TM 13</span>
            <strong id="totalStokTm13">{{ number_format($totalTm13, 0, ',', '.') }}</strong>
            <small>tiang TM jenis 13</small>
        </div>
    </div>

    <form class="box stok-form" method="POST" action="{{ route('stok.tiang.update') }}">
        @csrf
        <div class="stok-form-heading">
            <div>
                <h2>Daftar Stok per Vendor</h2>
                <p>Masukkan jumlah stok terbaru pada kolom yang sesuai, lalu simpan perubahan.</p>
            </div>
            <span class="pill">{{ $vendors->count() }} vendor</span>
        </div>

        @if ($vendors->isEmpty())
            <div class="stok-empty">Belum ada vendor tiang yang terdaftar.</div>
        @else
            <div class="scroll stok-table-scroll">
                <table class="tabel stok-table">
                    <thead>
                        <tr>
                            <th class="stok-number">NO.</th>
                            <th>NAMA VENDOR TIANG</th>
                            <th class="stok-column stok-column-tr">STOK TIANG TR</th>
                            <th class="stok-column stok-column-tm">STOK TIANG TM 11</th>
                            <th class="stok-column stok-column-tm stok-column-tm-13">STOK TIANG TM 13</th>
                            <th class="stok-total-column">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $index => $vendor)
                            @php
                                $stokTr = old("stok.{$vendor->id}.tr", $vendor->stokTiang?->stok_tr ?? 0);
                                $stokTm11 = old("stok.{$vendor->id}.tm_11", $vendor->stokTiang?->stok_tm_11 ?? 0);
                                $stokTm13 = old("stok.{$vendor->id}.tm_13", $vendor->stokTiang?->stok_tm_13 ?? 0);
                            @endphp
                            <tr>
                                <td class="stok-number">{{ $index + 1 }}</td>
                                <td>
                                    <div class="stok-vendor-name">
                                        <span class="stok-vendor-mark" aria-hidden="true">{{ strtoupper(substr($vendor->nama, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $vendor->nama }}</strong>
                                            <small>Vendor tiang</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="stok-input-cell stok-input-tr">
                                    <label class="sr-only" for="stok-{{ $vendor->id }}-tr">Stok tiang TR {{ $vendor->nama }}</label>
                                    <input id="stok-{{ $vendor->id }}-tr" class="stok-input stok-tr-input" type="number" name="stok[{{ $vendor->id }}][tr]" min="0" step="1" value="{{ $stokTr }}" inputmode="numeric" required>
                                    <span>tiang</span>
                                </td>
                                <td class="stok-input-cell stok-input-tm">
                                    <label class="sr-only" for="stok-{{ $vendor->id }}-tm-11">Stok tiang TM jenis 11 {{ $vendor->nama }}</label>
                                    <input id="stok-{{ $vendor->id }}-tm-11" class="stok-input stok-tm-11-input" type="number" name="stok[{{ $vendor->id }}][tm_11]" min="0" step="1" value="{{ $stokTm11 }}" inputmode="numeric" required>
                                    <span>tiang</span>
                                </td>
                                <td class="stok-input-cell stok-input-tm stok-input-tm-13">
                                    <label class="sr-only" for="stok-{{ $vendor->id }}-tm-13">Stok tiang TM jenis 13 {{ $vendor->nama }}</label>
                                    <input id="stok-{{ $vendor->id }}-tm-13" class="stok-input stok-tm-13-input" type="number" name="stok[{{ $vendor->id }}][tm_13]" min="0" step="1" value="{{ $stokTm13 }}" inputmode="numeric" required>
                                    <span>tiang</span>
                                </td>
                                <td class="stok-row-total"><strong>{{ (int) $stokTr + (int) $stokTm11 + (int) $stokTm13 }}</strong> tiang</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="stok-form-footer">
                <p><span class="stok-info-icon">i</span> Perubahan stok berlaku untuk vendor yang dipilih setelah disimpan.</p>
                <button type="submit" class="btn stok-save-button">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    Simpan stok
                </button>
            </div>
        @endif
    </form>
    </div>

