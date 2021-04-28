<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductoController extends Controller
{

    /**
     * @var ProductoService
     */
    protected $productoService;

    /**
     * Create a new controller instance.
     *
     * @param ProductoService $productoService
     */
    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $servicio  = $this->getProductoService();
        return $servicio->getProductos();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $servicio = $this->getProductoService();
        $guardado = $servicio->guardarProducto($request);
        if ($guardado->error()) {
            $errores = $guardado->getMensajesErrorArray();
            return response()->json([
                'code'      => 500,
                'message' => $errores
            ], 500);
        }
        return response()->json([
            'code' => 200,
            'message' => "El producto se ha guardado con éxito"
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $servicio = $this->getProductoService();
        $guardado = $servicio->guardarProducto($request, $id);
        if ($guardado->error()) {
            $errores = $guardado->getMensajesErrorArray();
            return response()->json([
                'code'      => 500,
                'message' => $errores
            ], 500);
        }
        return response()->json([
            'code' => 200,
            'message' => "El producto se ha guardado con éxito"
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $servicio = $this->getProductoService();
        $borrado  = $servicio->borrarProducto($id);
        if ($borrado->error()) {
            $errores = $borrado->getMensajesErrorArray();
            return response()->json([
                'code' => 500,
                'message' => $errores
            ], 500);
        }
        return response()->json([
            'code' => 200,
            'message' => "El producto se ha borrado con éxito"
        ], 200);
    }

    public function categorias(Request $request)
    {
        $servicio = $this->getProductoService();
        return $servicio->getCategorias();
    }

    protected function getProductoService(): ProductoService
    {
        return $this->productoService;
    }
}
