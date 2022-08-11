<?php

namespace App\DataPersister;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Message;
use App\Entity\Notification;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Pusher\Pusher;
class MessageDataPersister implements ContextAwareDataPersisterInterface
{

    private $entityManager;
    private $security;
    private $messageRepository;
    private $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager, Security $security, MessageRepository $messageRepository, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->messageRepository = $messageRepository;
        $this->notificationRepository = $notificationRepository;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Message;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context["collection_operation_name"]) && $context["collection_operation_name"]  == 'post') { 
            if($data->getAuthor() == $this->security->getUser() && $this->isSubscriber($data)) {
                $data->setCreatedDate(new \DateTime());
                $this->entityManager->persist($data);
                $this->entityManager->flush();
                $this->initPusher($data);
                $this->createNotification($data);
                // $this->entityManager->persist($data);
                // $this->entityManager->flush();
                // $this->initPusher($data);
                // $this->createNotification($data);
            }
        } else {
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

    public function initPusher($data, array $context = [])
    {
        $options = array(
            'cluster' => 'eu',
            'useTLS' => true
          );
          $pusher = new Pusher(
            '498f9f1d1a87ee7c6ee2',
            'c651e4db8bd4cd7e3fe7',
            '1456123',
            $options
          );

          $message = [
            'content' => $data->getContent(),
            'author' => [
                'id'=> $data->getAuthor()->getId(),
                'username'=> $data->getAuthor()->getUsername(),
            ],
            'channel' => $data->getChannel()->getName(),
            'createdDate' => $data->getCreatedDate()->format("Y-m-d\TH:i:s\Z"),
          ];
          
          $pusher->trigger('channel_'.$data->getChannel()->getId(), 'message', $message);
    }

    public function createNotification($data, array $context = [])
    { 
        foreach ($data->getChannel()->getSubscribers() as $subscriber) {
            $notification = $this->notificationRepository->findOneByChannel($data->getChannel()->getId()) ?? new Notification();
            if ($subscriber->getId() != $data->getAuthor()->getId()) {
                $notification->setRefTable('Message');
                $notification->setIdTable($data->getChannel()->getId());
                $notification->setReceiver($subscriber);
                $notification->setSender($data->getAuthor());
                $notification->setCreatedAt(new \DateTimeImmutable());
                $notification->setDescription('Nouveau message de '.$data->getAuthor()->getUsername().'.');
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            }
        } 
    }

    private function isSubscriber(Message $message): bool
    {
        $usersIds = array_map(function ($user) {
            return $user->getId();
        }, $message->getChannel()->getSubscribers()->toArray()); 
        
        // $usersIds = $message->getChannel()->getSubscribers()->map(function (User $user) {
        //     return $user->getId();
        // })->toArray();
        return in_array($this->security->getUser()->getId(), $usersIds);
        // return true;
    }
}
