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
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function showRegisterForm(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render($response,'register.twig',[]);
    }

    public function showLanding(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response,'landing.twig',[]);
    }


    public function showChangePass(Request $request, Response $response): Response
    {
        return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
    }

    public function showLogin(Request $request, Response $response): Response
    {

        return $this->container->get('view')->render($response,'login.twig',[]);
    }

    public function showBlank(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response,'blank.twig',[]);
    }

    public function showWallet(Request $request,Response $response): Response
    {
        return $this->container->get('view')->render($response,'wallet.twig',[
            'data' => $_SESSION['wallet']
        ]);
    }

    public function updateWallet(Request $request,Response $response): Response
    {
        $data = $request->getParsedBody();
        $_SESSION['wallet'] = $data['result'];
        $this->container->get('repository')->updateWallet($_SESSION['wallet'],$_SESSION['email']);
        return $this->container->get('view')->render($response,'wallet.twig',[
            'data' => $_SESSION['wallet']
        ]);
    }
}