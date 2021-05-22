<?php

namespace SallePW\SlimApp\Controller;


use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SallePW\SlimApp\Repository\MYSQLCallback;
use Slim\Views\Twig;

final class ProfileController{
    private Twig $twig;
    private MYSQLCallback $mysqlRepository;

    public function __construct(Twig $twig, MYSQLCallback $repository)
    {
        $this->twig = $twig;
        $this->mysqlRepository = $repository;
    }

    function validate($value): bool {

        $str = strval($value);


        str_replace(' ','',$str);
        if(str_starts_with($value, '+34')){
            $str = substr($str, 4);
        }

        return strlen($str) == 9 && preg_match('/^[679]{1}[0-9]{8}$/', $str);
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $errors = [];
        if(isset($_SESSION['passErrors'])){
            $errors = $_SESSION['passErrors'];
            unset($_SESSION['passErrors']);
        }
        $user = $this->mysqlRepository->getUser($_SESSION['email']);
        if($user->password() != "TODO MAL"){
            $history = $this->mysqlRepository->getPurchaseHistory($_SESSION['email']);
            return $this->twig->render($response,'profile.twig',[
                'user' => $user,
                'wallet' => $user->getWallet(),
                'errors' => $errors,
                'uuid' => $user->getUuid(),
                'history' => $history
            ]);
        }else{
            $_SESSION['isRedirected'] = "An error occured with your Profile!";
            return $response->withHeader('Location', '/')->withStatus(302);
        }
    }

    public function changeProfile(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $ok = true;
        $errors = [];


        if(isset($_FILES["profilepic"])){
            $tmpName = $_FILES['profilepic']['tmp_name'];
            if ( $tmpName != NULL)  {
                $fileinfo = @getimagesize($tmpName);
                $width = $fileinfo[0];
                $heigth = $fileinfo[1];

                $allowed_extensions = array (
                    "png",
                    "jpg"
                );

                $file_extension = pathinfo($_FILES["profilepic"]["name"], PATHINFO_EXTENSION);

                if(!in_array($file_extension, $allowed_extensions)){
                    $errors['image'] = 'Only PNG or JPG images are accepted';
                    $ok = false;
                } else if($_FILES["profilepic"]["size"] > 1000000){
                    $errors['image'] = 'Image cannot weigh more than 1MB';
                    $ok = false;
                } else if($width != "500" || $heigth != "500"){
                    $errors['image'] = 'Size image is not 500x500';
                    $ok = false;
                } else {
                    $date = new DateTime();
                    $result = $date->format('Y-m-d H:i:s');
                    $nombreImagen = $_FILES["profilepic"]["name"] . $result;
                    $uuid_tmp = hash ("sha256" , $nombreImagen ,false );
                    $uuid = $uuid_tmp .'.'. $file_extension;
                    $target = __DIR__ . '/../../public/uploads/' . basename($uuid) ;
                    if(move_uploaded_file($tmpName, $target)){
                        $this->mysqlRepository->updateUuid($_SESSION['email'], $uuid);
                        $_SESSION['uuid'] = $uuid;
                    } else {
                        $errors['image'] = 'Error uploading image';
                        $ok = false;
                    }
                }
                if ($ok) {
                    $errors['image'] = 'Image saved successfully';
                }
            }

        } else {
            $errors['image'] = 'ERROL ';
        }

        $ok2 = true;

        if(strlen($data['phone'])>0){

            if (!$this->validate($data['phone'])) {
                $errors['phone'] = 'This is not a valid Spanish number';
                $ok2 = false;
            }
            $phone = $data['phone'];
            if($ok2 && !str_starts_with($data['phone'], '+34')){
                $phone = "+34 ".$phone;
            }
            $data['phone'] = $phone;

        }

       if ($ok2) {
           $this->mysqlRepository->updatePhone($_SESSION['email'], $data['phone']);
           $errors['phone'] = 'Phone number saved successfully';
       }



       $user = $this->mysqlRepository->getUser($_SESSION['email']);
        $history = $this->mysqlRepository->getPurchaseHistory($_SESSION['email']);
       return $this->twig->render($response,'profile.twig',[
           'user' => $user,
           'errors' => $errors,
           'wallet' => $user->getWallet(),
           'uuid' => $user->getUuid(),
           'history' => $history
       ]);

    }

    public function changePassword(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $currPass = $data['currPass'];
        $errors = [];
        $newPass = $data['newPass'];
        $repPass = $data['repPass'];
        $ok = true;

        if($newPass != $repPass){
            $errors['password'] = 'Passwords must match';
            $ok = false;
        }else{
            $uppercase = preg_match('@[A-Z]@', $newPass);
            $lowercase = preg_match('@[a-z]@', $newPass);
            $number    = preg_match('@[0-9]@', $newPass);
            if( !$uppercase || !$lowercase || !$number  || strlen($newPass)< 6) {
                $ok = false;
                if(!$uppercase){
                    $errors['password'] = 'Not a valid password';
                }else if(!$lowercase){
                    $errors['password'] = 'Not a valid password';
                }else if(!$number){
                    $errors['password'] = 'Not a valid password';
                }else if(strlen($newPass) < 6){
                    $errors['password'] = 'Not a valid password';
                }else{
                    $errors['password'] = 'There is an error with the password';
                }
            }
        }

        if($ok){
            $user = $this->mysqlRepository->getUser($_SESSION['email']);
            if($user->password() != "TODO MAL") {
                if (!password_verify($currPass, $user->password())) {
                    $errors['password'] = 'Current password is not correct';
                    $ok = false;
                }else{
                    if ($newPass == $currPass) {
                        $errors['password'] = 'New password must not match your old password';
                        $ok = false;
                    }else{
                        $contraFinal = password_hash($newPass, PASSWORD_DEFAULT);
                        $this->mysqlRepository->updatePass($_SESSION['email'], $contraFinal);
                        $errors['password'] = 'Password changed succesfully!'; 
                    }
                }
            }
        }

        $_SESSION['passErrors'] = $errors;
        return $response->withHeader('Location', '/profile#changePassword')->withStatus(302);
       
    }
}
