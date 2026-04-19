<?php

namespace App\Console\Commands;

use App\Team;
use App\Term;
use Illuminate\Console\Command;

class FlushSCTHTerm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush:terms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     *
     * @return mixed
     */
    public function handle()
    {
        $terms = [
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
            ]
        ];

        $teams = Team::all();
        /** @var Team $team */
        foreach ($teams as $team) {
            foreach ($terms as $data) {
                $term = new Term;
                $term->setTranslations('name',$data['title']);
                $term->type = $data['type'];
                $term->deleteable = isset($data['deleteable']) ? $data['deleteable']  : 1 ;
                $term->team_id = $team->id;
                $term->save();
            }
        }
    }
}
