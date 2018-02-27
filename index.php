<?php

error_reporting('E_STRICT');

require './init.php';

use Pourpre\Db;
use Pourpre\Models\Model;
use Pourpre\Models\User;
use Pourpre\Api\Api;
use Pourpre\Api\UserApi;

// $user = User::authenticate('paulgirardin@hotmail.fr', 'paul');
$user = UserApi::get(1);

var_dump($user);

// echo '<br><br><br>';

// Db::insert(
//     [
//         'from' => 'user',
//         'keys' => [
//             'first_name',
//             'last_name',
//             'email',
//             'can_donate'
//         ],
//         'values' => [
//             'Paul',
//             'Girardin',
//             'paulgirardin@hotmail.fr',
//             true
//         ]
//     ]
// );

// echo '<br><br><br>';

// Db::update(
//     [
//         'from' => 'user',
//         'update' => [
//             'first_name' => 'Paul',
//             'last_name' => 'Girardin',
//             'email' => 'paulgirardin@hotmail.fr',
//             'can_donate' => false
//         ]
//     ]
// );

// echo '<br><br><br>';

// DB::delete([
//     'from' => 'user',
//     'where' => [
//         'id = 1'
//     ]
// ]);