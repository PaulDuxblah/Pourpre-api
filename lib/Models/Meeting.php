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

    public function __construct($params = [])
    {
        $this->id           = isset($params['id']) ? $params['id'] : '';
        $this->longitude    = isset($params['longitude']) ? $params['longitude'] : '';
        $this->latitude     = isset($params['latitude']) ? $params['latitude'] : '';
        $this->date         = isset($params['date']) ? $params['date'] : '';
        $this->creator      = isset($params['creator']) ? $params['creator'] : '';
        $this->description  = isset($params['description']) ? $params['description'] : '';

        if (!empty($this->id)) $this->loadData(); 
    }

    public function loadData()
    {
        $this->loadMembers();
    }

    public function loadMembers($refresh = false)
    {
        if (!isset($this->members) || empty($this->members) || $refresh) {
            $this->members = Db::select([
                'select' => [User::getTableName() . '.*'],
                'from' => self::getTableName() . ' as m',
                'where' => [
                    self::getTableName() . '.id = ' . $this->id
                ],
                'join' => self::getJoinQueryTo('user')
            ]);
            if (empty($this->members)) $this->members = [];
        }
    }

    public function save()
    {
        $result = parent::save();

        Db::insert([
            'from'      => self::getModel()::JOIN_TABLES['user']['table'],
            'keys'      => [self::getModel()::JOIN_TABLES['user']['key'], User::JOIN_TABLES['meeting']['key']],
            'values'    => [$result->id, $result->creator]
        ]);

        if (isset($_POST['escort']) && $_POST['escort'] > 0) {
            Db::insert([
                'from'      => self::getModel()::JOIN_TABLES['user']['table'],
                'keys'      => [self::getModel()::JOIN_TABLES['user']['key'], User::JOIN_TABLES['meeting']['key']],
                'values'    => [$result->id, $_POST['escort']]
            ]);
        }

        return $this;
    }

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

    public function __construct($params = []) {
        $this->id           = isset($params['id']) ? $params['id'] : '';
        $this->date         = isset($params['date']) ? $params['date'] : '';
        $this->longitude    = isset($params['longitude']) ? $params['longitude'] : '';
        $this->latitude     = isset($params['latitude']) ? $params['latitude'] : '';
        $this->description  = isset($params['description']) ? $params['description'] : '';
    }
}