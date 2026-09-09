<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPE_ENTRY = 'entry';

    public const TYPE_SALE = 'sale';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'reason',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $movement): void {
            $stock = ProductStock::query()
                ->where('product_id', $movement->product_id)
                ->lockForUpdate()
                ->first();

            if ($stock === null) {
                throw ValidationException::withMessages([
                    'product_id' => 'El producto no tiene una existencia inicial configurada.',
                ]);
            }

            $quantityAfter = $stock->quantity + $movement->quantity;

            if ($quantityAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'La operación excede la existencia disponible.',
                ]);
            }

            $movement->quantity_before = $stock->quantity;
            $movement->quantity_after = $quantityAfter;
            $movement->user_id ??= auth()->id();

            $stock->update(['quantity' => $quantityAfter]);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
