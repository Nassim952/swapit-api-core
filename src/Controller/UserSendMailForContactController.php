<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class UserSendMailForContactController
{
    public function __invoke(User $data, MailerInterface $mailer): User
    {
        $email = (new Email())
            ->from($data->getEmail())
            ->to('esgi.swapit@gmail.com')
            ->subject('Hello')
            ->text('This is a test email');

        $mailer->send($email);

        return $data;
    }
}
