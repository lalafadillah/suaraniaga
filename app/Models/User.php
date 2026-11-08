<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;

#[Fillable(['name', 'email', 'phone', 'address', 'password', 'mode_aksesibilitas'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_BUYER = 'buyer';

    public const ROLE_SELLER = 'seller';

    public const ROLE_ADMIN = 'admin';

    public const ROLES = [self::ROLE_BUYER, self::ROLE_SELLER, self::ROLE_ADMIN];

    protected $attributes = [
        'is_active' => true,
    ];

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
            'is_active' => 'boolean',
            'mode_aksesibilitas' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function isSeller(): bool
    {
        return $this->role === self::ROLE_SELLER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Peran tidak boleh masuk lewat mass assignment bebas.
     *
     * Kolom role sengaja tidak ada di daftar fillable, tetapi seeder dan
     * pendaftaran tetap boleh menetapkan peran. Nilainya dicek di setter,
     * jadi hanya buyer atau seller yang bisa tersimpan.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function fill(array $attributes): static
    {
        $role = $attributes['role'] ?? null;

        unset($attributes['role']);

        parent::fill($attributes);

        if ($role !== null) {
            $this->role = $role;
        }

        return $this;
    }

    public function setRoleAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['role'] = self::ROLE_BUYER;

            return;
        }

        if (! in_array($value, self::ROLES, true)) {
            throw new InvalidArgumentException('Peran tidak dikenal: '.$value);
        }

        $this->attributes['role'] = $value;
    }
}
