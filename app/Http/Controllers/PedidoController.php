<?php

namespace App\Http\Controllers;

use App\Modelo\Pedido\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;

class PedidoController extends Controller {

    /**
     * @var PedidoService
     */
    protected $pedidoService;

    /**
     * Create a new controller instance.
     *
     * @param PedidoService $pedidoService
     */
    public function __construct(PedidoService $pedidoService) {
        $this->pedidoService = $pedidoService;
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse|Pedido
     */
    public function index() {
        $servicio = $this->getPedidoService();
        $pedido   =  $servicio->getPedidoAbierto();
        $success  = true;
        if ($pedido === null) {
            $success = false;
        }
        return response()->json([
            'code'	  => 200,
            'success' => $success,
            'pedido'  => $pedido
        ], 200);;
    }

    protected function getPedidoService(): PedidoService {
        return $this->pedidoService;
    }
}
