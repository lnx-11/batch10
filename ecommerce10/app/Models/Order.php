<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_number',
        'user_id',
        'product_id',
        'quantity',
        'total',
        'status',
        'payment_status',
        'payment_verified_at',
        'payment_verified_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total' => 'integer',
            'payment_verified_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }
}
