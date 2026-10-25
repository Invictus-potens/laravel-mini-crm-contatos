<?php

namespace App\Dominio\Contato\ObjetosDeValor;

use invalidArgumentException;

final class Email
{
    private readonly string $value;
    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("email invalido {$value}.");
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr(strrchr($this->value, "@"), 1);
    }
}
