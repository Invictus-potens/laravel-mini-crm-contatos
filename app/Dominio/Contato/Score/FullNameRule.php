<?php

namespace App\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;

final class FullNameRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        $words = preg_split('/\s+/', $contact->name());
        return count($words) > 1 ? 10 : 0;
    }
}