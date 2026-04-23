<?php

namespace App\Nova\Metrics;

use App\Models\SafeTransaction;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;

class NetSafeBalance extends Value {
    public function calculate(NovaRequest $request) {
        $deposits = SafeTransaction::where('type', 'deposit')->sum('amount');
        $withdrawals = SafeTransaction::where('type', 'withdrawal')->sum('amount');
        return $this->result($deposits - withdrawals)->currency('SAR');
    }
    public function uriKey() { return 'net-safe-balance'; }
}
