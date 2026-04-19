<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class WebsiteSetting extends Model
{
    use Rememberable;

//    use HasTranslations;

//    public $translatable = ['slide_first_text', 'slide_second_text', 'title_first_text', 'title_second_text'];

    protected $guarded = [];
//    protected $fillable = [
//        'team_id','featured_unit_categories','about_us_image','enable_about_us'
//    ];

    protected $with = [
        'gallery',
        'slider'
    ];

    protected $casts = [
        'slide_first_text'  =>  'array',
        'slide_second_text'  =>  'array',
        'title_first_text'  =>  'array',
        'title_second_text'  =>  'array',
        'search_box_top_first_text' =>  'array',
        'search_box_top_second_text' =>  'array',
        'search_box_top_third_text' =>  'array',
        'search_button_text' =>  'array',
        'rights' =>  'array',
        'contact_note' =>  'array',
        'contact_address_description' =>  'array',
        'cancellation_policy' =>  'array',
        'description_block_text'    =>  'array',
        'images_block_text'    =>  'array',
        'video_block_text'    =>  'array',
        'special_features_block_text'    =>  'array',
        'general_features_block_text'    =>  'array',
        'note_for_waiting'    =>  'array',
        'options_block_text'    =>  'array',
        'view_all'    =>  'array',
        'bank_account_info' => 'array',
        'featured_unit_categories' => 'array',
        'about_us_title' => 'array',
        'about_us_content' => 'array',
        'intro_text' => 'array',
        'maximum_calendar_date' => 'date:Y-m-d',
        'minimum_calendar_date' => 'date:Y-m-d'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    } 

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function gallery()
    {
        return $this->hasMany(WebsiteGallery::class, 'website_id', 'id')->where('type' , '=' , 'gallery');
    }


    public function slider()
    {
        return $this->hasMany(WebsiteGallery::class, 'website_id', 'id')->where('type' , '=' , 'slider');
    }


    public function getRightsAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  'جميع الحقوق محفوظة ©',
                'en'    =>  'All Rights Reserved ©',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getDescriptionBlockTextAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  'الوصف',
                'en'    =>  'description',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getImagesBlockTextAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  'الصور',
                'en'    =>  'images',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getSpecialFeaturesBlockTextAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  'المميزات الخاصة',
                'en'    =>  'special Features',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getGeneralFeaturesBlockTextAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  'المميزات العامة',
                'en'    =>  'general Features',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getOptionsBlockTextAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  ' مميزات إضافية',
                'en'    =>  'options',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getVideoBlockTextAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  'فيديو',
                'en'    =>  'Video',
            ];
        }

        return json_decode($value, TRUE);
    }

    public function getContactAddressDescriptionAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  '',
                'en'    =>  '',
            ];
        }

        return json_decode($value, TRUE);
    }


    public function getBankAccountInfoAttribute($value)
    {
        if (empty($value)) {
            return [
                'ar'    =>  '',
                'en'    =>  '',
            ];
        }

        return json_decode($value, TRUE);
    }
}
