<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Product extends Model
{
    public const TYPE_MENU = 'menu';

    public const TYPE_INFAK = 'infak';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'bazar_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBazar(Builder $query, string $type): Builder
    {
        return $query->where('bazar_type', $type);
    }

    public static function menuOrderingOpen(): bool
    {
        $deadline = Carbon::parse(config('bazar.menu_order_deadline'), 'Asia/Jakarta')->endOfDay();

        return now()->timezone('Asia/Jakarta')->lte($deadline);
    }

    public static function menuDeadlineLabel(): string
    {
        return Carbon::parse(config('bazar.menu_order_deadline'), 'Asia/Jakarta')
            ->locale('id')
            ->isoFormat('D MMMM Y');
    }

    public function isInfak(): bool
    {
        return $this->bazar_type === self::TYPE_INFAK;
    }

    public function orderingClosed(): bool
    {
        return ! $this->isInfak() && ! static::menuOrderingOpen();
    }

    public function getBazarLabelAttribute(): string
    {
        return $this->isInfak() ? 'Infak & Sedekah' : 'Menu Bazar';
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        return 'https://placehold.co/600x600/e8f5e9/1b5e20?text='.urlencode($this->name);
    }
}
