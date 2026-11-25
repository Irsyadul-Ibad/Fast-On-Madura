@extends('layouts.app')

@php
    $notificationRole = auth()->user()->role;
    $notificationScope = match ($notificationRole?->type) {
        'ULP' => 'ULP '.($notificationRole->ulp?->nama ?? 'Anda'),
        'UP3' => 'UP3 Madura',
        default => 'wilayah kerja Anda',
    };
    $notificationGroups = $notifications->getCollection()->groupBy(
        fn ($notification) => $notification->created_at?->toDateString() ?? 'unknown'
    );
@endphp

@section('title', 'Notifikasi')
@section('judul', 'Notifikasi')
@section('deskripsi', 'Pembaruan proses PB/PD di '.$notificationScope)
@section('header-actions')
    <form method="POST" action="{{ route('notifikasi.read-all') }}" class="notification-mark-all-form">
        @csrf
        <button type="submit" class="notification-mark-all">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 12 5 5L20 5"/><path d="m10 12 5 5L24 8" transform="translate(-2 -1)"/></svg>
            Tandai semua dibaca
        </button>
    </form>
@endsection

@section('isi')
    @include('notifikasi.partials._content')
@endsection

@include('notifikasi.partials._styles')
