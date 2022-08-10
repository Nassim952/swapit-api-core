<?php

namespace App\Entity;

use App\Entity\Channel;
use App\Filter\UserFilter;

use App\Filter\CountFilter;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;


use ApiPlatform\Core\Annotation\ApiResource;

use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\UserGenerateTokenPasswordController;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Controller\UserSetMailConfirmedToTrueController;
use App\Controller\UserSetPasswordTokenToNullController;
use App\Controller\UserSendMailForConfirmationController;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:User:collection', 'read:User:item']],
            "security" => "is_granted('view', object)",
            "security_message" => "Only Admin or Owner can view this resource.",
            "enable_max_depth" => true,
        ],
        'patch' => [
            'denormalization_context' => ['groups' => ['patch:User:item']],
            'normalization_context' => ['groups' => ['read:User:collection', 'read:User:item']],
            "security" => "is_granted('edit', object)",
            "security_message" => "Only Admin or Owner can patch."
        ],
        'delete' => [
            "security" => "is_granted('delete', object)",
            "security_message" => "Only Admin or Owner can delete."
        ],
        'user-generate-token-password' => [
            'method' => 'PATCH',
            'path' => '/users/{id}/generate-token-password',
            'controller' => UserGenerateTokenPasswordController::class,
            'openapi_context' => [
                'summary' => 'generate token password',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ],
        ],
        'set-token-reset-password-to-null' => [
            'method' => 'PATCH',
            'path' => '/users/{id}/set-token-reset-password-to-null',
            'controller' => UserSetPasswordTokenToNullController::class,
            'openapi_context' => [
                'summary' => 'set token reset password to null',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ],
        ],
        'send-mail-for-confirmation' => [
            'method' => 'PATCH',
            'path' => '/users/{id}/send-mail-for-confirmation',
            'controller' => UserSendMailForConfirmationController::class,
            'openapi_context' => [
                'summary' => 'send mail for confirmation',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ],
        ],
        'send-mail-for-contact' => [
            'method' => 'PATCH',
            'path' => '/users/{id}/send-mail-for-contact',
            'controller' => UserSendMailForContactController::class,
            'openapi_context' => [
                'summary' => 'send mail for contact',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ],
        ],
        'set-mail-confirmed-to-true' => [
            'method' => 'PATCH',
            'path' => '/users/{id}/set-mail-confirmed-to-true',
            'controller' => UserSetMailConfirmedToTrueController::class,
            'openapi_context' => [
                'summary' => 'set mail confirmed to true',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => []
                        ]
                    ]
                ]
            ],
        ],
        
    ],
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:User:collection']],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only Admin or Owner can view this resource."
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['post:User:collection']],
            'normalization_context' => ['groups' => ['read:User:collection', 'read:User:item']],
            // "security_message" => "Only admin can create Admin users."
        ],
    ]
)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(UserFilter::class)]
#[ApiFilter(CountFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'username' => 'exact', 'email' => 'exact', 'ownGames' => 'partial', 'wishGames' => 'partial', 'resetTokenPassword' => 'exact'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:User:collection','read:Channel:collection','read:Channel:item'])]
    private $id;


    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read:User:collection', 'post:User:collection', 'read:Channel:collection','read:Channel:item'])]
    private $username;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['post:User:collection', 'patch:User:item'])]
    private $password;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['post:User:collection', 'read:User:item', 'patch:User:item'])]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(['post:User:collection', 'read:User:item', 'patch:User:item'])]
    private $roles = ["ROLE_USER"];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read:User:item', 'patch:User:item'])]
    private $resetTokenPassword;

    #[Groups(['read:User:item', 'patch:User:item', 'read:User:collection'],)]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Exchange::class, orphanRemoval: true)]
    #[ApiSubresource(
        maxDepth: 1,
    )]
    private $receivedExchanges;

    #[Groups(['read:User:item', 'patch:User:item', 'read:User:collection'])]
    #[ORM\OneToMany(mappedBy: 'proposer', targetEntity: Exchange::class, orphanRemoval: true)]
    #[ApiSubresource(
        maxDepth: 1,
    )]
    private $sendExchanges;

    #[ORM\Column(type: 'array', nullable: true)]
    #[Groups(['read:User:item', 'patch:User:item', 'read:User:collection'])]
    private $ownGames = [];

    #[ORM\Column(type: 'array', nullable: true)]
    #[Groups(['read:User:item', 'patch:User:item', 'read:User:collection'])]
    private $wishGames = [];

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Notification::class, orphanRemoval: true)]
    #[ApiSubresource(
        maxDepth: 1,
    )]
    #[Groups(['read:User:item'])]
    private $notifications;

    #[ORM\ManyToMany(targetEntity: Channel::class, mappedBy: 'subscribers')]
    #[ApiSubresource(
        maxDepth: 1,
    )]
    #[Groups(['read:User:item'])]
    private $channels;
    
    public function __construct()
    {
        $this->receivedExchanges = new ArrayCollection();
        $this->sendExchanges = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->channels_received = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public static function createFromPayload($email, array $payload)
    {
        $user = (new User())->setEmail($email);
        return $user;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getResetTokenPassword(): ?string
    {
        return $this->resetTokenPassword;
    }

    public function setResetTokenPassword(string $tokenPassword): self
    {
        $this->resetTokenPassword = $tokenPassword;
        return $this;
    }

    /**
     * @return Collection|Exchange[]
     */
    public function getReceivedExchanges(): Collection
    {
        return $this->receivedExchanges;
    }

    public function addreceivedExchange(Exchange $receivedExchange): self
    {
        if (!$this->receivedExchanges->contains($receivedExchange)) {
            $this->receivedExchanges[] = $receivedExchange;
            $receivedExchange->setOwner($this);
        }

        return $this;
    }

    public function removeReceivedExchange(Exchange $receivedExchange): self
    {
        if ($this->receivedExchanges->removeElement($receivedExchange)) {
            // set the owning side to null (unless already changed)
            if ($receivedExchange->getOwner() === $this) {
                $receivedExchange->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Exchange[]
     */
    public function getSendExchanges(): Collection
    {
        return $this->sendExchanges;
    }

    public function addSendExchange(Exchange $sendExchange): self
    {
        if (!$this->sendExchanges->contains($sendExchange)) {
            $this->sendExchanges[] = $sendExchange;
            $sendExchange->setProposer($this);
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function removeSendExchange(Exchange $sendExchange): self
    {
        if ($this->sendExchanges->removeElement($sendExchange)) {
            // set the owning side to null (unless already changed)
            if ($sendExchange->getProposer() === $this) {
                $sendExchange->setProposer(null);
            }
        }

        return $this;
    }

    public function getOwnGames(): ?array
    {
        return $this->ownGames;
    }

    public function setOwnGames(?array $ownGames): self
    {
        $this->ownGames = $ownGames;

        return $this;
    }

    public function getWishGames(): ?array
    {
        return $this->wishGames;
    }

    public function setWishGames(?array $wishGames): self
    {
        $this->wishGames = $wishGames;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setReceiver($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getReceiver() === $this) {
                $notification->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Channel>
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): self
    {
        if (!$this->channels->contains($channel)) {
            $this->channels[] = $channel;
            $channel->addSubscriber($this);
        }

        return $this;
    }

    public function removeChannel(Channel $channel): self
    {
        if ($this->channels->removeElement($channel)) {
            $channel->removeSubscriber($this);
        }

        return $this;
    }
}
