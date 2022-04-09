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
        $user = $this->getUser()->getRights();

        $formPermString = str_replace("[", "", $user['data_entry']);

        $formPerms = explode("]", $formPermString);
        $formPermissions = [];
        foreach( $formPerms as $formPerm){

            if ( $formPerm ){
                
                $formPermParts = explode(",", $formPerm);
                $formPermissions[ $formPermParts[0] ] = $formPermParts[1];
            }
        }

        return [

            'username' => $this->getUser()->getUsername(),
            'isDesigner' => ( $this->getUser()->hasDesignRights() ) ? 1:0,
            'isSuper' => ( $this->getUser()->isSuperUser() ) ? 1:0,
            'group_id' => (int)$user['group_id'],
            'dag' => ( $user['group_id'] ) ? REDCap::getGroupNames(true, $user['group_id']) : "",
            'export' => (int)$user['data_export_tool'],
            'import' => (int)$user['data_import_tool'],
            'api_export' => (int)$user['api_export'],
            'api_import' => (int)$user['api_import'],
            'form_permissions' => $formPermissions
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

}