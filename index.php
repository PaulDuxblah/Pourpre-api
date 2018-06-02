<?php

if (!isset($_GET['api']) || !isset($_GET['item'])) {
    http_response_code(404);
    die;
}

error_reporting(E_ALL);

require './init.php';

use Pourpre\Db;
use Pourpre\Models\Model;
use Pourpre\Models\User;
use Pourpre\Api\Api;
use Pourpre\Api\UserApi;

$getItem = ucfirst($_GET['item']);
$className = 'Pourpre\Api\\' . $getItem . 'Api';

function noId($getItem) {
    echo 'You need to set an id to update a ' . strtolower($getItem);
    http_response_code(400);
    die;
}

$fileGetContents = json_decode(file_get_contents('php://input'));

Db::insert([
    'from' => 'logs',
    'keys' => [
        'method',
        'message',
        'date'
    ],
    'values' => [
        $_SERVER['REQUEST_METHOD'],
        json_encode([
            'GET' => $_GET,
            'POST' => $_POST,
            'file_get_contents' => $fileGetContents
        ]),
        date('Y-m-d H:i:s')
    ]
]);

if ($fileGetContents) {
    foreach ($fileGetContents as $key => $value) {
        $_POST[$key] = $value;
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (!isset($_GET['id'])) {
            noId($getItem);
        }

        $result;
        if (intval($_GET['id']) != 0) {
            $result = $className::get($_GET['id']);
        } else {
            $method = $_GET['id'];
            $result = $className::$method();
        }

        if (!$result) {
            http_response_code(400);
        }

        echo json_encode($result);
        break;
    case 'POST':
        $result;
        if (isset($_GET['id'])) {
            $method = $_GET['id'];
            $result = $className::$method();
        } else {
            $result = $className::post();
        }

        echo json_encode($result);
        http_response_code(201);
        break;
    case 'PUT':
        if (! isset($_GET['id'])) {
            noId($getItem);
        }

        echo json_encode($className::put());
        break;
    case 'DELETE':
        if (! isset($_GET['id'])) {
            noId($getItem);
        }
        break;
    default:
        echo 'We only treat GET, POST, PUT and DELETE HTTP requests.';
        http_response_code(404);
        die;
        break;
}

// $user = User::authenticate('paulgirardin@hotmail.fr', 'paul');
// $user = UserApi::get(1);

// var_dump($user);

// echo '<br><br><br>';
