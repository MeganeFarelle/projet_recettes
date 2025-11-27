<?php

namespace App\EventListener;

use App\Entity\Ingredient;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class IngredientListener
{
    public function preUpdate(Ingredient $ingredient, PreUpdateEventArgs $event)
    {
        $ingredient->setUpdatedAt(new \DateTimeImmutable());
    }
}
