<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;

class UserGameOwnerController{
    
    public function __invoke($gameID, UserRepository $repository, Security $security): array {
        
        $users = $repository->findAll();

        $owners = array_filter($users, function($user) use ($gameID) {
            return in_array($gameID,$user->getOwnGames());
        });
        
        return $owners ?? [];
    }
}