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
        $stmt2->bindParam('uid', $userID, PDO::PARAM_INT);
        $stmt2->execute();

        if($stmt2->rowCount() != 0){
            $stmt3 = $this->database->connection()->prepare('DELETE FROM `User-Game-Wishlist` WHERE gameID = :gid AND userID = :uid');
            $stmt3->bindParam('gid', $gameID, PDO::PARAM_INT);
            $stmt3->bindParam('uid', $userID, PDO::PARAM_INT);
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

        $stmt = $this->database->connection()->prepare('SELECT gameID FROM `User-Game-Wishlist` WHERE id = :gid ');
        $stmt->bindParam('gid', $gameID, PDO::PARAM_INT);
        $stmt->execute();


        if($stmt->rowCount() == 0){
          
            $query = <<<'QUERY'
            INSERT INTO `User-Game-Wishlist`(gameID, userID, sellPrice)
            VALUES(:gameid,:userid, :sell)
    QUERY;
            
            $userID = $this->getUserId($email);
            $sellPrice = (String)$data['salePrice'];
            
            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('gameid', $gameID, PDO::PARAM_INT);
            $statement->bindParam('userid', $userID, PDO::PARAM_INT);
            $statement->bindParam('sell', $sellPrice, PDO::PARAM_INT);
            $statement->execute();
        }

       

    }


    

}