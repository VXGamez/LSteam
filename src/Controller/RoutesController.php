<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class RoutesController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function doLogout(Request $request, Response $response): Response
    {

        unset($_SESSION['email']);
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function showRegisterForm(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render(
            $response,
            'register.twig',
            []
        );
    }

    public function showLanding(Request $request, Response $response): Response
    {
        if (isset($_SESSION['email'])) {
            return $this->container->get('view')->render($response,'landing.twig',['isLoggedIn' => "estaLogueado"]);
        }else{
            return $this->container->get('view')->render($response,'landing.twig',[]);
        }
    }

    public function showLogin(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render(
            $response,
            'login.twig',
            []
        );
    }

    public function showBlank(Request $request, Response $response): Response
    {
        //return $response->withHeader('Location', '/login')->withStatus(302);
        return $this->container->get('view')->render(
            $response,
            'blank.twig',
            [
            ]
        );
    }
}