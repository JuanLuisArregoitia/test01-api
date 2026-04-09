<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[OA\Get(
        path: '/products',
        tags: ['Products'],
        summary: 'Listar productos',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista paginada de productos')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(Product::paginate(10));
    }

    #[OA\Post(
        path: '/products',
        tags: ['Products'],
        summary: 'Crear producto',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'quantity'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 45),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 9.99),
                    new OA\Property(property: 'quantity', type: 'integer', example: 100),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado'),
            new OA\Response(response: 422, description: 'Errores de validación'),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], 201);
    }

    #[OA\Get(
        path: '/products/{id}',
        tags: ['Products'],
        summary: 'Mostrar producto',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Producto encontrado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    #[OA\Put(
        path: '/products/{id}',
        tags: ['Products'],
        summary: 'Actualizar producto',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'price', type: 'number'),
                new OA\Property(property: 'quantity', type: 'integer'),
            ])
        ),
        responses: [new OA\Response(response: 200, description: 'Producto actualizado')]
    )]
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product->fresh()),
        ]);
    }

    #[OA\Delete(
        path: '/products/{id}',
        tags: ['Products'],
        summary: 'Eliminar producto',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Producto eliminado')]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
