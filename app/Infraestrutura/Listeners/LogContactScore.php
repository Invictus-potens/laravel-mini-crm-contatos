<?php

namespace App\Infraestrutura\Listeners;

use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use Illuminate\Support\Facades\Log;

class LogContactScore
{
    public function handle(ContactScoreprocessed $event): void
    {
         Log::channel('contact')->info('Score do contato processado', [
            'contact_id' => $event->contactId,
            'email' => $event->email,
            'score' => $event->score,
            'status' => $event->status->value,
        ]);
    }
}