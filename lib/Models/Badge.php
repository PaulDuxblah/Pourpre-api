<?php

namespace Pourpre\Models;

use Pourpre\Models\Model;
use Pourpre\Db;

class Badge extends Model
{
    const JOIN_TABLES = [
        'user' => ['table' => 'user_badge', 'key' => 'badge_id']
    ];

    public static function getTableName()
    {
        return 'badge';
    }

    public static function getKeysNeededToCreate()
    {
        return [
            'name',
            'description'
        ];
    }

    public static function getDbToObjectConvertionArray()
    {
        return [];
    }

    public function __construct($params = []) {
        $this->id           = isset($params['id']) ? $params['id'] : '';
        $this->name         = isset($params['name']) ? $params['name'] : '';
        $this->description  = isset($params['description']) ? $params['description'] : '';
    }
}