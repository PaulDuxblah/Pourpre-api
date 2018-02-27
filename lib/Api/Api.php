<?php

namespace Pourpre\Api;

abstract class Api
{
    static abstract public function getModel();

    public static function get($id)
    {
        $model = static::getModel();
        return $model::find($id);
    }

    protected static function getHttpCode($code)
    {
        return http_response_code($code);
    } 
}