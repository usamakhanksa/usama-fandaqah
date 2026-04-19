<?php
namespace App\Objects;
use App\Services\ZATCA\Phase2\GenerateOrReportInvoice;
use Carbon\Carbon;

class Invoice {
    
    /**
     * @var object
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $invoice_type;

    /**
     * @var string
     */
    protected $invoice_sub_type;

    /**
     * @var string
     */
    protected $payment_mean;

    /**
     * @var object
     */
    protected $customer_information;

     /**
     * @var object
     */
    protected $tax_total;

     /**
     * @var object
     */
    protected $monetary_total;

    /**
     * @var object
     */
    protected $allowance_charge;

    /**
     * @var string
     */
    protected $issue_date;

    /**
     * @var string
     */
    protected $issue_time;

     /**
     * @var array [
     *  @var object
     * ]
     */
    protected $items;

     /**
     * @var string
     */
    protected $invoice_billing_reference_id;
  
    /**
     * @var string
     * invoice_billing_reference_id
     */
    protected $canceled_invoice_billing_reference_id;

    /**
     * @var string
     * credit note issue reason must be provided when creating a credit note
     */
    protected $payment_instruction;
    
    /**
     * @var object
     * credit note issue reason must be provided when creating a credit note
     */
    protected $supplier_information;

    /**
     * @var object
     * credit note issue reason must be provided when creating a credit note
    */
    protected $zatcaReportingService;

    public function __construct($username, $password, $invoice_type, $invoice_sub_type, $supplier_information)
    {
        $this->payment_mean = "cash";
        $this->invoice_type = $invoice_type;
        $this->invoice_sub_type = $invoice_sub_type;
        $this->credentials = (object) Array (
            'binarySecurityToken' => $username,
            'secret' => $password
        );
        $this->supplier_information = $supplier_information;
        $this->allowance_charge = Array (
            'amount' => "0.0",
            'reason' => "",
        );
        $this->zatcaReportingService = new GenerateOrReportInvoice($this->credentials->binarySecurityToken, $this->credentials->secret, $this->supplier_information);
    }

    public function setPaymentMean ($payment_mean) {
        $this->payment_mean = $payment_mean;
    }

    public function setCustomerInformation ($vat_number, $name, $countryCode, $street, $building, $city_subdivision, $city, $postal_zone) {
        $this->customer_information = Array (
            'vat_number' => $vat_number ?? "",
            'reg_name' => $name ?? "", 
            'location' => Array (
                'countryCode' => $countryCode ?? "SA",
                'street' =>  $street ?? "",
                'building' =>   $building ?? "",
                'city_subdivision' =>  $city_subdivision ?? "",
                'city' =>   $city ?? "",
                'postal_zone' =>  $postal_zone ?? ""
            )   
        );
    }

    public function setTaxTotal ($total_tax_amount, $total_amount_excl_tax) {
        $this->tax_total = Array (
            'tax_amount' => $total_tax_amount, //excl unit actual amount
            'amount' => $total_amount_excl_tax
        );
    }

    public function setMonetaryTotal ($total_amount_incl_tax, $total_amount_excl_tax, $total_allowance, $total_prepaid ) {
        $this->monetary_total = Array (
            'total_amount_excl_tax' => $total_amount_excl_tax,
            'total_amount_incl_tax' => $total_amount_incl_tax,
            'total_allowance' =>  $total_allowance ?? 0.00,
            'total_prepaid' => $total_prepaid ?? 0.00,
            'total_amount' => $total_amount_incl_tax
        );
    }

    public function setAllowanceCharge ($allowance_amount, $allowance_reason) {
        $this->allowance_charge = Array (
            'amount' => $allowance_amount ?? "0.0",
            'reason' =>  $allowance_reason ?? "",
        );
    }

    public function setIssueDateTime ($issue_date, $issue_time) {
        $this->issue_date = $issue_date;
        $this->issue_time = $issue_time;
    }

    public function setItems (array $items) {
        $this->items = $items;
    }

