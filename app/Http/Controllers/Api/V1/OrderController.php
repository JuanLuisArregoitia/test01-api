<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Get(
        path: '/orders',
        tags: ['Orders'],
        summary: 'Listar órdenes',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista paginada de órdenes')]
    )]
    public function index(): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Order::with(['client', 'details.product'])->paginate(10)
        );
    }

    #[OA\Post(
        path: '/orders',
        tags: ['Orders'],
        summary: 'Crear orden',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['order_number', 'status_id', 'client_id'],
                properties: [
                    new OA\Property(property: 'order_number', type: 'string', maxLength: 45, example: 'ORD-0001'),
                    new OA\Property(property: 'status_id', type: 'integer', minimum: 1, maximum: 3, example: 1),
                    new OA\Property(property: 'client_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Orden creada'),
            new OA\Response(response: 422, description: 'Errores de validación'),
        ]
    )]
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = Order::create($request->validated());

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order->load('client')),
        ], 201);
    }

    #[OA\Get(
        path: '/orders/{id}',
        tags: ['Orders'],
        summary: 'Mostrar orden',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Orden con cliente y detalles'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load(['client', 'details.product']));
    }

    #[OA\Put(
        path: '/orders/{id}',
        tags: ['Orders'],
        summary: 'Actualizar orden',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'status_id', type: 'integer', minimum: 1, maximum: 3),
            ])
        ),
        responses: [new OA\Response(response: 200, description: 'Orden actualizada')]
    )]
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $order->update($request->validated());

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => new OrderResource($order->fresh()->load('client')),
        ]);
    }

    #[OA\Delete(
        path: '/orders/{id}',
        tags: ['Orders'],
        summary: 'Eliminar orden',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Orden eliminada')]
    )]
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}
