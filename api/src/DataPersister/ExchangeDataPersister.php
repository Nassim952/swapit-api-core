<?php

namespace App\DataPersister;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Exchange;
use App\Entity\Notification;
use Symfony\Component\Security\Core\Security;

class ExchangeDataPersister implements ContextAwareDataPersisterInterface
{

    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
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
            // faire un retour msg permission denied
            if ($data->getOwner() != $user ) {
                $this->entityManager->persist($data);
                $this->entityManager->flush();

                $this->createNotification($data);
            }
        } else {
            $this->entityManager->persist($data);
            $this->entityManager->flush();

            $this->createNotification($data);
        }
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

    public function createNotification($data, array $context = [])
    {
        $notification = new Notification();
        $notification->setRefTable('Exchange');
        $notification->setCreatedAt(new \DateTimeImmutable());
        $notification->setIdTable($data->getId());
        $notification->setReceiver($data->getOwner());
        $notification->setSender($data->getProposer());
        $notification->setDescription('Nouvelle demande d\'echange de '.$data->getProposer()->getUsername().'.');

        if ($data->getOwner() == $this->security->getUser()) {
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }  
    }
}
