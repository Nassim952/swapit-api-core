<?php

namespace App\DataPersister;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Channel;
use App\Repository\ChannelRepository;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;

class ChannelDataPersister implements ContextAwareDataPersisterInterface
{

    private $entityManager;
    private $security;
    private $channelRepository;

    public function __construct(EntityManagerInterface $entityManager, Security $security, ChannelRepository $channelRepository)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->channelRepository = $channelRepository;
    }
   

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Channel;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context["collection_operation_name"]) && $context["collection_operation_name"]  == 'post') {
            // $this->logger->info(print_r($data, true));
            // dump($data);
            // print_r($data, true);
            // $user = $this->security->getUser();
            // print_r($user, true);
            // $data->addSubscriber($user);
            // return $this->security->getUser();
            if(!empty($data->getSubscribers()) && $this->channelRepository->findOneBySubscribers($data->getSubscribers()->toArray()) == null){
                $data->addSubscriber($this->security->getUser()); 
                $data->setName($this->createName($data->getSubscribers()->toArray()));
                $this->entityManager->persist($data);
                $this->entityManager->flush();          
            }
        } else {
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }

    public function createName($subscribers, array $context = [])
    {
        $name = '';
        array_map(function($subscriber) use (&$name) {
            $name .= $subscriber->getUsername() . ' ';
        }, $subscribers);
        return $name;
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

}
