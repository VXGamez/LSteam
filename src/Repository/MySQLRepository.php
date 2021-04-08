<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use PDO;
use SallePW\SlimApp\Model\Search;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\Repository;
use SallePW\SlimApp\Repository\PDOSingleton;

final class MySQLRepository implements Repository
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
        INSERT INTO User(email, password, created_at)
        VALUES(:email, :password, :created_at)
QUERY;
        $statement = $this->database->connection()->prepare($query);

        $email = $user->email();
        $password = $user->password();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);
        $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);

        $ok = $statement->execute();

        return $ok;
    }

    public function validateUser($email, $password): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE email=? AND password=?');
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->bindParam(2, $password, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $ok = false;
        }

        return $ok;
    }

    public function checkIfExists($email): bool{
        $ok = true;

        $stmt = $this->database->connection()->prepare('SELECT * FROM User WHERE email=?');
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            $ok = false;
        }

        return $ok;
    }

    public function saveSearch(Search $s): bool
    {
        $query = <<<'QUERY'
        INSERT INTO Search(user_id, search, created_at)
        VALUES(:userId, :cerca, :created_at)
QUERY;
        $statement = $this->database->connection()->prepare($query);


        $userId = $this->database->connection()->query("SELECT id FROM User WHERE email = '".$_SESSION['email']."'")->fetch();

        $search = $s->search();
        $createdAt = $s->createdAt()->format(self::DATE_FORMAT);

        $statement->bindParam('userId', $userId, PDO::PARAM_INT);
        $statement->bindParam('cerca', $search, PDO::PARAM_STR);
        $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);

        $ok = $statement->execute();

        return $ok;

    }
}