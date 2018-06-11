<?php

namespace Pourpre;

class Db
{
    private static function getMysqli()
    {
        return new \mysqli('127.0.0.1', 'root', '', 'pourpre');
    }

    private static function execute($query)
    {
        $mysqli = self::getMysqli();

        $result = $mysqli->query($query);
        if (!$result) {
            return $mysqli->error;
        }

        if (strpos($query, 'INSERT INTO') !== false) {
            $result = \mysqli_insert_id($mysqli);
        }
        $mysqli->close();
        return $result;
    }

    public static function escapeVar($var)
    {
        $mysqli = self::getMysqli();
        return $mysqli->real_escape_string($var);
    }

    public static function select($params = [])
    {
        $query = '';

        $query = isset($params['select']) ? self::addSelectsToQuery($query, $params['select']) : self::addSelectsToQuery($query);
        $query = isset($params['from']) ? self::addFromToQuery($query, $params['from']) : $query;
        $query = isset($params['join']) ? self::addJoinsToQuery($query, $params['join']) : $query;
        $query = isset($params['where']) ? self::addWheresToQuery($query, $params['where']) : $query;
        $query = isset($params['group']) ? self::addGroupsToQuery($query, $params['group']) : $query;
        $query = isset($params['order']) ? self::addOrdersToQuery($query, $params['order']) : $query;
        $query = isset($params['limit']) ? self::addLimitToQuery($query, $params['limit']) : $query;
        $query = isset($params['offset']) ? self::addOffsetToQuery($query, $params['offset']) : $query;

        $result = self::execute($query);
        if (is_string($result)) {
            return $result;
        }

        $rows = [];
        if ($result->num_rows > 1) {
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        } else {
            $rows = $result->fetch_assoc();
        }

        if (count($rows) == 1 && !is_array(reset($rows))) {
            return reset($rows);
        }

        return $rows;
    }

    public static function insert($params = [])
    {
        $query = 'INSERT INTO ' . $params['from'];
        $query = (isset($params['keys']) && isset($params['values'])) ? self::addInsertsToQuery($query, $params['keys'], $params['values']) : $query;

        return self::execute($query);
    }

    public static function update($params = [])
    {
        $query = 'UPDATE ' . $params['from'];

        $query = isset($params['update']) ? self::addUpdatesToQuery($query, $params['update']) : $query;
        $query = isset($params['where']) ? self::addWheresToQuery($query, $params['where']) : $query;

        return self::execute($query);
    }

    public static function delete($params = [])
    {
        $query = 'DELETE';
        $query = isset($params['from']) ? self::addFromToQuery($query, $params['from']) : $query;
        $query = isset($params['where']) ? self::addWheresToQuery($query, $params['where']) : $query;

        echo $query;
    }

    private static function addInsertsToQuery($query, $keys = [], $values = [])
    {
        return self::addValuesToQuery(self::addKeysToQuery($query, $keys), $values);
    }

    private static function addKeysToQuery($query, $keys = [])
    {
        $query .= ' (';

        foreach ($keys as $key) {
            $query .= '`' . $key . '`, ';
        }

        return rtrim($query, ', ') . ')';
    }

    private static function addValuesToQuery($query, $values = [])
    {
        $query .= ' VALUES (';

        foreach ($values as $value) {
            $value = self::escapeVar($value);

            if (is_string($value)) {
                $query .= '"' . $value . '"';
            } else {
                switch ($value) {
                    case true:
                        $query .= 1;
                        break;
                    case false:
                        $query .= 0;
                        break;
                    default:
                        $query .= $value;
                        break;
                }
            }

            $query .= ', ';
        }

        return rtrim($query, ', ') . ')';
    }

    private static function addUpdatesToQuery($query, $updates = [])
    {
        $query .= ' SET ';

        foreach ($updates as $key => $value) {
            $query .= '`' . $key . '` = ';

            $value = self::escapeVar($value);
            if (is_string($value)) {
                $query .= '"' . $value . '"';
            } else {
                switch ($value) {
                    case true:
                        $query .= 1;
                        break;
                    case false:
                        $query .= 0;
                        break;
                    default:
                        $query .= $value;
                        break;
                }
            }

            $query .= ', ';
        }

        return rtrim($query, ', ');
    }

    private static function addSelectsToQuery($query, $selects = [])
    {
        $query .= 'SELECT ';

        if (isset($selects) && ! empty($selects)) {
            foreach ($selects as $select) {
                $query .= $select . ', ';
            }
        } else {
            $query .= '*';
        }

        return rtrim($query, ', ');
    }

    private static function addFromToQuery($query, $table)
    {
        return $query . ' FROM ' . $table;
    }

    private static function addJoinsToQuery($query, $joins = [])
    {
        if (! empty($joins)) {
            if (! is_array($joins)) $joins = [$joins];
            foreach ($joins as $join) {
                $query .= ' JOIN ' . $join . ' ';
            }
        }

        return $query;
    }

    private static function addWheresToQuery($query, $wheres = [])
    {
        if (! empty($wheres)) {
            if (! is_array($wheres)) $wheres = [$wheres];
            $query .= ' WHERE ';

            foreach ($wheres as $where) {
                $query .= '(' . $where . ') AND ';
            }
        }

        return rtrim($query, ' AND ');
    }

    private static function addGroupsToQuery($query, $groups = [])
    {
        if (! empty($groups)) {
            if (! is_array($groups)) $groups = [$groups];
            $query .= ' GROUP BY ';

            foreach ($groups as $group) {
                $query .= $group . ', ';
            }
        }

        return rtrim($query, ', ');
    }

    private static function addOrdersToQuery($query, $orders = [])
    {
        if (! empty($orders)) {
            if (! is_array($orders)) $orders = [$orders];
            $query .= ' ORDER BY ';

            foreach ($orders as $order) {
                $query .= $order . ', ';
            }
        }

        return rtrim($query, ', ');
    }

    private static function addLimitToQuery($query, $limit = '')
    {
        if ($limit) {
            return $query . ' LIMIT ' . $limit;
        }

        return $query;
    }

    private static function addOffsetToQuery($query, $offset = '')
    {
        if ($offset) {
            return $query . ' OFFSET ' . $offset;
        }

        return $query;
    }
}