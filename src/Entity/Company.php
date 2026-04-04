<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'companies')]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: CompanyRequest::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $requests;

    #[ORM\ManyToOne(cascade: ['persist'], fetch: "EAGER", inversedBy: 'ownedCompanies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $phone_number = null;

    #[ORM\Column(length: 100)]
    private ?string $address = null;

    #[ORM\Column(length: 50)]
    private ?string $country = null;

    #[ORM\Column(length: 75)]
    private ?string $city = null;

    #[ORM\Column]
    private ?bool $is_paid = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $is_searchable = true;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Work::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $records;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->records = new ArrayCollection();
        $this->requests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCompany($this);
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->is_paid;
    }

    public function setIsPaid(bool $is_paid): static
    {
        $this->is_paid = $is_paid;

        return $this;
    }

    public function isSearchable(): ?bool
    {
        return $this->is_searchable;
    }

    public function setIsSearchable(bool $is_searchable): static
    {
        $this->is_searchable = $is_searchable;

        return $this;
    }

    /**
     * @return Collection<int, Work>
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(Work $record): static
    {
        if (!$this->records->contains($record)) {
            $this->records->add($record);
            $record->setCompany($this);
        }

        return $this;
    }

    public function removeRecord(Work $record): static
    {
        if ($this->records->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getCompany() === $this) {
                $record->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyRequest>
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(CompanyRequest $request): static
    {
        if (!$this->requests->contains($request)) {
            $this->requests->add($request);
            $request->setCompany($this);
        }

        return $this;
    }

    public function removeRequest(CompanyRequest $request): static
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getCompany() === $this) {
                $request->setCompany(null);
            }
        }

        return $this;
    }
}
