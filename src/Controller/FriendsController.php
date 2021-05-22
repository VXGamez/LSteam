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

    public function isRequestValid($email, $friendUsername) {
          $isValid = false;

          //Comprobamos que no sean amigos ya
          $idUser = $this->mysqlRepository->getUserId($email);
          $idFriend = $this->mysqlRepository->getUserId($friendUsername);

          $bien = $this->mysqlRepository->checkAreFriends($idUser, $idFriend);

          if($bien) { //No son amigos
              //Comprobamos si ya existe solicitud entre estos
              $bien = $this->mysqlRepository->checkRequest($idUser, $idFriend);
              if($bien){
                  $isValid = true;
              } else{
                  //Añadir a la sesion que ya ha habido una solicitud previa
                  $anadido = $this->mysqlRepository->checkRequestExists($idUser, $idFriend);
                  if($anadido){
                      //Se ha aceptado directamente la solicitud
                  } else {
                      //Avisar de que ya ha habido una solicitud entre ellos y alguien ya la rechazo
                  }
              }
          } else {
              //Añador error a la sesion de que ya son amigos
          }
          return $isValid;
    }

    public function showMyFriends(Request $request, Response $response): Response
    {
        $errors = [];
        if (isset($_SESSION['friendError'])) {
            $errors = $_SESSION['friendError'];
            unset($_SESSION['friendError']);
        }
        return $this->twig->render($response, 'friends.twig', [
            'errores' => $errors
        ]);

        /*else {
            $friends = $this->mysqlRepository->getFriends($_SESSION['email']);
            return $this->twig->render($response, 'friends.twig', [
                'friends' => $friends //Array asociativo con ['id'], ['date_accepted'], ['totalJuegos']
            ]);
        }*/
    }

    public function showMyRequests(Request $request, Response $response): Response {

        $requests = $this->mysqlRepository->getRequests($_SESSION['email']);



        return $response->withHeader('Location', '/user/friends#myRequests')->withStatus(302);

    }

    public function addFriend(Request $request, Response $response): Response {
        $request->getParsedBody();
        $user = $this->mysqlRepository->getUser(data['username']); //Para comprobar que existe al que buscamos

        //Mismas comprobaciones que en el login que es?? Aqui comprobamos la cond 1?
        if($user->password() != "TODO MAL"){ //El usuario existe
            if(requestIsValid($_SESSION['email'], data['username'])){ //Nuestro correo y el del que queremos añadir
                //añadimos request
                $this->mysqlRepository->addRequest($_SESSION['email'], data['username']);
            }
        } else{
            $_SESSION['friendError'] = 'This user does not exists';
        }
        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);
    }

    public function showAddFriend(Request $request, Response $response): Response {

        return $response->withHeader('Location', '/user/friends#addFriend')->withStatus(302);
    }

    public function acceptRequest(Request $request, Response $response): Response {
        $id = $this->mysqlRepository->getUserId($_SESSION['email']);

        $requestId = $request->getAttribute('requestID');

        $existe = $this->mysqlRepository->userInRequest($id, $requestId); //Queremos comprobar que el usuario que va a aceptar esta request sea el usuario que la ha recibido

        if($existe){
            //Tod bien
            $this->mysqlRepository->solicitudAceptada($requestId); //Ponemos bool de esta request a true
            $this->mysqlRepository->addNewFriendship($requestId); //Añadimos esta relacion de amistad
        } else {
            //Ha intentado aceptar sin ser el user bueno
        }
    }



}