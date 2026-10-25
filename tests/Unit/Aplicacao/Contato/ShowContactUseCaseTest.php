<?php

namespace Tests\Unit\Aplicacao\Contato;

use App\Aplicacao\Contato\ShowContactUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use PHPUnit\Framework\TestCase;

class ShowContactUseCaseTest extends TestCase
{
    public function test_retorna_o_contato_encontrado()
: void
{
    $existente = Contact::restore(
        7,
        'Felipe Ortiz',
        new Email('felipe@empresa.com.br'),
        new Phone('11999999999'),
        0,
        Status::Pending,
        null,
    );

    $repository = $this->createMock(ContactRepository::class);
    $repository->expects($this->once())
        ->method('findById')
        ->with(7)
        ->willReturn($existente);

        $contact = (new ShowContactUseCase($repository))->execute(7);
        $this->assertSame($existente, $contact);
    }

    public function test_lanca_excecao_quando_contato_nao_existe(): void
    {
        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->willReturn(null);

        $this->expectException(ContactNotFoundException::class);
        (new ShowContactUseCase($repository))->execute(99);
    }
}

