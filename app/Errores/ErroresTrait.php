<?php


namespace App\Errores;


trait ErroresTrait
{
    /**
     * @var array
     */
    protected $errores = [];

    /**
     * Deja el array de errores vacío.
     *
     * @return $this
     */
    protected function erroresReiniciar() {
        return $this->setErrores([]);
    }

    /**
     * Agrega un mensaje de error al array de errores.
     *
     * @param string $error
     * @return $this
     */
    protected function erroresAgregar($error) {
        $this->errores[] = $error;
        return $this;
    }

    /**
     * Establece la lista completa de errores.
     *
     * @param array $errores
     * @return $this
     */
    protected function setErrores(array $errores) {
        $this->errores = $errores;
        return $this;
    }

    /**
     * Devuelve un array con mensajes de error de la última operación.
     *
     * @return array
     */
    public function getErrores() {
        return $this->errores;
    }
}