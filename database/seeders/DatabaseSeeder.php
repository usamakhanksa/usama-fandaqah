<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder {
  public function run(): void {
    $roles=['super-admin','manager','receptionist','accountant','service-staff'];
    foreach($roles as $r){DB::table('roles')->insert(['name'=>ucwords(str_replace('-',' ',$r)),'slug'=>$r,'created_at'=>now(),'updated_at'=>now()]);}
    DB::table('users')->insert(['role_id'=>1,'name'=>'Aya Ahmed Abdullah','email'=>'aya@hotel.test','password'=>Hash::make('password'),'avatar'=>'/assets/avatars/admin.svg','last_seen_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
    for($i=1;$i<=4;$i++){DB::table('room_types')->insert(['name'=>"Type $i",'base_price'=>rand(80,350),'created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=55;$i++){DB::table('rooms')->insert(['room_type_id'=>rand(1,4),'number'=>str_pad((string)$i,3,'0',STR_PAD_LEFT),'floor'=>rand(1,5),'status'=>['available','occupied','maintenance'][array_rand([0,1,2])],'created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=30;$i++){DB::table('units')->insert(['name'=>"Unit $i",'number'=>'U'.str_pad((string)$i,3,'0',STR_PAD_LEFT),'status'=>'active','created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=120;$i++){DB::table('guests')->insert(['name'=>fake()->name(),'email'=>fake()->safeEmail(),'phone'=>fake()->phoneNumber(),'avatar'=>'/assets/avatars/guest'.(($i%8)+1).'.svg','created_at'=>now(),'updated_at'=>now()]);}
    foreach(['Pending','Confirmed','Checked In','Checked Out','Cancelled'] as $s){DB::table('reservation_statuses')->insert(['name'=>$s,'created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=220;$i++){ $checkIn=now()->subDays(rand(1,15))->addDays(rand(0,30)); $checkOut=(clone $checkIn)->addDays(rand(1,5)); DB::table('reservations')->insert(['code'=>'RSV'.str_pad((string)$i,5,'0',STR_PAD_LEFT),'guest_id'=>rand(1,120),'room_id'=>rand(1,55),'unit_id'=>rand(1,30),'reservation_status_id'=>rand(1,5),'check_in'=>$checkIn,'check_out'=>$checkOut,'stay_type'=>rand(0,1)?'checkin':'checkout','created_at'=>now(),'updated_at'=>now()]); }
    for($i=1;$i<=80;$i++){ DB::table('bookings')->insert(['reservation_id'=>$i,'guest_id'=>rand(1,120),'room_id'=>rand(1,55),'check_in'=>now()->addDays(rand(0,20)),'check_out'=>now()->addDays(rand(21,30)),'total_amount'=>rand(200,1800),'created_at'=>now(),'updated_at'=>now()]); }
    for($i=1;$i<=80;$i++){DB::table('payments')->insert(['booking_id'=>$i,'amount'=>rand(100,1800),'method'=>['cash','card','transfer'][array_rand([0,1,2])],'created_at'=>now(),'updated_at'=>now()]); DB::table('invoices')->insert(['booking_id'=>$i,'number'=>'INV'.str_pad((string)$i,5,'0',STR_PAD_LEFT),'amount'=>rand(100,1800),'status'=>'paid','created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=15;$i++){DB::table('services')->insert(['name'=>'Service '.$i,'price'=>rand(10,150),'created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=30;$i++){DB::table('products')->insert(['name'=>'Product '.$i,'price'=>rand(5,70),'stock'=>rand(30,200),'created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=45;$i++){DB::table('service_orders')->insert(['service_id'=>rand(1,15),'guest_id'=>rand(1,120),'qty'=>rand(1,5),'created_at'=>now(),'updated_at'=>now()]); DB::table('product_orders')->insert(['product_id'=>rand(1,30),'guest_id'=>rand(1,120),'qty'=>rand(1,5),'created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=60;$i++){DB::table('p_o_s_orders')->insert(['guest_id'=>rand(1,120),'amount'=>rand(20,400),'status'=>'completed','created_at'=>now(),'updated_at'=>now()]);}
    for($i=1;$i<=12;$i++){DB::table('notifications')->insert(['user_id'=>1,'title'=>'Notification '.$i,'body'=>'Operational update '.$i,'is_read'=>$i%3===0,'created_at'=>now(),'updated_at'=>now()]);}
    for($i=0;$i<30;$i++){ $d=now()->subDays(29-$i)->toDateString(); DB::table('customer_metrics')->insert(['metric_date'=>$d,'period'=>'monthly','new_customers'=>rand(20,90),'current_customers'=>rand(120,280),'created_at'=>now(),'updated_at'=>now()]); DB::table('revenue_metrics')->insert(['metric_date'=>$d,'amount'=>rand(12000,50000),'created_at'=>now(),'updated_at'=>now()]); DB::table('unit_status_metrics')->insert(['metric_date'=>$d,'occupied'=>rand(80,180),'available'=>rand(40,120),'created_at'=>now(),'updated_at'=>now()]); }
    foreach([['Welcome Back','This slider for any update','/assets/banners/banner1.svg'],['Premium Stays Await','Discover seasonal offers','/assets/banners/banner2.svg'],['Operational Insights','Track reservations in real-time','/assets/banners/banner3.svg']] as $b){DB::table('dashboard_banners')->insert(['title'=>$b[0],'subtitle'=>$b[1],'image_path'=>$b[2],'is_active'=>1,'created_at'=>now(),'updated_at'=>now()]);}
  }
}
