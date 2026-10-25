<?php

namespace Tests\Unit\Dominio\Contato\ObjetosDeValor;

use App\Dominio\Contato\ObjetosDeValor\Phone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_cria_telefone_valido(): void
    {
        $phone = new Phone('(11) 11111-1111');
        $this->assertSame('(11) 11111-1111', $phone->value());
    }

    public function test_rejeita_telefone_vazio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Phone('');
    }

    public function test_extrai_areaCode_de_telefone_formatado(): void
    {
        $phone = new Phone('(11) 11111-1111');
        $this->assertSame(11, $phone->areaCode());
    }

    public function test_extrai_areaCode_de_telefone_sem_formatacao(): void
    {
        $phone = new Phone('11999999999');
        $this->assertSame(11, $phone->areaCode());
    }

    public function test_retorna_null_quando_nao_possui_areaCode(): void
    {
        $phone = new Phone('111111111');
        $this->assertNull($phone->areaCode());
    }
}
