<?php

namespace App\Exports;

use App\Reservation;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReservationsExport implements FromArray, WithHeadings, ShouldAutoSize, WithMapping
{
    protected $reservations;

    public function __construct(array $reservations)
    {
        $this->reservations = $reservations;
    }

    public function array(): array
    {
        return $this->reservations;
    }

    public function map($reservation): array
    {
        return [
            $reservation['number'],
            $reservation['status'],
            $reservation['reservation_category_type'],
            $reservation['customer']['name'] ?? 'N/A',
            $reservation['unit']['unit_number'] ?? 'N/A',
            $reservation['date_in'],
            $reservation['date_out'],
            $reservation['total_price'],
            $reservation['created_at'],
        ];
    }

    public function headings(): array
    {
        return [
            'Number',
            'Status',
            'Category Type',
            'Customer Name',
            'Unit Number',
            'Check In',
            'Check Out',
            'Total Price',
            'Created At',
        ];
    }
}