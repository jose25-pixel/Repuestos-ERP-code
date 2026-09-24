<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KardexMovimiento extends Model
{
    use HasFactory;

    protected $table = 'kardex_movimientos';

    public const TIPO_ENTRADA = 'entrada';
    public const TIPO_SALIDA = 'salida';
    public const TIPO_AJUSTE = 'ajuste';
    public const TIPO_TRASLADO_SALIDA = 'traslado_salida';
    public const TIPO_TRASLADO_ENTRADA = 'traslado_entrada';
    public const TIPO_CARGA_INICIAL = 'carga_inicial';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'tipo',
        'cantidad',
        'referencia',
        'usuario_id',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'producto_id' => 'integer',
            'sucursal_id' => 'integer',
            'cantidad' => 'integer',
            'usuario_id' => 'integer',
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
