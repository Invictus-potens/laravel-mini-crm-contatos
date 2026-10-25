<?php

namespace App\Dominio\Contato\ObjetosDeValor;

use InvalidArgumentException;

final class Phone
{
    private readonly string $value;

    public function __construct(string $value)
    {
        if (trim($value) == ''){
            throw new InvalidArgumentException("telefone é obrigatorio.");
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function areaCode(): ?int
    {
        $digits = preg_replace('/\D/', '', $this->value);
        if (strlen($digits) < 10 || strlen($digits) > 11) {
            return null;
        }
        return (int) substr($digits, 0, 2);
    }

}
