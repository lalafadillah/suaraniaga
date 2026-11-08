<?php

namespace App\Models;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['seller_id', 'name', 'price', 'stock', 'description', 'audio_description', 'image_url'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * Produk dengan stok kosong tidak boleh dipesan.
     */
    public function tersedia(): bool
    {
        return $this->stock > 0;
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getPriceTextAttribute(): string
    {
        return Rupiah::format($this->price);
    }
}