    public function setInvoiceBillingReferenceId ($invoice_billing_reference_id) {
        $this->invoice_billing_reference_id = $invoice_billing_reference_id;
    }

    public function setCanceledInvoiceBillingReferenceId ($canceled_invoice_billing_reference_id) {
        $this->canceled_invoice_billing_reference_id = $canceled_invoice_billing_reference_id;
    }

    public function setPaymentInstruction ($payment_instruction) {
        $this->payment_instruction = $payment_instruction;
    }

    public function setInvoiceSubType ($invoice_sub_type) {
        $this->invoice_sub_type = $invoice_sub_type;
    }

    public function checkIfCreditNote ($invoice_sub_type) {
        if($invoice_sub_type == 'credit note'){
            return true;
        } else {
            return false;
        }
    }

    public function checkIfDebitNote ($invoice_sub_type) {
        if($invoice_sub_type == 'debit note'){
            return true;
        } else {
            return false;
        }
    }



    public function get () {
       $invoice = [
        'credential' => $this->credentials,
        'invoice_billing_reference_id' => $this->invoice_billing_reference_id,
        'invoice_type' => $this->invoice_type,
        'invoice_sub_type' => $this->invoice_sub_type,
        'payment_means' => $this->payment_mean,
        'customer_information' => $this->customer_information,
        'allowance_charge' => $this->allowance_charge,
        'tax_total' => $this->tax_total,
        'monetary_total' => $this->monetary_total,
        'issue_date' => $this->issue_date,
        'issue_time' => $this->issue_time,
        'items' => $this->items
        ];

        if(isset($this->canceled_invoice_billing_reference_id)) {
            $invoice['canceled_invoice_billing_reference_id'] = $this->canceled_invoice_billing_reference_id;
        }

        if(isset($this->payment_instruction)) {
            $invoice['payment_instruction'] = $this->payment_instruction;
        }

        return $invoice;
    }

    public function getCompliantInvoice () {
        $compliant_invoice = $this->zatcaReportingService->generateCompliantInvoice($this->get());
        return $compliant_invoice;
    }

    public function reportInvoice (object $compliant_invoice) {
        $response = null;
        if($this->invoice_type === 'tax invoice') {
            $response =  $this->zatcaReportingService->reportStandard($compliant_invoice->data->base64_signed_invoice_string, 
                                                        $compliant_invoice->data->invoice_hash, 
                                                        $compliant_invoice->data->uuid);
        } else if ($this->invoice_type === 'simplified tax invoice') { 
            $response =  $this->zatcaReportingService->reportSimplified($compliant_invoice->data->base64_signed_invoice_string, 
                                                        $compliant_invoice->data->invoice_hash, 
                                                        $compliant_invoice->data->uuid);
        }
        return $response;
    } 

