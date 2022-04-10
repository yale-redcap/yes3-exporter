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

use Exception;
use REDCap;
use ZipArchive;
use Project;

class Yes3FieldMapper extends \ExternalModules\AbstractExternalModule
{
    public $project_id = 0;
    public $RecordIdField = "";
    public $isLongitudinal = "";
    public $username = "";
    public $serviceUrl = "";
    public $documentationUrl = "";
    private $token = "";
    private $salt = "";
    private $project_salt = "";
    private $date_shift_max = "";

    use Yes3Trait;

    public function __construct() {

        global $salt;

        parent::__construct(); // call parent (AbstractExternalModule) constructor

        if ( $_GET['pid'] ){

            $this->project_id = Yes3::getREDCapProjectId();

            $this->username = $this->getUser()->getUsername();
            $this->serviceUrl = $this->getUrl('services/services.php');
            $this->documentationUrl = $this->getUrl('plugins/yes3_exporter_documentation.php');

            $this->RecordIdField = REDCap::getRecordIdField();
            $this->isLongitudinal = REDCap::isLongitudinal();

            $this->token = "this-should-be-private";

            $Proj = new Project();

            $this->salt = $salt;
            $this->project_salt = $Proj->project['__SALT__'];
            $this->date_shift_max = (int)$Proj->project['date_shift_max'];

            //Yes3::logDebugMessage($this->project_id, "salt={$this->salt}, project_salt={$this->project_salt}, date_shift_max={$this->date_shift_max}", "Yes3FieldMapper");
        }
    }

    /**
     * Hash and Date shifting code from Record class, REDCap 11.3.4
     * Copied here to remove dependency on class def
     */

    /**
     * formula from Records::getData
     * 
     * function: hash_record
     * 
     * @param mixed $record
     * 
     * @return string
     */
    private function hash_record($record)
    {
        return md5($this->salt . $record . $this->project_salt);
    }
  
	/**
	 * DATE SHIFTING: Get number of days to shift for a record
	 */
	private function get_shift_days($idnumber)
	{
		$dec = hexdec(substr(md5($this->salt . $idnumber . $this->project_salt), 10, 8));
		// Set as integer between 0 and $date_shift_max
		$days_to_shift = round($dec / pow(10,strlen($dec)) * $this->date_shift_max);
		return $days_to_shift;
	}

	/**
	 * DATE SHIFTING: Shift a date by providing the number of days to shift
	 */
	private function shift_date_format($date, $days_to_shift)
	{
		if ($date == "") return $date;

        if ( strlen($date) < 10 ) return $date;

		// Explode into date/time pieces (in case a datetime field)
		list ($date, $time) = explode(' ', $date, 2);
		// Separate date into components
		$mm   = (int)substr($date, 5, 2);
		$dd   = (int)substr($date, 8, 2);
		$yyyy = (int)substr($date, 0, 4);
		// Shift the date
		$newdate = date("Y-m-d", mktime(0, 0, 0, $mm , $dd - $days_to_shift, $yyyy));
		// Re-add time component (if applicable)
		$newdate = trim("$newdate $time");
		// Return new date/time
		return $newdate;
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
         * Export specification (assoc array):
         * 
         *      export_uuid,
         *      export_name,
         *      export_layout,
         *      export_selection,
         *      export_criterion_field,
         *      export_criterion_event,
         *      export_criterion_value,
         *      export_target,
         *      export_target_folder,
         *      export_max_label_length,
         *      export_max_text_length,
         *      export_inoffensive_text,
         *      export_uspec_json, 
         *      export_items_json
         *      removed
         *      
         */
        $export_specification = $this->getExportSpecification($export_uuid);

        //Yes3::logDebugMessage($this->project_id, print_r($export_specification, true), "buildExportDataDictionary");

        /**
         * export object:
         * 
         *      export_name = "";
         *      export_uuid = "";
         *      export_layout = "";
         *      export_selection = "";
         *      export_criterion_field = "";
         *      export_criterion_event = "";
         *      export_criterion_value = "";
         *      export_target = "";
         *      export_target_folder = "";
         *      export_data_dictionary = [];
         */
        $export = new Yes3Export( $export_specification ); 
    
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
        $forms = $this->getFormMetadataStructures();
    
        /**
         * 
         * field_metadata: list of field objects
         *      {
         *          field_name
         *          field_label
         *          form_name
         *          field_type: REDCap element_type
         *          field_validation
         *          field_phi
         *          field_valueset: list of valueset objects {value, label}
         *      }
         * 
         * field_index: list index, keyed by field_name
         * 
         */
        $fields = $this->getFieldMetadataStructures();

        /**
         * uRights: a curated set of rights and permissions
         * 
         *  username
         *  isDesigner
         *  isSuper
         *  group_id
         *  dag:    unique group name
         *  export: data_export_tool permission
         *  import: data_import_tool permission
         *  api_export
         *  api_import
         *  form_permissions: assoc array of (1,0) read permission, keyed by form_name
         * 
        */
        $uRights = $this->yes3UserRights();

        if ( !$uRights['export'] ){

            throw new Exception("ERROR: User does not have permission to export data.");
        }

        $allowed = [
            'group_id' => 0,
            'forms' => [],
            'phi' => ( $export->export_remove_phi ) ? 0 : (($uRights['export']==1) ? 1:0),
            'dates' => ( $export->export_remove_dates ) ? 0 : (($uRights['export']==1 || $uRights['export']==3) ? 1:0),
            'smalltext' => ( $export->export_remove_freetext ) ? 0 : (($uRights['export']==1 || $uRights['export']==3) ? 1:0),
            'largetext' => ( $export->export_remove_freetext || $export->export_remove_largetext) ? 0 : (($uRights['export']==1 || $uRights['export']==3) ? 1:0)
        ];

        foreach ($uRights['form_permissions'] as $form_name => $readWrite ){

            if ( (int)$readWrite ){

                $allowed['forms'][] = $form_name;
            }
        }

        if ( $uRights['group_id'] ){

            $allowed['group_id'] = $uRights['group_id'];
        }
/*
        Yes3::logDebugMessage($this->project_id, print_r($export_specification, true), "buildExportDataDictionary: export_specification");
        Yes3::logDebugMessage($this->project_id, print_r($export, true), "buildExportDataDictionary: export");
        Yes3::logDebugMessage($this->project_id, print_r($allowed, true), "buildExportDataDictionary: allowed");
        throw new Exception("Have a nice day");
*/   
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
         *      frequency_table (JSON)
         * }
         * 
         */

