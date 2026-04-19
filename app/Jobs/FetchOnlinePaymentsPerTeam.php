<?php

namespace App\Jobs;

use App\Integration;
use App\OnlinePaymentServiceInvoice;
use GuzzleHttp\Client;
use App\ReservationInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\ZATCA\Phase2\GenerateOrReportInvoice;

class FetchOnlinePaymentsPerTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $team_data;
    protected $headers;
    protected $url;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($team_data)
    {
        $this->team_data = $team_data;
        $this->headers =  [
            'x-team' => $team_data['id'],
            'Content-Type' => 'application/json',
            'x-invoicing-type' => $team_data['online_payment_service_invoicing_type'] 
        ];

        $this->url = env('HYPERPAY_PAYMENT_URL') . 'api/hyperpay/get-transactions';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $hyperpayTransactionsRequest = $this->getTransactions();

       if(isset($hyperpayTransactionsRequest['total_fees']) &&  $hyperpayTransactionsRequest['total_fees'] > 0){
           //******* GENERATE SIMPLIFIED ZATCA INVOICE  ********/
           $zatcaGenerateAndReportInvoiceRequest = $this->generateAndReportZatcaInvoice($hyperpayTransactionsRequest['total_fees']);
  
           if($zatcaGenerateAndReportInvoiceRequest && $zatcaGenerateAndReportInvoiceRequest->status == 200){
               
                    if($zatcaGenerateAndReportInvoiceRequest && $zatcaGenerateAndReportInvoiceRequest->pdf){
                        $file_name = $zatcaGenerateAndReportInvoiceRequest->file_name.'.pdf';
                        $file = base64_decode($zatcaGenerateAndReportInvoiceRequest->pdf);
                        $file_path = 'online-payment-invoicing/' . $this->team_data['id'] . '/';
                        Storage::disk(env('FILESYSTEM_DRIVER','s3'))->put($file_path . $file_name, $file);

                        $storeFile = new OnlinePaymentServiceInvoice();
                        $storeFile->team_id = $this->team_data['id'];
                        $storeFile->total_fees = $hyperpayTransactionsRequest['total_fees'];
                        $storeFile->vat_on_fees = number_format(round($hyperpayTransactionsRequest['total_fees']*0.15,2),2,'.','');
                        $storeFile->file_name = $file_name;
                        $storeFile->driver = env('FILESYSTEM_DRIVER','s3');
                        $storeFile->file_path = $file_path;
                        $storeFile->save();
                        
                        if($zatcaGenerateAndReportInvoiceRequest && $zatcaGenerateAndReportInvoiceRequest->invoice_number){
                            $this->updateTransactionsInHyperpay($zatcaGenerateAndReportInvoiceRequest->invoice_number,$hyperpayTransactionsRequest['transaction_ids']);
                        }
                    }
                
           }
            
       }else{
            logger(json_encode([
                'status' => 500,
                'message' => 'no transactions available to generate invoice'
            ]));
       }
    }
    function formatDataForgenerateAndReportZatcaInvoice($total_fees){

        $total_amount = number_format( ($total_fees + round($total_fees*0.15,2)),2,'.','');
        return  [
            'credential' => [
                'binarySecurityToken' => env('FANDAQAH_SELLER_ZATCA_USERNAME'),
                'secret' => env('FANDAQAH_SELLER_ZATCA_PASSWORD'),
            ],
            'invoice_type' => 'simplified tax invoice',
            'invoice_sub_type' => 'invoice',
            'invoice_billing_reference_id' => '',
            'payment_means' => 'cash',
            'customer_information' => [
                'vat_number' => '',
                'reg_name' => $this->team_data['name'] ?? '',
                'location' => [
                    "countryCode" => $this->getCountryIsoFromCountryName($this->team_data['country']),
                    "street" => "",
                    "building" => "",
                    "city_subdivision" => "",
                    "city" => "",
                    "postal_zone" => ""
                ]
            ],
            "allowance_charge" => [
                "amount" => 0.00,
                "reason" => ""
            ],
            "tax_total" => [
                "tax_amount" => number_format(round($total_fees*0.15,2),2,'.',''),
                "amount" => $total_fees
            ],
            "monetary_total" => [
                "total_amount_excl_tax" => $total_fees,
                "total_amount_incl_tax" => $total_amount,
                "total_allowance" => 0.00,
                "total_prepaid" => 0.00,
                "total_amount" => $total_amount
            ],
            "items" => [
                [
                    "quantity" => 1,
                    "total_amount_excl_tax" => $total_fees,
                    "total_tax_amount" => number_format(round($total_fees*0.15,2),2,'.',''),
                    "total_amount_incl_tax" => $total_amount,
                    "item_name" => env('FANDAQAH_PAYMENT_SERVICE_INVOICE_DESCRIPTION'),
                    "item_cost" => $total_fees,
                    "item_discount" => 0.00
                ],
            ],
            "meta" => [
                "logo_url" =>  env('FANDAQAH_INVOICEING_LOGO_URL')
            ]

        ]; 
    }
    function generateAndReportZatcaInvoice($total_fees){
            $data = $this->formatDataForgenerateAndReportZatcaInvoice($total_fees);
            $this->headers =  [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '. env('FANDAQAH_ZATCA_COMPANY') . '::' . env('ZATCA_EINVOICING_API_TOKEN')
            ];

            $this->url = env('ZATCA_EINVOICING_API_URL') . '/generate-and-submit';
            $zatcaRequest = guzzleRequester($this->url, $this->headers, 'POST', $data);
            return $zatcaRequest;
    }

    //****** GET LIST OF TRANSACTOINS FROM HYPERPAY AND SUM THE TOTAL FEES  *******/
    function getTransactions(){
        //***** HIT HYPERPAY TRANSACTIONS ENDPOINT TO GET A LIST OF TRANSACTIONS  ******/
        $fetchTransactions = guzzleRequester($this->url, $this->headers, 'GET');
        $total_fees = 0;
        $transaction_ids = [];
        if(count($fetchTransactions)){
           $total_fees = number_format(round(collect($fetchTransactions)->pluck('total_fees')->sum(),2),2,'.','');
           $transaction_ids = collect($fetchTransactions)->pluck('id')->toArray();
        }
       return [
            'total_fees' => $total_fees,
            'transaction_ids' => $transaction_ids
       ];
    }
    function getCountryIsoFromCountryName($country_name){
        $countries = [
            'Afghanistan' => 'AF',
            'Aland Islands' => 'AX',
            'Albania' => 'AL',
            'Algeria' => 'DZ',
            'American Samoa' => 'AS',
            'Andorra' => 'AD',
            'Angola' => 'AO',
            'Anguilla' => 'AI',
            'Antarctica' => 'AQ',
            'Antigua and Barbuda' => 'AG',
            'Argentina' => 'AR',
            'Armenia' => 'AM',
            'Aruba' => 'AW',
            'Australia' => 'AU',
            'Austria' => 'AT',
            'Azerbaijan' => 'AZ',
            'Bahamas' => 'BS',
            'Bahrain' => 'BH',
            'Bangladesh' => 'BD',
            'Barbados' => 'BB',
            'Belarus' => 'BY',
            'Belgium' => 'BE',
            'Belize' => 'BZ',
            'Benin' => 'BJ',
            'Bermuda' => 'BM',
            'Bhutan' => 'BT',
            'Bolivia' => 'BO',
            'Bonaire, Saint Eustatius and Saba' => 'BQ',
            'Bosnia and Herzegovina' => 'BA',
            'Botswana' => 'BW',
            'Bouvet Island' => 'BV',
            'Brazil' => 'BR',
            'British Indian Ocean Territory' => 'IO',
            'British Virgin Islands' => 'VG',
            'Brunei' => 'BN',
            'Bulgaria' => 'BG',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Cambodia' => 'KH',
            'Cameroon' => 'CM',
            'Canada' => 'CA',
            'Cape Verde' => 'CV',
            'Cayman Islands' => 'KY',
            'Central African Republic' => 'CF',
            'Chad' => 'TD',
            'Chile' => 'CL',
            'China' => 'CN',
            'Christmas Island' => 'CX',
            'Cocos Islands' => 'CC',
            'Colombia' => 'CO',
            'Comoros' => 'KM',
            'Cook Islands' => 'CK',
            'Costa Rica' => 'CR',
            'Croatia' => 'HR',
            'Cuba' => 'CU',
            'Curacao' => 'CW',
            'Cyprus' => 'CY',
            'Czech Republic' => 'CZ',
            'Democratic Republic of the Congo' => 'CD',
            'Denmark' => 'DK',
            'Djibouti' => 'DJ',
            'Dominica' => 'DM',
            'Dominican Republic' => 'DO',
            'East Timor' => 'TL',
            'Ecuador' => 'EC',
            'Egypt' => 'EG',
            'El Salvador' => 'SV',
            'Equatorial Guinea' => 'GQ',
            'Eritrea' => 'ER',
            'Estonia' => 'EE',
            'Ethiopia' => 'ET',
            'Falkland Islands' => 'FK',
            'Faroe Islands' => 'FO',
            'Fiji' => 'FJ',
            'Finland' => 'FI',
            'France' => 'FR',
            'French Guiana' => 'GF',
            'French Polynesia' => 'PF',
            'French Southern Territories' => 'TF',
            'Gabon' => 'GA',
            'Gambia' => 'GM',
            'Georgia' => 'GE',
            'Germany' => 'DE',
            'Ghana' => 'GH',
            'Gibraltar' => 'GI',
            'Greece' => 'GR',
            'Greenland' => 'GL',
            'Grenada' => 'GD',
            'Guadeloupe' => 'GP',
            'Guam' => 'GU',
            'Guatemala' => 'GT',
            'Guernsey' => 'GG',
            'Guinea' => 'GN',
            'Guinea-Bissau' => 'GW',
            'Guyana' => 'GY',
            'Haiti' => 'HT',
            'Heard Island and McDonald Islands' => 'HM',
            'Honduras' => 'HN',
            'Hong Kong' => 'HK',
            'Hungary' => 'HU',
            'Iceland' => 'IS',
            'India' => 'IN',
            'Indonesia' => 'ID',
            'Iran' => 'IR',
            'Iraq' => 'IQ',
            'Ireland' => 'IE',
            'Isle of Man' => 'IM',
            'Israel' => 'IL',
            'Italy' => 'IT',
            'Ivory Coast' => 'CI',
            'Jamaica' => 'JM',
            'Japan' => 'JP',
            'Jersey' => 'JE',
            'Jordan' => 'JO',
            'Kazakhstan' => 'KZ',
            'Kenya' => 'KE',
            'Kiribati' => 'KI',
            'Kosovo' => 'XK',
            'Kuwait' => 'KW',
            'Kyrgyzstan' => 'KG',
            'Laos' => 'LA',
            'Latvia' => 'LV',
            'Lebanon' => 'LB',
            'Lesotho' => 'LS',
            'Liberia' => 'LR',
            'Libya' => 'LY',
            'Liechtenstein' => 'LI',
            'Lithuania' => 'LT',
            'Luxembourg' => 'LU',
            'Macao' => 'MO',
            'Macedonia' => 'MK',
            'Madagascar' => 'MG',
            'Malawi' => 'MW',
            'Malaysia' => 'MY',
            'Maldives' => 'MV',
            'Mali' => 'ML',
            'Malta' => 'MT',
            'Marshall Islands' => 'MH',
            'Martinique' => 'MQ',
            'Mauritania' => 'MR',
            'Mauritius' => 'MU',
            'Mayotte' => 'YT',
            'Mexico' => 'MX',
            'Micronesia' => 'FM',
            'Moldova' => 'MD',
            'Monaco' => 'MC',
            'Mongolia' => 'MN',
            'Montenegro' => 'ME',
            'Montserrat' => 'MS',
            'Morocco' => 'MA',
            'Mozambique' => 'MZ',
            'Myanmar' => 'MM',
            'Namibia' => 'NA',
            'Nauru' => 'NR',
            'Nepal' => 'NP',
            'Netherlands' => 'NL',
            'New Caledonia' => 'NC',
            'New Zealand' => 'NZ',
            'Nicaragua' => 'NI',
            'Niger' => 'NE',
            'Nigeria' => 'NG',
            'Niue' => 'NU',
            'Norfolk Island' => 'NF',
            'North Korea' => 'KP',
            'Northern Mariana Islands' => 'MP',
            'Norway' => 'NO',
            'Oman' => 'OM',
            'Pakistan' => 'PK',
            'Palau' => 'PW',
            'Palestinian Territory' => 'PS',
            'Panama' => 'PA',
            'Papua New Guinea' => 'PG',
            'Paraguay' => 'PY',
            'Peru' => 'PE',
            'Philippines' => 'PH',
            'Pitcairn' => 'PN',
            'Poland' => 'PL',
            'Portugal' => 'PT',
            'Puerto Rico' => 'PR',
            'Qatar' => 'QA',
            'Republic of the Congo' => 'CG',
            'Reunion' => 'RE',
            'Romania' => 'RO',
            'Russia' => 'RU',
            'Rwanda' => 'RW',
            'Saint Barthelemy' => 'BL',
            'Saint Helena' => 'SH',
            'Saint Kitts and Nevis' => 'KN',
            'Saint Lucia' => 'LC',
            'Saint Martin' => 'MF',
            'Saint Pierre and Miquelon' => 'PM',
            'Saint Vincent and the Grenadines' => 'VC',
            'Samoa' => 'WS',
            'San Marino' => 'SM',
            'Sao Tome and Principe' => 'ST',
            'Saudi Arabia' => 'SA',
            'Senegal' => 'SN',
            'Serbia' => 'RS',
            'Seychelles' => 'SC',
            'Sierra Leone' => 'SL',
            'Singapore' => 'SG',
            'Sint Maarten' => 'SX',
            'Slovakia' => 'SK',
            'Slovenia' => 'SI',
            'Solomon Islands' => 'SB',
            'Somalia' => 'SO',
            'South Africa' => 'ZA',
            'South Georgia and the South Sandwich Islands' => 'GS',
            'South Korea' => 'KR',
            'South Sudan' => 'SS',
            'Spain' => 'ES',
            'Sri Lanka' => 'LK',
            'Sudan' => 'SD',
            'Suriname' => 'SR',
            'Svalbard and Jan Mayen' => 'SJ',
            'Swaziland' => 'SZ',
            'Sweden' => 'SE',
            'Switzerland' => 'CH',
            'Syria' => 'SY',
            'Taiwan' => 'TW',
            'Tajikistan' => 'TJ',
            'Tanzania' => 'TZ',
            'Thailand' => 'TH',
            'Togo' => 'TG',
            'Tokelau' => 'TK',
            'Tonga' => 'TO',
            'Trinidad and Tobago' => 'TT',
            'Tunisia' => 'TN',
            'Turkey' => 'TR',
            'Turkmenistan' => 'TM',
            'Turks and Caicos Islands' => 'TC',
            'Tuvalu' => 'TV',
            'U.S. Virgin Islands' => 'VI',
            'Uganda' => 'UG',
            'Ukraine' => 'UA',
            'United Arab Emirates' => 'AE',
            'United Kingdom' => 'GB',
            'United States' => 'US',
            'United States Minor Outlying Islands' => 'UM',
            'Uruguay' => 'UY',
            'Uzbekistan' => 'UZ',
            'Vanuatu' => 'VU',
            'Vatican' => 'VA',
            'Venezuela' => 'VE',
            'Vietnam' => 'VN',
            'Wallis and Futuna' => 'WF',
            'Western Sahara' => 'EH',
            'Yemen' => 'YE',
            'Zambia' => 'ZM',
            'Zimbabwe' => 'ZW',
        ];
        if(isset($countries[$country_name])){
            return $countries[$country_name];
        }

        return 'SA';
    }
    function updateTransactionsInHyperpay($invoice_number,$transaction_ids){
        $this->url = env('HYPERPAY_PAYMENT_URL') . 'api/hyperpay/update-transactions';
        $data = [
            'invoice_number' => $invoice_number,
            'transaction_ids' => $transaction_ids
        ];
        $updateTransactions = guzzleRequester($this->url, $this->headers, 'POST', $data);
        return $updateTransactions;
    }
}
