<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use SallePW\SlimApp\Controller\RoutesController;
use SallePW\SlimApp\Controller\GamesController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;

final class WishlistController
{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    public function saveMyWishlist(Request $request,Response $response): Response
    {
        $gameid = $request->getAttribute('gameID');
        $data = $request->getParsedBody();

        $this->mysqlRepository->getWish($_SESSION['email'], $gameid, $data);

        return $response->withHeader('Location', '/store')->withStatus(302);

    }

    public function showMyWishlist(Request $request,Response $response): Response
    {
        $a = new GamesController($this->twig);
        $stores =  $a->getStoresInformation();

        $games = $this->mysqlRepository->getWishHistory($_SESSION['email']);

        return $this->twig->render($response,'myGames.twig',[
            'stores' => $stores,
            'product' => $games
        ]);
    }


    public function ViewGameDetail(Request $request,Response $response): Response{
        $gameid = $request->getAttribute('gameID');
        $url = '/user/wishlist?gameId='. $gameid;
        return $response->withHeader('Location', $url)->withStatus(302);
    }

}
