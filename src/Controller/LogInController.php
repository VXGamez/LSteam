<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use DateTime;
use Exception;
use Google\Client;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Draft;
use Google_Service_Gmail_Message;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Psr\Container\ContainerInterface;
use SallePW\SlimApp\Model\CosEmail;
use SallePW\SlimApp\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Model\Repository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use SallePW\SlimApp\Repository\MySQLRepository;
use SallePW\SlimApp\Repository\PDOSingleton;
use function DI\value;


final class LogInController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loginUser(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();
        $ok=true;
        $errors=[];
        $user = $this->container->get('repository')->getUser($data['username']);
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
            return $response->withHeader('Location', '/')->withStatus(302);
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