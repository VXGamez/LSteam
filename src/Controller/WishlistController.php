<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use SallePW\SlimApp\Controller\RoutesController;
use SallePW\SlimApp\Controller\GamesController;
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
        $gameid = $request->getAttribute('gid');
        $data = $request->getParsedBody();

        $this->container->get('repository')->getWish($_SESSION['email'], $gameid, $data);

        return $response->withHeader('Location', '/store')->withStatus(302);

    }

    public function showMyWishlist(Request $request,Response $response): Response
    {
        $a = new GamesController($this->container);
        $stores =  $a->getStoresInformation();

        $games = $this->container->get('repository')->getWishHistory($_SESSION['email']);

        return $this->container->get('view')->render($response,'myGames.twig',[
            'stores' => $stores,
            'product' => $games
        ]);
    }


    public function ViewGameDetail(Request $request,Response $response): Response{
        $gameid = $request->getAttribute('gid');
        $url = '/user/wishlist?gameId='. $gameid;
        return $response->withHeader('Location', $url)->withStatus(302);
    }

}
