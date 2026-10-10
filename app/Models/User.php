<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'institution'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_INTI = 'inti';

    public const ROLE_KOORDINATOR = 'koordinator';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function isInti(): bool
    {
        return $this->role === self::ROLE_INTI;
    }

    public function isKoordinator(): bool
    {
        return $this->role === self::ROLE_KOORDINATOR;
    }

    public function canSeeOrder(Order $order): bool
    {
        if ($this->isInti()) {
            return true;
        }

        return $order->buyer_type === 'siswa' && $order->institution === $this->institution;
    }

    public function getRoleLabelAttribute(): string
    {
        return 'Panitia';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
