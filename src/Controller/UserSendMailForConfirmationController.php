<?php 

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class UserSendMailForConfirmationController
{
    public function __invoke(User $data, MailerInterface $mailer): User {

        $email = (new Email())
            ->from('esgi.swapit@gmail.com')
            ->to($data->getEmail())
            ->subject('Confirmation de votre compte')
            ->text('Veuillez cliquer sur le lien suivant pour confirmer votre compte : ' . 'http://localhost:8080/' . 'mail-confirmation/' . $data->getId());
        
        $mailer->send($email);

        return $data;
    }
}