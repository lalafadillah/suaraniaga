<?php

namespace App\Models;

use App\Support\Rupiah;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['buyer_id', 'product_id', 'quantity', 'status', 'total_price', 'recipient_name', 'recipient_phone', 'shipping_address', 'note', 'payment_method'])]
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
     * Label status yang dibaca pengguna, bukan nilai mentah di database.
     */
    public const LABEL_STATUS = [
        self::STATUS_BARU => 'Pesanan baru',
        self::STATUS_DIPROSES => 'Sedang diproses',
        self::STATUS_DIKIRIM => 'Dalam pengiriman',
        self::STATUS_SELESAI => 'Selesai',
    ];

    /**
     * Metode pembayaran simulasi yang bisa dipilih saat checkout.
     */
    public const METODE_PEMBAYARAN = [
        'simulasi' => 'Simulasi pembayaran',
        'transfer' => 'Transfer bank (simulasi)',
        'ditempat' => 'Bayar di tempat (simulasi)',
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
            'quantity' => 'integer',
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
     * Nomor pesanan yang dibaca pengguna, misalnya SN-000012.
     */
    public function getNomorAttribute(): string
    {
        return 'SN-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Label status dalam bahasa pengguna.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? $this->status;
    }

    /**
     * Status berikutnya yang boleh dipilih penjual, atau null kalau sudah selesai.
     */
    public function statusBerikutnya(): ?string
    {
        $index = array_search($this->status, self::URUTAN_STATUS, true);

        if ($index === false) {
            return null;
        }

        return self::URUTAN_STATUS[$index + 1] ?? null;
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