        /**
         * Start with recordid field; always the first column
         */
        $field_name = REDCap::getRecordIdField();

        $this->addExportItem_REDCapField( $export, $field_name, Yes3::getREDCapEventIdForField($field_name), $fields, $forms, $event_settings, $allowed );

        if ( REDCap::getGroupNames() ) {

            $this->addExportItem_otherProperty($export, VARNAME_GROUP_ID,   "REDCap Data Access Group Id", "INTEGER");
            $this->addExportItem_otherProperty($export, VARNAME_GROUP_NAME, "REDCap Data Access Group Name", "TEXT");
        }

        if ( $export->export_layout !== "h" && REDCap::isLongitudinal() ) {

            $this->addExportItem_otherProperty($export, VARNAME_EVENT_ID,   "REDCap Event Id", "INTEGER");
            $this->addExportItem_otherProperty($export, VARNAME_EVENT_NAME, "REDCap Event Name", "TEXT");
        }

        if ( $export->export_layout !== "r" ) {

            $this->addExportItem_otherProperty($export, VARNAME_INSTANCE, "REDCap Repeat Instance", "INTEGER");
        }

        $export_items = json_decode( $export_specification['export_items_json'], true);

        $export_uspec = json_decode( $export_specification['export_uspec_json'], true);
    
        foreach ($export_items as $element){
    
            if ( $element['export_item_origin'] === 'specification' ) {
    
                $this->addExportItem_Specification( $export, $element, $event_settings, $export_uspec );
            }
    
            elseif ( $element['redcap_field_name'] ) {
    
                $this->addExportItem_REDCapField( $export, $element['redcap_field_name'], $element[VARNAME_EVENT_ID], $fields, $forms, $event_settings, $allowed );
            }
    
            elseif ( $element['redcap_form_name'] ) {
    
                $this->addExportItem_REDCapForm( $export, $element['redcap_form_name'], $element[VARNAME_EVENT_ID], $fields, $forms, $event_settings, $allowed );
            }
        }

        $dd = [];

        $fields_rejected = 0;

        //Yes3::logDebugMessage($this->project_id, print_r($uRights['form_permissions'], true), "buildExportDataDictionary:fp");
        //Yes3::logDebugMessage($this->project_id, print_r($export->export_items, true), "buildExportDataDictionary:fp");

