<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Holiday::class)]
    private Collection $concedii;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Work::class)]
    private Collection $pontaje;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'users')]
    private ?Company $company = null;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: Company::class)]
    private ?Company $ownedCompany = null;

    public function __construct()
    {
        $this->concedii = new ArrayCollection();
        $this->pontaje = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
     * @return Collection<int, Holiday>
     */
    public function getConcedii(): Collection
    {
        return $this->concedii;
    }

    public function addConcedii(Holiday $concedii): self
    {
        if (!$this->concedii->contains($concedii)) {
            $this->concedii->add($concedii);
            $concedii->setUserId($this);
        }

        return $this;
    }

    public function removeConcedii(Holiday $concedii): self
    {
        if ($this->concedii->removeElement($concedii)) {
            // set the owning side to null (unless already changed)
            if ($concedii->getUserId() === $this) {
                $concedii->setUserId(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Work>
     */
    public function getPontajes(): Collection
    {
        return $this->pontaje;
    }

    public function addPontaje(Work $pontaje): self
    {
        if (!$this->pontaje->contains($pontaje)) {
            $this->pontaje->add($pontaje);
            $pontaje->setUser($this);
        }

        return $this;
    }

    public function removePontaje(Work $pontaje): self
    {
        if ($this->pontaje->removeElement($pontaje)) {
            // set the owning side to null (unless already changed)
            if ($pontaje->getUser() === $this) {
                $pontaje->setUser(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getOwnedCompany(): ?Company
    {
        return $this->ownedCompany;
    }

    public function setOwnedCompany(?Company $ownedCompany): self
    {
        $this->ownedCompany = $ownedCompany;
        return $this;
    }

    public function isEnrolled(): bool
    {
        return (bool)$this->getCompany();
    }
}
