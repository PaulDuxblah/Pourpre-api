<?php

namespace Pourpre\Api;

use Pourpre\Api\Api;
use Pourpre\Models\Meeting;
use Pourpre\Models\User;

class MeetingApi extends Api
{
    public static function getModel()
    {
        return 'Pourpre\Models\Meeting';
    }

    public static function post()
    {
        if (! self::checkPost()) {
            http_response_code(400);
            die;
        }
        
        $model = self::getModel();
        $userModel = UserApi::getModel();
        $user = $userModel::getUserByToken(self::getTokenFromHeaders());

        if (is_string($user)) {
            http_response_code(403);
            die;
        }

        $meeting = new Meeting([
            'longitude' => $_POST['longitude'],
            'latitude' => $_POST['latitude'],
            'creator' => $user->id,
            'date' => $_POST['date']
        ]);

        if (isset($_POST['description'])) {
            $meeting->description = $_POST['description'];
        }
        
        $result = $meeting->save();
        if (is_string($result)) {
            echo $result;
            http_response_code(500);
            die;
        }

        return $result;
    }
}