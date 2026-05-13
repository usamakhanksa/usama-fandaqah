<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Scopes\TeamScope;

class ServiceLog extends Model
{


    use SoftDeletes, LogsActivity;


    protected $fillable = [
        'team_id',
        'transaction_id',
        'unit_id',
        'service_id',
        'number',
        'amount',
        'meta',
        'is_freezed',
        'business_date',
        'user_id'
    ];

    protected $casts = [
        'meta' => 'json',
        'is_freezed' => 'boolean',
        'business_date' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    } 

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getId()
    {
        return $this->id;
    }
}
