<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'menunggu_verifikasi',
        'diproses',
        'siap_diantar',
        'selesai',
        'batal',
    ];

    protected $fillable = [
        'order_code',
        'buyer_type',
        'institution',
        'class_name',
        'full_name',
        'phone',
        'pickup_method',
        'delivery_address',
        'payment_method',
        'payment_proof',
        'subtotal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeInstitutionFilter(Builder $query, ?string $filter): Builder
    {
        if (! $filter || $filter === 'semua') {
            return $query;
        }

        if ($filter === 'Umum') {
            return $query->where('buyer_type', 'umum');
        }

        return $query->where('institution', $filter);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isInti()) {
            return $query;
        }

        return $query
            ->where('buyer_type', 'siswa')
            ->where('institution', $user->institution);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format($this->subtotal, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diproses' => 'Diproses',
            'siap_diantar' => 'Siap Diantar',
            'selesai' => 'Selesai',
            'batal' => 'Batal',
            default => $this->status,
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'online' => 'QRIS',
            'transfer' => 'Transfer Bank',
            'tunai' => 'Tunai / COD',
            default => $this->payment_method,
        };
    }

    public function getPickupMethodLabelAttribute(): string
    {
        return match ($this->pickup_method) {
            'ambil_stand' => 'Ambil di Lokasi Bazar',
            'antar_kelas' => 'Diantar ke Kelas',
            'kirim_alamat' => 'Kirim ke Alamat',
            default => $this->pickup_method,
        };
    }

    public function getBuyerBadgeAttribute(): string
    {
        if ($this->buyer_type === 'siswa') {
            return $this->institution ?? 'Siswa';
        }

        return 'Umum';
    }

    public static function generateCode(): string
    {
        do {
            $code = 'BA-'.now()->format('ymd').'-'.strtoupper(bin2hex(random_bytes(8)));
        } while (static::where('order_code', $code)->exists());

        return $code;
    }
}
