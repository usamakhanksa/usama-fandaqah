<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\SourceController;
use App\Http\Controllers\Marketing\OfferController;
use App\Http\Controllers\Marketing\SpecialPriceController;
use App\Http\Controllers\Marketing\PromoCodeController;
use App\Http\Controllers\Marketing\VoucherController;
use App\Http\Controllers\Marketing\PricingPreviewController;

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::resource('sources', SourceController::class)->names('sources');
        
        Route::resource('offers', OfferController::class)->names('offers');
        Route::post('offers/{offer}/toggle', [OfferController::class, 'toggle'])->name('offers.toggle');
        
        Route::resource('special-prices', SpecialPriceController::class)->names('special-prices');
        Route::post('special-prices/{special_price}/toggle', [SpecialPriceController::class, 'toggle'])->name('special-prices.toggle');
        Route::get('special-prices/calendar', [SpecialPriceController::class, 'calendar'])->name('special-prices.calendar');
        
        Route::resource('promo-codes', PromoCodeController::class)->names('promo-codes');
        Route::post('promo-codes/validate', [PromoCodeController::class, 'validateCode'])->name('promo-codes.validate');
        
        Route::resource('vouchers', VoucherController::class)->names('vouchers');
        Route::post('vouchers/{voucher}/redeem', [VoucherController::class, 'redeem'])->name('vouchers.redeem');
        
        Route::get('pricing-preview', [PricingPreviewController::class, 'index'])->name('pricing-preview.index');
        Route::post('pricing-preview/calculate', [PricingPreviewController::class, 'calculate'])->name('pricing-preview.calculate');
    });
});

