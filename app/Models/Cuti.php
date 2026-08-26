<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'tipe_cuti',
        'alasan',
        'status',
        'catatan_admin',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'approved_at' => 'datetime',
        'jumlah_hari' => 'integer',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'approved' => 'bg-[#e6f4ea] text-[#137333] border border-emerald-200',
            'rejected' => 'bg-[#fce8e6] text-[#c5221f] border border-rose-200',
            default    => 'bg-amber-50 text-amber-800 border border-amber-200',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => 'Menunggu Persetujuan',
        };
    }
}
