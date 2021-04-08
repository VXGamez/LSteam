<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;

final class Search
{
    private string $search;
    private DateTime $createdAt;

    public function __construct(
        string $search,
        DateTime $createdAt,
    ) {
        $this->search = $search;
        $this->createdAt = $createdAt;
    }

    public function id(): int
    {
        return $this->id;
    }


    public function search(): string
    {
        return $this->search;
    }


    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

}