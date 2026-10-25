<?php

namespace Tests\Unit\Aplicacao\Contato;

use App\Aplicacao\Contato\CalculateScoreUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use App\Dominio\Contato\Score\ScoreCalculator;
use App\Dominio\Contato\Score\ScoreRule;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CalculateScoreUseCaseTest extends TestCase
{
    private function contatoPendente(): Contact
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

    private function regraQueVale(int $pontos): ScoreRule
    {
        return new class($pontos) implements ScoreRule{
            public function __construct(private int $pontos){            
            }

            public function score(Contact $contact): int
            {
                return $this->pontos;
            }  
        };
    }

    private function regraQueFalha(): ScoreRule
    {
        return new class implements ScoreRule
        {
            public function score(Contact $contact): int
            {
                throw new RuntimeException('Erro ao calcular pontuação');
            }
        };
    }

    private function test_calcula_score_ativaa_contato_e_retorna_evento(): void
    {
        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->with(7)->willReturn($this->contatoPendente());
        $repository->method('save')->willReturnArgument(0);

        $calculator = new ScoreCalculator([$this->regraQueVale(20), $this->regraQueVale(10)]);

        $evento = (new CalculateScoreUseCase($repository, $calculator))->execute(7);

        $this->assertInstanceOf(ContactScoreProcessed::class, $evento);
        $this->assertSame(7, $evento->contactId);
        $this->assertSame('felipe@empresa.com.br', $evento->email);
        $this->assertSame(30, $evento->score);
        $this->assertSame(Status::Active, $evento->status);
        $this->assertNotNull($evento->processedAt);
    }

    public function test_salva_como_processing_antes_de_salvar_como_active(): void
    {
        $statusSalvos = [];

        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->willReturn($this->contatoPendente());
        $repository->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function (Contact $contact) use (&$statusSalvos) {
                $statusSalvos[] = $contact->status();

                return $contact;
            });
            
            $calculator = new ScoreCalculator([$this->regraQueVale(10)]);

            (new CalculateScoreUseCase($repository, $calculator))->execute(7);

            $this->assertSame([Status::Processing, Status::Active], $statusSalvos);
    }

    public function test_marca_como_failed_quando_uma_regra_falha(): void
    {
        $statusSalvos = [];

        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->willReturn($this->contatoPendente());
        $repository->method('save')
            ->willReturnCallback(function (Contact $contact) use (&$statusSalvos) {
                $statusSalvos[] = $contact->status();

                return $contact;
            });

            $calculator = new ScoreCalculator([$this->regraQueFalha()]);

            $evento = (new CalculateScoreUseCase($repository, $calculator))->execute(7);

            $this->assertSame(Status::Failed, $evento->status);
            $this->assertSame(0, $evento->score);
            $this->assertSame([Status::Processing, Status::Failed], $statusSalvos);
    }

    Public function test_lanca_excecao_quando_contato_nao_existe(): void
    {
        $repository = $this->createMock(ContactRepository::class);
        $repository->method('findById')->willReturn(null);
        $repository->expects($this->never())->method('save');
        
        $this->expectException(ContactNotFoundException::class);
        
        (new CalculateScoreUseCase($repository, new ScoreCalculator([])))->execute(99);
    }
}