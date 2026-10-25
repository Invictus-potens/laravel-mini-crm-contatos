<?php

namespace Tests\Unit\Aplicacao\Contato;

use App\Aplicacao\Contato\CreateContactUseCase;
use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Status;
use App\Dominio\Contato\Repositorios\ContactRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreateContactUseCaseTest extends TestCase
{
    public function test_cria_contato_pendente_e_salva_no_repositorio(): void
    {
        $repository = $this->createMock(ContactRepository::class);

        $repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Contact $contact){
                $this->assertSame(Status::Pending, $contact->status());
                $this->assertSame(0, $contact->score());

                return Contact::restore(
                    1,
                    $contact->name(),
                    $contact->email(),
                    $contact->phone(),
                    $contact->score(),
                    $contact->status(),
                    $contact->processedAt()
                );
            });
            
            $useCase = new CreateContactUseCase($repository);
            $contact = $useCase->execute('Felipe Ortiz', 'felipe@empresa.com.br', '11999999999');

            $this->assertSame(1, $contact->id());
            $this->assertSame('Felipe Ortiz', $contact->name());
    }

    public function test_nao_salva_quando_email_e_invalido(): void
    {
        $repository = $this->createMock(ContactRepository::class);
        $repository->expects($this->never())->method('save');

        $this->expectException(InvalidArgumentException::class);

        (new CreateContactUseCase($repository))->execute('Felipe', 'email-invalido', '11999999999');
    }
}
