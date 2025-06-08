<?php

use App\Http\Controllers\InboundEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/postmark/inbound', InboundEmailController::class);
