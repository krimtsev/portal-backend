<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/dashboard')
    ->middleware([
        'auth:sanctum',
        'dashboard.context',
        'maintenance',
    ])
    ->group(function () {
        Route::prefix('panel')
            ->middleware('dashboard.section:panel')
            ->group(function () {
                Route::get('analytics', [Controllers\Panel\PanelController::class, 'analytics']);
            });

        Route::prefix('partners')->group(function () {
            Route::middleware('dashboard.section:partners')
                ->group(function () {
                    Route::post('list', [Controllers\Partners\PartnerController::class, 'list']);
                    Route::get('partner/{partner}', [Controllers\Partners\PartnerController::class, 'get']);
                    Route::post('partner/{partner}', [Controllers\Partners\PartnerController::class, 'create']);
                    Route::put('partner/{partner}', [Controllers\Partners\PartnerController::class, 'update']);
                    Route::get('export', [Controllers\Partners\PartnerController::class, 'export']);
                });

            Route::middleware('dashboard.access')
                ->group(function () {
                    Route::get('options', [Controllers\Partners\PartnerController::class, 'options']);
                });
        });

        Route::prefix('partner-groups')->group(function () {
            Route::middleware('dashboard.section:partner_groups')
                ->group(function () {
                    Route::post('list', [Controllers\Partners\PartnerGroupController::class, 'list']);
                    Route::get('group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'get']);
                    Route::post('group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'create']);
                    Route::put('group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'update']);
                    Route::delete('group/{partnerGroup}', [Controllers\Partners\PartnerGroupController::class, 'remove']);
                });

            Route::middleware('dashboard.access')
                ->group(function () {
                    Route::get('options', [Controllers\Partners\PartnerGroupController::class, 'options']);
                });
        });

        Route::prefix('partner-notifications')
            ->middleware('dashboard.section:partner_notifications')
            ->group(function () {
                Route::post('list', [Controllers\Partners\PartnerNotificationsController::class, 'list']);
                Route::get('partner/{partner}', [Controllers\Partners\PartnerNotificationsController::class, 'get']);
                Route::put('partner/{partner}', [Controllers\Partners\PartnerNotificationsController::class, 'update']);
            });

        Route::prefix('partner-messages')
            ->middleware('dashboard.section:partner_messages')
            ->group(function () {
                Route::get('options', [Controllers\Partners\PartnerMessagesController::class, 'options']);
                Route::post('send', [Controllers\Partners\PartnerMessagesController::class, 'send']);
            });

        Route::prefix('tickets')
            ->middleware('dashboard.section:tickets')
            ->group(function () {
                Route::post('list', [Controllers\Tickets\TicketsController::class, 'list']);
                Route::get('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'get']);
                Route::post('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'update']);
                Route::get('ticket/{ticket}/download/{fileName}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
                Route::get('export', [Controllers\Tickets\TicketsController::class, 'export']);
            });

        Route::prefix('users')->group(function () {
            Route::middleware('dashboard.section:users')
                ->group(function () {
                    Route::post('list', [Controllers\Users\UserController::class, 'list']);
                    Route::get('user/{user}', [Controllers\Users\UserController::class, 'get']);
                    Route::post('user/{user}', [Controllers\Users\UserController::class, 'create']);
                    Route::put('user/{user}', [Controllers\Users\UserController::class, 'update']);
                    Route::get('export', [Controllers\Users\UserController::class, 'export']);
                });

            Route::middleware('dashboard.access')
                ->group(function () {
                    Route::get('options', [Controllers\Users\UserController::class, 'options']);
                });
        });

        Route::prefix('cloud')
            ->middleware('dashboard.section:cloud')
            ->group(function () {
                Route::post('tree', [Controllers\Cloud\CloudController::class, 'tree']);
                Route::get('options', [Controllers\Cloud\CloudController::class, 'options']);
                Route::get('options-tree', [Controllers\Cloud\CloudController::class, 'optionsTree']);
                Route::get('folder/{folder}', [Controllers\Cloud\CloudController::class, 'get']);
                Route::post('folder/{folder}', [Controllers\Cloud\CloudController::class, 'create']);
                Route::put('folder/{folder}', [Controllers\Cloud\CloudController::class, 'update']);
                Route::delete('folder/{folder}', [Controllers\Cloud\CloudController::class, 'remove']);

                Route::get('folder/{folder}/files', [Controllers\Cloud\CloudFilesController::class, 'list']);
                Route::post('folder/{folder}/files', [Controllers\Cloud\CloudFilesController::class, 'upload']);
                Route::put('folder/{folder}/file/{file}', [Controllers\Cloud\CloudFilesController::class, 'update']);
                Route::delete('folder/{folder}/file/{file}', [Controllers\Cloud\CloudFilesController::class, 'remove']);
                Route::get('folder/{folder}/download/{fileName}', [Controllers\Cloud\CloudFilesController::class, 'download']);
            });

        Route::prefix('royalty')
            ->middleware('dashboard.section:royalty')
            ->group(function () {
                Route::post('percent/list', [Controllers\Royalty\RoyaltyController::class, 'list']);
                Route::post('records/list', [Controllers\Royalty\RoyaltyRecordsController::class, 'list']);
            });

        Route::prefix('statistics')
            ->middleware('dashboard.section:statistics')
            ->group(function () {
                Route::get('staff/list', [Controllers\Statistics\StatisticsStaffController::class, 'list']);
                Route::get('partner/income', [Controllers\Statistics\StatisticsPartnerController::class, 'income']);
            });

        Route::prefix('event-calendar')
            ->middleware('dashboard.section:event_calendar')
            ->group(function () {
                Route::post('list', [Controllers\EventCalendar\EventCalendarController::class, 'list']);
                Route::post('batch', [Controllers\EventCalendar\EventCalendarController::class, 'create']);
                Route::get('batch/{batch}', [Controllers\EventCalendar\EventCalendarController::class, 'get']);
                Route::put('batch/{batch}', [Controllers\EventCalendar\EventCalendarController::class, 'update']);
                Route::delete('batch/{batch}', [Controllers\EventCalendar\EventCalendarController::class, 'remove']);
            });

        Route::prefix('telnums')
            ->middleware('dashboard.section:telnums')
            ->group(function () {
                Route::post('incoming/list', [Controllers\Telnums\TelnumsIncomingController::class, 'list']);
                Route::get('incoming/{partnerId}/hourly', [Controllers\Telnums\TelnumsIncomingController::class, 'partnerDay']);
            });

        Route::prefix('settings')
            ->middleware('dashboard.section:settings')
            ->group(function () {
                Route::get('maintenance', [Controllers\Maintenance\MaintenanceController::class, 'get']);
                Route::put('maintenance', [Controllers\Maintenance\MaintenanceController::class, 'update']);
            });

        Route::prefix('promo-dialog')
            ->middleware('dashboard.section:promo_dialogs')
            ->group(function () {
                Route::post('list', [Controllers\PromoDialog\PromoDialogController::class, 'list']);
                Route::get('dialog/{promoDialog}', [Controllers\PromoDialog\PromoDialogController::class, 'get']);
                Route::post('dialog', [Controllers\PromoDialog\PromoDialogController::class, 'create']);
                Route::post('dialog/{promoDialog}', [Controllers\PromoDialog\PromoDialogController::class, 'update']);
                Route::delete('dialog/{promoDialog}', [Controllers\PromoDialog\PromoDialogController::class, 'remove']);
                Route::get('dialog/{promoDialog}/export', [Controllers\PromoDialog\PromoDialogController::class, 'export']);
                Route::get('dialog/{promoDialog}/image', [Controllers\PromoDialog\PromoDialogController::class, 'image']);
            });

        Route::prefix('promotions')
            ->middleware('dashboard.section:promotions')
            ->group(function () {
                Route::post('list', [Controllers\Promotion\PromotionController::class, 'list']);
                Route::get('event/{promotion}', [Controllers\Promotion\PromotionController::class, 'get']);
                Route::post('event', [Controllers\Promotion\PromotionController::class, 'create']);
                Route::post('event/{promotion}', [Controllers\Promotion\PromotionController::class, 'update']);
                Route::delete('event/{promotion}', [Controllers\Promotion\PromotionController::class, 'remove']);
                Route::get('export', [Controllers\Promotion\PromotionController::class, 'export']);
                Route::get('event/{promotion}/image', [Controllers\Promotion\PromotionController::class, 'image']);
            });

        Route::prefix('bank-detail')
            ->middleware('dashboard.section:bank_details')
            ->group(function () {
                Route::post('list', [Controllers\BankDetails\BankDetailsController::class, 'list']);
                Route::get('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'get']);
                Route::post('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'create']);
                Route::put('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'update']);
                Route::delete('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'remove']);
                Route::get('export', [Controllers\BankDetails\BankDetailsController::class, 'export']);
            });
    });
