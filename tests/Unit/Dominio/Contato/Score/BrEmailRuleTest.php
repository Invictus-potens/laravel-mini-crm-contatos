<?php

namespace Tests\Unit\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Score\BrEmailRule;
use PHPUnit\Framework\TestCase;


class BrEmailRuleTest extends testCase
{
    private function contatoComemail(string $email): Contact
    {
        return Contact::create('Felipe', new Email($email), new Phone('11999999999'));
    }

    public function test_email_terminado_em_br_ganha_10_pontos(): void
    {
        $rule = new BrEmailRule();

        $this->assertSame(10, $rule->score($this->contatoComemail('felipe@empresa.com.br')));

    }

    public function test_email_sem_br_nao_ganha_pontos(): void
    {
        $rule = new BrEmailRule();

        $this->assertSame(0, $rule->score($this->contatoComemail('felipe@empresa.com')));
    }
}

