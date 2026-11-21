<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::prefix('public/v1')
    ->middleware('token.auth:partner.read')
    ->group(function () {
        Route::get('promotions', [Controllers\Promotion\PromotionController::class, 'publicList']);
    });
