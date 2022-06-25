<?php

declare(strict_types=1);

namespace App\Authorizations;

interface Authorization
{
    public function isAuthenticated(): void;
    public function isMethodAllowed(string $method): bool;
    public function check($object, string $method): void;
}