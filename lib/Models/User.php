<?php

namespace Pourpre\Models;

use Pourpre\Db;

use Pourpre\Models\Model;
use Pourpre\Models\Badge;

class User extends Model
{
    const JOIN_TABLES = [
        'badge' => ['table' => 'user_badge', 'key' => 'user_id'],
        'meeting' => ['table' => 'user_meeting', 'key' => 'user_id']
    ];

    public static function getTableName()
    {
        return 'user';
    }

    public static function getKeysNeededToCreate()
    {
        return [
            'pseudo',
            'password'
        ];
    }

    public static function getDbToObjectConvertionArray()
    {
        return [
            'can_donate'    => 'canDonate',
            'blood_type'    => 'bloodType'
        ];
    }

    public function __construct($params = []) {
        $this->id           = isset($params['id']) ? $params['id'] : '';
        $this->pseudo       = isset($params['pseudo']) ? $params['pseudo'] : '';
        $this->password     = isset($params['password']) ? $params['password'] : '';
        $this->bloodType    = isset($params['bloodType']) ? $params['bloodType'] : '';
        $this->canDonate    = isset($params['canDonate']) ? $params['canDonate'] : '';
        $this->avatar       = isset($params['avatar']) ? $params['avatar'] : '';
    }

    public static function encodePassword($password)
    {
        return hash('sha256', $password);
    }

    public function setEncodedPassword($password) {
        $this->password = $this->encodePassword($password);
    }

    public static function authenticate($pseudo, $password)
    {
        if (! $user = self::login($pseudo, $password)) {
            return false;
        }

        return $user;
    }

    public static function login($pseudo, $password)
    {
        $user = self::select([
            'where' => [
                'pseudo = "' . Db::escapeVar($pseudo) . '"', 
                'password = "' . self::encodePassword($password) . '"'
            ]
        ]);

        if ($user) return $user;

        if (self::findByPseudo($pseudo)) {
            return 'Wrong password';
        }

        return 'Unknown pseudo';
    }

    public static function findByPseudo($pseudo)
    {
        return self::select([
            'where' => ['pseudo = "' . Db::escapeVar($pseudo) . '"']
        ]);
    }

    public static function find($id)
    {
        $user = self::select([
            'where' => 'id = ' . $id
        ]);

        if (!$user) {
            return false;
        }

        $user->loadBadges();
        $user->loadMeetings();
        $user->loadNumberOfEscort();
        $user->loadNumberOfDonations();
        return $user;
    }

    private function loadBadges($refresh = false)
    {
        if (!isset($this->badges) || empty($this->badges) || $refresh) {
            $this->badges = self::select([
                'select' => [Badge::getTableName() . '.*'],
                'where' => [self::getTableName() . '.id = ' . $this->id],
                'join' => self::getJoinQueryTo('badge')
            ]);
            if (is_null($this->badges)) $this->badges = [];
        }
    }

    private function loadMeetings($refresh = false)
    {
        if (!isset($this->meetings) || empty($this->meetings) || $refresh) {
            $this->meetings = self::select([
                'select' => [Meeting::getTableName() . '.*'],
                'where' => [self::getTableName() . '.id = ' . $this->id],
                'join' => self::getJoinQueryTo('meeting')
            ]);
            if (is_null($this->meetings)) $this->meetings = [];
        }
    }

    private function loadNumberOfEscort($refresh = false)
    {
        if (!isset($this->escorts) || $refresh) {
            $this->escorts = Db::select([
                'select' => ['COUNT(' . Meeting::getTableName() . '.id)'],
                'from' => self::getTableName(),
                'where' => [
                    self::getTableName() . '.id = ' . $this->id,
                    Meeting::getTableName() . '.creator != ' . $this->id
                ],
                'join' => self::getJoinQueryTo('meeting')
            ]);
            if (is_null($this->escorts)) $this->escorts = 0;
        }
    }

    private function loadNumberOfDonations($refresh = false)
    {
        if (!isset($this->donations) || $refresh) {
            $this->donations = Db::select([
                'select' => ['COUNT(' . Meeting::getTableName() . '.id)'],
                'from' => self::getTableName(),
                'where' => [
                    self::getTableName() . '.id = ' . $this->id,
                    Meeting::getTableName() . '.creator = ' . $this->id
                ],
                'join' => self::getJoinQueryTo('meeting')
            ]);
            if (is_null($this->donations)) $this->donations = 0;
        }
    }

    public function isCreatorOfMeeting($meeting)
    {
        return $this->id == $meeting->creator;
    }
}