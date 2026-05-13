<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardWidgetSeeder extends Seeder
{
    public function run()
    {
        $widgets = [
            ['widget_key' => 'occupancy_rate', 'label_en' => 'Occupancy Rate', 'label_ar' => 'نسبة الإشغال', 'icon' => 'percentage'],
            ['widget_key' => 'available_rooms', 'label_en' => 'Available Rooms', 'label_ar' => 'الغرف المتاحة', 'icon' => 'door-open'],
            ['widget_key' => 'arrivals_today', 'label_en' => 'Arrivals Today', 'label_ar' => 'وصول اليوم', 'icon' => 'plane-arrival'],
            ['widget_key' => 'departures_today', 'label_en' => 'Departures Today', 'label_ar' => 'مغادرة اليوم', 'icon' => 'plane-departure'],
            ['widget_key' => 'revenue_today', 'label_en' => 'Revenue Today', 'label_ar' => 'دخل اليوم', 'icon' => 'money-bill-wave'],
            ['widget_key' => 'housekeeping_status', 'label_en' => 'Housekeeping Status', 'label_ar' => 'حالة التنظيف', 'icon' => 'broom'],
            ['widget_key' => 'zatca_queue', 'label_en' => 'ZATCA Pending', 'label_ar' => 'بانتظار هيئة الزكاة', 'icon' => 'file-invoice-dollar'],
            ['widget_key' => 'failed_integrations', 'label_en' => 'Failed Integrations', 'label_ar' => 'تكاملات فاشلة', 'icon' => 'exclamation-triangle'],
        ];

        foreach ($widgets as $widget) {
            DB::table('dashboard_widgets')->updateOrInsert(['widget_key' => $widget['widget_key']], $widget);
        }
    }
}
