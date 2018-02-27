<?php

namespace Pourpre\Models;

use Pourpre\Db;

abstract class Model
{
    abstract public static function getTableName();

    abstract public static function getDbToObjectConvertionArray();

    protected static function select($params)
    {
        $params['from'] = static::getTableName();
        return self::convertDbResultToObject(Db::select($params));
    }

    public static function convertDbResultToObject($dbResult)
    {
        $params = [];

        foreach ($dbResult as $key => $value) {
            if (isset(static::getDbToObjectConvertionArray()[$key])) {
                $params[static::getDbToObjectConvertionArray()[$key]] = $value;
            } else {
                $params[$key] = $value;
            }
        }

        $class = get_called_class();
        return new $class($params);
    }

    public static function find($id)
    {
        return self::select([
            'where' => 'id = ' . $id
        ]);
    }

    public function save()
    {

    }
}