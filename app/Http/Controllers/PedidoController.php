<?php

namespace App\Http\Controllers;

use App\Modelo\Pedido\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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
            'pedido'  => $pedido->toArray()
        ], 200);
    }

    public function store(Request $request) {
        $servicio = $this->getPedidoService();
        $pedido   = json_decode($request->getContent(), true);
        $nuevo    =  $servicio->guardar($pedido);
        if ($nuevo->error()) {
            $errores = $nuevo->getMensajesErrorArray();
            return Response::json(array(
                'code'    => 500,
                'message' => $errores
            ), 500);
        }
        $pedido = $servicio->getPedidoAbierto();
        return Response::json(array(
            'code'   => 200,
            'pedido' => $pedido
        ), 200);
    }

    protected function getPedidoService(): PedidoService {
        return $this->pedidoService;
    }
}
