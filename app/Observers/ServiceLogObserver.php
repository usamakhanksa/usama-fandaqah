<?php

namespace App\Observers;

use App\ServiceLog;
use App\Services\AuditEnforcementService;
use Illuminate\Support\Facades\Auth;

class ServiceLogObserver
{
    protected $enforcement;

    public function __construct(AuditEnforcementService $enforcement)
    {
        $this->enforcement = $enforcement;
    }

    /**
     * Handle the ServiceLog "creating" event.
     */
    public function creating(ServiceLog $serviceLog)
    {
        $teamId = $serviceLog->team_id ?? Auth::user()->current_team_id ?? null;
        $date = $serviceLog->business_date ?? null;

        if ($teamId && $date) {
            if ($this->enforcement->isDateClosed($teamId, $date) && !$this->enforcement->canBackdate()) {
                abort(403, 'Cannot create service logs for a closed business date.');
            }
        }
    }

    /**
     * Handle the ServiceLog "updating" event.
     */
    public function updating(ServiceLog $serviceLog)
    {
        if ($serviceLog->getOriginal('is_freezed')) {
            abort(403, 'This service log is frozen by Night Audit and cannot be modified.');
        }
    }

    /**
     * Handle the ServiceLog "deleting" event.
     */
    public function deleting(ServiceLog $serviceLog)
    {
        if ($serviceLog->is_freezed) {
            abort(403, 'This service log is frozen by Night Audit and cannot be deleted.');
        }
    }
}
