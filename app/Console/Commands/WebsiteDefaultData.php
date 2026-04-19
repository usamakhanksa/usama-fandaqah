<?php

namespace App\Console\Commands;

use App\WebsiteSetting;
use Illuminate\Console\Command;

use App\Team;

class WebsiteDefaultData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:website-default-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will seed all the default website data to current registered tenants';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @rememmber to remove the take and make it for all teams
     * @return mixed
     */
    public function handle()
    {
        $teams = Team::whereNull('deleted_at')->orderBy('id' , 'desc')->get();

        foreach ($teams as $team){
            $this->defaultWebsiteData($team);
        }

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
                "contact_email" => $team->owner ? $team->owner->email : '',
                "contact_phone" => $team->owner ? $team->owner->phone : '',
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
