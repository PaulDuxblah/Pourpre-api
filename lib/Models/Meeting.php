<?php

namespace Pourpre\Models;

use Pourpre\Models\Model;
use Pourpre\Models\User;
use Pourpre\Db;

class Meeting extends Model
{
    const JOIN_TABLES = [
        'user' => ['table' => 'user_meeting', 'key' => 'meeting_id']
    ];

    public static function getTableName()
    {
        return 'meeting';
    }

    public static function getKeysNeededToCreate()
    {
        return [
            'name',
            'date',
            'longitude',
            'latitude',
            'creator'
        ];
    }

    public static function getDbToObjectConvertionArray()
    {
        return [];
    }

    public static function getAllOfUser($id)
    {
        return self::select([
            'select' => [self::getTableName() . '.*'],
            'where' => [User::getTableName() . '.id = ' . $id],
            'join' => self::getJoinQueryTo('user')
        ]);
    }

    public function __construct($params = []) {
        $this->id           = isset($params['id']) ? $params['id'] : '';
        $this->date         = isset($params['date']) ? $params['date'] : '';
        $this->longitude    = isset($params['longitude']) ? $params['longitude'] : '';
        $this->latitude     = isset($params['latitude']) ? $params['latitude'] : '';
        $this->description  = isset($params['description']) ? $params['description'] : '';
    }
}