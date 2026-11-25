@extends('layouts.app')

@php
    $unitCakupanPbpd = match (auth()->user()->role?->type) {
        'ULP' => auth()->user()->role?->ulp?->nama ?? 'ULP Anda',
        'UP3' => 'seluruh ULP',
        default => 'Anda',
    };
@endphp
@section('title', 'Data PB/PD')
@section('judul', 'Data PB/PD')
@section('deskripsi', 'Daftar transaksi PB/PD dalam cakupan '.$unitCakupanPbpd)
@section('header-actions')
    @if ($canKirim)
        <button type="button" class="btn table-send-button" id="openSendModal" disabled>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>
            Kirim data terpilih (<span id="selectedPelangganCount">0</span>)
        </button>
    @endif
@endsection

@section('isi')
    @if (session('success'))
        <div class="box" style="color:#176b35">{{ session('success') }}</div>
    @endif
    @include('pbpd.partials._summary')

    @include('pbpd.partials._filters')

    @include('pbpd.partials._table')

    @include('pbpd.partials._modals')

    @include('pbpd.partials._styles')

    @include('pbpd.partials._scripts')
    @include('pbpd.partials._final-styles')
@endsection
