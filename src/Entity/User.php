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

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CompanyRequest::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $companyRequests;

    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_company')]
    private Collection $companies;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Work::class)]
    private Collection $pontaje;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Company::class)]
    private Collection $ownedCompanies;

    public function __construct()
    {
        $this->concedii = new ArrayCollection();
        $this->pontaje = new ArrayCollection();
        $this->companyRequests = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->ownedCompanies = new ArrayCollection();
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

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        $this->companies->removeElement($company);

        return $this;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getOwnedCompanies(): Collection
    {
        return $this->ownedCompanies;
    }

    public function addOwnedCompany(Company $ownedCompany): static
    {
        if (!$this->ownedCompanies->contains($ownedCompany)) {
            $this->ownedCompanies->add($ownedCompany);
            $ownedCompany->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedCompany(Company $ownedCompany): static
    {
        if ($this->ownedCompanies->removeElement($ownedCompany)) {
            // set the owning side to null (unless already changed)
            if ($ownedCompany->getOwner() === $this) {
                $ownedCompany->setOwner(null);
            }
        }

        return $this;
    }

    public function isEnrolled(): bool
    {
        return !$this->companies->isEmpty();
    }

    /**
     * @return Collection<int, CompanyRequest>
     */
    public function getCompanyRequests(): Collection
    {
        return $this->companyRequests;
    }

    public function addCompanyRequest(CompanyRequest $companyRequest): static
    {
        if (!$this->companyRequests->contains($companyRequest)) {
            $this->companyRequests->add($companyRequest);
            $companyRequest->setUser($this);
        }

        return $this;
    }

    public function removeCompanyRequest(CompanyRequest $companyRequest): static
    {
        if ($this->companyRequests->removeElement($companyRequest)) {
            // set the owning side to null (unless already changed)
            if ($companyRequest->getUser() === $this) {
                $companyRequest->setUser(null);
            }
        }

        return $this;
    }
}
