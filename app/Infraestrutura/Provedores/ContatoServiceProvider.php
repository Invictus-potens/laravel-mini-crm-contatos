<?php

namespace App\Infraestrutura\Provedores;

use App\Aplicacao\Contato\CalculateScoreUseCase;
use App\Dominio\Contato\Repositorios\ContactRepository;
use App\Dominio\Contato\Score\BrEmailRule;
use App\Dominio\Contato\Score\CorporateEmailRule;
use App\Dominio\Contato\Score\FullNameRule;
use App\Dominio\Contato\Score\PhoneAreaCodeRule;
use App\Dominio\Contato\Score\ScoreCalculator;
use App\Infraestrutura\Persistencia\EloquentContactRepository;
use Illuminate\Support\ServiceProvider;
use App\Infraestrutura\Observadores\ContactObserver;
use App\Infraestrutura\Persistencia\ContactModel;
use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Infraestrutura\Listeners\BroadcastContactScore;
use App\Infraestrutura\Listeners\LogContactScore;
use Illuminate\Support\Facades\Event;

class ContatoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContactRepository::class, EloquentContactRepository::class);

        $this->app->bind(ScoreCalculator::class, fn () => new ScoreCalculator([
            new BrEmailRule(),
            new CorporateEmailRule(),
            new FullNameRule(),
            new PhoneAreaCodeRule(),
        ]));

        $this->app->bind(CalculateScoreUseCase::class, fn ($app) => new CalculateScoreUsecase(
            $app->make(ContactRepository::class),
            $app->make(ScoreCalculator::class),
            2,
        ));
    }

    public function boot(): void
    {
        ContactModel::observe(ContactObserver::class);

        Event::listen(ContactScoreProcessed::class, LogContactScore::class);
        Event::listen(ContactScoreProcessed::class, BroadcastContactScore::class);
    }
}