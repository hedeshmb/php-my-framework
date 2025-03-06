<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Repository;
use App\Core\Request;

class DomainController
{
    /**
     * @var string|true
     */
    private $userStatus;

    public function __construct()
    {
        session_start();
        $user = new UserController();
        $this->userStatus = $user->checkUserinfo();
    }

    public function store()
    {
        if ($this->userStatus !== true) {
            return $this->userStatus;
        }

        $request = new Request();
        $input = $request->getBody();

        $repository = new Repository();
        return ['domain_id' => $repository->storeDomain($input['url'], $input['description'])];
    }
}