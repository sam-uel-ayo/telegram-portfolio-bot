<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

Route::post('/webhook', [TelegramController::class, 'handleWebhook']);



