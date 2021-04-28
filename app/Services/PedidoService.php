<?php

namespace App\Services;

use App\Modelo\Pedido\Estado;
use App\Modelo\Pedido\Pedido;
use Illuminate\Support\Facades\Log;
use Throwable;

class PedidoService
{

    /**
     * Busca el último pedido abierto
     *
     * @return Pedido|null
     */
    public function getPedidoAbierto(): ?Pedido
    {
        try {
            $pedido = Pedido::where('ultimoEstado', Estado::ABIERTO)->orderBy('fecha', 'DESC')->first();
        } catch (Throwable $exc) {
            Log::info($exc);
            return null;
        }
        return $pedido;
    }
}
