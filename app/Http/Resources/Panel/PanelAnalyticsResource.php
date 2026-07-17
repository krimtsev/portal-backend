<?php

declare(strict_types=1);

namespace App\Http\Resources\Panel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array $resource
 */
final class PanelAnalyticsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tickets' => [
                'new_count'     => $this->resource['new_count'],
                'pending_count' => $this->resource['pending_count'],
                'total_count'   => $this->resource['total_count'],
            ],

            'periods' => [
                '1day' => [
                    'current'  => $this->resource['periods']['1_day']['current'],
                    'previous' => $this->resource['periods']['1_day']['previous'],
                ],
                '7days' => [
                    'current'  => $this->resource['periods']['7_days']['current'],
                    'previous' => $this->resource['periods']['7_days']['previous'],
                ],
                '30days' => [
                    'current'  => $this->resource['periods']['30_days']['current'],
                    'previous' => $this->resource['periods']['30_days']['previous'],
                ],
            ],

            'efficiency' => [
                'success_rate_percentage' => (int) round($this->resource['efficiency']['success_rate_percentage']),
                'average_time'            => (int) round($this->resource['efficiency']['average_closure_time_minutes']),
            ],

            'partners' => [
                'total_count'    => $this->resource['partners']['total_count'],
                'active_count'   => $this->resource['partners']['active_count'],
                'inactive_count' => $this->resource['partners']['disabled_count'],
            ],

            'royalty_stats' => $this->resource['royalty'] ?? [],

            'jobs' => $this->when(isset($this->resource['jobs']), function () {
                return [
                    'total_count'    => $this->resource['jobs']['total_count'] ?? 0,
                    'default_count'  => $this->resource['jobs']['default_count'] ?? 0,
                    'yclients_count' => $this->resource['jobs']['yclients_count'] ?? 0,
                    'failed_count'   => $this->resource['jobs']['failed_count'] ?? 0,
                ];
            }),
        ];
    }
}
