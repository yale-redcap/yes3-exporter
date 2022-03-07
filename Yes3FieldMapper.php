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

    public function buildExportDataDictionary( $export_uuid )
    {
        /**
         * Event settings
         * 
         * [ {event_id, event_name, event_prefix}, ... ]
         * 
         * event_name is REDCap unique event name ("screen_arm_1")
         * 
         */
        $event_settings = $this->getEventSettings(); /* evcen abbreviations */
    
        /**
         * Export specification (settings):
         * 
         *       export_uuid,
         *       export_name,
         *       export_layout,
         *       export_selection,
         *       export_criterion_field,
         *       export_criterion_event,
         *       export_criterion_value,
         *       export_target,
         *       export_target_folder,
         *       mapping_specification,
         *       removed
         *      
         */
        $export = new Yes3Export( $this->getExportSpecification($export_uuid) ); 
    
        /**
         * export elements (stored separately from settings)
         * 
         * List of REDCap objects
         *  {
         *      yes3_fmapr_data_element_name: specification element name or 'redcap_element_[n]'
         *      element_origin: 'redcap' or 'specification'
         *      redcap_object_type: 'field' or 'form'
         *      redcap_event_id: event_id or 'all'
         *      redcap_field_name: if relevant (type is 'field')
         *      redcap_form_name: if relevant (type is 'form') - form name or 'all'
         *      spec_type: data type if from specification
         *      spec_format: display format if from specification
         * 
         *      values: list of REDCap-to-specification value mappings
         *              {
         *                  yes3_fmapr_lov_value: specification value
         *                  redcap_field_value: redcap value
         *              }
         *  }
         */
        //if ( !$map_record = getFieldMapRecord( $export_uuid ) ){
        //    return "no mappings";
        //}
        //$export_elements = json_decode($map_record['field_mappings'], true)['elements'];
    
        $export_elements = $this->getExportElements( $export_uuid );
    
        //return print_r($export_elements, true);
    
        /**
         * 
         * form_metadata: list of form objects
         *      {
         *      form_name,
         *      form_label,
         *      form_events: list of event objects {event_id, event_label},
         *      form_fields: list of field names,
         *      form_repeating: 1 or 0
         *      }
         * 
         * form_index: list index, keyed by form_name
         * 
         */
        //$forms = get_form_metadata_structures();
        $forms = $this->getFormMetadataStructures();
    
        /**
         * 
         * field_metadata: list of field objects
         *      {
         *          field_name
         *          field_label
         *          form_name
         *          field_type: REDCap element_type
         *          field_valueset: list of valueset objects {value, label}
         *      }
         * 
         * field_index: list index, keyed by field_name
         * 
         */
        //$fields = get_field_metadata_structures();
        $fields = $this->getFieldMetadataStructures();
    
        /**
         * DATA DICTIONARY
         * 
         * {
         *      var_name
         *      var_type ( INTEGER, FLOAT, NOMINAL, TEXT, DATE, DATETIME, TIME )
         *      var_label
         *      valueset [{value, label}, ...] as JSON string (NOMINAL only)
         * 
         *      for computation:
         * 
         *      source_field
         *      source_form
         *      origin ('specification', 'redcap')
         *      spec_value_map [{spec_value, redcap_value}, ... ] as JSON string
         * 
         *      for validation / code generation:
         * 
         *      non_missing_count
         *      min_length (TEXT)
         *      max_length (TEXT)
         *      min_value
         *      max_value
         *      sum_of_values
         *      sum_of_squared_values
         * }
         * 
         */

        /**
         * Start with recordid field; always the first column
         */
        $field_name = \REDCap::getRecordIdField();

        $this->addExportItem_REDCapField( $export, $field_name, Yes3::getREDCapEventIdForField($field_name), $fields, $forms, $event_settings );

        /**
         * If not horizontal, event is next column
         */
        if ( $export->export_layout !== "h" ){

            $this->addExportItem_otherProperty($export, "redcap_event", "REDCap Event");

            /**
             * If repeating, instance is next
             */
            if ( $export->export_layout === "r" ){

                $this->addExportItem_otherProperty($export, "redcap_repeat_instance", "REDCap Repeat Instance");
            }
        }
    
        foreach ($export_elements as $element){
    
            if ( $element['element_origin'] === 'specification' ) {
    
                $this->addExportItem_Specification( $export, $element, $event_settings );
            }
    
            elseif ( $element['redcap_field_name'] ) {
    
                $this->addExportItem_REDCapField( $export, $element['redcap_field_name'], $element['redcap_event_id'], $fields, $forms, $event_settings );
            }
    
            elseif ( $element['redcap_form_name'] ) {
    
                $this->addExportItem_REDCapForm( $export, $element['redcap_form_name'], $element['redcap_event_id'], $fields, $forms, $event_settings );
            }
        }

        $dd = [];

        foreach( $export->export_items as $export_item ){

            $dd[] = [
                'var_name' => $export_item->var_name,
                'var_label' => $export_item->var_label,
                'var_type' => $export_item->var_type,
                'valueset' => ( $export_item->valueset ) ? json_encode($export_item->valueset) : "",
                'origin' => $export_item->origin,
                'redcap_field_name' => $export_item->redcap_field_name,
                'redcap_form_name' => $export_item->redcap_form_name,
                'redcap_event_id' => $export_item->redcap_event_id,
                'redcap_event_name' => $export_item->redcap_event_name,
                'non_missing_count' => $export_item->non_missing_count,
                'min_length' => $export_item->min_length,
                'max_length' => $export_item->max_length,
                'min_value' => $export_item->min_value,
                'max_value' => $export_item->max_value,
                'sum_of_values' => $export_item->sum_of_values,
                'sum_of_squared_values' => $export_item->sum_of_squared_values   
            ];
        }

        //return print_r($event_settings, true);
        //return print_r($export, true);
        //return print_r($fields['field_index'], true);
        //return print_r($dd, true);
        //return "";

        return [
            'export_uuid' => $export_uuid,
            'export_name' => $export->export_name,
            'export_target_folder' => $export->export_target_folder,
            'export_data_dictionary' => $dd
        ];

        //$this->download($export->export_name . "_dd", $dd);
    
        //return count($dd) . " export columns defined.";
    }

    private function openTempFile()
    {
        if ( !$filename = tempnam(sys_get_temp_dir(), "ys3") ){

            return false;
        }

        // ensure file is erased as soon as script finishes
        register_shutdown_function( function() use($filename){

            unlink($filename);
        });

        return fopen( $filename, "w+" );
    }

    private function writeExportDataDictionaryFile( $export_uuid, $export_name, $export_target_folder, $dd, &$bytesWritten=0 )
    {
        $delim = ",";
        
        if ( !$export_target_folder ) {

            $path = tempnam(sys_get_temp_dir(), "ys3");
            $keepOpen = true;
        }
        else {

            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $export_target_folder .= DIRECTORY_SEPARATOR;
            }

            $path = $export_target_folder . $this->exportDataDictionaryFilename($export_name, "filesystem");           
        }

        $h = fopen( $path, "w+" );

        if ( $h===false ){

            exit("Fail: could not create export file {$path}");
        }

        $R = 0;

        $C = count($dd[0]);
           
        $bytesWritten = fputcsv($h, array_keys($dd[0]), $delim);
     
        foreach ( $dd as $x ) {

            $bytesWritten += fputcsv($h, array_values($x), $delim);
            $R++;
        }
     
        fclose($h);

        return "Success: {$bytesWritten} bytes, {$R} rows and {$C} columns written to {$path}.";
    }

    private function writeExportDataFile( $export_uuid, $export_name, $export_target_folder, $dd, &$bytesWritten=0 )
    {

        //exit( print_r($eventSpecs, true) )
        
        if ( !$export_target_folder ) {

            $path = tempnam(sys_get_temp_dir(), "ys3");
            $keepOpen = true;
        }
        else {

            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $export_target_folder .= DIRECTORY_SEPARATOR;
            }

            $path = $export_target_folder . $this->exportDataFilename($export_name, "filesystem");
            $keepOpen = false;
        }

        $h = fopen( $path, "w+" );

        if ( $h===false ){

            exit("Fail: could not create export file {$path}");
        }

        /**
         * build an assoc array for rapid event name resolution
         */
        $eventSpecs = $this->getEventSettings();

        $eventName = [];

        foreach( $eventSpecs as $eventSpec){

            $eventName[$eventSpec['event_id']] = $eventSpec['event_name'];
        }

        /**
         * Assemble 
         *   (1) the list of all events that contribute to this export, for use with IN() operator.
         *   (2) an index, keyed by field_name and event_id, for rapid retrieval of dd entry during record processing.
         * 
         * Most likely too many fields for the IN() approach to be efficient,
         * but perhaps worth testing.
         */
        $events = [];
        $dd_index = [];

        for ($i=0; $i<count($dd); $i++){

            if ( $dd[$i]['redcap_field_name'] && $dd[$i]['redcap_event_id'] && is_numeric($dd[$i]['redcap_event_id']) ){

                $dd_index[$dd[$i]['redcap_field_name']][$dd[$i]['redcap_event_id']] = $i;

                if ( !in_array($dd[$i]['redcap_event_id'], $events) ){

                    $events[] = $dd[$i]['redcap_event_id'];
                }
            }
        }

        /**
         * Assemble the list of records to include
         */

        /**
         * build the query
         */
        $critXOperators = [ "=>", "<=", "=", "<", ">"];

        $spec = $this->getExportSpecification( $export_uuid );

        $sqlWhere = "d.`project_id`=?";
        $sqlParams = [$this->project_id];

        if ( $events ){

            $inString = "";

            foreach ($events as $event_id){

                $inString .= (($inString) ? ",":"") . "?";
                $sqlParams[] = $event_id;
            }
            $sqlWhere .= " AND d.`event_id` IN({$inString})";
        }

        $sqlInnerJoin = "";

        if ( $spec['export_selection']=='2' && $spec['export_criterion_field'] && $spec['export_criterion_event'] && $spec['export_criterion_value'] ) {

            $criterion_expression = "";

            $critXParams = [ $spec['export_criterion_field'], $spec['export_criterion_event'] ];

            $critXStr = trim( $spec['export_criterion_value'] );

            $critXOp = "="; // default operator for the SELECT query
            $critXVal = $critXStr; // the value applied to the operator

            $critXQ = ""; // the query expression to be determined

            /**
             * lists of comma-separated values are allowed
             */
            if ( strpos($critXVal, ',') !== false ){

                $valParts = explode($critXVal, ",");

                $critXQList = "";

                foreach ( $valParts as $val){

                    $critXQList .= (( $critXQList ) ? "," : "") . "?";
                    $critXParams[] = $val;
                }
                $critXQ = "IN({$critXQList})";
            }
            else {

                foreach ($critXOperators as $op){
                    if (strpos($critXStr, $op)===0){
                        $critXOp = $op;
                        $critXVal = trim(substr($critXStr, strlen($critXOp)));
                        break;
                    }
                }

                $critXQ = $critXOp . "?";
                $critXParams[] = $critXVal;
            }

            $sqlInnerJoin = "INNER JOIN redcap_data c ON c.`project_id`=d.`project_id` AND c.`record`=d.`record` AND c.`field_name`=? AND c.`event_id`=?"
                . " AND c.`value` {$critXQ}";

            // The inner join params are now first in order
            $sqlParams = array_merge( $critXParams, $sqlParams );
        }

        $sql = "       
