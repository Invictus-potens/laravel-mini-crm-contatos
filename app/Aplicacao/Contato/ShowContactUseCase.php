<?php

namespace App\Aplicacao\Contato;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\Repositorios\ContactRepository;

final class ShowContactUseCase
{
    public function __construct(private readonly ContactRepository $repository)
    {

    }

    public function execute(int $id): Contact
    {
        $contact = $this->repository->findById($id);
        
        if ($contact === null){
            throw new ContactNotFoundException($id);
        }

        return $contact;
    }
}