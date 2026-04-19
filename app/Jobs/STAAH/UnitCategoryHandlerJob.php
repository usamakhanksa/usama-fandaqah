<?php

namespace App\Jobs\STAAH;

use App\Team;
use App\UnitCategory;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UnitCategoryHandlerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    public $team_id;
    public $unit_category_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($team_id, $unit_category_id)
    {
        $this->team_id = $team_id;
        $this->unit_category_id = $unit_category_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $team = Team::findOrFail($this->team_id);
            if ($team && $team->channel_manager_status == 'connected') {
                $fandaqah_unit_category = UnitCategory::findOrFail($this->unit_category_id);
                // the team is connected with staah and ready to move forwared
                $unit_categories_from_staah = $this->getRoomListingFromStaah();
                $unit_categories_ids_from_staah = [];
                if (count($unit_categories_from_staah->rooms)) {
                    foreach ($unit_categories_from_staah->rooms as $room_type) {
                        $unit_categories_ids_from_staah[] = $room_type->roomid;
                    }
                }
                if (in_array($fandaqah_unit_category->id, $unit_categories_ids_from_staah)) {
                    // i have some use cases here
                    // unit category modified ( name , prices , etc )
                    // unit category statuses has been changed like deactivated or activated
                    // there is three stauses in staah for update room type Modify, Deactivated , Active
                    // hence active , deactive in the same request of update unit category in fandaqah so i will stick to if status is not enabled
                    // i will set the room type as in active in staah and the opposite will apply

                    if ($fandaqah_unit_category->status) {
                        // to activate initiallly
                        $sendUpdateRoomTypeRequest = $this->updateRoomTypeInStaah($fandaqah_unit_category, 'Active');
                        // logger(json_encode($sendUpdateRoomTypeRequest));
                        // then to modifiy
                        $sendUpdateRoomTypeRequest = $this->updateRoomTypeInStaah($fandaqah_unit_category, 'Modify');
                        // logger(json_encode($sendUpdateRoomTypeRequest));
                    } else {
                        // it means it is disabled
                        $sendUpdateRoomTypeRequest = $this->updateRoomTypeInStaah($fandaqah_unit_category, 'Deactivated');
                        // logger(json_encode($sendUpdateRoomTypeRequest));
                    }

                    $pushAvailability = $this->pushAvailability($fandaqah_unit_category);
                }
            }
        } catch (\Throwable $e) {
            logger($e->getMessage());
        }
    }

    public function getRoomListingFromStaah()
    {
        $body = ['team_id' => $this->team_id];
        $url = config('app.staah_mediator_api_url') . '/api/v1/roomType/list';
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        $request = $client->request('POST', $url, [
            'body' => \GuzzleHttp\json_encode($body),
            'exceptions' => true
        ]);
        return json_decode($request->getBody()->getContents());
    }

    public function updateRoomTypeInStaah($category, $InvStatusType)
    {
        $unit_catergories = $this->formUnitCategories($category);
        $body = [
            'unit_categories' => $unit_catergories,
            'InvStatusType' => $InvStatusType,
            'team_id' => $this->team_id
        ];

        // logger(\GuzzleHttp\json_encode($body));
        $url = config('app.staah_mediator_api_url') . '/api/v1/roomType/update';
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        $request = $client->request('POST', $url, [
            'body' => \GuzzleHttp\json_encode($body),
            'exceptions' => true
        ]);
        return json_decode($request->getBody()->getContents());
    }

    public function formUnitCategories($category)
    {
        return [
            [
                'id' => $category->id,
                'team_id' => $category->team_id,
                'name' => $category->name,
                'name_array' => json_decode($category->getOriginal('name')),
                'status' => $category->status,
                'show_in_website' => $category->show_in_website,
                'units_count' => $category->units->count(),
                'main_image' => is_null($category->getFirstMediaUrl('main')) || empty($category->getFirstMediaUrl('main')) ? '/images/placeholder.jpg' : $category->getFirstMediaUrl('main'),
                'rooms_to_sell' => count($category->available_to_sync_units), // based on rooms related to the same category
                'prices' => $category->dailyPrices(),
                'prices_as_days_names' => $category->dailyByDayNamePrices()
            ]
        ];
    }

    public function formUnitCategory($category)
    {
        return [
            'id' => $category->id,
            'team_id' => $category->team_id,
            'name' => $category->name,
            'name_array' => json_decode($category->getOriginal('name')),
            'status' => $category->status,
            'show_in_website' => $category->show_in_website,
            'units_count' => $category->units->count(),
            'main_image' => is_null($category->getFirstMediaUrl('main')) || empty($category->getFirstMediaUrl('main')) ? '/images/placeholder.jpg' : $category->getFirstMediaUrl('main'),
            'rooms_to_sell' => count($category->available_to_sync_units), // based on rooms related to the same category
            'prices' => $category->dailyPrices(),
            'prices_as_days_names' => $category->dailyByDayNamePrices()
        ];
    }

    public function pushAvailability($category)
    {
        $format_category = $this->formUnitCategory($category);
        $body = [
            'category' => $format_category
        ];

        // logger(\GuzzleHttp\json_encode($body));
        $url = config('app.staah_mediator_api_url') . '/api/v1/availability';
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        $request = $client->request('POST', $url, [
            'body' => \GuzzleHttp\json_encode($body),
            'exceptions' => true
        ]);
        return json_decode($request->getBody()->getContents());
    }
}
