<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class IngredientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Option A : chaînage
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom est obligatoire.'),
                    new Assert\Length(min: 3, max: 50),
                ],
                'help' => 'Le nom doit avoir 3 caractères ou plus.',
            ])
            ->add('prix', NumberType::class, [
                'constraints' => [
                    new Assert\NotNull(message: 'Le prix est obligatoire.'),
                    new Assert\Range(min: 0, max: 200),
                ],
                'help' => 'Entre 0 et 200.',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);

        // Option B (équivalente) : sans chaînage
        // $builder->add('nom', ...);
        // $builder->add('prix', ...);
        // $builde  r->add('save', SubmitType::class, ['label' => 'Enregistrer']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ingredient::class,
        ]);
    }
}
