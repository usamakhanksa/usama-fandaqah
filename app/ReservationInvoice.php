<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class ReservationInvoice extends Model
{
    use HasTeam , SoftDeletes;

    protected $guarded = [];
    protected $appends = [
        'pdf_url', 'print_url' , 'public_url', 'hash_id'
    ];

    protected $casts = ['data' => 'array', 'is_reported_to_zatca' => 'array'];

    protected $with = ['invoiceCreditNote'];

    protected static function boot()
    {
        // framework issue -> fixed in later version of laravel
        // !!!!!!! work around set mutator not working on save() !!!!!!!!
        parent::boot();

        static::saving(function ($model) {
           if(isset($model->is_reported_to_zatca)) {
                if (is_object($model->is_reported_to_zatca)) {
                    $compressedData = gzcompress(json_encode($model->is_reported_to_zatca));
                    //$model->attributes['is_reported_to_zatca'] = base64_encode($compressedData);
                    //dd($model);
                    $data = base64_encode($compressedData);
                    $model->is_reported_to_zatca = $data;
                    // $decodedData = base64_decode(base64_encode($compressedData));
                    // $g_decoded = gzuncompress($decodedData);
                    // dd(json_decode($g_decoded));
                    // dd($g_decoded);
                } 
           }
        });
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class)->withTrashed();
    }


    public function unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    public function getPdfUrlAttribute()
    {
        $id = Hashids::encode($this->id);
        return url("/home/reservation/pdf/invoice/{$id}");
    }

    public function getPrintUrlAttribute()
    {
        $id = Hashids::encode($this->id);
        $locale = app()->getLocale();
        if($this->reservation->reservation_type == 'group'){
            // return url("/home/group-reservation/company-live-invoice?inv={$id}&locale={$locale}");
            if(!$this->is_group_reservation){
                return url("/home/reservation/invoice/{$id}");
            }else{
                return url("/home/reservation/group-invoice?inv={$id}");
            }

        }else{
            return url("/home/reservation/invoice/{$id}");
        }
    }

    public function getPublicUrlAttribute()
    {
        $id = Hashids::encode($this->id);
        $locale = app()->getLocale();
        if($this->reservation->reservation_type == 'group'){

            if(!$this->is_group_reservation){
                return url("/home/reservation/invoice/{$id}/public?lang=$locale");
            }else{
                return url("/home/reservation/group-invoice/public?inv={$id}&type=embed&locale=$locale");
            }
        }else{
            return url("/home/reservation/invoice/{$id}/public?lang=$locale");
        }
        
    }

    public function scopeWhereIntersectsFrom($query,$dateFrom){

        return  $query->whereNotNull('from')
            ->where('from' , '>=' , $dateFrom)
            ->orWhere('to' , '>=' , $dateFrom);
    }

    public function scopeWhereIntersectsTo($query,$dateTo){

        return  $query->whereNotNull('to')
            ->where('to' , '<=' , $dateTo)
            ->orWhere('from' , '<=' , $dateTo);
    }

    public function invoiceCreditNote()
    {
        return $this->hasOne(InvoiceCreditNote::class);
    }
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }
    
    // public function setIsReportedToZatcaAttribute($value)
    // {
    //     if($this->exists()) {
    //         $compressedData = gzcompress(json_encode($value));
    //         $encodedData = base64_encode($compressedData);
    //         $this->attributes['is_reported_to_zatca'] = $encodedData;
    //     } else {
    //         if($value !== null) {
    //             $compressedData = gzcompress(json_encode($value));
    //             $encodedData = base64_encode($compressedData);
    //             $this->attributes['is_reported_to_zatca'] = $encodedData;
    //         }
    //     }
    // }

    // Define a method to decode and decompress data when retrieving
    public function getIsReportedToZatcaAttribute($value)
    {
        if($value !== null) {
            if (is_object(json_decode($value))) {
                return json_decode($value);
            }
            $decodedData = base64_decode($value);
            $g_decoded = gzuncompress($decodedData);
            return json_decode($g_decoded);
        }
    }
}
