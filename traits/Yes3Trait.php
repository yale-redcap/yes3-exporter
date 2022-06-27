<?php

namespace Yale\Yes3FieldMapper;

use REDCap;

trait Yes3Trait {

    public $greetingsFromYes3Trait = "hi mom";

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
                $props[$propKey] = "json encoding failed for {$propKey}: " . json_last_error_msg();
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

    public function yes3UserRights()
    {
        $isDesigner = ( $this->getUser()->hasDesignRights() ) ? 1:0;

        $user = $this->getUser()->getRights();

        //Yes3::logDebugMessage($this->project_id, print_r($user, true), "user rights");

        /**
         * The rank order of export permission codes
         * 
         * 0 - no access (export code = 0)
         * 1 - de-identified: no identifiers, dates or text fields (export code = 2)
         * 2 - no identifiers (export code = 3)
         * 3 - full access (export code = 1)
         */
        $exportPermRank = [0, 3, 1, 2];

        $formPermString = str_replace("[", "", $user['data_entry']);

        $formPerms = explode("]", $formPermString);
        $formPermissions = [];
        foreach( $formPerms as $formPerm){

            if ( $formPerm ){
                
                $formPermParts = explode(",", $formPerm);
                $formPermissions[ $formPermParts[0] ] = ($isDesigner) ? 1 : (int) $formPermParts[1];
            }
        }

        /**
         * Export permissions differ as of REDCap v12(!)
         */

        $formExportPermissions = [];

        $exporter = $isDesigner;

        if ( $isDesigner ){

            $export_tool = 1;
        }
        else {

            $export_tool = (int)$user['data_export_tool']; // this is always blank in v12, have to use form-specific
            if ( !$export_tool ) $export_tool = 0; // I'm paranoid
        }

        if ( isset($user['data_export_instruments'])) {

            //Yes3::logDebugMessage($this->project_id, print_r($user['data_export_instruments'], true), "user[data_export_instruments]");

            $formExportPermString = str_replace("[", "", $user['data_export_instruments']);

            $formExportPerms = explode("]", $formExportPermString);

            foreach( $formExportPerms as $formExportPerm){

                if ( $formExportPerm ){

                    $formExportPermParts = explode(",", $formExportPerm);

                    $xPerm = (int)$formExportPermParts[1];

                    if ( $exportPermRank[$xPerm] > $exportPermRank[$export_tool] ){

                        $export_tool = $xPerm;
                    }

                    if ( $xPerm > 0 && $exporter === 0 ){

                        $exporter = 1;
                    }
                    
                    $formExportPermissions[ $formExportPermParts[0] ] = $xPerm;
                }
            }
        }
        // pre-v12
        else {

            // create the v12-style form export permission array, with each instrument having the global permission
            foreach ( array_keys($formPermissions) as $instrument){

                $formExportPermissions[$instrument] = $export_tool;
            }
            $exporter = ( $export_tool > 0 ) ? 1 : 0;
        }

        /**
         * set export permission to "none" for any form the user is not allowed to view
         */
        foreach ( $formPermissions as $form_name=>$formperm){

            if ( !$formperm ){

                $formExportPermissions[$form_name] = 0;
            }
        }

        //Yes3::logDebugMessage($this->project_id, print_r($formPermissions, true), "form permissions");
        
        return [

            'username' => $this->getUser()->getUsername(),
            'isDesigner' => ( $this->getUser()->hasDesignRights() ) ? 1:0,
            'isSuper' => ( $this->getUser()->isSuperUser() ) ? 1:0,
            'group_id' => (int)$user['group_id'],
            'dag' => ( $user['group_id'] ) ? REDCap::getGroupNames(true, $user['group_id']) : "",
            'export' => $export_tool,
            'import' => (int)$user['data_import_tool'],
            'api_export' => (int)$user['api_export'],
            'api_import' => (int)$user['api_import'],
            'form_permissions' => $formPermissions,
            'form_export_permissions' => $formExportPermissions,
            'exporter' => $exporter
        ];
    }

    public function getCodeFor( string $libname, bool $includeHtml=false ):string
    {
        $s = "";
        $js = "";
        $css = "";
        
        $s .= "\n<!-- Yes3 getCodeFor: {$libname} -->";
        
        $js .= file_get_contents( $this->getModulePath()."js/yes3.js" );  
        $js .= file_get_contents( $this->getModulePath()."js/common.js" );  
        $js .= file_get_contents( $this->getModulePath()."js/{$libname}.js" );

        $js .= "\n" . $this->initializeJavascriptModuleObject() . ";";

        $js .= "\nYES3.moduleObject = " . $this->getJavascriptModuleObjectName() . ";";

        $js .= "\nYES3.moduleObjectName = '" . $this->getJavascriptModuleObjectName() . "';";

        $js .= "\nYES3.moduleProperties = " . $this->objectProperties() . ";\n";

        //$js .= "\nYES3.REDCapUserRights = " . json_encode( $this->getUser()->getRights() ) . ";\n";

        $js .= "\nYES3.userRights = " . json_encode( $this->yes3UserRights() ) . ";\n";

        $css .= file_get_contents( $this->getModulePath()."css/yes3.css" );
        $css .= file_get_contents( $this->getModulePath()."css/common.css" );
        $css .= file_get_contents( $this->getModulePath()."css/{$libname}.css" );

        if ( $js ) $s .= "\n<script>{$js}</script>";

        if ( $css ) $s .= "\n<style>{$css}</style>";

        if ( $includeHtml ){
            $s .= file_get_contents( $this->getModulePath()."html/yes3.html" );
        }

        print $s;

        return $s;
    }

        /* ==== ERROR LOGGING ==== */

        public function logException( string $message, \Exception $e )
        {
            $exceptionReport = "message: " . $e->getMessage()
                . "\nFile: " . $e->getFile()
                . "\nLine: " . $e->getLine()
                . "\nTrace: " . $e->getTraceAsString()
            ;
    
            $params = [
                'username' => $this->username,
                'log_entry_type' => EMLOG_TYPE_ERROR_REPORT,
                'exception_report' => $exceptionReport,
            ];
    
            $log_id = $this->log(
                $message,
                $params
            );
    
            return $log_id;
        }
    

}