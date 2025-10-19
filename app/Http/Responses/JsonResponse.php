<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Response;

class JsonResponse
{
    /**
     * Метод для отправки данных
     */
    static function Send(?array $data, string $message = 'Ok', int $code = Response::HTTP_OK): \Illuminate\Http\JsonResponse
    {
        $content = [
            'message' => $message,
        ];

        if ($data) {
            $content = array_merge($content, $data);
        }

        return response()->json($content, $code);
    }

    /**
     * Методы для отправки ошибок
     */
    static function Forbidden(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Forbidden'
        ], Response::HTTP_FORBIDDEN);
    }

    static function BadRequest(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Bad Request'
        ], Response::HTTP_BAD_REQUEST);
    }

    static function FileNotFound(string $message = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message ?? 'File not found'
        ], Response::HTTP_NOT_FOUND);
    }

    static function UserNotFound(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'User Not Found'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
