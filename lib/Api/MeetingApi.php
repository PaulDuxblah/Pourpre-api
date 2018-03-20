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
        if (! self::checkPost() || ! isset($_POST['creator'])) {
            http_response_code(400);
            die;
        }

        $meeting = new Meeting();
        $meeting->longitude = $_POST['longitude'];
        $meeting->latitude = $_POST['latitude'];
        $meeting->creator = $_POST['creator'];
        $meeting->date = $_POST['date'];

        if (isset($_POST['description'])) {
            $meeting->description = $_POST['description'];
        }
        
        $result = $meeting->save();
        if (is_string($result)) {
            echo $result;
            http_response_code(500);
            die;
        }

        Db::insert([
            'from'      => self::getModel()::JOIN_TABLES['user']['table'],
            'keys'      => [self::getModel()::JOIN_TABLES['user']['key'], User::JOIN_TABLES['meeting']['key']],
            'values'    => [$result->id, $result->creator]
        ]);

        if (isset($_POST['escort'])) {
            Db::insert([
                'from'      => self::getModel()::JOIN_TABLES['user']['table'],
                'keys'      => [self::getModel()::JOIN_TABLES['user']['key'], User::JOIN_TABLES['meeting']['key']],
                'values'    => [$result->id, $_POST['escort']]
            ]);
        }

        return $result;
    }
}