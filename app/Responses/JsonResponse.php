<?php

namespace App\Responses;

use Symfony\Component\HttpFoundation\Response;

class JsonResponse
{
    /**
     * Метод для отправки данных
     */
    public static function Send(?array $data, string $message = 'Ok', int $code = Response::HTTP_OK): \Illuminate\Http\JsonResponse
    {
        $content = [
            'message' => $message,
        ];

        if ($data) {
            $content = array_merge($content, $data);
        }

        return response()->json($content, $code);
    }

    public static function Created(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Created',
        ], Response::HTTP_CREATED);
    }

    public static function Updated(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Updated',
        ], Response::HTTP_OK);
    }

    public static function Removed(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Removed',
        ], Response::HTTP_OK);
    }

    /**
     * Методы для отправки ошибок
     */
    public static function Forbidden(string $message = 'Forbidden'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }

    public static function BadRequest(?array $data): \Illuminate\Http\JsonResponse
    {
        $content = [
            'message' => 'Bad Request',
            'data'    => array_merge([], $data),
        ];

        return response()->json($content, Response::HTTP_BAD_REQUEST);
    }

    public static function FileNotFound(string $message = 'File not found'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    public static function UserNotFound(string $message = 'User Not Found'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function InvalidCredentials(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => 'Invalid credentials',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
