<?php

namespace Tests\Unit\Aplicacao\Contato;

use App\Aplicacao\Contato\ListContactsUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use PHPUnit\Framework\TestCase;

class ListContactsUseCaseTest extends TestCase
{
    public function test_retorna_a_pagina_pedida(): void
    {
        $contato = Contact::restore(
            7,
            'Felipe Ortiz',
            new Email('felipe@empresa.com.br'),
            new Phone('11999999999'),
            0,
            Status::Pending,
            null, 
        );

        $resultado = ['data' => [$contato], 'total' => 16];

        $repository = $this->createMock(ContactRepository::class);
        $repository->expects($this->once())
            ->method('paginate')
            ->with(2, 15)
            ->willReturn($resultado);

        $pagina = (new ListContactsUseCase($repository))->execute(2, 15);

        $this->assertSame($resultado, $pagina);
    }
}

