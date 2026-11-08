<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['seller_id', 'name', 'price', 'description', 'audio_description', 'image_url'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
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
        return 'Rp'.number_format($this->price, 0, ',', '.');
    }
}
