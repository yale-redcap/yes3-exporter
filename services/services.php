<?php

namespace Yale\Yes3FieldMapper;

use Exception;

/**
 * Enable PHP error message output to browser.
 * DISABLE IN PRODUCTION!
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$module = new Yes3FieldMapper();

/**
 * validate the csrf token
 * which can be passed by AJAX calls in the request header as 'X-CSRF-Token'
 * or as a POST from a redcap form as 'redcap_csrf_token'
 */

$csrf_token = "";

if ( isset($_POST['croms_csrf_token']) ) {
   $csrf_token = $_POST['croms_csrf_token'];
}
else {
   $headers = apache_request_headers();
   if ( $headers['X-CSRF-Token'] ){
      $csrf_token = $headers['X-CSRF-Token'];
   }   
}

if ( !$csrf_token ){
   toesUp("error: csrf token missing");
}

/**
 * Validate the csrf token against the list of REDCap-generated tokens
 * for this session.
 */
if ( !in_array( $csrf_token, $_SESSION['redcap_csrf_token']) ){
   toesUp("error: invalid csrf token.");
}

/**
 * Validate the 'request' parameter, 
 * which can be via GET or POST
 */

if ( isset($_POST['request']) ) {
    $request = $_POST['request'];
}
elseif ( isset($_GET['request']) ) {
   $request = $_GET['request'];
}
else {
   toesUp("request parameter not passed");
}
 
if ( !requestIsValid($request) ) {
    toesUp("error: invalid request: ".$request);
};
 
/**
 * Execute the requested function and head out.
 */
exit ( call_user_func( __NAMESPACE__."\\".$request ) );

/**
 * Handle the reported error by going toes-up.
 */
function toesUp($errmsg)
{
    throw new \Exception("ARHH! YES3 Services reports ".$errmsg);  
}
 
 /**
  * Only functions defined in this namespace will be accepted.
  */
function requestIsValid( $request ):bool 
{
    return function_exists( __NAMESPACE__."\\".$request );
}

function saveExportSettings()
{
    global $module;

    $eventSettingsSaved = saveEventSettings();

    $specificationsSaved = 0;

    $specifications = json_decode($_POST['specifications'], true);

    if ( !is_array($specifications) ){

        $specificationsSaved = 0;
    }
    else {
        foreach ( $specifications as $specification ){

            $specificationsSaved += saveExportSpecification( $specification );
        }
    }

    return "{$eventSettingsSaved} events and {$specificationsSaved} specifications saved.";
}

function saveExportSpecification( $specification )
{
    global $module;

    if ( !isset($specification['mapping_specification']) ){

        $specification['mapping_specification'] = [];
    }

    if ( !isset($specification['field_mappings']) ){

        $specification['field_mappings'] = [];
    }
        
    $logId = $module->log(
        "export_specification",
        [
            "user" => $module->username,
            "setting" => "export-specification",
            "export_uuid" => $specification['export_uuid'],
            "removed" => $specification['removed'],
            "export_specification_json" => json_encode($specification)
        ]
    );

    if ( $logId ){

        return 1;
    }

    return 0;
}

function saveEventSettings()
{
    global $module;

    $events = json_decode($_POST['events'], true);

    if ( !is_array($events) ){

        return -1;
    }
    
    $logId = $module->log(
        "export_events",
        [
            "user" => $module->username,
            "setting" => "export-events",
            "export_events_json" => json_encode($events)
        ]
    );

    if ( $logId ){

        return count($events);
    }

    return -2;
}

function saveExportSettings_legacy(): string
{
    global $module;
    
    $logId = $module->log(
        "export_settings",
        [
            "user" => $module->username,
            "setting" => "export-settings",
            "export_settings_json" => json_encode($_POST['export_settings'])
        ]
    );

    if ( $logId ){

        return "settings saved";
    }

    return "Fiddlesticks: Settings NOT saved due to some unknowable error.";
}

