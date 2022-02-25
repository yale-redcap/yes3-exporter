<?php

namespace Yale\Yes3FieldMapper;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/**
 * an autoloader for Yes3 classes and traits
 */
require "autoload.php";

/**
 * defines and enums
 */
require "defines/yes3_defines.php";

use REDCap;

class Yes3FieldMapper extends \ExternalModules\AbstractExternalModule
{
    public $project_id = 0;
    public $RecordIdField = "";
    public $isLongitudinal = "";
    public $username = "";
    public $serviceUrl = "";
    public $documentationUrl = "";
    public $specifications = [];
    public $eventPrefixes = [];
    private $token = "";

    use Yes3Trait;

    public function __construct() {

        parent::__construct(); // call parent (AbstractExternalModule) constructor

        if ( $_GET['pid'] ){

            $this->project_id = Yes3::getREDCapProjectId();

            $this->username = $this->getUser()->getUsername();
            $this->serviceUrl = $this->getUrl('services/services.php');
            $this->documentationUrl = $this->getUrl('plugins/yes3_exporter_documentation.php');

            $this->RecordIdField = \REDCap::getRecordIdField();
            $this->isLongitudinal = \REDCap::isLongitudinal();

            $this->token = "this-should-be-private";

            $this->getSpecificationsFromSettings();

            $this->setEventPrefixes();
        }
    }

    /**
     * ensures a valid data_elements specification, no matter what the user put into the EM setting
     */
    public function validated_data_elements( $elements )
    {
        if ( !is_array( $elements ) ){
            return [];
        }
        
        $t = [];

        foreach ($elements as $element){

            if ( isset($element['name']) && isset($element['type']) && isset($element['description']) ){

                $e = [
                    'name' => Yes3::alphaNumericString( $element['name'] )
                    , 'type' => Yes3::alphaNumericString( $element['type'] )
                    , 'description' => Yes3::alphaNumericString( $element['description'] )
                    , 'format' => ( isset($element['format']) ) ? Yes3::alphaNumericString($element['description']) : ""
                ];

                if ( isset($element['valueset']) && is_array($element['valueset']) ) {
                    $v = [];
                    foreach( $element['valueset'] as $valuesetItem ){
                        if ( isset( $valuesetItem['value']) && isset( $valuesetItem['label']) ) {
                            $v[] = [
                                'value' => Yes3::alphaNumericString( $valuesetItem['value'] ),
                                'label' => Yes3::escapeHtml( $valuesetItem['label'] )
                            ];
                        }
                    }

                    if ( count($v) > 0 ){
                        $e['valueset'] = $v;
                        $e['format'] = "valueset";
                    } else {
                        if ( $e['format'] === "valueset" ){
                            $e['format'] = "";
                        }
                    }
                }

                $t[] = $e;
            }
        }

        return $t;
    }

    public function getSpecificationsFromSettings()
    {
        $this->specifications = [];
        /*
        if ( $json = $this->getProjectSetting('specifications-json') ){
            $this->specifications = json_decode( htmlentities($json, ENT_NOQUOTES), true )['specifications'];
        }
        */

        $specification = $this->getProjectSetting('specification');
        $specification_name = $this->getProjectSetting('specification-name');
        $specification_description = $this->getProjectSetting('specification-description');
        $specification_export_layout = $this->getProjectSetting('specification-export-layout');
        $specification_data_elements_json = $this->getProjectSetting('specification-data-elements');

        for ($i=0; $i<count($specification); $i++){

            $specification_data_elements = json_decode( $specification_data_elements_json[$i], true );

            $this->specifications[$i] = [
                'name' => Yes3::alphaNumericString($specification_name[$i]),
                'description' => Yes3::escapeHtml($specification_description[$i]),
                'export_layout' => ($specification_export_layout[$i]) ? $specification_export_layout[$i]:"H",
                'data_elements' => $this->validated_data_elements( $specification_data_elements )
            ];
        }

        /*
        Yes3::logDebugMessage($this->project_id, print_r($specification, true), 'specification');
        Yes3::logDebugMessage($this->project_id, print_r($specification_name, true), 'specification_name');
        Yes3::logDebugMessage($this->project_id, print_r($data_elements_json, true), 'specification_data_elements');
        */
    }

    /* ==== HOOKS ==== */

    public function redcap_module_link_check_display( $project_id, $link )
    {
        return $link; // noop for now
    }

    public function redcap_module_save_configuration($project_id)
    {
        $this->updateEventPrefixes( true );
    }

    public function updateEventPrefixes( $force=false )
    {        
        $events = REDCap::getEventNames(true);

        $maxUniquePrefixLen = 8;
        $uniquePrefixLen = 0;
        $k = 1;
        while ( $k <= $maxUniquePrefixLen && !$uniquePrefixLen ){
            $prefixes = [];
            $uniquePrefixLen = $k;
            foreach ( $events as $event_id=>$event_name ){
                $event_name = str_replace("_", "", $event_name);
                if ( !ctype_lower($event_name[0]) ){
                    $event_name = "e" . $event_name;
                }
                $prefix = substr($event_name, 0, $uniquePrefixLen);
                if ( in_array($prefix, $prefixes, true) ){
                    $uniquePrefixLen = 0;
                    break;
                }
                $prefixes[$event_id] = $prefix;
            }
            $k++;
        }

        $event_prefixes = [];

        foreach( $events as $event_id => $event_name ){

            $strEventId = (string) $event_id;

            if ( isset($this->eventPrefixes[$strEventId]['event_prefix']) && !$force ){

                $prefix = $this->eventPrefixes[$strEventId]['event_prefix'];
            }
            else {

                if ( $uniquePrefixLen ){
                    $prefix = $prefixes[$event_id];
                }
                else {
                    $prefix = "e" . $strEventId;
                }
            }

            $event_prefixes[$strEventId] = ['event_id'=>$strEventId, 'event_name'=>$event_name, 'event_prefix'=>$prefix];        
        }

        return $this->saveEventPrefixes( $event_prefixes );
    }

    public function saveEventPrefixes( $event_prefixes )
    {
        Yes3::logDebugMessage($this->project_id, print_r($event_prefixes, true), "saveEventPrefixes");

        $logId = $this->log(
            "Event prefixes saved",
            [
                "setting" => "event-prefixes",
                "user" => $this->username,
                "event_prefixes" => json_encode( $event_prefixes )
            ]
        );

        if ( $logId ){
            $this->setEventPrefixes();
        }
    }

    public function getEventPrefixesRecord()
    {    
        $fields = "log_id, message, user, timestamp, event_prefixes";

        $pSql = "
            SELECT {$fields}
            WHERE project_id=? AND setting='event-prefixes'
            ORDER BY timestamp DESC
        ";

        $params = [$this->project_id];

        return $this->queryLogs($pSql, $params)->fetch_assoc();
    }

    private function setEventPrefixes()
    {
        if ( $eventPrefixesRecord = $this->getEventPrefixesRecord() ){

            $this->eventPrefixes = json_decode( $eventPrefixesRecord['event_prefixes'], true);
        }
        else {

            $this->eventPrefixes = [];
        }
    }
}
