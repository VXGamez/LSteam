<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

final class StoreController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }



    public function showStore(Request $request, Response $response): Response
    {
        
        $client = new Client([
            'base_uri' => 'https://www.cheapshark.com/',
            'timeout'  => 5.0,
        ]);

        $promise = $client->get('https://www.cheapshark.com/api/1.0/deals'); 
        $v = json_decode((string)$promise->getBody(), true);
        
        if(isset($_SESSION['email'])){
            $juegos = $this->container->get('repository')->getUserGames($_SESSION['email']);
        }else{
            $juegos = [];
            $juegos['fav'] = [];
            $juegos['comprados'] = [];
        }
        
        return $this->container->get('view')->render($response,'store.twig',[
            'product'=>$v,
            'favoritos' => $juegos['fav'],
            'comprados' => $juegos['comprados']
            ]);
    }

}
