<?php

namespace Tests\Unit\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Score\ScoreCalculator;
use App\Dominio\Contato\Score\ScoreRule;
use PHPUnit\Framework\TestCase;

class ScoreCalculatorTest extends TestCase
{
    private function regraQueVale(int $pontos): ScoreRule
    {
        return new class($pontos) implements ScoreRule {
            public function __construct(private int $pontos){}
            public function score(Contact $contact): int
            {
                return $this->pontos;
            }
        };
    }

    private function contato(): Contact
    {
        return Contact::create('Felipe', new Email('felipe@empresa.com'), new Phone('11999999999'));
    }

    public function test_soma_os_pontos_de_todas_as_regras(): void
    {
        $calculator = new ScoreCalculator([
            $this->regraQueVale(20),
            $this->regraQueVale(10),
            $this->regraQueVale(5),
        ]);

        $this->assertSame(35, $calculator->calculate($this->contato()));
    }

    public function test_sem_regras_o_score_e_zero(): void
    {
        $this->assertSame(0, (new ScoreCalculator([]))->calculate($this->contato()));
    }
}

