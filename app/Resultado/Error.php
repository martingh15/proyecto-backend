<?php

namespace App\Resultado;

/**
 * Para uso interno de la clase Resultado.
 *
 * @author Alejandro Fiore
 */
class Error {

	protected $codigo;
	protected $mensaje;

	public function __construct(string $codigo, string $mensaje) {
		$this->codigo	 = $codigo;
		$this->mensaje	 = $mensaje;
	}

	public function getCodigo(): string {
		return $this->codigo;
	}

	public function getMensaje(): string {
		return $this->mensaje;
	}

	public function setCodigo(string $codigo) {
		$this->codigo = $codigo;
		return $this;
	}

	public function setMensaje(string $mensaje) {
		$this->mensaje = $mensaje;
		return $this;
	}

}
