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
use Slim\Flash\Messages;
use Slim\Views\Twig;

$container = new Container();

/************************************************
* @Finalitat: Defineix la vista com a global a les dependencies i afegeix la session i l'objecte Flash com a constants a la vista.
Es comprova si la session ha començat per controlar errors, fem la mateixa comprovació al index.php i als middlewares per assegurar-nos que la session estigui iniciada.
************************************************/
$container->set(
    'view',
    function (ContainerInterface $c) {
        if(!isset($_SESSION['started'])){
            session_start();
            $_SESSION['started'] = "ha empesado";
            
        }
        
        $view = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
        $view->getEnvironment()->addGlobal('session', $_SESSION);
        $view->getEnvironment()->addGlobal('flash', $c->get(Messages::class));
        return $view;
    }
);

/************************************************
* @Finalitat: Defineix l'objecte flash com a global a les dependencies
************************************************/
$container->set('flash', function () {
    return new Messages();
});

/************************************************
* @Finalitat: Defineix l'objecte PDOSingleton com a global per poder-la accedir desde MySQLRepository més endevant
************************************************/
$container->set('db', function () {
    return PDOSingleton::getInstance(
        $_ENV['MYSQL_ROOT_USER'],
        $_ENV['MYSQL_ROOT_PASSWORD'],
        $_ENV['MYSQL_HOST'],
        $_ENV['MYSQL_PORT'],
        $_ENV['MYSQL_DATABASE']
    );
});

/************************************************
* @Finalitat: Fem l'objecte app global a les dependencies
************************************************/
$container->set('app', function () {
    return $app;
});

/************************************************
* @Finalitat: Declarem el MySQLRepository i li passem el callback que implementa ja que és com el comuniquem amb els controllers
************************************************/
$container->set(MYSQLCallback::class, function (ContainerInterface $container) {
    return new MySQLRepository($container->get('db'));
});


//-------------------- CONTROLLERS A LES DEPENDENCIES --------------------
/************************************************
* @Finalitat: Declarem cada un dels controllers a les dependencies passant el objecte Twig de la vista, i el MySQLCallback per les peticions a la BBDD
************************************************/


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

//Com a extra, el controller de la store reb l'objecte flash per poder mostrar un flash message d'error tal i com indica l'enunciat
$container->set(
    StoreController::class,
    function (ContainerInterface $c) {
        $controller = new StoreController($c->get("view"), $c->get(MYSQLCallback::class), $c->get("flash"));
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

