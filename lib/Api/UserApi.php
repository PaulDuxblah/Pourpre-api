<?php

namespace Pourpre\Api;

use Pourpre\Models\User;
use Pourpre\Api\Api;

class UserApi extends Api
{
    public static function getModel()
    {
        return 'Pourpre\Models\User';
    }

    public function login()
    {
        if (! isset($_POST['email']) || ! isset($_POST['password'])) return self::getHttpCode(400);
        $model = self::getModel();
        return $model::authenticate($_POST['email'], $_POST['password']);
    }
}