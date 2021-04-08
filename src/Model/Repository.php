<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

interface Repository
{
    public function save(User $user): bool;
    public function saveSearch(Search $user): bool;
}