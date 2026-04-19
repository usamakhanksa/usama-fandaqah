<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DigitalSignature extends Model
{
    public const TYPE_RESERVATION = 'reservation';
    public const TYPE_RESERVATION_USER = 'reservation_user';
    public const TYPE_USER = 'user';
    public const TYPE_PROMISSORY = 'promissory';
    protected $fillable = ['team_id', 'ref_id', 'type', 'signature_base64', 'user_id'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public static function getUserSignature () {
        return self::where('user_id', \Auth()->user()->id)
                            ->whereNull('team_id')
                            ->whereNull('ref_id')
                            ->where('type', DigitalSignature::TYPE_USER)
                            ->first();
    }

    public static function transactionUserSignPromissory ($reservation_id, $reservation_team_id) {
        if(isset(\Auth()->user()->id)) {
            $team = Team::find(\auth()->user()->currentTeam->id);
            if($team->enable_digital_signature && $team->enable_digital_signature !== 0) {
            $user_digital_signature = self::getUserSignature();
            if(isset($user_digital_signature)) {
                $data = [
                    'team_id' => $reservation_team_id,
                    'ref_id' => $reservation_id,
                    'type' => self::TYPE_PROMISSORY,
                    'user_id' => \Auth()->user()->id,
                    'signature_base64' => $user_digital_signature->signature_base64 ?? null,
                ];
                self::insert($data);
                $data['signature'] = gzuncompress(base64_decode($data['signature_base64']));
                return (object) $data;
            }
            }
            return null;            
        }
    }

    public static function transactionUserSignReservation ($reservation_id, $reservation_team_id) {
        
        if(isset(\Auth()->user()->id)) {
            //querying due to data invalidation in presistent session 
            $team = Team::find(\auth()->user()->currentTeam->id);
            if($team->enable_digital_signature && $team->enable_digital_signature !== 0) {
            $user_digital_signature = self::getUserSignature();
            if(isset($user_digital_signature)) {
                $data = [
                    'team_id' => $reservation_team_id,
                    'ref_id' => $reservation_id,
                    'type' => self::TYPE_RESERVATION_USER,
                    'user_id' => \Auth()->user()->id,
                    'signature_base64' => $user_digital_signature->signature_base64 ?? null,
                ];
                self::insert($data);
                $data['signature'] = gzuncompress(base64_decode($data['signature_base64']));
                return (object) $data;
            }
            }
            return null;
        }
    }
}
