<?php

namespace Tests\Feature\Infraestrutura\Http;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Repositorios\ContactRepository;
use App\Infraestrutura\Filas\ProcessContactScoreJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessScoreEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function test_enfileira_o_processamento_do_score(): void
    {
        Queue::fake();

        $contato = app(ContactRepository::class)->save(
            Contact::create('Felipe Ortiz', new Email('felipe@empresa.com.br'), new Phone('1234567890'))
        );

        $this->postJson("api/contacts/{$contato->id()}/process-score")->assertAccepted();

        Queue::assertPushed(
            ProcessContactScoreJob::class,
            fn (ProcessContactScoreJob $job) => $job->contactId === $contato->id()
        );
    }

    public function test_retorna_404_e_nao_enfileira_quando_contato_nao_existe(): void
    {
        Queue::fake();

        $this->postJson('/api/contacts/999/process-score')->assertNotFound();

        Queue::assertNothingPushed();
    }
}