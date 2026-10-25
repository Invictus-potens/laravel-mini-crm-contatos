<?php

namespace App\Aplicacao\Contato;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Repositorios\ContactRepository;

final class ListContactsUseCase
{
    public function __construct(private readonly ContactRepository $repository)
    {

    }

    /** @return array{data: Contact[], total: int} */
    public function execute(int $page, int $perPage): array
    {
        return $this->repository->paginate($page, $perPage);
    }
}