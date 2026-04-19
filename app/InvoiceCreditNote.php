<?php

namespace App;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceCreditNote extends Model
{
    use SoftDeletes;
    protected $fillable = [];

    protected $appends = [
         'print_url' , 'public_url' , 'hash_id'
    ];

    protected $casts = [
        'is_reported_to_zatca' => 'array',
        'payload' => 'array',
    ];


    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function invoice()
    {
        return $this->belongsTo(ReservationInvoice::class , 'reservation_invoice_id');
    }

    public function getPrintUrlAttribute()
    {
        $id = Hashids::encode($this->id);
        return url("/home/reservation/credit-note/{$id}");
    }

    public function getPublicUrlAttribute()
    {
        $id = Hashids::encode($this->id);
        return url("/home/reservation/credit-note/{$id}/public");
    }

    public function setIsReportedToZatcaAttribute($value)
    {
        if($value !== null) {
            $compressedData = gzcompress(json_encode($value));
            $encodedData = base64_encode($compressedData);
            $this->attributes['is_reported_to_zatca'] = $encodedData;
        }
    }

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
