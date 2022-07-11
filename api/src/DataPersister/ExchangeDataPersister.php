<?php

namespace App\DataPersister;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Exchange;
use Symfony\Component\Security\Core\Security;

class ExchangeDataPersister implements ContextAwareDataPersisterInterface {

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Security $security) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []) : bool
    {
        return $data instanceof Exchange;
      
    }

    public function persist($data, array $context = [])
    {
        // $data->setOwner($data->getOwner());
        // $data->setProposer($data->getProposer());
        // $data->setProposerGame($data->getProposerGame());
        // $data->setSenderGame($data->getSenderGame());
        // $data->setConfirmed(null);

        $user=$this->security->getUser();
        if($data->getProposer() == $user){
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        } 
        
        // elseif ($data->getOwner($user)) {
            
        // }
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
      
    }
}