<?php

namespace App\Http\Resources\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicesTransactionsResource extends JsonResource
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
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'payable' => $this->payable,
            'type' =>  $this->payable_type == 'App\Reservation' ? 'withdraw' : 'deposit',
            'term_id' => array_key_exists('type' , $this->meta) ? $this->meta['type'] : null,
            'transaction_number' => $this->number,
            'reservation_number'   => $this->payable_type == 'App\Reservation' &&  $this->reservation ? $this->reservation->number : '-',
            'unit_number' => $this->payable_type == 'App\Reservation' && $this->reservation && $this->reservation->unit ? $this->reservation->unit->unit_number . ' - ' . $this->reservation->unit->name : '-',
            'amount' => abs($this->amount / 100),
            'received_from' => $this->payable_type == 'App\Reservation' &&  $this->reservation && $this->reservation->customer ? $this->reservation->customer->name : '-',
            'for' => $this->meta['statement'],
//            'transaction_date' => array_key_exists('date' , $this->meta )   ? $this->meta['date']  : Carbon::parse($this->created_at)->format('Y-m-d h:m'),
            'transaction_date' =>  Carbon::parse($this->created_at)->format('Y-m-d H:i'),
//            'transaction_date' =>  date('Y-m-d H:i' , strtotime($this->created_at)),
            'payment_method' => array_key_exists('payment_type' , $this->meta ) ?  $this->meta['payment_type'] : 'cash',
            'reference' => array_key_exists('reference' , $this->meta ) ?  $this->meta['reference'] : '-',
            'employee' => $this->creator->name,
            'note' => array_key_exists('note' , $this->meta ) ?  $this->meta['note'] : '-',
            'services'=> array_key_exists('services' , $this->meta ) ?  $this->meta['services'] : '-',
            'sub_total'=> array_key_exists('sub_total' , $this->meta ) ?  $this->meta['sub_total'] : 0,
            'ttx_total'=> array_key_exists('ttx_total' , $this->meta ) ?  $this->meta['ttx_total'] : 0,
            'vat_total'=> array_key_exists('vat_total' , $this->meta ) ?  $this->meta['vat_total'] : 0,
            'total_with_taxes'=> array_key_exists('total_with_taxes' , $this->meta ) ?  $this->meta['total_with_taxes'] : 0,
            'qty'=> array_key_exists('qty' , $this->meta ) ?  $this->meta['qty'] : '-',
            'from'=> array_key_exists('from' , $this->meta ) ?  $this->meta['from'] : __('Loaded On Services Revenue'),
        ];
    }

}
