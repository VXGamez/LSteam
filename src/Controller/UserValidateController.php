<?php

namespace SallePW\SlimApp\Controller;



use DateTime;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use SallePW\SlimApp\Model\CosEmail;
use SallePW\SlimApp\Model\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use SallePW\SlimApp\Repository\MYSQLCallback;
use SallePW\SlimApp\Repository\MySQLRepository;
use SallePW\SlimApp\Repository\PDOSingleton;
use Slim\Views\Twig;
use function DI\value;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UserValidateController{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
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
            return $this->twig->render(
                $response,
                'landing.twig',
                []);
        } else {
            $ok = $this->mysqlRepository->checkActivation($params['token']);
            $mensaje="";
            if($ok){
                $this->mysqlRepository->updateActivation($params['token']);
                $mensaje = "HA IDO BIEN";
                $user = $this->mysqlRepository->getUser($params['token']);
                $this->enviarCorreu(0, $user->email(), $user->username, '');
            }
            return $this->twig->render(
                $response,
                'activation.twig',
                [
                    'mensaje' => $mensaje
                ]);
        }
    }

}