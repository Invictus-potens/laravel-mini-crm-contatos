<?php

namespace App\Infraestrutura\Transmissao;

use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ContactScoreBroadcast implements ShouldBroadcastNow
{
    public function __construct(public readonly ContactScoreProcessed $evento)
    {

    }
    public function broadcastOn(): array
    {
        return [new Channel("contacts.{$this->evento->contactId}")];
    }

    public function broadcastAs(): string
    {
        return 'contact.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->evento->contactId,
            'email' => $this->evento->email,
            'score' => $this->evento->score,
            'status' => $this->evento->status->value,
            'processed_at' => $this->evento->processedAt?->format(DATE_ATOM),
        ];
    }
}