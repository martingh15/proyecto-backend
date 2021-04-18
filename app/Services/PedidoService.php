<?php

namespace App\Services;

use App\Modelo\Pedido\Estado;
use App\Modelo\Pedido\Linea;
use App\Modelo\Pedido\Pedido;
use App\Modelo\Producto\Producto;
use App\Resultado\Resultado;
use Carbon\Carbon;
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
     * @return Pedido|null
     */
    public function getPedidoAbierto(): ?Pedido {
        try {
            $pedido = Pedido::where('ultimoEstado', Estado::ABIERTO)->with('lineas')->orderBy('fecha', 'DESC')->first();
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

    public function guardar(array $pedido): Resultado {
        $resultado = new Resultado();
        try {
            DB::beginTransaction();
            $idPedido  = $pedido['id'];
            $nuevo     = new Pedido();
            if (intval($idPedido) > 0) {
                $nuevo = $this->getPedido($idPedido);
            }
            $nuevo->fecha  = Carbon::now();
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
               $idProducto = $linea['producto'] ?? 0;
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
               $creada   = $this->crearLinea($pedido, $producto, $cantidad);
               $linea    = $creada->getResultado();
               $subtotal = $linea->total;
               $total    += $subtotal;
           }
           $pedido->total = $total;
           $pedido->save();
       } catch (Throwable $exce) {
           $resultado->agregarError(Resultado::ERROR_GUARDADO, "Ha ocurrido un error al crear el pedido.");
       }
       return $resultado;
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