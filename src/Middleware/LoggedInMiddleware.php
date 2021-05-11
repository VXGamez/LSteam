<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Middleware;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

final class LoggedInMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): ?Response
    {

        if (!isset($_SESSION['email'])) {
            header("Location:/");
            exit();
        }

        $response = $next->handle($request);
        return $response;


    }
}