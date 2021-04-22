<?php

namespace SallePW\SlimApp\Controller;


use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SallePW\SlimApp\Model\User;

final class ProfileController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $errors = [];
        if(isset($_SESSION['passErrors'])){
            $errors = $_SESSION['passErrors'];
            unset($_SESSION['passErrors']);
        }
        $user = $this->container->get('repository')->getUser($_SESSION['email']);
        if($user->password() != "TODO MAL"){
            return $this->container->get('view')->render($response,'profile.twig',[
                'user' => $user, 
                'wallet' => $user->getWallet(),
                'errors' => $errors
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
        $data = $request->getParsedBody();
        $ok = true;
        $errors = [];
        if(strlen($data['phone'])>0){
            $phoneUtil = PhoneNumberUtil::getInstance();
            try {
                $phoneNumberObject = $phoneUtil->parse($data['phone'], 'ES');
                $possible = $phoneUtil->isValidNumberForRegion($phoneNumberObject, 'ES');
                if(!$possible){
                    $errors['phone'] = 'This is not a valid Spanish number';
                    $ok = false;
                }
            } catch (NumberParseException $e) {
                $errors['phone'] = 'This is not a valid Spanish number';
                $ok = false;
            }
            $phone = $data['phone'];
            if($ok && !str_starts_with($data['phone'], '+34')){
                $phone = "+34 ".$phone;
            }
            $data['phone'] = $phone;
        }

       if($ok){
           $this->container->get('repository')->updatePhone($_SESSION['email'], $data['phone']);
       }

       $user = $this->container->get('repository')->getUser($_SESSION['email']);

       return $this->container->get('view')->render($response,'profile.twig',[
           'user' => $user,
           'errors' => $errors,
           'wallet' => $user->getWallet()
       ]);

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
            $_SESSION['passErrors'] = $errors;
            return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
        }
    }
}