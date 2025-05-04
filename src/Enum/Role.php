<?php

namespace App\Enum;

enum Role: string
{
    case ETUDIANT = 'etudiant';
    case FORMATEUR = 'formateur';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::ETUDIANT => 'Étudiant',
            self::FORMATEUR => 'Formateur',
            self::ADMIN => 'Administrateur',
        };
    }
}
