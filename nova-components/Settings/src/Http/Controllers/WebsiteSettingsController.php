<?php

/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 10/2/19
 * Time: 10:15 AM
 */

namespace Surelab\Settings\Http\Controllers;

use App\City;
use App\Team;
use App\Rating;
use App\WebsitePage;
use App\UnitCategory;
use App\WebsiteGallery;
use App\WebsiteSetting;
use App\Handlers\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use App\Jobs\FeaturedUnitCategoriesJob;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\WebsitePageRequest;
use Illuminate\Support\Facades\Validator;
use Surelab\Settings\Entities\SettingValue;
use App\Http\Resources\Corneer\PagesResource;
use App\Http\Resources\Corneer\CitiesResource;
use Surelab\Settings\ValueObjects\SettingRegister;
use App\Http\Resources\Corneer\RelatedHotelsResource;
use Surelab\Settings\Http\Requests\CheckDomainRequest;
use Surelab\Settings\Http\Requests\UpdateDomainRequest;
use App\Http\Resources\CustomerReviews\WebsiteRatingResource;
use App\Http\Resources\Corneer\FeaturedUnitCategoriesResource;

class WebsiteSettingsController
{
    public function updateWebsiteGallery(Team $team)
    {

        /** @var WebsiteSetting $websiteSetting */
        $websiteSetting = $team->websiteSetting;

        $websiteSetting->enable_gallery = \request('enable_gallery', false);

        //        $websiteSetting->gallery()->delete();
        collect(\request('images'))->each(function ($image) use ($websiteSetting) {
            $websiteSetting->gallery()->create(['path'  =>  $image])->save();
        });

        $websiteSetting->save();

        return response()->json($websiteSetting->toArray());
    }

    public function uploadFile(Request $request)
    {
        $path = $request->file('file')->store('/settings/' . auth()->user()->current_team_id);

        $disk = \Storage::disk();

        return $disk->url($path);
    }

    public function uploadFavicon(Request $request)
    {
        $path = $request->file('file')->store('public/settings/' . auth()->user()->current_team_id);

        $disk = \Storage::disk();

        return $disk->url($path);
    }

    public function updateWebsiteSettings(Team $team)
    {
        $team->websiteSetting->forceFill(\request()->toArray());
        $team->websiteSetting->save();

        return response()->json($team->websiteSetting->toArray());
    }

