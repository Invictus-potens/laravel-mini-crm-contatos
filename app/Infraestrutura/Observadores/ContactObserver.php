<?php

namespace App\Infraestrutura\Observadores;
use App\Infraestrutura\Persistencia\ContactModel;

class ContactObserver
{
    public function saving(ContactModel $contact): void
    {
        $contact->phone = preg_replace('/\D/', '', $contact->phone);
    }
}