<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\Role;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield TextField::new('firstName', 'Prénom')
            ->setColumns(6)
            ->setRequired(true);

        yield TextField::new('lastName', 'Nom')
        ->setColumns(6)
        ->setRequired(true);

        yield EmailField::new('email')
        ->setColumns(12)
        ->setRequired(true);

        yield ChoiceField::new('role', 'Rôle')
            ->setChoices([
                'Administrateur' => Role::ADMIN,
                'Étudiant'    => Role::ETUDIANT,
                'Formateur' => Role::FORMATEUR,
            ])
            ->setColumns(6);
    }
}
