<?php

namespace App\Aplicacao\Contato;

use App\Dominio\Contato\Eventos\ContactScoreProcessed;
use App\Dominio\Contato\Excecoes\ContactNotFoundException;
use App\Dominio\Contato\Repositorios\ContactRepository;
use App\Dominio\Contato\Score\ScoreCalculator;
use DateTimeImmutable;
use Throwable;

final class CalculateScoreUseCase
{
    public function __construct(
        private readonly ContactRepository $repository,
        private readonly ScoreCalculator $calculator,
        private readonly int $simulatedLoadSeconds = 0,
    ){
    }

    public function execute(int $id): ContactScoreProcessed
    {
        $contact = $this->repository->findbyId($id);

        if ($contact == null){
            throw new ContactNotFoundException($id);
        }

        $contact->startProcessing();
        $contact = $this->repository->save($contact);

        sleep($this->simulatedLoadSeconds);

        try {
            $score = $this->calculator->calculate($contact);
            $contact->completeProcessing($score, new DateTimeImmutable());
        }catch (Throwable){
            $contact->fail();
        }

        $contact = $this->repository->save($contact);

        return new ContactScoreProcessed(
            $contact->id(),
            $contact->email()->value(),
            $contact->score(),
            $contact->status(),
            $contact->processedAt(),
        );
    }

}