<?php

namespace App\Controller;

use App\Entity\Exchange;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class ExchangeConfirmController
{

    public function __invoke(Exchange $data, MailerInterface $mailer): Exchange
    {
        $data->setConfirmed(true);

        // l'owner reçoit l'échange
        $emailToOwner = (new Email())
            ->from('swapit.esgi@gmail.com')
            ->to($data->getOwner()->getEmail())
            ->subject('Confirmation de votre demande')
            ->text('L\'échange avec' . $data->getProposer()->getUsername() . ' a bien été acceptée, amusez-vous bien avec votre nouveau jeu !');

        // le proposer reçoit l'échange
        $emailToProposer = (new Email())
            ->from('swapit.esgi@gmail.com')
            ->to($data->getProposer()->getEmail())
            ->subject($data->getOwner()->getUsername() . ' a accepté votre demande d\'échange !')
            ->text('L\'échange a été acceptée, amusez-vous bien avec votre prochain nouveau jeu !');

        $mailer->send($emailToOwner);
        $mailer->send($emailToProposer);

        return $data;
    }
}
