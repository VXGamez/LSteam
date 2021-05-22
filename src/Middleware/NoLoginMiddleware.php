<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Middleware;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class NoLoginMiddleware
{
    public function __invoke(Request $request, RequestHandler $next): ?Response
    {

        if (!isset($_SESSION['email'])) {
            $_SESSION['isRedirected'] = 'Please login first before accessing this endpoint.';
            header("Location:/login");
            exit();
        }
        $response = $next->handle($request);
        return $response;


    }
}