<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Repository;

use SallePW\SlimApp\Model\User;

/************************************************
* Interfície amb totes les funcions de la base de dades
************************************************/
interface MYSQLCallback
{
    public function save(User $user): bool;
    public function validateUser($email, $password): bool;
    public function getUser($usrEmail): User;
    public function checkIfEmailExists($email): bool;
    public function checkIfUsernameExists($usr): bool;
    public function checkToken($token): bool;
    public function checkActivation($token): bool;
    public function updateActivation($token);
    public function updatePass($user, $newPass);
    public function updatePhone($user, $phone);
    public function updateUuid($email, $uuid);
    public function updateWallet($wallet, $email);
    public function getUserId($usrEmail): int;
    public function getPurchaseHistory($usrEmail);
    public function getWishHistory($usrEmail);
    public function getUserGames($usrEmail);
    public function buyGame($email, $gameID, $data);
    public function getWish($email, $gameID, $data);
    public function deleteWish($email, $gameID);
    public function requestIsValid($myUserId, $friendUserId): int;
    public function getFriends($usrEmail, $flag);
    public function addRequest($idUsr, $idFriend);
    public function userInRequest($id, $requestID);
    public function solicitudAceptada($requestID);
    public function addNewFriendship($requestID);

    }