<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ExchangeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;



#[ORM\Entity(repositoryClass: ExchangeRepository::class)]
#[ApiResource(mercure: true,
    itemOperations: [
        'get' => [
            'normalisation_context' => ['groups' => ['read:Exchange:collection','read:Exchange:item','read:User:collection']]
        ],
        'patch' => [
            'denrmalization_context' => ['groups' => ['patch:Exchange:item']]
        ] ,
        'delete'
        ],
    collectionOperations: [
        'get' => [
            'normalisation_context' => ['groups' => ['read:Exchange:collection']]
        ],
        'post'
    ]
)]
#[ApiFilter(PropertyFilter::class)]
class Exchange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:Exchange:collection'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedExchanges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['write:Exchange:item','read:Exchange:collection'])]
    private $owner;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sendExchanges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['write:Exchange:item','read:Exchange:collection'])]
    private $proposer;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write:Exchange:item','read:Exchange:collection'])]
    private $proposerGame;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write:Exchange:item','read:Exchange:collection'])]
    private $senderGame;


    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['write:Exchange:item','read:Exchange:collection'])]
    private $confirmed;

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getProposer(): ?User
    {
        return $this->proposer;
    }

    public function setProposer(?User $proposer): self
    {
        $this->proposer = $proposer;

        return $this;
    }

    public function getProposerGame(): string
    {
        return $this->proposerGame;
    }

    public function setProposerGame(string $proposerGame): self
    {
        $this->proposerGame = $proposerGame;

        return $this;
    }

    
    public function getSenderGame(): string
    {
        return $this->senderGame;
    }

    public function setSenderGame(string $senderGame): self
    {
        $this->senderGame = $senderGame;

        return $this;
    }

    public function getConfirmed(): ?bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(?bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

}
