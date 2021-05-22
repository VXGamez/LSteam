<?php

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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


    /************************************************
    * @Finalitat: Aquesta funció mostra el llistat d'amics, renderitzant el friends.twig
    ************************************************/
    public function showMyFriends(Request $request, Response $response): Response
    {
        $errors = [];
        $alert = null;
        if (isset($_SESSION['requestResult'])) {
            $errors['requestResult'] = $_SESSION['requestResult'];
            $alert = $_SESSION['alertClass'];
            unset($_SESSION['requestResult']);
            unset($_SESSION['alertClass']);
        }

        $friends = $this->mysqlRepository->getFriends($_SESSION['email'], 0);
        $requests = $this->mysqlRepository->getFriends($_SESSION['email'], 1);

        return $this->twig->render($response, 'friends.twig', [
            'errores' => $errors,
            'alertClass' => $alert,
            'friends' => $friends,
            'requests' => $requests,
            'numRequests' => count($requests),
            'numFriends' => count($friends)
        ]);
    }

    /************************************************
    * @Finalitat: Aquesta funció mostra el llistat de requests pendets
    ************************************************/
    public function showMyRequests(Request $request, Response $response): Response {

        return $response->withHeader('Location', '/user/friends#myRequests')->withStatus(302);

    }

    /************************************************
    * @Finalitat: Aquesta funció mostra ens permet afegir un usuari 
    * com amic, tot controlant que la request rebuda sigui vàlida
    ************************************************/
    public function addFriend(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $myUserId = $this->mysqlRepository->getUserId($_SESSION['email']);
        $friendUserId = $this->mysqlRepository->getUserId($data['username']);
        if($friendUserId == -1 ){
            $_SESSION['alertClass'] = 'alert alert-danger';
            $_SESSION['requestResult'] = 'There is no user with the username ' . $data['username'] . '.';
        }else{
            $_SESSION['alertClass'] = 'alert alert-danger';
            switch($this->mysqlRepository->requestIsValid($myUserId, $friendUserId)){
                case 0:
                    $this->mysqlRepository->addRequest($myUserId, $friendUserId);
                    $_SESSION['alertClass'] = 'alert alert-success';
                    $_SESSION['requestResult'] = 'Friend request sent!';
                    break;
                case 1:
                    $_SESSION['requestResult'] = 'This user is already a friend!';
                    break;
                case 2:
                    $_SESSION['requestResult'] = 'You already sent a request to this user!';
                    break;
                case 3:
                    $_SESSION['alertClass'] = 'alert alert-success';
                    $_SESSION['requestResult'] = 'This user already sent you a request. It has been accepted!';
                    break;
                default:
                    $_SESSION['requestResult'] = 'Error in the database';
                    break;
            }
        }

        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);
    }

    /************************************************
    * @Finalitat: Aquesta funció ens mostra el form 
    * per enviar una Friend request a un altre usuari
    ************************************************/
    public function showAddFriend(Request $request, Response $response): Response {
        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);
    }

    /************************************************
    * @Finalitat: Aquesta funció ens permet comprovar que
    * podem acceptar una request, i en cas de que sí, afegir com a nou amic
    ************************************************/
    public function acceptRequest(Request $request, Response $response): Response {
        
        $id = $this->mysqlRepository->getUserId($_SESSION['email']);

        $requestId = $request->getAttribute('requestId');

        $existe = $this->mysqlRepository->userInRequest($id, $requestId);

        if($existe){
            $this->mysqlRepository->solicitudAceptada($requestId); 
            $this->mysqlRepository->addNewFriendship($requestId); 
        } else {
            return $this->twig->render($response, 'badRequest.twig');
        }
        
        return $response->withHeader('Location', '/user/friends#myFriends')->withStatus(302);
    }

    /************************************************
    * @Finalitat: Aquesta funció ens permet comprovar que podem no acceptar una request
    ************************************************/
    public function denyRequest(Request $request, Response $response): Response {
        
        $id = $this->mysqlRepository->getUserId($_SESSION['email']);

        $requestId = $request->getAttribute('requestId');

        $existe = $this->mysqlRepository->userInRequest($id, $requestId);

        if($existe){
            $this->mysqlRepository->solicitudAceptada($requestId);
        } else {
            return $this->twig->render($response, 'badRequest.twig');
           
        }
        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);

    }



}