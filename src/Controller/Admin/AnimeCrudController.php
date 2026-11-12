<?php

namespace App\Controller\Admin;

use App\Entity\Anime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AnimeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Anime::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            TextareaField::new('synopsis'),
            IntegerField::new('release_year'),
            IntegerField::new('episodes_count'),
            BooleanField::new('is_featured'),
            AssociationField::new('studio'),
            AssociationField::new('genres'),
        ];
    }

    // Assigne automatiquement l'admin connecté comme créateur de l'anime
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Anime && !$entityInstance->getCreatedBy()) {
            $entityInstance->setCreatedBy($this->getUser());
        }
        
        parent::persistEntity($entityManager, $entityInstance);
    }
}