        foreach( $export->export_items as $export_item ){

            $dd[] = [
                'var_name' => $export_item->var_name,
                'var_label' => Yes3::ellipsis( $export_item->var_label, $export->export_max_label_length ),
                'var_type' => $export_item->var_type,
                'valueset' => ( $export_item->valueset ) ? json_encode($export_item->valueset) : "",
                'origin' => $export_item->origin,
                'redcap_field_name' => $export_item->redcap_field_name,
                'redcap_form_name' => $export_item->redcap_form_name,
                VARNAME_EVENT_ID => $export_item->redcap_event_id,
                VARNAME_EVENT_NAME => $export_item->redcap_event_name,
                'non_missing_count' => $export_item->non_missing_count,
                'min_length' => $export_item->min_length,
                'max_length' => $export_item->max_length,
                'min_value' => $export_item->min_value,
                'max_value' => $export_item->max_value,
                'sum_of_values' => $export_item->sum_of_values,
                'sum_of_squared_values' => $export_item->sum_of_squared_values, 
                'mean' => $export_item->mean, 
                'standard_deviation' => $export_item->standard_deviation, 
                'formatted_min_value' => $export_item->formatted_min_value,
                'formatted_max_value' => $export_item->formatted_max_value,
                'formatted_mean' => $export_item->formatted_mean,
                'frequency_table' => $export_item->frequency_table   
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
            'export_layout' => $export->export_layout,
            'export_selection' => $export->export_selection,
            'export_criterion_field' => $export->export_criterion_field,
            'export_criterion_event' => $export->export_criterion_event,
            'export_criterion_value' => $export->export_criterion_value,
            'export_target' => $export->export_target,
            'export_target_folder' => $export->export_target_folder,
            'export_max_label_length' => $export->export_max_label_length,
            'export_max_text_length' => $export->export_max_text_length,
            'export_inoffensive_text' => $export->export_inoffensive_text,
            'export_hash_recordid' => $export->export_hash_recordid,
            'export_shift_dates' => $export->export_shift_dates,
            'export_group_id' => $allowed['group_id'],
            'export_data_dictionary' => $dd,
            'export_fields_rejected' => $fields_rejected
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

    private function writeExportDataDictionaryFile( $export_name, $export_target_folder, $dd, $destination, &$bytesWritten=0 )
    {
        $delim = ",";
        
        if ( !$export_target_folder || $destination==="download" ) {

            $path = tempnam(sys_get_temp_dir(), "ys3");
        }
        else {

            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $export_target_folder .= DIRECTORY_SEPARATOR;
            }

            $path = $export_target_folder . $this->exportDataDictionaryFilename($export_name, "filesystem");           
        }

        $h = fopen( $path, "w+" );

        if ( $h===false ){

            throw new Exception("Fail: could not create export file {$path}");
        }

        $R = 0;

        $C = count($dd[0]);
           
        $bytesWritten = fputcsv($h, array_keys($dd[0]), $delim);
     
        foreach ( $dd as $x ) {

            $bytesWritten += fputcsv($h, array_values($x), $delim);
            $R++;
        }
     
        fclose($h);

        return [
            'export_data_dictionary_message' => "Success: {$bytesWritten} bytes, {$R} rows and {$C} columns written to {$path}.",
            'export_data_dictionary_filename' => $path,
            'export_data_dictionary_file_size' => $bytesWritten
        ];
    }

    private function writeExportFiles( &$ddPackage, $destination="", &$bytesWritten=0)
    {
        //exit( print_r($eventSpecs, true) )

        // for code clarity
        $export_uuid                = $ddPackage['export_uuid'];
        $export_name                = $ddPackage['export_name'];
        $export_target_folder       = $ddPackage['export_target_folder'];
        $export_layout              = $ddPackage['export_layout'];
        $export_max_text_length     = (int)$ddPackage['export_max_text_length'];
        $export_inoffensive_text    = (int)$ddPackage['export_inoffensive_text'];
        $export_shift_dates         = (int)$ddPackage['export_shift_dates'];
        $export_group_id            = (int)$ddPackage['export_group_id'];
        $export_hash_recordid       = (int)$ddPackage['export_hash_recordid'];

        $dd = $ddPackage['export_data_dictionary'];

        if ( !$export_target_folder || $destination==="download" ) {

            $path = tempnam(sys_get_temp_dir(), "ys3");

            $destination = "download";
        }
        else {

            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $export_target_folder .= DIRECTORY_SEPARATOR;
            }

            $path = $export_target_folder . $this->exportDataFilename($export_name, "filesystem");

            $destination = "filesystem";
        }

        $h = fopen( $path, "w+" );

        if ( $h===false ){

            throw new Exception("Fail: could not create export file {$path}");
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
         * get an assoc array of dag names
        **/

        $dagNameForGroupId = REDCap::getGroupNames(true);
        if ( !$dagNameForGroupId ){

            $dagNameForGroupId = [];
        }

        //$spec = $this->getExportSpecification( $export_uuid );

        /**
         * DD pre-processing
         * 
         * (1) unpack the specmap valueset
         * 
         * (2) Assemble 
         *   (a) the list of all events that contribute to this export, for use with IN() operator.
         *   (b) two indexes, keyed by field_name and event_id, for rapid retrieval of dd entry during record processing.
         * 
         * Most likely too many fields for the IN() approach to be efficient,
         * but perhaps worth testing.
         */
        $events = [];
        $dd_index = [];
        $dd_specmap_index = [];

        //Yes3::logDebugMessage($this->project_id, print_r($dd, true), "writeExportFiles: dd");

        for ($i=0; $i<count($dd); $i++){

            if ( $export_layout==="h" ){

                if ( $dd[$i]['redcap_field_name'] && $dd[$i][VARNAME_EVENT_ID] && is_numeric($dd[$i][VARNAME_EVENT_ID]) ){

                    if ( $dd[$i]['origin'] === "redcap" ){

                        $dd_index[$dd[$i]['redcap_field_name']][$dd[$i][VARNAME_EVENT_ID]] = $i;
                    }

                    elseif ( $dd[$i]['origin'] === "specification" ){

                        $dd_specmap_index[$dd[$i]['redcap_field_name']][$dd[$i][VARNAME_EVENT_ID]] = $i;
                    }
                    
                    if ( !in_array($dd[$i][VARNAME_EVENT_ID], $events) ){

                        $events[] = $dd[$i][VARNAME_EVENT_ID];
                    }
                }
            }
            else {

                if ( $dd[$i]['redcap_field_name'] ){

                    if ( $dd[$i]['origin'] === "redcap" ){

                        $dd_index[$dd[$i]['redcap_field_name']] = $i;
                    }

                    elseif ( $dd[$i]['origin'] === "specification" ){

                        $dd_specmap_index[$dd[$i]['redcap_field_name']] = $i;
                    }
                }
            }

            if ( $dd[$i]['valueset'] && is_string($dd[$i]['valueset']) ){

                $dd[$i]['valueset'] = json_decode($dd[$i]['valueset'], true);
            }
        }

        /**
         * Assemble the SELECT query and event params to be passed to the record writer
         */
        $sqlSelect = "SELECT d.* FROM redcap_data d WHERE d.`project_id`=? AND d.`record`=?";

        $sqlEvent = "";
        $sqlEventParams = [];
        $sqlOrderBy = "";

        if ( $events ){
            
            $sqlEvent = "AND d.`event_id` IN(";

            for($e=0; $e<count($events); $e++){

                $sqlEvent .= ( $e===0 ) ? "?":",?";

                $sqlEventParams[] = $events[$e];
            }

            $sqlEvent .= ")";
        }

        if ( $ddPackage['export_layout']==="r" ){

            $sqlOrderBy = " ORDER BY d.`event_id`, d.`instance`";
        }
        else if ( $ddPackage['export_layout']==="v" ){

            $sqlOrderBy = "ORDER BY d.`event_id`";
        }

        if ( $sqlEvent ){

            $sqlSelect .= "\n" . $sqlEvent;
        }

        if ( $sqlOrderBy ){

            $sqlSelect .= "\n" . $sqlOrderBy;
        }

        /**
         * Assemble the list of records to include
         */

        $sqlParams = [$this->project_id];

        if ( $ddPackage['export_selection']=='2' && $ddPackage['export_criterion_field'] && $ddPackage['export_criterion_event'] && $ddPackage['export_criterion_value'] ) {

            $critXOperators = [ "=>", "<=", "=", "<", ">"];

            $sqlCritXParams = [];

            $critXStr = trim( $ddPackage['export_criterion_value'] );

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
                    $sqlCritXParams[] = $val;
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

                $sqlCritXParams[] = $critXVal;
            }

            if ( $export_group_id ){

                $sql = "
                SELECT DISTINCT d.`record`
                FROM redcap_data d
                INNER JOIN redcap_data dg ON dg.`project_id`=d.`project_id` AND dg.`event_id`=d.`event_id` AND dg.`record`=d.`record` AND dg.`field_name`='__GROUPID__'
                WHERE d.`project_id`=? AND dg.`value`=? AND d.`event_id`=? AND d.`field_name`=? AND d.`value` {$critXQ}";

                $sqlParams = array_merge([ $this->project_id, $export_group_id, $ddPackage['export_criterion_event'], $ddPackage['export_criterion_field'] ], $sqlCritXParams);
            }
            else {

                $sql = "
                SELECT DISTINCT d.`record`
                FROM redcap_data d
                WHERE d.`project_id`=? AND d.`event_id`=? AND d.`field_name`=? AND d.`value` {$critXQ}";

                $sqlParams = array_merge([$this->project_id, $ddPackage['export_criterion_event'], $ddPackage['export_criterion_field'] ], $sqlCritXParams);
            }
        }
        else {

            if ( $export_group_id ){

                $sql = "       
                SELECT DISTINCT d.`record`
                FROM redcap_data d
                INNER JOIN redcap_data dg ON dg.`project_id`=d.`project_id` AND dg.`event_id`=d.`event_id` AND dg.`record`=d.`record` AND dg.`field_name`='__GROUPID__'
                WHERE d.`project_id`=? AND dg.`value`=?";

                $sqlParams = [ $this->project_id, $export_group_id ];
            }
            else {

                $sql = "       
                SELECT DISTINCT d.`record`
                FROM redcap_data d
                WHERE d.`project_id`=?";

                $sqlParams = [ $this->project_id ];                
            }
        }

        //$sql .= " LIMIT 10";

        $records = [];
        $all_numeric = true;
        foreach ( Yes3::recordGenerator($sql, $sqlParams) as $x ){

            $records[] = $x['record'];

            if ( $all_numeric && !is_numeric($x['record']) ){

                $all_numeric = false;
            }
         }

        if ( $all_numeric ){

            sort($records, SORT_NUMERIC);
        }
        else {

            sort($records, SORT_NATURAL | SORT_FLAG_CASE);
        }

        $K = 0; // datum count
        $R = 0; // export row count
        $C = 0; // col count
        $bytesWritten = 0;

        foreach ( $records as $record ){

            $sqlSelectParams = array_merge([$this->project_id, $record], $sqlEventParams);

            $bytesWritten += $this->writeExportDataFileRecord(
                $record, 
                $sqlSelect, 
                $sqlSelectParams, 
                $eventName, 
                $dd, 
                $dd_index, 
                $dd_specmap_index,
                $dagNameForGroupId, 
                $h, 
                $export_layout, 
                $export_max_text_length, 
                $export_inoffensive_text,
                $export_hash_recordid,
                $export_shift_dates,
                $K, 
                $R, 
                $C
            );
        }

        /**
         * DD post-processing
         * 
         * (1) repack the valueset
         * (2) Tidy up the dd validation section
         */

        $this->tidyUpDD($dd);
        
/*        
        foreach($dd as $d){

            if ( $d['non_missing_count'] > 0 ) {

                print "<p>".nl2br(print_r($d, true))."</p>";
            }
        }
        exit;
*/        
        
        fclose($h);

        $ddPackage['export_data_dictionary'] = $dd;

        $export_data_dictionary_response = $this->writeExportDataDictionaryFile( $export_name, $export_target_folder, $dd, $destination );

        $this->logExport(
            "export files written",
            $destination,
            $export_uuid,
            $export_name,
            $path,
            $export_data_dictionary_response['export_data_dictionary_filename'],
            null,
            $bytesWritten,
            $K,
            $R,
            $C
        );

        return [
            'export_data_message' => "Success: {$bytesWritten} bytes, {$R} rows and {$C} columns written to {$path}.",
            'export_data_filename' => $path,
            'export_data_file_size' => $bytesWritten,
            'export_data_items' => $K,
            'export_data_rows' => $R,
            'export_data_columns' => $C,
            'export_data_dictionary_message' => $export_data_dictionary_response['export_data_dictionary_message'],
            'export_data_dictionary_filename' => $export_data_dictionary_response['export_data_dictionary_filename'],
            'export_data_dictionary_file_size' => $export_data_dictionary_response['export_data_dictionary_file_size']
        ];
    }

