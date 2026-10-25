<?php

namespace App\Dominio\Contato\Excecoes;

use DomainException;

final class ContatoNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("Contato {$id} não encontrado.");
    }
}