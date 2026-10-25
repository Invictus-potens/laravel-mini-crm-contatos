<?php

namespace App\Dominio\Contato\Repositorios;

use App\Dominio\Contato\Entidades\Contact;

interface ContactRepository
{
    public function save(Contact $contact): void;
    public function findById(int $id): ?Contact;

    /** @return array{data: Contact[], total: int} */

    public function paginate(int $page, int $perpage): array;
    public function delete(int $id): void;

}