    private function logExport($message, $destination, $export_uuid, $export_name, $filename_data, $filename_data_dictionary, $filename_zip, $bytes, $items, $rows, $columns)
    {
        $params = [
            'username' => $this->username,
            'log_entry_type' => EMLOG_LOG_ENTRY_TYPE,
            'destination' => $destination,
            'export_uuid' => $export_uuid,
            'export_name' => $export_name,
            'filename_data' => $filename_data,
            'filename_data_dictionary' => $filename_data_dictionary,
            'filename_zip' => $filename_zip,
            'exported_bytes' => $bytes,
            'exported_items' => $items,
            'exported_rows' => $rows,
            'exported_columns' => $columns
        ];

        $log_id = $this->log(
            $message,
            $params
        );

        return $log_id;
    }

    public function getExportLogs($export_uuid, $descending = false)
    {
        $pSql = "
SELECT log_id, timestamp, username, message
    , log_entry_type, destination, export_uuid, export_name
    , filename_data, filename_data_dictionary, filename_zip 
    , exported_bytes, exported_items, exported_rows, exported_columns
WHERE project_id=? AND export_uuid=? AND log_entry_type=?
        ";

        if ( $descending ){

            $pSql .= " ORDER BY timestamp DESC";
        }
        else {

            $pSql .= " ORDER BY timestamp ASC";
        }

        $logRecords = [];

        $result = $this->queryLogs($pSql, [ $this->project_id, $export_uuid, EMLOG_LOG_ENTRY_TYPE ]);

        while ($logRecord = $result->fetch_assoc()){

            $logRecords[] = $logRecord;
        }

        return $logRecords;
    }

    private function tidyUpDD( &$dd, $noCalculations=false )
    {
        for ( $i=0; $i<count($dd); $i++ ){

            /**
             * dang "complete" fields
             */
            if ( $dd[$i]['redcap_field_name'] === $dd[$i]['redcap_form_name']."_complete" ){

                $dd[$i]['valueset'] = [
                    [
                        'value'=>"0",
                        'label'=>"incomplete"
                    ],
                    [
                        'value'=>"1",
                        'label'=>"unverified"
                    ],
                    [
                        'value'=>"2",
                        'label'=>"complete"
                    ]
                ];
            }     
            
            if ( is_array($dd[$i]['valueset']) && count($dd[$i]['valueset']) > 0 ){

                $dd[$i]['valueset'] = json_encode($dd[$i]['valueset']);
            }

            if ( $dd[$i]['min_value']===NULL || $noCalculations ){

                $dd[$i]['min_value'] = "";
                $dd[$i]['max_value'] = "";
                $dd[$i]['mean'] = "";
                $dd[$i]['standard_deviation'] = "";
                $dd[$i]['formatted_min_value'] = "";
                $dd[$i]['formatted_max_value'] = "";
                $dd[$i]['formatted_mean'] = "";
                $dd[$i]['sum_of_values'] = "";
                $dd[$i]['sum_of_squared_values'] = "";

                if ( !$dd[$i]['non_missing_count'] ) {

                    $dd[$i]['min_length'] = "";
                    $dd[$i]['max_length'] = "";
                }

                if ( $noCalculations ){

                    $dd[$i]['non_missing_count'] = "";
                }
            }
            elseif ( $dd[$i]['non_missing_count'] > 0 ) {

                $dd[$i]['mean'] = (float) $dd[$i]['sum_of_values'] / $dd[$i]['non_missing_count'];

                if ( $dd[$i]['non_missing_count'] > 1 ) {

                    $dd[$i]['standard_deviation'] 
                        = (float) sqrt(($dd[$i]['sum_of_squared_values'] - ($dd[$i]['sum_of_values']*$dd[$i]['sum_of_values']/$dd[$i]['non_missing_count']))/($dd[$i]['non_missing_count'] - 1));
                }

                if ( $dd[$i]['var_type']==="DATE" ){

                    $dd[$i]['formatted_min_value'] = strftime("%F", $dd[$i]['min_value']);
                    $dd[$i]['formatted_max_value'] = strftime("%F", $dd[$i]['max_value']);
                    $dd[$i]['formatted_mean']      = strftime("%F", round($dd[$i]['mean']));
                }

                elseif ( $dd[$i]['var_type']==="DATETIME" ){

                    $dd[$i]['formatted_min_value'] = strftime("%F %T", $dd[$i]['min_value']);
                    $dd[$i]['formatted_max_value'] = strftime("%F %T", $dd[$i]['max_value']);
                    $dd[$i]['formatted_mean']      = strftime("%F %T", round($dd[$i]['mean']));
                }

                elseif ( $dd[$i]['var_type']==="TIME" ){

                    $dd[$i]['formatted_min_value'] = strftime("%T", $dd[$i]['min_value']);
                    $dd[$i]['formatted_max_value'] = strftime("%T", $dd[$i]['max_value']);
                    $dd[$i]['formatted_mean']      = strftime("%T", round($dd[$i]['mean']));
                }
            }

            if ( is_array($dd[$i]['frequency_table']) && count($dd[$i]['frequency_table']) > 0 ){

                $frqTbl = [];

                $j = 0;

                foreach($dd[$i]['frequency_table'] as $key=>$count ){
                
                    $frqTbl[$j] = [
                        "value" => $key,
                        "count" => $count
                    ];
                    $j++;
                }

                $arVal = array_column($frqTbl, 'value');

                array_multisort($arVal, SORT_ASC, SORT_NATURAL, $frqTbl);

                //$dd[$i]['frequency_table'] = Yes3::json_encode_pretty($dd[$i]['frequency_table']);
                $dd[$i]['frequency_table'] = json_encode($frqTbl);
            }

            else {

                $dd[$i]['frequency_table'] = "";
            }
        }
    }

