<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class UserGenerateTokenPasswordController{
    
    public function __invoke(User $data, MailerInterface $mailer): User {
        $data->setResetTokenPassword(bin2hex(random_bytes(32)));

        $email = (new Email())
            ->from('esgi.swapit@gmail.com')
            ->to($data->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->text('Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe : ' . 'http://localhost:8080/' . 'form-reset-password/' . $data->getResetTokenPassword());
        
        $mailer->send($email);

        return $data;
    }
}