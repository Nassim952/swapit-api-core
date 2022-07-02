<?php

namespace App\DataPersister;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Exchange;

class ExchangeDataPersister implements ContextAwareDataPersisterInterface {

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function supports($data, array $context = []) : bool
    {
        return $data instanceof Exchange;
      
    }

    public function persist($data, array $context = [])
    {
        $data->setOwner($data->getOwner());
        $data->setProposer($data->getProposer());
        $data->setProposerGame($data->getProposerGame());
        $data->setSenderGame($data->getSenderGame());
        // $data->setConfirmed(null);

        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
      
    }
}