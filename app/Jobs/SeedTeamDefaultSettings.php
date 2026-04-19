<?php

namespace App\Jobs;

use App\Highlight;
use App\Mail\Owner\RegistrationWelcomeEmail;
use App\Setting;
use App\Source;
use App\Team;
use App\TeamCounter;
use App\Term;
use App\Unit;
use App\UnitCategory;
use App\UnitGeneralFeature;
use App\UnitSpecialFeature;
use App\WebsiteSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Spark\Subscription;
use Laravel\Spark\TeamSubscription;
use Illuminate\Support\Facades\Cache;

class SeedTeamDefaultSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $team;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $unit_category = $this->createUnitCategory();

        $unit = $this->createUnit($unit_category->id);
        $features = $this->createFeatures();
        $settings = $this->createSettings();
        $terms = $this->createTerms();
        $sources = $this->createSources();
        $this->createHighlights();
        TeamCounter::create(['team_id' => $this->team->id]);
        $this->createFreeSubscription();

        $this->defaultWebsiteData($this->team);

    }

    /**
     * create Unit Category
     *
     * @return [type] [description]
     */
    protected function createUnitCategory()
    {
        $unit_category_t = [
           'en' => 'single room',
           'ar' => 'غرفة مفردة'
        ];

        $unit_category = new UnitCategory;
        $unit_category->setTranslations('name', $unit_category_t);
        $unit_category->sunday_day_price = 100;
        $unit_category->monday_day_price = 100;
        $unit_category->tuesday_day_price = 100;
        $unit_category->wednesday_day_price = 100;
        $unit_category->thursday_day_price = 100;
        $unit_category->friday_day_price = 100;
        $unit_category->saturday_day_price = 100;
        $unit_category->status = 1;
        $unit_category->type_id = 1;
        $unit_category->month_price = 2000;
        $unit_category->hour_price = 50;
        $unit_category->team_id = $this->team->id;
        $unit_category->special_features = 'array';
        $unit_category->general_features = 'array';
        $unit_category->save();
        return $unit_category;
    }

    protected function createUnit($unit_category_id)
    {
        $unit = new Unit;
        $unit->unit_number = 101;
        $unit->status = 1;
        $unit->unit_category_id = $unit_category_id;
        $unit->team_id = $this->team->id;
        $unit->save();
        return $unit;
    }

    /**
     * create Settings
     *
     * @return [type] [description]
     */
    protected function createSettings()
    {
        $setting = new Setting;
        $setting->team_id = $this->team->id;
        $setting->value = '13:00';
        $setting->key = 'day_start';
        $setting->save();

        $setting = new Setting;
        $setting->team_id = $this->team->id;
        $setting->value = '12:00';
        $setting->key = 'day_end';
        $setting->save();


        $setting = new Setting;
        $setting->team_id = $this->team->id;
        $setting->value = 0;
        $setting->key = 'time_12hr';
        $setting->save();


        $generalSettings = [
            'day_start' => '13:00',
            'day_end' => '12:00',
            'time_12hr' => 0
        ];

        Cache::add($this->team->id , $generalSettings);


    }

    /**
     * create Terms
     *
     * @return [type] [description]
     */
    protected function createTerms()
    {
        $terms = [
            [
                'title' => [
                    'en' => 'Rent redemption',
                    'ar' => 'مرتجع إيجار'
                ],
                'type' => 1
            ],
            [
                'title' => [
                    'en' => 'Salaries',
                    'ar' => 'رواتب'
                ],
                'type' => 1
            ],
            [
                'title' => [
                    'en' => 'maintenance',
                    'ar' => 'صيانة'
                ],
                'type' => 1
            ],
            [
                'title' => [
                    'en' => 'General expenses',
                    'ar' => 'مصروفات عامة'
                ],
                'type' => 1
            ],
            [
                'title' => [
                    'en' => 'Transfer from the safe to management',
                    'ar' => 'تحويل من الصندوق الى الادارة'
                ],
                'type' => 1,
                'deleteable' => 0
            ],
            [
                'title' => [
                    'en' => 'Insurance Retrieval',
                    'ar' => 'استرجاع تامين'
                ],
                'type' => 1,
                'deleteable' => 0
            ],
            [
                'title' => [
                    'en' => 'Retrieve the deposit',
                    'ar' => 'استرجاع العربون'
                ],
                'type' => 1
            ],
            [
                'title' => [
                    'en' => 'General receipts',
                    'ar' => 'مقبوضات عامة'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Rent value',
                    'ar' => 'قيمة إيجار'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Bills',
                    'ar' => 'فواتير'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Transfer from management to the safe',
                    'ar' => 'تحويل من الادارة الى الصندوق'
                ],
                'type' => 2,
                'deleteable' => 0
            ],
            [
                'title' => [
                    'en' => 'Services ',
                    'ar' => 'خدمات'
                ],
                'type' => 2,
                'deleteable' => 0
            ],
            [
                'title' => [
                    'en' => 'insurance',
                    'ar' => 'تامين'
                ],
                'type' => 2,
                'deleteable' => 0
            ],
            [
                'title' => [
                    'en' => 'Retainer',
                    'ar' => 'عربون'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Not Applicable',
                    'ar' => 'لا ينطبق'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Other',
                    'ar' => 'آخر'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Laundry',
                    'ar' => 'مغسلة'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Wifi - Internet',
                    'ar' => 'واي فاي - الإنترنت'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Car Parking',
                    'ar' => 'مواقف السيارات'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Food',
                    'ar' => 'طعام'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Food & Beverages',
                    'ar' => 'الأغذية والمشروبات'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Beverages',
                    'ar' => 'مشروبات'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Cold Drinks',
                    'ar' => 'المشروبات الباردة'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Hot Drinks',
                    'ar' => 'المشروبات الساخنة'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Breakfast',
                    'ar' => 'الإفطار'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Lunch',
                    'ar' => 'غداء'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Dinner',
                    'ar' => 'عشاء'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Bakery & Cakes',
                    'ar' => 'مخبز و كعك'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Swimming pool',
                    'ar' => 'حمام سباحة'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Gym',
                    'ar' => 'الصالة الرياضية'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'SPA & Beauty Services',
                    'ar' => 'سبا و خدمات الجمال'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Pick & Drop (Transport Services)',
                    'ar' => 'اختيار وإسقاط (خدمات النقل)'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Minibar',
                    'ar' => 'ميني بار'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Cable - TV',
                    'ar' => 'كابل - تلفزيون'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Extra Bed',
                    'ar' => 'سرير إضافي'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Hairdresser',
                    'ar' => 'تصفيف الشعر'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Shopping',
                    'ar' => 'التسوق'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Organized Tours Services',
                    'ar' => 'خدمات الجولات السياحية المنظمة'
                ],
                'type' => 2
            ],
            [
                'title' => [
                    'en' => 'Tour Guide Services',
                    'ar' => 'خدمات الدليل السياحي'
                ],
                'type' => 2
            ],

            [
                'title' => [
                    'en' => 'Fulfill Promissory',
                    'ar' => 'تحصيل كمبيالة'
                ],
                'type' => 2,
                'deleteable' => 0
            ],
        ];
        foreach ($terms as $data) {
            $term = new Term;
            $term->setTranslations('name',$data['title']);
            $term->type = $data['type'];
            $term->deleteable = isset($data['deleteable']) ? $data['deleteable']  : 1 ;
            $term->team_id = $this->team->id;
            $term->save();
        }
    }

    /**
     * create Sources
     *
     * @return [type] [description]
     */
    protected function createSources()
    {
        $sources = [
            [
                'en' => 'Recepion',
                'ar' => 'استقبال'
            ],
            [
                'en' => 'website',
                'ar' => 'الموقع الإلكتروني'
            ],
            [
                'en' => 'booking',
                'ar' => 'بوكينج'
            ],
            [
                'en' => 'almosafer',
                'ar' => 'المسافر'
            ],
            [
                'en' => 'Expedia',
                'ar' => 'اكسبيديا'
            ],
            [
                'en' => 'Social Media',
                'ar' => 'مواقع التواصل الإجتماعي'
            ],
            [
                'en' => 'oyo',
                'ar' => 'اويو'
            ],
            [
                'en' => 'Other',
                'ar' => 'اخرى'
            ],
        ];
        foreach ($sources as $key => $data) {
            $term = new Source;
            $term->setTranslations('name', $data);
            $term->team_id = $this->team->id;
            $term->order = $key + 1;
            $term->save();
        }
    }

    protected function createHighlights()
    {
        $highlights = [
            [
                'name'  =>  [
                    'en' => 'Special',
                    'ar' => 'مميز'
                ],
                'color' =>  '#3F51B5',
            ],
            [
                'name'  =>  [
                    'en' => 'VIP',
                    'ar' => 'خاص'
                ],
                'color' =>  '#FFEB3B',
            ],
            [
                'name'  =>  [
                    'en' => 'unwanted',
                    'ar' => 'غير مرغوب فيه'
                ],
                'color' =>  '#E91E63',
            ],
        ];

        foreach ($highlights as $key => $data) {
            $term = new Highlight;
            $term->setTranslations('name', $data['name']);
            $term->team_id = $this->team->id;
            $term->order = $key + 1;
            $term->color = $data['color'];
            $term->save();
        }
    }

    /**
     * create Features
     *
     * @return [type] [description]
     */
    protected function createFeatures()
    {
        $unit_s_fs = [
            [
                'en' => 'AC',
                'ar' => 'تكييف'
            ],
            [
                'en' => 'King Sized Bed',
                'ar' => 'سرير حجم كنج'
            ],
            [
                'en' => 'TV',
                'ar' => 'تلفزيون'
            ],
        ];
        foreach ($unit_s_fs as $item) {
            $unit_s_f = new UnitSpecialFeature;
            $unit_s_f->setTranslations('name', $item);
            $unit_s_f->status = true;
            $unit_s_f->team_id = $this->team->id;
            $unit_s_f->save();
        }


        $unit_g_fs = [
            [
                'en' => 'Free Wifi',
                'ar' => 'انترنت مجاني'
            ],
            [
                'en' => 'Card Payment',
                'ar' => 'الدفع بالبطاقة'
            ],
            [
                'en' => 'CCTV Cameras',
                'ar' => 'كاميرات مراقبة'
            ],
            [
                'en' => 'Wheelchair Accessible',
                'ar' => 'مهيئ لذوي الاحتياجات الخاصة'
            ],
            [
                'en' => 'Daily Housekeeping',
                'ar' => 'خدمة تنظيف الغرف اليومية'
            ],
        ];
        foreach ($unit_g_fs as $item) {
            $unit_g_f = new UnitGeneralFeature;
            $unit_g_f->setTranslations('name', $item);
            $unit_g_f->status = true;
            $unit_g_f->team_id = $this->team->id;
            $unit_g_f->save();
        }
    }

    protected function createFreeSubscription()
    {
        $end = Carbon::now()->addDays(15);

        $s = new TeamSubscription();

        $this->team->current_billing_plan = 'trial';
        $this->team->trial_ends_at = $end;
        $this->team->ends_at = $end;

        $s->team_id = $this->team->id;
        $s->name = 'trial';
        $s->stripe_plan = 'trial';
        $s->quantity = 1;
        $s->trial_ends_at = $end;
        $s->ends_at = $end;

        $s->save();
        $this->team->save();
    }


    /**
     * Update website default settings or create new settings
     * @param $team
     */
    private function defaultWebsiteData($team){

        $matchThese = array('team_id' => $team->id);
        $websiteSetting = WebsiteSetting::updateOrCreate(
            $matchThese,
            [
                "team_id" => $team->id,

                // payment options settings
                "credit_card" => 0,
                "bank_transfer" => 1,
                "bank_account_info" => [
                    'ar'    =>  '<p>يتم تأكيد الحجز بعد تحويل قيمة الحجز وإرسال الإيصال</p>
                                 <p>معلومات الحساب البنكي</p>
                                 <p class="ql-align-right ql-direction-rtl">IBAN: SA0000000000000000000000</p>',

                    'en'    =>  '<p>Reservation is confirmed after transfer</p>
                                 <p>Bank account information</p>
                                 <p>IBAN: SA0000000000000000000000</p>',
                ],

                // logo and main colors
                "logo" => "/default/logo_and_icon/logo.png",
                "favicon" => "/default/logo_and_icon/favicon.png",
                "basic_text_color" => "#494949",
                "sub_text_color" => "#696969",
                "hover_text_color" => "#6A256E",
                "button_background_color" => "#6A256E",
                "button_color" => "#FFFFFF",
                "button_hover_click_color" => "#973C9C",

                // gallery
                "enable_gallery" => 1,

                // social
                "enable_social" => 1,
                "social_instagram_url" => "https://www.instagram.com/instagram",
                "social_twitter_url" => "https://twitter.com/twitter",
                "social_snapchat_url" => "https://www.snapchat.com/add/snapchat",
                "social_youtube_url" => "https://www.youtube.com/youtube",


                // main phrases
                "title_first_text" => [
                    "ar" => "أفضل العروض الحصرية",
                    "en" => "Best exclusive offers"
                ],

                "title_second_text" => [
                    "ar" => "استمتع بمزايا إضافية عند الحجز عبر موقعنا الإلكتروني",
                    "en" => "Enjoy additional benefits when booking through our website"
                ],

                "search_box_top_first_text" => [
                    "ar" => "ماهي وجهتك",
                    "en" => "Select City"
                ],

                "search_box_top_second_text" => [
                    "ar" => "حدد المكان",
                    "en" => "Select hotel"
                ],

                "search_box_top_third_text" => [
                    "ar" => "حدد الموعد المناسب",
                    "en" => "Select the appropriate date"
                ],

                "search_button_text" => [
                    "ar" => "بحث",
                    "en" => "Search"
                ],

                "rights" => [
                    "ar" => "جميع الحقوق محفوظة ©",
                    "en" => "All Rights Reserved ©"
                ],

                "special_features_block_text" => [
                    "ar" => "المميزات الخاصة",
                    "en" => "special Features"
                ],

                "general_features_block_text" => [
                    "ar" => "المميزات العامة",
                    "en" => "general Features"
                ],

                // cancellation policy
                "cancellation_policy" => [
                    "ar" => "",
                    "en" => ""
                ],


                // contact infromation
                "contact_email" => $team->owner->email,
                "contact_phone" => $team->owner->phone,
                "contact_note" => [
                    "ar" => "",
                    "en" => ""
                ],
                "contact_map_url" => "",
                "contact_address_description" => [
                    "ar" => "",
                    "en" => ""
                ],

                // about website
                "enable_about_us" => 1,
                "about_us_image" => "/default/about/about.jpg",
                "about_us_title" => [
                    "ar" => "مرحبا بكم ..",
                    "en" => "Welcom to Hotel Name"
                ],
                "about_us_content" => [
                    "ar" => '<p>استمتع بالإقامة في غرف من تصميم أفضل المصممين في وسط مدينة الرياض.</p>
                            <p>يقع فندقنا في قلب المدينة على طريق الملك عبد العزيز، ويوفر للمسافرين بغرض العمل سهولة القرب من الوزارات الرئيسية والمناطق الصناعية والشركات العالمية. سيعثر الضيوف على مجموعة من معالم الجذب التاريخية والثقافية التي يمكن الوصول إليها بسهولة عبر خيارات النقل المحلية وسيارات الأجرة بالعداد وخدمات أوبر بالمدينة. يُعد الفندق مثاليًا للضيوف الدوليين، حيث يقع على بُعد 40 كيلومترًا فقط من مطار الملك خالد الدولي (RUH).</p>' ,

                    "en" => '<p>Stay in designer rooms in Riyadh\'s city center .</p>
                             <p> Located in the heart of the city on King Abdulaziz Road, our hotel offers business travelers easy proximity to main ministries, industrial zones and international companies. Guests will find an array of historic and cultural attractions that are easily reached via the city\'s local transport options, metered taxis and Uber services. Perfect for international guests, the King Khalid International Airport (RUH) is 40 kilometers from the hotel.</p>'
                ]
            ]
        );

    }
}
