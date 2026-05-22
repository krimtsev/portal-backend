<?php

use App\Models\Ticket\Ticket;
use App\Models\User\User;
use App\Notifications\Ticket\TicketCreatedNotification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return 'Hello World'; });
