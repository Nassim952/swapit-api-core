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
            if(empty($data->getSubscribers())){
                throw new \LogicException('You can not create a channel with no subscribers');
            }

            $data->addSubscriber($this->security->getUser()); 
            $data->setName($this->createName($data->getSubscribers()->toArray()));

            if(!$this->exists($data)){ 
                $this->entityManager->persist($data);
                $this->entityManager->flush();          
            }
            else{
                throw new \LogicException('You can not create a channel with no subscribers');
            }
        } else if(isset($context["item_operation_name"]) && in_array($context["item_operation_name"], ['put', 'patch'])) {
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

    public function exists($data, array $context = [])
    {
        $user = $this->security->getUser();

        $channels = $user->getChannels();

        $names = explode(' ', $data->getName());
        foreach ($channels as $channel) {
            $channel_names = explode(' ', $channel->getName());
            if(count(array_intersect($names, $channel_names)) == count($names)){
                return true;
            }
            // if (array_diff($a, $b) === array_diff($channel_names, $names)) {
            //     return true;
            // }
        }
        return false;
    }

}
