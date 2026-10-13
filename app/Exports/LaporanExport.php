<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'NO.', 'ASAL ULP', 'NO AGENDA', 'NAMA PELANGGAN', 'IDPEL',
            'ALAMAT', 'TRANSAKSI', 'STATUS', 'JENIS PERLUASAN', 'RAB',
            'JENIS TIANG', 'JUMLAH TIANG', 'JENIS KONDUKTOR TM', 'JUMLAH KONDUKTOR TM',
            'JENIS KONDUKTOR TR', 'JUMLAH KONDUKTOR TR',
            'JENIS TRAFO', 'JUMLAH TRAFO', 'JENIS KWH METER', 'JUMLAH KWH METER',
            'JENIS PANEL', 'JUMLAH PANEL',
            'TOTAL BIAYA', 'TANGGAL MOHON', 'TANGGAL BAYAR',
            'TARIF LAMA', 'DAYA LAMA', 'TARIF BARU', 'DAYA BARU',
            'DURASI HARI KERJA', 'BERKAS PENDUKUNG', 'BERKAS IJIN',
        ];
    }

    public function map($row): array
    {
        $material = $row->permintaan;

        return [
            $row->id,
            $row->asal_ulp,
            $row->no_agenda,
            $row->nama_pelanggan,
            $row->id_pelanggan,
            $row->alamat,
            $row->jenis_transaksi,
            $row->status,
            $row->tujuan_perluasan ?: ($material?->jenis_perluasan ?? '-'),
            $row->rab,
            $material?->jenis_tiang ?? '-',
            $material?->jml_tiang ?? '-',
            $material?->jenis_konduktor_tm ?? '-',
            $material?->jml_konduktor_tm ?? '-',
            $material?->jenis_konduktor_tr ?? '-',
            $material?->jml_konduktor_tr ?? '-',
            $material?->jenis_trafo ?? '-',
            $material?->jml_trafo ?? '-',
            $material?->jenis_kwh_meter ?? '-',
            $material?->jml_kwh_meter ?? '-',
            $material?->jenis_panel_meter ?? '-',
            $material?->jml_panel_meter ?? '-',
            $row->total_biaya,
            $row->tgl_mohon?->format('d/m/Y'),
            $row->tgl_bayar?->format('d/m/Y'),
            $row->tarif_lama,
            $row->daya_lama,
            $row->tarif_baru,
            $row->daya_baru,
            $row->keterangan,
            implode(', ', $row->berkas_pendukung_paths ?? []),
            implode(', ', $row->berkas_ijin_paths ?? []),
        ];
    }
}
