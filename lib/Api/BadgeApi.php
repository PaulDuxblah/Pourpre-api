<?php

namespace Pourpre\Api;

use Pourpre\Api\Api;
use Pourpre\Models\Badge;
use Pourpre\Models\User;

class BadgeApi extends Api
{
    public static function getModel()
    {
        return 'Pourpre\Models\Badge';
    }

    public static function post()
    {
        if (! isset($_POST['badge_id']) || ! isset($_POST['user_id'])) {
            http_response_code(400);
            die;
        }

        if (! static::manageTokenAuthentication($_POST['user_id'])) return false;

        $result = Db::insert([
            'from'      => self::getModel()::JOIN_TABLES['user']['table'],
            'keys'      => [self::getModel()::JOIN_TABLES['user']['key'], User::JOIN_TABLES['badge']['key']],
            'values'    => [$_POST['badge_id'], $_POST['user_id']]
        ]);

        if (is_string($result)) {
            echo $result;
            http_response_code(500);
            die;
        }

        return $result;
    }
}