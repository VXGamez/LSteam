<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ProfileController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $user = $this->container->get('repository')->getUser($_SESSION['email']);
        if($user->password() != "TODO MAL"){
            return $this->container->get('view')->render($response,'profile.twig',[
                'user' => $user, 
                'wallet' => $user->getWallet()
            ]);
        }else{
            return $this->container->get('view')->render($response,'blank.twig',[]);
        }
    }
}