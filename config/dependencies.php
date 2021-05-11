<?php
declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
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
use SallePW\SlimApp\Repository\MYSQLCallback;
use SallePW\SlimApp\Repository\MySQLRepository;
use SallePW\SlimApp\Repository\PDOSingleton;
use Slim\Views\Twig;

$container = new Container();

$container->set(
    'view',
    function () {
        if(!isset($_SESSION['started'])){
            session_start();
            $_SESSION['started'] = "ha empesado";
        }
        $view = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
        $view->getEnvironment()->addGlobal('session', $_SESSION);
        return $view;
    }
);

$container->set('db', function () {
    return PDOSingleton::getInstance(
        $_ENV['MYSQL_ROOT_USER'],
        $_ENV['MYSQL_ROOT_PASSWORD'],
        $_ENV['MYSQL_HOST'],
        $_ENV['MYSQL_PORT'],
        $_ENV['MYSQL_DATABASE']
    );
});

$container->set('app', function () {
    return $app;
});

$container->set(MYSQLCallback::class, function (ContainerInterface $container) {
    return new MySQLRepository($container->get('db'));
});

//-------------------- CONTROLLERS A LES DEPENDENCIES --------------------

$container->set(
    FriendsController::class,
    function (ContainerInterface $c) {
        $controller = new FriendsController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    GamesController::class,
    function (ContainerInterface $c) {
        $controller = new GamesController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    LogInController::class,
    function (ContainerInterface $c) {
        $controller = new LogInController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    ProfileController::class,
    function (ContainerInterface $c) {
        $controller = new ProfileController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    RoutesController::class,
    function (ContainerInterface $c) {
        $controller = new RoutesController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    StoreController::class,
    function (ContainerInterface $c) {
        $controller = new StoreController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    UserRegisterController::class,
    function (ContainerInterface $c) {
        $controller = new UserRegisterController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    UserValidateController::class,
    function (ContainerInterface $c) {
        $controller = new UserValidateController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    WalletController::class,
    function (ContainerInterface $c) {
        $controller = new WalletController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);

$container->set(
    WishlistController::class,
    function (ContainerInterface $c) {
        $controller = new WishlistController($c->get("view"), $c->get(MYSQLCallback::class));
        return $controller;
    }
);



//-------------------------------------------------------------------------

