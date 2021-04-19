<?php

namespace App\Services;

use App\Modelo\Pedido\Estado;
use App\Modelo\Pedido\Linea;
use App\Modelo\Pedido\Pedido;
use App\Modelo\Producto\Producto;
use App\Resultado\Resultado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PedidoService  {

    /**
     * @var ProductoService
     */
    protected $productoService;

    /**
     * Create a new service instance.
     *
     * @param ProductoService $productoService
     */
    public function __construct(ProductoService $productoService) {
        $this->productoService = $productoService;
    }

    /**
     * Busca el último pedido abierto
     *
     * @param int $idUsuario
     * @return Pedido|null
     */
    public function getPedidoAbierto(int $idUsuario): ?Pedido {
        $pedido = null;
        try {
            $pedido = Pedido::where([
                ['usuario_id', $idUsuario],
                ['ultimoEstado', Estado::ABIERTO]
            ])->with('lineas')->orderBy('fecha', 'DESC')->first();
        } catch (Throwable $exc) {
            return null;
        }
        return $pedido;
    }

    /**
     * Busca pedido por id
     *
     * @return Pedido|null
     */
    public function getPedido(int $id): ?Pedido {
        try {
            $pedido = Pedido::find($id);
        } catch (Throwable $exc) {
            \Log::info($exc);
            return null;
        }
        return $pedido;
    }

    public function guardar(array $pedido, int $idUsuario): Resultado {
        $resultado = new Resultado();
        try {
            DB::beginTransaction();
            $idPedido  = $pedido['id'];
            $nuevo     = new Pedido();
            if (intval($idPedido) > 0) {
                $nuevo = $this->getPedidoAbierto($idUsuario);
            }
            if (intval($nuevo->id) !== intval($idPedido)) {
                \Log::info("Se están duplicando los pedidos");
            }
            $nuevo->usuario_id = $idUsuario;
            $nuevo->fecha      = Carbon::now();
            $nuevo->save();
            $lineasArray   = $pedido['lineas'];
            $creadas       = $this->crearLineas($nuevo, $lineasArray);
            if ($creadas->error()) {
                return $creadas;
            }
            DB::commit();
        } catch(Throwable $exc) {
            DB::rollback();
            $resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al crear el pedido");
        }
        return $resultado;
    }

    public function crearLineas(Pedido $pedido, array $lineas): Resultado {
       $total           = 0;
       $resultado       = new Resultado();
       $productoService = $this->getProductoService();
       try {
           foreach ($lineas as $linea) {
               $id         = $linea['id'] ?? 0;
               $idProducto = $linea['producto_id'] ?? 0;
               $cantidad   = $linea['cantidad'];
               $producto   = is_numeric($idProducto) ? $productoService->getProducto($idProducto) : null;
               if (empty($producto)) {
                   $resultado->agregarError(Resultado::ERROR_GUARDADO, "No se ha encontrado el producto a agregar.");
                   return $resultado;
               }
               if (!is_numeric($cantidad)) {
                   $resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al agregar el producto.");
                   return $resultado;
               }
               $precio = $producto->precioVigente;
               $actual = $this->getLinea($id, $pedido);
               if ($actual !== null && $cantidad > 0) {
                   $this->actualizarLinea($actual, $cantidad, $precio);
               } else if ($actual !== null && $cantidad === 0) {
                   $actual->delete();
               }
               if ($actual === null) {
                   $creada  = $this->crearLinea($pedido, $producto, $cantidad);
                   $actual  = $creada->getResultado();
               }
               if ($actual !== null && $cantidad > 0) {
                   $total += $cantidad * $precio;
               }
           }
           if ($pedido->lineas()->count() === 0) {
               $pedido->estados()->delete();
               $pedido->delete();
               return $resultado;
           }
           $pedido->total = $total;
           $pedido->save();
       } catch (Throwable $exce) {
           $resultado->agregarError(Resultado::ERROR_GUARDADO, "Ha ocurrido un error al crear el pedido.");
       }
       return $resultado;
    }

    public function getLinea(int $id, Pedido $pedido): ?Linea {
        return Linea::where([['id', $id], ['pedido_id', $pedido->id]])->first();
    }

    public function actualizarLinea(Linea $linea, int $cantidad, float $precio) {
        $linea->cantidad = $cantidad;
        $linea->subtotal = $precio;
        $linea->total    = $precio * $cantidad;
        $linea->save();
    }

    public function crearLinea(Pedido $pedido, Producto $producto, int $cantidad): Resultado {
        $resultado = new Resultado();
        try {
            $linea              = new Linea();
            $precio             = $producto->precioVigente;
            $linea->pedido_id   = $pedido->id;
            $linea->producto_id = $producto->id;
            $linea->cantidad    = $cantidad;
            $linea->subtotal    = $precio;
            $linea->total       = $precio * $cantidad;
            $linea->save();
        } catch (Throwable $exc) {
            $resultado->agregarError(Resultado::ERROR_GUARDADO,"Hubo un error al crear el pedido.");
        }
        $resultado->setResultado($linea);
        return $resultado;
    }

    public function getProductoService(): ProductoService {
        return $this->productoService;
    }

}