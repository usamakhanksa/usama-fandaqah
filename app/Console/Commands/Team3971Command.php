<?php

namespace App\Console\Commands;

use App\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Team3971Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:3971-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will update team 3971 reservations from excel';

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

        {
            $filePath = public_path('3971-team-res.xlsx'); // Adjust filename if needed
            
            try {
                // Load the spreadsheet
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                // Skip header row (assuming first row is header)

                $reservations_bar = $this->output->createProgressBar(count(array_slice($rows, 1)));
                foreach (array_slice($rows, 1) as $row) {
                    $resNum = (string) $row[0] ?? null;
                    $subTotal = $row[1] ?? null;
                    $ewaTotal = $row[2] ?? null;
                    $vatTotal = $row[3] ?? null;
                    $totalPrice = $row[4] ?? null;
    
                    
                    
                    if (!$resNum) continue; // Skip invalid rows
    
                    // Find reservation by res_num and team_id
                    $reservation = Reservation::where('number', $resNum)
                        ->where('team_id', 3971) // Adjust team retrieval
                        ->first();
    
                    if ($reservation) {
    
                        if ($reservation->ewa_total) {
                            $oldPrices = $reservation->old_prices;
                            // Re-assign the updated value properly
                            $updatedOldPrices = $oldPrices;
                            $updatedOldPrices['ewa_parentage'] = 2.50;
                            // Save back to database
                            $reservation->old_prices = $updatedOldPrices;
                            $reservation->save();
                        }
    
                        $reservation->total_price = $totalPrice;
                        $reservation->sub_total = $subTotal;
                        $reservation->ewa_total = $ewaTotal;
                        $reservation->vat_total = $vatTotal;
                        $reservation->save();
                    }else{
                      Log::info("Import failed: can not find reservation with number : " . $resNum);
                    }

                    $reservations_bar->advance();
                }
                
                $reservations_bar->finish();
                return response()->json(['success' =>  count(array_slice($rows, 1)) . ' Reservations import process excuted successfully!']);
            } catch (\Exception $e) {
                Log::error("Import failed: " . $e->getMessage());
                return response()->json(['error' => 'Import failed, check logs'], 500);
            }
        }
    
    }
}
