<?php

namespace SallePW\SlimApp\Controller;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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

    /************************************************
    * @Finalitat: Aquesta funció ens permet realitzar Logout, fent unset de la Session iniciada
    ************************************************/
    public function doLogout(Request $request, Response $response): Response
    {

        unset($_SESSION['email']);
        unset($_SESSION['wallet']);
        unset($_SESSION['uuid']);
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    /************************************************
    * @Finalitat: Funció que mostra el formulari de registre
    ************************************************/
    public function showRegisterForm(Request $request, Response $response): Response
    {

        return $this->twig->render($response,'register.twig',[]);
    }

    /************************************************
    * @Finalitat: Funció que mostra la pàgina d'inici (Landing)
    ************************************************/
    public function showLanding(Request $request, Response $response): Response
    {
        $redirect = [];
        if(isset($_SESSION['isRedirected'])){
            $redirect['isRedirected'] = $_SESSION['isRedirected'];
            unset($_SESSION['isRedirected']);
        }
        return $this->twig->render($response,'landing.twig',[
            'redirect' => $redirect
        ]);
    }

    /************************************************
    * @Finalitat: Funció que mostra la pàgina per poder canviar de contrasenya
    ************************************************/
    public function showChangePass(Request $request, Response $response): Response
    {
        return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
    }

    /************************************************
    * @Finalitat: Funció que mostra la pàgina de Login
    ************************************************/
    public function showLogin(Request $request, Response $response): Response
    {
        $redirect = [];
        if(isset($_SESSION['isRedirected'])){
            $redirect['isRedirected'] = $_SESSION['isRedirected'];
            unset($_SESSION['isRedirected']);
        }
        return $this->twig->render($response,'login.twig',[
            'redirect' => $redirect
        ]);
    }

    /************************************************
    * @Finalitat: Funció que mostra la pàgina de Wallet
    ************************************************/
    public function showWallet(Request $request,Response $response): Response
    {
        return $this->twig->render($response,'wallet.twig',[
            'data' => $_SESSION['wallet']
        ]);
    }

}