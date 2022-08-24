<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Notification;
use Symfony\Component\Mime\Email;
use App\Repository\NotificationRepository;

class ChannelNotficationController{
    public function __invoke(Channel $data, NotificationRepository $repository): ?Notification
    {
        // $notifications = $repository->findBy(['channel' => $data]);
        return $repository->findOneByChannel($data->getId());
    }
}