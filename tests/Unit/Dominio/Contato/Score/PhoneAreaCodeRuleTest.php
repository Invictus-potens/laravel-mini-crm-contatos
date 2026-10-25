<?php

namespace Tests\Unit\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Score\PhoneAreaCodeRule;
use PHPUnit\Framework\TestCase;

class PhoneAreaCodeRuleTest extends TestCase
{
        private function contatoComTelefone(string $telefone): Contact
    {
        return Contact::create('Felipe', new Email('a@empresa.com'), new Phone($telefone));
    }

    public function test_ddd_11_de_sao_paulo_ganha_20_pontos(): void
    {
        $this->assertSame(20, (new PhoneAreaCodeRule())->score($this->contatoComTelefone('(11) 99999-9999')));
    }

    public function test_ddd_19_de_sao_paulo_ganha_20_pontos(): void
    {
        $this->assertSame(20, (new PhoneAreaCodeRule())->score($this->contatoComTelefone('(19) 99999-9999')));
    }

    public function test_ddd_de_outro_estado_ganha_10_pontos(): void
    {
        $this->assertSame(10, (new PhoneAreaCodeRule())->score($this->contatoComTelefone('(21) 99999-9999')));
    }

    public function test_telefone_sem_ddd_nao_ganha_pontos(): void
    {
        $this->assertSame(0, (new PhoneAreaCodeRule())->score($this->contatoComTelefone('99999-9999')));
    }
}