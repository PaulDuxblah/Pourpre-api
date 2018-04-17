<?php

namespace Pourpre\Models;

use Pourpre\Db;
use Pourpre\Models\Badge;
use Pourpre\Models\User;

abstract class Model
{
    abstract public static function getTableName();

    abstract public static function getDbToObjectConvertionArray();

    abstract public static function getKeysNeededToCreate();

    protected static function select($params)
    {
        $params['from'] = static::getTableName();
        return self::convertDbResultToObject(Db::select($params));
    }

    public static function convertDbResultToObject($dbResult)
    {
        if (!$dbResult) return;

        $classes = [];
        $params = [];

        foreach ($dbResult as $key => $row) {
            if (!is_array($row)) {
                $params[$key] = $row;
            } else {
                $classes[] = static::generateModelFromRow($row);
            }
        }

        if (! empty($params)) {
            return static::generateModelFromRow($params);
        }
        
        return $classes;
    }

    private static function generateModelFromRow($row)
    {
        $params = [];
        foreach ($row as $key => $value) {
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
        if (isset($this->id) && !empty($this->id)) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    public function insert()
    {
        $params = [
            'from' => static::getTableName(),
            'keys' => [],
            'values' => []
        ];

        foreach ($this as $key => $value) {
            if ($value) {
                if ($dbKey = array_search($key, static::getDbToObjectConvertionArray())) {
                    $params['keys'][] = $dbKey;
                } else {
                    $params['keys'][] = $key;
                }

                $params['values'][] = $value;
            }
        }

        $result = Db::insert($params);
        if (is_string($result)) {
            return $result;
        }

        return self::convertDbResultToObject(self::find($result));
    }

    public function update()
    {
        if (!isset($this->id)) {
            return false;
        }

        $objectFromDb = static::find($this->id);

        $params = [
            'from' => static::getTableName(),
            'updates' => [],
            'where' => ['id = ' . $this->id]
        ];

        foreach ($this as $key => $value) {
            if (!is_null($value) && !is_null($key) && $key != 'id' && $value != $objectFromDb->$key) {
                if ($dbKey = array_search($key, static::getDbToObjectConvertionArray())) {
                    $params['update'][$dbKey] = $value;
                } else {
                    $params['update'][$key] = $value;
                }
            }
        }

        if (empty($params['update'])) return true;

        $result = Db::update($params);
        if (is_string($result)) {
            return $result;
        }

        return self::convertDbResultToObject(self::find($result));
    }

    public static function getJoinTableName($tableName)
    {
        return static::JOIN_TABLES[$tableName]['table'];
    }

    public static function getJoinTableKey($tableName)
    {
        return static::JOIN_TABLES[$tableName]['key'];
    }

    public function getJoinQueryTo($tableName)
    {
        $targetClass = 'Pourpre\Models\\' . ucfirst($tableName);
        return [
            static::getJoinTableName($tableName) . ' ON ' . static::getTableName() . '.id = ' . static::getJoinTableName($tableName) . '.' . static::getJoinTableKey($tableName),
            $tableName . ' ON ' . $targetClass::getTableName() . '.id = ' . $targetClass::getJoinTableName(static::getTableName()) . '.' . $targetClass::getJoinTableKey(static::getTableName()),
        ];
    }
}