<?php

namespace App\Dominio\Contato\Entidades;

use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\ObjetosDeValor\Status;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;


final class Contact
{
    private function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private Phone $phone,
        private int $score,
        private Status $status,
        private ?DateTimeImmutable $processedAt,

    ){
        if (trim($name) == ''){
            throw new InvalidArgumentException('O nome do contato não pode ser vazio.');
        }
    }

    public static function create(string $name, Email $email, Phone $phone): self
    {
        return new self(null, $name, $email, $phone, 0, Status::Pending, null);
    }

    public static function restore(

        int $id,
        string $name,
        Email $email,
        Phone $phone,
        int $score,
        Status $status,
        ?DateTimeImmutable $processedAt,

    ): self{
        return new self($id, $name, $email, $phone, $score, $status, $processedAt);
    }

    public function update(string $name, Email $email, Phone $phone): void
    {
        if (trim($name) == ''){
            throw new InvalidArgumentException('O nome do contato é obrigatorio.');
        }
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function startProcessing(): void
    {
        $this->status = Status::Processing;
    }

    public function completeProcessing(int $score, DateTimeImmutable $processedAt): void
    {
        $this->assertIsProcessing();
        $this->score = $score;
        $this->status = Status::Active;
        $this->processedAt = $processedAt;
    }

    public function fail(): void
    {
        $this->assertIsProcessing();
        $this->status = Status::Failed;
    }

    private function assertIsProcessing(): void
    {
        if ($this->status !== Status::Processing){
            throw new DomainException('O contato não está em processamento.');
        }
    }

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function email(): Email { return $this->email; }
    public function phone(): Phone { return $this->phone; }
    public function score(): int { return $this->score; }
    public function status(): Status { return $this->status; }
    public function processedAt(): ?DateTimeImmutable { return $this->processedAt; }
}
