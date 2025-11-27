<?php

namespace App\Taxe;

class CalculatorTaxe
{
    // Méthode qui calcule la TVA de 20%
    public function calculerTVA(float $prixHT): float
    {
        return $prixHT * 0.20;
    }

    // Méthode qui calcule le TTC
    public function calculerTTC(float $prixHT): float
    {
        return $prixHT * 1.20; // ou prixHT + calculerTVA(prixHT)
    }
}
