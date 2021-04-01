<?php

namespace App\Resultado;

use Exception;
use Throwable;

/**
 * Representa el resultado de una operación. Ideal para procesos que deben
 * validar varias condiciones y pueden mostrar más de un mensaje de error.
 *
 * Se puede usar tanto para servicios como en el modelo.
 *
 * @author Alejandro Fiore
 */
class Resultado {

	/**
	 * Ocurrió un error que no requiere codificación específica.
	 */
	const ERROR_GENERICO = 'generico';

	/**
	 * Ocurrió un error al intentar persistir en base de datos.
	 */
	const ERROR_GUARDADO = 'guardado';

	/**
	 * El usuario no posee autorización para la operación.
	 */
	const ERROR_NO_AUTORIZADO = 'no-autorizado';

	/**
	 * No se ha encontrado el recurso u objeto indicado.
	 */
	const ERROR_NO_ENCONTRADO = 'no-encontrado';

	protected $resultado;
	protected $mensajes	   = [];
	protected $errores	   = [];
	protected $excepciones = [];

	/**
	 * Agrega un error a la lista de errores.
	 *
	 * Se recomienda utilizar constantes en lugar de mensajes, de manera que
	 * podamos mostrar diferentes mensajes dependiendo del contexto. Entonces
	 * un controlador podría ver cierto código y mostrar un mensaje diferente
	 * al que mostraría otro controlador bajo otro contexto.
	 *
	 * @param string $codigo
	 * @param string $mensaje
	 * @return Resultado
	 */
	public function agregarError(string $codigo, string $mensaje): Resultado {
		$this->errores[] = new Error($codigo, $mensaje);
		return $this;
	}

	/**
	 * Agrega un mensaje al resultado. A diferencia de los errores, los mensajes
	 * no generan un resultado con error.
	 *
	 * El objetivo del mensaje es mostrar información acerca del proceso.
	 *
	 * @param string $mensaje
	 * @return Resultado
	 */
	public function agregarMensaje(string $mensaje): Resultado {
		$this->mensajes[] = $mensaje;
		return $this;
	}

	/**
	 * Agrega los mensajes al resultado. A diferencia de los errores, los
	 * mensajes no generan un resultado con error.
	 *
	 * El objetivo del mensaje es mostrar información acerca del proceso.
	 *
	 * @param array $mensajes
	 * @return Resultado
	 */
	public function agregarMensajes(array $mensajes): Resultado {
		$this->mensajes = array_merge($this->mensajes, $mensajes);
		return $this;
	}

	/**
	 * Agrega una excepción al resultado. Útil cuando ocurre un error y debemos
	 * proceder según la excepción o throwable lanzada.
	 *
	 * @param string $excepcion
	 * @return Resultado
	 */
	public function agregarExcepcion($excepcion): Resultado {
		$this->excepciones[] = $excepcion;
		return $this;
	}

	/**
	 * Agrega las excepciones al resultado. Útil cuando ocurre un error y
	 * debemos proceder según la excepción o throwable lanzada.
	 *
	 * @param array $excepciones
	 * @return Resultado
	 */
	public function agregarExcepciones(array $excepciones): Resultado {
		$this->mensajes = array_merge($this->excepciones, $excepciones);
		return $this;
	}

	/**
	 * Fusiona al resultado actual los errores y mensajes (si tiene) del
	 * resultado dado en el parámetro.
	 * 
	 * Ideal para procesos complejos cuyo métodos devuelven sus propios 
	 * resultados pero que deben al final devolver un solo resultado final.
	 * 
	 * @param Resultado $resultado
	 * @return Resultado
	 */
	public function fusionar(Resultado $resultado): Resultado {
		$errores	 = $resultado->getErrores();
		$mensajes	 = $resultado->getMensajesArray();
		$excepciones = $resultado->getMensajesArray();

		$this->errores     = array_merge($this->errores, $errores);
		$this->mensajes	   = array_merge($this->mensajes, $mensajes);
		$this->excepciones = array_merge($this->excepciones, $excepciones);

		return $this;
	}

	/**
	 * True si el proceso se ejecutó con éxito.
	 *
	 * @return bool
	 */
	public function exito(): bool {
		return empty($this->errores);
	}

	/**
	 * True si el proceso contiene al menos un error.
	 *
	 * @return bool
	 */
	public function error(): bool {
		return !empty($this->errores);
	}

	/**
	 * El resultado de la operación.
	 *
	 * @return mixed|null
	 */
	public function getResultado() {
		return $this->resultado;
	}

	/**
	 * Devuelve un array con todos los códigos de error del resultado.
	 *
	 * Ideal por si se desean mostrar mensajes según el contexto en lugar de los
	 * mensajes genéricos.
	 *
	 * @return array
	 */
	public function getCodigosError(): array {
		/* @var $error Error */
		$salida  = [];
		$errores = $this->getErrores();
		foreach ($errores as $error) {
			$salida[] = $error->getCodigo();
		}
		return $salida;
	}

	/**
	 * Devuelve una cadena con todos los mensajes de error.
	 *
	 * @param string $separador
	 * @return string
	 */
	public function getMensajesError($separador = PHP_EOL): string {
		/* @var $error Error */
		$salida  = [];
		$errores = $this->getErrores();
		foreach ($errores as $error) {
			$mensaje = $error->getMensaje();
			if ($mensaje !== "") {
				$salida[] = $mensaje;
			}
		}
		return implode($separador, $salida);
	}

	/**
	 * Devuelve un array con todos los mensajes de error.
	 *
	 * @param string $separador
	 * @return array
	 */
	public function getMensajesErrorArray(): array {
		/* @var $error Error */
		$salida  = [];
		$errores = $this->getErrores();
		foreach ($errores as $error) {
			$mensaje = $error->getMensaje();
			if ($mensaje !== "") {
				$salida[] = $mensaje;
			}
		}
		return $salida;
	}

	/**
	 * Devuelve todos los errores.
	 *
	 * @return array|Error
	 */
	public function getErrores() {
		return $this->errores;
	}

	/**
	 * Devuelve un array con todos los mensajes del resultado.
	 *
	 * Los mensajes son información contenida en el resultado que no genera que
	 * el mismo sea un error.
	 *
	 * @return array
	 */
	public function getMensajesArray(): array {
		return $this->mensajes;
	}

	/**
	 * Devuelve una cadena con todos los mensajes del resultado.
	 *
	 * Los mensajes son información contenida en el resultado que no genera que
	 * el mismo sea un error.
	 *
	 * @param string $separador
	 * @return string
	 */
	public function getMensajes($separador = PHP_EOL): string {
		return implode($separador, $this->mensajes);
	}

	/**
	 * Devuelve un array con las excepciones del resultado.
	 *
	 * @return array
	 */
	public function getExcepciones() : array {
		return $this->excepciones;
	}

	/**
	 * Devuelve la última excepción, si es que tiene.
	 *
	 * @return Exception|Throwable|null
	 */
	public function getExcepcion() {
		$excepciones = $this->getExcepciones();
		return count($excepciones) > 0 ? end($excepciones) : null;
	}

	/**
	 * Establece el objeto a devolver como resultado de la ejecución.
	 *
	 * @param mixed $resultado
	 * @return $this
	 */
	public function setResultado($resultado): Resultado {
		$this->resultado = $resultado;
		return $this;
	}
	
	public function __toString() {
		$mensajes = $this->getMensajes();
		$errores  = $this->getMensajesError();
		return "Mensajes: $mensajes, Errores: $errores";
	}
}
