<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    #[OA\Get(
        path: '/clients',
        tags: ['Clients'],
        summary: 'Listar clientes',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista paginada de clientes')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return ClientResource::collection(Client::paginate(10));
    }

    #[OA\Post(
        path: '/clients',
        tags: ['Clients'],
        summary: 'Crear cliente',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'lastname', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 45),
                    new OA\Property(property: 'lastname', type: 'string', maxLength: 45),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cliente creado'),
            new OA\Response(response: 422, description: 'Errores de validación'),
        ]
    )]
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return response()->json([
            'message' => 'Client created successfully',
            'data' => new ClientResource($client),
        ], 201);
    }

    #[OA\Get(
        path: '/clients/{id}',
        tags: ['Clients'],
        summary: 'Mostrar cliente',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Cliente encontrado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(Client $client): ClientResource
    {
        return new ClientResource($client);
    }

    #[OA\Put(
        path: '/clients/{id}',
        tags: ['Clients'],
        summary: 'Actualizar cliente',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'lastname', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
            ])
        ),
        responses: [new OA\Response(response: 200, description: 'Cliente actualizado')]
    )]
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return response()->json([
            'message' => 'Client updated successfully',
            'data' => new ClientResource($client->fresh()),
        ]);
    }

    #[OA\Delete(
        path: '/clients/{id}',
        tags: ['Clients'],
        summary: 'Eliminar cliente',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cliente eliminado')]
    )]
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
