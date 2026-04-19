<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceLogNote extends Model
{
    use SoftDeletes, LogsActivity;

    /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'service_logs_notes';
  
  protected static $logAttributes = ['service_log_id', 'payload', 'created_at', 'updated_at'];


  protected $guarded = [];

  protected $casts = [
    'payload' => 'array',
  ];

  public function service_log() {
      return $this->belongsTo(ServiceLog::class)->withTrashed();
  }

  public function setPayloadAttribute($value)
    {
        if($value !== null) {
            $compressedData = gzcompress(json_encode($value));
            $encodedData = base64_encode($compressedData);
            $this->attributes['payload'] = $encodedData;
        }
    }

    // Define a method to decode and decompress data when retrieving
    public function getPayloadAttribute($value)
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
