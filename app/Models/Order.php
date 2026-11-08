<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['buyer_id', 'product_id', 'status', 'total_price'])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_BARU = 'baru';

    public const STATUS_DIPROSES = 'diproses';

    public const STATUS_DIKIRIM = 'dikirim';

    public const STATUS_SELESAI = 'selesai';

    public const STATUSES = [
        self::STATUS_BARU,
        self::STATUS_DIPROSES,
        self::STATUS_DIKIRIM,
        self::STATUS_SELESAI,
    ];

    protected $attributes = [
        'status' => self::STATUS_BARU,
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
            'status' => 'string',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalTextAttribute(): string
    {
        return 'Rp'.number_format($this->total_price, 0, ',', '.');
    }
}
