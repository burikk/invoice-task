<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\Controllers\CreateInvoiceController;
use Modules\Invoices\Presentation\Http\Controllers\SendInvoiceController;
use Modules\Invoices\Presentation\Http\Controllers\ViewInvoiceController;

Route::prefix('invoices')->group(function () {
    Route::post('/', CreateInvoiceController::class);
    Route::get('/{id}', ViewInvoiceController::class)->whereUuid('id');
    Route::post('/{id}/send', SendInvoiceController::class)->whereUuid('id');
});
