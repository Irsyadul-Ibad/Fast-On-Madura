    <div class="box laporan-filter-box">
        <form class="filter" method="GET">
            <input type="text" name="no_agenda" value="{{ request('no_agenda') }}" placeholder="No agenda">
            @if ($showUlpFilter)
                <select name="ulp">
                    <option value="">Semua ULP</option>
                    @foreach ($ulps as $ulp)
                        <option value="{{ $ulp->id }}" @selected((string) request('ulp') === (string) $ulp->id)>
                            {{ $ulp->nama }}
                        </option>
                    @endforeach
                </select>
            @endif
            @if (in_array(auth()->user()->role?->type, ['ULP', 'UP3'], true))
                <select name="tahap">
                    <option value="">Semua tahap</option>
                    @foreach ($tahapPilihan as $tahap => $label)
                        <option value="{{ $tahap }}" @selected(request('tahap') === $tahap)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            @endif
            <select name="jenis">
                <option value="">Semua transaksi</option>
                @foreach ($jenisPilihan as $jenis)
                    <option value="{{ $jenis }}" @selected(request('jenis') === $jenis)>{{ $jenis }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">Semua status</option>
                @foreach ($statusPilihan as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Filter</button>
        </form>
    </div>
