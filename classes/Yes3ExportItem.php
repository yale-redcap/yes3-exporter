<?php

namespace Yale\Yes3FieldMapper;

class Yes3ExportItem {

    public $var_name = "";
    public $var_type = "";
    public $var_label = "";
    public $valueset = [];

    public $origin = "";
    public $redcap_field_name = "";
    public $redcap_form_name = "";
    public $redcap_event_id = "";
    public $redcap_event_name = "";

    public $non_missing_count = "";
    public $min_length = "";
    public $max_length = "";
    public $min_value = "";
    public $max_value = "";
    public $sum_of_values = "";
    public $sum_of_squared_values = "";

    public function __construct( $exportItemProperties )
    {
        foreach ( $exportItemProperties as $propName => $propValue ) {

            $this->$propName = $propValue;
        }
    }
}