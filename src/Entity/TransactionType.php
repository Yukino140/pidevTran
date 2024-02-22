<?php

namespace App\Entity;
enum TransactionType: string
{
    case Virement = "Virement";
    case Versement = "Versement";
    case Retrait = "Retrait";

}