function getExportSettings()
{
    global $module;

    //$specifications = getExportSpecifications();

    $output = [
        'specification_settings' => getExportSpecifications(),
        'event_settings' => getEventSettings()
    ];

    return json_encode($output);
}

function getEventSettings()
{
    global $module;

    $fields = "export_events_json";

    $pSql = "SELECT {$fields} WHERE project_id=? AND setting='export-events' ORDER BY timestamp DESC LIMIT 1";

    if ( $x = $module->queryLogs($pSql, [$module->project_id])->fetch_assoc() ){

        return json_decode($x['export_events_json']);
    }

    return [];
}

function getExportSpecifications()
{
    global $module;
    
    $log_id = (int) $_POST['log_id'];

    /**
     * retrieve the unique export UUIDs
     */

    $pSql = "SELECT DISTINCT export_uuid WHERE export_uuid IS NOT NULL AND setting='export-specification' AND project_id=?";

    $uuid_result = $module->queryLogs($pSql, [$module->project_id]);
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
        $params = [$module->project_id, $uuids[$i]];

        if ( $specification_settings = $module->queryLogs($pSql, $params)->fetch_assoc() ){

            $specification = json_decode($specification_settings['export_specification_json']);

            if ( !$specification->removed ) $specification->removed = "0";

            //if ( $specification->removed !== "1" ){

                $specifications[] = $specification;
            //}
        }
    }

    return $specifications;
}

function getExportSettings_legacy()
{
    global $module;
    
    $log_id = (int) $_POST['log_id'];

    $fields = "log_id, message, setting, user, timestamp, export_settings_json";

    if ( $log_id ){

        $pSql = "SELECT {$fields} WHERE log_id=?";
        $params = [$log_id];
    }
    else {

        $pSql = "
            SELECT {$fields}
            WHERE project_id=? AND setting='export-settings' AND export_settings_json IS NOT NULL
            ORDER BY timestamp DESC LIMIT 1
        ";
        $params = [$module->project_id];
    }

    if ( $x = $module->queryLogs($pSql, $params)->fetch_assoc() ){

        $output = [
            "result" => "success",
            "log_id" => (int)$x['log_id'],
            "timestamp" => $x['timestamp'],
            "user" => $x['user'],
            "export_settings" => json_decode( $x['export_settings_json'], true )
        ];
    }
    else {

        $output = [
            "result" => "fail",
            "log_id" => 0,
            "timestamp" => 0,
            "user" => "",
            "export_settings" => []
        ];        
    }

    return json_encode( $output );
}

/**
 * Demonstration function, returns the provided message.
 * 
 * Because the response may be displayed by the browser, 
 * HTML tags and quotes are neutralized by conversion to HTML entities.
 * 
 * function: Yale\Yes3\Services\hello_world
 * 
 * 
 * @return string
 */
function hello_world(): string
{
    $message = (string) $_POST['message'];

    return "Message received serverside: " . htmlentities($message, ENT_QUOTES);
}

function get_wayback_html()
{
    global $module;

    $export_uuid = $_POST['export_uuid'];
    
    $fields = "log_id, message, user, timestamp, export_uuid, field_mappings";
    
    $pSql = "
        SELECT {$fields}
        WHERE project_id=? AND setting='yes3-exporter-field-map' AND export_uuid=?
        ORDER BY timestamp DESC
    ";

    $params = [$module->project_id, $export_uuid];

    $result = $module->queryLogs($pSql, $params);

    $html = "";

    while( $x = $result->fetch_assoc() ){

        $log_id = $x['log_id'];
        $bytes = str_pad( strval(strlen($x['field_mappings'])), 6, " ", STR_PAD_LEFT);
        $user = Yes3::escapeHtml($x['user']);
        $time = date("D m/d/Y g:i a",  strtotime($x['timestamp']) );

        $html .= "\n<option value='{$log_id}'>{$time}{$bytes} bytes ({$user})</option>";
    }

    return $html;
}

