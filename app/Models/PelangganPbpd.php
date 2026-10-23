<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelangganPbpd extends Model
{
    public const STATUS_TAMPIL = ['CETAK PK', 'BAYAR', 'PENGESAHAN PDL', 'PDL AWAL'];

    protected $table = 'pelanggan_pbpd';
    protected $guarded = [];

    // Schema hanya memiliki created_at, tidak memiliki updated_at.
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'bp' => 'decimal:2',
            'rab' => 'decimal:2',
            'total_biaya' => 'decimal:2',
            'tgl_mohon' => 'date',
            'tgl_bayar' => 'date',
            'hasil_konstruksi_paths' => 'array',
            'nps_wo_paths' => 'array',
            'nps_checklist_paths' => 'array',
            'hasil_transaksi_paths' => 'array',
            'hasil_jaringan_paths' => 'array',
            'berkas_pendukung_paths' => 'array',
            'berkas_ijin_paths' => 'array',
            'berkas_pendukung_names' => 'array',
            'berkas_ijin_names' => 'array',
            'hasil_konstruksi_at' => 'datetime',
            'nps_wo_at' => 'datetime',
            'nps_checklist_at' => 'datetime',
            'hasil_transaksi_at' => 'datetime',
            'hasil_jaringan_at' => 'datetime',
        ];
    }

    public function ulp()         { return $this->belongsTo(Ulp::class); }
    public function permintaan()  { return $this->hasOne(PermintaanMaterial::class, 'pelanggan_id'); }
    public function pengirimanVendor() { return $this->hasOne(PengirimanVendor::class, 'pelanggan_id'); }
    public function laporanVendor() { return $this->hasOne(LaporanVendor::class, 'pelanggan_id'); }
    public function pengirimanKonstruksi() { return $this->hasOne(PengirimanKonstruksi::class, 'pelanggan_id'); }
    public function perencanaan() { return $this->hasOne(PerencanaanData::class, 'pelanggan_id'); }
    public function konstruksi()  { return $this->hasOne(KonstruksiData::class, 'pelanggan_id'); }
    public function checklist()   { return $this->hasMany(ChecklistVendor::class, 'pelanggan_id'); }
    public function berkas()      { return $this->hasMany(Berkas::class, 'pelanggan_id'); }

    public function scopeStatusTampil($query)
    {
        return $query
            ->whereIn($this->qualifyColumn('status'), self::STATUS_TAMPIL)
            ->where(function ($q) {
                $q->whereNull($this->qualifyColumn('jenis_transaksi'))
                    ->orWhereNotIn($this->qualifyColumn('jenis_transaksi'), ['PS', 'PENERANGAN SEMENTARA']);
            });
    }

    /** Pindah tahap. Pencatatan riwayat belum digunakan karena tabel riwayat belum tersedia. */
    public function pindahTahap(string $ke, ?int $userId = null): void
    {
        $this->update(['tahap' => $ke]);
    }

    /** Menandai selesai setelah seluruh lima proses memiliki berkas lengkap. */
    public function tandaiSelesaiJikaLengkap(?int $userId = null): bool
    {
        $this->loadMissing(['pengirimanKonstruksi', 'laporanVendor']);

        $planningDone = count($this->laporanVendor?->berkas_hasil_paths ?? []) > 0
            || count($this->pengirimanKonstruksi?->hasil_paths ?? []) > 0;
        $constructionDone = count($this->hasil_konstruksi_paths ?? []) > 0
            || count($this->pengirimanKonstruksi?->hasil_konstruksi_paths ?? []) > 0;
        $transactionDone = count($this->hasil_transaksi_paths ?? []) > 0;
        $networkDone = count($this->hasil_jaringan_paths ?? []) > 0;
        $ulpDone = $this->tahap !== 'ULP' || (bool) $this->tujuan_perluasan;

        if (! $ulpDone || ! $planningDone || ! $constructionDone || ! $transactionDone || ! $networkDone) {
            return false;
        }

        if ($this->tahap !== 'SELESAI') {
            $this->pindahTahap('SELESAI', $userId);
        }

        return true;
    }
}
