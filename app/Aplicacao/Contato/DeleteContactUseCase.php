<?php

namespace App\Aplicacao\Contato;

use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\Repositorios\ContactRepository;


final class DeleteContactUseCase
{
    public function __construct(private readonly COntactRepository $repository)
    {

    }

    public function execute(int $id): void
    {
        if ($this->repository->findById($id) === null){
            throw new ContactNotFoundException($id);
        }
        $this->repository->delete($id);
    }
}