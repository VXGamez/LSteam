<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;

final class RoutesController{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    public function doLogout(Request $request, Response $response): Response
    {

        unset($_SESSION['email']);
        unset($_SESSION['wallet']);
        unset($_SESSION['uuid']);
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function showRegisterForm(Request $request, Response $response): Response
    {

        return $this->twig->render($response,'register.twig',[]);
    }

    public function showLanding(Request $request, Response $response): Response
    {
        return $this->twig->render($response,'landing.twig',[]);
    }


    public function showChangePass(Request $request, Response $response): Response
    {
        return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
    }

    public function showLogin(Request $request, Response $response): Response
    {

        return $this->twig->render($response,'login.twig',[]);
    }

    public function showBlank(Request $request, Response $response): Response
    {
        return $this->twig->render($response,'blank.twig',[]);
    }

    public function showWallet(Request $request,Response $response): Response
    {
        return $this->twig->render($response,'wallet.twig',[
            'data' => $_SESSION['wallet']
        ]);
    }

}