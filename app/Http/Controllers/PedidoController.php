<?php

namespace App\Http\Controllers;

use App\Modelo\Pedido\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $servicio  = $this->getPedidoService();
        $idUsuario = Auth::user()->id;
        $pedido    = $servicio->getPedidoAbierto($idUsuario);
        $success   = true;
        if ($pedido === null) {
            $success = false;
        } else {
            $pedido = $pedido->toArray();
        }
        return response()->json([
            'code'	  => 200,
            'success' => $success,
            'pedido'  => $pedido
        ], 200);
    }

    public function store(Request $request) {
        $servicio  = $this->getPedidoService();
        $idUsuario = Auth::user()->id;
        $pedido    = json_decode($request->getContent(), true);
        $nuevo     =  $servicio->guardarPedidoActivo($pedido, $idUsuario);
        if ($nuevo->error()) {
            $errores = $nuevo->getMensajesErrorArray();
            return Response::json(array(
                'code'    => 500,
                'message' => $errores
            ), 500);
        }
        $pedido = $servicio->getPedidoAbierto($idUsuario);
        return Response::json(array(
            'code'   => 200,
            'pedido' => $pedido
        ), 200);
    }

    public function destroy(Request $request, int $id) {
        if ($id <= 0) {
            return response()->json([
                'message' => 'El pedido a borrar es inválido.',
            ], 500);
        }
        $servicio = $this->getPedidoService();
        $borrado  = $servicio->borrar($id);
        if ($borrado->error()) {
            $errores = $borrado->getMensajesError();
            return Response::json(array(
                'code' => 500,
                'message' => "$errores"
            ), 500);
        }
        $mensaje = $borrado->getMensajes();
        return Response::json(array(
            'code'	  => 200,
            'message' => $mensaje,
            'usuario' => $id
        ), 200);
    }

    public function finalizar($id) {
        if ($id <= 0) {
            return response()->json([
                'message' => 'El pedido a guardar es inválido.',
            ], 500);
        }
        $servicio = $this->getPedidoService();
        $borrado  = $servicio->finalizar($id);
        if ($borrado->error()) {
            $errores = $borrado->getMensajesError();
            return Response::json(array(
                'code' => 500,
                'message' => "$errores"
            ), 500);
        }
        $mensaje = $borrado->getMensajes();
        return Response::json(array(
            'code'	  => 200,
            'message' => $mensaje,
        ), 200);
    }

    protected function getPedidoService(): PedidoService {
        return $this->pedidoService;
    }
}
