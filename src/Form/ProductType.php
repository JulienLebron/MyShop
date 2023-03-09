<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => '💬 Titre'
            ])
            ->add('content', TextType::class, [
                'label' => '💬 Description'
            ])
            ->add('color', TextType::class, [
                'label' => '🌈 Couleur'
            ])
            ->add('size', TextType::class, [
                'label' => '👟 Taille'
            ])
            ->add('collection', ChoiceType::class, [
                'label' => '🚻 Collection',
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ]
            ])
            ->add('imageFile', FileType::class, [
                'required' => false,
                'label' => '📸 Photo du produit'
            ])
            ->add('price', TextType::class, [
                'label' => '🛒 Prix'
            ])
            ->add('stock', TextType::class, [
                'label' => '🏭'
            ])
            // ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
