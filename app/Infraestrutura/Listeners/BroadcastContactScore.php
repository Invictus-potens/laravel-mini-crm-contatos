<?php

namespace App\Infraestrutura\Listeners;

use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Infraestrutura\Transmissao\ContactScoreBroadcast;

class BroadcastContactScore
{
    public function handle(ContactScoreProcessed $event): void
    {
        event(new ContactScoreBroadcast($event));
    }
}