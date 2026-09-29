<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ShippingController;
use App\Http\Controllers\Frontend\ChatbotController;

Route::post('/invoice/callback', [ShippingController::class, 'PaymentCallback'])->name('frontend.invoice.callback');

// Endpoint untuk dipanggil oleh n8n (Tool: search_products)
Route::post('/chatbot/search', [ChatbotController::class, 'searchAPI'])->name('api.chatbot.search');
