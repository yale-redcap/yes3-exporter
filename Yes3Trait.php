<?php

namespace Yale\Yes3FieldMapper;

use REDCap;

trait Yes3Trait {

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

    public function yes3UserRights( $designerHasExportRights=false )
    {
        $isDesigner = ( $this->getUser()->hasDesignRights() ) ? 1:0;

        $user = $this->getUser()->getRights();

        $longitudinal = REDCap::isLongitudinal();

        //$this->logDebugMessage($this->project_id, print_r($user, true), "user rights");

        /**
         * The rank order of export permission codes
         * 
         * 0 - no access (export code = 0)
         * 1 - de-identified: no identifiers, dates or text fields (export code = 2)
         * 2 - no identifiers (export code = 3)
         * 3 - full access (export code = 1)
         */
        $exportPermRank = [0, 3, 1, 2];

        /**
         * DATA ENTRY PERMISSIONS
         * Feb 24: for longitudinal projects, only forms that are on the event grid are included in the form permissions array
         */

        $formPermString = str_replace("[", "", $user['data_entry']);

        $formPerms = explode("]", $formPermString);
        $formPermissions = [];
        foreach( $formPerms as $formPerm){

            // not sure why this is necessary, I guess belts and suspenders?
            if ( $formPerm ){

                $formPermParts = explode(",", $formPerm);
                $form_name = $formPermParts[0];

                // for longitudinal projects, only include forms that are on the event grid
                if ( !$longitudinal || $this->getREDCapEventsForForm($form_name) ){

                    $formPermissions[ $form_name ] = ($isDesigner) ? 1 : (int) $formPermParts[1];
                }
            }
        }

        /**
         * EXPORT PERMISSIONS
         * Feb 24: for longitudinal projects, only forms that are on the event grid are included in the form permissions array
         * 
         * The REDCap Export permission model changed with v12, so we have to handle both pre-v12 and v12+ permissions
         * 
         */

        $formExportPermissions = [];

        $exporter = ( $designerHasExportRights ) ? $isDesigner : 0;

        if ( $isDesigner && $designerHasExportRights ){

            $export_tool = 1; // simulated pre-v12 property
        }
        else {

            // this is always blank in v12+, have to build it while sweeping the forms
            // i.e. we set it to the highest-ranked permission we find for any form
            $export_tool = (int)$user['data_export_tool']; 
            if ( !$export_tool ) $export_tool = 0; // I'm paranoid
        }

        // 'data_export_instruments' is a v12+ property
        if ( isset($user['data_export_instruments'])) {

            //$this->logDebugMessage($this->project_id, print_r($user['data_export_instruments'], true), "user[data_export_instruments]");

            $formExportPermString = str_replace("[", "", $user['data_export_instruments']);

            $formExportPerms = explode("]", $formExportPermString);

            foreach( $formExportPerms as $formExportPerm){

                if ( $formExportPerm ){

                    $formExportPermParts = explode(",", $formExportPerm);

                    $xPerm = (int)$formExportPermParts[1];

                    $form_name = $formExportPermParts[0];

                    // for longitudinal projects, only include forms that are on the event grid
                    if ( !$longitudinal || $this->getREDCapEventsForForm($form_name) ){

                        // set the simulated pre-v12-style 'export_tool' property to the highest-ranked permission we find
                        if ( $exportPermRank[$xPerm] > $exportPermRank[$export_tool] ){

                            $export_tool = $xPerm;
                        }

                        if ( $xPerm > 0 && $exporter === 0 ){

                            $exporter = 1;
                        }
                        
                        $formExportPermissions[ $form_name ] = $xPerm;
                    }
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

        //$this->logDebugMessage($this->project_id, print_r($formPermissions, true), "form permissions");
        
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

    /* ==== STATIC METHODS ==== */

    /**
     * V13, V14+ compatible method for getting the project_id
     * 
     * @param string $project_id 
     * @return mixed 
     */
    public function getDataTable( $project_id=0 ){

        if ( !is_numeric($project_id) || $project_id < 1 ) $project_id = (int) $this->getProjectId();

        if ( method_exists('REDCap', "getDataTable") ) {

            //$this->logDebugMessage($project_id, "using REDCap::getDataTable: project_id={$project_id}, dataTable=".REDCap::getDataTable($project_id), "getDataTable");
            
            return REDCap::getDataTable($project_id);
        }

        return "redcap_data";
    }

    // the framework getDAG crashes for longitudinal studies
    public function getGroupIdForRecord($recordId, $pid=0){

        if ( !$pid ){

            $pid = $this->getProjectId();
        }

        if ( !$pid ){

            return null;
        }

        $redcap_data = $this->getDataTable($pid);

        return $this->fetchValue("select value from $redcap_data where project_id = ? and record = ? and field_name = ? limit 1", [$pid, $recordId, '__GROUPID__']);
    }

    public function recordGenerator( $sql, $parameters = [] )
    {
        $resultSet = $this->query($sql, $parameters);

        while ($row = $resultSet->fetch_assoc()) {

            yield $row;
        }
    }

    public function fetchRecords($sql, $parameters = [])
    {

        $rows = [];
        $resultSet = $this->query($sql, $parameters);
        if ( $resultSet->num_rows > 0 ) {
            while ($row = $resultSet->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function sql_limit_1( $sql )
    {

        if ( stripos($sql, "LIMIT 1") === false ) {
            return $sql . " LIMIT 1";
        } else {
            return $sql;
        }

    }

    public function fetchRecord($sql, $parameters = [])
    {

        return $this->query($this->sql_limit_1($sql), $parameters)->fetch_assoc();
    }

    public function fetchValue($sql, $parameters = [])
    {
        return $this->query($this->sql_limit_1($sql), $parameters)->fetch_row()[0];
    }

    public function tableExists($table_name)
    {
        $dbname = $this->fetchValue("SELECT DATABASE() AS DB");
        if ( !$dbname ) return false;
        $sql = "SELECT COUNT(*) FROM information_schema.tables"
            ." WHERE table_schema=?"
            ." AND table_name=?"
        ;
        return $this->fetchValue($sql, [$dbname, $table_name]);
    }

    public function json_encode_pretty( $x )
    {
        return json_encode( $x, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT );
    }

    public function is_json_encodable( $x )
    {
        if ( json_encode( $x )===false ) return false;
        return true;
    }

    public function is_json_decodable( $s )
    {
        if ( json_decode( $s)===null ) return false;
        return true;
    }

    public function getFirstREDCapEventId(int $project_id=null)
    {
        if ( !$project_id ){
            $project_id = $this->getProjectId();
        }

        $sql = "SELECT e.event_id
        FROM redcap_events_metadata e
            INNER JOIN redcap_events_arms a ON a.arm_id=e.arm_id
        WHERE a.project_id=?
        ORDER BY e.day_offset, e.event_id
        LIMIT 1";

        return $this->fetchValue($sql, [$project_id]);
    }

    public function getREDCapEventIdForField( string $field_name, int $project_id=null )
    {
        return $this->getREDCapEventIdForForm( $this->getREDCapFormForField($field_name, $project_id) );
    }

    /**
    * Returns first event_id associated with form
    * Only useful if form is on a single event
    * 
    * function: getREDCapEventIdForForm
    * 
    * @param string $form_name
    * @param int|null $project_id
    * 
    * @return mixed
    * @throws Exception
    */
    public function getREDCapEventIdForForm( string $form_name, int $project_id=null )
    {
        if ( !$project_id ){
            $project_id = $this->getProjectId();
        }

        if ( REDCap::isLongitudinal() ) {     
            $sql = "SELECT e.event_id
            FROM redcap_events_metadata e
                INNER JOIN redcap_events_arms a ON a.arm_id=e.arm_id
                INNER JOIN redcap_events_forms ef ON ef.form_name=? AND ef.event_id=e.event_id
            WHERE a.project_id=?
            ORDER BY e.day_offset, e.event_id
            LIMIT 1";

            return $this->fetchValue($sql, [$form_name, $project_id]) ?? 0;
        }
        
        return $this->getFirstREDCapEventId($project_id);
    }

    public function getREDcapEventsForForm($form_name, $project_id=null)
    {
        if ( !$project_id ){
            $project_id = $this->getProjectId();
        }

        if ( !REDCap::isLongitudinal() ) return $this->getFirstREDCapEventId($project_id);

        $sql = "SELECT e.event_id
        FROM redcap_events_metadata e
            INNER JOIN redcap_events_arms a ON a.arm_id=e.arm_id
            INNER JOIN redcap_events_forms ef ON ef.form_name=? AND ef.event_id=e.event_id
        WHERE a.project_id=?
        ORDER BY e.day_offset, e.event_id";

        $eventRecords = $this->fetchRecords($sql, [$form_name, $project_id]);

        if ( !$eventRecords ) return [];

        return array_column($eventRecords, 'event_id');
    }

    public function getEventIdForDescription( int $project_id, string $descrip)
    {
        return (int) $this->fetchValue(
            "SELECT e.event_id
            from redcap_events_metadata e
            INNER join redcap_events_arms a on a.arm_id=e.arm_id
            where a.project_id=? and e.descrip=?",
            [$project_id, $descrip]
        );
    }

    public function getREDCapFormForField( string $field_name, int $project_id=null )
    {
        if ( !$project_id ){
            $project_id = $this->getProjectId();
        }

        $sql = "SELECT m.form_name
        FROM redcap_metadata m
        WHERE m.project_id=? AND m.field_name=?
        LIMIT 1";

        return $this->fetchValue($sql, [$project_id, $field_name]);
    }

    public function getREDCapValue( string $record, string $field_name, int $event_id=null, $instance=1 )
    {
        $project_id = $this->getProjectId();

        if ( !$event_id ) {
            $event_id = $this->getREDCapEventIdForField($field_name, $project_id);
        }

        $redcap_data = $this->getDataTable($project_id);

        $sql = "
    SELECT `value` 
    FROM $redcap_data 
    WHERE `project_id`=? AND `event_id`=? AND `record`=? AND `field_name`=? AND ifnull(instance, 1)=? LIMIT 1
    ";
        return $this->fetchValue($sql, [$project_id, $event_id, $record, $event_id, $instance]);
    }

    public function isRepeatingInstrument(string $form_name)
    {
            $sql = "SELECT COUNT(*) AS k
                    FROM redcap_events_repeat er
                        INNER JOIN redcap_events_metadata em ON em.event_id=er.event_id
                        INNER JOIN redcap_events_arms ea ON ea.arm_id=em.arm_id
                    WHERE ea.project_id=? AND er.form_name=?";
            
            return $this->fetchValue($sql, [$this->getProjectId(), $form_name]);
    }

    public function REDCapDateTimeString()
    {
        return strftime("%Y-%m-%d %H:%M");
    }

    public function timeStampString()
    {
        return strftime("%y%m%d%H%M%S");
    }

    public function inoffensiveFieldName( $s )
    {

        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        /**
         * @psalm-suppress InvalidReturnStatement
         */
        return preg_replace("/[^a-zA-Z0-9_]+/", "", str_replace(' ', '_', $s));
    }


    /**
    * lower case, alphanumeric (blanks converted to underscores)
    * suitable for REDCap field names
    * 
    * function: normalized_string
    * 
    * @param $s
    * 
    * @psalm-suppress InvalidNullableReturnType
    * @return string
    */
    public function normalized_string( $s )
    {

        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

    /**
        * @psalm-suppress InvalidReturnStatement
        */
    return preg_replace("/[^a-z0-9_]+/", "", strtolower(str_replace([' ', '-', '.'], '_', $s)));
    }

    /**
     * Converts every ASCII/UTF-8 quotation mark-like character to straight quote (including html entities)
     * 
     * adapted from:
     * https://stackoverflow.com/questions/20025030/convert-all-types-of-smart-quotes-with-php
     * 
     * function: straightQuoter
     * 
     * @param $s
     * 
     * @return string
     */
    public function straightQuoter( $s ):string
    {  

        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        $qSearch = [

            '"',

            // Windows codepage 1252

            "\xC2\x82", // U+0082⇒U+201A single low-9 quotation mark
            "\xC2\x84", // U+0084⇒U+201E double low-9 quotation mark
            "\xC2\x8B", // U+008B⇒U+2039 single left-pointing angle quotation mark
            "\xC2\x91", // U+0091⇒U+2018 left single quotation mark
            "\xC2\x92", // U+0092⇒U+2019 right single quotation mark
            "\xC2\x93", // U+0093⇒U+201C left double quotation mark
            "\xC2\x94", // U+0094⇒U+201D right double quotation mark
            "\xC2\x9B", // U+009B⇒U+203A single right-pointing angle quotation mark
        
            // Regular Unicode  
            
            "\x22"        , // U+0022 quotation mark (")
            "\x60"        , // U+0060 grave accent

            "\xC2\xB4"    , // U+00B4 acute accent
            "\xC2\xAB"    , // U+00AB left-pointing double angle quotation mark
            "\xC2\xBB"    , // U+00BB right-pointing double angle quotation mark
            "\xE2\x80\x98", // U+2018 left single quotation mark
            "\xE2\x80\x99", // U+2019 right single quotation mark
            "\xE2\x80\x9A", // U+201A single low-9 quotation mark
            "\xE2\x80\x9B", // U+201B single high-reversed-9 quotation mark
            "\xE2\x80\x9C", // U+201C left double quotation mark
            "\xE2\x80\x9D", // U+201D right double quotation mark
            "\xE2\x80\x9E", // U+201E double low-9 quotation mark
            "\xE2\x80\x9F", // U+201F double high-reversed-9 quotation mark
            "\xE2\x80\xB9", // U+2039 single left-pointing angle quotation mark
            "\xE2\x80\xBA"  // U+203A single right-pointing angle quotation mark         
        ];

        return str_replace($qSearch, "'", $s);
    }

    /**
     * Tries to guarantee inoffensive text, suitable for labels or SAS text fields
     * Should be UTF-8 compatible
     * 
     * - trimmed
     * - stripped of HTML tags
     * - control chars (0-31, 127) converted to spaces
     * - all flavors of quotes converted to straight quote (apostrophe)
     * 
     * regexp from: https://stackoverflow.com/questions/1176904/how-to-remove-all-non-printable-characters-in-a-string
     * 
     * function: inoffensiveText
     * 
     * @param $s
     * @param int $maxLen
     * 
     * @return string
     */
    public function inoffensiveText( $s, $maxLen=0 ):string
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }
        
        $s = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $this->straightQuoter( strip_tags($s)) ); 

        if ( $maxLen ) return $this->truncate($s, $maxLen);

        return $s;       
    }

    /**
     * Allows ASCII alphanumerics
     * 
     * function: alphaNumericString
     * 
     * @param $s
     * 
     * @return string
     */
    public function alphaNumericString( $s ):string
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }
        
        return preg_replace("/[^a-zA-Z0-9_ ]+/", "", $s);
    }

    /**
     * Strips control characters, allows all UTF-8
     * all HTML tags stripped
     * 
     * function: printableEscHtmlString
     * 
     * @param mixed $s
     * @param int $maxLen
     * 
     * @return string|false|string[]|null
     */
    public function printableEscHtmlString( $s, $maxLen=0)
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        $s = preg_replace('/[\x00-\x1F\x7F]/u', '', strip_tags($s));

        if ( $maxLen ) return $this->truncate($s, $maxLen);

        return $s;       
    }

    public function escapeHtml( $s )
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        return REDCap::escapeHtml($s);
    }

