<?php

return [
    ['label' => 'Dashboard',         'route' => 'dashboard',         'path' => '/dashboard',   'permission' => 'dashboard.view'],
    ['label' => 'Laporan',           'route' => 'laporan',            'path' => '/laporan',      'permission' => 'laporan.view', 'placeholder' => false],
    ['label' => 'Data PB/PD', 'route' => 'pbpd.index', 'path' => '/pbpd',
    'permission' => 'pbpd.view', 'placeholder' => false],
    ['label' => 'Perluasan JTM',     'route' => 'perluasan.jtm',     'path' => '/perluasan/jtm', 'permission' => 'perluasan.jtm.view'],
    ['label' => 'Perluasan JTR',     'route' => 'perluasan.jtr',     'path' => '/perluasan/jtr', 'permission' => 'perluasan.jtr.view'],
    ['label' => 'Tanpa perluasan',   'route' => 'tanpa.perluasan',   'path' => '/tanpa/perluasan', 'permission' => 'tanpa.perluasan.view'],
    ['label' => 'Upload Data PB/PD', 'route' => 'pbpd.upload', 'path' => '/pbpd-upload',
    'permission' => 'pbpd.upload', 'placeholder' => false],
    ['label' => 'Agenda Vendor Tiang', 'route' => 'vendor.tiang', 'path' => '/vendor/tiang',
    'permission' => 'vendor.tiang.view', 'placeholder' => false],
    ['label' => 'Stok Tiang', 'route' => 'stok.tiang', 'path' => '/stok-tiang',
    'permission' => 'stok.tiang.view', 'placeholder' => false],
    ['label' => 'Riwayat Pengiriman', 'route' => 'vendor.tiang.history', 'path' => '/vendor/tiang/riwayat',
    'permission' => 'vendor.tiang.history', 'placeholder' => false],
    ['label' => 'Agenda Vendor Konstruksi', 'route' => 'vendor.konstruksi', 'path' => '/vendor/konstruksi',
    'permission' => 'vendor.konstruksi.view', 'placeholder' => false],
    ['label' => 'Riwayat Vendor Konstruksi', 'route' => 'vendor.konstruksi.history', 'path' => '/vendor/konstruksi/riwayat',
    'permission' => 'vendor.konstruksi.history', 'placeholder' => false],
    ['label' => 'Pengoperasian',   'route' => 'pengoperasian',   'path' => '/pengoperasian', 'permission' => 'pengoperasian.view'],
    ['label' => 'Pencarian',   'route' => 'pencarian',   'path' => '/pencarian', 'permission' => 'pencarian.view'],
    ['label' => 'Notifikasi',   'route' => 'notifikasi',   'path' => '/notifikasi', 'permission' => 'notifikasi.view', 'placeholder' => false]
];