    public function seedInvoice ($invoice) {
        
        // "sub_total" => 650
        // "vat" => 99.94
        // "ewa" => 16.25
        // "ttx" => 0
        // "total_price" => 766.19
        // "nights" => 1
        // "servicesSum" => 0
        // "services" => []
        // "transactions_ids" => []
        // "amount" => 766.19
        $actual_total_amount =  0.00;
        $total_amount_excl_tax = 0.00;
        $total_amount_incl_tax = 0.00;
        $total_amount_incl_tax_array = [];
        $total_tax_amount = 0;
        $items = [];

        $invoice_data = (object) $invoice->data;
        
        if(isset($invoice_data->company)) {
            $company = (object) $invoice_data->company;
            // response()->json($company->countryCode);
            $this->setCustomerInformation(
                $company->tax_number ?? "",
                $company->name ?? "",
                $company->countryCode ?? "SA",
                $company->street ?? "",
                $company->building ?? "",
                $company->city_subdivision ?? "",
                $company->city ?? "",
                $company->postal_zone ?? ""
            );
        } else if (isset($invoice->reservation->customer)) {
            $this->setCustomerInformation(
                $invoice->reservation->customer->tax_number ?? "",
                $invoice->reservation->customer->name ?? "",
                $invoice->reservation->customer->countryCode ?? "SA",
                $invoice->reservation->customer->street ?? "",
                $invoice->reservation->customer->building ?? "",
                $invoice->reservation->customer->city_subdivision ?? "",
                $invoice->reservation->customer->city ?? "",
                $invoice->reservation->customer->postal_zone ?? ""
            );
        }

        //calculate total amounts
    
       //group reservations invoice
       if(isset($invoice_data->reservations_minified)) {
           foreach ($invoice_data->reservations_minified as $key => $reservation) {
                $prices = (object) $reservation;
                $sub_total = round(floatval($prices->sub_total ?? 0.00), 2);
                $ewa = round(floatval($prices->ewa ?? 0.00), 2);
                $vat = round(floatval($prices->vat ?? 0.00), 2);

                $actual_total_amount_price = $sub_total + $ewa;
                $total_amount_with_vat_price = $actual_total_amount_price + $vat;
                $total_amount_excl_tax_price = $actual_total_amount_price;
                $total_amount_incl_tax_price = $total_amount_with_vat_price;
                //$vat_with_ewa = (float)$invoice_data->vat + (float)$invoice_data->ewa;
                $total_tax_amount_price = $vat;
                
                $actual_total_amount = $actual_total_amount + $actual_total_amount_price;
                $total_amount_excl_tax = $total_amount_excl_tax + $total_amount_excl_tax_price;
                $total_amount_incl_tax = $total_amount_incl_tax + $total_amount_incl_tax_price;
                $total_tax_amount = $total_tax_amount_price + $total_tax_amount;

            }
  
       //single reservation invoice
       } else {
        $sub_total = round(floatval($invoice_data->sub_total ?? 0.00), 2);
        $ewa = round(floatval($invoice_data->ewa ?? 0.00), 2);
        $vat = round(floatval($invoice_data->vat ?? 0.00), 2);
        $actual_total_amount = $sub_total + $ewa;
        $total_amount_with_vat = $actual_total_amount + $vat;
        $total_amount_excl_tax = $actual_total_amount;
        $total_amount_incl_tax = $total_amount_with_vat;
        //$vat_with_ewa = (float)$invoice_data->vat + (float)$invoice_data->ewa;
        $total_tax_amount = $vat;
       }
    //    return round($total_amount_incl_tax,2);
        //quantity -> nights
        //total_amount
       
        if(isset($invoice_data->reservations)) {

            foreach ($invoice_data->reservations as $key => $reservation_item) {
                $reservation_item = (object) $reservation_item;
                $unit = (object) $invoice->reservation->unit->name;
                $unit_name = $reservation_item->unit['name']['en'];
                //check if exist in minified version of reservations
                //200.00
                //5.00
                //30.75
                if(isset($invoice_data->reservations_minified[$key])) {
                    $prices =  (object) $invoice_data->reservations_minified[$key];
                    $sub_total = round(floatval($prices->sub_total ?? 0.00),2);
                    $ewa = round(floatval($prices->ewa ?? 0.00),2);
                    $vat = round(floatval($prices->vat ?? 0.0),2);

                    $actual_total_amount_price = $sub_total + $ewa;
                    $total_amount_incl_tax_price = $actual_total_amount_price + $vat;

                    //define vat percentage 
                    $item_vat_percentage = null;
                    if(isset($prices->vat)) {
                        if($prices->vat == 0.0){
                            $item_vat_percentage = 0.00;
                        }
                    }

                    array_push($items, Array (
                        "quantity" => '1',
                        "total_amount_excl_tax" => $actual_total_amount_price,
                        "total_tax_amount" => $vat,
                        "total_amount_incl_tax" => $total_amount_incl_tax_price,
                        "item_name" => $unit_name ?? "",
                        "item_cost" => $actual_total_amount_price,
                        "item_discount" => '0.0',
                        "item_vat_percentage" => $item_vat_percentage
                    ));

                    array_push($total_amount_incl_tax_array, (float)$total_amount_incl_tax_price);
                }
            }
        } elseif (isset($invoice->reservation)) {
            $unit = (object) $invoice->reservation->unit->name;
            // return response()->json($unit);
            //$prices = (object) $invoice->reservation->prices;

            // $actual_total_amount = floatval($prices->sub_total ?? 0.00) + floatval($prices->total_ewa ?? 0.00);
            // $total_amount_incl_tax = floatval($actual_total_amount ?? 0.0) + floatval($prices->total_vat ?? 0.0);
            // $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');
            
            //define vat percentage 
            $item_vat_percentage = null;
            if(isset($invoice_data->vat)) {
                if($invoice_data->vat == 0.0){
                    $item_vat_percentage = 0.00;
                }
            }
            
            $vat = round(floatval($invoice_data->vat ?? 0.0),2);
            
            array_push($items, Array (
                "quantity" => '1',
                "total_amount_excl_tax" => $actual_total_amount,
                "total_tax_amount" => $vat,
                "total_amount_incl_tax" => $total_amount_incl_tax,
                "item_name" => $unit->scalar ?? "",
                "item_cost" => $actual_total_amount,
                "item_discount" => '0.0',
                "item_vat_percentage" => $item_vat_percentage
            ));

            array_push($total_amount_incl_tax_array, (float)$total_amount_incl_tax);

        }

   

        if(isset($invoice_data->services)) {
            foreach ($invoice_data->services as $key => $posItem) {

               //item price breakdown 
               $total_vat = round(floatval($posItem['vat'] ?? 0.0),2);
               $total_price = round(floatval($posItem['sub_total'] ?? 0.0),2);
               $total_sum = $total_price; //31....
               $sum_of_all_items_exc_tax = $total_price;
               $sum_of_all_items_inc_tax =  round(floatval($posItem['totalGeneralSum'] ?? 0.0),2);
               $sum_of_all_items_vat = $total_vat;

               //using item breakdown in calculations
               $actual_total_amount_price = $sum_of_all_items_exc_tax;
               $total_amount_with_vat_price = $sum_of_all_items_inc_tax;
               $total_amount_excl_tax_price = $sum_of_all_items_exc_tax;
               $total_amount_incl_tax_price =  $sum_of_all_items_inc_tax;
               //$vat_with_ewa = (float)$invoice_data->vat + (float)$invoice_data->ewa;
               $total_tax_amount_price = $sum_of_all_items_vat;
               
               $actual_total_amount = $actual_total_amount + $actual_total_amount_price;
               $total_amount_excl_tax = $total_amount_excl_tax + $total_amount_excl_tax_price;
               $total_amount_incl_tax = $total_amount_incl_tax + $total_amount_incl_tax_price;
               $total_tax_amount = $total_tax_amount_price + $total_tax_amount;
            }
       }

        if(isset($invoice_data->services)) {
            foreach ($invoice_data->services as $key => $posItem) {
                
               $total_vat =  round(floatval($posItem['vat'] ?? 0.0),2);
               $total_price = round(floatval($posItem['sub_total'] ?? 0.0),2);
               $total_sum = round(floatval($posItem['totalGeneralSum'] ?? 0.0),2);
               $sum_of_all_items_exc_tax = $total_price;
               $sum_of_all_items_inc_tax = $total_sum;
               $sum_of_all_items_vat = $total_vat;
            
               //define vat percentage 
               $item_vat_percentage = null;
               if(isset($total_vat)) {
                   if($total_vat == 0.0){
                       $item_vat_percentage = 0.00;
                   }
               }
               

               array_push($items, Array (
                "quantity" => $posItem['qty'],
                "total_amount_excl_tax" => $sum_of_all_items_exc_tax,
                "total_tax_amount" => $sum_of_all_items_vat,
                "total_amount_incl_tax" => $sum_of_all_items_inc_tax,
                "item_name" => $posItem['statement'] ?? "",
                "item_cost" => round(floatval($posItem['price'] ?? 0.0),2),
                "item_discount" => '0.0',
                "item_vat_percentage" => $item_vat_percentage
               ));
               
               array_push($total_amount_incl_tax_array, (float)$sum_of_all_items_inc_tax);

            }
        }

        $total_amount_incl_tax = $total_tax_amount + $total_amount_excl_tax;
        $this->setTaxTotal(strval($total_tax_amount ?? 0.00), strval($total_amount_excl_tax ?? 0.00));
        //$total_amount_incl_tax = array_sum($total_amount_incl_tax_array);
        $this->setMonetaryTotal(strval($total_amount_incl_tax ?? 0.00), strval($total_amount_excl_tax ?? 0.00), null, null);

        $this->setItems($items);
        $datetime = Carbon::parse($invoice->created_at);
        $this->setIssueDateTime($datetime->format('Y-m-d'), $datetime->format('h:i:s'));

        $this->setInvoiceBillingReferenceId($invoice->number);

        if($this->checkIfCreditNote($this->invoice_sub_type)) {
            $this->setCanceledInvoiceBillingReferenceId($invoice->number);
            $this->setPaymentInstruction("Returned");
        }

    
    }

