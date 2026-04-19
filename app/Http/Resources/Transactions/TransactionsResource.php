<?php

namespace App\Http\Resources\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class TransactionsResource extends JsonResource
{

    /**
     * UnitResource constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        static::$wrap = null;
        parent::__construct($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'hash_id' => Hashids::encode($this->id),
            'transaction_number' => $this->number,
            'loaded_on' => $this->payable_type == 'App\\Team' ? 'team' : 'reservation',
            'reservation_number' => $this->payable_type == 'App\\Team' ? '-': $this->reservation->number ,
            'unit_number' => $this->reservation->unit ? $this->reservation->unit->unit_number . ' - ' . $this->reservation->unit->name : '-',
            'amount' => abs($this->amount / 100),
            'from' => isset($this->meta['from']) ? $this->meta['from'] : '-',
            'for' => isset($this->meta['statement']) ? $this->meta['statement'] : '-' ,
            'date' => isset($this->meta['date']) ? $this->meta['date'] : '-',
            'payment_method' => isset($this->meta['payment_type']) ? __(ucfirst($this->meta['payment_type'])) : '-',
            'reference_number' => isset($this->meta['reference']) ? $this->meta['reference'] : '-',
            'employee' => isset($meta['employee']) ? $this->meta['employee'] : '-'
        ];
    }

}
