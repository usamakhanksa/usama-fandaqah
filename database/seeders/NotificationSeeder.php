<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\NotificationTemplate;
use App\NotificationTrigger;

class NotificationSeeder extends Seeder
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

        // Create notification templates
        $notificationTemplates = [
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Reservation Confirmation', 'ar' => 'تأكيد الحجز']),
                'template_key' => 'reservation_confirmation',
                'subject' => json_encode([
                    'en' => 'Your Reservation at Fandaqah Demo Hotel',
                    'ar' => 'حجزك في فندق ديمو فنداق'
                ]),
                'body' => json_encode([
                    'en' => 'Dear {{guest_name}},<br><br>Your reservation ({{reservation_code}}) has been confirmed.<br><br>Check-in: {{check_in_date}}<br>Check-out: {{check_out_date}}<br>Room: {{room_number}}<br><br>We look forward to welcoming you!<br><br>Best regards,<br>Fandaqah Demo Hotel Team',
                    'ar' => 'عزيزي {{guest_name}}،<br><br>تم تأكيد حجزك ({{reservation_code}}).<br><br>تاريخ الدخول: {{check_in_date}}<br>تاريخ الخروج: {{check_out_date}}<br>الغرفة: {{room_number}}<br><br>نتطلع لاستقبالك!<br><br>مع أطيب التحيات،<br>فريق فندق ديمو فنداق'
                ]),
                'sms_body' => json_encode([
                    'en' => 'Hi {{guest_name}}, your reservation {{reservation_code}} is confirmed. Check-in: {{check_in_date}}, Check-out: {{check_out_date}}. Welcome to Fandaqah Demo Hotel!',
                    'ar' => 'مرحباً {{guest_name}}، تم تأكيد حجزك {{reservation_code}}. تاريخ الدخول: {{check_in_date}}، تاريخ الخروج: {{check_out_date}}. نرحب بك في فندق ديمو فنداق!'
                ]),
                'channel_email' => true,
                'channel_sms' => true,
                'channel_push' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Check-in Reminder', 'ar' => 'تذكير بالدخول']),
                'template_key' => 'checkin_reminder',
                'subject' => json_encode([
                    'en' => 'Arrival Reminder for Your Stay',
                    'ar' => 'تذكير بالوصول لإقامتك'
                ]),
                'body' => json_encode([
                    'en' => 'Dear {{guest_name}},<br><br>This is a friendly reminder that your stay begins tomorrow ({{check_in_date}}).<br><br>Check-in time is 3:00 PM. If you expect to arrive earlier, please contact our front desk.<br><br>Thank you for choosing Fandaqah Demo Hotel.<br><br>Best regards,<br>Fandaqah Demo Hotel Team',
                    'ar' => 'عزيزي {{guest_name}}،<br><br>هذا تذكير ودي ببدء إقامتك غدًا ({{check_in_date}}).<br><br>وقت تسجيل الدخول هو 3:00 مساءً. إذا كنت تتوقع الوصول مبكرًا، يرجى الاتصال بمكتب الاستقبال لدينا.<br><br>شكرًا لاختيارك فندق ديمو فنداق.<br><br>مع أطيب التحيات،<br>فريق فندق ديمو فنداق'
                ]),
                'sms_body' => json_encode([
                    'en' => 'Hi {{guest_name}}, your stay begins tomorrow ({{check_in_date}}). Check-in time is 3:00 PM. Welcome to Fandaqah Demo Hotel!',
                    'ar' => 'مرحباً {{guest_name}}، تبدأ إقامتك غدًا ({{check_in_date}}). وقت تسجيل الدخول هو 3:00 مساءً. نرحب بك في فندق ديمو فنداق!'
                ]),
                'channel_email' => true,
                'channel_sms' => true,
                'channel_push' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Check-out Reminder', 'ar' => 'تذكير بالخروج']),
                'template_key' => 'checkout_reminder',
                'subject' => json_encode([
                    'en' => 'Check-out Reminder for Today',
                    'ar' => 'تذكير بموعد الخروج اليوم'
                ]),
                'body' => json_encode([
                    'en' => 'Dear {{guest_name}},<br><br>This is a reminder that your stay ends today ({{check_out_date}}).<br><br>Check-out time is 12:00 PM. If you need a late checkout, please contact our front desk.<br><br>We hope you enjoyed your stay at Fandaqah Demo Hotel.<br><br>Best regards,<br>Fandaqah Demo Hotel Team',
                    'ar' => 'عزيزي {{guest_name}}،<br><br>هذا تذكير بأن إقامتك تنتهي اليوم ({{check_out_date}}).<br><br>وقت تسجيل الخروج هو 12:00 ظهرًا. إذا كنت بحاجة إلى تمديد وقت الخروج، يرجى الاتصال بمكتب الاستقبال لدينا.<br><br>نأمل أن تكون قد استمتعت بإقامتك في فندق ديمو فنداق.<br><br>مع أطيب التحيات،<br>فريق فندق ديمو فنداق'
                ]),
                'sms_body' => json_encode([
                    'en' => 'Hi {{guest_name}}, your stay ends today ({{check_out_date}}). Check-out time is 12:00 PM. Thank you for staying at Fandaqah Demo Hotel!',
                    'ar' => 'مرحباً {{guest_name}}، تنتهي إقامتك اليوم ({{check_out_date}}). وقت تسجيل الخروج هو 12:00 ظهرًا. شكرًا لاختيارك فندق ديمو فنداق!'
                ]),
                'channel_email' => true,
                'channel_sms' => true,
                'channel_push' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Welcome Message', 'ar' => 'رسالة الترحيب']),
                'template_key' => 'welcome_message',
                'subject' => json_encode([
                    'en' => 'Welcome to Fandaqah Demo Hotel',
                    'ar' => 'مرحباً بك في فندق ديمو فنداق'
                ]),
                'body' => json_encode([
                    'en' => 'Dear {{guest_name}},<br><br>Welcome to Fandaqah Demo Hotel! We are delighted to have you as our guest.<br><br>During your stay, feel free to reach out to our 24/7 front desk for any assistance. Don\'t hesitate to explore our amenities including our restaurant, spa, and fitness center.<br><br>Enjoy your stay!<br><br>Best regards,<br>Fandaqah Demo Hotel Team',
                    'ar' => 'عزيزي {{guest_name}}،<br><br>مرحباً بك في فندق ديمو فنداق! نحن سعيدون بوجودك كضيف لدينا.<br><br>أثناء إقامتك، لا تتردد في التواصل مع مكتب الاستقبال لدينا على مدار الساعة للحصول على أي مساعدة. لا تتردد في استكشاف وسائل الراحة لدينا بما في ذلك مطعمنا وسبا ومركز اللياقة البدنية.<br><br>استمتع بإقامتك!<br><br>مع أطيب التحيات،<br>فريق فندق ديمو فنداق'
                ]),
                'sms_body' => json_encode([
                    'en' => 'Welcome {{guest_name}}! Enjoy your stay at Fandaqah Demo Hotel. For assistance, contact our 24/7 front desk. Have a great time!',
                    'ar' => 'مرحباً {{guest_name}}! استمتع بإقامتك في فندق ديمو فنداق. للمساعدة، اتصل بمكتب الاستقبال على مدار الساعة. نتمنى لك وقتًا رائعًا!'
                ]),
                'channel_email' => true,
                'channel_sms' => true,
                'channel_push' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Service Request Confirmation', 'ar' => 'تأكيد طلب الخدمة']),
                'template_key' => 'service_request_confirmation',
                'subject' => json_encode([
                    'en' => 'Service Request Confirmation',
                    'ar' => 'تأكيد طلب الخدمة'
                ]),
                'body' => json_encode([
                    'en' => 'Dear {{guest_name}},<br><br>Your service request ({{service_name}}) has been received and will be processed shortly.<br><br>Expected completion: {{estimated_completion}}<br>Reference Number: {{reference_number}}<br><br>If you have any questions, please contact our guest services team.<br><br>Best regards,<br>Fandaqah Demo Hotel Team',
                    'ar' => 'عزيزي {{guest_name}}،<br><br>تم استلام طلب خدمتك ({{service_name}}) وسيتم معالجته قريبًا.<br><br>الانتهاء المتوقع: {{estimated_completion}}<br>رقم المرجع: {{reference_number}}<br><br>إذا كانت لديك أي أسئلة، يرجى الاتصال بفريق خدمات الضيوف لدينا.<br><br>مع أطيب التحيات،<br>فريق فندق ديمو فنداق'
                ]),
                'sms_body' => json_encode([
                    'en' => 'Hi {{guest_name}}, your service request ({{service_name}}) has been received. Expected completion: {{estimated_completion}}. Ref: {{reference_number}}',
                    'ar' => 'مرحباً {{guest_name}}، تم استلام طلب خدمتك ({{service_name}}). الانتهاء المتوقع: {{estimated_completion}}. المرجع: {{reference_number}}'
                ]),
                'channel_email' => true,
                'channel_sms' => true,
                'channel_push' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($notificationTemplates as $template) {
            NotificationTemplate::updateOrCreate(
                ['template_key' => $template['template_key'], 'team_id' => $team->id],
                $template
            );
        }

        // Create notification triggers
        $notificationTriggers = [
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Upon Reservation Confirmation', 'ar' => 'عند تأكيد الحجز']),
                'event' => 'reservation.confirmed',
                'template_key' => 'reservation_confirmation',
                'conditions' => json_encode([
                    'requires_payment' => false,
                    'minimum_advance_days' => 0
                ]),
                'delay_minutes' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => '24 Hours Before Check-in', 'ar' => 'قبل 24 ساعة من وقت الدخول']),
                'event' => 'reservation.before_checkin',
                'template_key' => 'checkin_reminder',
                'conditions' => json_encode([
                    'reservation_status' => 'confirmed',
                    'guest_has_email' => true
                ]),
                'delay_minutes' => -1440, // 24 hours before
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'On Day of Check-out', 'ar' => 'في يوم الخروج']),
                'event' => 'reservation.checkout_day',
                'template_key' => 'checkout_reminder',
                'conditions' => json_encode([
                    'reservation_status' => 'checked_in',
                    'days_until_checkout' => 0
                ]),
                'delay_minutes' => 480, // Around 8 AM on checkout day
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Upon Guest Arrival', 'ar' => 'عند وصول الضيف']),
                'event' => 'reservation.checked_in',
                'template_key' => 'welcome_message',
                'conditions' => json_encode([
                    'first_night_only' => true
                ]),
                'delay_minutes' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => json_encode(['en' => 'Upon Service Request', 'ar' => 'عند طلب الخدمة']),
                'event' => 'service.requested',
                'template_key' => 'service_request_confirmation',
                'conditions' => json_encode([
                    'service_priority' => ['high', 'normal']
                ]),
                'delay_minutes' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($notificationTriggers as $trigger) {
            NotificationTrigger::updateOrCreate(
                ['event' => $trigger['event'], 'team_id' => $team->id],
                $trigger
            );
        }
    }
}