<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductoController extends Controller {
	
	 /**
     * @var ProductoService
     */
    protected $productoService;
	
	/**
     * Create a new controller instance.
     *
     * @param ProductoService $productoService
     */
    public function __construct(ProductoService $productoService) {
        $this->productoService = $productoService;
    }
	
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $servicio  = $this->getProductoService();
		return $servicio->getProductos();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(int $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(int $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, int $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id) {
        //
    }
	
    protected function getProductoService(): ProductoService {
        return $this->productoService;
    }
}
