<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;

use App\Repository\ChannelRepository;
use \Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\ChannelNotficationController;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
// use App\Entity\Message;

#[ORM\Entity(repositoryClass: ChannelRepository::class)]
#[ApiResource(
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:Channel:collection', 'read:Channel:item']],
            "security" => "is_granted('view', object)",
        ],
        'patch' => [
            'denormalization_context' => ['groups' => ['patch:Channel:item']],
            "security" => "is_granted('edit', object)",
            "security_message" => "Only admins or channel members can patch."
        ],
        'delete' => [
            "security" => "is_granted('delete', object)",
            "security_message" => "Only admins or channel members can delete."
        ],
        'channel-notification' => [
            'method' => 'GET',
            'path' => '/channels/{id}/channel-notification',
            'controller' => ChannelNotficationController::class,
            'openapi_context' => [
                'summary' => 'get channel notification',
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:Channel:collection']],
            "security" => "is_granted('ROLE_ADMIN')",
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['post:Channel:collection']],
            "security" => "is_granted('ROLE_USER')",
            "security_message" => "Only admins or user can post."
        ],
    ],
    subresourceOperations: [
        'api_users_channel_get_subresource' => [
            'method' => 'GET',
            'normalization_context' => [
                'groups' => ['read:Channel:item'],
            ],
        ],
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'subscribers' => 'exact', 'name' => 'partial'])]
class Channel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:Channel:collection','read:User:item','read:Channel:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read:Channel:collection','post:Channel:collection', 'read:User:item'])]
    private $name;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: Message::class, orphanRemoval: true)]
    #[Groups(['read:Channel:item'])]
    #[ApiSubresource(
        maxDepth: 1,
    )]
    private $messages;

    #[Assert\NotBlank]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'channels')]
    #[Groups(['read:Channel:item', 'post:Channel:collection'])]
    #[ApiSubresource(
        maxDepth: 1,
    )]
    private $subscribers;

    #[Groups(['read:Channel:item'])]
    private $hasNotification;


    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->subscribers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setChannel($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChannel() === $this) {
                $message->setChannel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getSubscribers(): Collection
    {
        return $this->subscribers;
    }

    public function addSubscriber(User $subscriber): self
    {
        if (!$this->subscribers->contains($subscriber)) {
            $this->subscribers[] = $subscriber;
        }

        return $this;
    }

    public function removeSubscriber(User $subscriber): self
    {
        $this->subscribers->removeElement($subscriber);

        return $this;
    }

    #[Groups(['read:Channel:collection'])]
    public function getLastMessage(): ?array
    {
        $lastMessage = $this->messages->last();
        if ($lastMessage) {
            return [
                'id' => $lastMessage->getId(),
                'content' => $lastMessage->getContent(),
                'createdDate' => $lastMessage->getCreatedDate()->format('Y-m-d H:i:s'),
                'author' => [
                    'id' => $lastMessage->getAuthor()->getId(),
                    'username' => $lastMessage->getAuthor()->getUsername(),
                ],
            ];
        }
        return [];
    }

    public function getHasNotification(): bool
    {
        if ($this->hasNotification === null) {
           return false;
        }
        return $this->hasNotification;
    }

    public function setHasNotification(bool $hasNotification): self
    {
        $this->hasNotification = $hasNotification;

        return $this;
    }
    // public function sethasNotification(bool $hasNotification)
    // {
    //     $this->hasNotification = $hasNotification;
    // }

}
