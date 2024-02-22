<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'facture', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transaction $idTransaction = null;

    #[ORM\Column]
    private ?float $tax = null;

    #[ORM\Column]
    private ?float $montantTTC = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdTransaction(): ?Transaction
    {
        return $this->idTransaction;
    }

    public function setIdTransaction(Transaction $idTransaction): static
    {
        $this->idTransaction = $idTransaction;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getMontantTTC(): ?float
    {
        return $this->montantTTC;
    }

    public function setMontantTTC(float $montantTTC): static
    {
        $this->montantTTC = $montantTTC;

        return $this;
    }


}