    public function seedInvoicePOS($invoice) {
        $total_amount_excl_tax = 0;
        $total_amount_incl_tax = 0;
        $total_tax_amount = 0;
        $items = [];

        $data = $invoice->service_log->meta;

        $invoice_number = $invoice->service_log->id;

        $this->setCustomerInformation(
            $data['tax_number'] ?? "", 
            $data['customer_name'] ?? "",
            $data['countryCode'] ?? "SA",
            $data['street'] ?? "",
            $data['building'] ?? "",
            $data['city_subdivision'] ?? "",
            $data['city'] ?? "",
            $data['postal_zone'] ?? ""
        );

        $total_amount_excl_tax = number_format((float) $data['sub_total'] ?? 0.0, 2, '.', '');
        $total_amount_incl_tax = number_format((float) $data['total_with_taxes'] ?? 0.0, 2, '.', '');
        $total_tax_amount = number_format((float) $data['vat_total'] ?? 0.0, 2, '.', '');

        $total_amount_incl_tax = floatval($total_amount_excl_tax) + floatval($total_tax_amount);
        $total_amount_incl_tax = number_format($total_amount_incl_tax ?? 0.0, 2, '.', '');

        $this->setTaxTotal($total_tax_amount,$total_amount_excl_tax);
        $this->setMonetaryTotal($total_amount_incl_tax,$total_amount_excl_tax, null, null);
        
        if(isset($data['services'])) {
            foreach ($data['services'] as $key => $item) {
                $item = (object) $item;
                $unit_price =  floatval((float) $item->price ?? 0.0);
                $total_amount_incl_tax = number_format((float) $item->totalGeneralSum ?? 0.0, 2, '.', '');
                array_push(
                    $items,
                    array(
                        "quantity" => $item->qty,
                        "total_amount_excl_tax" => number_format((float) $item->sub_total ?? 0.0, 2, '.', ''),
                        "total_tax_amount" => number_format((float) $item->vat ?? 0.0, 2, '.', ''),
                        "total_amount_incl_tax" => $total_amount_incl_tax,
                        "item_name" => $item->statement ?? "",
                        "item_cost" => number_format((float) $unit_price ?? 0.0, 2, '.', ''),
                        "item_discount" => '0.0',
                        "item_vat_percentage" => $item->vatIsChecked ? '15.00' : '0.00'
                    )
                );
            }
        }

        $this->setItems($items);
        
        $datetime = Carbon::parse($data['date']);
    
        $this->setIssueDateTime($datetime->format('Y-m-d'), $datetime->format('h:i:s'));

        $this->setInvoiceBillingReferenceId($invoice_number);
    
        if($this->checkIfCreditNote($this->invoice_sub_type) || $this->checkIfDebitNote($this->invoice_sub_type)) {
            $this->setCanceledInvoiceBillingReferenceId($invoice_number);
            $this->setPaymentInstruction("Returned");
        }

    }

    public function getQRCode (object $compliant_invoice) {
        return $compliant_invoice->data->qrcode;
    }
}