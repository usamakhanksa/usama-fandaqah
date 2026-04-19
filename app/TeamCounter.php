<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

class TeamCounter extends Model
{
    use Rememberable;
    use HasTeam;

    protected $fillable = [
        'team_id',
        'reservation_number',
        'payment_number',
        'receipt_number',
        'service_number',
        'contract_number',
        'invoice_number',
        'promissory_number',
        'credit_note_number'
    ];

    /**
     *
     * @param  string  $value
     * @return string
     */
    public function getInvoiceNumAttribute()
    {
        return max([$this->invoice_number, $this->last_invoice_number]) + 1;
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getReservationNumAttribute()
    {
        return max([$this->reservation_number, $this->last_reservation_number]) + 1;
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getReceiptNumAttribute()
    {
        return max([$this->receipt_number, $this->last_receipt_number]) + 1;
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getPaymentNumAttribute()
    {
        return max([$this->payment_number, $this->last_payment_number]) + 1;
    }

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getServiceNumAttribute()
    {
        return max([$this->service_number, $this->last_service_number]) + 1;
    }

    public function getPromissoryNumAttribute()
    {
        return max([$this->promissory_number, $this->last_promissory_number]) + 1;
    }

    public function getCreditNoteNumAttribute()
    {
        return max([$this->credit_note_number, $this->last_credit_note_number]) + 1;
    }
}
