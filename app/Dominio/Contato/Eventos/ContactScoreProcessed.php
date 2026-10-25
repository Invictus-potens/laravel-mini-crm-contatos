<?php

namespace App\Dominio\Contato\Eventos;

use App\Dominio\Contato\ObjetosDeValor\Status;
use DateTimeImmutable;

final class ContactScoreProcessed
{
    public function __construct(
        public readonly int $contactId,
        public readonly string $email,
        public readonly int $score,
        public readonly Status $status,
        public readonly ?DateTimeImmutable $processedAt,
    ){

}
}