<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Inventario;
use App\Models\KardexMovimiento;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    public function registrarEntrada(
        Product $producto,
        Branch $sucursal,
        int $cantidad,
        ?string $referencia = null,
        ?User $usuario = null,
        ?string $observacion = null,
        string $tipo = KardexMovimiento::TIPO_ENTRADA,
    ): Inventario {
        $this->validarCantidadPositiva($cantidad);
        $this->validarProductoYSucursal($producto, $sucursal);

        return DB::transaction(function () use ($producto, $sucursal, $cantidad, $referencia, $usuario, $observacion, $tipo): Inventario {
            $inventario = $this->obtenerInventarioBloqueado($producto, $sucursal);
            $inventario->increment('cantidad_disponible', $cantidad);

            $this->registrarMovimiento($producto, $sucursal, $tipo, $cantidad, $referencia, $usuario, $observacion);

            return $inventario->refresh();
        });
    }

    public function registrarCargaInicial(
        Product $producto,
        Branch $sucursal,
        int $cantidad,
        ?User $usuario = null,
        ?string $observacion = null,
    ): Inventario {
        return $this->registrarEntrada(
            $producto,
            $sucursal,
            $cantidad,
            'carga-inicial-'.$producto->id,
            $usuario,
            $observacion,
            KardexMovimiento::TIPO_CARGA_INICIAL,
        );
    }

    public function registrarSalida(
        Product $producto,
        Branch $sucursal,
        int $cantidad,
        ?string $referencia = null,
        ?User $usuario = null,
        ?string $observacion = null,
        string $tipo = KardexMovimiento::TIPO_SALIDA,
    ): Inventario {
        $this->validarCantidadPositiva($cantidad);
        $this->validarProductoYSucursal($producto, $sucursal);

        return DB::transaction(function () use ($producto, $sucursal, $cantidad, $referencia, $usuario, $observacion, $tipo): Inventario {
            $inventario = $this->obtenerInventarioBloqueado($producto, $sucursal);

            if ($inventario->cantidad_disponible < $cantidad) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La sucursal no tiene stock suficiente para esta salida.',
                ]);
            }

            $inventario->decrement('cantidad_disponible', $cantidad);
            $this->registrarMovimiento($producto, $sucursal, $tipo, $cantidad, $referencia, $usuario, $observacion);

            return $inventario->refresh();
        });
    }

    public function registrarTraslado(
        Product $producto,
        Branch $sucursalOrigen,
        Branch $sucursalDestino,
        int $cantidad,
        ?User $usuario = null,
    ): void {
        $this->validarCantidadPositiva($cantidad);
        $this->validarProductoYSucursal($producto, $sucursalOrigen);
        $this->validarProductoYSucursal($producto, $sucursalDestino);

        if ($sucursalOrigen->is($sucursalDestino)) {
            throw ValidationException::withMessages([
                'sucursal_destino' => 'La sucursal de origen y destino deben ser diferentes.',
            ]);
        }

        DB::transaction(function () use ($producto, $sucursalOrigen, $sucursalDestino, $cantidad, $usuario): void {
            $referencia = 'traslado-'.now()->format('YmdHis').'-'.str()->random(6);
            $this->registrarSalida(
                $producto,
                $sucursalOrigen,
                $cantidad,
                $referencia,
                $usuario,
                'Traslado hacia '.$sucursalDestino->name,
                KardexMovimiento::TIPO_TRASLADO_SALIDA,
            );
            $this->registrarEntrada(
                $producto,
                $sucursalDestino,
                $cantidad,
                $referencia,
                $usuario,
                'Traslado desde '.$sucursalOrigen->name,
                KardexMovimiento::TIPO_TRASLADO_ENTRADA,
            );

        });
    }

    public function registrarAjuste(
        Product $producto,
        Branch $sucursal,
        int $nuevaCantidad,
        User $usuario,
        string $observacion,
    ): Inventario {
        if ($nuevaCantidad < 0) {
            throw ValidationException::withMessages(['cantidad' => 'La cantidad física no puede ser negativa.']);
        }

        if (blank($observacion)) {
            throw ValidationException::withMessages(['observacion' => 'La observación del ajuste es obligatoria.']);
        }

        $this->validarProductoYSucursal($producto, $sucursal);

        return DB::transaction(function () use ($producto, $sucursal, $nuevaCantidad, $usuario, $observacion): Inventario {
            $inventario = $this->obtenerInventarioBloqueado($producto, $sucursal);
            $diferencia = $nuevaCantidad - $inventario->cantidad_disponible;

            if ($diferencia === 0) {
                return $inventario;
            }

            $inventario->update(['cantidad_disponible' => $nuevaCantidad]);
            $this->registrarMovimiento(
                $producto,
                $sucursal,
                KardexMovimiento::TIPO_AJUSTE,
                abs($diferencia),
                null,
                $usuario,
                ($diferencia > 0 ? 'Aumento: ' : 'Disminución: ').$observacion,
            );

            return $inventario->refresh();
        });
    }

    private function obtenerInventarioBloqueado(Product $producto, Branch $sucursal): Inventario
    {
        $inventario = Inventario::query()->firstOrCreate([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
        ], [
            'cantidad_disponible' => 0,
            'cantidad_minima' => 0,
        ]);

        return Inventario::query()->whereKey($inventario->id)->lockForUpdate()->firstOrFail();
    }

    private function registrarMovimiento(
        Product $producto,
        Branch $sucursal,
        string $tipo,
        int $cantidad,
        ?string $referencia,
        ?User $usuario,
        ?string $observacion,
    ): KardexMovimiento {
        return KardexMovimiento::query()->create([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'referencia' => $referencia,
            'usuario_id' => $usuario?->id ?? auth()->id(),
            'observacion' => $observacion,
        ]);
    }

    private function validarCantidadPositiva(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw ValidationException::withMessages(['cantidad' => 'La cantidad debe ser mayor que cero.']);
        }
    }

    private function validarProductoYSucursal(Product $producto, Branch $sucursal): void
    {
        if ($producto->company_id !== $sucursal->company_id) {
            throw ValidationException::withMessages(['sucursal' => 'El producto y la sucursal deben pertenecer a la misma empresa.']);
        }
    }
}
