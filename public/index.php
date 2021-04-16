<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();

$dotenv->load(__DIR__ . '/../.env');

require_once __DIR__ . '/../config/dependencies.php';

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->add(TwigMiddleware::createFromContainer($app));

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

if(!isset($_SESSION['started'])){
    session_start();
    $_SESSION['started'] = "ha empesado";
}

$errorMiddleware->setErrorHandler(HttpNotFoundException::class, function (
    ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors,bool $logErrorDetails) {
    $response = new \Slim\Psr7\Response();
    $response->getBody()->write('Portant-te a una adreça vàlida...');

    if (isset($_SESSION['email'])) {
        return $response->withHeader('Location', '/')->withStatus(302);
    }else{
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
});

$app->addBodyParsingMiddleware();

require_once __DIR__ . '/../config/routing.php';

$app->run();

?>

