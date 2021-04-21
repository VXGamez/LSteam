<?php
declare(strict_types=1);

use SallePW\SlimApp\Controller\ProfileController;
use SallePW\SlimApp\Controller\RoutesController;
use SallePW\SlimApp\Controller\UserController;
use SallePW\SlimApp\Middleware\NoLoginMiddleware;
use SallePW\SlimApp\Middleware\LoggedInMiddleware;

$app->get(
    '/',
    RoutesController::class . ":showLanding"
)->setName('home');

$app->get(
    '/login',
    RoutesController::class . ":showLogin"
)->setName('login_user')->add(LoggedInMiddleware::class);

$app->get(
    '/register',
    RoutesController::class . ":showRegisterForm"
)->setName('home')->add(LoggedInMiddleware::class);

$app->get(
    '/activate',
    UserController::class . ":validateUser"
)->setName('home');

$app->get(
    '/profile',
    ProfileController::class . ":showProfile"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/profile/changePassword',
    ProfileController::class . ":showChangePass"
)->setName('home')->add(NoLoginMiddleware::class);


//TODO CANVIAR LES FUNCIONS DELS CONTROLLERS QUAN ESTIGUIN IMPLEMENTADES

//----------------------------------------------------------------------------

$app->get(
    '/store',
    RoutesController::class . ":showBlank"
)->setName('home');

$app->get(
    '/user/wallet',
    RoutesController::class . ":showBlank"
)->setName('home');

$app->get(
    '/user/wishlist',
    RoutesController::class . ":showBlank"
)->setName('home');

$app->get(
    '/user/friends',
    RoutesController::class . ":showBlank"
)->setName('home');

$app->get(
    '/user/myGames',
    RoutesController::class . ":showBlank"
)->setName('home');
//----------------------------------------------------------------------------

$app->post(
    '/profile/changePassword',
    ProfileController::class . ":changePassword"
)->setName('home');

$app->post(
    '/logout',
    RoutesController::class . ":doLogout"
)->setName('logout_user');

$app->post(
    '/login',
    UserController::class . ":loginUser"
)->setName('home');

$app->post(
    '/register',
    UserController::class . ":registerUser"
)->setName('create_user');