SELECT d.* 
FROM redcap_data d 
    {$sqlInnerJoin}
WHERE {$sqlWhere}
ORDER BY d.`record`, d.`event_id`, d.`instance`, d.`field_name`
        ";

        /**
         * Records are fetched from a generator, instead of the entire recordset at once.
         * Presumably slower, but won't exhaust PHP resources.
         */

        $K = 0; // datum count
        $R = 0; // export row count
        $C = 0; // col count

        $record = ".";
        $event_id = ".";
        $instance = ".";
        $field_index = -1;
        $y = [];
        $max_col_count = 0;

        $BOR = true;
        $EOR = true;

        $RecordIdField = \REDCap::getRecordIdField();

        $bytesWritten = 0;

        //foreach ( Yes3::recordGenerator($sql, $sqlParams) as $x ){
        $xx = Yes3::fetchRecords($sql, $sqlParams);
        foreach ( $xx as $x ){

            $K++;

            $x_instance = $x['instance'] || "1";

            /**
             * $BOR: beginning of record
             * 
             * The break will be on (record) for horiz layouts,
             *   (record_event_id) for vertical,
             *   (record, instance) for repeating
             */

            if ( $spec['export_layout']==="h" ) {

                $BOR = ( $x['record'] !== $record );
            }
            elseif ( $spec['export_layout']==="v" ) {

                $BOR = ( $x['record'] !== $record || $x['event_id'] !== $event_id );
            }
            elseif ( $spec['export_layout']==="r" ) {

                $BOR = ( $x['record'] !== $record || $x_instance !== $$instance );
            }
            else {

                $BOR = false;
            }
            
            if ( $BOR ) {

                if ( $y ){

                    if ( !$R ) $C = count($y);

                    $bytesWritten += $this->writeTempExportRecord($h, $y, $R, $C);
                }
                    
                $y = [
                    $RecordIdField => $x['record']
                ];

                if ( $spec['export_layout']==="v" ) {
    
                    $y['event_id'] = $x['event_id'];
                    $y['event_name'] = $eventName[$x['event_id']];
                }
                elseif ( $spec['export_layout']==="r" ) {
    
                    $y['event_id'] = $x['event_id'];
                    $y['event_name'] = $eventName[$x['event_id']];
                    $y['instance'] = $x_instance;
                }

                /**
                 * fill out the record
                 */
                foreach ($dd as $d){

                    if ( !isset($y[$d['var_name']]) ){
                        $y[$d['var_name']] = "";
                    }
                }

                $BOR = false;
            }

            $record = $x['record'];
            $event_id = $x['event_id'];
            $instance = $x_instance;

            $field_index = $dd_index[$x['field_name']][$x['event_id']] ?? -1;

            if ( $field_index > -1 ){

                $y[ $dd[$field_index]['var_name'] ] = $x['value'];
            }
        }

        if ( $y ){

            $bytesWritten += $this->writeTempExportRecord($h, $y, $R, $C);
        }

        if ( $keepOpen ){

            return $h;
        }

        fclose($h);

        return "Success: {$bytesWritten} bytes, {$R} rows and {$C} columns written to {$path}.";
    }

    private function writeTempExportRecord( $h, $y, &$rowNumber, &$colCount ){

        $delim = ",";

        $bytes = 0;

        if ( $rowNumber===0 ){

            $bytes += fputcsv($h, array_keys($y), $delim);
            $colCount = count($y);
        }

        $rowNumber++;

        $bytes += fputcsv($h, array_values($y), $delim);

        return $bytes;
    }

    public function exportData($export_uuid)
    {
        $t = time();

        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $response = "";
 
        $response .= $this->writeExportDataDictionaryFile($ddPackage['export_uuid'], $ddPackage['export_name'], $ddPackage['export_target_folder'], $ddPackage['export_data_dictionary']);

        $response .= "\n\n" . $this->writeExportDataFile($ddPackage['export_uuid'], $ddPackage['export_name'], $ddPackage['export_target_folder'], $ddPackage['export_data_dictionary']);

        $response .= "\n\nElapsed time: " . time() - $t . " seconds.";

        return nl2br($response);
    }

    public function downloadDataDictionary($export_uuid)
    {
     
        $h = fopen('php://output', 'w');

        if ( $h===false ){

            exit("Fail: could not open PHP output stream.");
        }

        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $filename = $this->exportDataDictionaryFilename( $ddPackage['export_name'], "download" );

        $delim = ",";

        ob_start();
        header("Content-type: text/csv");
        header("Cache-Control: no-store, no-cache");
        header('Content-Disposition: attachment; filename="'.$filename.'"');
     
        fputcsv($h, array_keys($ddPackage['export_data_dictionary'][0]), $delim);
     
        foreach ( $ddPackage['export_data_dictionary'] as $x ) {

            fputcsv($h, array_values($x), $delim);
        }
     
        fclose($h);
        ob_end_flush();

        exit;
    }

    public function downloadData($export_uuid)
    {
        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $bytesWritten = 0;

        $h = $this->writeExportDataFile($ddPackage['export_uuid'], $ddPackage['export_name'], "", $ddPackage['export_data_dictionary'], $bytesWritten);

        //exit('downloadData: '.$bytesWritten.' bytes written to export data file');

        if ( $h===false ) {

            exit("Fail: download export file not written");
        }

        $filename = $this->exportDataFilename( $ddPackage['export_name'], "download" );

        $chunksize = 1024 * 1024; // 1MB per one chunk of file.

        $size = intval(sprintf("%u", $bytesWritten));

        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$size);
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        rewind($h);

        while (!feof($h)){

            print(@fread($h, $chunksize));

            ob_flush();
            flush();
        }

        fclose($h);

        exit;
    }

    private function getEventName($event_id, $event_settings)
    {
        foreach ($event_settings as $event){

            if ( $event['event_id']==$event_id ){

                return $event['event_name'];
            }
        }

        return "";
    }

    private function exportDataFilename( $export_name, $target="download")
    {
        $extension = "csv"; // will work on this later

        if ( $target==="download") {
            return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_" . Yes3::timeStampString() . "." . $extension;
        }

        return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "." . $extension;
    }

    private function exportDataDictionaryFilename( $export_name, $target="download")
    {
        $extension = "csv"; // will work on this later

        if ( $target==="download") {
            return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_dd_" . Yes3::timeStampString() . "." . $extension;
        }

        return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_dd." . $extension;
    }

    public function getEventSettings()
    {
        if ( !\REDCap::isLongitudinal() ){

            return [
                [
                    'event_id' => (string) Yes3::getFirstREDCapEventId(),
                    'event_name' => "Event_1_arm_1",
                    'event_prefix' => ""
                ]
            ];
        }

        /**
         * [ {event_id, event_name, event_prefix}, ... ]
         */
        $event_settings = $this->getDefaultExportEvents(); 

        $fields = "export_events_json";

        $pSql = "SELECT {$fields} WHERE project_id=? AND setting='export-events' ORDER BY timestamp DESC LIMIT 1";

        if ( $x = $this->queryLogs($pSql, [$this->project_id])->fetch_assoc() ){

            if ( $export_events_settings = json_decode($x['export_events_json'], true) ){

                for ( $i=0; $i<count($event_settings); $i++ ){

                    for ( $j=0; $j<count($export_events_settings); $j++ ){

                        if ( $export_events_settings[$j]['event_id'] == $event_settings[$i]['event_id'] ){

                            $event_settings[$i]['event_prefix'] = $export_events_settings[$j]['event_prefix'];
                            break;
                        }
                    }
                }
            }
        }

        return $event_settings;
    }

    public function getExportSpecification( $export_uuid )
    {        
        $fields = "log_id, user, removed, setting, export_uuid, export_specification_json";
    
        $pSql = "
            SELECT {$fields}
            WHERE project_id=? AND setting='export-specification' AND removed='0' AND export_uuid=?
            ORDER BY timestamp DESC LIMIT 1
        ";
        $params = [$this->project_id, $export_uuid];
    
        if ( $specification_settings = $this->queryLogs($pSql, $params)->fetch_assoc() ){
    
            return json_decode($specification_settings['export_specification_json'], true);
        }
    
        return [];
    }

    public function getExportSpecifications()
    {
        /**
         * retrieve the unique export UUIDs
         */

        $pSql = "SELECT DISTINCT export_uuid WHERE export_uuid IS NOT NULL AND setting='export-specification' AND project_id=?";

        $uuid_result = $this->queryLogs($pSql, [$this->project_id]);
        $uuids = [];
        while ( $row = $uuid_result->fetch_assoc() ){
            $uuids[] = $row['export_uuid'];
        }

        //return $uuids;

        $specifications = [];

        for ( $i=0; $i<count($uuids); $i++ ){

            $fields = "log_id, user, removed, setting, export_uuid, export_specification_json";

            $pSql = "
                SELECT {$fields}
                WHERE project_id=? AND setting='export-specification' AND export_uuid=?
                ORDER BY timestamp DESC LIMIT 1
            ";
            $params = [$this->project_id, $uuids[$i]];

            if ( $specification_settings = $this->queryLogs($pSql, $params)->fetch_assoc() ){

                $specification = json_decode($specification_settings['export_specification_json']);

                if ( !$specification->removed ) $specification->removed = "0";

                //if ( $specification->removed !== "1" ){

                    $specifications[] = $specification;
                //}
            }
        }

        return $specifications;
    }

    public function getDefaultExportEvents()
    {
        if ( !\REDCap::isLongitudinal() ){
            return [];
        }

        $events = \REDCap::getEventNames(true);

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

        $export_events = [];

        foreach( $events as $event_id => $event_name ){

            $strEventId = (string) $event_id;

            if ( $uniquePrefixLen ){

                $prefix = $prefixes[$event_id];
            }
            else {

                $prefix = "e" . $strEventId;
            }

            $export_events[] = ['event_id'=>$strEventId, 'event_name'=>$event_name, 'event_prefix'=>$prefix];        
        }

        return $export_events;
    }

    public function getExportElements($export_uuid)
    {
        $fields = "log_id, message, user, timestamp, export_uuid, field_mappings";

        $pSql = "
            SELECT {$fields}
            WHERE project_id=? AND setting='yes3-exporter-field-map' AND export_uuid=?
            ORDER BY timestamp DESC LIMIT 1
        ";
        $params = [$this->project_id, $export_uuid];

        if ( !$map_record = $this->queryLogs($pSql, $params)->fetch_assoc() ){
            return [];
        }

        if ( !$field_mappings = json_decode($map_record['field_mappings'], true) ){
            return [];
        }

        if ( !$field_mappings['elements']) {
            return [];
        }

        return $field_mappings['elements'];
    }

    public function getFormMetadataStructures():array
    {
        $events = [];

        if ( $isLong = \REDCap::isLongitudinal() ) {

            $sql = "
            SELECT DISTINCT m.form_name, m.field_order, m.form_menu_description
            FROM redcap_metadata m
                INNER JOIN redcap_events_forms ef ON ef.form_name=m.form_name
                INNER JOIN redcap_events_metadata em ON em.event_id=ef.event_id
                INNER JOIN redcap_events_arms ea ON ea.arm_id=em.arm_id AND ea.project_id=m.project_id
            WHERE m.project_id=? AND m.form_menu_description IS NOT NULL
            ORDER BY m.field_order
            ";

        } else {

            $events = [[ 
                'event_id' => Yes3::getFirstREDCapEventId(),
                'descrip' => "Event 1",
                'event_label' => "Event_1"
            ]];

            $sql = "
            SELECT m.form_name, m.form_menu_description
            FROM redcap_metadata m
            WHERE m.project_id=? AND m.form_menu_description IS NOT NULL
            ";

        }

        $mm = Yes3::fetchRecords($sql, [$this->project_id]);

        $form_metadata = [];

        $form_index_num = 0;

        $form_index = [];

        foreach ($mm as $m){

            if ( $isLong ){

                $sqlE = "
                SELECT ef.event_id, em.descrip
                FROM redcap_events_forms ef
                    INNER JOIN redcap_events_metadata em ON em.event_id=ef.event_id
                    INNER JOIN redcap_events_arms ea ON ea.arm_id=em.arm_id
                WHERE ea.project_id=? and ef.form_name=?
                ORDER BY em.day_offset, ef.event_id
                ";

                $ee = Yes3::fetchRecords($sqlE, [$this->project_id, $m['form_name']]);

                $events = [];

                foreach( $ee as $e ){

                    $events[] = [ 
                        'event_id' => (string)$e['event_id'],
                        'event_label' => Yes3::escapeHtml($e['descrip']),
                        'descrip' => Yes3::escapeHtml($e['descrip'])
                    ];      
                }
            }

            $sqlF = "
            SELECT m.field_name
            FROM redcap_metadata m
            WHERE m.project_id=? and m.form_name=?
                AND m.element_type<>'descriptive'
            ORDER BY m.field_order ; 
            ";

            $form_fields = [];

            $fields = Yes3::fetchRecords($sqlF, [$this->project_id, $m['form_name']]);
            foreach ( $fields as $field ){
                $form_fields[] = $field['field_name'];
            }
    
            $form_name = Yes3::escapeHtml($m['form_name']);

            $form_metadata[] = [
                'form_name' => $form_name,
                'form_label' => Yes3::escapeHtml($m['form_menu_description']),
                'form_events' => $events,
                'form_fields' => $form_fields,
                'form_repeating' => ( Yes3::isRepeatingInstrument($m['form_name']) ) ? 1 : 0
            ];

            $form_index[$form_name] = $form_index_num;

            $form_index_num++;
        }
        
        return [
            'form_index'=>$form_index, 
            'form_metadata'=>$form_metadata
        ];
    }

    public function getFieldMetadataStructures(): array
    {
        if ( \REDCap::isLongitudinal() ){

            $sql = "
            SELECT DISTINCT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum, m.element_validation_type
            FROM redcap_metadata m
                INNER JOIN redcap_events_forms ef ON ef.form_name=m.form_name
                INNER JOIN redcap_events_metadata em ON em.event_id=ef.event_id
                INNER JOIN redcap_events_arms ea ON ea.arm_id=em.arm_id AND ea.project_id=m.project_id
            WHERE m.project_id=?
            AND m.element_type NOT IN('descriptive')
            ORDER BY m.field_order;  
            ";
        }
        else {

            $sql = "
            SELECT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum, m.element_validation_type
            FROM redcap_metadata m
            WHERE m.project_id=?
            AND m.element_type NOT IN('descriptive')
            ORDER BY m.field_order      
            ";
        }

        $fields = Yes3::fetchRecords($sql, [$this->project_id]);

        $field_metadata = [];

        $field_autoselect_source = [];

        $field_index_num = 0;

        $field_index = [];

        foreach ($fields as $field){

            $valueset = [];

            if ( $field['element_type']==="radio" || $field['element_type']==="select" || $field['element_type']==="checkbox"){
                $vv = $this->getChoiceLabels($field['field_name']);
                foreach ( $vv as $value => $label) {
                    $valueset[] = [
                        'value' => (string)$value,
                        'label' => Yes3::ellipsis(Yes3::printableEscHtmlString($label), MAX_LABEL_LEN)
                    ];
                }
            }

            $field_label = Yes3::ellipsis( Yes3::printableEscHtmlString($field['element_label']), MAX_LABEL_LEN );

            $field_metadata[] = [

                'field_name'        => $field['field_name'],
                'form_name'         => Yes3::escapeHtml($field['form_name']),
                'field_type'        => $field['element_type'],
                'field_validation'  => $field['element_validation_type'],
                'field_label'       => $field_label,
                'field_valueset'    => $valueset

            ];

            if ( !Yes3::isRepeatingInstrument($field['form_name'])) {
                $field_autoselect_source[] = [
                    'value' => $field['field_name'],
                    'label' => "[" . $field['field_name'] . "] " . $field_label
                ];
            }

            $field_index[$field['field_name']] = $field_index_num;

            $field_index_num++;
        }

        return [
            'field_index'=>$field_index, 
            'field_metadata'=>$field_metadata, 
            'field_autoselect_source'=>$field_autoselect_source
        ];
    }

    private function addExportItem_Specification( $export, $element, $event_settings )
    {
        $valueset = [];

        foreach( $element['values'] as $v ){

            $valueset[] = [
                'value' => $v['yes3_fmapr_lov_value'],
                'label' => $v['yes3_fmapr_lov_label'],
                'redcap_field_value' => $v['redcap_field_value']
            ];
        }

        $export->addExportItem([
            'var_name' => $element['yes3_fmapr_data_element_name'],
            'var_label' => $element['yes3_fmapr_data_element_description'],
            'var_type' => $this->specificationTypeToVarType( $element['type'], $valueset ),
            'valueset' => $valueset,
            'origin' => "specification",
            'redcap_field_name' => $element['redcap_field_name'],
            'redcap_form_name' => Yes3::getREDCapFormForField($element['redcap_field_name']),
            'redcap_event_id' => $element['redcap_event_id'],
            'redcap_event_name' => $this->getEventName($element['redcap_event_id'], $event_settings)
        ]);
    }

    private function addExportItem_REDCapField( $export, $redcap_field_name, $redcap_event_id, $fields, $forms, $event_settings )
    {
        $field_index = $fields['field_index'][$redcap_field_name];

        $form_name = $fields['field_metadata'][$field_index]['form_name'];

        $event_ids = [];

        if ( $redcap_event_id === ALL_OF_THEM && $export->export_layout === "h" ){

            $form_index = $forms['form_index'][$form_name];

            foreach($forms['form_metadata'][$form_index]['form_events'] as $event){

                $event_ids[] = $event['event_id'];
            }
        }
        else {

            $event_ids = [ $redcap_event_id ];
        }

        foreach ( $event_ids as $event_id ){

            $var_name = $this->exportFieldName($export, $redcap_field_name, $event_id, $event_settings);

            if ( !$export->itemInExport($var_name) ){

                $export->addExportItem([
                    'var_name' => $var_name,
                    'var_label' => $fields['field_metadata'][$field_index]['field_label'],
                    'var_type' => $this->REDCapFieldTypeToVarType($redcap_field_name, $fields),
                    'valueset' => $fields['field_metadata'][$field_index]['field_valueset'],
                    'origin' => "redcap",
                    'redcap_field_name' => $redcap_field_name,
                    'redcap_form_name' => $form_name,
                    'redcap_event_id' => $event_id,
                    'redcap_event_name' => $this->getEventName($event_id, $event_settings)
                ]);
            }
        }
    }

    private function addExportItem_otherProperty( $export, $property_name, $property_label, $property_type="TEXT" )
    {
        $export->addExportItem([
            'var_name' => $property_name,
            'var_label' => $property_label,
            'var_type' => $property_type,
            'valueset' => [],
            'origin' => "other",
            'redcap_field_name' => "",
            'redcap_form_name' => "",
            'redcap_event_id' => "",
            'redcap_event_name' => ""
        ]);
    }

    private function addExportItem_REDCapForm( $export, $redcap_form_name, $redcap_event_id, $fields, $forms, $event_settings )
    {
        $form_names = [];

        if ( $redcap_form_name === ALL_OF_THEM ){

            foreach ( $forms['form_metadata'] as $form ){

                if ( !$form['form_repeating'] ) {

                    $includeForm = ( $redcap_event_id === ALL_OF_THEM || !\REDCap::isLongitudinal() );

                    if ( !$includeForm ){

                        foreach ( $form['form_events'] as $event ){

                            if ( $event['event_id'] == $redcap_event_id ){

                                $includeForm = true;
                                break;
                            }
                        }
                    }

                    if ( $includeForm ){
                        
                        $form_names[] = $form['form_name'];
                    }
                } // no repeaters allowed in 'all' forms
            }
        }
        else {

            $form_names = [ $redcap_form_name ];
        }

        foreach ( $form_names as $form_name ){

            $form_index = $forms['form_index'][$form_name];

            $event_ids = [];

            if ( $redcap_event_id === ALL_OF_THEM ) {

                foreach ( $forms['form_metadata'][$form_index]['form_events'] as $event ){

                    $event_ids[] = $event['event_id'];
                }
            }
            else {

                $event_ids[] = $redcap_event_id;
            }

            foreach ( $event_ids as $event_id ){

                foreach ( $forms['form_metadata'][$form_index]['form_fields'] as $field_name ){

                    $this->addExportItem_REDCapField($export, $field_name, $event_id, $fields, $forms, $event_settings);
                }
            }
        }
    }

    private function exportFieldName( $export, $field_name, $event_id, $event_settings)
    {
        if ( $export->export_layout==="h" && $field_name !== \REDCap::getRecordIdField() ) {
    
            return $this->eventPrefixForEventId($event_id, $event_settings) . "_" . $field_name;
        }
    
        return $field_name;
    }
    
    private function eventPrefixForEventId($event_id, $event_settings)
    {
        for ( $i=0; $i<count($event_settings); $i++){
    
            if ( $event_settings[$i]['event_id']==$event_id ){
    
                return $event_settings[$i]['event_prefix'];
            }
        }
    
        return "???";
    }
 
    private function specificationTypeToVarType( $spec_type, $valueset )
    {
        if ( $valueset ) return "NOMINAL";

        if ( !$spec_type ) return "TEXT";

        $spec_type = strtolower(trim($spec_type));

        if ( $spec_type==="string" ) return "TEXT";
        if ( $spec_type==="text" ) return "TEXT";
        if ( $spec_type==="character" ) return "TEXT";
        if ( $spec_type==="integer" ) return "INTEGER";
        if ( $spec_type==="float" ) return "FLOAT";
        if ( $spec_type==="date" ) return "DATE";
        if ( $spec_type==="datetime" ) return "DATETIME";
        if ( $spec_type==="time" ) return "TIME";
        if ( $spec_type==="number" ) return "FLOAT";
        if ( $spec_type==="real" ) return "FLOAT";
        if ( $spec_type==="categorical" ) return "NOMINAL";

        return "TEXT";
    }

    private function REDCapFieldTypeToVarType( $field_name, $fields )
    {
        $field_index = $fields['field_index'][$field_name];
        $field_type = $fields['field_metadata'][$field_index]['field_type'];
        $field_validation = $fields['field_metadata'][$field_index]['field_validation'];

        if ( $field_type === "radio" ) return "NOMINAL";
        if ( $field_type === "dropdown" ) return "NOMINAL";
        if ( $field_type === "yesno" ) return "NOMINAL";
        if ( $field_type === "truefalse" ) return "NOMINAL";
        if ( $field_type === "checkbox" ) return "CHECKBOX";
        if ( $field_type === "select" ) return "NOMINAL";
        if ( $field_type === "slider" ) return "INTEGER";

        if ( $field_validation === "date_mdy" ) return "DATE";
        if ( $field_validation === "date_ymd" ) return "DATE";
        if ( $field_validation === "datetime_mdy" ) return "DATETIME";
        if ( $field_validation === "datetime_ymd" ) return "DATETIME";
        if ( $field_validation === "datetime_seconds_mdy" ) return "DATETIME";
        if ( $field_validation === "datetime_seconds_ymd" ) return "DATETIME";
        if ( $field_validation === "time" ) return "TIME";
        if ( $field_validation === "float" ) return "FLOAT";
        if ( $field_validation === "int" ) return "INTEGER";

        return "TEXT";
    }
   

    /* ==== HOOKS ==== */

    public function redcap_module_link_check_display( $project_id, $link )
    {
        return $link; // noop for now
    }
}
