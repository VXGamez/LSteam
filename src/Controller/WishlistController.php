<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

final class WishlistController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function saveMyWishlist(Request $request,Response $response): Response
    {
        
        $this->container->get('repository')->getWish($_SESSION['email'], $gameid, $data);
       

       
    }

    public function showMyWishlist(Request $request,Response $response): Response
    {
        $stores = $this->getStoresInformation();

        $games = $this->container->get('repository')->getWishHistory($_SESSION['email']);

        return $this->container->get('view')->render($response,'myGames.twig',[
            'stores' => $stores,
            'product' => $games
        ]);
    }


    

    public function ViewGameDetail(Request $request,Response $response): Response{
        return $this->container->get('view')->render($response,'gameDetail.twig',[
            
        ]);
    }

}