    private function sortByValue($a, $b)
    {
        if ( is_numeric($a['value']) && is_numeric($b['value']) ){

            return intval($a['value']) > intval($b['value']);
        }

        return $a['value'] > $b['value'];
    }

    private function isDateOrTimeType( $varType )
    {
        return in_array( $varType, ['DATE', 'TIME', 'DATETIME']);
    }

    private function isDateType( $varType )
    {
        return in_array( $varType, ['DATE', 'DATETIME']);
    }
    
    private function writeExportDataFileRecord( 
        $record,
        $sqlSelect, 
        $sqlSelectParams, 
        $eventName, 
        &$dd, 
        $dd_index, 
        $dd_specmap_index, 
        $dagNameForGroupId, 
        $h, 
        $export_layout, 
        $export_max_text_length, 
        $export_inoffensive_text,
        $export_hash_recordid,
        $export_shift_dates,
        &$K, 
        &$R, 
        &$C
    ){
        $event_id = "?";
        $instance = "?";
        $field_index = -1;
        $days_to_shift = 0;

        if ( $export_shift_dates ){

            $days_to_shift = $this->get_shift_days($record);
        }

        if ( $export_hash_recordid ){

            $record = $this->hash_record($record);
        }

        $y = [];

        $BOR = true;

        $RecordIdField = REDCap::getRecordIdField();

        $bytesWritten = 0;

        $exportValues = 0;

        //Yes3::logDebugMessage($this->project_id, $sqlSelect, "writeExportDataFileRecord: sqlSelect");
        //Yes3::logDebugMessage($this->project_id, print_r($sqlSelectParams, true), "writeExportDataFileRecord: sqlSelectParams");

        foreach ( Yes3::recordGenerator($sqlSelect, $sqlSelectParams) as $x ){
        //$xx = Yes3::fetchRecords($sql, $sqlParams);
        //foreach ( $xx as $x ){

            $K++;

            $x_instance = $x['instance'] || "1";

            /**
             * $BOR: beginning of record
             * 
             * No break for horiz layouts,
             *   (event_id) for vertical,
             *   (event_id, instance) for repeating
             */

            if ( $export_layout==="v" ) {

                $BOR = ( $x['event_id'] !== $event_id );
            }
            elseif ( $export_layout==="r" ) {

                $BOR = ( $x['event_id'] !== $event_id || $x_instance !== $instance );
            }
            
            if ( $BOR ) {

                if ( $y && $exportValues ){

                    $bytesWritten += $this->writeExportRecord($h, $y, $R, $C);
                }
                    
                $y = [
                    $RecordIdField => $record
                ];

                if ( $export_layout!=="h" && REDCap::isLongitudinal() ) {
    
                    $y[VARNAME_EVENT_ID  ] = $x['event_id'];
                    $y[VARNAME_EVENT_NAME] = $eventName[$x['event_id']];
                }

                if ( $export_layout==="r" ) {
    
                    $y[VARNAME_INSTANCE  ] = $x_instance;
                }

                /**
                 * fill out the record
                 */

                foreach ($dd as $d){

                    if ( !isset($y[$d['var_name']]) ){

                        $y[$d['var_name']] = "";
                    }

                    /**
                     * constant specmap field?
                     */
                    if ( substr($d['redcap_field_name'], 0, 9)==="constant:" ) {

                        $y[$d['var_name']] = str_replace("'", "", trim(substr($d['redcap_field_name'], 9)));
                    }
                }

                $exportValues = 0;

                $BOR = false;
            }

            /**
             * add the value to the record
             */

            $event_id = $x['event_id'];

            $field_name = $x['field_name'];

            $REDCapValue = $this->conditionREDCapValue( $x['value'], $export_max_text_length, $export_inoffensive_text );

            if ( $field_name === "__GROUPID__" ) {

                $y[VARNAME_GROUP_ID  ]   = $x['value'];
                $y[VARNAME_GROUP_NAME] = $dagNameForGroupId[ $x['value'] ];
            }

            $instance = $x_instance;

            if ( $export_layout==="h" ){

                $field_index = $dd_index[$field_name][$event_id] ?? -1;
                $specmap_field_index = $dd_specmap_index[$field_name][$event_id] ?? -1;
            }
            else {

                $field_index = $dd_index[$field_name] ?? -1;
                $specmap_field_index = $dd_specmap_index[$field_name] ?? -1;
            }

            if ( $field_index > -1 && $field_name !== $RecordIdField ){

                $exportValues++;

                /**
                 * goddam multiselects
                 */
                if ( $dd[$field_index]['var_type'] === "CHECKBOX" ){

                    if ( strlen($y[ $dd[ $field_index]['var_name'] ]) ) {

                        $y[ $dd[ $field_index]['var_name'] ] .= ",";
                    }

                    $y[ $dd[ $field_index]['var_name'] ] .= $REDCapValue;
                }
                else {

                    if ( $this->isDateOrTimeType($dd[$field_index]['var_type']) && $days_to_shift > 0 ) {

                        $y[ $dd[ $field_index]['var_name'] ] = $this->shift_date_format($REDCapValue, $days_to_shift);
                    }
                    else {

                        $y[ $dd[ $field_index]['var_name'] ] = $REDCapValue;
                    }
                }

                $K++;

                $this->doValidationCalculations($dd[$field_index], $REDCapValue);
            }

            if ( $specmap_field_index > -1 ){

                //print "<br>Specification block entered for [{$record}] [{$x['event_id']}] [{$field_name}] [{$x['value']}] [{$specmap_field_index}]";

                $specValue = "";

                if ( is_array($dd[$specmap_field_index]['valueset']) && count($dd[$specmap_field_index]['valueset'])>0 ){

                    foreach( $dd[$specmap_field_index]['valueset'] as $vMap ){

                        if ( $REDCapValue == $vMap['redcap_field_value'] ){

                            $specValue = $vMap['value'];
                            break;
                        }

                        elseif ( strpos($vMap['redcap_field_value'], ",")!==false ){

                            $vMapREDCapValues = explode(",", $vMap['redcap_field_value']);

                            foreach ($vMapREDCapValues as $vMapREDCapValue){

                                if ( $REDCapValue == trim($vMapREDCapValue)){

                                    $specValue = $vMap['value'];
                                    break(2);   
                                }
                            }
                        }
                    }
                }

                // no valueset, so just assign the REDCap value
                else {

                    $specValue = $REDCapValue;
                }

                //print "<br>--> specValue = [{$specValue}]";

                $y[ $dd[$specmap_field_index]['var_name'] ] = $specValue;

                $K++;

                $this->doValidationCalculations($dd[$specmap_field_index], $specValue);
           }

        }

        if ( $y && $exportValues ){

            $bytesWritten += $this->writeExportRecord($h, $y, $R, $C);
        }

        return $bytesWritten;
    }

