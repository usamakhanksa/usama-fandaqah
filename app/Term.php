<?php

namespace App;

use App\Traits\HasTeam;
use App\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Term extends Model
{
    use Rememberable;
    use HasTranslations, HasTeam, LogsActivity;

    public $translatable = ['name'];

    protected $fillable = ['type', 'order', 'status'];


    protected $attributes = [
        'order' => 0,
        'status' => 1,
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    } 

    public function transactions()
    {
        return Transaction::whereJsonContains('meta->type', $this->id)->where('is_public' , 1)->get();               
    }

}
