<?php

namespace App\Aplicacao\Contato;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Repositorios\ContactRepository;

final class UpdateContactUseCase
{
    public function __construct(private readonly ContactRepository $repository)
    {
    }

    public function execute(int $id, string $name, string $email, string $phone): Contact
    {
        $contact = $this->repository->findById($id);

        if ($contact === null){
            throw new ContactNotFoundException($id);
        }

        $contact->update($name, new Email($email), new Phone($phone));

        return $this->repository->save($contact);
    }
}