<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReservationContract extends Model
{
    protected $fillable = [
        'reservation_id',
        'team_id',
        'html_path',
        'status',
        'signed_at',
        'uuid',
        'version',
        'shorten_url_code'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function getHtmlUrlAttribute()
    {
        return Storage::disk('s3')->url($this->html_path);
    }

    public function getSnapshotUrlAttribute()
    {
        return Storage::disk('s3')->url($this->snapshot_path);
    }

    public function getPdfUrlAttribute()
    {
        return $this->pdf_path ? Storage::disk('s3')->url($this->pdf_path) : null;
    }
}