    public function websiteSettings(Team $team)
    {
        $websiteSetting = $team->websiteSetting;


        if (is_null($websiteSetting)) {
            $team->websiteSetting()->create()->save();

            $websiteSetting = $team->websiteSetting()->get()->first();

            $websiteSetting->forceFill([
                'background_color'    =>  '#fff',
                'banner_file'   =>  '/web-assets/images/banner-placeholder.jpg',
                'banner_search_box_background_color'    =>  '#313131',
                'banner_search_button_background_color' =>  '#5b5b5b',
                'banner_search_button_text_color'   =>  '#fff',
                'banner_text_color' =>  '#fff',
                'basic_text_color'  =>  '#777',
                'button_background_color'   =>  '#5b5b5b',
                'button_color'  =>  '#fff',
                'enable_gallery'    =>  false,
                'enable_social' =>  false,
                'footer_logo'   =>  '',
                'footer_text_color' =>  '#fff',
                'footer_background_color' =>  '#000',
                'logo'  =>  '/web-assets/images/logo.png',
                'favicon'  =>  '',
                'social_background_color'   =>  '#282828',
                'social_icons_color'    =>  '#fff',
                'sub_text_color'    =>  '#777',
                'slide_first_text'  =>  [
                    'ar'    =>  '',
                    'en'    =>  '',
                ],
                'slide_second_text'  =>  [
                    'ar'    =>  '',
                    'en'    =>  '',
                ],
                'title_first_text'  =>  [
                    'ar'    =>  '',
                    'en'    =>  '',
                ],
                'title_second_text'  =>  [
                    'ar'    =>  '',
                    'en'    =>  '',
                ],
                'cancellation_policy'  =>  [
                    'ar'    =>  '',
                    'en'    =>  '',
                ],
                'search_box_top_first_text'  =>  [
                    'ar'    =>  'اختر المدينة',
                    'en'    =>  'Select City',
                ],
                'search_box_top_second_text'  =>  [
                    'ar'    =>  'حدد الفندق',
                    'en'    =>  'Select hotel',
                ],
                'search_box_top_third_text'  =>  [
                    'ar'    =>  'حدد الموعد المناسب',
                    'en'    =>  'Select the appropriate date',
                ],
                'search_button_text'  =>  [
                    'ar'    =>  'بحث',
                    'en'    =>  'Search',
                ],
                'rights'  =>  [
                    'ar'    =>  'جميع الحقوق محفوظة ©',
                    'en'    =>  'All Rights Reserved ©',
                ],
                'description_block_text'  =>  [
                    'ar'    =>  'الوصف',
                    'en'    =>  'description',
                ],
                'images_block_text'  =>  [
                    'ar'    =>  'الصور',
                    'en'    =>  'images',
                ],
                'special_features_block_text'  =>  [
                    'ar'    =>  'المميزات الخاصة',
                    'en'    =>  'special Features',
                ],
                'general_features_block_text'  =>  [
                    'ar'    =>  'المميزات العامة',
                    'en'    =>  'general Features',
                ],
                'options_block_text'  =>  [
                    'ar'    =>  ' مميزات إضافية',
                    'en'    =>  'options',
                ],
            ])->save();
        }

        $list = [];
        $settings = SettingValue::withoutGlobalScope('team_id')->where('team_id', '=', $team->id)->get();
        foreach ($settings as $setting) {
            $list[$setting->key] = $setting->value;
        }

        $slides = $team->websiteSetting ? $team->websiteSetting->slider : null;

        $settings = array_merge([
            'currency' => $team->currency,
            'main_team_id' => $team->id,
            'team_name' => $team->name,
            'bills_client_id' => $team->sure_bills_client_id,
            'bills_secret'    => $team->sure_bills_secret,
            'ratings' => $this->getRatings($team->id, 2),
            'slider' => $slides,  'has_attached_hotels' => (bool) count($team->attachedHotels),
            'enable_website' => $team->enable_website
        ], $websiteSetting->toArray(), $list);

        return response()->json($settings);
    }

    function getRatings($team_id, $total_number)
    {
        $ratings = Rating::with('reservation', 'reservation.unit')
            ->where('team_id', $team_id)
            ->where('status', 1)
            // ->orderByDesc('overall_rating')
            ->orderByDesc('created_at')
            ->get()
            ->take($total_number);
        return WebsiteRatingResource::collection($ratings);
    }

    public function checkDomain(CheckDomainRequest $request)
    {
        return response()->json(['success'  =>  true]);
    }


    public function checkDns(Request $request)
    {
        $urlString = $request->get('private_domain');

        $restricted_domains = env('RESTRICTED_DOMAINS');


        if (strpos($urlString, $restricted_domains) !== false) {
            return response()->json(['success' => false, 'restricted' => true, 'not_unique' => false]);
        }

        if (!$this->domainIsUnique($urlString)) {
            return response()->json(['success' => false, 'restricted' => false, 'not_unique' => true]);
        }


        $domain = str_replace(array('http://', 'https://', '/'), '', $urlString);
        $ip = gethostbyname($domain);
        if ($ip == config('app.ip')) {
            return response()->json(['success' => true, 'ip' => $ip, 'restricted' => false, 'not_unique' => false]);
        } else {
            return response()->json(['success' => false, 'ip' => $ip, 'restricted' => false, 'not_unique' => false]);
        }
    }

    function domainIsUnique($domain)
    {

        $private_domains = collect(Team::whereNull('deleted_at')->whereNotNull('private_domain')->where('id', '!=', auth()->user()->current_team_id)->get())->pluck('private_domain')->toArray();
        if (in_array($domain, $private_domains)) {
            return false;
        } else {
            return true;
        }
    }

