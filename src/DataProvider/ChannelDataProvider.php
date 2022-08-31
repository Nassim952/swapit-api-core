<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\SubresourceDataProviderInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\NotificationRepository;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Channel;

final class ChannelDataProvider implements ContextAwareCollectionDataProviderInterface, DenormalizedIdentifiersAwareItemDataProviderInterface, RestrictedDataProviderInterface, SubresourceDataProviderInterface
{
    private $repository;
    private $collectionDataProvider;
    private $itemDataProvider;
    private $security;
    private $entityManager;
    private $subresourceDataProvider;



    public function __construct(CollectionDataProviderInterface $collectionDataProvider, ItemDataProviderInterface $itemDataProvider, SubresourceDataProviderInterface $subresourceDataProvider, NotificationRepository $repository, Security $security, EntityManagerInterface $entityManager, ChannelRepository $channelRepository) {
       
        // dd('toto');
        $this->repository = $repository;
        $this->collectionDataProvider = $collectionDataProvider;
        $this->security = $security;
        $this->itemDataProvider = $itemDataProvider;
        $this->entityManager = $entityManager;
        $this->subresourceDataProvider = $subresourceDataProvider;
        $this->channelRepository = $channelRepository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // dd($resourceClass);
        return Channel::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $channels = $this->collectionDataProvider->getCollection($resourceClass, $operationName, $context);
        if($channels){
            foreach ($channels as $channel) {
                $this->UpdateChannelNotification($channel);
            }  
        }
        return $channels;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {   
        $channel = $this->itemDataProvider->getItem($resourceClass, $id, $operationName, $context);
        if($channel){
            if($notification = $this->repository->findOneByChannel($channel->getId())) {
                $this->entityManager->remove($notification);
                $this->entityManager->flush();    
            }
            $channel->setHasNotification(false);
        }
        return $channel;
    }

    public function getSubresource(string $resourceClass, array $identifiers, array $context, string $operationName = null)
    {
        $channels = $this->subresourceDataProvider->getSubresource($resourceClass, $identifiers, $context, $operationName);
        if($channels){
            foreach ($channels as $channel) {
                $this->UpdateChannelNotification($channel);
            }
        }
        
        return $channels;
    }


    private function UpdateChannelNotification( Channel $channel)
    {
        if($notification = $this->repository->findOneByChannel($channel->getId())) {
            $notification->getReceiver() ==  $this->security->getUser() ? $channel->setHasNotification(true) : $channel->setHasNotification(false);
        }
        else {
            $channel->setHasNotification(false);
        }
        return $channel;
    }
}