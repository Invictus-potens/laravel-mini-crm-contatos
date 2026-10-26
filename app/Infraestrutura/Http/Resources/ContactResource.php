<?php

namespace App\Infraestrutura\Http\Resources;

use App\Dominio\Contato\Entidades\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Contact $contact */
        $contact = $this->resource;
        
        return [
             'id' => $contact->id(),
            'name' => $contact->name(),
            'email' => $contact->email()->value(),
            'phone' => $contact->phone()->value(),
            'score' => $contact->score(),
            'status' => $contact->status()->value,
            'processed_at' => $contact->processedAt()?->format(DATE_ATOM),
        ];
    }
}