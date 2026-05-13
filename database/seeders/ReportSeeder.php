<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Report;
use App\ReportSchedule;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Create reports
        $reports = [
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Daily Occupancy Report', 'ar' => 'تقرير الت occupancy اليومي']),
                'report_type' => 'occupancy',
                'description' => json_encode([
                    'en' => 'Daily report showing occupancy rates, room statistics, and revenue',
                    'ar' => 'تقرير يومي يوضح معدلات الاشغال وإحصائيات الغرف والإيرادات'
                ]),
                'sql_query' => 'SELECT COUNT(*) as total_rooms, SUM(occupied) as occupied_rooms, AVG(occupancy_rate) as avg_occupancy FROM rooms WHERE created_at = CURDATE()',
                'chart_config' => json_encode([
                    'type' => 'line',
                    'xAxis' => 'date',
                    'yAxis' => ['occupancy_rate'],
                    'colors' => ['#28a745']
                ]),
                'columns' => json_encode([
                    ['key' => 'date', 'label' => ['en' => 'Date', 'ar' => 'التاريخ']],
                    ['key' => 'total_rooms', 'label' => ['en' => 'Total Rooms', 'ar' => 'إجمالي الغرف']],
                    ['key' => 'occupied_rooms', 'label' => ['en' => 'Occupied Rooms', 'ar' => 'الغرف المشغولة']],
                    ['key' => 'occupancy_rate', 'label' => ['en' => 'Occupancy Rate %', 'ar' => 'معدل الإشغال %']]
                ]),
                'filters' => json_encode([
                    'date_range' => true,
                    'room_type' => true,
                    'reservation_source' => false
                ]),
                'permissions' => json_encode(['view_reports', 'view_occupancy_reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Revenue Analysis Report', 'ar' => 'تقرير تحليل الإيرادات']),
                'report_type' => 'revenue',
                'description' => json_encode([
                    'en' => 'Detailed revenue breakdown by source, room type, and payment method',
                    'ar' => 'تحليل مفصل للإيرادات حسب المصدر ونوع الغرفة وطريقة الدفع'
                ]),
                'sql_query' => 'SELECT SUM(amount) as total_revenue, payment_method, DATE(created_at) as date FROM transactions WHERE created_at >= CURDATE() - INTERVAL 30 DAY GROUP BY payment_method, DATE(created_at)',
                'chart_config' => json_encode([
                    'type' => 'bar',
                    'xAxis' => 'date',
                    'yAxis' => ['total_revenue'],
                    'colors' => ['#007bff', '#28a745', '#ffc107']
                ]),
                'columns' => json_encode([
                    ['key' => 'date', 'label' => ['en' => 'Date', 'ar' => 'التاريخ']],
                    ['key' => 'total_revenue', 'label' => ['en' => 'Total Revenue', 'ar' => 'إجمالي الإيرادات']],
                    ['key' => 'payment_method', 'label' => ['en' => 'Payment Method', 'ar' => 'طريقة الدفع']],
                    ['key' => 'transaction_count', 'label' => ['en' => 'Transactions', 'ar' => 'المعاملات']]
                ]),
                'filters' => json_encode([
                    'date_range' => true,
                    'payment_method' => true,
                    'room_type' => true
                ]),
                'permissions' => json_encode(['view_reports', 'view_revenue_reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Guest Demographics Report', 'ar' => 'تقرير تعداد ضيوف']),
                'report_type' => 'demographics',
                'description' => json_encode([
                    'en' => 'Analysis of guest demographics by nationality, age group, and stay purpose',
                    'ar' => 'تحليل تعداد الضيوف حسب الجنسية وفئة العمر وغرض الإقامة'
                ]),
                'sql_query' => 'SELECT nationality, COUNT(*) as guest_count, AVG(stay_duration) as avg_stay FROM guests JOIN reservations ON guests.id = reservations.guest_id WHERE reservations.check_in >= CURDATE() - INTERVAL 90 DAY GROUP BY nationality',
                'chart_config' => json_encode([
                    'type' => 'pie',
                    'xAxis' => 'nationality',
                    'yAxis' => ['guest_count'],
                    'colors' => ['#17a2b8', '#6f42c1', '#fd7e14']
                ]),
                'columns' => json_encode([
                    ['key' => 'nationality', 'label' => ['en' => 'Nationality', 'ar' => 'الجنسية']],
                    ['key' => 'guest_count', 'label' => ['en' => 'Guest Count', 'ar' => 'عدد الضيوف']],
                    ['key' => 'avg_stay', 'label' => ['en' => 'Avg Stay (Days)', 'ar' => 'متوسط الإقامة (أيام)']],
                    ['key' => 'repeat_guests', 'label' => ['en' => 'Repeat Guests', 'ar' => 'الضيوف المتكررون']]
                ]),
                'filters' => json_encode([
                    'date_range' => true,
                    'nationality' => true,
                    'purpose_of_stay' => true
                ]),
                'permissions' => json_encode(['view_reports', 'view_demographic_reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Night Audit Summary', 'ar' => 'ملخص مراجعة الليل']),
                'report_type' => 'night_audit',
                'description' => json_encode([
                    'en' => 'Summary of night audit with occupancy, revenue, and operational metrics',
                    'ar' => 'ملخص مراجعة الليل مع الإشغال والإيرادات والمقاييس التشغيلية'
                ]),
                'sql_query' => 'SELECT business_date, total_rooms_sold, total_revenue, occupancy_rate, avg_daily_rate FROM night_audits WHERE created_at >= CURDATE() - INTERVAL 30 DAY ORDER BY business_date DESC',
                'chart_config' => json_encode([
                    'type' => 'area',
                    'xAxis' => 'business_date',
                    'yAxis' => ['total_revenue', 'occupancy_rate'],
                    'colors' => ['#28a745', '#007bff']
                ]),
                'columns' => json_encode([
                    ['key' => 'business_date', 'label' => ['en' => 'Business Date', 'ar' => 'تاريخ العمل']],
                    ['key' => 'total_rooms_sold', 'label' => ['en' => 'Rooms Sold', 'ar' => 'الغرف المباعة']],
                    ['key' => 'total_revenue', 'label' => ['en' => 'Total Revenue', 'ar' => 'إجمالي الإيرادات']],
                    ['key' => 'occupancy_rate', 'label' => ['en' => 'Occupancy %', 'ar' => 'نسبة الإشغال %']],
                    ['key' => 'avg_daily_rate', 'label' => ['en' => 'ADR (SAR)', 'ar' => 'متوسط السعر اليومي (ريال)']]
                ]),
                'filters' => json_encode([
                    'date_range' => true,
                    'metrics' => true,
                    'comparison_period' => true
                ]),
                'permissions' => json_encode(['view_reports', 'view_night_audit_reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Cashier Shift Report', 'ar' => 'تقرير وردية الصندوق']),
                'report_type' => 'cashier_shift',
                'description' => json_encode([
                    'en' => 'Detailed cashier shift report with cash flow and transaction summaries',
                    'ar' => 'تقرير مفصل لوردية الصندوق مع ملخص التدفق النقدي والمعاملات'
                ]),
                'sql_query' => 'SELECT shift_date, user_id, opening_balance, closing_balance, cash_sales, card_sales, variance FROM cashier_shifts WHERE created_at >= CURDATE() - INTERVAL 30 DAY ORDER BY shift_date DESC',
                'chart_config' => json_encode([
                    'type' => 'column',
                    'xAxis' => 'shift_date',
                    'yAxis' => ['cash_sales', 'card_sales'],
                    'colors' => ['#28a745', '#007bff']
                ]),
                'columns' => json_encode([
                    ['key' => 'shift_date', 'label' => ['en' => 'Shift Date', 'ar' => 'تاريخ الوردية']],
                    ['key' => 'cashier_name', 'label' => ['en' => 'Cashier', 'ar' => 'الصندوق']],
                    ['key' => 'opening_balance', 'label' => ['en' => 'Opening Balance', 'ar' => 'الرصيد الافتتاحي']],
                    ['key' => 'closing_balance', 'label' => ['en' => 'Closing Balance', 'ar' => 'الرصيد الختامي']],
                    ['key' => 'cash_sales', 'label' => ['en' => 'Cash Sales', 'ar' => 'مبيعات نقدية']],
                    ['key' => 'card_sales', 'label' => ['en' => 'Card Sales', 'ar' => 'مبيعات بطاقة']],
                    ['key' => 'variance', 'label' => ['en' => 'Variance', 'ar' => 'الاختلاف']]
                ]),
                'filters' => json_encode([
                    'date_range' => true,
                    'cashier' => true,
                    'shift_status' => true
                ]),
                'permissions' => json_encode(['view_reports', 'view_cashier_reports']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($reports as $report) {
            Report::updateOrCreate(
                ['name->en' => json_decode($report['name'], true)['en'], 'team_id' => $team->id],
                $report
            );
        }

        // Create report schedules
        $reportSchedules = [
            [
                'team_id' => $team->id,
                'report_id' => 1, // Daily Occupancy Report
                'schedule_type' => 'daily',
                'schedule_time' => '06:00', // 6 AM
                'recipients' => json_encode(['manager@demo.hotel', 'ops@demo.hotel']),
                'is_active' => true,
                'last_run_at' => Carbon::yesterday()->setTime(6, 0),
                'next_run_at' => Carbon::today()->setTime(6, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'report_id' => 4, // Night Audit Summary
                'schedule_type' => 'daily',
                'schedule_time' => '05:30', // 5:30 AM (after night audit completes)
                'recipients' => json_encode(['gm@demo.hotel', 'finance@demo.hotel']),
                'is_active' => true,
                'last_run_at' => Carbon::yesterday()->setTime(5, 30),
                'next_run_at' => Carbon::today()->setTime(5, 30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'report_id' => 2, // Revenue Analysis Report
                'schedule_type' => 'weekly',
                'schedule_time' => '08:00', // 8 AM Monday
                'day_of_week' => 'monday',
                'recipients' => json_encode(['gm@demo.hotel', 'finance@demo.hotel', 'owner@demo.hotel']),
                'is_active' => true,
                'last_run_at' => Carbon::now()->subWeek()->startOfWeek()->setTime(8, 0),
                'next_run_at' => Carbon::now()->startOfWeek()->setTime(8, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'report_id' => 3, // Guest Demographics Report
                'schedule_type' => 'monthly',
                'schedule_time' => '09:00', // 9 AM 1st of month
                'day_of_month' => 1,
                'recipients' => json_encode(['gm@demo.hotel', 'marketing@demo.hotel', 'owner@demo.hotel']),
                'is_active' => true,
                'last_run_at' => Carbon::now()->subMonth()->startOfMonth()->setTime(9, 0),
                'next_run_at' => Carbon::now()->startOfMonth()->setTime(9, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($reportSchedules as $schedule) {
            ReportSchedule::updateOrCreate(
                ['report_id' => $schedule['report_id'], 'team_id' => $team->id],
                $schedule
            );
        }
    }
}