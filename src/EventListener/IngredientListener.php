<?php

namespace App\EventListener;

use App\Entity\Ingredient;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class IngredientListener
{
    public function prePersist(Ingredient $ingredient, PrePersistEventArgs $event)
    {
        // createdAt doit être défini uniquement à la création
        $ingredient->setCreatedAt(new \DateTimeImmutable());
        $ingredient->setUpdatedAt(new \DateTimeImmutable());
    }

    public function preUpdate(Ingredient $ingredient, PreUpdateEventArgs $event)
    {
        // updatedAt doit être mis à jour seulement à la modification
        $ingredient->setUpdatedAt(new \DateTimeImmutable());
    }
}
