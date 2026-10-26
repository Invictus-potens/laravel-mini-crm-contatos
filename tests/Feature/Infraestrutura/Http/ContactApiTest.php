<?php

namespace Tests\Feature\Infraestrutura\Http;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Repositorios\ContactRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    private function contatoExistente(string $email = 'felipe@empresa.com.br'): Contact
    {
        return app(ContactRepository::class)->save(
            Contact::create('Felipe Ortiz', new Email($email), new Phone('11999999999'))
        );
    }

    public function test_cria_contato_pendente_com_score_zero(): void
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'Felipe Ortiz',
            'email' => 'felipe@empresa.com.br',
            'phone' => '11999999999',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Felipe Ortiz')
            ->assertJsonPath('data.email', 'felipe@empresa.com.br')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.score', 0)
            ->assertJsonPath('data.processed_at', null);

        $this->assertDatabaseHas('contacts', ['email' => 'felipe@empresa.com.br']);
    }

    public function test_valida_campos_obrigatorios(): void
    {
        $this->postJson('/api/contacts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'phone']);
    }

    public function test_valida_formato_do_email(): void
    {
        $this->postJson('/api/contacts', [
            'name' => 'Felipe Ortiz',
            'email' => 'email-invalido',
            'phone' => '11999999999',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_valida_email_unico(): void
    {
        $this->contatoExistente('felipe@empresa.com.br');

        $this->postJson('/api/contacts', [
            'name' => 'Outro Felipe',
            'email' => 'felipe@empresa.com.br',
            'phone' => '11988887777',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_lista_contatos_com_paginacao(): void
    {
        $this->contatoExistente('a@empresa.com');
        $this->contatoExistente('b@empresa.com');
        $this->contatoExistente('c@empresa.com');

        $this->getJson('/api/contacts')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_mostra_contato(): void
    {
        $contato = $this->contatoExistente();

        $this->getJson("/api/contacts/{$contato->id()}")
            ->assertOk()
            ->assertJsonPath('data.id', $contato->id())
            ->assertJsonPath('data.name', 'Felipe Ortiz');
    }

    public function test_retorna_404_quando_contato_nao_existe(): void
    {
        $this->getJson('/api/contacts/999')->assertNotFound();
    }

    public function test_atualiza_contato_mantendo_o_mesmo_email(): void
    {
        $contato = $this->contatoExistente('felipe@empresa.com.br');

        $this->putJson("/api/contacts/{$contato->id()}", [
            'name' => 'Felipe Ortiz Silva',
            'email' => 'felipe@empresa.com.br',
            'phone' => '21988887777',
        ])->assertOk()->assertJsonPath('data.name', 'Felipe Ortiz Silva');

        $this->assertDatabaseHas('contacts', ['id' => $contato->id(), 'name' => 'Felipe Ortiz Silva']);
    }

    public function test_exclui_contato_com_soft_delete(): void
    {
        $contato = $this->contatoExistente();

        $this->deleteJson("/api/contacts/{$contato->id()}")->assertNoContent();

        $this->assertSoftDeleted('contacts', ['id' => $contato->id()]);
    }

    public function test_retorna_404_ao_excluir_contato_inexistente(): void
    {
        $this->deleteJson('/api/contacts/999')->assertNotFound();
    }

    public function test_normaliza_o_telefone_ao_salvar(): void
    {
        $this->postJson('/api/contacts', [
            'name' => 'Felipe Ortiz',
            'email' => 'felipe@empresa.com.br',
            'phone' => '(16) 99999-9999',
        ])->assertCreated()->assertJsonPath('data.phone', '16999999999');

        $this->assertDatabaseHas('contacts', ['phone' => '16999999999']);
    }
}