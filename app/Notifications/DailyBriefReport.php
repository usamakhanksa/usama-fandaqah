<?php

namespace App\Notifications;

use App\Team;
use App\Unit;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyBriefReport extends Notification
{
    /** @var string */
    private $subject;
    /** @var string */
    private $occupancyRate;

    /** @var array */
    private $reservationsAndUnitsHolder;

    private $to;

    private $data;

    /** @var Team */
    public $team;

    private $revenueTaxFeeReportCalcuations;

    private $day;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($to, Team $team, $data)
    {
        $this->to = $to;
        $this->data = $data;
        $this->team = $team;

        $now = Carbon::now()->subDay();
        $this->subject = __('Statistical report for the last 24 hours') . '  ( '  . __($now->localeDayOfWeek) . ' - ' . $now->format('Y/m/d') . ' )';

        $this->day = Carbon::today()->subDay();
        $this->revenueTaxFeeReportCalcuations = $this->callRevenueTaxFeeReport();
        $this->reservationsAndUnitsHolder = $this->getReservationsAndUnits();
        $this->occupancyRate();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->to;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data = [
            'team' => $this->team->name,
            'subject' => $this->subject,
            'reservationsAndUnitsHolder' => $this->reservationsAndUnitsHolder,
            'occupancy_rate' => $this->occupancyRate,
            'revenueTaxFeeReportCalcuations' => $this->revenueTaxFeeReportCalcuations
        ];

        return (new MailMessage())
            ->from('postmaster@app.fandaqah.com', __('Fandaqah'))
            ->view('email.owner.daily_report', ['data' => $data])
            ->subject($this->team->name . ' , '  . $this->subject);
    }

    public function toSms($notifiable)
    {

        $count_rented_units = count($this->reservationsAndUnitsHolder['reservations']) ? count($this->reservationsAndUnitsHolder['reservations']) : 1;
        $casted_count_rented_units =  $count_rented_units;
        $count_units = count($this->reservationsAndUnitsHolder['units']);
        $casted_count_units =  $count_units;

        $message = __('Hello There')  . ' ' . $this->team->name;
        $message .= PHP_EOL;
        $message .= $this->subject;
        $message .= PHP_EOL;

        if ((int) $casted_count_rented_units) {
            $message .= __('Rented Units') . ' : ' . count($this->reservationsAndUnitsHolder['reservations']);
            $message .= PHP_EOL;
        }

        if (count($this->reservationsAndUnitsHolder['units'])) {
            $message .= __('Available Units') . ' : ' . ((float) $casted_count_units - (float) $casted_count_rented_units);
            $message .= PHP_EOL;
        }

        $message .= __('Total Revenue') . ' : ' . $this->revenueTaxFeeReportCalcuations['total_revenue'];
        $message .= PHP_EOL;
        $message .= __('Total Ewa') . ' : ' . $this->revenueTaxFeeReportCalcuations['total_ewa'];
        $message .= PHP_EOL;
        $message .= __('Total Vat') . ' : ' . $this->revenueTaxFeeReportCalcuations['total_vat'];
        $message .= PHP_EOL;
        $message .= __('Grand Total') . ' : ' . $this->revenueTaxFeeReportCalcuations['total_reservations'];
        $message .= PHP_EOL;
        $message .= __('Rental Rate') . ' : ' . number_format(((float) $this->revenueTaxFeeReportCalcuations['total_revenue_unformatted'] / $casted_count_rented_units), 2);
        $message .= PHP_EOL;
        $message .= __('Occupancy rate') . ' : ' . $this->occupancyRate;

        try {
            foreach ($this->data->value as $obj) {
                $phone = preg_replace('/\s+/', '', $obj->phone);
                $phone = preg_replace('/[^0-9\-]/', '', $obj->phone);
                sendSms($this->team->id, $message, intval($phone), false, false);

                // UnifonicFacade::send(intval($phone), $message);
            }
        } catch (\Exception $exception) {
            logger($exception);
        }
    }

    private function occupancyRate()
    {
        /**
         * Why where('status', '!=', 0) ? cause we have different statuses like under cleaning , maintenance and available 
         */
        try {
            $units = Unit::where('team_id', $this->team->id)->whereEnabled(true)->where('status', '!=', 0)->whereNull('deleted_at')->get();
            $rate = count($units) ? number_format(((count($this->reservationsAndUnitsHolder['reservations']) / count($units)) * 100), 2) : 0;
            $this->occupancyRate =  $rate . '%';
        } catch (\Throwable $th) {
            logger(json_encode($this->team));
        }
    }


    private function getReservationsAndUnits()
    {
        $day = $this->day;
        $units = Unit::where('team_id', $this->team->id)->whereEnabled(true)->where('status', '!=', 0)->whereNull('deleted_at')->get();
        $reservations = Reservation::where('team_id', $this->team->id)
            ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
            ->whereDateBetween($day->startOfDay())
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->get();

        return ['units' => $units, 'reservations' => $reservations];
    }




    /*----------------------------------------------------------Some Helpers-----------------------------------------------*/

    /**
     * Function will fetch the same data as revenue tax fee report 
     * @return array
     */
    private function callRevenueTaxFeeReport()
    {

        $calculations = [];

        $dateFrom     =  $this->day;
        $dateTo       =  $this->day;

        $reservations = Reservation::where('team_id', $this->team->id)
            ->whereIntersectsDateIn($dateFrom)
            ->whereIntersectsDateOut($dateTo)
            ->where('status', '!=', 'canceled')
            ->get();



        if (count($reservations)) {

            /**
             * This Hack to check if reservations has at least one reservation with ttx tax applied
             */
            $collection = collect($reservations);
            $count  =  $collection->filter(function ($reservation) {
                return $reservation->ttx_total > 0;
            })->count();

            $hasAtLeastOneTourismTaxApplied = $count > 0 ? true : false;

            // General Holders
            $leasing_revenue        = [];
            $services_revenue       = [];
            $total_revenue          = [];
            $total_ewa              = [];
            $vat_on_reservation     = [];
            $vat_on_services        = [];
            $ttx_on_reservation     = [];
            $ttx_on_services        = [];
            $reservationsSkeleton   = [];
            $reservationsHolder  = [];
            $total_reservation      = [];


            foreach ($reservations as $reservation) {



                // reservations dates
                $dateFromParsed = Carbon::parse($reservation->date_in);
                $dateToParsed   = Carbon::parse($reservation->date_out);

                // form dates
                $fromParsed = Carbon::parse($dateFrom);
                $toParsed   = Carbon::parse($dateTo);




                // forming objects and make calculations in backend instead of front-end
                $reservationsSkeleton['reservation_id'] = $reservation->id;
                $reservationsSkeleton['reservation_number'] = $reservation->number;
                $reservationsSkeleton['unit_number'] = $reservation->unit ? $reservation->unit->unit_number : '-';
                $reservationsSkeleton['customer_name'] = $reservation->customer ? $reservation->customer->name : '-';
                $reservationsSkeleton['from_date'] = date('Y/m/d', strtotime($dateFrom));

                $nights = $this->calculateNights($reservation, $dateFrom, $dateTo);


                if ($reservation->date_out > $dateTo) {
                    $dateCompared = $dateTo;
                } else {
                    $dateCompared = $reservation->date_out;
                }

                if ($fromParsed->lessThanOrEqualTo($dateFromParsed)) {
                    $reservationsSkeleton['from_date'] = $dateFromParsed->format('Y/m/d');
                    if (
                        $reservation->nights == 1
                    )
                        $dateCompared = $reservation->date_in;
                } else {
                    $reservationsSkeleton['to_date'] = $reservation->date_in;
                }

                if ($toParsed->greaterThanOrEqualTo($dateToParsed) and $reservation->nights != 1) {
                    if ($dateToParsed == $fromParsed) {
                        $dateCompared = $dateToParsed;
                    } else {
                        $dateCompared = $dateToParsed->subDay();
                    }
                    $nights = $reservation->nights;
                }

                $reservationsSkeleton['to_date'] =   date('Y/m/d', strtotime($dateCompared));
                //                $reservationsSkeleton ['nights_count'] =  $reservation->nights;
                $reservationsSkeleton['nights_count'] =  $nights;

                if ($reservationsSkeleton['from_date'] == $reservationsSkeleton['to_date']) {
                    $reservationsSkeleton['nights_count'] = 1;
                }

                $from = Carbon::parse($reservationsSkeleton['from_date']);
                $to = Carbon::parse($reservationsSkeleton['to_date']);
                $nights = $to->diff($from)->days;

                $reservationsSkeleton['nights_count'] = $nights + 1;


                // avoid division by zero
                $reservation_nights = $reservation->nights <= 0 ? 1 : $reservation->nights;


                /*----------------------------------------------------- Data --------------------------------------------------------------*/
                $servicesTransactionsCalculations                           = $this->servicesPerDate($reservation, $reservation->services, $reservationsSkeleton['from_date'], $reservationsSkeleton['to_date'], $reservation->date_out);
                $reservationsSkeleton['leasing_price']                     = number_format(($reservation->sub_total / $reservation_nights), 2);
                $reservationsSkeleton['services']                          = number_format($servicesTransactionsCalculations['services_subtotal'], 2);
                $reservationsSkeleton['total_revenue']                     = number_format((($reservation->sub_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_subtotal'], 2);
                $totalRevenue                                               = (($reservation->sub_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_subtotal'];
                $reservationsSkeleton['total_ewa']                         = number_format(($reservation->ewa_total / $reservation_nights) * $reservationsSkeleton['nights_count'], 2);
                $totalEwa                                                   = (($reservation->ewa_total / $reservation_nights) * $reservationsSkeleton['nights_count']);
                $reservationsSkeleton['vat_on_reservation']                = number_format(($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count'], 2);
                $reservationsSkeleton['vat_on_services']                   = number_format($servicesTransactionsCalculations['services_vat'], 2);
                $reservationsSkeleton['total_vat']                         = number_format((($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_vat'], 2);
                $totalVat                                                   = (($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_vat'];
                $reservationsSkeleton['ttx_on_reservation']                = number_format(($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count'], 2);
                $reservationsSkeleton['ttx_on_services']                   = number_format($servicesTransactionsCalculations['services_ttx'], 2);
                $reservationsSkeleton['total_ttx']                         = number_format((($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_ttx'], 2);
                $totalTtx                                                   = (($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count']) + $servicesTransactionsCalculations['services_ttx'];
                $reservationsSkeleton['total_reservation']                 = number_format($totalRevenue + $totalEwa  + $reservationsSkeleton['total_vat'] +  $reservationsSkeleton['total_ttx'], 2);
                $totalReservation                                           = $totalRevenue + $totalEwa  + $totalVat +  $totalTtx;

                /*--------------------------------------------------- Calculations -------------------------------------------------------- */
                $leasing_revenue[]      = ($reservation->sub_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $services_revenue[]      = $reservationsSkeleton['services'];
                $total_revenue[]      = $totalRevenue;
                $total_ewa[]      = ($reservation->ewa_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $vat_on_reservation[]      = ($reservation->vat_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $vat_on_services[]      = $reservationsSkeleton['vat_on_services'];
                $ttx_on_reservation[]      = ($reservation->ttx_total / $reservation_nights) * $reservationsSkeleton['nights_count'];
                $ttx_on_services[]      = $reservationsSkeleton['ttx_on_services'];
                $total_reservation[]      = $totalReservation;


                $reservationsHolder[] = $reservationsSkeleton;
            }

            /**
             * Calculations needed for our table statistics
             */
            $calculations['total_revenue'] = number_format(array_sum($total_revenue), 2);
            $calculations['total_revenue_unformatted'] = array_sum($total_revenue);
            $calculations['total_ewa'] = number_format(array_sum($total_ewa), 2);
            $calculations['total_vat'] = number_format(array_sum($vat_on_reservation)  + array_sum($vat_on_services), 2);
            $calculations['total_reservations'] = number_format(array_sum($total_reservation), 2);
            return $calculations;
        }
        $calculations['total_revenue'] = 0;
        $calculations['total_revenue_unformatted'] = 0;
        $calculations['total_vat'] = 0;
        $calculations['total_ewa'] = 0;
        $calculations['total_reservations'] = 0;
        return $calculations;
    }

    /**
     * Function will fetch services per date
     * @param $reservation_id
     * @param $from
     * @param $to
     * @return array
     */
    function servicesPerDate($reservation, $services, $from, $to, $reservation_date_out = null)
    {

        $date_out = Carbon::parse($reservation_date_out)->subDay()->endOfDay();
        $from  = Carbon::parse($from)->startOfDay();
        $to  = Carbon::parse($to)->endOfDay();

        $services =  $reservation->services()->whereBetween('created_at', [$from, $to])->get();

        /**
         * Include services that has been made on the same date with checkout in revenue report
         */
        if ($date_out->getTimestamp() == $to->getTimestamp()) {
            // i want to include all services that has been made on the checkout date 
            // i wanna fetch them also 
            $checkoutServices =  $reservation->services()->whereDate('created_at', 'LIKE', "%$reservation_date_out%")->get();
            if (!$checkoutServices->isEmpty()) {
                $services = $services->merge($checkoutServices);
            }
        }

        if ($services) {
            return  $this->filterServices($services);
        }
        return $services;
    }

    /**
     * Function will filter services then return array of calculations
     * @param $services
     * @return array
     */
    function filterServices($services)
    {

        $vat = [];
        $ttx = [];
        $subtotal = [];
        $total_with_taxes = [];
        foreach ($services as $service) {

            $vat[] = $service->meta['vat_total'];
            $ttx[] = $service->meta['ttx_total'];
            $subtotal[] = $service->meta['sub_total'];
            $total_with_taxes[] = $service->meta['total_with_taxes'];
        }

        return [
            'services_vat' =>  array_sum($vat),
            'services_ttx' =>  array_sum($ttx),
            'services_subtotal' => array_sum($subtotal),
            'services_total_with_taxes' => array_sum($total_with_taxes)
        ];
    }

    /**
     * Function to calculate nights
     * @param $reservation
     * @param $dateFrom
     * @param $dateTo
     * @return int|mixed
     * @throws \Exception
     */
    function calculateNights($reservation, $dateFrom, $dateTo)
    {
        $date_in = new \DateTime($dateFrom);
        $date_out = new \DateTime($dateTo);

        $days = $date_out->diff($date_in)->days;

        if ($days == 0) {
            $days = 1;
            return $days;
        }

        return $days > $reservation->nights ?   $days  : $reservation->nights;
    }
}
