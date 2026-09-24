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
        'precio_venta_sugerido',
        'precio_costo',
        'exento_iva',
        'activo',
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
            'precio_venta_sugerido' => 'decimal:2',
            'precio_costo' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'tax_included' => 'boolean',
            'exento_iva' => 'boolean',
            'is_heavy' => 'boolean',
            'is_active' => 'boolean',
            'activo' => 'boolean',
            'images' => 'array',
            'compatible_models' => 'array',
            'warranty_days' => 'integer',
            'supplier_id' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if ($product->isDirty('precio_venta_sugerido')) {
                $product->price = $product->precio_venta_sugerido;
            }

            if ($product->isDirty('precio_costo')) {
                $product->cost = $product->precio_costo;
            }

            if ($product->isDirty('activo')) {
                $product->is_active = $product->activo;
            }
        });

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

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class, 'producto_id');
    }

    public function precioConDesglose(?int $sucursalId = null): array
    {
        $precio = (float) $this->precio_venta_sugerido;

        if ($sucursalId !== null) {
            $precioLocal = $this->inventarios()
                ->where('sucursal_id', $sucursalId)
                ->value('precio_venta_local');

            if ($precioLocal !== null) {
                $precio = (float) $precioLocal;
            }
        }

        if ((bool) $this->exento_iva) {
            return [
                'precio_con_iva' => round($precio, 2),
                'precio_sin_iva' => round($precio, 2),
                'iva' => 0.00,
            ];
        }

        $porcentajeIva = (float) ($this->company?->porcentaje_iva ?? 13.00);
        $precioSinIva = $precio / (1 + ($porcentajeIva / 100));

        return [
            'precio_con_iva' => round($precio, 2),
            'precio_sin_iva' => round($precioSinIva, 2),
            'iva' => round($precio - $precioSinIva, 2),
        ];
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
