<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class NovaStaticsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->getMethod() === 'GET') {
            $version = Cache::get('js_version_number');
            $routes = [
                '/nova-api/styles/nova-fields?v=' . $version,
                '/nova-api/styles/nova-slug-field?v=' . $version,
                '/nova-api/styles/nova-phone-field?v=' . $version,
                '/nova-api/styles/nova-toggle?v=' . $version,
                '/nova-api/styles/nova-medialibrary-field?v=' . $version,
                '/nova-api/styles/tabs?v=' . $version,
                '/nova-api/styles/nova-checkboxes?v=' . $version,
                '/nova-api/styles/nova-file-upload-field?v=' . $version,
                '/nova-api/styles/laravel-nova-theme-responsive?v=' . $version,
                '/nova-api/styles/indicator?v=' . $version,
                '/nova-api/styles/nova-time-field?v=' . $version,
                '/nova-api/styles/spark-impersonate?v=' . $version,
                '/nova-api/styles/nova-youtube-field?v=' . $version,
                '/nova-api/styles/time?v=' . $version,
                '/nova-api/styles/translatable?v=' . $version,
                '/nova-api/styles/nova-volve-rtl?v=' . $version,
                '/nova-api/styles/multiselect-field?v=' . $version,
                '/nova-api/styles/novafieldcheckboxes?v=' . $version,
                '/nova-api/styles/advanced-number?v=' . $version,
                '/nova-api/styles/searchable-select?v=' . $version,
                '/nova-api/styles/big-filters?v=' . $version,
                '/nova-api/styles/custom-date?v=' . $version,
                '/nova-api/styles/custom-toggle?v=' . $version,
                '/nova-api/styles/customer-notes?v=' . $version,
                '/nova-api/styles/DashboardUnits?v=' . $version,
                '/nova-api/styles/images-custom-field?v=' . $version,
                '/nova-api/styles/nova-editor-v2?v=' . $version,
                '/nova-api/styles/percentage?v=' . $version,
                '/nova-api/styles/nova-custom-rtl?v=' . $version,
                '/nova-api/styles/tel-input?v=' . $version,
                '/nova-api/styles/theme?v=' . $version,
                '/nova-api/styles/total-occupied?v=' . $version,
                '/nova-api/styles/unit-price?v=' . $version,
                '/nova-api/styles/color?v=' . $version,
                '/nova-api/styles/nova-money-field?v=' . $version,
                '/nova-api/styles/nova-breadcrumbs?v=' . $version,
                '/nova-api/styles/NovaPermissions?v=' . $version,
                '/nova-api/styles/Calender?v=' . $version,
                '/nova-api/styles/settings?v=' . $version,
                '/nova-api/styles/units?v=' . $version,
                '/nova-api/styles/users?v=' . $version,
                '/nova-api/styles/transactions-feature?v=' . $version,
                '/nova-api/styles/techincal-support?v=' . $version,
                '/nova-api/styles/financial-management?v=' . $version,
                '/nova-api/styles/pos?v=' . $version,
                '/nova-api/styles/notification-control?v=' . $version,
                '/nova-api/styles/customer-reviews?v=' . $version,
                '/nova-api/styles/customers?v=' . $version,
                '/nova-api/scripts/notifications?v=' . $version,
                '/nova-api/scripts/nova-fields?v=' . $version,
                '/nova-api/scripts/nova-multiple-dashboard?v=' . $version,
                '/nova-api/scripts/nova-date-range-filter?v=' . $version,
                '/nova-api/scripts/nova-slug-field?v=' . $version,
                '/nova-api/scripts/nova-phone-field?v=' . $version,
                '/nova-api/scripts/nova-echo?v=' . $version,
                '/nova-api/scripts/nova-toggle?v=' . $version,
                '/nova-api/scripts/nova-attach-many?v=' . $version,
                '/nova-api/scripts/errors-field?v=' . $version,
                '/nova-api/scripts/nova-grouped-field?v=' . $version,
                '/nova-api/scripts/nova-medialibrary-field?v=' . $version,
                '/nova-api/scripts/media-lib-images-field?v=' . $version,
                '/nova-api/scripts/tabs?v=' . $version,
                '/nova-api/scripts/nova-checkboxes?v=' . $version,
                '/nova-api/scripts/nova-file-upload-field?v=' . $version,
                '/nova-api/scripts/laravel-nova-theme-responsive?v=' . $version,
                '/nova-api/scripts/indicator?v=' . $version,
                '/nova-api/scripts/nova-time-field?v=' . $version,
                '/nova-api/scripts/spark-impersonate?v=' . $version,
                '/nova-api/scripts/nova-youtube-field?v=' . $version,
                '/nova-api/scripts/time?v=' . $version,
                '/nova-api/scripts/translatable?v=' . $version,
                '/nova-api/scripts/multiselect-field?v=' . $version,
                '/nova-api/scripts/novafieldcheckboxes?v=' . $version,
                '/nova-api/scripts/searchable-select?v=' . $version,
                '/nova-api/scripts/big-filters?v=' . $version,
                '/nova-api/scripts/custom-date?v=' . $version,
                '/nova-api/scripts/custom-toggle?v=' . $version,
                '/nova-api/scripts/customer-notes?v=' . $version,
                '/nova-api/scripts/DashboardUnits?v=' . $version,
                '/nova-api/scripts/images-custom-field?v=' . $version,
                '/nova-api/scripts/nova-editor-v2?v=' . $version,
                '/nova-api/scripts/percentage?v=' . $version,
                '/nova-api/scripts/tel-input?v=' . $version,
                '/vendor/nova/jquery.js?v=' . $version,
                '/vendor/nova/arrive.js?v=' . $version,
                '/vendor/nova/hashids.js?v=' . $version,
                '/nova-api/scripts/theme?v=' . $version,
                '/nova-api/scripts/total-occupied?v=' . $version,
                '/nova-api/scripts/unit-price?v=' . $version,
                '/nova-api/scripts/nova-scroll-top?v=' . $version,
                '/js/my-nova-scroll-to-error.js?v=' . $version,
                '/nova-api/scripts/color?v=' . $version,
                '/nova-api/scripts/nova-money-field?v=' . $version,
                '/js/nova-permissions.js?v=' . $version,
                '/js/helpers.js?v=' . $version,
                '/js/pusher.js?v=' . $version,
                '/nova-api/scripts/nova-breadcrumbs?v=' . $version,
                '/nova-api/scripts/NovaPermissions?v=' . $version,
                '/nova-api/scripts/Calender?v=' . $version,
                '/nova-api/scripts/settings?v=' . $version,
                '/nova-api/scripts/units?v=' . $version,
                '/nova-api/scripts/users?v=' . $version,
                '/nova-api/scripts/transactions-feature?v=' . $version,
                '/nova-api/scripts/techincal-support?v=' . $version,
                '/nova-api/scripts/financial-management?v=' . $version,
                '/nova-api/scripts/pos?v=' . $version,
                '/nova-api/scripts/notification-control?v=' . $version,
                '/nova-api/scripts/customer-reviews?v=' . $version,
                '/nova-api/scripts/customers?v=' . $version,

            ];
            if (\in_array($request->getRequestUri(), $routes, true)) {
                return app(SetCacheControl::class)
                    ->handle($request, static function ($request) use ($next) {
                        return $next($request);
                    }, 'private;max_age=604800;etag');
            }
        }

        return $next($request);
    }
}
