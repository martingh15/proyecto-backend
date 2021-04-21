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

    /**
     * Crea o edita el pedido activo.
     *
     * @param array $pedido
     * @param int $idUsuario
     * @return Resultado
     */
    public function guardarPedidoActivo(array $pedido, int $idUsuario): Resultado {
        $resultado = new Resultado();
        try {
            DB::beginTransaction();
            $idPedido  = $pedido['id'];
            $nuevo     = new Pedido();
            if (intval($idPedido) > 0) {
                $nuevo = $this->getPedidoAbierto($idUsuario);
            }
            if (intval($nuevo->id) !== intval($idPedido)) {
                \Log::info("ALERTA: Se están duplicando los pedidos");
            }
            $nuevo->usuario_id = $idUsuario;
            $nuevo->fecha      = Carbon::now();
            $nuevo->save();
            $lineasArray = $pedido['lineas'];
            $guardadas   = $this->guardarLineas($nuevo, $lineasArray);
            if ($guardadas->error()) {
                return $guardadas;
            }
            DB::commit();
        } catch(Throwable $exc) {
            DB::rollback();
            $resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al crear el pedido.");
        }
        return $resultado;
    }

    /**
     * Crea o edita las líneas del pedido.
     *
     * @param Pedido $pedido
     * @param array $lineas
     * @return Resultado
     */
    public function guardarLineas(Pedido $pedido, array $lineas): Resultado {
       $total           = 0;
       $resultado       = new Resultado();
       $productoService = $this->getProductoService();
       try {
           $idsNuevos = array_column($lineas, 'id');
           $borradas  = $this->borrarLineas($pedido, $idsNuevos);
           if ($borradas->error()) {
               return $borradas;
           }
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
               $actual = $id > 0 ? $this->getLinea($id, $pedido) : null;
               if ($actual !== null && $cantidad === 0) {
                   $actual->delete();
                   continue;
               }
               $creada = $this->guardarLinea($pedido, $producto, $cantidad, $actual);
               if ($creada->error()) {
                   $resultado->fusionar($creada);
                   continue;
               }
               $total += $cantidad * $precio;
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

    /**
     * Busca una línea de un pedido por id.
     *
     * @param int $id
     * @param Pedido $pedido
     * @return Linea|null
     */
    public function getLinea(int $id, Pedido $pedido): ?Linea {
        return Linea::where([['id', $id], ['pedido_id', $pedido->id]])->first();
    }

    /**
     * Crea o edita la línea de un pedido.
     *
     * @param Pedido $pedido
     * @param Producto $producto
     * @param int $cantidad
     * @param Linea|null $linea
     * @return Resultado
     */
    public function guardarLinea(
        Pedido $pedido,
        Producto $producto,
        int $cantidad,
        Linea $linea = null
    ): Resultado {
        $resultado = new Resultado();
        try {
            if ($linea === null) {
                $linea              = new Linea();
                $linea->pedido_id   = $pedido->id;
                $linea->producto_id = $producto->id;
            }
            $precio             = $producto->precioVigente;
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

    /**
     * Borra las líneas del pedido según los nuevos ids de líneas comparando
     * con los ids viejos
     *
     * @param Pedido $pedido
     * @param array $nuevosIds
     * @return Resultado
     */
    protected function borrarLineas(Pedido $pedido, array $nuevosIds): Resultado {
        $resultado = new Resultado();
        try {
            $viejos = $pedido->getIdsLineas();
            foreach ($viejos as $id) {
                $linea = null;
                if (!in_array($id, $nuevosIds)) {
                    $linea = $this->getLinea($id, $pedido);
                }
                if ($linea !== null) {
                    $linea->delete();
                }
            }
        } catch(Throwable $exc) {
            $resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al guardar el pedido.");
        }
        return $resultado;
    }

    /**
     * Borra un pedido por id.
     *
     * @param int $id
     * @return Resultado
     */
    public function borrar(int $id): Resultado {
        $resultado = new Resultado();
        try {
            DB::beginTransaction();
            $pedido = $this->getPedido($id);
            if ($pedido === null) {
                $resultado->agregarError(Resultado::ERROR_GENERICO, "No se ha encontrado el pedido a borrar.");
            }
            $pedido->estados()->delete();
            $pedido->lineas()->delete();
            $pedido->delete();
            DB::commit();
        } catch (Throwable $exc) {
            DB::rollback();
            $resultado->agregarError(Resultado::ERROR_GENERICO, "Hubo un error al borrar el pedido.");
        }
        return $resultado;
    }

    /**
     * Cerrar un pedido por id.
     *
     * @param int $id
     * @return Resultado
     */
    public function finalizar(int $id): Resultado {
        $resultado = new Resultado();
        try {
            DB::beginTransaction();
            $pedido = $this->getPedido($id);
            if ($pedido === null) {
                $resultado->agregarError(Resultado::ERROR_GENERICO, "No se ha encontrado el pedido a guardar.");
            }
            $pedido->finalizar();
            $pedido->save();
            DB::commit();
            $resultado->agregarMensaje("El local ha recibido su pedido con éxito. Se estima que podrá retirarse en 40 minutos.");
        } catch (Throwable $exc) {
            DB::rollback();
            $resultado->agregarError(Resultado::ERROR_GENERICO, "Hubo un error al guardar el pedido.");
        }
        return $resultado;
    }

    public function getProductoService(): ProductoService {
        return $this->productoService;
    }

}