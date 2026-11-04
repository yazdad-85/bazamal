<?php

namespace App\Models;

use Database\Factories\DeliveryVillageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryVillage extends Model
{
    /** @use HasFactory<DeliveryVillageFactory> */
    use HasFactory;

    public const FREE_DELIVERY_DISTRICTS = ['Manyar', 'Gresik', 'Bungah'];

    protected $fillable = [
        'code',
        'district',
        'name',
        'postal_code',
        'latitude',
        'longitude',
        'distance_km',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'distance_km' => 'decimal:2',
        ];
    }

    public function hasFreeDelivery(): bool
    {
        return in_array($this->district, self::FREE_DELIVERY_DISTRICTS, true);
    }

    public function getAreaLabelAttribute(): string
    {
        return $this->name.', Kec. '.$this->district;
    }
}
