<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventarios';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'cantidad_disponible',
        'cantidad_minima',
        'precio_venta_local',
    ];

    protected function casts(): array
    {
        return [
            'producto_id' => 'integer',
            'sucursal_id' => 'integer',
            'cantidad_disponible' => 'integer',
            'cantidad_minima' => 'integer',
            'precio_venta_local' => 'decimal:2',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'sucursal_id');
    }
}
