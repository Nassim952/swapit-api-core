<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ExchangeRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ExchangeRefuseController;
use App\Controller\ExchangeConfirmController;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: ExchangeRepository::class)]
#[ApiResource(mercure: true,
    itemOperations: [
        'get' => [
            'normalisation_context' => ['groups' => ['read:Exchange:collection','read:Exchange:item','read:User:collection']]
        ],
        'patch' => [
            'denormalization_context' => ['groups' => ['patch:Exchange:item']]
        ] ,
        'delete',
        'accept' => [
            'method' => 'PATCH',
            'path' => '/exchanges/{id}/accept',
            'controller' => ExchangeConfirmController::class,
            'openapi_context' => [
                'summary' => 'accept an exchange',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ]
        ],
        'refuse' => [
            'method' => 'PATCH',
            'path' => '/exchanges/{id}/refuse',
            'controller' => ExchangeRefuseController::class,
            'openapi_context' => [
                'summary' => 'refuse an exchange',
                'requestBody' => [
                    'content' => [
                        'application/merge-patch+json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ]
        ],
    ],
    collectionOperations: [
        'get' => [
            'normalisation_context' => ['groups' => ['read:Exchange:collection']]
        ],
        'post'=> [
            'denormalization_context' => ['groups' => ['write:Exchange:item']]
        ],
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

    #[ORM\Column(type: 'integer')]
    #[Groups(['write:Exchange:item','read:Exchange:collection'])]
    private $proposerGame;

    #[ORM\Column(type: 'integer')]
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

    public function getProposerGame(): ?int
    {
        return $this->proposerGame;
    }

    public function setProposerGame(?int $proposerGame): self
    {
        $this->proposerGame = $proposerGame;

        return $this;
    }

    
    public function getSenderGame(): ?int
    {
        return $this->senderGame;
    }

    public function setSenderGame(?int $senderGame): self
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