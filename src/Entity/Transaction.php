<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use http\Message;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"You have to put amount of money")]
    #[Assert\Positive(message:"This number  can not be accepted as an amount of money'")]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type:"string",enumType: TransactionType::class)]
    private TransactionType|null $typeTransaction = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max:20,maxMessage:"The Account Number can't be longer than '{{limit}}' numbers" )]
    private ?string $compteRecus = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Compte $idCompte = null;

    #[ORM\OneToOne(mappedBy: 'idTransaction', cascade: ['persist', 'remove'])]
    private ?Facture $facture = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTypeTransaction(): ?object
    {
        return $this->typeTransaction;
    }

    public function setTypeTransaction(TransactionType $typeTransaction): static
    {
        $this->typeTransaction = $typeTransaction;

        return $this;
    }

    public function getCompteRecus(): ?string
    {
        return $this->compteRecus;
    }

    public function setCompteRecus(?string $compteRecus): static
    {
        $this->compteRecus = $compteRecus;

        return $this;
    }
    public function __toString()
    {
        return $this->typeTransaction->name; // Replace with the property that represents the string representation of the TransactionType
    }

    public function getIdCompte(): ?Compte
    {
        return $this->idCompte;
    }

    public function setIdCompte(?Compte $idCompte): static
    {
        $this->idCompte = $idCompte;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(Facture $facture): static
    {
        // set the owning side of the relation if necessary
        if ($facture->getIdTransaction() !== $this) {
            $facture->setIdTransaction($this);
        }

        $this->facture = $facture;

        return $this;
    }


}
