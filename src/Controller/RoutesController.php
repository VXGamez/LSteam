<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

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
        unset($_SESSION['wallet']);
        unset($_SESSION['uuid']);
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

    public function showmyGames(Request $request,Response $response): Response
    {
        $client = new Client([
            'base_uri' => 'https://www.cheapshark.com/',
            'timeout'  => 5.0,
        ]);

        
        $games = $this->container->get('repository')->getPurchaseHistory($_SESSION['email']);

        $tt = $client->get('https://www.cheapshark.com/api/1.0/stores'); 
        $s = json_decode((string)$tt->getBody(), true);
        $stores = [];
        foreach($s as &$value){
            $img = 'https://www.cheapshark.com'.$value['images']['banner'];
            array_push($stores, $img);
        }

        return $this->container->get('view')->render($response,'myGames.twig',[
            'stores' => $stores,
            'product' => $games
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