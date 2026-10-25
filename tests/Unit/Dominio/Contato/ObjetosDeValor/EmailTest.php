<?php

namespace Tests\Unit\Dominio\Contato\ObjetosDeValor;

use App\Dominio\Contato\ObjetosDeValor\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_cria_email_valido(): void
    {
        $email = new Email('felipe@email.com');
        $this->assertSame('felipe@email.com', $email->value());
    }

    public function test_rejeita_email_com_formato_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('nao-e-um-email-valido');
    }

    public function test_rejeita_email_vazio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('');
    }

    public function test_retorna_o_dominio_do_email(): void
    {
        $email = new Email('felipe@email.com');
        $this->assertSame('email.com', $email->domain());
    }
}
