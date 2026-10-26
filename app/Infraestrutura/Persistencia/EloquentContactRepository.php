<?php

namespace App\Infraestrutura\Persistencia;

use App\Dominio\Contato\Entidades\Contact;
use App\Dominio\Contato\ObjetosDeValor\Email;
use App\Dominio\Contato\ObjetosDeValor\Phone;
use App\Dominio\Contato\Repositorios\ContactRepository;

final class EloquentContactRepository implements ContactRepository
{
    public function save(Contact $contact): Contact
    {
        $model = $contact->id() !== null
            ? ContactModel::findOrFail($contact->id())
            : new ContactModel();

        $model->fill([
            'name' => $contact->name(),
            'email' => $contact->email()->value(),
            'phone' => $contact->phone()->value(),
            'score' => $contact->score(),
            'status' => $contact->status(),
            'processed_at' => $contact->processedAt(),
        ]);

        $model->save();

        return $this->toEntity($model->refresh());
    }

    public function findById(int $id): ?Contact
    {
        $model = ContactModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function paginate(int $page, int $perPage): array
    {
        $paginator = ContactModel::query()
            ->orderBy('id')
            ->paginate(perPage: $perPage, page: $page);

            return [
                'data' => array_map(fn(ContactModel $model) => $this->toEntity($model), $paginator->items()),
                'total' => $paginator->total(),
            ];
    }

    public function delete(int $id): void
    {
        ContactModel::find($id)?->delete();
    }

    private function toEntity(ContactModel $model): Contact
    {
        return Contact::restore(
            $model->id,
            $model->name,
            new Email($model->email),
            new Phone($model->phone),
            $model->score,
            $model->status,
            $model->processed_at,
        );
    }
}