<?php

namespace App\Services\Mango;

use App\Constants\Cache\MangoCache;
use App\Helpers\Cache;
use App\Integrations\Mango\MangoApi;
use App\Models\Mango\MangoBlacklist;
use Illuminate\Support\Facades\DB;

final readonly class MangoBlacklistService
{
    public function __construct(
        private MangoApi $api,
    ) {}

    public function sync(): void
    {
        $response = $this->api->bwlists()->getBwlists();

        $numbers = $response['data']['black']['numbers'] ?? [];
        $incomingIds = array_column($numbers, 'number_id');

        DB::transaction(function () use ($numbers, $incomingIds) {
            MangoBlacklist::query()
                ->whereNotIn('number_id', $incomingIds)
                ->delete();

            if (empty($numbers)) {
                return;
            }

            $records = array_map(static fn (array $item) => [
                'number_id'   => $item['number_id'],
                'number'      => $item['number'],
                'number_type' => $item['number_type'],
                'comment'     => $item['comment'] ?? null,
            ], $numbers);

            MangoBlacklist::upsert(
                $records,
                ['number_id'],
                ['number', 'number_type', 'comment']
            );
        });

        Cache::flush(MangoCache::MANGO_BLACKLIST_PATTERNS);
    }
}
