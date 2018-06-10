<?php

namespace Pourpre\Api;

abstract class Api
{
    public static function manageTokenAuthentication($userId)
    {
        if (!static::checkIfTokenExists()) {
            static::getHttpCode(401);
            return false;
        }

        $user = User::find($userId);
        if (! $user) {
            static::getHttpCode(404);
            return false;
        }

        if (!static::checkToken($user->token)) {
            static::getHttpCode(401);
            return false;
        }

        return true;
    }

    public static function getTokenFromHeaders()
    {
        return apache_request_headers()['token'];
    }

    public static function checkIfTokenExists()
    {
        return isset(apache_request_headers()['token']);
    }

    public static function checkToken($dbUserToken)
    {
        return static::checkIfTokenExists() && static::getTokenFromHeaders() === $dbUserToken;
    }

    static abstract public function getModel();

    public static function get($id)
    {
        $model = static::getModel();
        return $model::find($id);
    }

    public static function findBy($param, $needle) {
        $model = static::getModel();
        return $model::find($param, $id);
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