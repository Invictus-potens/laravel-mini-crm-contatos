<?php

namespace Tests\Unit\Dominio\Contato\Score;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Score\CorporateEmailRule;
use PHPUnit\Framework\TestCase;

class CorporateEmailRuleTest extends TestCase
{
    private function contatoComEmail(string $email): Contact
    {
        return Contact::create('Felipe', new Email($email), new Phone('11999999999'));
    }

    public function test_email_corporativo_ganha_20_pontos(): void
    {
        $rule = new CorporateEmailRule();

        $this->assertSame(20, $rule->score($this->contatoComemail('felipe@empresa.com')));
    }

    public function test_gmail_nao_ganha_pontos(): void
    {
        $rule = new CorporateEmailRule();

        $this->assertSame(0, $rule->score($this->contatoComemail('felipe@gmail.com')));
    }

    
    public function test_hotmail_nao_ganha_pontos(): void
    {
        $rule = new CorporateEmailRule();

        $this->assertSame(0, $rule->score($this->contatoComemail('felipe@hotmail.com')));
    }

    public function test_yahoo_nao_ganha_pontos(): void
    {
        $rule = new CorporateEmailRule();

        $this->assertSame(0, $rule->score($this->contatoComemail('felipe@yahoo.com')));
    }

}