/**
 * Saves field mappings to the EM log
 * 
 * function: save_field_mappings
 * 
 * 
 * @return string
 * @throws Exception
 */
function save_field_mappings()
{
    global $module;

    $export_uuid = $_POST['export_uuid'];

    $field_mappings_json = $_POST['field_mappings_json'];

    $logMsg = "YES3 Exporter Mappings";

    $logId = $module->log(
        $logMsg,
        [
            "export_uuid" => $export_uuid,
            "user" => $module->username,
            "setting" => "yes3-exporter-field-map",
            "field_mappings" => $field_mappings_json
        ]
    );

    if ( $logId ){

        return "Success: YES3 Exporter Field Mappings for " . Yes3::escapeHtml($export_uuid) . " saved.";
    }

    return "Fiddlesticks: Mappings NOT saved due to some unknowable error.";
}

function get_field_mappings()
{
    global $module;

    $export_uuid = (string) $_POST['export_uuid'];

    $log_id = (int) $_POST['log_id'];

    $fields = "log_id, message, user, timestamp, export_uuid, field_mappings";

    if ( $log_id ){

        $pSql = "SELECT {$fields} WHERE log_id=?";
        $params = [$log_id];
    }
    else {

        $pSql = "
            SELECT {$fields}
            WHERE project_id=? AND setting='yes3-exporter-field-map' AND export_uuid=?
            ORDER BY timestamp DESC LIMIT 1
        ";
        $params = [$module->project_id, $export_uuid];
    }

    $map_record = $module->queryLogs($pSql, $params)->fetch_assoc();

    $msg = "log_id=" . $map_record['log_id'] . ", bytes=" . strlen($map_record['field_mappings']);

    //Yes3::logDebugMessage($module->project_id, $pSql, "get_field_mappings:pSql");
    //Yes3::logDebugMessage($module->project_id, $msg, "get_field_mappings:result");

    if ( !$map_record ){

        $map_record = [

            "log_id" => 0,

            "message" => "",

            "user" => "",

            "export_uuid" => $export_uuid,
            
            "timestamp" => "",

            "formatted_time" => "",

            "field_mappings" => [

                "elements" => [
                    [
                        'yes3_fmapr_data_element_name' => "redcap_element_1",
                        'element_origin' => "redcap",
                        'redcap_field_name' => \REDCap::getRecordIdField(),
                        'redcap_event_id' => $module->getFirstEventId(),
                        'redcap_object_type' => "field",
                        'redcap_form_name' => "",
                        'values' => []
                    ]
                ]  
            ],
        ];
    }
    else {

        $map_record['field_mappings'] = json_decode( $map_record['field_mappings'], true );

        $map_record['formatted_time'] = date("D m/d/Y g:i a",  strtotime($map_record['timestamp']) );
    }

    return Yes3::json_encode_pretty( $map_record );
}

function escHtml( $s )
{
    return Yes3::escapeHtml( $s );
}

function get_project_settings():string 
{
    global $module;

    $field_metadata_structures = get_field_metadata_structures();
    $form_metadata_structures  = get_form_metadata_structures();

    // note: field_metadata_structures is properly html-escaped

    /**
     * Count fields on the event grid if longitudinal.
     * Otherwise, count all fields.
     */
    if ( \REDCap::isLongitudinal() ){

        $sqlCount = "SELECT COUNT(distinct field_name) AS field_count
        FROM redcap_metadata m
        INNER JOIN redcap_events_forms ef ON ef.form_name=m.form_name
        INNER JOIN redcap_events_metadata em on ef.event_id=em.event_id
        INNER JOIN redcap_events_arms ea on ea.arm_id=em.arm_id AND ea.project_id=m.project_id
        WHERE m.project_id=?
        AND m.field_name<>?
        AND m.element_type<>'descriptive'";
    } 
    else {

        $sqlCount = "SELECT COUNT(*) AS field_count
        FROM redcap_metadata m
        WHERE m.project_id=? 
        AND m.field_name<>?
        AND m.element_type<>'descriptive'";
    }

    return Yes3::json_encode_pretty( [
        'project_id' => $module->project_id,
        'field_index' => $field_metadata_structures['field_index'],
        'field_metadata' => $field_metadata_structures['field_metadata'],
        'field_count' => Yes3::fetchValue($sqlCount, [$module->project_id, \REDCap::getRecordIdField()]),
        'field_autoselect_source' => $field_metadata_structures['field_autoselect_source'],
        'form_index' => $form_metadata_structures['form_index'],
        'form_metadata' => $form_metadata_structures['form_metadata'],
        'event_metadata' => get_event_metadata(),
        'project_event_metadata' => get_project_event_metadata(),
        'default_event_id' => get_first_event_id(),
        'specification_settings' => get_specification_settings(),
        'event_abbreviations_settings' => get_event_abbreviation_settings()
    ] );
}

