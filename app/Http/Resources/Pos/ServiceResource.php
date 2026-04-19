<?php

namespace App\Http\Resources\Pos;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{


    /**
     * UnitResource constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        static::$wrap = null;
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

        $image = null;
        if(app()->environment() === 'local'){

            if(is_null($this->image) || $this->image == ''){
                $image = asset('no-service-image.png');
            }else{
                $image =  'http://fandaqah.test/storage/' . $this->image;
            }
        }else{

            if(is_null($this->image) || $this->image == ''){
                $image = asset('no-service-image.png');
            }else{
                if(app()->environment() == 'development'){
                    $image = 'https://app-dev-fandaqah.s3.me-south-1.amazonaws.com/' . $this->image;
                }

                if(app()->environment() == 'production'){
                    $image = 'https://app-fandaqah.s3.me-south-1.amazonaws.com/' . $this->image;
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'image'    =>  $image,
            'category_name' => $this->serviceCategory->name
        ];
    }

}
