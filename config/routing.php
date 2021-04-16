<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Controller\RoutesController;
use SallePW\SlimApp\Controller\UserController;
use SallePW\SlimApp\Middleware\BeforeMiddleware;
use SallePW\SlimApp\Middleware\SessionMiddleware;

$app->get(
    '/',
    RoutesController::class . ":showLanding"
)->setName('home');

$app->get(
    '/logout',
    RoutesController::class . ":doLogout"
)->setName('logout_user')->add(BeforeMiddleware::class);

$app->get(
    '/login',
    RoutesController::class . ":showLogin"
)->setName('login_user')->add(SessionMiddleware::class);

$app->get(
    '/register',
    RoutesController::class . ":showRegisterForm"
)->setName('home')->add(SessionMiddleware::class);

$app->get(
    '/activate',
    UserController::class . ":validateUser"
)->setName('home');



$app->post(
    '/login',
    UserController::class . ":loginUser"
)->setName('home');

$app->post(
    '/register',
    UserController::class . ":registerUser"
)->setName('create_user');