<?php

namespace Tests\Feature\Infraestrutura\Listeners;

use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Infraestrutura\Listeners\LogContactScore;
use DateTimeImmutable;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LogContactScoreTest extends TestCase
{
    private string $arquivo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->arquivo = storage_path('logs/contact.log');
        File::delete($this->arquivo);
    }

    public function test_grava_id_email_score_e_status_no_contact_log(): void
    {
        $evento = new ContactScoreProcessed(
            7,
            'felipe@empresa.com.br',
            60,
            Status::Active,
            new DateTimeImmutable(),
        );

        (new LogContactScore())->handle($evento);

        $this->assertFileExists($this->arquivo);

        $conteudo = File::get($this->arquivo);

        $this->assertStringContainsString('"contact_id":7', $conteudo);
        $this->assertStringContainsString('"email":"felipe@empresa.com.br"', $conteudo);
        $this->assertStringContainsString('"score":60', $conteudo);
        $this->assertStringContainsString('"status":"active"', $conteudo);
    }
}