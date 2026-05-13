<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Website\WebsiteSettingController;
// use App\Http\Controllers\Website\WebsitePageController;
// use App\Http\Controllers\Website\WebsiteGalleryController;

Route::middleware(['auth', 'verified', 'team.scope'])->group(function () {
    // Route::resource('website-settings', WebsiteSettingController::class)->names('website-settings');
    // Route::resource('website-pages', WebsitePageController::class)->names('website-pages');
    // Route::resource('website-galleries', WebsiteGalleryController::class)->names('website-galleries');
    // Route::get('website/preview', [WebsiteSettingController::class, 'preview'])->name('website.preview');
});
