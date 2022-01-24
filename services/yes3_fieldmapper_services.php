<?php

/**
 * Enable PHP error message output to browser.
 * DISABLE IN PRODUCTION!
 */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();
use Yale\Yes3\Yes3;

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
    throw new Exception("ARHH! YES3 Services reports ".$errmsg);  
}
 
 /**
  * Only functions defined in this namespace will be accepted.
  */
function requestIsValid( $request ):bool 
{
    return function_exists( __NAMESPACE__."\\".$request );
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

    $specification_index = (int) $_POST['specification_index'];

    $field_mappings_json = $_POST['field_mappings_json'];

    //if ( !$field_mappings_json = json_encode($field_mappings) ) {
    //    return "Fail: Mappings NOT saved: json error '" . json_last_error_msg() . "' reported.";
    //}

    if ( !$key = Yes3::normalized_string($module->getProjectSetting('specification-key')[$specification_index]) ) {
        return "Fail: Mappings NOT saved: no specification key provided.";        
    }

    $logMsg = "Success: Field map '{$key}' saved.";

    $logId = $module->log(
        $logMsg,
        [
            "setting" => "field-map",
            "specification_index" => $specification_index,
            "specification_key" => $key,
            "map_label" => "my map label",
            "user" => $module->username,
            "field_mappings" => $field_mappings_json
        ]
    );

    if ( $logId ){

        return $logMsg;

    }

    return "Fiddlesticks: Mappings NOT saved due to some unknowable error.";
}

function save_field_mappings_legacy()
{
    global $module;

    $specification_index = (int) $_POST['specification_index'];
    $field_mappings = $_POST['field_mappings'];

    if ( !$field_mappings_json = json_encode($field_mappings) ) {
        return "Field mappings NOT saved: json error '" . json_last_error_msg() . "' reported.";
    }

    $specification_field_mappings = $module->getProjectSetting('specification-field-mappings');

    $specification_field_mappings[$specification_index] = $field_mappings_json;

    $module->setProjectSetting('specification-field-mappings', $specification_field_mappings);

    return "Mappings saved at " . strftime("%F %T") . ".";

}

function get_field_mappings()
{
    global $module;

    $specification_index = (int) $_POST['specification_index'];

    $log_id = (int) $_POST['log_id'];

    if ( !$key = Yes3::normalized_string($module->getProjectSetting('specification-key')[$specification_index]) ) {
        return "Mappings NOT saved: no specification key provided.";        
    }

    $fields = "log_id, message, user, timestamp, map_label, field_mappings";

    if ( $log_id ){

        $pSql = "
            SELECT {$fields}
            WHERE log_id=?
        ";
        $params = [$log_id];
    }
    else {

        $pSql = "
            SELECT {$fields}
            WHERE project_id=? AND setting='field-map' AND specification_key=?
            ORDER BY timestamp DESC LIMIT 1
        ";
        $params = [$module->project_id, $key];
    }

    $map_record = $module->queryLogs($pSql, $params)->fetch_assoc();

    $map_record['field_mappings'] = json_decode( $map_record['field_mappings'], true );

    // insert the record id if this is a new mapping
    if ( !$map_record['field_mappings'] ){

        $map_record['field_mappings'] = [
            'specification_index' => (string) $specification_index,
            "elements" => []
        ];
    }
    if ( !$map_record['field_mappings']['elements'] ){

        $map_record['field_mappings']['elements'] = [
            [
                'yes3_fmapr_data_element_name' => "redcap_field_1",
                'redcap_field_name' => REDCap::getRecordIdField(),
                'redcap_event_id' => $module->getFirstEventId(),
                'values' => []
            ]
        ];
    }

    //Yes3::logDebugMessage($module->project_id, print_r($map_record, true), "get_field_mappings");

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

    // note: field_metadata_structures is properly html-escaped

    return Yes3::json_encode_pretty( [
        'project_id' => $module->project_id,
        'field_index' => $field_metadata_structures['field_index'],
        'field_metadata' => $field_metadata_structures['field_metadata'],
        'field_autoselect_source' => $field_metadata_structures['field_autoselect_source'],
        'form_metadata' => get_form_metadata(),
        'event_metadata' => get_event_metadata(),
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
  AND m.`element_type` NOT IN('descriptive', 'checkbox')
  AND m.`field_name` NOT LIKE '%\_complete'
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
            WHERE m.project_id=?
            AND m.element_type NOT IN('textarea', 'checkbox', 'descriptive')
            AND m.field_name NOT LIKE '%\_complete'
            ORDER BY m.field_order      
        ";
    }
    else {

        $sql = "
            SELECT m.field_order, m.form_name, m.field_name, m.element_type, m.element_label, m.element_enum
            FROM redcap_metadata m
            WHERE m.project_id=?
            AND m.element_type NOT IN('textarea', 'checkbox', 'descriptive')
            AND m.field_name NOT LIKE '%\_complete'
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
  
        $events = [48 =>'baseline', 49 => 'screen'];

        $field_label = ellipsis( Yes3::escapeHtml($field['element_label']) );

        $field_metadata[] = [

            'field_name'      => $field['field_name'],
            'form_name'       => Yes3::escapeHtml($field['form_name']),
            'field_label'     => $field_label,
            'field_valueset'  => $valueset

        ];

        $field_autoselect_source[] = [
            'value' => $field['field_name'],
            'label' => "[" . $field['field_name'] . "] " . $field_label
        ];

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

function get_form_metadata():array
{
    global $module;

    $events = [];

    if ( $isLong = \REDCap::isLongitudinal() ) {

        $sql = "
        SELECT DISTINCT m.form_name, m.form_menu_description
        FROM redcap_metadata m
            INNER JOIN redcap_events_forms ef ON ef.form_name=m.form_name
        WHERE m.project_id=? AND m.form_menu_description IS NOT NULL
        ";

    } else {

        $events = [[ 
            'event_id' => get_first_event_id(),
            'descrip' => "Event 1"
        ]];

        $sql = "
        SELECT m.form_name, m.form_menu_description
        FROM redcap_metadata m
        WHERE m.project_id=? AND m.form_menu_description IS NOT NULL
        ";

    }

    $mm = Yes3::fetchRecords($sql, [$module->project_id]);

    $form_metadata = [];

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
                    'descrip' => Yes3::escapeHtml($e['descrip'])
                ];
        
            }
        }

        $form_metadata[$m['form_name']] = [
            'form_label' => Yes3::escapeHtml($m['form_menu_description']),
            'form_events' => $events
        ];
    }

    return $form_metadata;   
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