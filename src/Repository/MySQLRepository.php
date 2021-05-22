<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use DateTime;
use PDO;
use SallePW\SlimApp\Model\User;

/************************************************
* Classe encarregada de fer les peticions a la base de dades. Implementa el MySQLCallback
************************************************/
final class MySQLRepository implements MYSQLCallback
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDOSingleton $database;

    //Reb el PDOSingleton de les dependencies
    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }


    /************************************************
    * Aquesta funció té com a finalitat desar un usuari a la base de dades
    ************************************************/
    public function save(User $user): bool{
        $statement = $this->database->connection()->prepare('INSERT INTO User(username, email, password, birthday, phone, activated, token, created_at) VALUES(:username,:email, :password, :birthday,:phone,:activated, :token, :created_at)');
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();
        $birthday = $user->birthday()->format(self::DATE_FORMAT);
        $phone = $user->phone();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $activated = FALSE;

        $token = $user->token();

        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('birthday', $birthday, PDO::PARAM_STR);
        $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        $statement->bindParam('activated', $activated, PDO::PARAM_BOOL);
        $statement->bindParam('token', $token, PDO::PARAM_STR);
        $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);

        $ok = $statement->execute();

        return $ok;
    }

    /************************************************
    * Aquesta funció té com a finalitat comprovar si el correu i la contrasenya rebuts pertanyen a un usuari
    ************************************************/
    public function validateUser($email, $password): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE email=:email AND password=:password');
        $stmt->bindParam('email', $email, PDO::PARAM_STR);
        $stmt->bindParam('password', $password, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $ok = false;
        }

        return $ok;
    }

    /************************************************
    * Aquesta funció té com a finalitat saber si es pot enviar una request a aquest usuari, i sinó, perquè.
    ************************************************/
    public function requestIsValid($myUserId, $friendUserId): int{
        $ok = 0;

        $stmt = $this->database->connection()->prepare('SELECT pending, request_id FROM Request WHERE user_origen= :userid2 AND user_desti= :userid1');
        $stmt->bindParam('userid1', $myUserId, PDO::PARAM_INT);
        $stmt->bindParam('userid2', $friendUserId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() != 0){
            if ($row['pending'] == 1) {
                $this->solicitudAceptada($row['request_id']);
                $this->addNewFriendship($row['request_id']);
                $ok = 3;
            }
        }
        
        if ($ok == 0){
            
            $stmt = $this->database->connection()->prepare('SELECT id FROM `Friend-User` WHERE (user1_id= :userid1 OR user1_id= :userid2) AND (user2_id= :userid1 OR user2_id= :userid2)');
            $stmt->bindParam('userid1', $myUserId, PDO::PARAM_INT);
            $stmt->bindParam('userid2', $friendUserId, PDO::PARAM_INT);
            $stmt->execute();
        
            if($stmt->rowCount() != 0){
                $ok = 1; 
            } 

        }

        if ($ok == 0){
            
            $stmt = $this->database->connection()->prepare('SELECT pending FROM Request WHERE user_origen= :userid1 AND user_desti= :userid2');
            $stmt->bindParam('userid1', $myUserId, PDO::PARAM_INT);
            $stmt->bindParam('userid2', $friendUserId, PDO::PARAM_INT);
            $stmt->execute();

            if($stmt->rowCount() != 0){
                $ok = 2;
            }

        }
               
        return $ok;
    }


    /************************************************
    * Retorna el usuari amb el correu rebut, a vegades es fa servir rebent el nom de usuari o el token donat que qualsevol d'aquests 3 elements és únic.
    ************************************************/
    public function getUser($usrEmail): User{


        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE (email=:email OR username=:usr OR token=:token) AND activated = 1 ');
        $stmt->bindParam('email', $usrEmail, PDO::PARAM_STR);
        $stmt->bindParam('usr', $usrEmail, PDO::PARAM_STR);
        $stmt->bindParam('token', $usrEmail, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            $row = $stmt->fetch();

            $u = new User(
                $row['username'],
                $row['email'],
                $row['password'],
                new DateTime($row['birthday']),
                $row['phone'],
                $row['token'],
                new DateTime($row['created_at']),
                floatval($row['wallet']),
                $row['uuid']
            );

        }else{
            $u = new User(
                "TODOMAL",
                "TODOMAL",
                "TODOMAL",
                new DateTime(),
                "TODOMAL",
                "TODOMAL",
                new DateTime(),
                0.0,
                "TODOMAL"
            );

        }

        return $u;
    }

    /************************************************
    * Comprova si el correu ja s'ha fet servir per algun usuari a la base de dades
    ************************************************/
    public function checkIfEmailExists($email): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE email= :email');
        $stmt->bindParam('email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $ok = false;
        }

        return $ok;
    }

    /************************************************
    * Comprova si el nom d'usuari ja s'ha fet servir per algun usuari a la base de dades
    ************************************************/
    public function checkIfUsernameExists($usr): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE username= :usr');
        $stmt->bindParam('usr', $usr, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $ok = false;
        }

        return $ok;
    }

    /************************************************
    * Comprova si el token ja s'ha fet servir per algun usuari a la base de dades
    ************************************************/
    public function checkToken($token): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE token= :tkn');
        $stmt->bindParam('tkn', $token, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $ok = false;
        }

        return $ok;
    }

    /************************************************
    * Comprova si el parametre activated de la taula User esta a TRUE, i per tant, aquest token ja esta activat
    ************************************************/
    public function checkActivation($token): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT activated FROM User WHERE token= :tkn');
        $stmt->bindParam('tkn', $token, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            $row = $stmt->fetch();
            if($row['activated'] == true){
                $ok = false;
            }
        }else{
            $ok=false;
        }

        return $ok;
    }

    /************************************************
    * Actualitza el parametre activated a true i posa 50€ a la wallet
    ************************************************/
    public function updateActivation($token) {
        $stmt = $this->database->connection()->prepare('UPDATE User SET activated = true, wallet = 50 WHERE token= :tkn');
        $stmt->bindParam('tkn', $token, PDO::PARAM_STR);
        $stmt->execute();
    }

    /************************************************
    * Actualitza la contrasenya amb la nova contrasenya
    ************************************************/
    public function updatePass($user, $newPass) {
        $id = $this->getUserId($user);
        $stmt = $this->database->connection()->prepare('UPDATE User SET password = :pass WHERE id = :ide');
        $stmt->bindParam('pass', $newPass, PDO::PARAM_STR);
        $stmt->bindParam('ide', $id, PDO::PARAM_INT);
        $stmt->execute();

    }

    /************************************************
    * Actualitza el número de telèfon amb el nou rebut
    ************************************************/
    public function updatePhone($user, $phone) {
        $stmt = $this->database->connection()->prepare('UPDATE User SET phone = :phone WHERE username= :usrname');
        $stmt->bindParam('phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $user, PDO::PARAM_STR);
        $stmt->execute();
    }

    /************************************************
    * Actualitza el camp uuid, per quan es canvii la foto de perfil
    ************************************************/
    public function updateUuid($email, $uuid){
        $stmt = $this->database->connection()->prepare('UPDATE User SET uuid = :uid WHERE username= :usrname');
        $stmt->bindParam('uid', $uuid, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $email, PDO::PARAM_STR);
        $stmt->execute();
    }


    /************************************************
    * Actualitza el valor de la columna wallet quan el usuari insereix més diners o en consumeix.
    ************************************************/
    public function updateWallet($wallet, $email){
        $stmt = $this->database->connection()->prepare('UPDATE User SET wallet = :wallet WHERE username= :usrname');
        $stmt->bindParam('wallet', $wallet, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $email, PDO::PARAM_STR);
        $stmt->execute();
        $_SESSION['wallet'] = $wallet;
    }

    /************************************************
    * Retorna el id del usuari que reb la funció. Novament comprova que sigui el correu o el nom d'usuari ja que els dos son únics i aixi podem fer servir la funció 
    ************************************************/
    public function getUserId($usrEmail): int{
        $stmt = $this->database->connection()->prepare('SELECT id FROM User WHERE email= :email OR username= :usrname');
        $stmt->bindParam('email', $usrEmail, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $usrEmail, PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            $rowID = $stmt->fetch();
            $id = $rowID['id'];
            return (int)$id;
        }else{
            return -1;
        }
    }

    /************************************************
    * Retorna el històric de compras del usuari rebut
    ************************************************/
    public function getPurchaseHistory($usrEmail){

        $id = $this->getUserId($usrEmail);

        $stmt = $this->database->connection()->prepare('SELECT title, sellPrice, dateBought, g.storeID, g.thumb, g.dealRating FROM `User-Game-Bought` AS gb INNER JOIN Game AS g ON gb.gameID = g.id WHERE userID = :uid ORDER BY dateBought DESC');
        $stmt->bindParam('uid', $id, PDO::PARAM_INT);
        $stmt->execute();

        $u = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u, $row);
        }
        return $u;
    }

    /************************************************
    * Retorna tots els jocs de la wishlist
    ************************************************/
    public function getWishHistory($usrEmail){
        
        $id = $this->getUserId($usrEmail);

        $stmt = $this->database->connection()->prepare('SELECT gb.gameID, gb.sellPrice as salePrice, gb.normalPrice as normalPrice, g.title, g.storeID, g.thumb, g.dealRating FROM `User-Game-Wishlist` AS gb INNER JOIN Game AS g ON gb.gameID = g.id WHERE userID = :uid');
        $stmt->bindParam('uid', $id, PDO::PARAM_INT);
        $stmt->execute();

        $u = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u, $row);
        }
        return $u;
    }

    /************************************************
    * Ens retorna un array associatiu dels jocs que té tant a la wishlist com comprats.
    ************************************************/
    public function getUserGames($usrEmail) {

        $id = $this->getUserId($usrEmail);

        $stmt = $this->database->connection()->prepare('SELECT gameID FROM `User-Game-Bought` WHERE userID = :uid');
        $stmt->bindParam('uid', $id, PDO::PARAM_INT);
        $stmt->execute();
        $u = [];
        $u['comprados'] = [];
        $tmp=$stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($tmp as &$value){
            foreach($value as &$kk){
                array_push($u['comprados'], $kk);
            }
        }

        $stmt = $this->database->connection()->prepare('SELECT gameID FROM `User-Game-Wishlist` WHERE userID = :uid');
        $stmt->bindParam('uid', $id, PDO::PARAM_INT);
        $stmt->execute();
        $u['fav'] = [];
        $tmp=$stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tmp as &$value){
            foreach($value as &$kk){
                array_push($u['fav'], $kk);
            }
        }

        return $u;
    }

    /************************************************
    * Compra el joc rebut amb el id rebut, i a nom del usuari amb el email rebut. Si esta a la wishlist el treu de la wishlist
    ************************************************/
    public function buyGame($email, $gameID, $data){

        $stmt = $this->database->connection()->prepare('SELECT id FROM `Game` WHERE id = :gid ');
        $stmt->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt->execute();

        $userid = $this->getUserId($email);
        $date = new DateTime();
        $comprahte = $date->format(self::DATE_FORMAT);
        $storeid = $data['storeID'];
        $sellPrice = $data['salePrice'];
        $title = $data['title'];
        $thumb = $data['thumb'];
        $dealRating = (String)$data['dealRating'];

        if($stmt->rowCount() == 0){
            $statement = $this->database->connection()->prepare('INSERT INTO Game(id, storeID,title,thumb,dealRating) VALUES(:gameid,:storeID, :title, :thumb, :dealRating)');
            $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
            $statement->bindParam('storeID', $storeid, PDO::PARAM_INT);
            $statement->bindParam('title', $title, PDO::PARAM_STR);
            $statement->bindParam('thumb', $thumb, PDO::PARAM_STR);
            $statement->bindParam('dealRating', $dealRating, PDO::PARAM_STR);
            $statement->execute();
        }

        $stmt2 = $this->database->connection()->prepare('SELECT id FROM `User-Game-Wishlist` WHERE gameID = :gid AND userID = :uid');
        $stmt2->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt2->bindParam('uid', $userid, PDO::PARAM_INT);
        $stmt2->execute();

        if($stmt2->rowCount() != 0){
            $stmt3 = $this->database->connection()->prepare('DELETE FROM `User-Game-Wishlist` WHERE gameID = :gid AND userID = :uid');
            $stmt3->bindParam('gid', $gameID, PDO::PARAM_INT);
            $stmt3->bindParam('uid', $userid, PDO::PARAM_INT);
            $stmt3->execute();
        }

        $statement = $this->database->connection()->prepare('INSERT INTO `User-Game-Bought`(gameID, userID,sellPrice,  dateBought) VALUES(:gameid,:userid, :sellPrice, :dataa)');
        $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
        $statement->bindParam('userid', $userid, PDO::PARAM_INT);
        $statement->bindParam('sellPrice', $sellPrice, PDO::PARAM_STR);
        $statement->bindParam('dataa', $comprahte, PDO::PARAM_STR);
    
        $statement->execute();


    }

    /************************************************
    * Afegeix un element a la wishlist si no ha estat afegit ja. 
    ************************************************/
    public function getWish($email, $gameID, $data){

        $stmt = $this->database->connection()->prepare('SELECT gameID FROM `User-Game-Wishlist` WHERE gameID = :gid ');
        $stmt->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt->execute();

        $stmt2 = $this->database->connection()->prepare('SELECT gameID FROM `User-Game-Bought` WHERE gameID = :gid ');
        $stmt2->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt2->execute();


        if($stmt->rowCount() == 0 && $stmt2->rowCount() == 0){

            $userID = $this->getUserId($email);
            $storeid = $data['storeID'];
            $sellPrice = $data['salePrice'];
            $normal = $data['normalPrice'];
            $title = $data['title'];
            $thumb = $data['thumb'];
            $dealRating = (String)$data['dealRating'];

            $stmt2 = $this->database->connection()->prepare('SELECT id FROM Game WHERE id = :gid ');
            $stmt2->bindParam('gid', $gameID, PDO::PARAM_INT);
            $stmt2->execute();

            if($stmt2->rowCount() == 0){
                $statement = $this->database->connection()->prepare('INSERT INTO Game(id, storeID,title,thumb,dealRating) VALUES(:gameid,:storeID, :title, :thumb, :dealRating)');
                $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
                $statement->bindParam('storeID', $storeid, PDO::PARAM_INT);
                $statement->bindParam('title', $title, PDO::PARAM_STR);
                $statement->bindParam('thumb', $thumb, PDO::PARAM_STR);
                $statement->bindParam('dealRating', $dealRating, PDO::PARAM_STR);
                $statement->execute();
            }

           
            $statement = $this->database->connection()->prepare('INSERT INTO `User-Game-Wishlist`(gameID, userID, sellPrice, normalPrice) VALUES(:gameid,:userid, :sell, :normal)');
            $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
            $statement->bindParam('userid', $userID, PDO::PARAM_INT);
            $statement->bindParam('sell', $sellPrice, PDO::PARAM_STR);
            $statement->bindParam('normal', $normal, PDO::PARAM_STR);
            $statement->execute();
        }

       

    }

    /************************************************
    * Elimina de la wishlist
    ************************************************/
    public function deleteWish($email, $gameID){


        $userID = $this->getUserId($email);

        $stmt2 = $this->database->connection()->prepare('SELECT id FROM `User-Game-Wishlist` WHERE gameID = :gid AND userID = :uid');
        $stmt2->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt2->bindParam('uid', $userID, PDO::PARAM_INT);
        $stmt2->execute();

        if($stmt2->rowCount() != 0){
            $stmt3 = $this->database->connection()->prepare('DELETE FROM `User-Game-Wishlist` WHERE gameID = :gid AND userID = :uid');
            $stmt3->bindParam('gid', $gameID, PDO::PARAM_INT);
            $stmt3->bindParam('uid', $userID, PDO::PARAM_INT);
            $stmt3->execute();

            $stmt4 = $this->database->connection()->prepare('DELETE FROM `Game` WHERE id = :gid');
            $stmt4->bindParam('gid', $gameID, PDO::PARAM_INT);
            $stmt4->execute();
        }


    }

    /************************************************
    * Ens retorna els amics del usuari amb lo necessari per la vista com la data de afegit o el total de jocs que té cada un.
    ************************************************/
    public function getFriends($usrEmail, $flag){
        $id = $this->getUserId($usrEmail);


        if($flag == 0){
            $stmt = $this->database->connection()->prepare('SELECT user1_id AS id, fecha FROM `Friend-User` WHERE user2_id = :id UNION SELECT user2_id AS id, fecha FROM `Friend-User` WHERE user1_id = :id');
        }else if($flag == 1){
            $stmt = $this->database->connection()->prepare('SELECT request_id, user_origen AS id, fecha FROM `Request` WHERE user_desti = :id AND pending = TRUE');
        }
        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $u = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u, $row);
        }

        for($i = 0; $i < count($u); $i++){
            $identificador = (int)$u[$i]["id"];
            $stmt = $this->database->connection()->prepare('SELECT COUNT(gameID) AS totalJuegos FROM `User-Game-Bought` WHERE userID = :id');
            
            $stmt->bindParam('id', $identificador, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $u[$i]['totalJuegos'] = $row['totalJuegos'];

            $stmt = $this->database->connection()->prepare('SELECT username, uuid FROM `User` WHERE id = :id');
            $stmt->bindParam('id', $identificador, PDO::PARAM_INT);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $u[$i]['username'] = $row['username'];
                $u[$i]['uuid'] = $row['uuid'];
            }
        }
        
        return $u;
    }

    /************************************************
    * Afegeix la request a la taula de requests
    ************************************************/
    public function addRequest($idUsr, $idFriend){
        $pending = 1;
        $statement = $this->database->connection()->prepare('INSERT INTO `Request`(user_origen, user_desti,fecha, pending) VALUES(:id1, :id2, :fecha, :pending)');
        $statement->bindParam('id1', $idUsr, PDO::PARAM_INT);
        $statement->bindParam('id2', $idFriend, PDO::PARAM_INT);
        $fecha = new DateTime();
        $datamen = $fecha->format(self::DATE_FORMAT);
        $statement->bindParam('fecha', $datamen, PDO::PARAM_STR);
        $statement->bindParam('pending', $pending, PDO::PARAM_INT);
        $statement->execute();
    }

    /************************************************
    * Comprova si el usuari esta a la request
    ************************************************/
    public function userInRequest($id, $requestID) {
        $exists = false;

        $stmt = $this->database->connection()->prepare('SELECT * FROM `Request` WHERE request_id = :rid AND user_desti = :user');
        $stmt->bindParam('rid', $requestID, PDO::PARAM_INT);
        $stmt->bindParam('user', $id, PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $exists = true;
        }

        return $exists;
    }

    /************************************************
    * Accepta una solicitud
    ************************************************/
    public function solicitudAceptada($requestID) {
        $pending = 0;
        $statement = $this->database->connection()->prepare('UPDATE Request SET pending = :pending WHERE request_id = :id');
        $statement->bindParam('id', $requestID, PDO::PARAM_INT);
        $statement->bindParam('pending', $pending, PDO::PARAM_INT);
        $statement->execute();
    }

    /************************************************
    * Afegeix una persona a amics
    ************************************************/
    public function addNewFriendship($requestID) {
        
        $stmt = $this->database->connection()->prepare('SELECT user_origen, user_desti FROM `Request` WHERE request_id = :id');
        $stmt->bindParam('id', $requestID, PDO::PARAM_INT);
        $stmt->execute();

        $users = $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        $origen = (int)$users['user_origen'];
        $desti = (int)$users['user_desti'];

        $date = new DateTime();
        $dateAccepted = $date->format(self::DATE_FORMAT);

        $statement = $this->database->connection()->prepare('INSERT INTO `Friend-User`(user1_id, user2_id, fecha) VALUES (:id1, :id2, :fecha)');
        $statement->bindParam('id1', $origen , PDO::PARAM_INT);
        $statement->bindParam('id2', $desti , PDO::PARAM_INT);
        $statement->bindParam('fecha', $dateAccepted, PDO::PARAM_STR);
        $statement->execute();
    }

}