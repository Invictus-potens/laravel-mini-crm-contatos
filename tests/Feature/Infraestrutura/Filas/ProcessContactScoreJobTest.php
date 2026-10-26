<?php

namespace Tests\Feature\Infraestrutura\Filas;

use App\Aplicacao\Contato\CalculateScoreUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use App\Dominio\Contato\Score\ScoreCalculator;
use App\Infraestrutura\Filas\ProcessContactScoreJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProcessContactScoreJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(CalculateScoreUseCase::class, fn ($app) => new CalculateScoreUseCase(
            $app->make(ContactRepository::class),
            $app->make(ScoreCalculator::class),
            0,
        ));
    }

    public function test_job_e_enfileiravel(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new ProcessContactScoreJob(1));
    }

    public function test_processa_score_salva_resultado_e_dispara_evento(): void
    {
        Event::fake([ContactScoreProcessed::class]);

        $repository = app(ContactRepository::class);
        $contato = $repository->save(
            Contact::create('Felipe Ortiz', new Email('felipe@empresa.com.br'), new Phone('11999999999'))
        );

        ProcessContactScoreJob::dispatchSync($contato->id());

        $processado = $repository->findById($contato->id());

        $this->assertSame(Status::Active, $processado->status());
        $this->assertSame(60, $processado->score());
        $this->assertNotNull($processado->processedAt());

        Event::assertDispatched(
            ContactScoreProcessed::class,
            fn (ContactScoreProcessed $evento) => $evento->contactId === $contato->id()
            && $evento->score === 60
            && $evento->status === Status::Active
        );
    }
}