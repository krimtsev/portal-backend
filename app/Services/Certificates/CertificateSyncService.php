<?php

namespace App\Services\Certificates;

use App\Integrations\Google\GoogleSheet;
use App\Models\Certificate\Certificate;
use Illuminate\Support\Str;
use Throwable;

final class CertificateSyncService
{
    /**
     * Обновить базу сертификатов
     */
    public function update(): void
    {
        $rows = $this->getRows();
        $duplicates = $this->duplicateId($rows);

        $rows = collect($rows)
            ->reject(fn ($row) => in_array($row['identifier'], $duplicates))
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
     */
    public function getRows(): array
    {
        $path = config('partner.secrets.google.certificate');
        $sheet = json_decode(file_get_contents($path));

        try {
            $table = (new GoogleSheet())->readSheet($sheet);
        } catch (Throwable $e) {
            logger()->error('Certificates get rows failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        $rows = [];

        foreach ($table as $line => $value) {
            if (count($value) < 3) {
                continue;
            }

            [$priceRaw, $idRaw, $partnerRaw] = array_map('trim', array_slice($value, 0, 3));

            $price = preg_replace('/[.,]/', '', $priceRaw);

            $rows[] = [
                'price'      => Str::upper($price),
                'identifier' => Str::upper($idRaw),
                'partner'    => Str::upper($partnerRaw),
                'line'       => $line + 1,
            ];
        }

        return $rows;
    }

    /**
     * Получить список значений дубликатов identifier
     */
    public function duplicateRows($rows): array
    {
        $rows = $rows ?: $this->getRows();

        $collection = collect($rows);
        $grouped = $collection->groupBy('identifier');
        $duplicates = $grouped->filter(fn ($items) => $items->count() > 1);

        return $duplicates->flatten(1)->all();
    }

    /**
     * Получить список Id дубликатов identifier
     */
    public function duplicateId($rows): array
    {
        $rows = $rows ?: $this->getRows();

        $collection = collect($rows);
        $grouped = $collection->groupBy('identifier');
        $duplicates = $grouped->filter(fn ($items) => $items->count() > 1);

        return $duplicates->keys()->all();
    }
}
