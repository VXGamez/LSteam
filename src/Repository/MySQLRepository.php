<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use DateTime;
use PDO;
use SallePW\SlimApp\Model\Search;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Repository\PDOSingleton;

final class MySQLRepository implements MYSQLCallback
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }


    public function save(User $user): bool
    {
        $query = <<<'QUERY'
        INSERT INTO User(username, email, password, birthday, phone, activated, token, created_at)
        VALUES(:username,:email, :password, :birthday,:phone,:activated, :token, :created_at)
QUERY;
        $statement = $this->database->connection()->prepare($query);
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

    public function checkActivation($token): bool{
        $ok = true;
        $validation = 0;

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

    public function updateActivation($token) {
        $stmt = $this->database->connection()->prepare('UPDATE User SET activated = true, wallet = 50 WHERE token= :tkn');
        $stmt->bindParam('tkn', $token, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updatePass($user, $newPass) {
        $stmt = $this->database->connection()->prepare('UPDATE User SET password = :pass WHERE username= :usrname');
        $stmt->bindParam('pass', $newPass, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $user, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updatePhone($user, $phone) {
        $stmt = $this->database->connection()->prepare('UPDATE User SET phone = :phone WHERE username= :usrname');
        $stmt->bindParam('phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $user, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateUuid($email, $uuid){
        $stmt = $this->database->connection()->prepare('UPDATE User SET uuid = :uid WHERE username= :usrname');
        $stmt->bindParam('uid', $uuid, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $email, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateWallet($wallet, $email){
        $stmt = $this->database->connection()->prepare('UPDATE User SET wallet = :wallet WHERE username= :usrname');
        $stmt->bindParam('wallet', $wallet, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $email, PDO::PARAM_STR);
        $stmt->execute();
        $_SESSION['wallet'] = $wallet;
    }


    public function getUserId($usrEmail): int{
        $stmt = $this->database->connection()->prepare('SELECT id FROM User WHERE email= :email OR username= :usrname');
        $stmt->bindParam('email', $usrEmail, PDO::PARAM_STR);
        $stmt->bindParam('usrname', $usrEmail, PDO::PARAM_STR);
        $stmt->execute();
        $rowID = $stmt->fetch();
        $id = $rowID['id'];
        return (int)$id;
    }

    public function getPurchaseHistory($usrEmail){
        /*$id = $this->getUserId($usrEmail);

        $stmt = $this->database->connection()->prepare('SELECT title, sellPrice, dateBought FROM `User-Game-Bought` AS gb INNER JOIN Game AS g ON gb.gameID = g.id WHERE userID = ? ORDER BY dateBought DESC');
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $u = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u, $row);
        }
        return $u;*/

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

    public function getWishHistory($usrEmail){
        
        $id = $this->getUserId($usrEmail);

        $stmt = $this->database->connection()->prepare('SELECT gb.gameID, gb.sellPrice as salePrice, g.title, g.storeID, g.thumb, g.dealRating FROM `User-Game-Wishlist` AS gb INNER JOIN Game AS g ON gb.gameID = g.id WHERE userID = :uid');
        $stmt->bindParam('uid', $id, PDO::PARAM_INT);
        $stmt->execute();

        $u = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u, $row);
        }
        return $u;
    }

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

    public function buyGame($email, $gameID, $data){

        $stmt = $this->database->connection()->prepare('SELECT id FROM `Game` WHERE id = :gid ');
        $stmt->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt->execute();

        $userid = $this->getUserId($email);
        $date = new DateTime();
        $comprahte = $date->format(self::DATE_FORMAT);
        $storeid = $data['storeID'];
        $sellPrice = (String)$data['salePrice'];
        $title = $data['title'];
        $thumb = $data['thumb'];
        $dealRating = (String)$data['dealRating'];

        if($stmt->rowCount() == 0){
          
            $query = <<<'QUERY'
            INSERT INTO Game(id, storeID,title,thumb,dealRating)
            VALUES(:gameid,:storeID, :title, :thumb, :dealRating)
    QUERY;
            
            $statement = $this->database->connection()->prepare($query);
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

        $query = <<<'QUERY'
        INSERT INTO `User-Game-Bought`(gameID, userID,sellPrice,  dateBought)
        VALUES(:gameid,:userid, :sellPrice, :dataa)
QUERY;
        $statement = $this->database->connection()->prepare($query);
    

        $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
        $statement->bindParam('userid', $userid, PDO::PARAM_INT);
        $statement->bindParam('sellPrice', $sellPrice, PDO::PARAM_STR);
        $statement->bindParam('dataa', $comprahte, PDO::PARAM_STR);
    
        $statement->execute();


    }

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
            $sellPrice = (String)$data['salePrice'];
            $title = $data['title'];
            $thumb = $data['thumb'];
            $dealRating = (String)$data['dealRating'];

            $query = <<<'QUERY'
            INSERT INTO Game(id, storeID,title,thumb,dealRating)
            VALUES(:gameid,:storeID, :title, :thumb, :dealRating)
    QUERY;

            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
            $statement->bindParam('storeID', $storeid, PDO::PARAM_INT);
            $statement->bindParam('title', $title, PDO::PARAM_STR);
            $statement->bindParam('thumb', $thumb, PDO::PARAM_STR);
            $statement->bindParam('dealRating', $dealRating, PDO::PARAM_STR);
            $statement->execute();

            $query = <<<'QUERY'
            INSERT INTO `User-Game-Wishlist`(gameID, userID, sellPrice)
            VALUES(:gameid,:userid, :sell)
    QUERY;

            
            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
            $statement->bindParam('userid', $userID, PDO::PARAM_INT);
            $statement->bindParam('sell', $sellPrice, PDO::PARAM_INT);
            $statement->execute();
        }

       

    }

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


    public function getFriends($usrEmail){

        $id = $this->getUserId($usrEmail);

        //Falta coger uuid de ese user
        $stmt = $this->database->connection()->prepare('SELECT user1_id FROM `Friend-User` WHERE user2_id = :id
                                                               UNION
                                                               SELECT user2_id FROM `Friend-User` WHERE user1_id = :id');

        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $u = [];

        $i = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u[$i]['id'], $row);
            $i++;
        }

        $stmt = $this->database->connection()->prepare('SELECT date_accepted FROM `Friend-User` WHERE user2_id = :id
                                                              UNION  date_accepted FROM `Friend-User` WHERE user1_id = :id'); //Para tener resultados ordenados

        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $i = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u[$i]['date_accepted'], $row);
            $i++;
        }

        for($i = 0; $i < count($u); $i++){
            $stmt = $this->database->connection()->prepare('SELECT COUNT(gameID) AS totalJuegos FROM `User-Game-Bought` WHERE userID = :id');

            $stmt->bindParam('id', $id[$i]['id'], PDO::PARAM_INT);
            $stmt->execute();

            $u[$i]['totalJuegos'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $u;
    }


    public function checkAreFriends($idUsr, $idFriend){
        $bool = true;

        $stmt = $this->database->connection()->prepare('SELECT id FROM `Friend-User` WHERE (user1_id = :id1 OR user1_id = :id2) AND (user2_id = :id1 OR user2_id = :id2)');
        $stmt->bindParam('id1', $idUsr, PDO::PARAM_INT);
        $stmt->bindParam('id2', $idFriend, PDO::PARAM_INT);

        $stmt->execute();

        if($stmt->rowCount() != 0){ //Ya son amigos
            $bool = false;
        }

        return $bool;
    }

    public function checkRequest($idUsr, $idFriend) {
        $bool = true;

        $stmt = $this->database->connection()->prepare('SELECT id_request FROM `Request` WHERE (user1_id = :id1 OR user1_id = :id2) AND (user2_id = :id1 OR user2_id = :id2)');
        $stmt->bindParam('id1', $idUsr, PDO::PARAM_INT);
        $stmt->bindParam('id2', $idFriend, PDO::PARAM_INT);

        $stmt->execute();

        if($stmt->rowCount() != 0){ //Ya existia o ha existido y ha sido denegada esta solicitud
            $bool = false;
        }

        return $bool;
    }

    public function addRequest($idUsr, $idFriend){
        $query = <<<'QUERY'
            INSERT INTO `Request`(user1_id, user2_id, pending)
            VALUES(:id1,:id2,:pending)
    QUERY;

        $pending = true;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id1', $idUsr, PDO::PARAM_INT);
        $statement->bindParam('id2', $idFriend, PDO::PARAM_INT);
        $statement->bindParam('pending', $pending, PDO::PARAM_INT);
        $statement->execute();

    }

    public function getRequests($email){
        $id = $this->getUserId($email);

        //Falta coger uuid de los usuarios que me han enviado peticion
        $stmt = $this->database->connection()->prepare('SELECT user1_id FROM `Request` WHERE user2_id = :id AND pending = true
                                                               UNION
                                                               SELECT user2_id FROM `Request` WHERE user1_id = :id AND pending = true');

        $stmt->bindParam('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $u = [];

        $i = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($u[$i]['id'], $row);
            $i++;
        }

        //No se que queremos mostrar en la lista de solicitudes recibidas
        for($i = 0; $i < count($u); $i++){
            $stmt = $this->database->connection()->prepare('SELECT COUNT(gameID) AS totalJuegos FROM `User-Game-Bought` WHERE userID = :id');

            $stmt->bindParam('id', $id[$i]['id'], PDO::PARAM_INT);
            $stmt->execute();

            $u[$i]['totalJuegos'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $u;
    }

    public function checkRequestExists($idUser, $idFriend) {
        $anado = false;

        $stmt = $this->database->connection()->prepare('SELECT id_request FROM `Request` WHERE (user1_id = :id1 OR user1_id = :id2) AND (user2_id = :id1 OR user2_id = :id2) AND pending = false'); //pending = false significa que en algun momento la han rechazado
        $stmt->bindParam('id1', $idUsr, PDO::PARAM_INT);
        $stmt->bindParam('id2', $idFriend, PDO::PARAM_INT);

        $stmt->execute();

        if($stmt->rowCount() != 0){ //Quiere decir que esta solicitud ya existia
            //Queremos aceptar directamente el amigo

            /*Eliminamos solicitud (pending = false)*/
            $query = <<<'QUERY' 
            INSERT INTO `Request`(user1_id, user2_id, pending)
            VALUES(:id1,:id2,:pending) WHERE id_request = $stmt->request_id //Mirar sintaxis
    QUERY;

            $pending = false;

            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('id1', $idUsr, PDO::PARAM_INT);
            $statement->bindParam('id2', $idFriend, PDO::PARAM_INT);
            $statement->bindParam('pending', $pending, PDO::PARAM_INT);
            $statement->execute();

            /*Aceptamos la solicitud*/
            $date = new DateTime();
            $dateAccepted = $date->format(self::DATE_FORMAT);
            //$dateAccepted = time(); //Obtenemos hora a la que se acepta la solicitud

            $query = <<<'QUERY' 
            INSERT INTO `Friend-User`(user1_id, user2_id, dateAccepted)
            VALUES(:id1,:id2,:date)
    QUERY;

            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('id1', $idUser, PDO::PARAM_INT);
            $statement->bindParam('id2', $idFriend, PDO::PARAM_INT);
            $statement->bindParam('date', $dateAccepted, PDO::PARAM_INT);
            $statement->execute();

            $anado = true;
        }
        return $anado;
    }

    public function userInRequest($id, $requestID) {
        $exists = false;

        $stmt = $this->database->connection()->prepare('SELECT id_request FROM `Request` WHERE id = :rid AND (user1_id = :user OR user2_id = :user)');
        $stmt->bindParam('rid', $requestID, PDO::PARAM_INT);
        $stmt->bindParam('user', $id, PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $exists = true;
        }

        return $exists;
    }

    public function solicitudAceptada($requestID) {
        $query = <<<'QUERY'
            INSERT INTO `Request`(pending)
            VALUES(:pending) WHERE request_id = :id
    QUERY;

        $pending = false;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id', $requestID, PDO::PARAM_INT);
        $statement->bindParam('pending', $pending, PDO::PARAM_INT);
        $statement->execute();
    }

    public function addNewFriendship($requestID) {
        $stmt = $this->database->connection()->prepare('SELECT user1_id FROM `Request` WHERE id = :id');
        $stmt->bindParam('id', $requestID, PDO::PARAM_INT);
        $stmt->execute();

        $id1 = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->database->connection()->prepare('SELECT user2_id FROM `Request` WHERE id = :id');
        $stmt->bindParam('id', $requestID, PDO::PARAM_INT);
        $stmt->execute();

        $id2 = $stmt->fetch(PDO::FETCH_ASSOC);

        $date = new DateTime();
        $dateAccepted = $date->format(self::DATE_FORMAT);
        //$dateAccepted = time(); //Obtenemos hora a la que se acepta la solicitud

        $query = <<<'QUERY'
            INSERT INTO `Friend-User`(user1_id, user2_id, date_accepted)
            VALUES(:id1, :id2, :date_accepted)
    QUERY;

        $statement = $this->database->connection()->prepare($query);
        $statement->bindParam('id1', $id1, PDO::PARAM_INT);
        $statement->bindParam('id2', $id2, PDO::PARAM_INT);
        $statement->bindParam('date_accepted', $dateAccepted, PDO::PARAM_INT);
        $statement->execute();
    }

}