<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $password;
    private DateTime $birthday;
    private string $phone;
    private DateTime $createdAt;

    public function __construct(
        string $username,
        string $email,
        string $password,
        DateTime $birthday,
        string $phone,
        DateTime $createdAt,
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->birthday = $birthday;
        $this->phone = $phone;
        $this->createdAt = $createdAt;

    }

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function birthday(): DateTime
    {
        return $this->birthday;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

}