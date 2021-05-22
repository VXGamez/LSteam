<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use DateTime;
use Exception;
use SallePW\SlimApp\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;


final class UserRegisterController
{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    /************************************************
    * @Finalitat: Funció d'utilitat que ens permet generar un token aleatori per a la validació del registre
    ************************************************/
    function generateRandomString($length = 10) : string {
        do{
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
        }while($this->mysqlRepository->checkToken($randomString));

        return $randomString;
    }


    /************************************************
    * @Finalitat: Aquesta funció ens permet validar que el número de telèfon 
    * cumpleix amb les característiques dels telèfons Espanyols
    ************************************************/
    function validate($value): bool {

        $str = strval($value);


        str_replace(' ','',$str);
        if(str_starts_with($value, '+34')){
            $str = substr($str, 4);
        }

        return strlen($str) == 9 && preg_match('/^[679]{1}[0-9]{8}$/', $str);
    }

    
    /************************************************
    * @Finalitat: Aquesta funció permet controlar que el registre es pot efectuar correctament,
    * controlant cada camp que s'omple en el formulari
    ************************************************/
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

            if($this->mysqlRepository->checkIfEmailExists($data['email'])){
                $errors['email'] = 'Not a valid email';
                $ok = false;
            }

            if(!preg_match('/^[a-zA-Z0-9_]+$/',$data['username']) || $this->mysqlRepository->checkIfUsernameExists($data['username'])){
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

                if (!$this->validate($data['phone'])) {
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
                    new DateTime(),
                    0.0,
                    ""
                );

                UserValidateController::enviarCorreu(1, $data['email'], $data['username'], $token);

                $ok = $this->mysqlRepository->save($user);
            }


            if($ok == true){
                return $response->withHeader('Location', '/')->withStatus(302);

            }else{

                return $this->twig->render(
                    $response,
                    'register.twig',
                    [
                        'data' => $data,
                        'errors' => $errors
                    ]
                );
            }
    }
}