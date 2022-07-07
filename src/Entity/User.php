<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Filter\Userfilter;

use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

use App\DataPersister\UserDataPersister;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    itemOperations: [
        'get' => [
            'normalisation_context' => ['groups' => ['read:Exchange:collection', 'read:User:collection', 'read:User:item']]
        ],
        'patch' => [
            "security" => "is_granted('edit', object)",
            "security_message" => "Only admins or Owner can patch."
        ],
        'delete' => [
            "security" => "is_granted('delete', object)",
            "security_message" => "Only admins or Owner can delete."
        ],
    ],
    collectionOperations: [
        'get' => [
            'normalisation_context' => ['groups' => ['read:User:collection']]
        ],
        'post'
    ]
)]
#[ApiFilter(PropertyFilter::class)]
#[ApiFilter(UserFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'username' => 'exact', 'email' => 'exact'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:User:collection'])]
    private $id;


    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read:User:collection', 'write:User:item'])]
    private $username;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write:User:item', 'read:User:item', 'patch:User:item'])]
    private $password;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(['read:User:item', 'write:User:item'])]
    private $roles = ["ROLE_USER"];

    #[Groups(['read:User:item', 'patch:User:item'],)]
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Exchange::class, orphanRemoval: true)]
    #[ApiSubresource]
    private $receivedExchanges;

    #[Groups(['read:User:item', 'patch:User:item'])]
    #[ORM\OneToMany(mappedBy: 'proposer', targetEntity: Exchange::class, orphanRemoval: true)]
    #[ApiSubresource]
    private $sendExchanges;

    #[ORM\Column(type: 'array', nullable: true)]
    private $ownGames = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private $wishGames = [];

    public function __construct()
    {
        $this->receivedExchanges = new ArrayCollection();
        $this->sendExchanges = new ArrayCollection();
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
}
