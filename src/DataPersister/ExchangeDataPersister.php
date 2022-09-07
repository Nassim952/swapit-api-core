<?php

namespace App\DataPersister;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Exchange;
use App\Entity\Notification;
use Symfony\Component\Security\Core\Security;
use App\Repository\ExchangeRepository;

class ExchangeDataPersister implements ContextAwareDataPersisterInterface
{

    private $entityManager;
    private $security;
    private $exchangeRepository;

    public function __construct(EntityManagerInterface $entityManager, Security $security, ExchangeRepository $exchangeRepository)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->exchangeRepository = $exchangeRepository;
    }
    

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Exchange;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context["collection_operation_name"]) && $context["collection_operation_name"]  == 'post') {
            
            $user = $this->security->getUser();
            $data->setProposer($user);
            if ($data->getOwner() == $user && $this->exchangeExiste($data)) {
                throw new \LogicException('You can not propose your own exchange or an exchange already proposed/recieved');
            }
            $this->entityManager->persist($data); 
        } else if (isset($context["item_operation_name"]) && in_array($context["item_operation_name"], ['cancel'])) {
            $this->entityManager->remove($data);
        } else {
            $this->entityManager->persist($data);
        }
        
        $this->createNotification($data,$context);
        $this->entityManager->flush();
       
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

    public function createNotification($data, array $context = [])
    {
        if(isset($context["item_operation_name"]) && in_array($context["item_operation_name"], ['refuse', 'accept', 'cancel'])) {
            $this->clearNotification($data->getId());
         } else {
            $sender = $this->security->getUser();
            $receiver = $data->getOwner() == $sender ? $data->getProposer() : $data->getOwner();
    
            $notification = new Notification();
            $notification->setRefTable('Exchange');
            $notification->setCreatedAt(new \DateTimeImmutable());
            $notification->setIdTable($data->getId());
            $notification->setReceiver($receiver);
            $notification->setSender($sender);
            $notification->setDescription('Nouvelle demande d\'echange de '.$sender->getUsername().'.');

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
         }
    }

    public function exchangeExiste($data, array $context = [])
    {
        $sent_exchanges = $this->security->getUser()->getSendExchanges() ?? [];
        $received_exchanges = $this->security->getUser()->getReceivedExchanges() ?? [];

        $exhanges = (array)$sent_exchanges->toArray() + (array)$received_exchanges->toArray();
    
        foreach ($exhanges as $exhange) {
                if ((
                    ( $exhange->getProposerGame() == $data->getProposerGame() && $exhange->getSenderGame() == $data->getSenderGame()) 
                || ($exhange->getProposerGame() == $data->getSenderGame() && $exhange->getSenderGame() == $data->getProposerGame())
                ) 
                && (
                    ($exhange->getOwner() == $data->getOwner() && $exhange->getProposer() == $data->getProposer()) 
                || ($exhange->getOwner() == $data->getProposer() && $exhange->getProposer() == $data->getOwner())
                )) {
                    return true;
                }
        }
        return false;
    }

    public function clearNotification($id_table)
    {
        $notifications = $this->entityManager->getRepository(Notification::class)->findBy(['refTable' => 'Exchange', 'idTable' => $id_table]);
        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }
        $this->entityManager->flush();
    }
}
