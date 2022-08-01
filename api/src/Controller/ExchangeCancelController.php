<?php

namespace App\Controller;

use App\Entity\Exchange;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ExchangeCancelController{
    public function __invoke(Exchange $data, MailerInterface $mailer): Exchange
    {

        $data->setConfirmed(false);

        // l'owner reçoit l'échange
        $email = (new Email())
            ->from('swapit.esgi@gmail.com')
            ->to($data->getOwner()->getEmail())
            ->subject('L\'échange a été annulé !')
            ->text($data->getProposer()->getUsername() . ' a annulé sa demande d\'échange !');

        // le proposer reçoit l'échange
        $emailToProposer = (new Email())
            ->from('swapit.esgi@gmail.com')
            ->to($data->getProposer()->getEmail())
            ->subject(' L\'échange a bien été annulé !')
            ->text('Votre échange avec ' . $data->getOwner()->getUsername() . ' a été annulé !');
        
        $mailer->send($email);
        $mailer->send($emailToProposer);

        return $data;
    }
}