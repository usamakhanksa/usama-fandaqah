<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Finance\PromissoryNoteService;

class CheckOverduePromissoryNotes extends Command
{
    protected $signature = 'promissory:check-overdue';
    protected $description = 'Check and mark promissory notes as overdue based on due date';

    public function handle(PromissoryNoteService $service)
    {
        $this->info('Checking for overdue promissory notes...');
        
        $count = $service->checkOverdue();
        
        $this->info("Completed. {$count} notes marked as overdue.");
        
        return Command::SUCCESS;
    }
}