function get_specification_settings()
{
    global $module;

    $specification_keys = $module->getProjectSetting('specification-key');
    $specification_names = $module->getProjectSetting('specification-name');
    $specification_descriptions = $module->getProjectSetting('specification-description');

    $x = [];

    for ( $i=0; $i<count($specification_keys); $i++ ){
        $x[] = [
            'specification_key' => Yes3::escapeHtml($specification_keys[$i]),
            'specification_name' => Yes3::escapeHtml($specification_names[$i]),
            'specification_description' => Yes3::escapeHtml($specification_descriptions[$i])
        ];
    }
    
    return $x;
}

function get_event_abbreviation_settings()
{
    global $module;

    $event_ids = $module->getProjectSetting('event-id');
    $event_abbreviations = $module->getProjectSetting('event-abbreviation');

    $x = [];

    for ( $i=0; $i<count($event_ids); $i++ ){
        $x[] = [
            'event_id' => Yes3::escapeHtml($event_ids[$i]),
            'event_abbreviation' => Yes3::escapeHtml($event_abbreviations[$i])
        ];
    }
    
    return $x;
}

function get_fields():array
{
   global $module;

   $fields = Yes3::fetchRecords("
SELECT m.`field_name`, m.`element_label`, m.`element_type`, m.`element_enum`
FROM redcap_metadata m
WHERE m.`project_id`={$module->project_id}
  AND m.`element_type` NOT IN('descriptive')
ORDER BY m.`field_order`
   ");

   $xx = [];
   foreach ($fields as $field){
      $choices = [];
      if ( $field['element_type']==="radio" ){
         $vv = $module->getChoiceLabels($field['field_name']);
         foreach ( $vv as $value => $label) {
            $choices[] = [
               'value' => $value,
               'label' => Yes3::escapeHtml(strip_tags($label))
            ];
         }
      }
      $xx[] = [
         'field_name' => $field['field_name'],
         'field_label' => Yes3::escapeHtml(strip_tags($field['element_label'])),
         'field_type' => $field['element_type'],
         'field_choices' => $choices
      ];
   }

   return $xx;
}

function get_field_metadata_structures(): array
{
    global $module;

    if ( \REDCap::isLongitudinal() ){

        $sql = "
        SELECT DISTINCT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum
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
        SELECT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum
        FROM redcap_metadata m
        WHERE m.project_id=?
        AND m.element_type NOT IN('descriptive')
        ORDER BY m.field_order      
        ";
    }

    $fields = Yes3::fetchRecords($sql, [$module->project_id]);

    $field_metadata = [];

    $field_autoselect_source = [];

    $field_index_num = 0;

    $field_index = [];

    foreach ($fields as $field){

        $valueset = [];

        if ( $field['element_type']==="radio" ){
           $vv = $module->getChoiceLabels($field['field_name']);
           foreach ( $vv as $value => $label) {
              $valueset[] = [
                 'value' => (string)$value,
                 'label' => Yes3::escapeHtml(strip_tags($label))
              ];
           }
        }

        $field_label = ellipsis( Yes3::escapeHtml($field['element_label']) );

        $field_metadata[] = [

            'field_name'        => $field['field_name'],
            'form_name'         => Yes3::escapeHtml($field['form_name']),
            'field_type'        => $field['element_type'],
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

function ellipsis( $s, $len=64 )
{
    $s = trim($s);
    if ( strlen($s) > $len-3 ) {
        return substr($s, 0, $len-3)."...";
    }
    return $s;
}

/**
 * 
 * note (2/23/2022): event_metadata now deprecated. moving to project_event_metadata
 * 
 * function: Yale\Yes3FieldMapper\get_event_metadata
 * 
 * 
 * @return array
 * @throws Exception
 */
function get_event_metadata():array
{
    global $module;

    $sql = "
SELECT e.event_id, e.descrip AS `event_label`
FROM redcap_events_metadata e
  INNER JOIN redcap_events_arms a on e.arm_id=a.arm_id
WHERE a.project_id=?
ORDER BY e.day_offset
    ";

    $ee = Yes3::fetchRecords($sql, [$module->project_id]);

    $event_metadata = [];

    foreach ($ee as $e){
        $event_metadata[(string)$e['event_id']] = [
            'event_label' => Yes3::escapeHtml($e['event_label'])
        ];
    }

    return $event_metadata;   
}

function get_project_event_metadata():array
{
    global $module;

    $sql = "
SELECT e.event_id, e.descrip AS `event_label`
FROM redcap_events_metadata e
  INNER JOIN redcap_events_arms a on e.arm_id=a.arm_id
WHERE a.project_id=?
ORDER BY e.day_offset
    ";

    $ee = Yes3::fetchRecords($sql, [$module->project_id]);

    $project_event_metadata = [];

    foreach ($ee as $e){
        $project_event_metadata[] = [
            'event_id' => (string) $e['event_id'],
            'event_label' => Yes3::escapeHtml($e['event_label'])
        ];
    }

    return $project_event_metadata;   
}


/**
 * Note (2/23/2022): event.descrip now deprecated, use event.event_label 
 * 
 * function: Yale\Yes3FieldMapper\get_form_metadata_structures
 * 
 * 
 * @return array
 * @throws Exception
 */
function get_form_metadata_structures():array
{
    global $module;

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
            'event_id' => get_first_event_id(),
            'descrip' => "Event 1",
            'event_label' => "Event_1"
        ]];

        $sql = "
        SELECT m.form_name, m.form_menu_description
        FROM redcap_metadata m
        WHERE m.project_id=? AND m.form_menu_description IS NOT NULL
        ";

    }

    $mm = Yes3::fetchRecords($sql, [$module->project_id]);

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

            $ee = Yes3::fetchRecords($sqlE, [$module->project_id, $m['form_name']]);

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

        $fields = Yes3::fetchRecords($sqlF, [$module->project_id, $m['form_name']]);
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

function get_first_event_id()
{
   global $module;

   $sql = "SELECT e.event_id
   FROM redcap_events_metadata e
     INNER JOIN redcap_events_arms a on a.arm_id=e.arm_id
   WHERE a.project_id=?
   ORDER BY e.day_offset, e.event_id
   LIMIT 1";

   return Yes3::fetchValue($sql, [$module->project_id]);
}

function get_event_select_options_html()
{
    global $module;

    $sql = "SELECT e.event_id, e.descrip
    FROM redcap_events_metadata e
    INNER JOIN redcap_events_arms a ON a.arm_id=e.arm_id
    WHERE a.project_id=?
    ORDER BY e.day_offset, e.event_id";

    $events = Yes3::fetchRecords($sql, [$module->project_id]);

    $eventSelectOptionsHtml = "<option value=''></option>";
    foreach ($events as $event){
        $eventSelectOptionsHtml .= "<option value='".$event['event_id']."'>".$event['descrip']."</option>";
    }

    return $eventSelectOptionsHtml;
}

 
?>