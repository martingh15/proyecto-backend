<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class BusinessException extends Exception
{
    private $errores = array();

    public function __construct($errores)
    {
        $this->errores = $errores;
    }

    /**
     * @return array|string
     */
    public function getErrores()
    {
        return $this->errores;
    }

    public function report()
    {
    }


    public function render()
    {
        DB::rollBack();
        return Response::json(array(
            'code' => 500,
            'message' => json_encode($this->errores)
        ), 500);
    }
}
