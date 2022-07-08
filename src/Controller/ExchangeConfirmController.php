<?php

namespace App\Controller;

use App\Entity\Exchange;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ExchangeConfirmController {

    public function __invoke(Exchange $data): Exchange {
        
        $data->setConfirmed(true);

        // $email = (new Email())
        //     ->from('swapit.esgi@gmail.com')
        //     ->to('nasfahdine@gmail.com')
        //     ->subject('Confirmation de votre demande')
        //     ->text('Votre demande a bien été prise en compte. Vous serez contacté par un membre de l\'équipe pour vous conformer à votre demande.');
        
        //     $mailer->send($email);
    
        return $data;
    }
}