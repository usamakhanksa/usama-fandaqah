<?php

namespace App\Http\Resources\Corneer;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;
use Vinkla\Hashids\Facades\Hashids;

class PagesResource extends JsonResource
{

    public static $wrap = '';
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {

        parent::__construct($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'title' => $this->attributesToArray()['title'],
            'slug' => $this->slug,
            'content' => $this->attributesToArray()['content'],
            'status' => $this->status,
            'order' => $this->order,
            'deleted_at' => $this->deleted_at
        ];
    }

}
