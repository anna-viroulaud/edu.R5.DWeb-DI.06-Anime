<?php

namespace App\Form;

use App\Entity\Anime;
use App\Entity\Genre;
use App\Entity\Studio;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AnimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('synopsis', TextareaType::class, ['label' => 'Synopsis', 'required' => false])
            ->add('releaseYear', IntegerType::class, ['label' => 'Année', 'required' => false])
            ->add('episodesCount', IntegerType::class, ['label' => "Nombre d'épisodes", 'required' => false])
            ->add('studio', EntityType::class, [
                'class' => Studio::class,
                'choice_label' => 'name',
                'label' => 'Studio',
                'required' => false,
            ])
            ->add('genres', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'name',
                'label' => 'Genres',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('posterImage', TextType::class, ['label' => "URL de l'affiche", 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Anime::class]);
    }
}
