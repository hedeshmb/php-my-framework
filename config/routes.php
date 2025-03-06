<?php
declare(strict_types=1);

use App\Controllers\LinkController;
use App\Controllers\UserController;
use App\Controllers\DomainController;
use App\Core\Router;

$router = new Router();

$router->get('/link/index', [LinkController::class, 'index']);
$router->get('/link/store', [LinkController::class, 'store']);
$router->get('/link/update', [LinkController::class, 'update']);
$router->get('/link/delete', [LinkController::class, 'delete']);
$router->get('/domain', [DomainController::class, 'store']);
$router->get('/user/login', [UserController::class, 'login']);