<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class StoreController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function showStore(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response,'store.twig',[]);
    }
}
