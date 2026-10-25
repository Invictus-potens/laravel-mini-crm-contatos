<?php

namespace App\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;

final class CorporateEmailRule implements ScoreRule
{
    private const FREE_PROVIDERS = ['gmail','hotmail','yahoo'];
    public function score(Contact $contact): int
    {
        $provider = explode('.', $contact->email()->domain())[0];
        return in_array($provider, self::FREE_PROVIDERS, true) ? 0: 20;
    }
}