@extends('layouts.app')

@php
    $deskripsiPerluasan = match ($tujuan) {
        'JTM' => 'Daftar transaksi PB/PD dengan perluasan jaringan tegangan menengah',
        'JTR' => 'Daftar transaksi PB/PD dengan perluasan jaringan tegangan rendah',
        default => 'Daftar transaksi PB/PD yang tidak memerlukan perluasan jaringan',
    };
@endphp
@section('title', $judul)
@section('judul', $judul)
@section('deskripsi', $deskripsiPerluasan)

@section('isi')

    @include('perluasan.partials._summary')
    @include('perluasan.partials._filters')
    @include('perluasan.partials._table')
    @include('perluasan.partials._detail-modal')
    @include('perluasan.partials._styles')
    @include('perluasan.partials._scripts')
@endsection
