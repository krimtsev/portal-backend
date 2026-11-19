<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [Controllers\Auth\AuthController::class, 'login']);
    Route::post('logout', [Controllers\Auth\AuthController::class, 'logout']);

    Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {
        Route::get('home', [Controllers\App\AppController::class, 'homeData']);

        Route::get('user-data', [Controllers\Auth\AuthController::class, 'userData']);

        Route::post('certificates', [Controllers\Sheet\CertificateController::class, 'list']);

        Route::prefix('contacts')->group(function () {
            Route::post('franchisee', [Controllers\Contacts\FranchiseeController::class, 'list']);
        });

        Route::prefix('cloud')->group(function () {
            Route::get('list', [Controllers\Cloud\CloudController::class, 'list']);

            Route::get('folder/{folder}/download/{fileName}', [Controllers\Cloud\CloudFilesController::class, 'download']);
        });

        Route::get('user-partners', [Controllers\Partners\PartnerController::class, 'getUserPartners']);

        Route::prefix('files')->group(function () {
            Route::get('download/{category}/{fileName}', [Controllers\Files\FileController::class, 'download']);
            Route::get('render/{category}/{fileName}', [Controllers\Files\FileController::class, 'render']);
        });

        Route::prefix('dadata')->group(function () {
            Route::get('suggest/bank', [Controllers\DaData\DaDataController::class, 'bankSuggest']);
            Route::get('find-by-id/bank', [Controllers\DaData\DaDataController::class, 'bankFindById']);

            Route::get('suggest/address', [Controllers\DaData\DaDataController::class, 'addressSuggest']);

            Route::get('suggest/party', [Controllers\DaData\DaDataController::class, 'partySuggest']);
            Route::get('find-by-id/party', [Controllers\DaData\DaDataController::class, 'partyFindById']);
        });

        Route::prefix('promo-dialog')->group(function () {
            Route::get('dialog/{promoDialog}/image', [Controllers\PromoDialog\PromoDialogController::class, 'image']);
            Route::post('dialog/{id}/read', [Controllers\PromoDialog\PromoDialogController::class, 'read']);
        });

        // PROFILE

        Route::prefix('user-profile')->group(function () {
            Route::get('/', [Controllers\Users\UserProfileController::class, 'show']);
        });

        Route::put('/change-password', [Controllers\Auth\ChangePasswordController::class, 'update']);

        Route::prefix('tickets')->group(function () {
            Route::post('list', [Controllers\Tickets\TicketsController::class, 'list']);
            Route::post('ticket', [Controllers\Tickets\TicketsController::class, 'create']);
            Route::get('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'get']);
            Route::post('ticket/{ticket}/message', [Controllers\Tickets\TicketsController::class, 'updateMessage']);
            Route::delete('ticket/{ticket}', [Controllers\Tickets\TicketsController::class, 'remove']);
            Route::get('ticket/{ticket}/download/{fileName}', [Controllers\Tickets\TicketsFilesController::class, 'download']);
        });

        Route::prefix('statistics')
            ->group(function () {
                Route::get('staff/compare', [Controllers\Statistics\StatisticsStaffController::class, 'compare']);
                Route::get('staff/details', [Controllers\Statistics\StatisticsStaffController::class, 'staffDetails']);
                Route::get('staff/total-compare', [Controllers\Statistics\StatisticsStaffController::class, 'totalCompare']);
            });

        Route::prefix('telnums')
            ->group(function () {
                Route::get('incoming/{partnerId}/hourly', [Controllers\Telnums\TelnumsIncomingController::class, 'partnerDay']);
            });

        Route::prefix('bank-detail')
            ->group(function () {
                Route::post('list', [Controllers\BankDetails\BankDetailsController::class, 'list']);
                Route::get('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'get']);
                Route::post('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'create']);
                Route::put('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'update']);
                Route::delete('record/{bankDetail}', [Controllers\BankDetails\BankDetailsController::class, 'remove']);
                Route::get('options', [Controllers\BankDetails\BankDetailsController::class, 'userOptions']);
            });
    });
});

Route::get('media/{category}/{fileName}', [Controllers\Files\PublicFileController::class, 'show'])
    ->where([
        'category' => '[a-zA-Z0-9_\-]+',
        'fileName' => '[a-zA-Z0-9_\-\.]+',
    ]);
