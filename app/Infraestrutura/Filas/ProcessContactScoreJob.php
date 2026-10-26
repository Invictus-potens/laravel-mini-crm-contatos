<?php

namespace App\Infraestrutura\Filas;

use App\Aplicacao\Contato\CalculateScoreUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessContactScoreJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $contactId)
    {
    }

    public function handle(CalculateScoreuseCase $useCase): void
    {
        $evento = $useCase->execute($this->contactId);

        event($evento);
    }
}