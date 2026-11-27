    <form class="box filter" method="GET" action="{{ route('laporan') }}">
        <input type="text" name="no_agenda" value="{{ request('no_agenda') }}" placeholder="Cari nama / no agenda / alamat">

        @if ($showUlpFilter)
            <select name="ulp">
                <option value="">Semua ULP</option>
                @foreach ($ulps as $ulp)
                    <option value="{{ $ulp->id }}" @selected((string) request('ulp') === (string) $ulp->id)>{{ $ulp->nama }}</option>
                @endforeach
            </select>
        @endif

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

        <button type="submit" class="btn !inline-flex !h-11 !w-[70px] !min-h-11 !items-center !justify-center !px-3 !py-0 !leading-normal">Filter</button>
        <a href="{{ route('laporan') }}" class="btn btn-abu !inline-flex !h-11 !w-[70px] !min-h-11 !items-center !justify-center !px-3 !py-0 !leading-normal">Reset</a>
    </form>
