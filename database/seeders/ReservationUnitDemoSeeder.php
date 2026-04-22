<?php

namespace Database\Seeders;

use Database\Factories\GuestFactory;
use Database\Factories\ReservationFactory;
use Database\Factories\UnitFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservationUnitDemoSeeder extends Seeder
{
    public function run(): void
    {
        $unitColumns = Schema::getColumnListing('units');
        $guestColumns = Schema::hasTable('guests') ? Schema::getColumnListing('guests') : [];
        $reservationColumns = Schema::getColumnListing('reservations');

        $roomId = DB::table('rooms')->value('id');
        if (! $roomId) {
            return;
        }

        if (DB::table('units')->count() < 25) {
            $units = UnitFactory::new()->count(25)->make()->map(function ($item) use ($unitColumns, $roomId) {
                $record = $item->toArray();
                $record['room_id'] = $roomId;

                return Arr::only($record, $unitColumns);
            })->all();

            DB::table('units')->insert($units);
        }

        if ($guestColumns !== [] && DB::table('guests')->count() < 80) {
            $guests = GuestFactory::new()->count(80)->make()->map(function ($item) use ($guestColumns) {
                return Arr::only($item->toArray(), $guestColumns);
            })->all();

            DB::table('guests')->insert($guests);
        }

        $guestId = DB::table('guests')->value('id');
        $unitId = DB::table('units')->value('id');

        if (! $guestId || ! $unitId) {
            return;
        }

        if (DB::table('reservations')->count() < 60) {
            $statusId = DB::table('reservation_statuses')->value('id') ?? 1;

            $reservations = ReservationFactory::new()->count(60)->make()->map(function ($item) use ($reservationColumns, $guestId, $roomId, $unitId, $statusId) {
                $record = $item->toArray();
                $record['guest_id'] = $guestId;
                $record['room_id'] = $roomId;
                $record['unit_id'] = $unitId;
                $record['reservation_status_id'] = $statusId;

                return Arr::only($record, $reservationColumns);
            })->all();

            DB::table('reservations')->insert($reservations);
        }
    }
}
