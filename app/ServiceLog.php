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


    protected $casts = [
        'meta' => 'json',
    ];

    protected static $logAttributes = ['number', 'amount', 'created_at', 'updated_at'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $guraded = [];

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
