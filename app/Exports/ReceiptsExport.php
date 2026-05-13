<?php

namespace App\Exports;

use App\Models\Receipt;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReceiptsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $teamId;
    protected $filters;

    public function __construct($teamId, array $filters = [])
    {
        $this->teamId = $teamId;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Receipt::with(['guest', 'company', 'createdBy'])
            ->forTeam($this->teamId);

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('receipt_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('receipt_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        return $query->orderBy('receipt_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Receipt Number',
            'Date',
            'Guest/Company',
            'Amount',
            'Currency',
            'SAR Equivalent',
            'Payment Method',
            'Status',
            'Created By',
            'Created At',
            'Description',
        ];
    }

    public function map($receipt): array
    {
        $customer = $receipt->guest?->name ?? $receipt->company?->name ?? '-';
        
        return [
            $receipt->receipt_number,
            $receipt->receipt_date->format('Y-m-d'),
            $customer,
            $receipt->amount,
            $receipt->currency,
            $receipt->sar_equivalent,
            $this->getPaymentMethodLabel($receipt->payment_method),
            $receipt->status,
            $receipt->createdBy?->name ?? '-',
            $receipt->created_at->format('Y-m-d H:i'),
            $receipt->description ?? '-',
        ];
    }

    protected function getPaymentMethodLabel($method)
    {
        $labels = [
            'cash' => 'Cash',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'online' => 'Online',
            'other' => 'Other',
        ];

        return $labels[$method] ?? $method;
    }
}
