<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
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
     * @param  int  $id
     * @return Response
     */
    public function show(int $id) {

    }

    protected function getPedidoService(): PedidoService {
        return $this->pedidoService;
    }
}
