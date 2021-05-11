<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;

final class FriendsController
{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    public function showMyFriends(Request $request, Response $response): Response
    {
        $errors = [];
        if(isset($_SESSION['friendError'])){
            $errors = $_SESSION['friendError'];
            unset($_SESSION['friendError']);
        }
        return $this->twig->render($response,'friends.twig',[
            'errores' => $errors
        ]);
    }

    public function showMyRequests(Request $request, Response $response): Response {
        return $response->withHeader('Location', '/user/friends#myRequests')->withStatus(302);

    }

    public function addFriend(Request $request, Response $response): Response {
        $_SESSION['friendError'] = 'This user does not exists';
        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);
    }

    public function showAddFriend(Request $request, Response $response): Response {

        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);
    }



}