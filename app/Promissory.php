<?php

namespace App;
use App\Scopes\TeamScope;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promissory extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
     protected $guarded = [];

     protected $appends = ['hash_id'];

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    }

    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    public static function getPromissoryCustomerSignature ($reservation_team_id, $reservation_id) {
        $signature = DigitalSignature::where('team_id',$reservation_team_id)
        ->where('ref_id',$reservation_id)
        ->where('type', DigitalSignature::TYPE_PROMISSORY)
        ->whereNull('user_id')
        ->first();
        if(isset($signature)) {
            try {
                $signature['signature'] =  gzuncompress(base64_decode($signature->signature_base64));
                $signature = (object) $signature;
            } catch (\Exception $e) {
                \Log::error('Error decoding customer or company digital signature: ' . $e->getMessage() . ' for team ' . $reservation_team_id . ' and reservation id ' . $reservation_id);
                $signature = null;
            }
        }
        return $signature;
    }

    public static function getPromissoryOfficialSignature($reservation_team_id, $reservation_id) {
       $signature = DigitalSignature::where('team_id',$reservation_team_id)
        ->where('ref_id',$reservation_id)
        ->where('type',DigitalSignature::TYPE_PROMISSORY)
        ->whereNotNull('user_id')
        ->first();
        if(isset($signature)) {
            try {
                $signature['user_id'] = $signature->user_id;
                $signature['signature'] = gzuncompress(base64_decode($signature->signature_base64));
                $signature = (object) $signature;
            } catch (\Exception $e) {
                \Log::error('Error decoding official digital signature: ' . $e->getMessage() . ' for team ' . $reservation_team_id . ' and reservation id ' . $reservation_id);
                $signature = null;
            }
        }
        return $signature;
    }
}
