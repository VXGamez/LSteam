<?php

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
use SallePW\SlimApp\Model\CosEmail;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\Repository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use SallePW\SlimApp\Repository\MySQLRepository;
use SallePW\SlimApp\Repository\PDOSingleton;
use function DI\value;
use SallePW\SlimApp\Controller\UserValidateController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserValidateController{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function enviarCorreu($flag, $email, $username, $token){
        $mail = new PHPMailer();
        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->Port = 587;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->SMTPAuth = true;

        $mail->Username = $_ENV['EMAIL_CORREU'];

        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom( $email, 'LSteam');

        $mail->addAddress($email, $username);

        $mail->Subject = 'PW2-LStream';

        $mail->isHTML(true);
        $emailBody = new CosEmail($token, $username);
        if($flag == 1){
            $mail->Body = $emailBody->body();
        }else{
            $mail->Body = $emailBody->confirmBody();
        }

        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    
    public function validateUser(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        if(!isset($params['token'])){
            return $this->container->get('view')->render(
                $response,
                'landing.twig',
                []);
        } else {
            $ok = $this->container->get('repository')->checkActivation($params['token']);
            $mensaje="";
            if($ok){
                $this->container->get('repository')->updateActivation($params['token']);
                $mensaje = "HA IDO BIEN";
                $user = $this->container->get('repository')->getUser($params['token']);
                $this->enviarCorreu(0, $user->email(), $user->username, '');
            }
            return $this->container->get('view')->render(
                $response,
                'activation.twig',
                [
                    'mensaje' => $mensaje
                ]);
        }
    }

}