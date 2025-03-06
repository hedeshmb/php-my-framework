<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Repository;
use App\Core\Request;

class LinkController
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

    public function index()
    {
        if ($this->userStatus !== true) {
            return $this->userStatus;
        }

        $repository = new Repository();
        return $repository->selectLink();
    }

    public function store()
    {
        if ($this->userStatus !== true) {
            return $this->userStatus;
        }

        $request = new Request();
        $input = $request->getBody();

        $repository = new Repository();
        $linkId = $repository->storeLink($input['title'], $input['source_link'], $input['domain_id']);

        return ['link_id' => $linkId];
    }

    public function update()
    {
        if ($this->userStatus !== true) {
            return $this->userStatus;
        }

        $request = new Request();
        $input = $request->getBody();

        $repository = new Repository();
        $result = $repository->updateLink($input['title'], $input['source_link'], $input['domain_id'], (int)$input['id']);

        if (!$result) {
            return ['message' => 'Error while updating link'];
        }

        return ['link_id' => $input['id']];
    }

    public function delete()
    {
        if ($this->userStatus !== true) {
            return $this->userStatus;
        }

        $request = new Request();
        $input = $request->getBody();

        $repository = new Repository();
        $repository->deleteLink((int)$input['id']);

        return 'Domain deleted successfully';
    }
}
