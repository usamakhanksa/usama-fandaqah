<?php

namespace App\Nova;
use App\Scopes\TeamScope;
use Laravel\Nova\Panel;
use R64\NovaFields\JSON;
use Manogi\Tiptap\Tiptap;
use NovaErrorField\Errors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Inspheric\Fields\Indicator;
use Laravel\Nova\Fields\Select;
use Surlab\UnitPrice\UnitPrice;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Textarea;
use Davidpiesse\NovaToggle\Toggle;
use Surelab\NovaEditorV2\NovaEditorV2;
use Media24si\NovaYoutubeField\Youtube;
use Fourstacks\NovaCheckboxes\Checkboxes;
use Spatie\NovaTranslatable\Translatable;
use Ebess\AdvancedNovaMediaLibrary\Fields\Media;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Http\Requests\NovaRequest;
class HotelAmenity extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\HotelAmenity::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Hotel Amenities');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Hotel Amenity');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
   
        $upload_file_size = env('MAX_UPLOAD_SIZE', 1048576) / 1024;
        return [
            Errors::make(),
        

                Text::make(__('Title Arabic'), 'title_ar')->rules('required'),
                Text::make(__('Title English'), 'title_en')->rules('required'),

                Toggle::make(__('Status'), 'status')
                    ->trueValue(1)
                    ->falseValue(0)
                    ->trueLabel(__('Active'))
                    ->falseLabel(__('Inactive'))
                    ->falseColor('#FF0000'), 

                Images::make(__('Main Image'), 'main')
                    ->singleMediaRules('max:' . $upload_file_size)
                    ->croppable(false)
                    ->hideFromIndex()
                    ->help(__('Main image size should not exceed 1MB , image extenstions accepted are : jpg,jpeg,png'))
                    ,
                Images::make(__('Gallery'), 'images')
                    ->croppable(false)
                    ->hideFromIndex()
                    ->singleMediaRules('max:' . $upload_file_size)
                    ->help(__('Gallery image size should not exceed 1MB , image extenstions accepted are : jpg,jpeg,png')),

                Textarea::make(__('Description AR'), 'description_ar')->withMeta([
                    'extraAttributes' => [
                        'style' => 'width : 60% !important;'
                    ]
                    ])->rules('required'),
                Textarea::make(__('Description EN'), 'description_en')->withMeta([
                        'extraAttributes' => [
                            'style' => 'width : 60% !important;'
                        ]
                ])->rules('required')
        ];
    }


    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }



    public static function softDeletes()
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return !$this->status;
    }


}
