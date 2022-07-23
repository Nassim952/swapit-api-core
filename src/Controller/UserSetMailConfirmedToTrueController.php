<?php 

namespace App\Controller;

use App\Entity\User;

class UserSetMailConfirmedToTrueController
{
    public function __invoke(User $data): User
    {
        $data->setIsMailConfirmed(true);
        return $data;
    }
}