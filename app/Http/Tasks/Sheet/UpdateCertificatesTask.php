<?php

namespace App\Http\Tasks\Sheet;

use App\Http\Services\Google\GoogleSheetService;
use App\Models\Certificate\Certificate;
use Illuminate\Support\Str;

class UpdateCertificatesTask
{
    /**
     * Обновить базу сертификатов
     * @return void
     */
    public function update(): void
    {
        $rows = $this->getRows();
        $duplicates = $this->duplicateId($rows);

        $rows = collect($rows)
            ->reject(fn($row) => in_array($row['identifier'], $duplicates))
            ->values()
            ->all();

        collect($rows)->chunk(250)->each(function ($chunk) {
            Certificate::upsert(
                $chunk->toArray(),
                ['identifier'],
                ['price', 'partner', 'line']
            );
        });

        $identifiers = collect($rows)->pluck('identifier')->all();
        Certificate::whereNotIn('identifier', $identifiers)->delete();
    }

    /**
     * Получить список строк из Google таблицы
     * @return array
     */
    public function getRows(): array
    {
        $path = config('services.google.certificate');
        $sheet = json_decode(file_get_contents($path));

        try {
            $table = (new GoogleSheetService)->readSheet($sheet);
        } catch (\Throwable $e) {
            logger()->error('Certificates get rows failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        $rows = [];

        foreach ($table as $line => $value) {
            if(count($value) < 3) continue;

            $rows[] = [
                'price'      => Str::lower(Str::trim($value[0])),
                'identifier' => Str::lower(Str::trim($value[1])),
                'partner'    => Str::lower(Str::trim($value[2])),
                'line'       => $line + 1,
            ];
        }

        return $rows;
    }

    /**
     * Получить список значений дукликатов identifier
     * @param $rows
     * @return array
     */
    public function duplicateRows($rows): array
    {
        $rows = $rows ?: $this->getRows();

        $collection = collect($rows);
        $grouped = $collection->groupBy('identifier');
        $duplicates = $grouped->filter(fn($items) => $items->count() > 1);

        return $duplicates->flatten(1)->all();
    }

    /**
     * Получить список Id дубликатов identifier
     * @param $rows
     * @return array
     */
    public function duplicateId($rows): array
    {
        $rows = $rows ?: $this->getRows();

        $collection = collect($rows);
        $grouped = $collection->groupBy('identifier');
        $duplicates = $grouped->filter(fn($items) => $items->count() > 1);

        return $duplicates->keys()->all();
    }
}


