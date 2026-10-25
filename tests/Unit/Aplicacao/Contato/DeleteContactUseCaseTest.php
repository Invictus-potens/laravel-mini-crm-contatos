<?php

namespace Tests\Unit\Aplicacao\Contato;

use App\Aplicacao\Contato\DeleteContactUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use PHPUnit\Framework\TestCase;

class DeleteContactUseCaseTest extends TestCase
{
    public function test_exclui_contato_existente(): void
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
        
        $repository =$this->createMock(ContactRepository::class);
        $repository->method('findById')->with(7)->willReturn($existente);
        $repository->expects($this->once())->method('delete')->with(7);

        (new DeleteContactUseCase($repository))->execute(7);
    }

    public function test_lanca_excecao_quando_contato_nao_existe(): void
    {
        $repository =$this->createMock(ContactRepository::class);
        $repository->method('findById')->willReturn(null);
        $repository->expects($this->never())->method('delete');

        $this->expectException(ContactNotFoundException::class);

        (new DeleteContactUseCase($repository))->execute(99);
    }
}