<?php

namespace TmrEcosystem\IAM\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string|Optional $password, // Password อาจไม่ต้องส่งมาตอน Update
        /** @var array<string>|Optional */
        public array|Optional $roles // รายชื่อ Role ที่ User นี้มี
    ) {}
}
