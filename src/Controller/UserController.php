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


final class UserController
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container)
    {
        $this->container = $container;
    }

    function generateRandomString($length = 10) : string {
        do{
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
        }while($this->container->get('repository')->checkToken($randomString));

        return $randomString;
    }


    public function registerUser(Request $request, Response $response): Response
    {


            $data = $request->getParsedBody();

            $errors = [];

            $dominsAcceptats = [
                'salle.url.edu',
                'students.salle.url.edu',
                'salleurl.edu'
            ];

            $ok = true;

            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Not a valid email';
                $ok = false;
            }else{
                $parts = explode('@', $data['email']);
                $domain = array_pop($parts);
                if ( !in_array($domain, $dominsAcceptats)){
                    $errors['email'] = 'Not a valid email';
                    $ok = false;
                }else{
                    $errors['emailOk'] = $data['email'];
                }
            }

            if($data['password'] != $data['cpassword']){
                $errors['password'] = 'Passwords must match';
                $ok = false;
            }else{
                $uppercase = preg_match('@[A-Z]@', $data['password']);
                $lowercase = preg_match('@[a-z]@', $data['password']);
                $number    = preg_match('@[0-9]@', $data['password']);
                if( !$uppercase || !$lowercase || !$number  || strlen($data['password']) < 6) {
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

            if($this->container->get('repository')->checkIfEmailExists($data['email'])){
                $errors['email'] = 'Not a valid email';
                $ok = false;
            }

            if(!preg_match('/^[a-zA-Z0-9_]+$/',$data['username']) || $this->container->get('repository')->checkIfUsernameExists($data['username'])){
                $errors['username'] = 'Not a valid username';
                $ok = false;
            }

            $birthday = "";
            try {
                $birthday = new DateTime($data['birthday']);
            } catch (Exception $e) {
                $errors['date'] = 'Not a valid date';
                $ok = false;
            }


            $years = date_diff(new DateTime(), $birthday);
            if($years->y<18){
                $errors['date'] = 'You must be at least 18 years old!';
                $ok = false;
            }

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

            if($ok == true) {
                $token = $this->generateRandomString();

                $user = new User(
                    $data['username'],
                    $data['email'],
                    password_hash($data['password'], PASSWORD_DEFAULT),
                    $birthday,
                    $data['phone'] ?? '',
                    $token,
                    new DateTime()
                );

                $mail = new PHPMailer();
                $mail->isSMTP();

                $mail->SMTPDebug = SMTP::DEBUG_SERVER;

                $mail->Host = 'smtp.gmail.com';

                $mail->Port = 587;

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

                $mail->SMTPAuth = true;

                $mail->Username = 'pw1.rafa.victor.marti@gmail.com';

                $mail->Password = '12345678ASDFGH';

                $mail->setFrom('pw1.rafa.victor.marti@gmail.com', 'LSteam');

                $mail->addAddress($data['email'], $data['username']);

                $mail->Subject = 'PW2-LStream';

                $mail->isHTML(true);
                $emailBody = new CosEmail($token, $data['username']);
                $mail->Body = $emailBody->body();

                if (!$mail->send()) {
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                }

                $_SESSION['email'] = $data['username'];

                $ok = $this->container->get('repository')->save($user);
            }


            if($ok == true){
                return $response->withHeader('Location', '/')->withStatus(302);

            }else{

                return $this->container->get('view')->render(
                    $response,
                    'register.twig',
                    [
                        'data' => $data,
                        'errors' => $errors
                    ]
                );
            }


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
            $_SESSION['email'] = $data['username'];
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

    public function validateUser(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        return $this->container->get('view')->render(
            $response,
            'blank.twig',
            [
                'token' => $params['token']
            ]
        );
    }




    }