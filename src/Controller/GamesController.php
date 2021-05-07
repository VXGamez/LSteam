<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

final class GamesController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    
    public function getStoresInformation(){
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
    }

    public function showmyGames(Request $request,Response $response): Response
    {
      
        $stores = $this->getStoresInformation();

        $games = $this->container->get('repository')->getPurchaseHistory($_SESSION['email']);
        $es = "lo es";

        return $this->container->get('view')->render($response,'myGames.twig',[
            'stores' => $stores,
            'product' => $games,
            'esMyGames' => $es
        ]);
    }

   


}