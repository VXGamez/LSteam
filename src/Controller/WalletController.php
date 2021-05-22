<?php

namespace SallePW\SlimApp\Controller;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;

final class WalletController{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }
       
    /************************************************
    * @Finalitat: Aquesta funció s'encarrega d'actualitzar la Wallet,
    * en el cas de que s'ha fet un Add. També comprova que es pugui fer correctament
    ************************************************/
    public function updateWallet(Request $request,Response $response): Response
    {
        $data = $request->getParsedBody();
        $_SESSION['wallet'] = $data['result'];
        $this->mysqlRepository->updateWallet($_SESSION['wallet'],$_SESSION['email']);
        return $this->twig->render($response,'wallet.twig',[
            'data' => $_SESSION['wallet']
        ]);
    }

}