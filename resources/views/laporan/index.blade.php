@extends('layouts.app')
@php
    $laporanRole = auth()->user()->role;
    $laporanScope = match ($laporanRole?->type) {
        'UP3' => 'dalam cakupan UP3 Madura',
        'ULP' => 'di '.($laporanRole->ulp?->nama ? 'ULP '.$laporanRole->ulp->nama : 'ULP Anda'),
        default => 'dalam cakupan Anda',
    };
@endphp
@section('title', 'Laporan')
@section('judul', 'Laporan')
@section('deskripsi', 'Daftar PB/PD yang sudah masuk proses '.$laporanScope)
@section('header-actions')
    <a class="btn btn-excel" href="{{ route('laporan.export', request()->only(['no_agenda', 'ulp', 'tahap', 'jenis', 'status'])) }}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg>
        Export Excel
    </a>
@endsection
@section('isi')
    @include('laporan.partials._summary')

    @include('laporan.partials._filters')

    @include('laporan.partials._table')

    @include('laporan.partials._detail-modal')

    @include('laporan.partials._scripts')

    @include('laporan.partials._styles')
@endsection
