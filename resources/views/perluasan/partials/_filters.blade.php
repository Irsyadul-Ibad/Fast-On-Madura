    <form class="box filter perluasan-filter" method="GET">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / no agenda / alamat">
        <select name="ulp">
            <option value="">Semua ULP</option>
            @foreach ($ulps as $ulp)
                <option value="{{ $ulp->id }}" @selected((string) request('ulp') === (string) $ulp->id)>{{ $ulp->nama }}</option>
            @endforeach
        </select>
        <select name="jenis">
            <option value="">Semua jenis transaksi</option>
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
        @if (auth()->user()->role?->type !== 'UP3')
            <select name="tahap">
                <option value="">Semua tahap</option>
                @foreach ($tahapPilihan as $tahapOption => $tahapLabel)
                    <option value="{{ $tahapOption }}" @selected(request('tahap') === $tahapOption)>{{ $tahapLabel }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit" class="btn">Filter</button>
        <a href="{{ url()->current() }}" class="btn btn-abu">Reset</a>
    </form>