    public function clearPrivateDomain(Request $request)
    {
        $team = Team::find($request->get('team_id'));
        $team->private_domain_status = 'deleted';
        $team->save();

        /** @essam : we will execute our delete command  here */


        return response()->json(['status' => Response::HTTP_OK]);
    }

    public function updateDomain(UpdateDomainRequest $request)
    {
        $team = Team::find($request->get('id'));

        $team->forceFill([
            'slug'                  => $request->get('slug'),
            'enable_website'        => $request->get('enable_website'),
            'enable_private_domain' => $request->get('enable_website') && $request->get('private_domain') ? true : false,
            'private_domain'        => $request->get('private_domain'),
            'private_domain_status' => $request->get('private_domain_status')
        ])->save();

        // update sure bills redirect url
        // $team->updateBillsAccountRedirectUrls();

        return response()->json(['success' => true, 'team' => $team]);
    }


    /** ---------------------   New Dev in Website ---------------------------------- */


    /**
     * Upload Slider in Slider Engine
     * @param Request $request
     * @return JsonResponse
     */
    public function sliderUploadHandler(Request $request)
    {
        $file = $request->file('file');

        // Get the maximum upload size from the environment variable
        $maxUploadSize = env('MAX_UPLOAD_SIZE', 1048576); // Default to 1 MB if not set

        // Check if the file size exceeds the max upload size
        if ($file->getSize() > $maxUploadSize) {
            return response()->json(['status' => 'error', 'message' => 'File size exceeds the maximum limit.'], 400);
        }

        // recieve file
        $file = $request->file('file')->store('public/settings/' . auth()->user()->current_team_id);
        // caching it's full path
        $path = Storage::disk()->url($file);


        // get the team
        $team = Team::find(auth()->user()->current_team_id);

        $websiteSetting = $team->websiteSetting;

        $websiteSetting->slider()->create(['path'  =>  $path, 'type' => 'slider', 'object' => ['file' => $file]])->save();

        $websiteSetting->save();

        return response()->json(['status' => 'slider_uploaded']);
    }

    /**
     * Enable or Disable Photo Album
     * @param Request $request
     * @return JsonResponse
     */
    public function enableOrDisablPhotoAlbum(Request $request)
    {

        $settings =   Team::find(auth()->user()->current_team_id)->websiteSetting;
        $settings->enable_gallery = $request->get('status');
        $settings->save();

        return response()->json(['status' => $settings->enable_gallery]);
    }

    /**
     * Upload Photos in Photos Engine
     * @param Request $request
     * @return JsonResponse
     */
    public function photosUploadHandler(Request $request)
    {
        $file = $request->file('file');

        // Get the maximum upload size from the environment variable
        $maxUploadSize = env('MAX_UPLOAD_SIZE', 1048576); // Default to 1 MB if not set

        // Check if the file size exceeds the max upload size
        if ($file->getSize() > $maxUploadSize) {
            return response()->json(['status' => 'error', 'message' => 'File size exceeds the maximum limit.'], 400);
        }

        // receive file
        $file = $request->file('file')->store('public/settings/' . auth()->user()->current_team_id);
        // caching it's full path
        $path = \Storage::disk()->url($file);

        // get the team
        $team = Team::find(auth()->user()->current_team_id);

        $websiteSetting = $team->websiteSetting;

        $websiteSetting->gallery()->create(['path'  =>  $path, 'type' => 'gallery', 'object' => ['file' => $file]])->save();

        $websiteSetting->save();

        return response()->json(['status' => 'photo_album_uploaded']);
    }

    /**
     * Retrieve Slides
     * @param Team $team
     * @return JsonResponse
     */
    public function getSlides(Team $team)
    {

        return response()->json($team->websiteSetting->slider);
    }

    /**
     * Retrieve Photos
     * @param Team $team
     * @return JsonResponse
     */
    public function getPhotos(Team $team)
    {

        return response()->json(['photos' => $team->websiteSetting->gallery, 'enable_photo_album' => $team->websiteSetting->enable_gallery]);
    }

