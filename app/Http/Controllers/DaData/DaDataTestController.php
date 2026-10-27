<?php

namespace App\Http\Controllers\DaData;

use App\Http\Controllers\Controller;
use App\Integrations\DaData\DaDataApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DaDataTestController extends Controller
{
    public function __construct(
        private readonly DaDataApi $dadata,
    ) {}

    /**
     * GET /test/dadata/bank/suggest?query=сбербанк&count=5
     */
    public function bankSuggest(Request $request): JsonResponse
    {
        $query = (string) $request->input('query', 'сбербанк');
        $count = (int) $request->input('count', 5);

        return $this->execute(fn () => $this->dadata->bank()->suggest($query, $count));
    }

    /**
     * GET /test/dadata/bank/find-by-bic?bic=044525225
     */
    public function bankFindById(Request $request): JsonResponse
    {
        $bic = (string) $request->input('bic', '044525225');

        return $this->execute(fn () => $this->dadata->bank()->findById($bic));
    }

    /**
     * GET /test/dadata/clean/address?value=мск сухонская 11/-89
     */
    public function cleanAddress(Request $request): JsonResponse
    {
        $value = (string) $request->input('value', 'мск сухонская 11/-89');

        return $this->execute(function () use ($value) {
            // Cleaner API ожидает массив строк
            return $this->dadata->client()->clean->post('/address', [$value]);
        });
    }

    /**
     * Универсальный обработчик для ответа
     */
    private function execute(callable $callback): JsonResponse
    {
        $startedAt = microtime(true);

        try {
            $data = $callback();

            return response()->json([
                'success'    => true,
                'duration_ms'=> round((microtime(true) - $startedAt) * 1000, 2),
                'data'       => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'class'   => $e::class,
            ], 500);
        }
    }
}
