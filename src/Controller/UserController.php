<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use DateTime;
use Exception;
use Psr\Container\ContainerInterface;
use SallePW\SlimApp\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\Repository;
use SallePW\SlimApp\Repository\MySQLRepository;
use SallePW\SlimApp\Repository\PDOSingleton;

final class UserController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function registerUser(Request $request, Response $response): Response
    {


            $data = $request->getParsedBody();

            $errors = [];

            $ok = true;

            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Not a valid email';
                $ok = false;
            }else{
                $errors['emailOk'] = $data['email'];
            }

            if($data['password'] != $data['cpassword']){
                $errors['password'] = 'Passwords must match';
                $ok = false;
            }else{
                $lowercase = preg_match('@[a-z]@', $data['password']);
                $number    = preg_match('@[0-9]@', $data['password']);
                if( !$lowercase || !$number  || strlen($data['password']) < 6) {
                    $errors['password'] = 'Password Wrong';
                    $ok = false;
                }
            }

            if($this->container->get('repository')->checkIfExists($data['email'])){
                $errors['email'] = 'Not a valid email';
                $ok = false;
            }

        $birthday = "";
        try {
            $birthday = new DateTime($data['birthday']);
        } catch (Exception $e) {
        }

        if($ok == true) {
                $user = new User(
                    $data['username'],
                    $data['email'],
                    $data['password'],
                    $birthday,
                    $data['phone'] ?? '',
                    new DateTime()
                );

                $_SESSION['email'] = $data['email'];

                $ok = $this->container->get('repository')->save($user);
            }


            if($ok == true){
                return $response->withHeader('Location', '/home')->withStatus(302);

            }else{

                return $this->container->get('view')->render(
                    $response,
                    'register.twig',
                    [
                        'errors' => $errors
                    ]
                );
            }


    }


    public function loginUser(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();
        $ok=false;
        $errors=[];
        if($this->container->get('repository')->checkIfExists($data['email'])){
            if($this->container->get('repository')->validateUser($data['email'], $data['password'])){
                $ok = true;
            }else{
                $errors['user'] = 'Email o contrasenya no son valids';
            }
        }else{
            $errors['user'] = 'Email o contrasenya no son valids';
        }

        $_SESSION['email'] = $data['email'];
        if($ok){
            return $response->withHeader('Location', '/home')->withStatus(302);
        }else{
            return $this->container->get('view')->render(
                $response,
                'login.twig',
                [
                    'errors' => $errors
                ]
            );
        }

    }


    }