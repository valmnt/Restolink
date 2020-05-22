<?php

namespace App\Commande;

class CommandeParameter
{
    private $fraisLivraison = 2.5;

    public function getFraislivraison(): ?float
    {
        return $this->fraisLivraison;
    }
}
