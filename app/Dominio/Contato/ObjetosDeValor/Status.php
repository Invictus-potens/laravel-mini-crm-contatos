<?php

namespace App\Dominio\Contato\ObjetosDeValor;

enum Status: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Active = 'active';
    case Failed = 'failed';
}
