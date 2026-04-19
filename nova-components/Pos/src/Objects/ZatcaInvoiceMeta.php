<?php

namespace Surelab\Pos\Objects;

class ZatcaInvoiceMeta {

    /**
     * @var string //json_encoded
     */
    protected $is_reported_to_zatca;
    /**
     * @var string //json_encoded
     */
    protected $is_reported_to_zatca_credit_note;

    public function __construct ($is_reported_to_zatca, $is_reported_to_zatca_credit_note) {
        $is_reported_to_zatca !== null ? array_push($this->is_reported_to_zatca, $is_reported_to_zatca) : null;                                                                            
        $is_reported_to_zatca_credit_note !== null ? array_push($this->is_reported_to_zatca_credit_note,
                                                     $is_reported_to_zatca_credit_note) : null;
    }

    public function setInvoice ($is_reported_to_zatca) {
            if($is_reported_to_zatca !== null) {
                array_push($this->is_reported_to_zatca, $is_reported_to_zatca);
            }
    }

    public function setCreditNote ($is_reported_to_zatca_credit_note) {
            if($is_reported_to_zatca_credit_note !== null) {
                array_push($this->is_reported_to_zatca_credit_note, $is_reported_to_zatca_credit_note);
            }
    }

    public function get() {
        return [
            $is_reported_to_zatca,
            $is_reported_to_zatca_credit_note
        ];
    }
}