    /**
     * Delete and Unlink Image or Slide
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function deleteSlide(Request $request, $id)
    {
        $image = WebsiteGallery::find($id);

        if (App::environment('production') || App::environment('development')) {
            Storage::disk('s3')->delete($image->object['file']);
        } else {
            Storage::disk()->delete($image->object['file']);
        }

        $image->delete();
        return response()->json('image_deleted');
    }


    /**
     *
     * @param Team $team
     * @return JsonResponse
     */
    public function getRelatedTeamsWithSettings(Team $team)
    {

        if (count($team->attachedHotels)) {
            $teams_ids = $team->attachedHotels->pluck('id')->toArray();
            $teams_ids[] = $team->id;
            $categories =   FeaturedUnitCategoriesResource::collection(UnitCategory::withoutGlobalScopes()->with('units')->whereIn('team_id', $teams_ids)->where('show_in_website', 1)->where('status', 1)->whereNull('deleted_at')->get());
            // $categories = $team->attachedHotels->pluck('id')->filter()->map(function($team_id){ return FeaturedUnitCategoriesResource::collection(UnitCategory::withoutGlobalScopes()->where('team_id' , $team_id)->whereNull('deleted_at')->get());});
            $has_related_hotels = true;
        } else {
            $categories = FeaturedUnitCategoriesResource::collection(UnitCategory::withoutGlobalScopes()->where('team_id', $team->id)->whereHas('units')->where('show_in_website', 1)->whereNull('deleted_at')->get());
            $has_related_hotels = false;
        }


        $featured_unit_categories = $team->websiteSetting->featured_unit_categories ?? null;

        return response()->json(['categories' => $categories, 'featured_unit_categories' => $featured_unit_categories, 'has_related_hotels' => $has_related_hotels]);
    }

    public function storeFeaturedUnitCategories(Request $request,  Team $team)
    {
        DB::table('website_settings')->where('team_id', $team->id)
            ->update(['featured_unit_categories' => json_encode($request->get('data'))]);

        // FeaturedUnitCategoriesJob::dispatch($request->get('data'),$team->id);
        return response()->json(['status' => 'featured_unit_categories_updated']);
    }

    public function aboutUsUploadHandler(Request $request, Team $team)
    {

        $settings = $team->websiteSetting;
        // we need to safely delete the old image so that we manage storage capacity
        // and remove unused files or images
        if ($request->file('file')) {

            if ($settings->about_us_image) {
                if (App::environment('production') || App::environment('development')) {
                    Storage::disk('s3')->delete($settings->about_us_image);
                } else {
                    Storage::disk()->delete($settings->about_us_image);
                }
            }
        }
      

        

        if ($request->file('file')) {

            if (App::environment('production') || App::environment('development')) {

                // $image = $file = $request->file('file');
                // $imageName = time() . '.' . $image->getClientOriginalExtension();
                // Storage::disk('s3')->put($imageName, file_get_contents($image), 'public');
                $file = $request->file('file')->store('public/settings/' . auth()->user()->current_team_id . '/about_us');
                $file = Storage::disk(env('FILESYSTEM_DRIVER'))->url($file);
            } else {
                $file = $request->file('file')->store('public/settings/' . auth()->user()->current_team_id);
            }
            // receive file

        } else {
            $file = $settings->about_us_image;
        }


        $enable_about_us = (int) $request->get('enable_about_us');

        $about_us_title = ['ar' => $request->get('about_us_title_ar'), 'en' => $request->get('about_us_title_en')];
        $about_us_content = ['ar' => $request->get('about_us_content_ar'), 'en' => $request->get('about_us_content_en')];
        DB::table('website_settings')->where('team_id', $team->id)
            ->update(['enable_about_us' =>  $enable_about_us, 'about_us_image' => $file, 'about_us_title' => json_encode($about_us_title), 'about_us_content' => json_encode($about_us_content)]);

        return response()->json('success');
    }


