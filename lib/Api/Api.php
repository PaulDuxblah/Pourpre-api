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

    abstract public static function post();

    public static function checkPost() {
        $keysToCheck = static::getModel()::getKeysNeededToCreate();

        foreach ($keysToCheck as $value) {
            if (!isset($_POST[$value])) {
                return false;
            }
        }

        return true;
    }

    protected static function getHttpCode($code)
    {
        return http_response_code($code);
    } 
}