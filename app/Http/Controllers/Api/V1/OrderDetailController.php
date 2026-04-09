<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderDetailRequest;
use App\Http\Requests\UpdateOrderDetailRequest;
use App\Http\Resources\OrderDetailResource;
use App\Models\OrderDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class OrderDetailController extends Controller
{
    #[OA\Get(
        path: '/order-details',
        tags: ['Order Details'],
        summary: 'Listar detalles de órdenes',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista paginada de detalles')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return OrderDetailResource::collection(
            OrderDetail::with('product')->paginate(10)
        );
    }

    #[OA\Post(
        path: '/order-details',
        tags: ['Order Details'],
        summary: 'Crear detalle de orden',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['order_id', 'product_id', 'price'],
                properties: [
                    new OA\Property(property: 'order_id', type: 'integer', example: 1),
                    new OA\Property(property: 'product_id', type: 'integer', example: 1),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 49.99),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Detalle creado'),
            new OA\Response(response: 422, description: 'Errores de validación'),
        ]
    )]
    public function store(StoreOrderDetailRequest $request): JsonResponse
    {
        $detail = OrderDetail::create($request->validated());

        return response()->json([
            'message' => 'Order detail created successfully',
            'data' => new OrderDetailResource($detail->load('product')),
        ], 201);
    }

    #[OA\Get(
        path: '/order-details/{id}',
        tags: ['Order Details'],
        summary: 'Mostrar detalle de orden',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle encontrado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(OrderDetail $orderDetail): OrderDetailResource
    {
        return new OrderDetailResource($orderDetail->load('product'));
    }

    #[OA\Put(
        path: '/order-details/{id}',
        tags: ['Order Details'],
        summary: 'Actualizar detalle de orden',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'price', type: 'number', format: 'float'),
            ])
        ),
        responses: [new OA\Response(response: 200, description: 'Detalle actualizado')]
    )]
    public function update(UpdateOrderDetailRequest $request, OrderDetail $orderDetail): JsonResponse
    {
        $orderDetail->update($request->validated());

        return response()->json([
            'message' => 'Order detail updated successfully',
            'data' => new OrderDetailResource($orderDetail->fresh()->load('product')),
        ]);
    }

    #[OA\Delete(
        path: '/order-details/{id}',
        tags: ['Order Details'],
        summary: 'Eliminar detalle de orden',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detalle eliminado')]
    )]
    public function destroy(OrderDetail $orderDetail): JsonResponse
    {
        $orderDetail->delete();

        return response()->json(['message' => 'Order detail deleted successfully']);
    }
}
