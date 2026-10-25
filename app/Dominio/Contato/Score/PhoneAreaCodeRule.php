<?php

namespace App\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;

final class PhoneAreaCodeRule implements ScoreRule
{
    public function score(Contact $contact): int
    {
        $areaCode = $contact->phone()->areaCode();

        if ($areaCode === null){
            return 0;
        }

        return ($areaCode >= 11 && $areaCode <= 19) ? 20 : 10;
    }
}