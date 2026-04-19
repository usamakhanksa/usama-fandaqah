<?php

namespace Surelab\Settings\Http\Controllers;

use App\Http\Resources\IntegrationLogResource;
use App\Http\Resources\JawalyLogResource;
use App\Integration;
use App\IntegrationLog;
use App\IntegrationSettings;
use App\Jobs\SCTH\CancelBooking;
use App\Jobs\SCTH\CreateBooking;
use App\Jobs\SCTH\ExpenseBooking;
use App\Jobs\SCTH\OccupancyUpdate;
use App\Jobs\SCTH\UpdateBooking;
use App\Reservation;
use App\Setting;
use App\Team;
use App\TeamCounter;



use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Storage\EntryQueryOptions;
use Liliom\Unifonic\UnifonicFacade;
use Surelab\Settings\Http\Requests\CheckDomainRequest;
use Surelab\Settings\Http\Requests\UpdateDomainRequest;
use Surelab\Settings\Traits\Settings;
use Surelab\Settings\ValueObjects\SettingRegister;

/**
 * Class SettingsController
 * @package Surelab\Settings\Http\Controllers
 */
class LogController
{
    public function scth($key, Request $request, EntriesRepository $storage)
    {
        $team_id = auth()->user()->current_team_id;
        $list = [];

        switch ($key) {
            case 'scth':
                $jobs = $storage->get(
                    EntryType::JOB,
                    EntryQueryOptions::fromRequest($request)
                );

                $list = collect($jobs)
                ->filter(function ($job) {
                    return $job->content['name'] == CancelBooking::class ||
                        $job->content['name'] == CreateBooking::class ||
                        $job->content['name'] == ExpenseBooking::class ||
                        $job->content['name'] == OccupancyUpdate::class ||
                        $job->content['name'] == UpdateBooking::class;
                })
                ->filter(function ($job) {
                    return isset($job->content['data']['reservation']);
                })
                ->filter(function ($job) use ($team_id) {
                    if(isset($job->content['data']['reservation'])) {
                        $object = $job->content['data']['reservation'];
                        $objectId = substr($object, strpos($object, ":") + 1);
                        $reservation = Reservation::withOutGlobalScope('team_id')->find($objectId);

                        if($reservation and $reservation->team_id) {
                            return $reservation->team_id == $team_id;
                        }
                    }
                });
                break;
            default:
                $list = [];
        }

        // Get current page form url e.x. &page=1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 50;
        $itemCollection = collect($list);

        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();

        // Create our paginator and pass it to the view
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);

        // set url path for generted links

        $paginatedItems->setPath($request->url());

        return response()->json($paginatedItems);
    }

    public function shomos(Request $request)
    {
        $team_id = auth()->user()->current_team_id;
        $logs = IntegrationLog::where('team_id', $team_id)
            ->where('type', 'App\Integration\SHMS')
            ->where('payload->id', 'not like', 'null' )
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));
        return IntegrationLogResource::collection($logs);
    }

    public function jawaly(Request $request)
    {
        $team_id = auth()->user()->current_team_id;
        $logs = IntegrationLog::where('team_id', $team_id)
            ->where('type', 'Jawaly')
            // ->where('payload->id', 'not like', 'null' )
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 5));
        return IntegrationLogResource::collection($logs);
    }

    public function unifonic(Request $request)
    {
        $team_id = auth()->user()->current_team_id;
        $logs = IntegrationLog::where('team_id', $team_id)
            ->where('type', 'unifonic')
            // ->where('payload->id', 'not like', 'null' )
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 5));
        return IntegrationLogResource::collection($logs);
    }

}
