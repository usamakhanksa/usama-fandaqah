<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Reservation;
use Carbon\Carbon;

class UpdateReservationPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:update-prices {reservationId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update reservation prices with calculated adjustments for a specific reservation ID';

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
        // Retrieve the reservation ID from the command argument
        $reservationId = $this->argument('reservationId');

        // Fetch the reservation by ID
        $reservation = Reservation::where("id", $reservationId)->first();

        if (!$reservation) {
            $this->error("Reservation with ID {$reservationId} not found.");
            return;
        }

        // Ensure the reservation meets certain conditions
        
            $unit = $reservation->unit;

            $prices = $unit->getDatesFromRange(new Carbon($reservation->date_in), new Carbon($reservation->date_out), 1);
            $reservation->prices = $prices;

            $reservation->old_prices = [
                'prices' => $unit->prices(),
                'min_prices' => $unit->minPrices(),
                'tourism_percentage' => $unit->getTourismTax(),
                'vat_percentage' => $unit->getVat(),
                'ewa_percentage' => $unit->getEwa(),
            ];

            $reservation->change_rate = (($reservation->sub_total / $reservation->prices['sub_total']) - 1) * 100;
            $adjustment_factor = 1 + ($reservation->change_rate / 100);

            // Adjust each price element
            $prices['price'] = is_numeric($prices['price'] ?? null) ? $prices['price'] * $adjustment_factor : (float) $prices['price'] * $adjustment_factor;
            $prices['sub_total'] = is_numeric($prices['sub_total'] ?? null) ? $prices['sub_total'] * $adjustment_factor : (float) $prices['sub_total'] * $adjustment_factor;
            $prices['total_vat'] = is_numeric($prices['total_vat'] ?? null) ? $prices['total_vat'] * $adjustment_factor : (float) $prices['total_vat'] * $adjustment_factor;
            $prices['total_ewa'] = is_numeric($prices['total_ewa'] ?? null) ? $prices['total_ewa'] * $adjustment_factor : (float) $prices['total_ewa'] * $adjustment_factor;
            $prices['total_price'] = is_numeric($prices['total_price'] ?? null) ? $prices['total_price'] * $adjustment_factor : (float) $prices['total_price'] * $adjustment_factor;
            $prices['total_price_raw'] = is_numeric($prices['total_price_raw'] ?? null) ? $prices['total_price_raw'] * $adjustment_factor : (float) $prices['total_price_raw'] * $adjustment_factor;

            foreach ($prices['days'] as &$day) {
                $day['price'] = is_numeric($day['price'] ?? null) ? $day['price'] * $adjustment_factor : (float) $day['price'] * $adjustment_factor;
                $day['price_row'] = is_numeric($day['price_row'] ?? null) ? $day['price_row'] * $adjustment_factor : (float) $day['price_row'] * $adjustment_factor;
            }

            $reservation->prices = $prices;
            $reservation->save();

            $reservation->forceWithdrawFloat($reservation->total_price, [
                'category' => 'reservation',
                'statement' => 'Reservation Total Price',
            ], true, false);
            $reservation->wallet->refreshBalance();

            $this->info("Reservation ID {$reservationId} updated successfully.");

    }
}
