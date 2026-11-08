<?php

namespace App\Models;

use App\Support\Rupiah;
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

    /**
     * Urutan status pesanan. Penjual hanya boleh menaikkan status, tidak
     * boleh mengembalikannya ke langkah sebelumnya.
     */
    public const URUTAN_STATUS = [
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
        return Rupiah::format($this->total_price);
    }

    /**
     * Status yang sama atau lebih maju dari status sekarang diperbolehkan.
     */
    public function bisaMajuKe(string $status): bool
    {
        $sekarang = array_search($this->status, self::URUTAN_STATUS, true);
        $target = array_search($status, self::URUTAN_STATUS, true);

        if ($sekarang === false || $target === false) {
            return false;
        }

        return $target >= $sekarang;
    }
}
