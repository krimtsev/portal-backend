<?php

use App\Models\Partner\Partner;
use App\Models\Partner\PartnerNotificationChannel;
use App\Models\Partner\PartnerReportSetting;
use Illuminate\Support\Facades\DB;

/**
 * php artisan tinker database/patches/partner_report_settings.php
 */
try {
    DB::transaction(function () {
        $partnerIds = Partner::withTrashed()->pluck('id');

        foreach ($partnerIds as $partnerId) {
            PartnerReportSetting::firstOrCreate(
                ['partner_id' => $partnerId],
                [
                    'lost_clients_days'     => 0,
                    'returned_clients_days' => 0,
                    'new_clients_days'      => 0,
                ]
            );

            PartnerNotificationChannel::firstOrCreate(
                ['partner_id' => $partnerId],
                [
                    'send_telegram'    => false,
                    'telegram_chat_id' => null,
                    'check_payment'    => false,
                    'payment_date'     => null,
                ]
            );
        }

        DB::table('partners')
            ->join('_partners', function ($join) {
                $join->on('partners.yclients_id', '=', DB::raw('_partners.yclients_id COLLATE utf8mb4_unicode_ci'));
            })
            ->select([
                'partners.id as new_partner_id',
                '_partners.tg_active',
                '_partners.tg_chat_id',
                '_partners.tg_pay_end',
                '_partners.lost_client_days',
                '_partners.repeat_client_days',
                '_partners.new_client_days',
            ])
            ->orderBy('partners.id')
            ->chunk(200, function ($records) {
                foreach ($records as $oldData) {
                    $hasPaymentDate = !empty($oldData->tg_pay_end);

                    PartnerNotificationChannel::where('partner_id', $oldData->new_partner_id)
                        ->update([
                            'send_telegram'    => (bool) $oldData->tg_active,
                            'telegram_chat_id' => $oldData->tg_chat_id ?: null,
                            'check_payment'    => $hasPaymentDate,
                            'payment_date'     => $hasPaymentDate ? $oldData->tg_pay_end : null,
                        ]);

                    PartnerReportSetting::where('partner_id', $oldData->new_partner_id)
                        ->update([
                            'lost_clients_days'     => (int) ($oldData->lost_client_days ?? 0),
                            'returned_clients_days' => (int) ($oldData->repeat_client_days ?? 0),
                            'new_clients_days'      => (int) ($oldData->new_client_days ?? 0),
                        ]);
                }
            });
    });

    echo 'Патч успешно выполнен!';
} catch (Throwable $e) {
    echo 'Ошибка: ' . $e->getMessage() . ' (Строка: ' . $e->getLine() . ')';
}
