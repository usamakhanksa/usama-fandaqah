<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;

class Country extends Model
{
    use Rememberable;
	use HasTranslations;

    protected $primaryKey = 'code';

    protected $filable = ['code', 'is_gcc', 'title'];

    public $translatable = ['title'];

    public function customers()
    {
    	return $this->hasMany(Customer::class);
    }
}
