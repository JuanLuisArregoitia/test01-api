<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class SupplierController extends Controller
{
    #[OA\Get(
        path: '/suppliers',
        tags: ['Suppliers'],
        summary: 'Listar proveedores',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista paginada de proveedores')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return SupplierResource::collection(Supplier::paginate(10));
    }

    #[OA\Post(
        path: '/suppliers',
        tags: ['Suppliers'],
        summary: 'Crear proveedor',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [new OA\Property(property: 'name', type: 'string', maxLength: 45, example: 'Proveedor S.A.')]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Proveedor creado'),
            new OA\Response(response: 422, description: 'Errores de validación'),
        ]
    )]
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'message' => 'Supplier created successfully',
            'data' => new SupplierResource($supplier),
        ], 201);
    }

    #[OA\Get(
        path: '/suppliers/{id}',
        tags: ['Suppliers'],
        summary: 'Mostrar proveedor',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Proveedor encontrado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(Supplier $supplier): SupplierResource
    {
        return new SupplierResource($supplier);
    }

    #[OA\Put(
        path: '/suppliers/{id}',
        tags: ['Suppliers'],
        summary: 'Actualizar proveedor',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [new OA\Property(property: 'name', type: 'string', maxLength: 45)])
        ),
        responses: [new OA\Response(response: 200, description: 'Proveedor actualizado')]
    )]
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json([
            'message' => 'Supplier updated successfully',
            'data' => new SupplierResource($supplier->fresh()),
        ]);
    }

    #[OA\Delete(
        path: '/suppliers/{id}',
        tags: ['Suppliers'],
        summary: 'Eliminar proveedor',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Proveedor eliminado')]
    )]
    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}
