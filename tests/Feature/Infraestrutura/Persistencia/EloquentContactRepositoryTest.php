<?php

namespace Tests\Feature\Infraestrutura\Persistencia;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Infraestrutura\Persistencia\EloquentContactRepository;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentContactRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentContactRepository $repository;

    protected function setUp(): void
    {
        Parent::setUp();
        $this->repository = new EloquentContactRepository();
    }

    private function novoContato(string $email = 'felipe@empresa.com.br'): Contact
    {
       return Contact::create('Felipe Ortiz', new Email($email), new Phone('11999999999'));
    }

    public function test_salva_contato_novo_e_gera_id(): void
    {
        $salvo = $this->repository->save($this->novoContato());

        $this->assertNotNull($salvo->id());
        $this->assertDatabaseHas('contacts', [
            'id' => $salvo->id(),
            'email' => 'felipe@empresa.com.br',
            'score' => 0,
            'status' => 'pending',
        ]);
    }

    public function test_atualiza_contato_existente_sem_duplicar(): void
    {
    $salvo = $this->repository->save($this->novoContato());

    $salvo->update('Ana Souza', new Email('ana@empresa.com'), new Phone('21988887777'));
    $this->repository->save($salvo);

    $this->assertDatabaseCount('contacts', 1);
    $this->assertDatabaseHas('contacts', ['id' => $salvo->id(), 'name' => 'Ana Souza']);
    }

    public function test_busca_contato_por_id(): void
    {
        $salvo = $this->repository->save($this->novoContato());

        $encontrado = $this->repository->findById($salvo->id());

        $this->assertSame($salvo->id(), $encontrado->id());
        $this->assertSame('Felipe Ortiz', $encontrado->name());
        $this->assertSame('felipe@empresa.com.br', $encontrado->email()->value());
        $this->assertSame(Status::Pending, $encontrado->status());
    }
    
    public function test_retorna_null_quando_contato_nao_existe(): void
    {
        $this->assertNull($this->repository->findById(999));
    }

     public function test_persiste_resultado_do_processamento(): void
    {
        $contato = $this->repository->save($this->novoContato());
        $processadoEm = new DateTimeImmutable('2026-10-25 10:00:00');

        $contato->startProcessing();
        $contato->completeProcessing(60, $processadoEm);
        $this->repository->save($contato);

        $encontrado = $this->repository->findById($contato->id());

        $this->assertSame(60, $encontrado->score());
        $this->assertSame(Status::Active, $encontrado->status());
        $this->assertSame('2026-10-25 10:00:00', $encontrado->processedAt()->format('Y-m-d H:i:s'));
    }

    public function test_pagina_os_contatos(): void
    {
        $this->repository->save($this->novoContato('a@empresa.com'));
        $this->repository->save($this->novoContato('b@empresa.com'));
        $this->repository->save($this->novoContato('c@empresa.com'));

        $pagina = $this->repository->paginate(1, 2);

        $this->assertCount(2, $pagina['data']);
        $this->assertSame(3, $pagina['total']);
        $this->assertInstanceOf(Contact::class, $pagina['data'][0]);
    }

    public function test_exclusao_e_soft_delete(): void
    {
        $salvo = $this->repository->save($this->novoContato());
        $this->repository->delete($salvo->id());

        $this->assertSoftDeleted('contacts', ['id' => $salvo->id()]);
        $this->assertNull($this->repository->findById($salvo->id()));
    }
}