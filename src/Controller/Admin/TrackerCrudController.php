<?php

namespace App\Controller\Admin;

use App\Entity\Tracker;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TrackerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tracker::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('status'),
            IntegerField::new('episodesWatched'),
            AssociationField::new('user'),
            AssociationField::new('anime'),
        ];
    }
}