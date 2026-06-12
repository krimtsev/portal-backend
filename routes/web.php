<?php

use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsFilters;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsResponse;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\Resources\Staff\DTO\StaffResponse;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsFilters;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsClient;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return 'Hello World'; });

Route::prefix('debug')->group(function () {
    Route::get('/timezone', function () {
        return response()->json([
            'config' => [
                'config_app_timezone' => config('app.timezone'),
            ],
            'php_and_carbon' => [
                'carbon_now'           => Carbon::now()->toIso8601String(),
                'php_date_now'         => date('Y-m-d H:i:s P'),
                'php_default_timezone' => date_default_timezone_get(),
            ],
        ]);
    });
});

Route::prefix('test')
    ->group(function () {
        Route::get('/staff', function () {
            $yclients = new YclientsApi(new YclientsClient());

            $raw = $yclients->staff()->getStaff(41120);
            $items = $raw['data'] ?? [];

            $upsertData = [];

            foreach ($items as $item) {
                $dto = StaffResponse::from($item);

                $upsertData[] = [
                    'company_id'     => $dto->company_id,
                    'staff_id'       => $dto->id,
                    'name'           => $dto->name,
                    'firstname'      => $dto->employee?->firstname,
                    'surname'        => $dto->employee?->surname,
                    'specialization' => $dto->specialization,
                    'fired'          => $dto->fired,
                    'dismissal_date' => $dto->dismissal_date,
                    'rating'         => $dto->rating,
                ];
            }

            return response()->json($upsertData);
        });

        Route::get('/company-stats', function () {
            $yclients = new YclientsApi(new YclientsClient());

            $raw = $yclients->analytics()->getCompanyStats(
                41120,
                new CompanyStatsFilters(
                    date_from: '2026-06-10',
                    date_to: '2026-06-10',
                )
            );

            $items = $raw['data'] ?? [];

            $dto = CompanyStatsResponse::from($items);

            $upsertData = [
                'income_total'     => $dto->income_total_stats->current_sum,
                'income_goods'     => $dto->income_goods_stats->current_sum,
                'income_services'  => $dto->income_services_stats->current_sum,
                'fullness_percent' => $dto->fullness_stats->current_percent,
                'record_completed' => $dto->record_stats->current_completed_count,
                'record_pending'   => $dto->record_stats->current_pending_count,
                'record_canceled'  => $dto->record_stats->current_canceled_count,
                'record_total'     => $dto->record_stats->current_total_count,
                'client_new'       => $dto->client_stats->new_count,
                'client_return'    => $dto->client_stats->return_count,
                'client_active'    => $dto->client_stats->active_count,
                'client_lost'      => $dto->client_stats->lost_count,
                'client_total'     => $dto->client_stats->total_count,
            ];

            return response()->json($upsertData);
        });

        Route::get('/comments', function () {
            $yclients = new YclientsApi(new YclientsClient());

            $raw = $yclients->comments()->getComments(
                41120,
                new CommentsFilters(
                    start_date: '2026-06-10',
                    end_date: '2026-06-10',
                )
            );

            $items = $raw['data'] ?? [];

            $upsertData = [];

            foreach ($items as $item) {
                $dto = CommentsResponse::from($item);

                $upsertData[] = [
                    // 'company_id' => $this->companyId,
                    'comment_id' => $dto->id,
                    'salon_id'   => $dto->salon_id,
                    'staff_id'   => $dto->master_id,
                    'type'       => $dto->type,
                    'rating'     => $dto->rating,
                    'date'       => $dto->date,
                ];
            }

            return response()->json($upsertData);
        });

        Route::get('/transactions', function () {
            $yclients = new YclientsApi(new YclientsClient());

            $raw = $yclients->transactions()->getTransactions(
                41120,
                new TransactionsFilters(
                    start_date: '2026-06-10',
                    end_date: '2026-06-10',
                )
            );

            $items = $raw['data'] ?? [];

            $upsertData = [];

            foreach ($items as $item) {
                $dto = TransactionsResponse::from($item);

                $upsertData[] = [
                    'transaction_id' => $dto->id,
                    'staff_id'       => $dto->master?->id,
                    'record_id'      => $dto->record_id,
                    'visit_id'       => $dto->visit_id,
                    'document_id'    => $dto->document_id,
                    'amount'         => $dto->amount,
                    'sold_item_type' => $dto->sold_item_type,
                    'expense_id'     => $dto->expense?->id,
                    'expense_title'  => $dto->expense?->title,
                    'expense_type'   => $dto->expense?->type,
                    'date'           => $dto->date,
                ];
            }

            return response()->json($upsertData);
        });

        Route::get('/records', function () {
            $yclients = new YclientsApi(new YclientsClient());

            $raw = $yclients->records()->getRecords(
                41120,
                new RecordsFilters(
                    start_date: '2026-05-01',
                    end_date: '2026-05-30',
                )
            );

            $items = $raw['data'] ?? [];

            $records = [];

            $SERVICES_COST = [

                // Услуги в комплексе основная сеть 44011

                5855572 => 2100, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК
                5855566 => 600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК
                13458944 => 3500, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT
                13458949 => 4100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT + ВОСК
                5855583 => 2100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК
                5855560 => 1500, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN

                7043251 => 1500, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN // ТОП-БАРБЕР
                7043266 => 2100, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК // ТОП-БАРБЕР
                7043330 => 2100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК // ТОП-БАРБЕР
                13458978 => 4100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT + ВОСК // ТОП-БАРБЕР
                13458971 => 3500, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT // ТОП-БАРБЕР
                7043262 => 600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК // ТОП-БАРБЕР

                8337664 => 1500, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN // БРЕНД-БАРБЕР
                8337665 => 2100, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК // БРЕНД-БАРБЕР
                8337669 => 2100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК // БРЕНД-БАРБЕР
                13458996 => 4100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT + ВОСК // БРЕНД-БАРБЕР
                13458994 => 3500, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT // БРЕНД-БАРБЕР
                8337666 => 600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК // БРЕНД-БАРБЕР

                14514601 => 1500, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN // БРЕНД-БАРБЕР+
                14514599 => 2100, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК // БРЕНД-БАРБЕР+
                14514600 => 2100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК // БРЕНД-БАРБЕР+
                15320508 => 4100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT + ВОСК // БРЕНД-БАРБЕР+
                15320509 => 3500, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT // БРЕНД-БАРБЕР+
                14514595 => 600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК // БРЕНД-БАРБЕР+

                12320307 => 3500, // МУЖСКАЯ СТРИЖКА + DEPOT // ЭКСПЕРТ
                12320304 => 4100, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + DEPOT + ВОСК // ЭКСПЕРТ
                12320306 => 600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК // ЭКСПЕРТ

                // Услуги в комплексе сеть регионы 772294

                11414147 => 1200, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN
                11414152 => 400, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК
                11414148 => 1600, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК
                11414151 => 1600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК

                11414167 => 1200, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN // ТОП-БАРБЕР
                11414172 => 400, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК // ТОП-БАРБЕР
                11414169 => 1600, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК // ТОП-БАРБЕР
                11414170 => 1600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК // ТОП-БАРБЕР

                11414156 => 1200, // МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN // БРЕНД-БАРБЕР
                11414160 => 400, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + ВОСК // БРЕНД-БАРБЕР
                11414157 => 1600, // МУЖСКАЯ СТРИЖКА + BLACK MASK/VOLCANO/ACUMEN + ВОСК // БРЕНД-БАРБЕР
                11414158 => 1600, // МУЖСКАЯ СТРИЖКА + МОДЕЛИРОВАНИЕ БОРОДЫ + BLACK MASK/VOLCANO/ACUMEN + ВОСК // БРЕНД-БАРБЕР

            ];

            $SERVICES_COST_IDS = array_keys($SERVICES_COST);

            foreach (array_chunk($items, 100000) as $chunk) {
                foreach ($chunk as $item) {
                    $dto = RecordsResponse::from($item);

                    if (!in_array($dto->id, ["1691701188", "1737557769"])) {
                        continue;
                    }

                    $records[$dto->id] = [
                        'record_id' => $dto->id,
                        'staff_id' => $dto->staff_id,
                        'visit_id' => $dto->visit_id,
                        'client_id' => $dto->client?->id,
                        'client_name' => $dto->client?->name,
                        'client_phone' => $dto->client?->phone,
                        'client_success_visits' => $dto->client?->success_visits_count ?? 0,
                        'client_fail_visits' => $dto->client?->fail_visits_count ?? 0,
                        'datetime' => $dto->datetime,

                        'total_cost'        => array_sum(array_map(fn($s) => $s->cost, $dto->services)),
                        'total_manual_cost' => array_sum(array_map(fn($s) => $s->manual_cost, $dto->services)),
                    ];

                    foreach ($dto->services as $serviceDto) {
                        if (!$serviceDto->id) {
                            continue;
                        }

                        $records[$dto->id]["services"][$serviceDto->id] = [
                            'record_id' => $dto->id,
                            'service_id' => $serviceDto->id,
                            'title' => $serviceDto->title,
                            'cost' => $serviceDto->cost,
                            'manual_cost' => $serviceDto->manual_cost,
                            'discount' => $serviceDto->discount,
                            'amount' => $serviceDto->amount,
                        ];
                    }
                }
            }

            return response()->json([
                'records' => $records,
            ]);
        });
    });
