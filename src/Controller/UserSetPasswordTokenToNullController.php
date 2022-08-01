<?php 

namespace App\Controller;

use App\Entity\User;

class UserSetPasswordTokenToNullController
{
    public function __invoke(User $data): User
    {
        $data->setResetTokenPassword('null');
        return $data;
    }
}