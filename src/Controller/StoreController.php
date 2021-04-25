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
        

        $tt = $client->get('https://www.cheapshark.com/api/1.0/stores'); 
        $s = json_decode((string)$tt->getBody(), true);
        $stores = [];
        foreach($s as &$value){
            $img = 'https://www.cheapshark.com'.$value['images']['banner'];
            array_push($stores, $img);
        }

        if(isset($_SESSION['email'])){
            $juegos = $this->container->get('repository')->getUserGames($_SESSION['email']);
        }else{
            $juegos = [];
            $juegos['fav'] = [];
            $juegos['comprados'] = [];
        }
        $err = null;
        if(isset($_SESSION['ERR_STORE'])){
            $err = $_SESSION['ERR_STORE'];
            unset($_SESSION['ERR_STORE']);
        }

        return $this->container->get('view')->render($response,'store.twig',[
            'product'=>$v,
            'favoritos' => $juegos['fav'],
            'comprados' => $juegos['comprados'],
            'stores' => $stores,
            'error' => $err
            ]);
    }

    public function buyGame(Request $request, Response $response): Response{
        $gameid = $request->getAttribute('gid');
        $data = $request->getParsedBody();

        if(floatval($_SESSION['wallet'])>= floatval($data['salePrice'])){
            $this->container->get('repository')->buyGame($_SESSION['email'], $gameid, $data);
            
            $this->container->get('repository')->updateWallet($_SESSION['wallet']-$data['salePrice'], $_SESSION['email']);
            return $response->withHeader('Location', '/store')->withStatus(302);
        }else{
            $_SESSION['ERR_STORE'] = 'Not enough funds';
            return $response->withHeader('Location', '/store')->withStatus(302);
        }

    } 

}
