<?php

namespace Yale\Yes3FieldMapper;

class Yes3ExportItem {

    public $var_name = "";
    public $var_type = "";
    public $var_label = "";
    public $valueset = [];

    public $origin = "";
    public $redcap_field_name = "";
    public $redcap_events = [];
    public $redcap_form_name = "";
    public $redcap_event_id = "";
    public $redcap_event_name = "";

    public $non_missing_count = 0;
    public $min_length = 32768;
    public $max_length = 0;
    public $min_value = NULL;
    public $max_value = NULL;
    public $sum_of_values = NULL;
    public $sum_of_squared_values = NULL;
    public $mean = NULL;
    public $standard_deviation = NULL;
    public $formatted_min_value = NULL;
    public $formatted_max_value = NULL;
    public $formatted_mean = NULL;
    public $frequency_table = [];

    public function __construct( $exportItemProperties )
    {
        foreach ( $exportItemProperties as $propName => $propValue ) {

            $this->$propName = $propValue;
        }
    }
}