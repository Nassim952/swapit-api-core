<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Action\NotFoundAction;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:Message:collection', 'read:Message:item']],
            "security" => "is_granted('view', object)",
        ],
        'delete' => [
            "security" => "object.getAuthor() == user",
            "security_message" => "Only admins or author can delete."   
        ],
    ],
    collectionOperations: [
        'post' => [
            'denormalization_context' => ['groups' => ['post:Message:collection']],
            
            // "security" => "is_granted('canCreate', object)",
            // "security_message" => "Only subscribers can create message." 
        ],
    ],
    subresourceOperations: [
        'api_channels_message_get_subresource' => [
            'method' => 'GET',
            'normalization_context' => [
                'groups' => ['read:Channel:item'],
            ],
        ],
    ],
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:Channel:item','read:Channel:collection','read:Message:item','read:Message:collection','read:User:item'])]
    private $id;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    #[Groups(['read:Channel:item','post:Message:collection','read:Channel:collection'])]
    private $content;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:Channel:item','post:Message:collection','read:Channel:collection'])]
    private $author;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post:Message:collection'])]
    private $channel;

    #[ORM\Column(type: 'datetime')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    #[Groups(['read:Channel:item','post:Message:collection','read:Channel:collection'])]
    private $createdDate;

    // #[ORM\Column(type: 'datetime')]
    // #[Groups(['read:Channel:item','post:Message:collection','read:Channel:collection'])]
    // private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    // public function getCreatedAt(): ?\DateTimeInterface
    // {
    //     return $this->createdAt;
    // }

    // public function setCreatedAt(\DateTimeInterface $createdAt): self
    // {
    //     $this->createdAt = $createdAt;

    //     return $this;
    // }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
