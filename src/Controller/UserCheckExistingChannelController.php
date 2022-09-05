<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;


class UserCheckExistingChannelController{
    
    public function __invoke( User $data, Security $security): bool {
        $user = $security->getUser();
        if($data == $user){
            throw new \LogicException('You can not check an existing channel  yourself as owner');
        }

        array_map(function($channel) use ($user) {
            if($channel->getSubscribers()->contains($user)){
                json_decode(['existing' => true])  ;
            }
        }, $data->getChannels()->toArray() ?? []);

        return json_decode(['existing' => false]);
    }
}