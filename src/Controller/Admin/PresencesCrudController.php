<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use App\Entity\Presences;

class PresencesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Presences::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('presence_user', 'ID Utilisateur')
                ->formatValue(fn ($v, $e) => $e->getPresenceUser()?->getId()),

            AssociationField::new('presences_session', 'ID Séance')
                ->formatValue(fn ($v, $e) => $e->getPresencesSession()?->getId()),

                TextField::new('signature')
                ->onlyOnIndex()
                ->formatValue(fn ($value) => sprintf(
                    '<img src="%s" style="height: 60px; background-color: #f0f0f0; padding: 4px; border-radius: 4px;" />',
                    $value
                ))
                ->renderAsHtml(),
            
            
            DateTimeField::new('horodatage'),
        ];
    }
}

