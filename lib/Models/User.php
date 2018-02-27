<?php

namespace Pourpre\Models;

use Pourpre\Models\Model;
use Pourpre\Db;

class User extends Model
{
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $bloodType;
    public $canDonate;

    public static function getTableName()
    {
        return 'user';
    }

    public static function getDbToObjectConvertionArray()
    {
        return [
            'first_name'    => 'firstName',
            'last_name'     => 'lastName',
            'can_donate'    => 'canDonate',
            'blood_type'    => 'bloodType'
        ];
    }

    public function __construct($params = []) {
        $this->firstName    = isset($params['firstName']) ? $params['firstName'] : '';
        $this->lastName     = isset($params['lastName']) ? $params['lastName'] : '';
        $this->email        = isset($params['email']) ? $params['email'] : '';
        $this->password     = isset($params['password']) ? $params['password'] : '';
        $this->bloodType    = isset($params['bloodType']) ? $params['bloodType'] : '';
        $this->canDonate    = isset($params['canDonate']) ? $params['canDonate'] : '';
    }

    public function getEncodedPassword()
    {
        return hash('sha256', $this->password);
    }

    public static function encodePassword($password)
    {
        return hash('sha256', $password);
    }

    public static function authenticate($email, $password)
    {
        if (! $user = self::login($email, $password)) {
            return false;
        }

        return $user;
    }

    public static function login($email, $password)
    {
        $user = self::select([
            'where' => ['email = "' . Db::escapeVar($email) . '"', 'password = "' . self::encodePassword($password) . '"']
        ]);

        if ($user) return $user;

        if (self::findByEmail($email)) {
            return 'Wrong password';
        }

        return 'Unknown email';
    }

    public static function findByEmail($email)
    {
        return self::select([
            'where' => ['email = "' . Db::escapeVar($email) . '"']
        ]);
    }
}