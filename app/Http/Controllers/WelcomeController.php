<?php

namespace App\Http\Controllers;

use App\Team;
use App\Unit;
use App\Rating;
use App\Company;
use Carbon\Carbon;
use App\Reservation;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use ParagonIE\Sodium\Compat;
use App\Imports\CompaniesImport;
use App\Jobs\DailyBriefReportJob;
//use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Illuminate\Support\Facades\DB;
use Laravel\Spark\TeamSubscription;
use Vinkla\Hashids\Facades\Hashids;
use App\Jobs\SeedTeamDefultSettings;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Notifications\DailyBriefReport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use App\Imports\Team3971ReservationsImport;
use App\Console\Commands\UpdateReservationDateOut;
use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Spark\Events\Teams\Subscription\TeamSubscribed;
use App\Http\Resources\ReservationManagement\ReservationGuestsResource;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{

    public function changeLanguage(Request $request, $lang)
    {

        session()->put('locale', $lang);
        return redirect()->back();
    }
    /**
     * Show the application splash screen.
     *
     * @return Response
     */
    public function show()
    {
        return redirect('home/login');
    }

    public function rating($id)
    {
        $decode = Hashids::decode("$id");
        $id = reset($decode);
        $reservation = Reservation::withOutGlobalScope('team_id')->find($id);
        // $reservation = Reservation::where('id', $id)->firstOrFail();

        $settings = DB::table('settings')
            ->whereIn('key', [
                'introduction_text',
                'text_of_the_first_question',
                'enable_first_question',
                'text_of_the_second_question',
                'enable_second_question',
                'text_of_question_three',
                'enable_question_three',
                'text_of_question_four',
                'enable_question_four',
                'text_of_question_five',
                'enable_question_five',
                'the_text_of_the_sixth_question',
                'enable_sixth_question',
                'the_text_of_the_seventh_question',
                'enable_seventh_question',
                'the_text_of_the_eighth_question',
                'enable_eighth_question',
                'the_text_of_the_ninth_question',
                'enable_ninth_question',
                'the_text_of_the_tenth_question',
                'enable_tenth_question',
                'first_custom_message',
                'enable_first_custom_message',
                'title_of_the_first_question',
                'title_of_the_second_question',
                'title_of_the_third_question',
                'title_of_the_fourth_question',
                'title_of_the_fifth_question',
                'title_of_the_sixth_question',
                'title_of_the_seventh_question',
                'title_of_the_eighth_question',
                'title_of_the_ninth_question',
                'title_of_the_tenth_question',

            ])
            ->where('team_id', '=', $reservation->team_id)->pluck('value', 'key');

        //        $reservation->unit = Unit::withTrashed()->find($reservation->unit_id);

        if (!$reservation || !is_null($reservation->rating)) {
            //            return redirect('/');
            if(!$reservation->rating->status){
                return view('edit-rating', ['reservation' => $reservation, 'settings' => $settings]);
            }else{

                return view('edit-rating', ['reservation' => $reservation, 'settings' => $settings]);
            }
        }
        return view('rating', ['reservation' => $reservation, 'settings' => $settings]);
    }
    public function update(Request $request, $reservationId)
    {
        $reservation = Reservation::where('id', $reservationId)->firstOrFail();
        $rating = DB::table('ratings')->where('reservation_id', $reservationId)->first();
        if($rating){

            $total_questions_factor = 0;
            $total_questions_factor += $request->q_one ? 1 : 0;
            $total_questions_factor += $request->q_two ? 1 : 0;
            $total_questions_factor += $request->q_three ? 1 : 0;
            $total_questions_factor += $request->q_four ? 1 : 0;
            $total_questions_factor += $request->q_five ? 1 : 0;
            $total_questions_factor += $request->q_six ? 1 : 0;
            $total_questions_factor += $request->q_nine ? 1 : 0;
            $total_questions_factor += $request->q_ten ? 1 : 0;
            $total_questions_factor += $request->q_eleven ? 1 : 0;
            $total_questions_factor += $request->q_twelve ? 1 : 0;

            // to avoid division by zero exception;
            if (!$total_questions_factor) {
                $total_questions_factor = 1;
            }
            /**
             *
             * calculating the total ratings factor
             */
            $total_ratings_factor = 0;
            $total_ratings_factor += $request->q_one;
            $total_ratings_factor += $request->q_two;
            $total_ratings_factor += $request->q_three;
            $total_ratings_factor += $request->q_four;
            $total_ratings_factor += $request->q_five;
            $total_ratings_factor += $request->q_six;
            $total_ratings_factor += $request->q_nine;
            $total_ratings_factor += $request->q_ten;
            $total_ratings_factor += $request->q_eleven;
            $total_ratings_factor += $request->q_twelve;

            $overall_rating = $total_ratings_factor / $total_questions_factor;

            DB::table('ratings')
            ->where('reservation_id', $reservationId)
            ->update([
                'q_one' => $request->q_one,
                'q_two' => $request->q_two,
                'q_three' => $request->q_three,
                'q_four' => $request->q_four,
                'q_five' => $request->q_five,
                'q_six' => $request->q_six,
                'q_seven' => $request->q_seven,
                'q_eight' => $request->q_eight,
                'q_nine' => $request->q_nine,
                'q_ten' => $request->q_ten,
                'q_eleven' => $request->q_eleven,
                'q_twelve' => $request->q_twelve,
                'q_custom' => $request->q_custom,
                'overall_rating' =>$overall_rating,
            ]);
        }

        return view('rating_thank', ['reservation' => $reservation]);
    }
    public function storeRating(Reservation $reservation, Request $request)
    {
        if (!is_null($reservation->rating)) {
            return redirect('/');
        }
        $rating = new Rating();
        $data = $request->all();
        unset($data['_token']);

        $rating->q_one = isset($data['q_one']) ? (int) $data['q_one'] : 0;
        $rating->q_two = isset($data['q_two']) ? (int) $data['q_two'] : 0;
        $rating->q_three = isset($data['q_three']) ? (int) $data['q_three'] : 0;
        $rating->q_four = isset($data['q_four']) ? (int) $data['q_four'] : 0;
        $rating->q_five = isset($data['q_five']) ? (int) $data['q_five'] : 0;
        $rating->q_six = isset($data['q_six']) ? (int) $data['q_six'] : 0;
        $rating->q_nine = isset($data['q_nine']) ? (int) $data['q_nine'] : 0;
        $rating->q_ten = isset($data['q_ten']) ? (int) $data['q_ten'] : 0;
        $rating->q_eleven = isset($data['q_eleven']) ? (int) $data['q_eleven'] : 0;
        $rating->q_twelve = isset($data['q_twelve']) ? (int) $data['q_twelve'] : 0;

        $rating->q_custom = isset($data['q_custom']) ? $data['q_custom'] : null;



        /**
         * Do the trick here to calculate the overall rating per row
         * calculating the total questions factor
         */
        $total_questions_factor = 0;
        $total_questions_factor += $rating->q_one ? 1 : 0;
        $total_questions_factor += $rating->q_two ? 1 : 0;
        $total_questions_factor += $rating->q_three ? 1 : 0;
        $total_questions_factor += $rating->q_four ? 1 : 0;
        $total_questions_factor += $rating->q_five ? 1 : 0;
        $total_questions_factor += $rating->q_six ? 1 : 0;
        $total_questions_factor += $rating->q_nine ? 1 : 0;
        $total_questions_factor += $rating->q_ten ? 1 : 0;
        $total_questions_factor += $rating->q_eleven ? 1 : 0;
        $total_questions_factor += $rating->q_twelve ? 1 : 0;
        // to avoid division by zero exception;
        if (!$total_questions_factor) {
            $total_questions_factor = 1;
        }
        /**
         *
         * calculating the total ratings factor
         */
        $total_ratings_factor = 0;
        $total_ratings_factor += $rating->q_one;
        $total_ratings_factor += $rating->q_two;
        $total_ratings_factor += $rating->q_three;
        $total_ratings_factor += $rating->q_four;
        $total_ratings_factor += $rating->q_five;
        $total_ratings_factor += $rating->q_six;
        $total_ratings_factor += $rating->q_nine;
        $total_ratings_factor += $rating->q_ten;
        $total_ratings_factor += $rating->q_eleven;
        $total_ratings_factor += $rating->q_twelve;

        $rating->overall_rating = $total_ratings_factor / $total_questions_factor;
        $rating->q_seven = isset($data['q_seven']) ?  $data['q_seven'] : null;
        $rating->q_eight = isset($data['q_eight']) ?  $data['q_eight'] : null;
        $rating->q_custom = isset($data['q_custom']) ?  $data['q_custom'] : null;
        $rating->team_id = $reservation->team_id;
        $rating->reservation_id = $reservation->id;
        $rating->save();
        $reservation->rating_id = $rating->id;
        $reservation->save();
        $settings = DB::table('settings')
        ->whereIn('key', [
            'introduction_text',
            'text_of_the_first_question',
            'enable_first_question',
            'text_of_the_second_question',
            'enable_second_question',
            'text_of_question_three',
            'enable_question_three',
            'text_of_question_four',
            'enable_question_four',
            'text_of_question_five',
            'enable_question_five',
            'the_text_of_the_sixth_question',
            'enable_sixth_question',
            'the_text_of_the_seventh_question',
            'enable_seventh_question',
            'the_text_of_the_eighth_question',
            'enable_eighth_question',
            'the_text_of_the_ninth_question',
            'enable_ninth_question',
            'the_text_of_the_tenth_question',
            'enable_tenth_question',
            'first_custom_message',
            'enable_first_custom_message',
            'title_of_the_first_question',
            'title_of_the_second_question',
            'title_of_the_third_question',
            'title_of_the_fourth_question',
            'title_of_the_fifth_question',
            'title_of_the_sixth_question',
            'title_of_the_seventh_question',
            'title_of_the_eighth_question',
            'title_of_the_ninth_question',
            'title_of_the_tenth_question',

        ])
        ->where('team_id', '=', $reservation->team_id)->pluck('value', 'key');
        $reservation = $reservation = Reservation::where('id', $reservation->id)->firstOrFail();
        return view('edit-rating', ['reservation' => $reservation, 'settings' => $settings, 'store' => 1]);

    }

    public function import(Request $request)
    {
        
    }

    public function test(Request $request)
    {



        //        $report = new DailyBriefReportJob($request->get('id'));
        //        if($report->handle()){
        //            print "Job ran";
        //        }
    }
}
