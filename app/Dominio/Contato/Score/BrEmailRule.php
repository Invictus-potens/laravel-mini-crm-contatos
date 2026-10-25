<?php

namespace App\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;

final class BrEmailRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        return str_ends_with($contact->email()->value(), '.br') ? 10 : 0;
    }
}