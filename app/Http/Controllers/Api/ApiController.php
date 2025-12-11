<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Respuesta exitosa genérica
     */
    protected function success($data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'version' => '1.0',
            'timestamp' => now()->toISOString(),
        ], $code);
    }

    /**
     * Respuesta de error genérica
     */
    protected function error(string $message = 'Error en la operación', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'version' => '1.0',
            'timestamp' => now()->toISOString(),
        ], $code);
    }

    /**
     * Respuesta de no autorizado
     */
    protected function unauthorized(string $message = 'No autorizado'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Respuesta de no encontrado
     */
    protected function notFound(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Respuesta de validación fallida
     */
    protected function validationError($errors): JsonResponse
    {
        return $this->error('Datos inválidos', 422, $errors);
    }
}