    /**
     * Add new website page
     * @param Request $request
     * @return JsonResponse
     */
    public function addPage(Request $request)
    {


        $messages = [
            'slug.unique' => __('Slug is used before'),
            'slug.max' => __('Slug is too long')
        ];

        $slug_unique = Rule::unique('website_pages')->where(function ($query) {
            return $query->where('team_id', auth()->user()->current_team_id)->whereNull('deleted_at');
        });

        $validator = Validator::make($request->all(), [
            'slug' => [$slug_unique, 'max:250'],
        ], $messages);

        if ($validator->fails()) {
            return \response()->json(['status' => 'error', 'errors' => $validator->errors()]);
        }
        $page = new WebsitePage();
        $page->team_id = auth()->user()->current_team_id;
        $page->title = \request()->get('title');
        $page->content = \request()->get('content');
        $page->slug = \request()->get('slug');
        $page->status = \request()->get('status');
        $page->order = \request()->get('order');
        $page->save();

        return \response()->json(['status' => 'success']);
    }

    public function getPages()
    {

        return PagesResource::collection(WebsitePage::where('team_id', auth()->user()->current_team_id)->whereNull('deleted_at')->orderByDesc('id')->orderByDesc('order')->paginate(10));
    }

    public function getPageDetails($id)
    {

        return new PagesResource(WebsitePage::find($id));
    }

    public function editPage(Request $request)
    {


        $messages = [
            'slug.unique' => __('Slug is used before'),
            'slug.max' => __('Slug is too long')
        ];

        $slug_unique = Rule::unique('website_pages')->where(function ($query) {
            return $query->where('team_id', auth()->user()->current_team_id)->where('id', '!=', \request()->get('id'))->whereNull('deleted_at');
        });

        $validator = Validator::make($request->all(), [
            'slug' => [$slug_unique, 'max:250'],
        ], $messages);

        if ($validator->fails()) {
            return \response()->json(['status' => 'error', 'errors' => $validator->errors()]);
        }
        $page =  WebsitePage::find(\request()->get('id'));
        $page->team_id = auth()->user()->current_team_id;
        $page->title = \request()->get('title');
        $page->content = \request()->get('content');
        $page->slug = \request()->get('slug');
        $page->status = \request()->get('status');
        $page->order = \request()->get('order');
        $page->save();

        return \response()->json(['status' => 'success']);
    }

    public function deletePage($id)
    {

        WebsitePage::destroy($id);
        return \response()->json(Response::HTTP_OK);
    }

    public function getTeamInfo(){
        return \response()->json(Team::with('city','setting','owner')->find(auth()->user()->current_team_id));
    }


    public function introVideo(Request $request, Team $team)
    {
        $settings = $team->websiteSetting;
        // we need to safely delete the old image so that we manage storage capacity
        // and remove unused files or images
        if ($request->file('file')) {

            if ($settings->intro_background && $settings->intro_background_type === 'image') {
                if (App::environment('production') || App::environment('development')) {
                    Storage::disk('s3')->delete($settings->intro_background);
                } else {
                    Storage::disk()->delete($settings->intro_background);
                }
            }
        }

        if ($request->file('file')) {

            if (App::environment('production') || App::environment('development')) {

                $image = $file = $request->file('file');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('s3')->put($imageName, file_get_contents($image), 'public');
                $file = Storage::disk('s3')->url($imageName);
            } else {
                $image = $file = $request->file('file');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('public')->put($imageName, file_get_contents($image), 'public');
                $file = Storage::disk('public')->url($imageName);
                //                $file = $request->file('file')->store('/storage/public/settings/'. auth()->user()->current_team_id);
            }
            // receive file

        } else {
            $file = $settings->intro_background;
        }


        $enable_intro_video = (int) $request->get('enable_intro_video');
        $intro_text = ['ar' => $request->get('intro_text_ar'), 'en' => $request->get('intro_text_en')];


        DB::table('website_settings')->where('team_id', $team->id)
            ->update([
                'enable_intro_video' =>  $enable_intro_video,
                'intro_text' => json_encode($intro_text),
                'intro_video_url' => $request->get('intro_video_url'),
                'intro_background' => $file
            ]);

        return response()->json('success');
    }
}
