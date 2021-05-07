<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

final class WalletController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
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