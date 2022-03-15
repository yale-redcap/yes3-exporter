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
    public $export_data_dictionary = [];
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
        //$this->mapping_specification = $exportSettings['mapping_specification'] ?? [];
    }

    public function addExportItem( $exportItemProperties )
    {

        $this->export_items[] = new Yes3ExportItem($exportItemProperties);
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
}