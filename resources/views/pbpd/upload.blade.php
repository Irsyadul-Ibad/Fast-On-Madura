@extends('layouts.app')

@section('title', 'Upload Data PB/PD')
@section('judul', 'Upload Data PB/PD')

@section('isi')
    @if (session('queued'))
        <div class="box" role="status">{{ session('queued') }}</div>
    @endif

    <form class="box filter" method="POST" action="{{ route('pbpd.upload') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
        <select name="ulp_id">
            <option value="">ULP dari kolom Excel</option>
            @foreach ($ulps as $u)
                <option value="{{ $u->id }}">{{ $u->nama }} (jika kolom ULP kosong)</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Upload</button>
        @error('file') <span style="color:#a61b1b">{{ $message }}</span> @enderror
    </form>

    <div class="box">
        <b>Riwayat upload</b>
        <div class="scroll">
            <table class="tabel">
                <thead>
                    <tr>
                        <th style="background:#0b3d6b">Waktu</th>
                        <th style="background:#0b3d6b">File</th>
                        <th style="background:#0b3d6b">Oleh (user id)</th>
                        <th style="background:#0b3d6b">Status / hasil</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($riwayat as $r)
                    <tr>
                        <td>{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $r->file_name }}</td>
                        <td>{{ $r->uploaded_by }}</td>
                        <td>
                            @if ($r->status === 'queued')
                                <span>Dalam antrean</span>
                            @elseif ($r->status === 'processing')
                                <span>Sedang diproses…</span>
                            @elseif ($r->status === 'completed')
                                <span>Selesai: {{ $r->baru }} baru, {{ $r->diperbarui }} diperbarui, {{ $r->jml_gagal }} dilewati.</span>
                                <a href="{{ route('pbpd.index', ['import' => $r->id]) }}">Lihat data</a>
                                @if ($r->jml_gagal && $r->failed_rows)
                                    <details>
                                        <summary>Detail baris yang dilewati</summary>
                                        <ul style="margin-top:8px;color:#a61b1b">
                                            @foreach ($r->failed_rows as $rowError)
                                                <li>{{ $rowError }}</li>
                                            @endforeach
                                            @if ($r->jml_gagal > count($r->failed_rows))
                                                <li>Menampilkan 100 dari {{ $r->jml_gagal }} baris.</li>
                                            @endif
                                        </ul>
                                    </details>
                                @endif
                            @else
                                <span>Gagal</span>
                                @if ($r->error_message)
                                    <details>
                                        <summary>Lihat penyebab</summary>
                                        <div>{{ $r->error_message }}</div>
                                    </details>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center">Belum ada upload.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($riwayat->contains(fn ($r) => in_array($r->status, ['queued', 'processing'], true)))
        <script>
            window.setTimeout(() => window.location.reload(), 5000);
        </script>
    @endif
@endsection
