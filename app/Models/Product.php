<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'sku',
        'barcode',
        'name',
        'brand',
        'description',
        'images',
        'cost',
        'price',
        'sale_price',
        'regular_price',
        'currency',
        'tax_included',
        'weight_kg',
        'status',
        'warranty_days',
        'supplier_id',
        'part_number',
        'compatible_models',
        'min_stock',
        'is_heavy',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'tax_included' => 'boolean',
            'is_heavy' => 'boolean',
            'is_active' => 'boolean',
            'images' => 'array',
            'compatible_models' => 'array',
            'warranty_days' => 'integer',
            'supplier_id' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            if ($product->barcode === null) {
                $product->barcode = sprintf('RERP-%05d-%08d', $product->company_id, $product->id);
            }

            $product->is_heavy = $product->is_heavy ?? (float) ($product->weight_kg ?? 0) >= 10 || str_contains(strtolower((string) $product->name), 'transmision');
        });

        static::created(function (self $product): void {
            if ($product->barcode === null) {
                $product->updateQuietly([
                    'barcode' => sprintf('RERP-%05d-%08d', $product->company_id, $product->id),
                ]);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function imagesRelation(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
