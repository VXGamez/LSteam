<?php

namespace SallePW\SlimApp\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ProfileController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $user = $this->container->get('repository')->getUser($_SESSION['email']);
        if($user->password() != "TODO MAL"){
            return $this->container->get('view')->render($response,'profile.twig',[
                'user' => $user, 
                'wallet' => $user->getWallet()
            ]);
        }else{
            return $this->container->get('view')->render($response,'blank.twig',[]);
        }
    }

    public function showChangePass(Request $request, Response $response): Response
    {
        return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
    }

    public function changeProfile(Request $request, Response $response): Response
    {
        return $response->withHeader('Location', '/profile')->withStatus(302);
    }

    public function changePassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $currPass = $data['currPass'];
        $errors = [];
        $newPass = $data['newPass'];
        $repPass = $data['repPass'];

        if($newPass != $repPass){
            $errors['password'] = 'Passwords must match';
            $ok = false;
        }else{
            $uppercase = preg_match('@[A-Z]@', $newPass);
            $lowercase = preg_match('@[a-z]@', $newPass);
            $number    = preg_match('@[0-9]@', $newPass);
            if( !$uppercase || !$lowercase || !$number  || $newPass < 6) {
                $ok = false;
                if(!$uppercase){
                    $errors['password'] = 'Password must contain at least one uppercase character';
                }else if(!$lowercase){
                    $errors['password'] = 'Password must contain at least one lowercase character';
                }else if(!$number){
                    $errors['password'] = 'Password must contain at least one numeric character';
                }else if(strlen($data['password']) < 6){
                    $errors['password'] = 'Password must be at least 6 characters long';
                }
            }
        }

        $user = $this->container->get('repository')->getUser($_SESSION['email']);
        if($user->password() != "TODO MAL") {
            if (!password_verify($currPass, $user->password())) {
                $errors['user'] = 'Current password is not correct';
                $ok = false;
            }
        }

        if($ok){
            $this->container->get('repository')->updatePass($_SESSION['email'], password_hash($newPass, PASSWORD_DEFAULT));
            return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
        }else{
            $url = $this->container->get('router')->pathFor('prueba', [
                'errors' => $errors
            ]);
            return $response->withHeader('Location', $url)->withStatus(302);
        }
    }
}