<?php

namespace Pourpre\Api;

use Pourpre\Api\Api;
use Pourpre\Models\User;

class UserApi extends Api
{
    public static function getModel()
    {
        return 'Pourpre\Models\User';
    }

    public static function login()
    {
        if (! isset($_GET['pseudo']) || ! isset($_GET['password'])) return self::getHttpCode(400);

        $model = self::getModel();
        return $model::authenticate($_GET['pseudo'], $_GET['password']);
    }

    static public function post()
    {
        if (!self::checkPost()) {
            http_response_code(400);
            die;
        }

        $user = new User();
        $user->pseudo = $_POST['pseudo'];
        $user->setEncodedPassword($_POST['password']);

        if (isset($_POST['canDonate'])) {
            $user->canDonate = $_POST['canDonate'];
        }

        if (isset($_POST['bloodType'])) {
            $user->bloodType = $_POST['bloodType'];
        }

        $result = $user->save();
        if (is_string($result)) {
            echo $result;
            http_response_code(500);
            die;
        }

        http_response_code(201);
        return $result;
    }

    public static function put()
    {
        $user = self::getModel()::find($_GET['id']);
        if (!$user) {
            echo 'User not found';
            http_response_code(400);
            die;
        }

        $putfp = fopen('php://input', 'r');
        $putData = [];
        while($data = fread($putfp, 1024)) {
            $explodedData = explode('=', $data);
            $putData[$explodedData[0]] = $explodedData[1];
        }
        fclose($putfp);

        if (isset($putData['canDonate'])) {
            $user->canDonate = $putData['canDonate'];
        }

        if (isset($putData['bloodType'])) {
            $user->bloodType = $putData['bloodType'];
        }

        if (isset($putData['avatar'])) {
            $user->avatar = $putData['avatar'];
        }

        $result = $user->save();
        if (is_string($result)) {
            echo $result;
            http_response_code(500);
            die;
        }

        http_response_code(200);
        return $result;
    }
}