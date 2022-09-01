<?php 

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class UserSendMailForConfirmationController
{
    public function __invoke(User $data, MailerInterface $mailer): User {

        $cryptedId = base64_encode($data->getId());

        $email = (new Email())
            ->from('esgi.swapit@gmail.com')
            ->to($data->getEmail())
            ->subject('Confirmation de votre compte')
            ->text('Veuillez cliquer sur le lien suivant pour confirmer votre compte : ' . 'https://swapit-vue.herokuapp.com/' . 'mail-confirmation/' . $cryptedId);
        
        $mailer->send($email);

        return $data;
    }
}