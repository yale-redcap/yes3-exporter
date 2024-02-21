<?php

namespace Yale\Yes3FieldMapper;
/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

/**
 * defines and enums
 */
require "defines/yes3_defines.php";

/**
 * an autoloader for Yes3 classes and traits
 */
require "autoload.php";

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
    public $imageUrl = [];
    public $documentationUrl = "";
    public $changelogUrl = "";
    public $technicalDocumentationUrl = "";
    public $form_export_permissions = [];
    private $token = "";
    private $salt = "";
    private $project_salt = "";
    private $date_shift_max = "";

    use Yes3Trait;

    public function __construct() {

        global $salt;

        parent::__construct(); // call parent (AbstractExternalModule) constructor

        $projId = $this->getProjectId();

        if ( $projId ){

            $this->project_id = $projId;

            $this->username = $this->getUser()->getUsername();
            $this->serviceUrl = $this->getUrl('services/services.php');
            $this->documentationUrl = $this->getUrl('plugins/yes3_documentation.php?doc=README');
            $this->changelogUrl = $this->getUrl('plugins/yes3_documentation.php?doc=documents%2Fchangelog');
            $this->technicalDocumentationUrl = $this->getUrl('plugins/yes3_documentation.php?doc=documents%2Ftechnical');
            $this->imageUrl = [
                'dark' => [
                    'logo_square' => $this->getUrl('images/YES3_Logo_Square_Black.png'),
                    'logo_horizontal' => $this->getUrl('images/YES3_Logo_Horizontal_Black.png')
                ],
                'light' => [
                    'logo_square' => $this->getUrl('images/YES3_Logo_Square_White.png'),
                    'logo_horizontal' => $this->getUrl('images/YES3_Logo_Horizontal_White.png')
                ]
            ];
            $this->RecordIdField = $this->getRecordIdField();
            $this->isLongitudinal = REDCap::isLongitudinal();

            $this->token = "this-should-be-private";

            $Proj = new Project();

            $this->salt = $salt;
            $this->project_salt = $Proj->project['__SALT__'];
            $this->date_shift_max = (int)$Proj->project['date_shift_max'];

            $this->form_export_permissions = $this->yes3UserRights()['form_export_permissions'];

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
        $event_settings = $this->getEventSettings(); /* event abbreviations */
    
        /**
         * Export specification (assoc array):
         * 
         *      export_uuid
         *      export_name
         *      export_label
         *      export_order
         *      export_username
         *      export_layout
         *      export_multiselect
         *      export_selection
         *      export_criterion_field
         *      export_criterion_event
         *      export_criterion_value
         *      export_target
         *      export_max_label_length
         *      export_max_text_length
         *      export_inoffensive_text
         *      export_remove_phi
         *      export_remove_freetext
         *      export_remove_largetext
         *      export_remove_dates
         *      export_shift_dates
         *      export_hash_recordid
         *      export_uspec_json
         *      export_items_json
         *      export_batch
         *      removed
         *      
         */
        $export_specification = $this->getExportSpecification($export_uuid);

        // add filesystem target, which is stored in EM settings
        $export_specification['export_target_folder'] = $this->get_export_target_folder();


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
         *      ...
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

        if ( !$uRights['exporter'] ){

            throw new Exception("ERROR: User does not have permission to export data.");
        }
        /*
        $allowed = [
            'group_id' => 0,
            'forms' => [],
            'phi' => ( $export->export_remove_phi ) ? 0 : (($uRights['export']==1) ? 1:0),
            'dates' => ( $export->export_remove_dates ) ? 0 : (($uRights['export']==1 || $uRights['export']==3) ? 1:0),
            'smalltext' => ( $export->export_remove_freetext ) ? 0 : (($uRights['export']==1 || $uRights['export']==3) ? 1:0),
            'largetext' => ( $export->export_remove_freetext || $export->export_remove_largetext) ? 0 : (($uRights['export']==1 || $uRights['export']==3) ? 1:0)
        ];
        */
        $allowed = [
            'group_id' => 0,
            'forms' => [],
            'phi' => ( $export->export_remove_phi ) ? 0 : 1,
            'dates' => ( $export->export_remove_dates ) ? 0 : 1,
            'smalltext' => ( $export->export_remove_freetext ) ? 0 : 1,
            'largetext' => ( $export->export_remove_freetext || $export->export_remove_largetext) ? 0 : 1
        ];

        /**
         * list of forms for which the user has at least some export rights
         */
        foreach ($uRights['form_export_permissions'] as $form_name => $xPerm ){

            if ( (int)$xPerm > 0 ){

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
         *      events [event_id, ...]
         * 
         *      for computation:
         * 
         *      source_field
         *      source_option_value
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
        $field_name = $this->getRecordIdField();

        $this->addExportItem_REDCapField( $export, $field_name, Yes3::getREDCapEventIdForField($field_name), $fields, $forms, $event_settings, $allowed, $uRights['form_export_permissions'] );

        if ( REDCap::getGroupNames() ) {

            $this->addExportItem_otherProperty($export, VARNAME_GROUP_ID,   "REDCap Data Access Group Id", "INTEGER");
            $this->addExportItem_otherProperty($export, VARNAME_GROUP_NAME, "REDCap Data Access Group Name", "TEXT");
        }

        if ( $export->export_layout !== "h" && REDCap::isLongitudinal() ) {

            $this->addExportItem_otherProperty($export, VARNAME_EVENT_ID,   "REDCap Event Id", "INTEGER");
            $this->addExportItem_otherProperty($export, VARNAME_EVENT_NAME, "REDCap Event Name", "TEXT");
        }
/*
        if ( $export->export_layout === "r" ) {

            $this->addExportItem_otherProperty($export, VARNAME_INSTANCE, "REDCap Repeat Instance", "INTEGER");
        }
*/
        /**
         * as of v1.1.0, instance is included on every export
         */
        $this->addExportItem_otherProperty($export, VARNAME_INSTANCE, "REDCap Repeat Instance", "INTEGER");

        $export_items = json_decode( $export_specification['export_items_json'], true);

        $export_uspec = json_decode( $export_specification['export_uspec_json'], true);
    
        foreach ($export_items as $element){

            /**
             * The form export spec will not include the event_id for a non-longitudinal project, so set it to the first and only..
             */
            if ( !REDCap::isLongitudinal() ){

                $element[VARNAME_EVENT_ID] = Yes3::getFirstREDCapEventId();
            }

            //Yes3::logDebugMessage($this->project_id, print_r($element, true), "buildExportDataDictionary: ELEMENT");

            if ( $element['export_item_origin'] === 'specification' ) {
    
                $this->addExportItem_Specification( $export, $element, $event_settings, $export_uspec );
            }
    
            elseif ( $element['redcap_field_name'] ) {
    
                $this->addExportItem_REDCapField( $export, $element['redcap_field_name'], $element[VARNAME_EVENT_ID], $fields, $forms, $event_settings, $allowed, $uRights['form_export_permissions'] );
            }
    
            elseif ( $element['redcap_form_name'] ) {

     
                $this->addExportItem_REDCapForm( $export, $element['redcap_form_name'], $element[VARNAME_EVENT_ID], $fields, $forms, $event_settings, $allowed, $uRights['form_export_permissions'] );
            }
        }

        $dd = [];

        $fields_rejected = 0;

        //Yes3::logDebugMessage($this->project_id, print_r($uRights['form_permissions'], true), "buildExportDataDictionary:fp");
        //Yes3::logDebugMessage($this->project_id, print_r($export->export_items, true), "buildExportDataDictionary:fp");

        foreach( $export->export_items as $export_item ){

            $dd[] = [
                'var_name' => $export_item->var_name,
                'var_label' => Yes3::truncate( $export_item->var_label, $export->export_max_label_length ),
                'var_type' => $export_item->var_type,
                'valueset' => ( $export_item->valueset ) ? json_encode($export_item->valueset) : "",
                'origin' => $export_item->origin,
                'redcap_field_name' => $export_item->redcap_field_name,
                'redcap_source_option' => $export_item->multiselect_option,
                'redcap_form_name' => $export_item->redcap_form_name,
                'redcap_events' => json_encode($export_item->redcap_events),
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

        /**
         * Goal: reduce this down to 3 props: export_specification, export_data_dictionary, fields_rejected
         */

        return [
            'export_uuid' => $export_uuid,
            'export_name' => $export->export_name,
            'export_label' => $export->export_label,
            'export_order' => $export->export_order,
            'export_layout' => $export->export_layout,
            'export_multiselect' => $export->export_multiselect,
            'export_batch' => $export->export_batch,
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
            'export_event_list' => $export->export_event_list,
            'export_specification' => $export_specification,
            'export_data_dictionary' => $dd,
            'export_fields_rejected' => $fields_rejected
        ];

        //$this->download($export->export_name . "_dd", $dd);
    
        //return count($dd) . " export columns defined.";
    }

    /**
     * Returns a non-assoc array suitable for CSV export
     * Utility columns removed
     * 
     * function: dataDictionaryForExport
     * 
     * @param mixed $dd
     * @param mixed $export_layout
     * 
     * @return void
     */
    private function dataDictionaryForExport( $dd, $export_layout, $dd_only_download=false):array
    {
        // columns that must be removed if this a dd-only download
        $dx_colnames = [
            'non_missing_count',
            'min_length',
            'max_length',
            'min_value',
            'max_value',
            'sum_of_values',
            'sum_of_squared_values', 
            'mean', 
            'standard_deviation', 
            'formatted_min_value',
            'formatted_max_value',
            'formatted_mean',
            'frequency_table'  
        ];

        // delete event columns as needed
        $columns_to_delete = [];

        $colnames = array_keys($dd[0]);

        $xx = [
            []
        ];

        for ($i=0; $i<count($colnames); $i++){

            if ( $colnames[$i]==="redcap_events") {

                $columns_to_delete[] = $i;
            }

            elseif ( $dd_only_download && in_array($colnames[$i], $dx_colnames) ){

                $columns_to_delete[] = $i;
            }

            elseif ( $export_layout !== "h" && ($colnames[$i]==="redcap_event_id" || $colnames[$i]==="redcap_event_name")){

                $columns_to_delete[] = $i;  
            }

            else {

                $xx[0][] = $colnames[$i];
            }
        }

        foreach ($dd as $dditem ){

            $v = array_values($dditem);

            $x = [];

            for ($i=0; $i<count($v); $i++){

                if ( !in_array($i, $columns_to_delete) ){

                    $x[] = $v[$i];
                }
            }

            $xx[] = $x;
        }

        return $xx;
    }

    private function writeExportInfoFile($export_name, $export_target_folder, $export_uuid, $export_layout, $bytesWritten, $R, $C, $data_file_path, $destination){
       
        /*
        if ( !$export_target_folder || $destination==="download" ) {

            $root = sys_get_temp_dir();
            $path = tempnam($root, "ys3");

        }
        else {

            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $export_target_folder .= DIRECTORY_SEPARATOR;
            }

            $root = $export_target_folder;
            $path = $root . $this->exportInfoFilename($export_name, $destination);           
        }

        $h = $this->fopen_w_utf8( $path, "w+", $root );

        if ( $h===false ){

            throw new Exception("Fail: could not create export file {$path}");
        }        
        */

        $path = "";

        $h = $this->export_file_handle( $path, $destination, $export_target_folder, $this->exportInfoFilename($export_name, $destination) );

        $project = $this->getProject();

        $info = [
            "host" => APP_PATH_WEBROOT_FULL,
            "timestamp" => strftime("%F %T"),
            "project_id" => $project->getProjectId(),
            "project_recordid_field" => $this->getRecordIdField(),
            "project_title" => $project->getTitle(),
            "project_is_longitudinal" => ( \REDCap::isLongitudinal() ) ? 1:0,
            "project_has_dags" => ( \REDCap::getGroupNames() !== FALSE ) ? 1:0,
            "export_name" => $export_name,
            "export_layout" => $export_layout,
            "export_uuid" => $export_uuid,
            "export_target_folder" => $export_target_folder,
            "path" => $data_file_path,
            "bytes_written" => $bytesWritten,
            "columns" => $C,
            "rows" => $R,
            "destination" => $destination,
            "notification_email" => $this->getProjectSetting("notification-email"),
            "username" => $this->username
        ];

        $json = Yes3::json_encode_pretty($info);

        $bytesWritten = fwrite($h, $json);

        fclose($h);

        return [
            'export_info_filename' => $path,
            'export_info_message' => "Success: {$bytesWritten} bytes written to {$path}.",
            'export_info_file_size' => $bytesWritten,
            'export_info' => $info
        ];
    }

    private function writeExportDataDictionaryFile( $export_name, $export_target_folder, $dd, $destination, $export_layout, &$bytesWritten=0 )
    {
        $delim = ",";
        /*
        if ( !$export_target_folder || $destination==="download" ) {

            $path = tempnam(sys_get_temp_dir(), "ys3");
        }
        else {

            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $export_target_folder .= DIRECTORY_SEPARATOR;
            }

            $path = $export_target_folder . $this->exportDataDictionaryFilename($export_name, "filesystem");           
        }

        //$h = fopen( $path, "w+" );
        $h = $this->fopen_w_utf8( $path );

        if ( $h===false ){

            throw new Exception("Fail: could not create export file {$path}");
        }

        $path = "";
        */

        $h = $this->export_file_handle( $path, $destination, $export_target_folder, $this->exportDataDictionaryFilename($export_name, "filesystem") );

        $R = 0;

        $xx = (array) $this->dataDictionaryForExport($dd, $export_layout);

        $C = count($xx[0]);
           
        $bytesWritten = 0;
     
        foreach ( $xx as $x ) {

            $bytesWritten += fputcsv($h, $x, $delim);
            $R++;
        }
     
        fclose($h);

        return [
            'export_data_dictionary_message' => "Success: {$bytesWritten} bytes, {$R} rows and {$C} columns written to {$path}.",
            'export_data_dictionary_filename' => $path,
            'export_data_dictionary_file_size' => $bytesWritten
        ];
    }

    private function export_file_handle( &$path, &$destination, $export_target_folder, $filename="", $utf8 = true)
    {
        if ( !$export_target_folder || $destination==="download" ) {

            $root = sys_get_temp_dir();
            $path = tempnam($root, "ys3");
        }
        else {

            $root = $export_target_folder;
            if ( substr($export_target_folder, -1) !== DIRECTORY_SEPARATOR ){

                $root .= DIRECTORY_SEPARATOR;
            }
            $path = $root . $filename;   
        }

        return $this->fopen_w_safe( $path, "w+", $root, $utf8 );
    }

    public function fopen_w_safe( $filename, $mode="w", $root = "", $utf8=true )
    {
        $h = fopen( $this->getSafePath($filename, $root), $mode );

        if ( $h===false ){

            throw new Exception("Fail: could not open file " . $this->getSafePath($filename, $root) );
        }

        if ( $utf8 ){

            fwrite($h, "\xEF\xBB\xBF" );
        }

        return $h;
    }

    /**
     * fopen_r
     * 
     * @param mixed $path   path of file to open, must be from the PHP system temp dir
     * @return resource 
     * @throws Exception 
     */
    public function fopen_r_safe( $path )
    {
        $h = fopen(  $this->getSafePath($path, sys_get_temp_dir()), 'r' );

        if ( $h===false ){

            throw new Exception("Fail: could not open file " . $this->getSafePath($path, sys_get_temp_dir()) );
        }

        return $h;
    }

    public function get_export_target_folder()
    {
        $enable_host_filesystem_exports = $this->getProjectSetting("enable-host-filesystem-exports");

        if ( $enable_host_filesystem_exports !== "Y" ){

            return "";
        }

        $etf = $this->getProjectSetting("export-target-folder");

        if ( !$etf ) $etf = "";

        return $etf;
    }

    private function writeExportFiles( &$ddPackage, $destination="", &$bytesWritten=0)
    {
        //exit( print_r($eventSpecs, true) )

        // for code clarity
        $export_uuid                = $ddPackage['export_uuid'];
        $export_name                = $ddPackage['export_name'];
        $export_target_folder       = $this->get_export_target_folder();
        $export_layout              = $ddPackage['export_layout'];
        $export_max_text_length     = (int)$ddPackage['export_max_text_length'];
        $export_inoffensive_text    = (int)$ddPackage['export_inoffensive_text'];
        $export_shift_dates         = (int)$ddPackage['export_shift_dates'];
        $export_group_id            = (int)$ddPackage['export_group_id'];
        $export_hash_recordid       = (int)$ddPackage['export_hash_recordid'];
        $export_event_list          = $ddPackage['export_event_list'];

        $dd = $ddPackage['export_data_dictionary'];

        $export_specification = $ddPackage['export_specification'];

        /*
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

        //$h = fopen( $path, "w+" );
        $h = $this->fopen_w_utf8( $path );

        if ( $h===false ){

            throw new Exception("Fail: could not create export file {$path}");
        }
        */

        $path = "";

        $h = $this->export_file_handle( $path, $destination, $export_target_folder, $this->exportDataFilename($export_name, "filesystem") );

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
        //$events = [];
        $dd_index = [];
        $dd_specmap_index = [];
        $dd_multiselect_index = [];

        //Yes3::logDebugMessage($this->project_id, print_r($dd, true), "writeExportFiles: dd");

        for ($i=0; $i<count($dd); $i++){

            if ( $export_layout==="h" ){

                if ( $dd[$i]['redcap_field_name'] && $dd[$i][VARNAME_EVENT_ID] && is_numeric($dd[$i][VARNAME_EVENT_ID]) ){

                    if ( $dd[$i]['origin'] === "redcap" ){

                        $dd_index[$dd[$i]['redcap_field_name']][$dd[$i][VARNAME_EVENT_ID]] = $i;

                        if ( $this->ddIsMultiselect($dd[$i]) ){

                            $dd_multiselect_index[$dd[$i]['redcap_field_name']][$dd[$i][VARNAME_EVENT_ID]][$dd[$i]['redcap_source_option']] = $i;
                        }
                    }

                    elseif ( $dd[$i]['origin'] === "specification" ){

                        $dd_specmap_index[$dd[$i]['redcap_field_name']][$dd[$i][VARNAME_EVENT_ID]] = $i;
                    }
                    
                    //if ( !in_array($dd[$i][VARNAME_EVENT_ID], $events) ){

                        //$events[] = $dd[$i][VARNAME_EVENT_ID];
                    //}
                }
            }
            else {

                if ( $dd[$i]['redcap_field_name'] ){

                    if ( $dd[$i]['origin'] === "redcap" ){

                        if ( $this->ddIsMultiselect($dd[$i]) ){

                            $dd_multiselect_index[$dd[$i]['redcap_field_name']][$dd[$i]['redcap_source_option']] = $i;
                        }
                        else {

                            $dd_index[$dd[$i]['redcap_field_name']] = $i;
                        }
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

        //Yes3::logDebugMessage($this->getProjectId(), print_r($dd_multiselect_index, true), "DD_MULTISELECT_INDEX");

        /**
         * Assemble the SELECT query and event params to be passed to the record writer
         */
        $sqlSelect = "SELECT d.* FROM redcap_data d WHERE d.`project_id`=? AND d.`record`=?";

        $sqlEvent = "";
        $sqlEventParams = [];
        $sqlOrderBy = "";

        if ( $export_event_list ){
            
            $sqlEvent = "AND d.`event_id` IN(";

            for($e=0; $e<count($export_event_list); $e++){

                $sqlEvent .= ( $e===0 ) ? "?":",?";

                $sqlEventParams[] = $export_event_list[$e];
            }

            $sqlEvent .= ")";
        }

        /**
         * WITHIN-RECORD SORT ORDER
         * horizontal layout: breaks instance
         * vertical layout: breaks on event, instance
         * repeating layout (DEPRECATED): breaks on event, instance
         */

        if ( $ddPackage['export_layout']==="r" ){

            $sqlOrderBy = " ORDER BY d.`event_id`, d.`instance`";
        }
        else if ( $ddPackage['export_layout']==="v" ){

            $sqlOrderBy = "ORDER BY d.`event_id`, d.`instance`";
        }
        else {

            $sqlOrderBy = " ORDER BY d.`instance`, d.`event_id`";
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

        if ( $ddPackage['export_selection']=='2' ) {

            if ( !strlen($ddPackage['export_criterion_field']) ||
                !strlen($ddPackage['export_criterion_event']) ||
                !strlen($ddPackage['export_criterion_value']) ){

                throw new Exception("Cannot proceed with the export or download, because the selection field, event and/or value is missing.");
            }
            /*
            if ( $export_layout==="h"){

                $critXFieldMetadata = $dd[$dd_index[$ddPackage['export_criterion_field'][$ddPackage['export_criterion_event']]]];
            }
            else {

                $critXFieldMetadata = $dd[$dd_index[$ddPackage['export_criterion_field']]];
            }
            */
            $critXOperators = [ ">=", "<=", "<>", "=", "<", ">"];

            $sqlCritXParams = [];

            $critXStr = trim( $ddPackage['export_criterion_value'] );

            $critXOp = "="; // default operator for the SELECT query
            $critXVal = $critXStr; // the value applied to the operator

            $critXQ = ""; // the query expression to be determined

            /**
             * lists of comma-separated values are allowed
             */
            if ( strpos($critXVal, ',') !== false ){

                $valParts = explode(",", $critXVal);

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

                if ( $critXOp==="<>" && ( $critXVal==="" || $critXVal==="''" || $critXVal==='""' ) ){

                    $critXQ = "<>''";
                }
                elseif ( strlen($critXVal)>0 && is_numeric($critXVal) ){

                    $critXQ = $critXOp . intval($critXVal);
                }
                else {

                    $critXQ = $critXOp . "?";
                    $sqlCritXParams[] = $critXVal;
                }
            }

            if ( $export_group_id ){

                $sql = "
                SELECT DISTINCT d.`record`
                FROM redcap_data d
                WHERE d.`project_id`=? AND d.`event_id`=? AND d.`field_name`=? AND d.`value` IS NOT NULL AND d.`value` {$critXQ}
                AND d.`record` IN(SELECT DISTINCT dg.`record` FROM redcap_data dg WHERE dg.`project_id`=? AND dg.field_name='__GROUPID__' AND dg.`value`=?)
                ";
                $sqlParams = array_merge([ $this->project_id, $ddPackage['export_criterion_event'], $ddPackage['export_criterion_field'] ], $sqlCritXParams, [$this->project_id, $export_group_id]);
            }
            else {

                $sql = "
                SELECT DISTINCT d.`record`
                FROM redcap_data d
                WHERE d.`project_id`=? AND d.`event_id`=? AND d.`field_name`=? AND d.`value` IS NOT NULL AND d.`value` {$critXQ}";

                $sqlParams = array_merge([$this->project_id, $ddPackage['export_criterion_event'], $ddPackage['export_criterion_field'] ], $sqlCritXParams);
            }
        }
        else if ( $ddPackage['export_selection']=='1' ) {

            if ( $export_group_id ){

                $sql = "SELECT DISTINCT dg.`record` FROM redcap_data dg WHERE dg.`project_id`=? AND dg.field_name='__GROUPID__' AND dg.`value`=?";

                $sqlParams = [ $this->project_id, $export_group_id ];
            }
            else {

                $sql = "SELECT DISTINCT d.`record` FROM redcap_data d WHERE d.`project_id`=?";

                $sqlParams = [ $this->project_id ];                
            }
        }
        else {

            throw new Exception("Cannot proceed with the export or download, because the record selection option is not specified.");
        }

        //$sql .= " LIMIT 10";

        //Yes3::logDebugMessage($this->getProjectId(), $sql, "writeExportFiles: CritX SQL");
        //Yes3::logDebugMessage($this->getProjectId(), implode(",", $sqlParams), "writeExportFiles: CritX Params");

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

        /**
         * For v and r layouts, we need to account for the events for this field
         * to include
         */
        $field_events = [];

        $multiselect_fields = [];


        foreach ( $dd as $d ){

            if ( $this->ddIsMultiselect($d) ){

                $multiselect_fields[] = $d['redcap_field_name'];
            }

            if ( $export_layout !== "h" ){

                if ( $d['redcap_field_name'] && $d['redcap_events']){

                    $field_events[$d['redcap_field_name']] = json_decode($d['redcap_events'], true);
                }
            }
        }

        //Yes3::logDebugMessage($this->project_id, print_r($field_events, true), 'writeX:field_events');
        //Yes3::logDebugMessage($this->project_id, $sqlSelect, 'writeX:sqlSelect');
        //Yes3::logDebugMessage($this->project_id, print_r($sqlEventParams, true), 'writeX:sqlEventParams');

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
                $dd_multiselect_index,
                $field_events,
                $multiselect_fields,
                $dagNameForGroupId, 
                $h, 
                $export_layout, 
                $export_max_text_length, 
                $export_inoffensive_text,
                $export_hash_recordid,
                $export_shift_dates,
                $export_group_id,
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

        $export_data_dictionary_response = $this->writeExportDataDictionaryFile( $export_name, $export_target_folder, $dd, $destination, $export_layout );

        $export_info_file_response = $this->writeExportInfoFile($export_name, $export_target_folder, $export_uuid, $export_layout, $bytesWritten, $R, $C, $path, $destination);

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
            $C,
            $export_specification
        );

        /**
         * the results of a download are communicated to the browser by way of a cookie
         */
        if ( $destination==="download") {

            $this->setExportCookie( $export_uuid, $export_name, $R );
        }

        return [
            'export_data_message' => "Success: {$bytesWritten} bytes, {$R} rows and {$C} columns written to {$path}.",
            'export_data_filename' => $path,
            'export_data_file_size' => $bytesWritten,
            'export_data_items' => $K,
            'export_data_rows' => $R,
            'export_data_columns' => $C,
            'export_data_dictionary_message' => $export_data_dictionary_response['export_data_dictionary_message'],
            'export_data_dictionary_filename' => $export_data_dictionary_response['export_data_dictionary_filename'],
            'export_data_dictionary_file_size' => $export_data_dictionary_response['export_data_dictionary_file_size'],
            'export_info_message' => $export_info_file_response['export_info_message'],
            'export_info_filename' => $export_info_file_response['export_info_filename'],
            'export_info_file_size' => $export_info_file_response['export_info_file_size'],
            'export_info' => $export_info_file_response['export_info']
            
        ];
    }

    private function setExportCookie( $export_uuid, $export_name, $R )
    {
        $cookie_name = $export_uuid;
        $expires = time() + 30 * 60 * 60; // expires in 30 minutes
        $value = $R;
        setcookie($cookie_name, $value, $expires);
    }

    private function logExport(
        $message, 
        $destination, 
        $export_uuid, 
        $export_name, 
        $filename_data, 
        $filename_data_dictionary, 
        $filename_zip, 
        $bytes, 
        $items, 
        $rows, 
        $columns,
        $export_specification = []
        )
    {
        /**
         * Pull out just the basics from the specification items. This structure is a mess...
         */
        //Yes3::logDebugMessage($this->project_id, "{$export_uuid}:{$filename_zip}", "logExport");


        if ( $export_specification ){

            // uspec is deprecated, will be replaced by crosswalk
            if ( isset($export_specification['export_uspec_json']) ){

                unset( $export_specification['export_uspec_json'] );
            }

            $xitems = [];

            if ( isset($export_specification['export_items_json']) ){

                if ( Yes3::is_json_decodable($export_specification['export_items_json']) ){

                    $export_spec_items = json_decode( $export_specification['export_items_json'], true );

                    foreach ( $export_spec_items as $export_spec_item ){

                        // this is the structure we are working toward
                        $xitems[] = [
                            'redcap_object_type' => $export_spec_item['redcap_object_type'],
                            'redcap_object_name' => ( $export_spec_item['redcap_object_type'] === "form" ) ? $export_spec_item['redcap_form_name'] : $export_spec_item['redcap_field_name'],
                            'redcap_object_event_id' => $export_spec_item['redcap_event_id'],
                            'redcap_object_event_name' => $this->getEventNameForEventId($export_spec_item['redcap_event_id'])
                        ];
                    }
                }

                $export_specification['export_items'] = $xitems;

                unset($export_specification['export_items_json']);
            }
        }

        $uRights = $this->yes3UserRights();

        $params = [
            'user_name' => $uRights['username'],
            'user_designer' => $uRights['isDesigner'],
            'user_dag' => $uRights['dag'],
            'log_entry_type' => EMLOG_TYPE_EXPORT_LOG_ENTRY,
            'destination' => $destination,
            'export_uuid' => $export_uuid,
            'export_name' => $export_name,
            'filename_data' => $filename_data,
            'filename_data_dictionary' => $filename_data_dictionary,
            'filename_zip' => $filename_zip,
            'exported_bytes' => $bytes,
            'exported_items' => $items,
            'exported_rows' => $rows,
            'exported_columns' => $columns,
            'export_specification' => json_encode( $export_specification )
        ];

        $log_id = $this->log(
            $message,
            $params
        );

        return $log_id;
    }

    public function getExportLogs($export_uuid, $descending = false, $sinceWhen = 0)
    {
        $pSql = "
SELECT project_id, log_id, timestamp, username, message
    , log_entry_type, destination, export_uuid, export_name
    , filename_data, filename_data_dictionary, filename_zip 
    , exported_bytes, exported_items, exported_rows, exported_columns, export_specification
WHERE project_id=? AND log_entry_type=?
        ";

        $params = [ 
            $this->getProjectId(), 
            EMLOG_TYPE_EXPORT_LOG_ENTRY 
        ];

        if ( $export_uuid ){

            $pSql .= " AND export_uuid=?";

            $params[] = $export_uuid;
        }

        if ( $sinceWhen ){

            $pSql .= " AND TIMEDIFF(timestamp, ?) >= 0";

            $params[] = $sinceWhen;
        }

        if ( $descending ){

            $pSql .= " ORDER BY log_id DESC";
        }
        else {

            $pSql .= " ORDER BY log_id ASC";
        }

        $logRecords = [];

        $result = $this->queryLogs($pSql, $params);

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

    private function ddIsMultiselect( $d ){

        return ( strlen($d['redcap_source_option']) ) ? true : false;
    }

    public function getEmLogParameter( $log_id, $key_name ){

        return Yes3::fetchValue(
            "SELECT `value` FROM redcap_external_modules_log_parameters WHERE log_id=? AND `name`=?",
            [ 
                $log_id, 
                $key_name 
            ]
        );
    }

    public function setEmLogParameter( $log_id, $key_name, $key_value ){

        /**
         * 0 if log param record doesn't exist
         */
        $status = Yes3::fetchValue(
            "SELECT COUNT(*) FROM redcap_external_modules_log_parameters WHERE log_id=? AND `name`=?",
            [ 
                $log_id, 
                $key_name 
            ]
        );

        if ( $status==0 ){

            $sql = "INSERT INTO redcap_external_modules_log_parameters (`value`, `log_id`, `name`) VALUES (?,?,?)";
        }
        else {

            $sql = "UPDATE redcap_external_modules_log_parameters SET `value`=? WHERE log_id=? AND `name`=?";
        }

        return $this->query( $sql, [$key_value, $log_id, $key_name] );
    }
    
    private function writeExportDataFileRecord( 
        $record,
        $sqlSelect, 
        $sqlSelectParams, 
        $eventName, 
        &$dd, 
        $dd_index, 
        $dd_specmap_index, 
        $dd_multiselect_index,
        $field_events,
        $multiselect_fields,
        $dagNameForGroupId, 
        $h, 
        $export_layout, 
        $export_max_text_length, 
        $export_inoffensive_text,
        $export_hash_recordid,
        $export_shift_dates,
        $export_group_id,
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

        $RecordIdField = $this->getRecordIdField();

        $bytesWritten = 0;

        $exportValues = 0;

        //Yes3::logDebugMessage($this->project_id, print_r($multiselect_fields, true), "writeExportDataFileRecord: multiselects");


        //Yes3::logDebugMessage($this->project_id, $sqlSelect, "writeExportDataFileRecord: sqlSelect");
        //Yes3::logDebugMessage($this->project_id, print_r($sqlSelectParams, true), "writeExportDataFileRecord: sqlSelectParams");
        //Yes3::logDebugMessage($this->getProjectId(), print_r($field_events, true), "writeExportDataFileRecord: FIELD_EVENTS");
            

        foreach ( Yes3::recordGenerator($sqlSelect, $sqlSelectParams) as $x ){

        //$xx = Yes3::fetchRecords($sqlSelect, $sqlSelectParams);
        //foreach ( $xx as $x ){

           //Yes3::logDebugMessage($this->getProjectId(), print_r($x, true), "writeExportDataFileRecord: X");

           //continue;

            //$K++;

            $x_instance = strval($x['instance']); if ( !$x_instance ) $x_instance="1";

            /**
             * $BOR: beginning of record
             * 
             * No break for horiz layouts,
             *   (event_id) for vertical,
             *   (event_id, instance) for repeating
            */
            /*
            if ( $export_layout==="v" ) {

                $BOR = ( $x['event_id'] !== $event_id );
            }
            elseif ( $export_layout==="r" ) {

                $BOR = ( $x['event_id'] !== $event_id || $x_instance !== $instance );
            }
            */

            if ( $export_layout==="h" ) {

                $BOR = ( $x_instance !== $instance );
            }
            else {

                $BOR = ( $x['event_id'] !== $event_id || $x_instance !== $instance );
            }

            //Yes3::logDebugMessage($this->getProjectId(), $BOR, "writeExportDataFileRecord: BOR");

            $event_id = $x['event_id'];
            $instance = $x_instance;

            //Yes3::logDebugMessage($this->getProjectId(),$BOR, "writeExportDataFileRecord: BOR");

            if ( $BOR ) {

                if ( $y && $exportValues ){

                    $bytesWritten += $this->writeExportRecord($h, $y, $R, $C);
                }

                /**
                 * fill out the record
                 */
                $y = [];

                foreach ($dd as $d){

                    if ( !isset($y[$d['var_name']]) ){

                        $y[$d['var_name']] =  ( $this->ddIsMultiselect($d) ) ? "0":"";
                    }

                    /**
                     * constant specmap field?
                     */
                    if ( substr($d['redcap_field_name'], 0, 9)==="constant:" ) {

                        $y[$d['var_name']] = str_replace("'", "", trim(substr($d['redcap_field_name'], 9)));
                    }
                }

                $y[$RecordIdField] = $record;

                if ( $export_layout!=="h" && REDCap::isLongitudinal() ) {
    
                    $y[VARNAME_EVENT_ID  ] = $x['event_id'];
                    $y[VARNAME_EVENT_NAME] = $eventName[$x['event_id']];
                }
                /*
                if ( $export_layout==="r" ) {
    
                    $y[VARNAME_INSTANCE  ] = $x_instance;
                }
                */
                $y[VARNAME_INSTANCE] = $x_instance;

                if ( isset($y[VARNAME_GROUP_ID]) ) {

                    $y[VARNAME_GROUP_ID  ] = Yes3::getGroupIdForRecord($record);
                    $y[VARNAME_GROUP_NAME] = $dagNameForGroupId[ $y[VARNAME_GROUP_ID  ] ];
                }

                $exportValues = 0;

                $BOR = false;

                //Yes3::logDebugMessage($this->getProjectId(), print_r($y, true), "writeExportDataFileRecord: Y INITIALIZED");
            }

            //continue;

            /**
             * add the value to the record
             */

            $event_id = $x['event_id'];

            $instance = $x_instance;

            $field_name = $x['field_name'];

            $REDCapValue = $this->conditionREDCapValue( $x['value'], $export_max_text_length, $export_inoffensive_text );

            $is_multiselect = in_array($field_name, $multiselect_fields);

            /*
            if ( $field_name === "__GROUPID__" && isset($y[VARNAME_GROUP_ID])) {

                $y[VARNAME_GROUP_ID  ]   = $x['value'];
                $y[VARNAME_GROUP_NAME] = $dagNameForGroupId[ $x['value'] ];
            }
            */

            if ( $export_layout==="h" ){

                if ( $is_multiselect ){

                    $field_index = $dd_multiselect_index[$field_name][$event_id][$REDCapValue] ?? -1;
                } else {

                    $field_index = $dd_index[$field_name][$event_id] ?? -1;
                }

                $specmap_field_index = $dd_specmap_index[$field_name][$event_id] ?? -1;
            }
            else {

                if ( $is_multiselect ){

                    $field_index = $dd_multiselect_index[$field_name][$REDCapValue] ?? -1;
                } else {

                    $field_index = $dd_index[$field_name] ?? -1;
                }

                $specmap_field_index = $dd_specmap_index[$field_name] ?? -1;
            }

            $acceptable = ( $field_index > -1 && $field_name !== $RecordIdField );

            //Yes3::logDebugMessage($this->getProjectId(), $acceptable, "writeExportDataFileRecord: ACCEPTABLE(1)");

            //continue;

            if ( $acceptable && $export_layout!=="h" && isset($field_events[$field_name]) && $field_events[$field_name]){

                //Yes3::logDebugMessage($this->project_id, $field_name . ", event_id=" . $event_id, 'WriteXRecord: field_name');
                //Yes3::logDebugMessage($this->project_id, print_r( $field_events[$field_name], true ), 'WriteXRecord: field_events');

                $acceptable = in_array((int)$event_id, $field_events[$field_name]);

            }

            //Yes3::logDebugMessage($this->getProjectId(), $acceptable, "writeExportDataFileRecord: ACCEPTABLE(2)");

            if ( $acceptable ){

                $exportValues++;

                /**
                 * goddam multiselects
                 */
                if ( $dd[$field_index]['var_type'] === "CHECKBOX" ){

                    if ( strlen($y[ $dd[ $field_index]['var_name'] ]) ) {

                        $y[ $dd[ $field_index]['var_name'] ] .= ",";
                    }

                    $y[ $dd[ $field_index]['var_name'] ] .= Yes3::normalized_string( $REDCapValue );
                }
                elseif ( $is_multiselect ) {

                    $y[ $dd[ $field_index]['var_name'] ] = "1";
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

                //Yes3::logDebugMessage($this->getProjectId(), print_r($y, true), "writeExportDataFileRecord: ACCEPTABLE FOR Y");

                $this->doValidationCalculations($dd[$field_index], $REDCapValue, $is_multiselect);
            }

            //continue;

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

        } // foreach record...

        /**
         * write the last (or only) export for this record
         */
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

    private function doValidationCalculations( &$d, $value, $is_multiselect=false )
    {
        if ( $is_multiselect ){

            $value = "1";
        }
        
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

    public function exportBatch(){

        $selected = $this->getExportBatchList();

        if ( !$selected ){

            return("No exports selected for batch processing");
        }

        $responses = "";

        foreach( $selected as $export_uuid) {

            $responses .= "<p>" . $this->exportData($export_uuid) . "</p>";
        }

        return $responses;
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
        . "\n\n" 
        . $results['export_info_message']
        ;

        $t = time() - $t;

        $response .= "\n\nElapsed time: {$t} seconds.";
        /*
        if ( $results['export_info']['notification_email'] && $this->getProjectSetting('enable-email-notifications')==="Y") {

            $this->emailExportNotice( $results['export_info'] );
        }
        */

        return nl2br($response);
    }

    private function emailExportNotice( $info ){

        $msg = '<html><body style="font-family:arial,helvetica;">';

        $msg .= '<p>You are receiving this email because you have enabled notifications from the REDCap YES3 Exporter.</p>';

        $msg .= '<p style="text-decoration:underline;">Summary of export</p>';

        $msg .= '<table><tbody>';

        $msg .= "<tr><td>REDCap host</td><td>"              . APP_PATH_WEBROOT_FULL         . "</td></tr>";
        $msg .= "<tr><td>Date and time</td><td>"            . $info['timestamp']            . "</td></tr>";
        $msg .= "<tr><td>Username</td><td>"                 . $info['username']             . "</td></tr>";
        $msg .= "<tr><td>REDCap project id (pid)</td><td>"  . $info['project_id']           . "</td></tr>";
        $msg .= "<tr><td>REDCap project title</td><td>"     . $info['project_title']        . "</td></tr>";
        $msg .= "<tr><td>Export name</td><td>"              . $info['export_name']          . "</td></tr>";
        $msg .= "<tr><td>Export uuid</td><td>"              . $info['export_uuid']          . "</td></tr>";
        $msg .= "<tr><td>Target folder</td><td>"            . $info['export_target_folder'] . "</td></tr>";
        $msg .= "<tr><td>Path</td><td>"                     . $info['path']                 . "</td></tr>";
        $msg .= "<tr><td>File size (bytes)</td><td>"        . $info['bytes_written']        . "</td></tr>";
        $msg .= "<tr><td>Columns</td><td>"                  . $info['columns']              . "</td></tr>";
        $msg .= "<tr><td>Rows</td><td>"                     . $info['rows']                 . "</td></tr>";

        $msg .= '</tbody></table>';

        $msg .= "</body></html>";

        $result = \REDCap::email( 
            
            $info['notification_email'],
            $info['notification_email'],
            "Notice of YES3 Data export",
            $msg

        );
    }

    public function getExportDataDictionary($export_uuid, $countOnly=false)
    {
        $dd = $this->buildExportDataDictionary($export_uuid)['export_data_dictionary'] ?? [];

        //$this->tidyUpDD($dd, true);

        return ( $countOnly ) ? count($dd) : $dd;
    }

    /**
     * returns the column count for the export
     * @param mixed $specification 
     * @return int 
     */
    public function getExportColumnCount($specification)
    {
        $log_id = (int) $specification['log_id'];

        $export_uuid = $specification['export_uuid'];

        if ( !$log_id || !$export_uuid ){

            return 0;
        }

        $column_count = $this->getEmLogParameter($log_id, "column_count");

        if ( !$column_count ){

            $column_count = $this->getExportDataDictionary($export_uuid, true);
            $this->setEmLogParameter($log_id, "column_count", $column_count);
        }

        return $column_count;
    }

    public function downloadDataDictionary($export_uuid)
    {
        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $filename = $this->exportDataDictionaryFilename( $ddPackage['export_name'], "download" );

        $this->tidyUpDD($ddPackage['export_data_dictionary'], true);

        $delim = ",";

        $xx = (array) $this->dataDictionaryForExport($ddPackage['export_data_dictionary'], $ddPackage['export_layout'], true);

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
     
        $h = fopen('php://output', 'w');

        if ( $h===false ){

            throw new Exception("Fail: could not open PHP output stream.");
        }

        $path = "";

        ob_start();

        header("Content-type: text/csv");
        header("Cache-Control: no-store, no-cache");
        header('Content-Disposition: attachment; filename=' . basename($filename) );
     
        foreach ( $xx as $x ) {

            fputcsv($h, $x, $delim);
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

        $h = $this->fopen_r_safe( $xFileResponse['export_data_filename'] );

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
            $xFileResponse['export_data_items'],
            $xFileResponse['export_data_rows'],
            $xFileResponse['export_data_columns']
        );

        ob_start();

        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header("Cache-Control: no-store, no-cache");
        header('Content-Length: '.$size);
        header('Content-Disposition: attachment;filename=' . basename($filename) );

        while (!feof($h)){

            print(@fread($h, $chunksize));

            ob_flush();
            flush();
        }

        fclose($h);

        ob_end_flush();

        //exit;
    }

    private function yes3TmpFilename(){
        
        return tempnam(sys_get_temp_dir(), "ys3");
    }

    private function getExportBatchList(){

        $y = [];

        $specs = $this->getExportSpecificationList();

        foreach ( $specs as $spec ){

            if ( $spec['export_batch'] ){

                $y[] = $spec['export_uuid'];
            }
        }

        return $y;
    }

    public function downloadBatch(){

        $export_uuid = $this->getExportBatchList();

        if ( $export_uuid ){

            $this->downloadZip($export_uuid);
        }
    }

    public function downloadZip($export_uuid){

        if ( !is_array($export_uuid) ){

            $export_uuid = array( 
                $export_uuid
            );
        }

        $zipFilename = $this->yes3TmpFilename();

        $downloadFilename = $this->exportProjectZipFilename( "", "download" );

        $zip = new ZipArchive;

        if ( !$zip->open($zipFilename, ZipArchive::CREATE) ){

            // log it

            return false;
        }

        $zip->close();

        // log it

        $exports = 0;
        $errors = 0;

        for ($i=0; $i<count($export_uuid); $i++){

            $e = $this->zipExport($export_uuid[$i], $zipFilename, $downloadFilename);

            if ( !$e ){

                $errors++;

                // log it
            }
            else {

                $exports++;
            }
        }

        if ( $exports ){

            $this->downloadZipFinish($zipFilename, $downloadFilename);
        }
    }

    public function zipExport($export_uuid, $zipFilename, $downloadFilename)
    {
        //Yes3::logDebugMessage($this->project_id, "{$export_uuid}:{$zipFilename}:{$downloadFilename}", "zipExport");
        
        $ddPackage = $this->buildExportDataDictionary($export_uuid);

        $bytesWritten = 0;

        $xFileResponse = $this->writeExportFiles($ddPackage, "download", $bytesWritten);

        //Yes3::logDebugMessage($this->project_id, print_r($xFileResponse, true), "zipExport");

        if ( !isset( $xFileResponse['export_data_filename']) || !isset( $xFileResponse['export_data_dictionary_filename']) ) {

            throw new Exception("Fail: download export file(s) not written");
        }

        $timestamp = Yes3::timeStampString();

        $zip = new ZipArchive;

        $z = $zip->open($zipFilename);

        if ( !$z ){

            // log it

            return 0;
        }

        $zip->addFile($xFileResponse['export_data_dictionary_filename'], $this->exportDataDictionaryFilename($ddPackage['export_name'], "download", $timestamp));
    
        $zip->addFile($xFileResponse['export_data_filename'], $this->exportDataFilename($ddPackage['export_name'], "download", $timestamp));

        $zip->addFile($xFileResponse['export_info_filename'], $this->exportInfoFilename($ddPackage['export_name'], "download", $timestamp));

        $zip->close();

        $this->logExport(
            "export zip generated",
            "download",
            $export_uuid,
            $ddPackage['export_name'],
            $xFileResponse['export_data_filename'],
            $xFileResponse['export_data_dictionary_filename'],
            $downloadFilename,
            $bytesWritten,
            $xFileResponse['export_data_items'],
            $xFileResponse['export_data_rows'],
            $xFileResponse['export_data_columns']
        );

        return 1;
    }

    private function downloadZipFinish( $zipFilename, $downloadFilename ){

        $chunksize = 1024 * 1024; // 1MB per one chunk of file.

        $size = intval(sprintf("%u", filesize($zipFilename)));

        $h = $this->fopen_r_safe($zipFilename);

        ob_start();

        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header("Cache-Control: no-store, no-cache");
        header('Content-Length: '.$size);
        header('Content-Disposition: attachment;filename=' . basename($downloadFilename) );

        while (!feof( $h )){

            print(@fread($h, $chunksize));

            ob_flush();
            flush();
        }

        fclose( $h );

        unlink( $zipFilename );

        ob_end_flush();
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
        return $this->exportFilename($export_name, "data", "csv", $target);
    }

    public function exportDataDictionaryFilename( $export_name, $target="download")
    {
        return $this->exportFilename($export_name, "dd", "csv", $target);
    }

    public function exportZipFilename( $export_name, $target="download")
    {
        return $this->exportFilename($export_name, "package", "zip", $target);
    }

    public function exportProjectZipFilename( $export_name="", $target="download")
    {
        if ( !$export_name ){

            $export_name = $this->getProject()->getTitle();
        }
        return $this->exportFilename($export_name, "package", "zip", $target);
    }

    public function exportLogFilename( $export_name, $target="download")
    {
        return $this->exportFilename($export_name, "log", "csv", $target);
    }

    public function exportInfoFilename( $export_name, $target="download")
    {
        return $this->exportFilename($export_name, "info", "json", $target);
    }

    public function exportFilename( $export_name, $type, $extension, $target="download", $timestamp="")
    {
        if ( $target==="download") {

            if ( !$timestamp ){

                $timestamp = Yes3::timeStampString();
            }

            return Yes3::normalized_string($export_name, 80) . "_". $type . "_" . $timestamp . "." . $extension;
        }

        return Yes3::normalized_string($export_name, 80) . "_". $type . "." . $extension;
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

        // as of v1.1.0 this will be changed to use the 'log_entry_type parameter' instead of 'setting'
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
     * Confirms that the user has permission to access an export specification
     * 
     * Relies on getFormMetadataStructures() and getFieldMetadataStructures()
     * which return form and field metadata as allowed by user export permissions
     * 
     * function: confirmSpecificationPermissions
     * 
     * @param mixed $specification
     * 
     * @return bool
     * @throws Exception
     */
    public function confirmSpecificationPermissions( $specification )
    {
        $uRights = $this->yes3UserRights();

        // designers and superusers always have permission
        if ( $uRights['isDesigner'] || $uRights['isSuper'] ){

            return true;
        }

        /**
         * The structures returned by getFormMetadataStructures() will include
         * only the forms for which the user has export permission.
         * 
         * We will use form_index, which is keyed by form_name
         */
        $allowed_forms = array_keys( $this->getFormMetadataStructures()['form_index'] );

        if ( !$allowed_forms ) {

            //Yes3::logDebugMessage($this->project_id, "no form access", "confirmSpecificationPermissions: denied");

            return false; // user has no form access
        }

        /**
         * similarly, the list of allowed fields is field_index returned by getFieldMetadataStructures()
         */
        $allowed_fields = array_keys( $this->getFieldMetadataStructures()['field_index'] );

        //Yes3::logDebugMessage($this->project_id, print_r($allowed_fields, true), "confirmSpecificationPermissions: allowed_fields");
 
        /**
         * the forms and fields to be exported are recorded in spec.export_items
         */
        $export_items = json_decode( $specification['export_items_json'], true );

        foreach($export_items as $export_item){

            if ( isset($export_item['redcap_form_name']) && $export_item['redcap_form_name'] ) {

                // form is not exportable by user
                if ( $export_item['redcap_form_name'] !== ALL_OF_THEM && !in_array($export_item['redcap_form_name'], $allowed_forms) ){

                    //Yes3::logDebugMessage($this->project_id, $export_item['redcap_form_name'], "confirmSpecificationPermissions: disallowed item form");

                    return false;
                }

                // now we have to check the fields

                $fields = $this->getFormDataEntryFieldMetadata( $export_item['redcap_form_name'] );

                foreach($fields as $field){

                    if ( !$this->fieldExcludedByExportOptions( $specification, $field ) && !in_array($field['field_name'], $allowed_fields) ){

                        //Yes3::logDebugMessage($this->project_id, $field['field_name'], "confirmSpecificationPermissions: disallowed form field");

                        return false;
                    }
                }
            }

            if ( isset($export_item['redcap_field_name']) && $export_item['redcap_field_name'] && !$this->isConstantExpression($export_item['redcap_field_name']) ){

                $field_name = $export_item['redcap_field_name'];

                $field = $this->getFieldMetadata($field_name);

                if ( !$this->fieldExcludedByExportOptions( $specification, $field ) && !in_array($field_name, $allowed_fields) ){

                    //Yes3::logDebugMessage($this->project_id, $export_item['redcap_field_name'], "confirmSpecificationPermissions: disallowed item field");
                    
                    return false;
                }
            }
        }

        return true;
    }

    private function isConstantExpression($s){

        return ( stripos( $s, "constant:") !== FALSE ) ? true : false;
    }

    /**
     * Determines if a field will be excluded based on export specification options.
     * NOTE: This logic is largely repeated in addExportItem_REDCapField(). It would be good to harmonize.
     * 
     * function: fieldExcludedByExportOptions
     * 
     * @param mixed $specification - export specification. Must have properties:
     *              export_remove_phi
     *              export_remove_dates
     *              export_remove_largetext
     *              export_remove_freetext
     * 
     * @param mixed $field - REDCap field metadata. Must have properties:
     *              element_type
     *              element_validation_type
     *              field_phi
     * 
     * @return bool
     */
    private function fieldExcludedByExportOptions( $specification, $field )
    {
        if ( $field['field_name'] === $this->getRecordIdField() ){

            return false;
        }
        elseif ( $specification['export_remove_phi'] && $field['field_phi'] ){

            return true;
        }
        elseif ( $specification['export_remove_dates'] && $this->isDateOrTimeType($this->REDCapFieldTypeToVarType($field['element_type'], $field['element_validation_type'])) ){

            return true;
        }
        elseif ( $specification['export_remove_largetext'] && $field['element_type']==="textarea" ){

            return true;
        }
        elseif ( $specification['export_remove_freetext'] && $field['element_type']==="text" && !$field['element_validation_type'] ){

            return true;
        }

        return false;
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
        , export_label
        , export_order
        , export_username
        , export_layout
        , export_multiselect
        , export_selection
        , export_criterion_field
        , export_criterion_event
        , export_criterion_value
        , export_target
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
        , export_batch";

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
            $params = [$this->getProjectId(), EMLOG_MSG_EXPORT_SPECIFICATION, $export_uuid];
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

            $spec = $this->queryLogs($pSql." LIMIT 1", $params)->fetch_assoc();

            if ( $spec['export_selection']=="1" ){

                $spec['export_criterion_field'] = "";
                $spec['export_criterion_event'] = "";
                $spec['export_criterion_value'] = "";
            }

            if ( !is_array($spec) ) return [];

            return $spec;
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

            $fields = "log_id, user, removed, setting, export_uuid, timestamp, export_specification_json";

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

        $eventNum = 0;

        foreach( $events as $event_id => $event_name ){

            $strEventId = (string) $event_id;

            $eventNum++;

            if ( $uniquePrefixLen ){

                $prefix = $prefixes[$event_id];
            }
            else {

                //$prefix = "e" . $strEventId;
                $prefix = "e" . strval($eventNum);
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
        $user_rights = $this->yes3UserRights();
        $form_export_permissions = $user_rights['form_export_permissions'];
        $designer = $user_rights['isDesigner'];

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

        //Yes3::logDebugMessage($this->project_id, print_r($form_export_permissions, true), "form_export_permissions");
        //Yes3::logDebugMessage($this->project_id, print_r($mm, true), "form metadata");

        $form_metadata = [];

        $form_index_num = 0;

        $form_index = [];

        foreach ($mm as $m){

            if ( !$designer && !$form_export_permissions[$m['form_name']] ){

                continue;
            }

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

            //Yes3::logDebugMessage($this->getProjectId(), print_r($form_metadata, true), "getFormMetadataStructures: form_metadata");

            $form_index[$form_name] = $form_index_num;

            $form_index_num++;
        }
        
        return [
            'form_index'=>$form_index, 
            'form_metadata'=>$form_metadata
        ];
    }

    function getEventNameForEventId( $event_id )
    {
        if ( !REDCap::isLongitudinal() ) return "n/a";

        if ( !$event_id ) return "";

        if ( $event_id === ALL_OF_THEM ) return "all events";

        $event_id = intval($event_id); if ( !$event_id ) return "";

        return REDCap::getEventNames(true, true, $event_id);
    }

    private function getFormDataEntryFieldMetadata($form_name)
    {
        $sql = "
        SELECT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum, m.element_validation_type, m.field_phi
        FROM redcap_metadata m
        WHERE m.project_id=?
        AND m.element_type NOT IN('descriptive')";

        $params = [$this->project_id];

        if ( $form_name === ALL_OF_THEM ){

            if ( \REDCap::isLongitudinal() ) {

                $sql .= " AND m.form_name IN("
                ."SELECT DISTINCT ef.form_name"
                ." FROM redcap_events_forms ef"
                ." INNER JOIN redcap_events_metadata em ON em.event_id=ef.event_id"
                ." INNER JOIN redcap_events_arms ea ON ea.arm_id=em.arm_id"
                ." WHERE ea.project_id=?"
                .")";
                $params[] = $this->project_id;
            }
        }
        else {

            $sql .= " AND m.form_name=?";
            $params[] = $form_name;
        }

        $sql .= " ORDER BY m.field_order";

        //Yes3::logDebugMessage($this->project_id, $sql, "getFormDataEntryFieldMetadata:" . $form_name);

        return Yes3::fetchRecords($sql, $params);
    }

    private function getFieldMetadata($field_name)
    {
        $sql = "
        SELECT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum, m.element_validation_type, m.field_phi
        FROM redcap_metadata m
        WHERE m.project_id=? AND m.field_name=?
        ";

        return Yes3::fetchRecords($sql, [$this->project_id, $field_name]);
    }

    public function getFieldMetadataStructures(): array
    {
        $form_export_permissions = $this->yes3UserRights()['form_export_permissions'];
        
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

            $form_export_permission = (int)$form_export_permissions[$field['form_name']];

            if ( !$form_export_permission ) {

                continue;
            }

            // phi only allowed for full access
            if ( $form_export_permission !== 1 && $field['field_phi'] === "1" ){

                continue;
            }

            $field_type = $field['element_type'];
            $field_validation = $field['element_validation_type'];

            // large text, small text, dates not allowed for de-identified access
            // note: for now we are 
            if ( $form_export_permission === 2 ){

                if ( $field_type === "textarea"
                    || ($field_type === "text" && !$field_validation)
                    || $this->isDateOrTimeType($this->REDCapFieldTypeToVarType( $field_type, $field_validation )) 
                ) {

                    continue;
                }
            }

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

            elseif ( $field['element_type']==="yesno" ){

                $valueset = [
                    ['value' =>" 0", "label" => "No"],
                    ['value' =>" 1", "label" => "Yes"]
                ];
            }

            elseif ( $field['element_type']==="truefalse" ){

                $valueset = [
                    ['value' =>" 0", "label" => "False"],
                    ['value' =>" 1", "label" => "True"]
                ];
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

            /**
             * (1) Fields from repeating instruments are not selectable, 
             *     since only forms are allowed on repeating layouts.
             * (2) The record ID field is not selectable. Its inclusion is determined at export time.
             */

            /**
             * v1.1.0: all layouts now take fields from repeating instruments
             */
            //if ( !Yes3::isRepeatingInstrument($field['form_name']) && $field['field_name'] !== $this->getRecordIdField() ) {
            if ( $field['field_name'] !== $this->getRecordIdField() ) {
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

    /**
     * Adds a REDCap field item to an export. 
     * The parameters are set in buildExportDataDictionary(), 
     * and passed either directly or through addExportItem_REDCapForm().
     * 
     * NOTE: see harmonization comment for fieldExcludedByExportOptions().
     * 
     * function: addExportItem_REDCapField
     * 
     * @param mixed $export - the export object
     * @param mixed $redcap_field_name - from export specification
     * @param mixed $redcap_event_id - from export specification
     * @param mixed $fields - the fields metadata array returned by getFieldMetadataStructures()
     * @param mixed $forms - the forms metadata array returned by getFormMetadataStructures()
     * @param mixed $event_settings - the array returned by getEventSettings()
     * @param mixed $allowed - array of allowed DAGs, forms, field types etc. Set in buildExportDataDictionary()
     * @param mixed $form_export_permissions - array of form permissions for the user. 
     *              Keyed by form_name. Values: 1=Full dataset, 2=Deidentified, 3=No PHI, 0=No access
     * 
     * @return int
     * @throws Exception
     */
    private function addExportItem_REDCapField( $export, $redcap_field_name, $redcap_event_id, $fields, $forms, $event_settings, $allowed, $form_export_permissions )
    {
        
        $VAR_TYPE_MULTISELECT = "INTEGER";
        
        $field_index = $fields['field_index'][$redcap_field_name];

        $form_name = $fields['field_metadata'][$field_index]['form_name'];

        if ( !in_array($form_name, $allowed['forms']) ){

            return 0;
        }

        $form_export_permission = $form_export_permissions[$form_name];

        $field_type = $fields['field_metadata'][$field_index]['field_type']; // aka element_type

        $field_validation = $fields['field_metadata'][$field_index]['field_validation']; // aka element_validation_type

        $field_phi = ( $fields['field_metadata'][$field_index]['field_phi'] == "1" );

        $field_largetext = ( $fields['field_metadata'][$field_index]['field_type']==="textarea" );

        $field_smalltext = false;

        $field_date = false;

        if ( $field_type === "text" ){

            if ( !$field_validation ){

                $field_smalltext = true;
            }
            elseif ( $this->isDateOrTimeType($this->REDCapFieldTypeToVarType( $field_type, $field_validation )) ) {

                $field_date = true;
            }
        }

        /*
        $msg = "redcap_field_name={$redcap_field_name}: form_name={$form_name}, field_type={$field_type}, field_validation={$field_validation}, field_phi={$field_phi}"
        .", field_largetext={$field_largetext}"
        .", field_smalltext={$field_smalltext}"
        .", field_date={$field_date}."
        ."\nallowed: phi={$allowed['phi']} largetext={$allowed['largetext']} smalltext={$allowed['smalltext']} dates={$allowed['dates']}."
        ;
        Yes3::logDebugMessage($this->project_id, $msg, "addExportItem_REDCapField");
        */

        /**
         * data dictionary inclusion depends on the export options and the user's form export permissions
         * the record id field always gets a pass
         */

        if (  $redcap_field_name !== $this->getRecordIdField() ){

            if ( $field_phi && (!$allowed['phi'] || $form_export_permission != 1) ){

                return 0;
            }

            if ( $field_largetext && (!$allowed['largetext'] || $form_export_permission == 2 ) ){

                return 0;
            }

            if ( $field_smalltext && (!$allowed['smalltext'] || $form_export_permission == 2 ) ){

                return 0;
            }

            if ( $field_date && (!$allowed['dates'] || $form_export_permission == 2 ) ){

                return 0;
            }
        }

        $event_ids = [];

        //if ( $redcap_event_id === ALL_OF_THEM && $export->export_layout === "h" ){
        if ( $redcap_event_id === ALL_OF_THEM ){

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

            //print "\n" . $redcap_field_name . ", var_name=" . $var_name . "\nexport=" . print_r($export, true);

            if ( !$export->itemInExport($var_name) ){

                $var_label = $fields['field_metadata'][$field_index]['field_label'];
                $var_type = $this->REDCapFieldTypeToVarType($field_type, $field_validation);
                $valueset = $fields['field_metadata'][$field_index]['field_valueset'];
                $event_name = $this->getEventName($event_id, $event_settings);

                if ( $field_type === "checkbox" && $export->export_multiselect === "1" ) {

                    //Yes3::logDebugMessage( $this->getProjectId(), print_r($valueset, true),"addExportItem_REDCapField:CHECKBOX {$redcap_field_name}" );
                    
                    foreach ( $valueset as $option ){

                        $export->addExportItem([
                            'var_name' =>  Yes3::inoffensiveFieldName($var_name . "___" . $option['value']),
                            'var_label' =>  Yes3::inoffensiveText($var_label. ": " . $option['label']),
                            'var_type' => "INTEGER",
                            'valueset' => [],
                            'origin' => "redcap",
                            'redcap_field_name' => $redcap_field_name,
                            'redcap_events' => [ (int)$event_id ],
                            'redcap_form_name' => $form_name,
                            'multiselect' => "1",
                            'multiselect_option' => strval($option['value']),
                            VARNAME_EVENT_ID => $event_id,
                            VARNAME_EVENT_NAME => $event_name
                        ]);
                    }
                }
                else {
                    $export->addExportItem([
                        'var_name' => $var_name,
                        'var_label' => $var_label,
                        'var_type' => $var_type,
                        'valueset' => $valueset,
                        'origin' => "redcap",
                        'redcap_field_name' => $redcap_field_name,
                        'redcap_events' => [ (int)$event_id ],
                        'redcap_form_name' => $form_name,
                        VARNAME_EVENT_ID => $event_id,
                        VARNAME_EVENT_NAME => $event_name
                    ]);
                }
            }
            else {

                $export->updateExportItemEvents($var_name, $event_id);
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

    private function addExportItem_REDCapForm( $export, $redcap_form_name, $redcap_event_id, $fields, $forms, $event_settings, $allowed, $form_export_permissions )
    {

        $form_names = [];

        if ( $redcap_form_name === ALL_OF_THEM ){

            foreach ( $forms['form_metadata'] as $form ){

                /**
                 * v1.1.0: no selection distinction between repeating and nonrepeating forms
                 */
                //if ( in_array($form['form_name'], $allowed['forms']) && !$form['form_repeating']) {
                if ( in_array($form['form_name'], $allowed['forms']) ) {

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

                    $this->addExportItem_REDCapField($export, $field_name, $event_id, $fields, $forms, $event_settings, $allowed, $form_export_permissions);
                }
            }
        }

        return count($form_names);
    }

    private function exportFieldName( $export, $field_name, $event_id, $event_settings)
    {
        if ( $export->export_layout==="h" && $field_name !== $this->getRecordIdField() ) {
    
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
        if ( $field_validation === "date_dmy" ) return "DATE";
        if ( $field_validation === "datetime_mdy" ) return "DATETIME";
        if ( $field_validation === "datetime_ymd" ) return "DATETIME";
        if ( $field_validation === "datetime_dmy" ) return "DATETIME";
        if ( $field_validation === "datetime_seconds_mdy" ) return "DATETIME";
        if ( $field_validation === "datetime_seconds_ymd" ) return "DATETIME";
        if ( $field_validation === "datetime_seconds_dmy" ) return "DATETIME";
        if ( $field_validation === "time" ) return "TIME";
        if ( $field_validation === "float" ) return "FLOAT";
        if ( $field_validation === "int" ) return "INTEGER";

        return "TEXT";
    }

    /* ==== CRONS ==== */

    public function yes3_exporter_cron( $cronInfo=['cron_description'=>"noname"] )
    {
        //Yes3::logDebugMessage(0, "YES3 Exporter cron job started", "yes3_exporter_cron");

        //return;

        if ( !$this->okayToRunCron() ){

            return "";
        }

        $t0 = time();

        $projects = 0;

        $cronlog = "Starting the \"{$cronInfo['cron_description']}\" cron job at " . strftime("%F %T");

        $originalPid = $_GET['pid'];

        foreach($this->getProjectsWithModuleEnabled() as $localProjectId){

            $_GET['pid'] = $localProjectId;

            $projects++;

            $projCronLog = "Starting the \"{$cronInfo['cron_description']}\" cron job at " . strftime("%F %T") . " for project #{$localProjectId}";

            $this->project_id = $localProjectId;

            //Yes3::logDebugMessage($localProjectId, "project {$localProjectId} has YES3 Exporter module enabled", "yes3_exporter_cron");

            // DAILY EMAIL

            if ( $this->getProjectSetting("notification-email-enable")==="Y" ){

                $projCronLog .= "\n" . $this->cronJob( "emailDailyLog" );
            }

            // HOUSEKEEPING

            $projCronLog .= "\n" . $this->cronJob( "hk_generations" );

            // PROJECT CRON LOG

            $projCronLog .= "\nEnding the \"{$cronInfo['cron_description']}\" cron job at " . strftime("%F %T") . " for project #{$localProjectId}";

            $cronlog .= "\n" . $projCronLog;
    
            $this->setProjectSetting("project-cron-log", $projCronLog);
        }
    
        $_GET['pid'] = $originalPid;

        // SYSTEM CRON TIME AND LOG

        $cronlog .= "\nEnding the \"{$cronInfo['cron_description']}\" cron job at " . strftime("%F %T") . "\n";

        $this->setSystemSetting("cron-ran-at", strftime("%F %T"));
        $this->setSystemSetting("cron-log", $cronlog);

        // SYSTEM LOG ENTRY

        $seconds = time() - $t0;

        $cron_summary = "YES3 Exporter cron jobs completed for {$projects} project(s). Run time was {$seconds} seconds.";

        $params = [
            "log_entry_type" => EMLOG_TYPE_CRON_LOG,
            "cronlog" => $cronlog
        ];

        $this->log( $cron_summary, $params );

        return $cronlog;
    }

    private function cronJob( $methodName )
    {
        try {

            $cronLog = $this->$methodName();
        } 
        catch( \Exception $e ){

            $cronLog = "{$methodName} ERROR: " . $e->getMessage();
            $this->logException( "{$methodName} cron job exception", $e);
        }

        return $cronLog;
    }

    /**
     * Ensures cron runs once per 24 hour interval
     * 
     * function: okayToRunCron
     * 
     * 
     * @return int|false
     * @throws Exception
     */
    private function okayToRunCron()
    {
        $today = strftime("%F");
        
        $cron_ran_at = $this->getSystemSetting("cron-ran-at");
        if ( $cron_ran_at ) {

            $lastRunDay = strftime("%F", strtotime($cron_ran_at));

            if ( $today === $lastRunDay ){

                return false; // ran today sometime
            }
        }

        $cron_time = $this->getSystemSetting("cron-time"); // hh:mm:ss to run job
        if ( !$cron_time ) {

            $cron_time = "00:11:00";
            $this->setSystemSetting("cron-time", $cron_time);
        }
        $runAt = strtotime( $today." ".$cron_time ); // today's cron run time

        return ( time() >= $runAt );
    }

    public function emailDailyLog(){

        if ( !$to = $this->getProjectSetting('notification-email') ){

            return "Cannot email daily log summary: no email address is provided.";
        }

        $sincewhen = strftime("%F %T", time()-ONE_DAY);

        $cc = "";

        $bcc = "";

        $project_contact = $this->getProjectContact(); // as stored in project settings

        if ( $project_contact['project_contact_email'] ){

            $from = $project_contact['project_contact_email'];
        }
        else {

            $from = $to;
        }

        $fromName = "YES3 Exporter";

        $subject = "YES3 Exporter Daily Log Report for PID #" . $this->getProjectId();

        $export_logs = $this->getExportLogs("", false, $sincewhen);

        if ( !$export_logs ) {
            
            $export_logs = [];

            return "The daily activity log summary was NOT emailed because there was nothing to report.";
        }

        $msg = '<html><body style="font-family:arial,helvetica;">';

        $msg .= '<style>td,th{padding-right: 10px;text-align:left;}</style>';

        $msg .= '<p>You are receiving this email because you have enabled notifications from the REDCap YES3 Exporter.</p>';

        $msg .= '<table style="border-collapse:collapse;"><tbody>';

        $msg .= "<tr>" . $this->emailTableCell("td", "Date and time of report") . $this->emailTableCell("td", strftime("%F %T")) . "</tr>";
        $msg .= "<tr>" . $this->emailTableCell("td", "REDCap host") . $this->emailTableCell("td", APP_PATH_WEBROOT_FULL) . "</tr>";
        $msg .= "<tr>" . $this->emailTableCell("td", "REDCap project id (pid)") . $this->emailTableCell("td", $this->getProjectId()) . "</tr>";
        $msg .= "<tr>" . $this->emailTableCell("td", "REDCap project title") . $this->emailTableCell("td", $this->getProject()->getTitle()) . "</tr>";

        $msg .= '</tbody></table>';

        //$msg .= '<p style="text-decoration:underline;">Export logging activity</p>';

        $msg .= '<p>' . count($export_logs) . ' export events were logged in the past 24 hours. Use the YES3 Exporter Log plugin to inspect and/or print the detailed log entries.</p>';

        $msg .= '<table style="border-collapse:collapse;"><tbody>';

        $msg .= '<tr>';

        $msg .= $this->emailTableCell("th", "log_id");
        $msg .= $this->emailTableCell("th", "timestamp");
        $msg .= $this->emailTableCell("th", "user");
        $msg .= $this->emailTableCell("th", "log_message");
        $msg .= $this->emailTableCell("th", "destination");

        $msg .= '</tr>';

        foreach ($export_logs as $log){

            $msg .= '<tr>';

            $msg .= $this->emailTableCell("td", $log['log_id']);
            $msg .= $this->emailTableCell("td", $log['timestamp']);
            $msg .= $this->emailTableCell("td", $log['username']);
            $msg .= $this->emailTableCell("td", $log['message']);
            $msg .= $this->emailTableCell("td", $log['destination']);

            $msg .= '</tr>';
        }

        $msg .= '</tbody></table>';

        $msg .= "</body></html>";
        /*
        print "to=" . $to
            . "<br>from=" . $from
            . "<br>subject=" . $subject
            . "<br>msg=<br>" . $msg
            . "<br>fromName=" . $fromName
        ;
        */
        $result = \REDCap::email( 
            
            $to,
            $from,
            $subject,
            $msg,
            $cc,
            $bcc,
            $fromName

        );

        if ( $result ){

            $this->setProjectSetting("notification-email-ran-at", strftime("%F %T"));

            return "The daily activity log summary was emailed to {$to}.";
        }

        return "The daily activity log summary was NOT emailed.";
    }

    private function getProjectContact()
    {
        $sql = "
        SELECT c1.value AS `project_contact_email`, c2.value AS `project_contact_name` 
        FROM redcap_config c1
          LEFT JOIN redcap_config c2 ON c2.field_name = 'project_contact_name'
        WHERE c1.field_name = 'project_contact_email'
        ";

        return Yes3::fetchRecord( $sql );
    }

    private function emailTableCell( $TdOrTh, $content ){

        return "<{$TdOrTh} style='padding-right:15px;text-align:left'>{$content}</{$TdOrTh}>";
    }

    /* ==== DAILY HOUSEKEEPING ==== */

    public function hk_generations()
    {
        $nGens = $this->getProjectSetting("export-spec-backup-retention");

        if ( $nGens === "all" ) return "hk_generations: Nothing to do since project is configured to retain all backups.";

        $log = "";

        $exports = $this->getExportSpecificationList(); if ( !$exports ) $exports=[];

        if ( !count($exports) ) return "hk_generations: Nothing to do since project has no export backups saved.";

        //Yes3::logDebugMessage($this->getProjectId(), print_r($exports, true), "hk_generations");

        foreach($exports as $export){

            $specification_history = $this->getExportSpecification($export['export_uuid'], 0, true);
            
            $nHx = count($specification_history);

            if ( $log ) $log .= "\n";

            $log .= "export_uuid=" . $export['export_uuid'] . ", export_name=" . $export['export_name'] . ", generation count=" . $nHx;

            $k= 0;
            $removed = 0;
            $ts = "";
            foreach ( $specification_history as $hx) {

                $k++;

                if ( $k <= $nGens ){

                    $ts = $hx['timestamp'];
                }
                else {

                    $removed++;
                }
            }

            if ( !$removed ){

                $log .= ": no backup generations removed.";
            }
            else {
            
                $log .= ": " . $removed . " generations saved before " . strftime("%F %T", strtotime($ts)) . " removed. ";

                $pSql = "project_id = ? and export_uuid = ? and message = ? and timestamp < ?";
                $params = [$this->getProjectId(), $export['export_uuid'], EMLOG_MSG_EXPORT_SPECIFICATION, $ts];
                $this->removeLogs( $pSql, $params);
            }
        }
        return $log;
    }

    public function getExportSpecificationList($get_removed=""):array
    {
       /**
         * Distinct export specifications best determined by direct query
         */
        $sqlUUID = "
        SELECT DISTINCT p01.`value` AS `export_uuid`
        FROM redcap_external_modules_log x
        INNER JOIN redcap_external_modules_log_parameters p01 ON p01.log_id=x.log_id AND p01.name='export_uuid'
        WHERE x.project_id=? and x.message=?
        ";

        $UUIDs = Yes3::fetchRecords($sqlUUID, [$this->getProjectId(), EMLOG_MSG_EXPORT_SPECIFICATION]);

        $data = [];

        foreach($UUIDs as $u){

            $s = $this->getExportSpecification($u['export_uuid']);

            if ( $s['removed']==='0' || $get_removed ) {

                $data[] = [
                    'timestamp' => $s['timestamp'],
                    'log_id' => $s['log_id'],
                    'export_uuid' => $s['export_uuid'],
                    'export_name' => ( $s['export_name'] ) ? Yes3::escapeHtml($s['export_name']) : "noname-{$s['log_id']}",
                    'export_label' => ( $s['export_label'] ) ? Yes3::escapeHtml($s['export_label']) : "",
                    'export_order' => $s['export_order'],
                    'export_layout' => $s['export_layout'],
                    'export_username' => ( $s['export_username'] ) ? $s['export_username'] : "nobody",
                    'export_batch' => $s['export_batch'],
                    'removed' => $s['removed']
                ];
            }
        }

        return $data;
    }

    public function countExportItems( $export_items_json )
    {
        if ( !$export_items_json ){

            return "0";
        }

        $elements = json_decode( $export_items_json, true );

        if ( !is_array($elements) ){

            return "err";
        }

        return (string) count( $elements );
    }

   
    /* ==== HOOKS ==== */

    public function redcap_module_link_check_display( $project_id, $link )
    {  
        if ( $this->yes3UserRights()['exporter'] ){

            if ( \REDCap::isLongitudinal() || $link['name'] !== "YES3 Exporter Event Prefixes" ){ 

                return $link;
            }
        }

        return false;
    }
}
