<?php

namespace App\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;

final class ScoreCalculator
{
    /** @param ScoreRule[] $rules */
    public function __construct(private readonly array $rules){

    }

    public function calculate(Contact $contact): int
    {
        $total = 0;

        foreach ($this->rules as $rule){
            $total += $rule->score($contact);
        }
        return $total;
    }
}