<?php

namespace Tests\Unit\Dominio\Contato\ObjetosDeValor;

use App\Dominio\Contato\ObjetosDeValor\Status;
use PHPUnit\Framework\TestCase;
use ValueError;

class StatusTest extends TestCase
{
    public function test_possui_os_quatro_status(): void
    {

        $this->assertSame('pending', Status::Pending->value);
        $this->assertSame('processing', Status::Processing->value);
        $this->assertSame('active', Status::Active->value);
        $this->assertSame('failed', Status::Failed->value);

    }

    public function test_cria_status_a_partir_do_texto(): void
    {
        $this->assertSame(Status::Active, Status::from('active'));
    }

    public function test_rejeita_status_inexistente(): void
    {
        $this->expectException(ValueError::class);
        Status::from('qualquercoisa');
    }
}
