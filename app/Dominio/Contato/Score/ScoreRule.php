<?php

namespace App\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;

interface ScoreRule
{
    public function score(Contact $contact): int;
}

