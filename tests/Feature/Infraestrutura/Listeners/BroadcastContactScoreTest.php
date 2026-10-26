<?php

namespace Tests\Feature\Infraestrutura\Listeners;

use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Infraestrutura\Listeners\BroadcastContactScore;
use App\Infraestrutura\Transmissao\ContactScoreBroadcast;
use DateTimeImmutable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastContactScoreTest extends TestCase
{
    private function evento(): ContactScoreProcessed
    {
        return new ContactScoreProcessed(
               7,
            'felipe@empresa.com.br',
            60,
            Status::Active,
            new DateTimeImmutable('2026-10-25 10:00:00'),
        );
    }

    public function test_dispara_o_broadcast_do_contato(): void
    {
        Event::fake([ContactScoreBroadcast::class]);

        (new BroadcastContactScore())->handle($this->evento());

        Event::assertDispatched(
            ContactScoreBroadcast::class,
            fn (ContactScoreBroadcast $broadcast) => $broadcast->evento->contactId === 7
        );
    }

    public function test_transmite_no_canal_do_contato(): void
    {
        $broadcast = new ContactScoreBroadcast($this->evento());

        $canais = $broadcast->broadcastOn();

        $this->assertInstanceOf(ShouldBroadcastNow::class, $broadcast);
        $this->assertCount(1, $canais);
        $this->assertInstanceOf(Channel::class, $canais[0]);
        $this->assertSame('contacts.7', $canais[0]->name);
    }

    public function test_envia_os_dados_atualizados_do_contato(): void
    {
        $broadcast = new ContactScoreBroadcast($this->evento());

        $this->assertSame('contact.updated', $broadcast->broadcastAs());
        $this->assertSame([
            'id' => 7,
            'email' => 'felipe@empresa.com.br',
            'score' => 60,
            'status' => 'active',
            'processed_at' => '2026-10-25T10:00:00+00:00',
        ], $broadcast->broadcastWith());
    }
}