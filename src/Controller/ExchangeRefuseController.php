<?php

namespace App\Controller;

use App\Entity\Exchange;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ExchangeRefuseController
{

    public function __invoke(Exchange $data, MailerInterface $mailer): Exchange
    {

        $data->setConfirmed(false);

        // l'owner reçoit l'échange
        $email = (new Email())
            ->from('swapit.esgi@gmail.com')
            ->to($data->getOwner()->getEmail())
            ->subject('Confirmation de votre refus')
            ->text('L\'échange avec ' . $data->getProposer()->getUsername() . ' a été refusé.');

        // le proposer reçoit l'échange
        $emailToProposer = (new Email())
            ->from('swapit.esgi@gmail.com')
            ->to($data->getProposer()->getEmail())
            ->subject($data->getOwner()->getUsername() . ' a refusé votre demande d\'échange !')
            ->text('L\'échange a été refusée...');
        
        $mailer->send($email);
        $mailer->send($emailToProposer);

        return $data;
    }
}
