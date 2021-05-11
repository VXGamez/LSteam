<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;

final class GamesController{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    
    public function getStoresInformation(): array
    {
        $client = new Client([
            'base_uri' => 'https://www.cheapshark.com/',
            'timeout'  => 5.0,
        ]);

        $tt = $client->get('https://www.cheapshark.com/api/1.0/stores'); 
        $s = json_decode((string)$tt->getBody(), true);
        $stores = [];
        foreach($s as &$value){
            $img = 'https://www.cheapshark.com'.$value['images']['banner'];
            array_push($stores, $img);
        }

        return $stores;
    }

    public function showmyGames(Request $request,Response $response): Response
    {
      
        $stores = $this->getStoresInformation();

        $games = $this->mysqlRepository->getPurchaseHistory($_SESSION['email']);
        $es = "lo es";

        return $this->twig->render($response,'myGames.twig',[
            'stores' => $stores,
            'product' => $games,
            'esMyGames' => $es
        ]);
    }

   


}