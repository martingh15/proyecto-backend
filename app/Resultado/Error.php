<?php

namespace Base\Model;

/**
 * Para uso interno de la clase Resultado.
 *
 * @author Alejandro Fiore
 */
class Error {

	protected $codigo;
	protected $mensaje;

	public function __construct($codigo, $mensaje) {
		$this->codigo	 = $codigo;
		$this->mensaje	 = $mensaje;
	}

	public function getCodigo() {
		return $this->codigo;
	}

	public function getMensaje() {
		return $this->mensaje;
	}

	public function setCodigo($codigo) {
		$this->codigo = $codigo;
		return $this;
	}

	public function setMensaje($mensaje) {
		$this->mensaje = $mensaje;
		return $this;
	}

}
