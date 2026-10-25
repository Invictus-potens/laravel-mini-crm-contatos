<?php

namespace Tests\Unit\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Score\FullNameRule;
use PHPUnit\Framework\TestCase;

class FullNameRuleTest extends TestCase
{
    private function contatoComNome(string $nome): Contact
    {
        return Contact::create($nome, new Email('felipe@empresa.com'), new Phone('11999999999'));
    }

    public function test_nome_completo_ganha_10_pontos(): void
    {
        $rule = new FullNameRule();

        $this->assertSame(10, $rule->score($this->contatoComNome('Felipe Ortiz')));

    }
    public function test_nome_simples_nao_ganha_ponto(): void
    {
         $rule = new FullNamerule();
         $this->assertSame(0, $rule->score($this->contatoComNome('Felipe')));
    }
}