    private function conditionREDCapValue( $x, $export_max_text_length, $export_inoffensive_text )
    {
        if ( !strlen($x) ){

            return "";
        }

        if ( $export_inoffensive_text ){

            $x = Yes3::inoffensiveText( $x );
        }

        if ( $export_max_text_length > 0 && strlen($x) > $export_max_text_length ){

            $x = substr( $x, 0, $export_max_text_length );
        }

        return $x;
    }

    private function doValidationCalculations( &$d, $value )
    {
        $len = strlen($value);

        if ( !$len ){

            return false;
        }

        $var_type = $d['var_type'];

        $d['non_missing_count']++;

        if ( $len > $d['max_length'] ) {

            $d['max_length'] = $len;
        }

        if ( $len < $d['min_length'] ) {

            $d['min_length'] = $len;
        }

        if ( $var_type === "NOMINAL" ){

            // force an associative array
            $vIndex = (string) $value;

            if ( !isset($d['frequency_table'][$vIndex]) ){

                $d['frequency_table'][$vIndex] = 1;
            }
            else {

                $d['frequency_table'][$vIndex]++;
            }
        }
        else {

            if ( $var_type==="FLOAT" || $var_type==="INTEGER" ){

                $v = $value;
            }

            elseif ( $this->isDateOrTimeType($var_type) ){

                $v = strtotime($value);
            }

            else {

                $v = NULL;
            }

            if ( $v !== NULL ) {

                /**
                 * All accumulators start out NULL
                 */
                if ( $d['min_value']===NULL ) {

                    $d['min_value'] = $v;
                    $d['max_value'] = $v;

                    $d['sum_of_values'] = (float) $v;
                    $d['sum_of_squared_values'] = (float) $v*$v;
                }
                else {

                    $d['sum_of_values'] += (float) $v;
                    $d['sum_of_squared_values'] += (float) $v*$v;

                    if ( $v > $d['max_value'] ){

                        $d['max_value'] = $v;
                    }

                    if ( $v < $d['min_value'] ){

                        $d['min_value'] = $v;
                    }
                }
            }
        }
        
        return true;
    }

    private function writeExportRecord( $h, $y, &$rowNumber, &$colCount ){

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

        $response = "";

        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $results = $this->writeExportFiles($ddPackage);

        if ( $ddPackage['export_fields_rejected'] ){

            $response .= "Note: " . $ddPackage['export_fields_rejected'] . " fields were rejected because of form permissions." . "\n\n";
        }

        $response .= $results['export_data_dictionary_message']
                    . "\n\n" 
                    . $results['export_data_message']
        ;

        $t = time() - $t;

        $response .= "\n\nElapsed time: {$t} seconds.";

        return nl2br($response);
    }

    public function downloadDataDictionary($export_uuid)
    {
     
        $h = fopen('php://output', 'w');

        if ( $h===false ){

            throw new Exception("Fail: could not open PHP output stream.");
        }

        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $filename = $this->exportDataDictionaryFilename( $ddPackage['export_name'], "download" );

        $this->tidyUpDD($ddPackage['export_data_dictionary'], true);

        $delim = ",";

        $this->logExport(
            "export data dictionary downloaded",
            "download",
            $export_uuid,
            $ddPackage['export_name'],
            null,
            $filename,
            null,
            null,
            null,
            null,
            null
        );

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

        //exit;
    }

    public function downloadData($export_uuid)
    {
        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $xFileResponse = $this->writeExportFiles($ddPackage, "download");     

        if ( !isset( $xFileResponse['export_data_filename'] ) ) {

            throw new Exception("Fail: download export file not written");
        }

        $h = fopen( $xFileResponse['export_data_filename'], "r");

        if ( $h === false ) {

            throw new Exception("Fail: download export file could not be opened");
        }

        $filename = $this->exportDataFilename( $ddPackage['export_name'], "download" );

        $chunksize = 1024 * 1024; // 1MB per one chunk of file.

        $size = intval(sprintf("%u", $xFileResponse['export_data_file_size']));

        $this->logExport(
            "export data downloaded",
            "download",
            $export_uuid,
            $ddPackage['export_name'],
            $filename,
            null,
            null,
            $size,
            null,
            null,
            null
        );

        ob_start();

        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$size);
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        while (!feof($h)){

            print(@fread($h, $chunksize));

            ob_flush();
            flush();
        }

        fclose($h);

        ob_end_flush();

