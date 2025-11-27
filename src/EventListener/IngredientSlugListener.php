<?php

namespace App\EventListener;

use App\Entity\Ingredient;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

class IngredientSlugListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Ingredient $ingredient, PrePersistEventArgs $event)
    {
        if (!$ingredient->getSlug()) {
            $slug = $this->slugger->slug($ingredient->getNom())->lower();
            $ingredient->setSlug($slug);
        }
    }

    public function preUpdate(Ingredient $ingredient, PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('nom')) {
            $slug = $this->slugger->slug($ingredient->getNom())->lower();
            $ingredient->setSlug($slug);
        }
    }
}
