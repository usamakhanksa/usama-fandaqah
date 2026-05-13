<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationExportMail;
use App\Exports\ReservationsExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportReservations extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Export Reservations';

    public function handle(ActionFields $fields, Collection $models)
    {
        // Export the reservations to Excel
        $fileName = 'reservations_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $export = new ReservationsExport($models->toArray());
        
        $filePath = storage_path('app/' . $fileName);
        Excel::store($export, $fileName);

        // Optionally send email with the export
        if ($fields->email_recipient) {
            Mail::to($fields->email_recipient)->send(new ReservationExportMail($filePath));
        }

        return Action::download($fileName, $fileName);
    }

    public function fields()
    {
        return [
            File::make('Download File', 'export_file')
                ->disk('local')
                ->storeAs(function () {
                    return 'reservations_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                })
                ->creationRules('file', 'mimes:xlsx,xls,csv'),
            
            \Laravel\Nova\Fields\Text::make('Email Recipient', 'email_recipient')
                ->help('Leave empty if you want to download directly')
                ->rules('email', 'nullable'),
        ];
    }
}