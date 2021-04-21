<?php
declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
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

$container->set('repository', function (ContainerInterface $container) {
    return new MySQLRepository($container->get('db'));
});