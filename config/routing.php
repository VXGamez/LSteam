<?php
declare(strict_types=1);

use SallePW\SlimApp\Controller\FriendsController;
use SallePW\SlimApp\Controller\GamesController;
use SallePW\SlimApp\Controller\LogInController;
use SallePW\SlimApp\Controller\ProfileController;
use SallePW\SlimApp\Controller\RoutesController;
use SallePW\SlimApp\Controller\StoreController;
use SallePW\SlimApp\Controller\UserRegisterController;
use SallePW\SlimApp\Controller\UserValidateController;
use SallePW\SlimApp\Controller\WalletController;
use SallePW\SlimApp\Controller\WishlistController;
use SallePW\SlimApp\Middleware\NoLoginMiddleware;
use SallePW\SlimApp\Middleware\LoggedInMiddleware;

/* --------------------------------- GET --------------------------------- */

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
    UserValidateController::class . ":validateUser"
)->setName('home');

$app->get(
    '/profile',
    ProfileController::class . ":showProfile"
)->setName('profile')->add(NoLoginMiddleware::class);

$app->get(
    '/profile/changePassword',
    RoutesController::class . ":showChangePass"
)->setName('profileChangePass')->add(NoLoginMiddleware::class);

$app->get(
    '/store',
    StoreController::class . ":showStore"
)->setName('home');

$app->get(
    '/user/wallet',
    RoutesController::class . ":showWallet"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/user/wishlist',
    WishlistController::class . ":showMyWishlist"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/user/myGames',
    GamesController::class . ":showmyGames"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/user/wishlist/{gameID}',
    WishlistController::class . ":ViewGameDetail"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/user/friends',
    FriendsController::class . ":showMyFriends"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/user/friendRequests',
    FriendsController::class . ":showMyRequests"
)->setName('home')->add(NoLoginMiddleware::class);

$app->get(
    '/user/friendRequests/send',
    FriendsController::class . ":showAddFriend"
)->setName('home')->add(NoLoginMiddleware::class);


/* --------------------------------- POST --------------------------------- */


$app->post(
    '/user/wishlist/{gameID}',
    WishlistController::class . ":saveMyWishlist"
)->setName('home')->add(NoLoginMiddleware::class);

$app->post(
    '/user/friendRequests/send',
    FriendsController::class . ":addFriend"
)->setName('home');

$app->post(
    '/store/buy/{gameID}',
    StoreController::class . ":buyGame"
)->setName('home')->add(NoLoginMiddleware::class);

$app->post(
    '/user/wallet',
    WalletController::class . ":updateWallet"
)->setName('home');

$app->post(
    '/profile/changePassword',
    ProfileController::class . ":changePassword"
)->setName('prueba');

$app->post(
    '/profile',
    ProfileController::class . ":changeProfile"
)->setName('profile');

$app->post(
    '/logout',
    RoutesController::class . ":doLogout"
)->setName('logout_user');

$app->post(
    '/login',
    LogInController::class . ":loginUser"
)->setName('home');

$app->post(
    '/register',
    UserRegisterController::class . ":registerUser"
)->setName('create_user');

$app->post(
    '/user/friendRequests/accept/{requestId}',
    FriendsController::class . ":acceptRequest"
)->setName('accept');

$app->post(
    '/user/friendRequests/deny/{requestId}',
    FriendsController::class . ":denyRequest"
)->setName('deny');


/* --------------------------------- DELETE --------------------------------- */

$app->delete(
    '/user/wishlist/{gameID}',
    WishlistController::class . ":deleteWish"
)->setName('delete');