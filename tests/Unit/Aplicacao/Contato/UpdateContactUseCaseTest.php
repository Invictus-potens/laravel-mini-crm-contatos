<?php

namespace Tests\Unit\Aplicacao\Contato;

use App\Aplicacao\Contato\UpdateContactUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use PHPUnit\Framework\TestCase;

class UpdateContactUseCaseTest extends TestCase
{
    private function contatoExistente(): Contact
    {
        return Contact::restore(
            7,
            'Felipe Ortiz',
            new Email('felipe@empresa.com.br'),
            new Phone('11999999999'),
            0,
            Status::Pending,
            null,
        );
    }

    public function test_atualiza_os_dados_e_salva(): void
    {
        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->with(7)->willReturn($this->contatoExistente());
        $repository->expects($this->once())
        ->method('save')
        ->willReturnArgument(0);

        $contact = (new UpdateContactUseCase($repository))
            ->execute(7, 'Lucas Emiliano', 'lucas@empresa.com.br', '11988888888');

            $this->assertSame(7, $contact->id());
            $this->assertSame('Lucas Emiliano', $contact->name());
            $this->assertSame('lucas@empresa.com.br', $contact->email()->value());
            $this->assertSame('11988888888', $contact->phone()->value());
    }

    public function test_lanca_excecao_e_nao_salva_quando_contato_nao_existe(): void
    {
        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->willReturn(Null);
        $repository->expects($this->never())->method('save');

        $this->expectException(ContactNotFoundException::class);
        (new UpdateContactUseCase($repository))
            ->execute(99, 'Lucas Emiliano', 'lucas@empresa.com.br', '11988888888');
    }

}