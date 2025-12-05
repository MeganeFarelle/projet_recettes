<?php

namespace App\Entity;

use App\Entity\Tag;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['recette:read']],
    denormalizationContext: ['groups' => ['recette:write']],
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_ADMIN') or object.getUser() == user"),
        new Patch(security: "is_granted('ROLE_ADMIN') or object.getUser() == user"),
        new Delete(security: "is_granted('ROLE_ADMIN') or object.getUser() == user"),
    ]
)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recette:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Groups(['recette:read', 'recette:write'])]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le temps est obligatoire.")]
    #[Assert\Positive(message: "Le temps doit être un nombre positif.")]
    #[Groups(['recette:read', 'recette:write'])]
    private ?int $temps = null;

    #[ORM\Column]
    #[Groups(['recette:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['recette:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Groups(['recette:read', 'recette:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le prix doit être positif.")]
    #[Groups(['recette:read', 'recette:write'])]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 5)]
    #[Groups(['recette:read', 'recette:write'])]
    private ?int $difficulte = null;

    #[ORM\ManyToMany(targetEntity: Ingredient::class, inversedBy: 'recettes')]
    #[Groups(['recette:read', 'recette:write'])]
    private Collection $ingredients;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['recette:read', 'recette:write'])]
    private ?User $user = null;

    /**
     * 🔥 Correction essentielle :
     * mappedBy doit correspondre à la propriété dans Tag.php → $recette
     */
    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Tag::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['recette:read', 'recette:write'])]
    private Collection $tags;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getTemps(): ?int { return $this->temps; }
    public function setTemps(int $temps): static { $this->temps = $temps; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(?float $prix): static { $this->prix = $prix; return $this; }

    public function getDifficulte(): ?int { return $this->difficulte; }
    public function setDifficulte(?int $difficulte): static { $this->difficulte = $difficulte; return $this; }

    public function getIngredients(): Collection { return $this->ingredients; }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
        }
        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        $this->ingredients->removeElement($ingredient);
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function __toString(): string { return $this->nom; }

    public function getTags(): Collection { return $this->tags; }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setRecette($this);
        }
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            if ($tag->getRecette() === $this) {
                $tag->setRecette(null);
            }
        }
        return $this;
    }
}
