@extends('layouts.app')

@section('title', 'Stok Tiang')
@section('judul', 'Stok Tiang')
@section('deskripsi', 'Kelola stok tiang tiap vendor tiang')

@section('isi')
    @include('stok.partials._content')

    @include('stok.partials._styles')

    @include('stok.partials._scripts')
@endsection
