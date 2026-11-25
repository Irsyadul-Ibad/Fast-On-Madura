    <div class="grid statistik">
        <div class="stat-card">
            <small>Total Data</small>
            <strong>{{ number_format($total, 0, ',', '.') }}</strong>
        </div>
        @foreach ($perJenis as $jenis => $jumlah)
            <div class="stat-card">
                <small>{{ $jenis ?: 'LAINNYA' }}</small>
                <strong>{{ number_format($jumlah, 0, ',', '.') }}</strong>
            </div>
        @endforeach
    </div>
