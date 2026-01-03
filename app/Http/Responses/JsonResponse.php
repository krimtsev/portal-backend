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

    static function Created(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Created'
        ], Response::HTTP_CREATED);
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

    static function BadRequest(?array $data): \Illuminate\Http\JsonResponse
    {
        $content = [
            'message' => 'Bad Request',
            'data' => array_merge([], $data)
        ];

        return response()->json($content, Response::HTTP_BAD_REQUEST);
    }

    static function FileNotFound(string $message = 'File not found'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message
        ], Response::HTTP_NOT_FOUND);
    }

    static function UserNotFound(string $message = 'User Not Found'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    static function InvalidCredentials(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Invalid credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }
}
