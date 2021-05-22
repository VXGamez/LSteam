<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;


final class LogInController
{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    public function loginUser(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();
        $ok=true;
        $errors=[];
        $user = $this->mysqlRepository->getUser($data['username']);
        if($user->password() != "TODO MAL"){
            if(!password_verify($data['pass'], $user->password())) {
                $errors['user'] = 'Not a valid username or password';
                $ok = false;
            }
        }else{
            $errors['user'] = 'Not a valid username or password';
            $ok = false;
        }

        if($ok){
            $_SESSION['email'] = $user->username();
            $_SESSION['wallet'] = $user->getWallet();
            if($user->getUuid()!=null){
                $_SESSION['uuid'] = $user->getUuid();
            }
            return $response->withHeader('Location', '/store')->withStatus(302);
        }else{
            return $this->twig->render(
                $response,
                'login.twig',
                [
                    'errors' => $errors
                ]
            );
        }

    }


}