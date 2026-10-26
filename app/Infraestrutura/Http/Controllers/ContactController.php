<?php

namespace App\Infraestrutura\Http\Controllers;

use App\Aplicacao\Contato\CreateContactUseCase;
use App\Aplicacao\Contato\DeleteContactUseCase;
use App\Aplicacao\Contato\ListContactsUseCase;
use App\Aplicacao\Contato\ShowContactUseCase;
use App\Aplicacao\Contato\UpdateContactUseCase;
use App\Infraestrutura\Http\Requests\StoreContactRequest;
use App\Infraestrutura\Http\Requests\UpdateContactRequest;
use App\Infraestrutura\Http\Resources\ContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Infraestrutura\Filas\ProcessContactScoreJob;

class ContactController
{
    private const PER_PAGE = 15;

    public function index(Request $request, ListContactsUseCase $useCase): AnonymousResourceCollection
    {
        $page = max(1, (int) $request->query('page', 1));

        $result = $useCase->execute($page, self::PER_PAGE);

        $paginator = new LengthAwarePaginator(
            $result['data'],
            $result['total'],
            self::PER_PAGE,
            $page,
            ['path' => $request->url()],
        );

        return ContactResource::collection($paginator);
    }

    public function store(StoreContactRequest $request, CreateContactUseCase $useCase): JsonResponse
    {
        $contact = $useCase->execute(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('phone'),
        );

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function show(int $id, ShowContactUseCase $useCase): ContactResource
    {
        return new ContactResource($useCase->execute($id));
    }

    public function update(UpdateContactRequest $request, int $id, UpdateContactUseCase $useCase): ContactResource
    {
        $contact = $useCase->execute(
            $id,
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('phone'),
        );

        return new ContactResource($contact);
    }

    public function destroy(int $id, DeleteContactUseCase $useCase): Response
    {
        $useCase->execute($id);

        return response()->noContent();
    }

    public function processScore(int $id, ShowContactUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        ProcessContactScoreJob::dispatch($id);

        return response()->json(['message' => 'Processamento do score enfileirado.'], 202);
    }
}