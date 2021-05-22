<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Middleware;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class LoggedInMiddleware
{   

    /************************************************
    * Essencialment, si estem loguejats, no ens deixarà entrar ni a /login ni a /register, portant-nos a la home novament
    ************************************************/
    public function __invoke(Request $request, RequestHandler $next): ?Response
    {

        if (isset($_SESSION['email'])) {
            $_SESSION['isRedirected'] = 'You are already logged in!';
            header("Location:/");
            exit();
        }

        $response = $next->handle($request);
        return $response;


    }
}