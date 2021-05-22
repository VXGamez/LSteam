<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Flash\Messages;
use Slim\Views\Twig;

final class StoreController
{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;
    private Messages $flash;


    public function __construct(Twig $twig, MYSQLCallback $repository, Messages $flash)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
        $this->flash = $flash;
        $this->flash->__construct($_SESSION);
    }

    /************************************************
    * @Finalitat: Funció que mostra la pàgina de Store, 
    * tot fent les peticions pertinents a cheapshark
    ************************************************/
    public function showStore(Request $request, Response $response): Response
    {
        set_time_limit(0);
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
            $juegos = $this->mysqlRepository->getUserGames($_SESSION['email']);
        }else{
            $juegos = [];
            $juegos['fav'] = [];
            $juegos['comprados'] = [];
        }
 
        $ww = null;
        if(isset($_SESSION['wallet'])){
            $ww = $_SESSION['wallet'];
        }

        return $this->twig->render($response,'store.twig',[
            'product'=>$v,
            'favoritos' => $juegos['fav'],
            'comprados' => $juegos['comprados'],
            'stores' => $stores,
            'ww' => $ww
            ]);
    }

    /************************************************
    * @Finalitat: Funció de control que ens permet veure que es pot 
    * comprar un joc, és a dir, hi ha saldo a la Wallet
    ************************************************/
    public function buyGame(Request $request, Response $response): Response{

        set_time_limit(0);
        $gameid = $request->getAttribute('gameID');
        $data = $request->getParsedBody();

        if(floatval($_SESSION['wallet'])>= floatval($data['salePrice'])){
            $this->mysqlRepository->buyGame($_SESSION['email'], $gameid, $data);

            $this->mysqlRepository->updateWallet($_SESSION['wallet']-$data['salePrice'], $_SESSION['email']);
            return $response->withHeader('Location', '/store')->withStatus(302);
        }else{
            $this->flash->addMessage('error', 'Insuficient funds. Please go to Wallet to add more.');
            return $response->withHeader('Location', '/store')->withStatus(302);
        }

    }
    
    

}
