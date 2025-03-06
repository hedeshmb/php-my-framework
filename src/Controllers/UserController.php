<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Repository;
use App\Core\Request;

class UserController
{
    public function login()
    {
        $_SESSION['user'] = array();
        $request = new Request();
        $input = $request->getBody();

        $repository = new Repository();
        $userInfo = $repository->selectUser($input['username'], $input['password']);

        if (empty($userInfo)) {
            return ['message' => 'The username or password is incorrect.'];
        }
        session_start();
        $_SESSION['user'] = $userInfo;
        return ['message' => 'The username logged in successfully.'];
    }

    public function checkUserinfo()
    {
        if (empty($_SESSION['user'])) {
            return 'unauthorized';
        }
        return true;
    }
}