    public function ellipsis( $s, $len=64 )
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        $s = trim($s);
        if ( $len > 0 &&  strlen($s) > $len-3 ) {
            return substr($s, 0, $len-3)."...";
        }
        return $s;
    }

    public function truncate( $s, $len=64 )
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        $s = trim($s);
        if ( $len > 0 && strlen($s) > $len) {
            return substr($s, 0, $len);
        }
        return $s;
    }


    /**
    * These SQL escaping functions will be retired
    * once parameterized queries are fully implemented.
    *
    */

    public function sql_string($x)
    {
        if (is_null($x)) {

            return "null";
        } 
        else if (strlen($x) == 0) {

            return "null";
        } 
        else if (is_numeric($x)) {

            return "'" . $x . "'";
        } 
        else {
            
            return "'" . db_real_escape_string($x) . "'";
        }
    }

    public function sql_datetime_string($x)
    {
        if (!$x) {
            return "null";
        } else {
            return "'" . strftime("%F %T", strtotime($x)) . "'";
        }
    }

    public function sql_date_string($x)
    {
        if (!$x) {
            return "null";
        } else {
            $d = strtotime($x);
            // if this didn't work, could be due to mm-dd-yyyy which doesn't fly
            if (!$d) {
                $date = str_replace('-', '/', $x);
                $d = strtotime($date);
            }
            if ($d) {
                return "'" . strftime("%F", $d) . "'";
            } else {
                return "null";
            }
        }
    }

    public function sql_timestamp_string()
    {
        return "'" . strftime("%F %T") . "'";
    }

    /*
    * LOGGING DEBUG INFO
    * Call this function to log messages intended for debugging, for example an SQL statement.
    * The log database must exist and its name stored in the DEBUG_LOG_TABLE constant.
    * Required columns: project_id(INT), debug_message_category(VARCHAR(100)), debug_message(TEXT).
    * (best to add an autoincrement id field). Sample table-create query:
    *
            CREATE TABLE ydcclib_debug_messages
            (
                debug_id               INT AUTO_INCREMENT PRIMARY KEY,
                project_id             INT                                 NULL,
                debug_message_category VARCHAR(100)                        NULL,
                debug_message          TEXT                                NULL,
                debug_timestamp        TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP
            );

        */

    public function logDebugMessage($project_id, $msg, $msgcat="") 
    {   
        if ( LOG_DEBUG_MESSAGES || !$this->tableExists(DEBUG_LOG_TABLE) ) return false;

        $sql = "INSERT INTO `".DEBUG_LOG_TABLE."` (project_id, debug_message, debug_message_category) VALUES (?,?,?)";

        return $this->query($sql, [$project_id, $msg, $msgcat]);
    }

}