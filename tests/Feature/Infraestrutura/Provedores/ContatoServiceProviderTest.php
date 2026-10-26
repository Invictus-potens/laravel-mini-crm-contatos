<?php

namespace Tests\Feature\Infraestrutura\Provedores;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Repositorios\ContactRepository;
use App\Dominio\Contato\Score\ScoreCalculator;
use App\Infraestrutura\Persistencia\EloquentContactRepository;
use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Infraestrutura\Listeners\BroadcastContactScore;
use App\Infraestrutura\Listeners\LogContactScore;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ContatoServiceProviderTest extends TestCase
{
    public function test_resolve_repositorio_eloquent_pela_interface(): void
    {
        $this->assertInstanceOf(EloquentContactRepository::class, app(ContactRepository::class));
    }

    public function test_calculator_usa_as_quatro_regras_do_desafio(): void
    {
        $contato = Contact::create(
            'Felipe Ortiz',
            new Email('felipe@empresa.com.br'),
            new Phone('(11) 99999-9999'),
        );

        $this->assertSame(60, app(ScoreCalculator::class)->calculate($contato));
    }

    public function test_registra_os_listeners_do_evento_de_score(): void
    {
        Event::fake();

        Event::assertListening(ContactScoreProcessed::class, LogContactScore::class);
        Event::assertListening(ContactScoreProcessed::class, BroadcastContactScore::class);
    }
}