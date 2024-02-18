<?php

namespace Yale\Yes3FieldMapper;

class Yes3Export {

    public $export_name = "";
    public $export_uuid = "";
    public $export_layout = "";
    public $export_selection = "";
    public $export_criterion_field = "";
    public $export_criterion_event = "";
    public $export_criterion_value = "";
    public $export_target = "";
    public $export_target_folder = "";
    public $export_max_label_length = "";
    public $export_max_text_length = "";
    public $export_inoffensive_text = "";
    public $export_shift_dates = "";
    public $export_hash_recordid = "";

    public $export_remove_phi = "";
    public $export_remove_dates = "";
    public $export_remove_freetext = "";
    public $export_remove_largetext = "";

    public $export_event_list = [];

    //public $export_data_dictionary = [];
    //public $mapping_specification = "";

    public $export_items = [];

    public function __construct( $exportSettings )
    {
        $this->export_name = $exportSettings['export_name'] ?? "noname";
        $this->export_uuid = $exportSettings['export_uuid'] ?? "";
        $this->export_layout = $exportSettings['export_layout'] ?? "";
        $this->export_selection = $exportSettings['export_selection'] ?? "";
        $this->export_criterion_field = $exportSettings['export_criterion_field'] ?? "";
        $this->export_criterion_event = $exportSettings['export_criterion_event'] ?? "";
        $this->export_criterion_value = $exportSettings['export_criterion_value'] ?? "";
        $this->export_target = $exportSettings['export_target'] ?? "";
        $this->export_target_folder = $exportSettings['export_target_folder'] ?? "";
        $this->export_max_label_length = $exportSettings['export_max_label_length'] ?? "0";
        $this->export_max_text_length = $exportSettings['export_max_text_length'] ?? "0";
        $this->export_inoffensive_text = $exportSettings['export_inoffensive_text'] ?? "0";
        $this->export_shift_dates = $exportSettings['export_shift_dates'] ?? "0";
        $this->export_hash_recordid = $exportSettings['export_hash_recordid'] ?? "0";

        $this->export_remove_phi = $exportSettings['export_remove_phi'] ?? "0";
        $this->export_remove_dates = $exportSettings['export_remove_dates'] ?? "0";
        $this->export_remove_freetext = $exportSettings['export_remove_freetext'] ?? "0";
        $this->export_remove_largetext = $exportSettings['export_remove_largetext'] ?? "0";
        
        //$this->mapping_specification = $exportSettings['mapping_specification'] ?? [];
    }

    public function addExportItem( $exportItemProperties )
    {

        // the record ID field should not be associated with an event
        if ( isset($exportItemProperties['redcap_field_name']) && $exportItemProperties['redcap_field_name']===\REDCap::getRecordIdField() ){

            $exportItemProperties['redcap_event_id'] = 0;
        }

        $this->export_items[] = new Yes3ExportItem($exportItemProperties);

        if ( isset($exportItemProperties['redcap_event_id']) ) {

            $this->updateEventList( $exportItemProperties['redcap_event_id'] );
        }
    }

    public function itemInExport($var_name)
    {
        for ( $i=0; $i<count($this->export_items); $i++){

            if ( $var_name===$this->export_items[$i]->var_name ){
                
                return true;
            }
        }
        return false;
    }

    public function updateExportItemEvents($var_name, $event_id)
    {
        $event_id = (int) $event_id;

        if ( !$event_id ){

            return false;
        }

        $this->updateEventList( $event_id );
        
        for ( $i=0; $i<count($this->export_items); $i++){

            if ( $var_name===$this->export_items[$i]->var_name ){
                
                $this->export_items[$i]->redcap_events[] = $event_id;

                return true;
            }
        }
        return false;
    }

    private function updateEventList($event_id)
    {
        $event_id = (int) $event_id;

        if ( $event_id ){

            if ( !in_array($event_id, $this->export_event_list) ){

                $this->export_event_list[] = $event_id;
            }
        }
    }
}