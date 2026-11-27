    <form class="box filter" method="GET" action="{{ route('pbpd.index') }}">
        <input type="text" name="cari" value="{{ request('cari') }}"
               placeholder="Cari nama / no agenda / alamat">

        @if ($ulps->isNotEmpty())
            <select name="ulp">
                <option value="">Semua ULP</option>
                @foreach ($ulps as $u)
                    <option value="{{ $u->id }}" @selected(request('ulp') == $u->id)>{{ $u->nama }}</option>
                @endforeach
            </select>
        @endif

        <select name="jenis">
            <option value="">Semua jenis transaksi</option>
            <option value="PERUBAHAN DAYA" @selected(request('jenis') == 'PERUBAHAN DAYA')>Perubahan Daya</option>
            <option value="PASANG BARU" @selected(request('jenis') == 'PASANG BARU')>Pasang Baru</option>
        </select>

        <select name="status">
            <option value="">Semua status</option>
            @foreach ($statuss as $s)
                <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
            @endforeach
        </select>

        @if (request('import'))
            <input type="hidden" name="import" value="{{ request('import') }}">
            <span class="pill">File upload #{{ request('import') }}</span>
        @endif

        <button type="submit" class="btn !inline-flex !h-11 !w-[70px] !min-h-11 !items-center !justify-center !px-3 !py-0 !leading-normal">Filter</button>
        <a href="{{ route('pbpd.index') }}" class="btn btn-abu !inline-flex !h-11 !w-[70px] !min-h-11 !items-center !justify-center !px-3 !py-0 !leading-normal">Reset</a>
    </form>
