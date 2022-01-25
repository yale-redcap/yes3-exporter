<?php

namespace Yale\Yes3FieldMapper;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/**
 * an autoloader for Yes3 classes and traits
 */
require "autoload.php";

use Exception;
use REDCap;
use Yale\Yes3\Yes3;

define('ONE_DAY', 24*60*60);
define('YES3_FORM_NAME', "yes3");

class Yes3FieldMapper extends \ExternalModules\AbstractExternalModule
{
    public $project_id = 0;
    public $RecordIdField = "";
    public $isLongitudinal = "";
    public $username = "";
    public $serviceUrl = "";
    public $specifications = [];
    private $token = "";

    public function __construct() {

        parent::__construct(); // call parent (AbstractExternalModule) constructor

        if ( $_GET['pid'] ){

            $this->project_id = Yes3::getREDCapProjectId();

            $this->username = USERID;
            $this->serviceUrl = $this->getUrl('services/yes3_fieldmapper_services.php');

            $this->RecordIdField = \REDCap::getRecordIdField();
            $this->isLongitudinal = \REDCap::isLongitudinal();

            $this->token = "this-should-be-private";

            $this->getSpecificationsFromSettings();
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

    public function objectProperties()
    {
        $propKeys = [];

        /**
         * A ReflectionObject is apparently required to distinuish the non-private properties of this object
         * https://www.php.net/ReflectionObject
         */
        $publicProps = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC+\ReflectionProperty::IS_PROTECTED);

        foreach( $publicProps as $rflxnProp){
            $propKeys[] = $rflxnProp->name;
        }
         
        $props = [ 'CLASS' => __CLASS__ ];

        foreach ( $propKeys as $propKey ){

            $json = json_encode($this->$propKey);

            /**
             * some properties can't be json-encoded...
             */
            if ( $json===false ){
                $props[$propKey] = "json encoding failed for this property: " . json_last_error_msg();
            }
            else {
                $props[$propKey] = $this->$propKey;
            }
        }

        if ( !$json = json_encode($props) ){
            return json_encode(['message'=>json_last_error_msg()]);
        }
        
        return $json;
    }

    public function getCodeFor( string $libname, bool $includeHtml=false ):string
    {
        $s = "\n<!-- Fieldmapper getCodeFor: {$libname} -->";

        $js = "\nlet yes3ModuleProperties = " . $this->objectProperties() . ";\n";

        $css = "";

        $js .= file_get_contents( $this->getModulePath()."js/yes3.js" );  
        $js .= file_get_contents( $this->getModulePath()."js/{$libname}.js" );

        //$css .= file_get_contents( $this->getModulePath()."css/yes3_dark.css" );
        $css .= file_get_contents( $this->getModulePath()."css/yes3.css" );
        $css .= file_get_contents( $this->getModulePath()."css/{$libname}.css" );

        if ( $js ) $s .= "\n<script>{$js}</script>";

        if ( $css ) $s .= "\n<style>{$css}</style>";

        if ( $includeHtml ){
            $s .= file_get_contents( $this->getModulePath()."html/yes3.html" );
        }

        print $s;

        return $s;
    }

}