        //exit;
    }

    public function downloadZip($export_uuid)
    {
        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $bytesWritten = 0;

        $xFileResponse = $this->writeExportFiles($ddPackage, "download", $bytesWritten);
        /*
        print nl2br(print_r($xFileResponse, true));

        exit;
        */

        if ( !isset( $xFileResponse['export_data_filename']) || !isset( $xFileResponse['export_data_dictionary_filename']) ) {

            throw new Exception("Fail: download export file(s) not written");
        }

        $zipFilename = tempnam(sys_get_temp_dir(), "ys3");

        $zip = new ZipArchive;

        $zip->open($zipFilename, ZipArchive::CREATE);

        $zip->addFile($xFileResponse['export_data_dictionary_filename'], $this->exportDataDictionaryFilename($ddPackage['export_name'], "download"));

        $zip->addFile($xFileResponse['export_data_filename'], $this->exportDataFilename($ddPackage['export_name'], "download"));

        $zip->close();

        $filename = $this->exportZipFilename( $ddPackage['export_name'], "download" );

        $chunksize = 1024 * 1024; // 1MB per one chunk of file.

        $size = intval(sprintf("%u", filesize($zipFilename)));

        $h = fopen($zipFilename, "r");

        if ( $h === false ) {

            throw new Exception("Fail: download export file could not be opened");
        }

        $this->logExport(
            "export zip downloaded",
            "download",
            $export_uuid,
            $ddPackage['export_name'],
            null,
            null,
            $filename,
            $size,
            null,
            null,
            null
        );

        ob_start();

        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$size);
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        while (!feof($h)){

            print(@fread($h, $chunksize));

            ob_flush();
            flush();
        }

        fclose($h);

        ob_end_flush();

        //exit;
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

    public function exportDataFilename( $export_name, $target="download")
    {
        $extension = "csv"; // will work on this later

        if ( $target==="download") {
            return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_" . Yes3::timeStampString() . "." . $extension;
        }

        return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "." . $extension;
    }

    public function exportDataDictionaryFilename( $export_name, $target="download")
    {
        $extension = "csv"; // will work on this later

        if ( $target==="download") {
            return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_dd_" . Yes3::timeStampString() . "." . $extension;
        }

        return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_dd." . $extension;
    }

    public function exportZipFilename( $export_name, $target="download")
    {
        $extension = "zip"; // will work on this later

        if ( $target==="download") {
            return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_" . Yes3::timeStampString() . "." . $extension;
        }

        return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "." . $extension;
    }

    public function exportLogFilename( $export_name, $target="download")
    {
        $extension = "csv"; // will work on this later

        if ( $target==="download") {
            return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_log_" . Yes3::timeStampString() . "." . $extension;
        }

        return substr(Yes3::alphaNumericString(str_replace(" ", "_", strtolower(trim($export_name)))), 0, 64) . "_log_." . $extension;
    }

    public function getEventSettings()
    {
        if ( !REDCap::isLongitudinal() ){

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

    /**
     * 
     * 
     * function: getExportSpecification
     * 
     * @param mixed $export_uuid
     * 
     * @return array
     * @throws Exception
     */
    public function getExportSpecification( $export_uuid, $log_id=0, $history=false ): array
    {        
        $fields = "log_id, message, timestamp
        , removed
        , export_uuid
        , export_name
        , export_username
        , export_layout
        , export_selection
        , export_criterion_field
        , export_criterion_event
        , export_criterion_value
        , export_target
        , export_target_folder
        , export_max_label_length
        , export_max_text_length
        , export_inoffensive_text
        , export_remove_phi
        , export_remove_freetext
        , export_remove_largetext
        , export_remove_dates
        , export_shift_dates
        , export_hash_recordid
        , export_uspec_json
        , export_items_json
        ";

        //Yes3::logDebugMessage($this->project_id, $export_uuid, "getExportSpecification");

        if ( $log_id ){

            $pSql = "SELECT {$fields} WHERE log_id=?";
            $params = [ $log_id ];
        }
        else {
            $pSql = "
                SELECT {$fields}
                WHERE project_id=? AND message=? AND export_uuid=?
                ORDER BY timestamp DESC
            ";
            $params = [$this->project_id, EMLOG_MSG_EXPORT_SPECIFICATION, $export_uuid];
        }

        if ( $history ){

            //Yes3::logDebugMessage($this->project_id, $this->getQueryLogsSql($pSql), 'getExportSpecification');
            //Yes3::logDebugMessage($this->project_id, print_r($params, true), 'getExportSpecification');

            $qResult = $this->queryLogs($pSql, $params);

            $specs = [];
            while ( $spec = $qResult->fetch_assoc() ){

                $specs[] = $spec;
            }

            return $specs;
        }
        else {

            return $this->queryLogs($pSql." LIMIT 1", $params)->fetch_assoc();
        }
    }

    /**
     * 'export specification' here is equiv to the preferred 'upload specification' or 'specMap'
     * This is stored as a JSON string in the settings.
     * 
     * The data dictionary is based on the 'field map' which is fetched by getExportElements.
     * 
     * 'specification' is also used to refer to the field map. A lot of under-the-hood ambiguity to clean up,
     * 
     * function: getExportSpecifications
     * 
     * 
     * @return array
     * @throws Exception
     */
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

 
                if ( Yes3::is_json_decodable($specification_settings['export_specification_json'])) {
                    
                    $specification = json_decode($specification_settings['export_specification_json']);

                    //Yes3::logDebugMessage($this->project_id, print_r($specification, true), "getExportSpecifications:object");

                    if ( is_object($specification) ){

                        if ( !is_object($specification->mapping_specification) ) {

                            $specification->mapping_specification = ['elements'=>[]];
                        }

                        if ( !$specification->removed ) {

                            $specification->removed = "0";
                        }

                        $specifications[] = $specification;
                    }
                }
            }
        }

        return $specifications;
    }

    public function getDefaultExportEvents()
    {
        if ( !REDCap::isLongitudinal() ){
            return [];
        }

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

        if ( $isLong = REDCap::isLongitudinal() ) {

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
                        'event_label' => Yes3::inoffensiveText($e['descrip']),
                        'descrip' => Yes3::inoffensiveText($e['descrip'])
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
    
            $form_name = Yes3::inoffensiveText($m['form_name']);

            $form_metadata[] = [
                'form_name' => $form_name,
                'form_label' => Yes3::inoffensiveText($m['form_menu_description']),
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
        if ( REDCap::isLongitudinal() ){

            $sql = "
            SELECT DISTINCT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum, m.element_validation_type, m.field_phi
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
            SELECT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum, m.element_validation_type, m.field_phi
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
                        'label' => Yes3::inoffensiveText($label, MAX_LABEL_LEN)
                    ];
                }
            }

            $field_label = Yes3::inoffensiveText( $field['element_label'], MAX_LABEL_LEN );

            $field_metadata[] = [

                'field_name'        => $field['field_name'],
                'form_name'         => Yes3::inoffensiveText($field['form_name']),
                'field_type'        => $field['element_type'],
                'field_validation'  => $field['element_validation_type'],
                'field_phi'         => $field['field_phi'],
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

    private function addExportItem_Specification( $export, $element, $event_settings, $export_uspec )
    {
        $valueset = [];

        // fetch the corresponding uspec item
        foreach ($export_uspec['elements'] as $uspec_element ){

            if ( $uspec_element['name']===$element['uspec_element_name'] ){

                /**
                 * update the uSpec valueset with mapped REDCap field values
                 */
                
                $valueset = [];

                foreach( $uspec_element['valueset'] as $v ){

                    $redcap_field_value = "";

                    // walk through the value map for this export specification
                    foreach( $element['uspec_element_value_map'] as $vMap ){
                        
                        if ( $vMap['uspec_value']==$v['value'] ){

                            $redcap_field_value = $vMap['redcap_field_value'];
                            break;
                        }
                    }

                    $valueset[] = [
                        'value' => $v['value'],
                        'label' => $v['label'],
                        'redcap_field_value' => $redcap_field_value
                    ];
                }

                $export->addExportItem([
                    'var_name' => $uspec_element['name'],
                    'var_label' => $uspec_element['label'],
                    'var_type' => $this->specificationTypeToVarType( $uspec_element['type'], $valueset ),
                    'valueset' => $valueset,
                    'origin' => "specification",
                    'redcap_field_name' => $element['redcap_field_name'],
                    'redcap_form_name'  => Yes3::getREDCapFormForField($element['redcap_field_name']),
                    VARNAME_EVENT_ID    => $element[VARNAME_EVENT_ID],
                    VARNAME_EVENT_NAME  => $this->getEventName($element[VARNAME_EVENT_ID], $event_settings)
                ]);

                break;    
            }
        }
    }

    private function addExportItem_REDCapField( $export, $redcap_field_name, $redcap_event_id, $fields, $forms, $event_settings, $allowed )
    {
        $field_index = $fields['field_index'][$redcap_field_name];

        $form_name = $fields['field_metadata'][$field_index]['form_name'];

        if ( !in_array($form_name, $allowed['forms']) ){

            return 0;
        }

        $field_type = $fields['field_metadata'][$field_index]['field_type'];

        $field_validation = $fields['field_metadata'][$field_index]['field_validation'];

        $field_phi = ( $fields['field_metadata'][$field_index]['field_phi'] == "1" );

        $field_largetext = ( $fields['field_metadata'][$field_index]['field_type']==="textarea" );

        $field_smalltext = false;

        $field_date = false;

        if ( $field_type === "text" ){

            if ( !$field_validation ){

                $field_smalltext = true;
            }
            elseif ( $this->isDateType($this->REDCapFieldTypeToVarType( $field_type, $field_validation )) ) {

                $field_date = true;
            }
        }

        $msg = "redcap_field_name={$redcap_field_name}: form_name={$form_name}, field_type={$field_type}, field_validation={$field_validation}, field_phi={$field_phi}"
        .", field_largetext={$field_largetext}"
        .", field_smalltext={$field_smalltext}"
        .", field_date={$field_date}."
        ."\nallowed: phi={$allowed['phi']} largetext={$allowed['largetext']} smalltext={$allowed['smalltext']} dates={$allowed['dates']}."
        ;

        //Yes3::logDebugMessage($this->project_id, $msg, "addExportItem_REDCapField");

        if ( $field_phi && !$allowed['phi'] ){

            return 0;
        }

        if ( $field_largetext && !$allowed['largetext'] ){

            return 0;
        }

        if ( $field_smalltext && !$allowed['smalltext'] ){

            return 0;
        }

        if ( $field_date && !$allowed['dates'] ){

            return 0;
        }

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
                    'var_type' => $this->REDCapFieldTypeToVarType($field_type, $field_validation),
                    'valueset' => $fields['field_metadata'][$field_index]['field_valueset'],
                    'origin' => "redcap",
                    'redcap_field_name' => $redcap_field_name,
                    'redcap_form_name' => $form_name,
                    VARNAME_EVENT_ID => $event_id,
                    VARNAME_EVENT_NAME => $this->getEventName($event_id, $event_settings)
                ]);
            }
        }

        return 1;
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
            VARNAME_EVENT_ID => "",
            VARNAME_EVENT_NAME => ""
        ]);
    }

    private function addExportItem_REDCapForm( $export, $redcap_form_name, $redcap_event_id, $fields, $forms, $event_settings, $allowed )
    {

        $form_names = [];

        if ( $redcap_form_name === ALL_OF_THEM ){

            foreach ( $forms['form_metadata'] as $form ){

                if ( in_array($form['form_name'], $allowed['forms']) && !$form['form_repeating']) {

                    $includeForm = ( $redcap_event_id === ALL_OF_THEM || !REDCap::isLongitudinal() );

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
                }
            }
        }
        else {

            if ( in_array($redcap_form_name, $allowed['forms']) ){
    
                $form_names = [ $redcap_form_name ];
            }
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

                    $this->addExportItem_REDCapField($export, $field_name, $event_id, $fields, $forms, $event_settings, $allowed);
                }
            }
        }

        return count($form_names);
    }

    private function exportFieldName( $export, $field_name, $event_id, $event_settings)
    {
        if ( $export->export_layout==="h" && $field_name !== REDCap::getRecordIdField() ) {
    
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

    private function REDCapFieldTypeToVarType( $field_type, $field_validation )
    {
        if ( $field_type === "radio" ) return "NOMINAL";
        if ( $field_type === "dropdown" ) return "NOMINAL";
        if ( $field_type === "yesno" ) return "NOMINAL";
        if ( $field_type === "truefalse" ) return "NOMINAL";
        if ( $field_type === "checkbox" ) return "CHECKBOX";
        if ( $field_type === "select" ) return "NOMINAL";
        if ( $field_type === "slider" ) return "INTEGER";
        if ( $field_type === "calc" ) return "FLOAT";

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
    
        $uRights =  $this->yes3UserRights();

        if ( $uRights['export'] || $uRights['isDesigner'] ){

            return $link;
        }

        return false;
    }
}
