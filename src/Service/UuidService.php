<?php

namespace App\Service;

use Ramsey\Uuid\Uuid;

class UuidService
{
    public function __construct()
    {
    }

    public function getUuid(): string
    {
        $uuid = Uuid::uuid4();
        $uuidString = $uuid->toString();

        return substr(str_replace('-', '', $uuidString),0, 9);
    }
}