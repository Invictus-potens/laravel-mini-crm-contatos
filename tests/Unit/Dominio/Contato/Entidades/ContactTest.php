<?php

namespace Tests\Unit\Dominio\Contato\Entidades;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContactTest extends testCase
{
    private function novoContato(): Contact
    {
        return Contact::create(
            'Felipe Ortiz',
            new Email('felipe@email.com'),
            new Phone('1234567890'),
        );

    }

    public function test_cria_contato_pendente_com_score_zero(): void
    {
        $contact = $this->novoContato();

        $this->assertNull($contact->id());
        $this->assertEquals('Felipe Ortiz', $contact->name());
        $this->assertEquals('felipe@email.com', $contact->email()->value());
        $this->assertEquals('1234567890', $contact->phone()->value());
        $this->assertSame(0, $contact->score());
        $this->assertSame(Status::Pending, $contact->status());
        $this->assertNull($contact->processedAt());
    }

    public function test_rejeita_nome_vazio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Contact::create('', new Email('email@email.com'),new Phone('1234567890'));
    }

    public function test_atualiza_dados_do_contato(): void
    {
        $contact = $this->novoContato();
        $contact->update('Felipe Melo', new Email('melo@email.com'), new Phone('0987654321'));

        $this->assertSame('Felipe Melo', $contact->name());
        $this->assertSame('melo@email.com', $contact->email()->value());
        $this->assertSame('0987654321', $contact->phone()->value());
    }

    public function test_inicia_processamento(): void
    {
        $contact = $this->novoContato();
        $contact->startProcessing();

        $this->assertSame(Status::Processing, $contact->status());

    }

    public function test_finaliza_processamento_com_score(): void
    {
        $contact = $this->novoContato();
        $agora = new DateTimeImmutable('2026-10-25 10:00:00');

        $contact->startProcessing();
        $contact->completeProcessing(40, $agora);

        $this->assertSame(Status::Active, $contact->status());
        $this->assertSame(40, $contact->score());
        $this->assertSame($agora, $contact->processedAt());
    }

    public function test_nao_finaliza_sem_iniciar_processamento(): void
    {
        $this->expectException(DomainException::class);
        $this->novoContato()->completeProcessing(40, new DateTimeImmutable());

    }

    public function test_marca_falha_no_processamento(): void
    {

        $contact = $this->novoContato();
        $contact->startProcessing();
        $contact->fail();

        $this->assertSame(Status::Failed, $contact->status());
        }

        public function test_nao_marca_falha_sem_iniciar_processamento(): void
        {
            $this->expectException(DomainException::class);
            $this->novoContato()->fail();
        }

        public function test_restaura_contato_existente(): void
        {
            $processadoEm = new DateTimeImmutable('2026-10-25 10:00:00');
            $contact = Contact::restore(
                1,
                'Felipe Ortiz',
                new Email('felipe@email.com'),
                new Phone('1234567890'),
                40,
                Status::Active,
                $processadoEm,
            );

            $this->assertSame(1, $contact->id());
            $this->assertSame(40, $contact->score());
            $this->assertSame(Status::Active, $contact->status());
            $this->assertSame($processadoEm, $contact->processedAt());
        }
}




