<?php

namespace Yale\Yes3FieldMapper;

use Exception;

use function Yale\Yes3FieldMapper\toesUp as Yes3FieldMapperToesUp;

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$request = "";
  
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
 
//if ( !requestIsValid($request) ) {
//    toesUp("error: invalid request: ".$request);
//};

/**
 * validate the csrf token
 * which can be passed by AJAX calls in the request header as 'X-CSRF-Token'
 * or as a POST from a redcap form as 'redcap_csrf_token'
**/

$csrf_token = "";

if ( isset($_POST['csrf_token']) ) {
    $csrf_token = $_POST['csrf_token'];
 }
 else if ( isset($_GET['csrf_token']) ) {
    $csrf_token = $_GET['csrf_token'];
 }
 else {
   $headers = apache_request_headers();
   if ( $headers['X-CSRF-Token'] ){
      $csrf_token = $headers['X-CSRF-Token'];
   }   
}

if ( !$csrf_token ){
   toesUp("error: csrf token missing for request '{$request}'.");
}

/**
 * Validate the csrf token against the list of REDCap-generated tokens
 * for this session.
 */
if ( !in_array( $csrf_token, $_SESSION['redcap_csrf_token']) ){
    toesUp("error: invalid csrf token for request '{$request}'.");
}
 
/**
 * Execute the requested function and head out.
*/

execRequest($request);

//exit ( call_user_func( __NAMESPACE__ . "\\". $request ) ); // tainted version

function getDefaultEventPrefixes()
{
    global $module;

    return json_encode( $module->getDefaultExportEvents() );
}

/**
 * Handle the reported error by going toes-up.
 */
function toesUp($errmsg)
{
    throw new \Exception("YES3 Services reports ".$errmsg);  
}

function execRequest( $request ){
    /**
     * list of allowed function requests.
     * Can be updated using output from listNamespaceFunctions()
     */
    $function_registry = [
        'addexportspecification'
        , 'checkdownloadpermission'
        , 'checkexportpermission'
        , 'countexportitems'
        , 'downloaddata'
        , 'downloaddatadictionary'
        , 'downloadexportlog'
        , 'downloadzip'
        , 'eschtml'
        , 'exportdata'
        , 'get_event_abbreviation_settings'
        , 'get_event_metadata'
        , 'get_event_select_options_html'
        , 'get_field_mappings'
        , 'get_field_metadata_structures'
        , 'get_fields'
        , 'get_first_event_id'
        , 'get_form_metadata_structures'
        , 'get_project_event_metadata'
        , 'get_project_settings'
        , 'get_specification_settings'
        , 'get_wayback_html'
        , 'getdefaulteventprefixes'
        , 'geteventsettings'
        , 'getexportlogdata'
        , 'getexportlogrecord'
        , 'getexportlogrecordsql'
        , 'getexportlogs'
        , 'getexportspecification'
        , 'getexportspecificationlist'
        , 'getfieldmaprecord'
        , 'hello_world'
        , 'sanitizeuploadspec'
        , 'save_field_mappings'
        , 'saveeventsettings'
        , 'saveexportspecification'
    ];

    $fnIndex = array_search( strtolower($request), $function_registry );

    if ( $fnIndex===false ){

        toesUp("error: invalid request '{$request}'.");
    }

    exit( call_user_func( __NAMESPACE__ . "\\". $function_registry[ $fnIndex ] ) );
}
    
function addExportSpecification()
{
    global $module;

    $qParams = [
        'removed' => "0"
        , 'log_entry_type' => EMLOG_TYPE_EXPORT_SPECIFICATION
        , 'export_uuid' => $_POST['export_uuid']
        , 'export_name' => $_POST['export_name']
        , 'export_username' => $module->username
        , 'export_layout' => $_POST['export_layout']
        , 'export_selection' => "1"
        , 'export_criterion_field' => ""
        , 'export_criterion_event' => ""
        , 'export_criterion_value' => ""
        , 'export_target' => "download"
        , 'export_max_label_length' => ""
        , 'export_max_text_length' => ""
        , 'export_inoffensive_text' => ""
        , 'export_uspec_json' => ""
        , 'export_items_json' => ""
    ];

    $log_id = $module->log(
        EMLOG_MSG_EXPORT_SPECIFICATION,
        $qParams
    );

    //Yes3::logDebugMessage($module->project_id, print_r($qParams, true), 'saveExportSpecification' );
    if ( $log_id ){
        return "Success: new export parameters saved to EM log record# ".$log_id;
    }

    return "FAIL: The new export specification could not be created.";
}

function saveExportSpecification()
{
    global $module;

    $qParams = [
        'removed' => ""
        , 'log_entry_type' => EMLOG_TYPE_EXPORT_SPECIFICATION
        , 'export_uuid' => ""
        , 'export_name' => ""
        , 'export_username' => $module->username
        , 'export_layout' => ""
        , 'export_selection' => ""
        , 'export_criterion_field' => ""
        , 'export_criterion_event' => ""
        , 'export_criterion_value' => ""
        , 'export_target' => ""
        , 'export_max_label_length' => ""
        , 'export_max_text_length' => ""
        , 'export_inoffensive_text' => ""
        , 'export_remove_phi' => ""
        , 'export_remove_freetext' => ""
        , 'export_remove_largetext' => ""
        , 'export_remove_dates' => ""
        , 'export_shift_dates' => ""
        , 'export_hash_recordid' => ""
        , 'export_uspec_json' => ""
        , 'export_items_json' => ""
    ];

    foreach (array_keys($qParams) as $key){

        if ( isset($_POST[$key]) ){

            $qParams[$key] = $_POST[$key];
        }
    }

    $log_id = $module->log(
        EMLOG_MSG_EXPORT_SPECIFICATION,
        $qParams
    );

    //Yes3::logDebugMessage($module->project_id, print_r($qParams, true), 'saveExportSpecification' );
    if ( $log_id ){
        return "Success: export parameters saved to EM log record# ".$log_id;
    }

    return "FAIL: The export specification could not be saved.";
}

/**
 * Returns the requested export specification, json-encoded.
 * 
 * Adds a 'permission' property that will be 'allowed' or 'denied'. 
 * If denied, this will be the ONLY property returned.
 * 
 * function: getExportSpecification
 * 
 * 
 * @return string|false
 * @throws Exception
 */
function getExportSpecification()
{
    global $module;

    $export_uuid = $_POST['export_uuid'];

    if ( isset($_POST['log_id']) ){

        $log_id = (int) $_POST['log_id'];
    }
    else {

        $log_id = 0;
    }

    $specification = $module->getExportSpecification( $export_uuid, $log_id );

    if ( !$module->confirmSpecificationPermissions($specification)){

        $specification = [
            'permission'=>"denied"
        ];
    }
    else {

        $specification['permission'] = "allowed";
    }

    return json_encode( $specification );
}

function getExportSpecificationList():string
{
    global $module;

    $get_removed = (int) $_POST['get_removed'];

    /**
     * Distinct export specifications best determined by direct query
     */
    $sqlUUID = "
    SELECT DISTINCT p01.`value` AS `export_uuid`
    FROM redcap_external_modules_log x
    INNER JOIN redcap_external_modules_log_parameters p01 ON p01.log_id=x.log_id AND p01.name='export_uuid'
    WHERE x.project_id=? and x.message=?
    ";

    $UUIDs = Yes3::fetchRecords($sqlUUID, [$module->project_id, EMLOG_MSG_EXPORT_SPECIFICATION]);

    $data = [];

    foreach($UUIDs as $u){

        $s = $module->getExportSpecification($u['export_uuid']);

        if ( $s['removed']==='0' || $get_removed ) {

            $data[] = [
                'timestamp' => $s['timestamp'],
                'log_id' => $s['log_id'],
                'export_uuid' => $s['export_uuid'],
                'export_name' => ( $s['export_name'] ) ? Yes3::escapeHtml($s['export_name']) : "noname-{$s['log_id']}",
                'export_layout' => $s['export_layout'],
                'export_username' => ( $s['export_username'] ) ? $s['export_username'] : "nobody",
                'removed' => $s['removed']
            ];
        }
    }

    return json_encode($data);
}

function getExportLogRecordSQL( $log_id=0 )
{
    $sql = "
    SELECT x.project_id, x.log_id
    , x.`timestamp`
    , ui.username
    , x.`message`
    , p0.`value` AS `export_name`
    , p1.`value` AS `log_entry_type`
    , p2.`value` AS `export_uuid`
    , p3.`value` AS `destination`
    , p4.`value` AS `filename_data`
    , p5.`value` AS `filename_data_dictionary`
    , p6.`value` AS `exported_bytes`
    , p7.`value` AS `exported_items`
    , p8.`value` AS `exported_rows`
    , p9.`value` AS `exported_columns`
    FROM redcap_external_modules_log x
    INNER JOIN redcap_external_modules_log_parameters p0 ON p0.log_id=x.log_id AND p0.name='export_name'
    INNER JOIN redcap_external_modules_log_parameters p1 ON p1.log_id=x.log_id AND p1.name='log_entry_type'
    INNER JOIN redcap_external_modules_log_parameters p2 ON p2.log_id=x.log_id AND p2.name='export_uuid'
    LEFT  JOIN redcap_external_modules_log_parameters p3 ON p3.log_id=x.log_id AND p3.name='destination'
    LEFT  JOIN redcap_external_modules_log_parameters p4 ON p4.log_id=x.log_id AND p4.name='filename_data'
    LEFT  JOIN redcap_external_modules_log_parameters p5 ON p5.log_id=x.log_id AND p5.name='filename_data_dictionary'
    LEFT  JOIN redcap_external_modules_log_parameters p6 ON p6.log_id=x.log_id AND p6.name='exported_bytes'
    LEFT  JOIN redcap_external_modules_log_parameters p7 ON p7.log_id=x.log_id AND p7.name='exported_items'
    LEFT  JOIN redcap_external_modules_log_parameters p8 ON p8.log_id=x.log_id AND p8.name='exported_rows'
    LEFT  JOIN redcap_external_modules_log_parameters p9 ON p9.log_id=x.log_id AND p9.name='exported_columns'
    LEFT  JOIN redcap_user_information ui ON ui.ui_id=x.ui_id
    ";

    if ( $log_id ){

        $sql .= " WHERE x.`log_id`=? LIMIT 1";
    }
    else {
 
        $sql .= " WHERE x.project_id=? AND p1.`value`=? AND p2.`value`=? ORDER BY timestamp ASC";
    }

    return $sql;
}

function getExportLogRecord()
{
    global $module;
    $log_id = $_POST['log_id'];

    return json_encode( Yes3::fetchRecord( getExportLogRecordSQL($log_id), [ $log_id ] ) );
}

function downloadExportLog()
{
    global $module;

    $export_uuid = $_GET['export_uuid'];

    $export_name = $module->getExportSpecification( $export_uuid )['export_name'];

    $path = tempnam(sys_get_temp_dir(), "ys3");

    //$h = fopen( $path, "w+" );
    $h = $module->fopen_w_utf8( $path, "w+" );

    if ( $h===false ){

        exit("Fail: could not create temporary file {$path}");
    }

    $sql = getExportLogRecordSQL();

    $bytes = 0;

    //exit("downloadExportLog: {$export_uuid}, {$export_name}, {$path}, {$bytes}, {$sql}");

    foreach ( Yes3::recordGenerator($sql, [ $module->project_id, EMLOG_TYPE_EXPORT_LOG_ENTRY, $export_uuid ]) as $x ){

        if ( !$bytes ) {

            $bytes = fputcsv($h, array_keys($x));

            $export_name = $x['export_name'];
        }

        $bytes += fputcsv($h, array_values($x));
    }

    rewind($h);

    //exit("downloadExportLog: {$export_uuid}, {$export_name}, {$path}, {$bytes}");

    $chunksize = 1024 * 1024; // 1MB per one chunk of file.

    $filename = $module->exportLogFilename( $export_name );

    ob_start();

    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.$bytes);
    header('Content-Disposition: attachment;filename="'.$filename.'"');

    while (!feof($h)){

        $s = fread($h, $chunksize);

        if ( $s === FALSE ){

            break;
        }

        print($s);

        ob_flush();
        flush();
    }

    fclose($h);

    ob_end_flush();

    exit;
}

function getExportLogData()
{
    global $module;

    $LIMIT = (isset($_POST['limit'])) ? (int)$_POST['limit'] : 0;

    $export_uuid = $_POST['export_uuid'];
    $username = $_POST['username'] ?? "";
    $date0 = $_POST['date0'] ?? "";
    $date1 = $_POST['date1'] ?? "";

    $subSql = "
SELECT x.*, ui.username, p2.`value` AS `export_uuid`, p3.`value` AS `destination`
FROM redcap_external_modules_log x
  INNER JOIN redcap_external_modules_log_parameters p1 ON p1.log_id=x.log_id AND p1.name='log_entry_type'
  INNER JOIN redcap_external_modules_log_parameters p2 ON p2.log_id=x.log_id AND p2.name='export_uuid'
  INNER JOIN redcap_external_modules_log_parameters p3 ON p3.log_id=x.log_id AND p3.name='destination'
  INNER JOIN redcap_user_information ui ON ui.ui_id=x.ui_id
WHERE x.project_id=? AND p1.`value`=? AND p2.`value`=?
    ";

    $params = [ $module->project_id, EMLOG_TYPE_EXPORT_LOG_ENTRY, $export_uuid ];

    if ( $username ){

        $subSql .= " AND ui.username = ?";

        $params[] = $username;
    }

    if ( $date0 ){

        $subSql .= " AND DATEDIFF(x.timestamp, ?) >= 0";

        $params[] = strftime("%F", strtotime($date0));
    }

    if ( $date1 ){

        $subSql .= " AND DATEDIFF(x.timestamp, ?) <= 0";

        $params[] = strftime("%F", strtotime($date1));
    }

    $subSql .= " ORDER BY timestamp DESC LIMIT {$LIMIT}";

    $sql = "SELECT * FROM ( " . $subSql . " ) y ORDER BY y.log_id ASC";

    //exit( json_encode( ['sql'=>$sql] ) );

    $data = Yes3::fetchRecords($sql, $params);

    $observed_usernames = [];
    $observed_date0 = "";
    $observed_date1 = "";
    $message = "";
    $n = 0;

    if ( $data ){

        $observed_date0 = strftime("%F", strtotime($data[0]['timestamp']));
        $observed_date1 = strftime("%F", strtotime($data[count($data)-1]['timestamp']));

        foreach( $data as $x ){

            $n++;

            if ( !in_array($x['username'], $observed_usernames )){
                $observed_usernames[] = $x['username'];
            }
        }

        sort($observed_usernames);
    }

    $truncated = ( $n === $LIMIT ) ? 1:0;

    if ( $n===0 ){

        $message = "No records met your search criteria.";
    }
    elseif ( $truncated===1 ){

        $message = "WARNING: The maximum allowed number of records ({$LIMIT}) was returned. Please narrow your search criteria by user or date range.";
    }

    return json_encode([
        'message' => $message,
        'data' => $data,
        'n' => $n,
        'truncated' => $truncated,
        'observed_usernames' => $observed_usernames,
        'observed_date0' => $observed_date0,
        'observed_date1' => $observed_date1,
    ]);
}

function getExportLogs()
{
    global $module;

    $export_uuid = $_POST['export_uuid'];

    $columns = [
        "log_id",
        "user", 
        "timestamp", 
        "destination", 
        "message" 
    ];

    $data = [];

    foreach( $module->getExportLogs($export_uuid) as $logrecord ){

        $data[] = [
            $logrecord['log_id'],
            $logrecord['username'],
            $logrecord['timestamp'],
            $logrecord['destination'],
            $logrecord['message']
        ];
    }

    return( json_encode(
        [
            'columns' => $columns,
            'data' => $data
        ]
    ));
}

function checkDownloadPermission()
{
    global $module;

    $enable_host_filesystem_exports = $module->getProjectSetting("enable-host-filesystem-exports");
    $enable_user_data_downloads = $module->getProjectSetting("enable-user-data-downloads");

    if ( $enable_host_filesystem_exports==="Y" && $enable_user_data_downloads !== "Y" ) {

        throw new Exception("User data downloads are not enabled for this project.");
    }
}

function checkExportPermission()
{
    global $module;

    $enable_host_filesystem_exports = $module->getProjectSetting("enable-host-filesystem-exports");

    if ( $enable_host_filesystem_exports!=="Y" ) {

        throw new Exception("File system exports are not enabled for this project.");
    }
}

function downloadDataDictionary()
{
    global $module;

    $export_uuid = $_POST['export_uuid'] ?? $_GET['export_uuid'];

    return $module->downloadDataDictionary($export_uuid);
}

function downloadData()
{
    global $module;

    checkDownloadPermission();

    $export_uuid = $_POST['export_uuid'] ?? $_GET['export_uuid'];

    return $module->downloadData($export_uuid);
}

function downloadZip()
{
    global $module;

    checkDownloadPermission();

    $export_uuid = $_POST['export_uuid'] ?? $_GET['export_uuid'];

    return $module->downloadZip($export_uuid);
}

function exportData()
{
    global $module;

    checkExportPermission();

    $export_uuid = $_POST['export_uuid'];

    return $module->exportData($export_uuid);
}

function getFieldMapRecord($export_uuid)
{
    global $module;

    $fields = "log_id, message, user, timestamp, export_uuid, field_mappings";

    $pSql = "
        SELECT {$fields}
        WHERE project_id=? AND setting='yes3-exporter-field-map' AND export_uuid=?
        ORDER BY timestamp DESC LIMIT 1
    ";
    $params = [$module->project_id, $export_uuid];

    return $module->queryLogs($pSql, $params)->fetch_assoc();
}

/**
 * does what it can to tidy up an upload specification map
 * 
 * function: Yale\Yes3FieldMapper\sanitizeUploadSpec
 * 
 * @param mixed $uSpec
 * 
 * @return mixed
 */
function sanitizeUploadSpec( $uSpec )
{
    global $module;
    
    if ( !is_array( $uSpec )){

        return null;
    }

    if ( !is_array($uSpec['elements']) ){

        return null;
    }

    if ( count($uSpec['elements'])==0 ){

        return null;
    }
      
    //Yes3::logDebugMessage($module->project_id, print_r($uSpec, true), "sanitizeUploadSpec:pre");

    for($i=0; $i<count($uSpec['elements']); $i++){

        if ( isset($uSpec['elements'][$i]['name']) ){

            $uSpec['elements'][$i]['name'] = Yes3::inoffensiveFieldName($uSpec['elements'][$i]['name']);
        }
        else {

            $uSpec['elements'][$i]['name'] = "element_name_needed_here";
        }

        if ( isset($uSpec['elements'][$i]['type']) ){

            $uSpec['elements'][$i]['type'] = Yes3::normalized_string($uSpec['elements'][$i]['type']);
        }
        else {

            $uSpec['elements'][$i]['type'] = "element_type_needed_here";
        }

        if ( isset($uSpec['elements'][$i]['label']) ){

            $uSpec['elements'][$i]['label'] = Yes3::inoffensiveText($uSpec['elements'][$i]['label']);
        }
        else {

            $uSpec['elements'][$i]['label'] = "element label needed here";
        }

        if ( isset($uSpec['elements'][$i]['valueset']) && is_array($uSpec['elements'][$i]['valueset']) ){

            if ( $k = count($uSpec['elements'][$i]['valueset']) ) {

                $uSpec['elements'][$i]['type'] = "nominal";

                for($j=0; $j<$k; $j++){

                    if ( isset($uSpec['elements'][$i]['valueset'][$j]['value']) ){

                        $uSpec['elements'][$i]['valueset'][$j]['value'] = Yes3::inoffensiveText($uSpec['elements'][$i]['valueset'][$j]['value']);
                    }
                    else {

                        $uSpec['elements'][$i]['valueset'][$j]['value'] = "value_needed_here";
                    }

                    if ( isset($uSpec['elements'][$i]['valueset'][$j]['label']) ){

                        $uSpec['elements'][$i]['valueset'][$j]['label'] = Yes3::inoffensiveText($uSpec['elements'][$i]['valueset'][$j]['label']);
                    }
                    else {

                        $uSpec['elements'][$i]['valueset'][$j]['label'] = "value label needed here";
                    }
                }
            }
        }
    }
  
    //Yes3::logDebugMessage($module->project_id, print_r($uSpec, true), "sanitizeUploadSpec:post");

    return $uSpec;
}

function getEventSettings()
{
    global $module;

    return json_encode( $module->getEventSettings() );
}

function saveEventSettings()
{
    global $module;

    $events = json_decode($_POST['events'], true);

    if ( !is_array($events) ){

        return -1;
    }
    
    // the 'setting' parameter will be dropped in a future release (in favor of log_entry_type)
    $logId = $module->log(
        EMLOG_MSG_EXPORT_EVENTS,
        [
            "user" => $module->username,
            "log_entry_type" => EMLOG_TYPE_EXPORT_EVENTS,
            "setting" => "export-events",
            "export_events_json" => json_encode($events)
        ]
    );

    if ( $logId ){

        return count($events);
    }

    return -2;
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

    $specification_history = $module->getExportSpecification($export_uuid, 0, true);

    $html = "<option disabled selected value=''>select a backup</option>";

    foreach ( $specification_history as $x) {

        $log_id = $x['log_id'];

        $elements = countExportItems($x['export_items_json']);

        $user = Yes3::escapeHtml($x['export_username']);

        $time = date("D m/d/Y g:i a",  strtotime($x['timestamp']) );

        $html .= "\n<option value='{$log_id}'>{$time} - {$elements} element(s) ({$user})</option>";
    }

    return $html;
}

function countExportItems( $export_items_json )
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

    //$msg = "log_id=" . $map_record['log_id'] . ", bytes=" . strlen($map_record['field_mappings']);
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
                        'redcap_event_id' => Yes3::getFirstREDCapEventId(),
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

    $repeating_forms = false;
    foreach ( $form_metadata_structures['form_metadata'] as $form ){

        if ( $form['form_repeating'] ){

            $repeating_forms = true;
            break;
        }
    }

    return Yes3::json_encode_pretty( [
        'project_id' => $module->project_id,
        'is_longitudinal' => \REDCap::isLongitudinal(),
        'repeating_forms' => $repeating_forms,
        'field_index' => $field_metadata_structures['field_index'],
        'field_metadata' => $field_metadata_structures['field_metadata'],
        'field_count' => Yes3::fetchValue($sqlCount, [$module->project_id, \REDCap::getRecordIdField()]),
        'field_autoselect_source' => $field_metadata_structures['field_autoselect_source'],
        'form_index' => $form_metadata_structures['form_index'],
        'form_metadata' => $form_metadata_structures['form_metadata'],
        'event_metadata' => get_event_metadata(),
        'project_event_metadata' => get_project_event_metadata(),
        'default_event_id' => get_first_event_id(),
        //'beta' => ( $module->getProjectSetting('beta')==="Y" ) ? 1 : 0,
        'beta' => 0,
        'user_data_downloads_disabled' => ( $module->getProjectSetting('enable-host-filesystem-exports')==="Y" && $module->getProjectSetting('enable-user-data-downloads')!=="Y" ) ? 1 : 0,
        'host_filesystem_exports_enabled' => ( $module->getProjectSetting('enable-host-filesystem-exports')==="Y" ) ? 1 : 0,
        'host_filesystem_target' => $module->getProjectSetting('export-target-folder')
        //, 'specification_settings' => get_specification_settings()
        //, 'event_abbreviations_settings' => get_event_abbreviation_settings()
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
               'label' => Yes3::truncate(Yes3::printableEscHtmlString($label), MAX_LABEL_LEN)
            ];
         }
      }
      $xx[] = [
         'field_name' => $field['field_name'],
         'field_label' => Yes3::truncate(Yes3::printableEscHtmlString($field['element_label']), MAX_LABEL_LEN),
         'field_type' => $field['element_type'],
         'field_choices' => $choices
      ];
   }

   return $xx;
}

function get_field_metadata_structures(): array
{
    global $module;

    return $module->getFieldMetadataStructures();
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
SELECT e.event_id, e.descrip
FROM redcap_events_metadata e
  INNER JOIN redcap_events_arms a on e.arm_id=a.arm_id
WHERE a.project_id=?
ORDER BY e.day_offset
    ";

    $ee = Yes3::fetchRecords($sql, [$module->project_id]);

    $event_metadata = [];

    foreach ($ee as $e){

        if ( \REDCap::isLongitudinal() ){
            $event_name = \REDCap::getEventNames(true, false, $e['event_id']);
        }
        else {
            $event_name = $e['descrip'];
        }

        $event_metadata[(string)$e['event_id']] = [
            'event_label' => Yes3::escapeHtml($e['descrip']),
            'event_name' => Yes3::escapeHtml($event_name)
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

    return $module->getFormMetadataStructures();
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

function listNamespaceFunctions()
{
    $f = get_defined_functions()['user'];

    sort( $f );

    $registry_list = "";
    
    foreach ( $f as $fn ){

        if ( stripos($fn, strtolower(__NAMESPACE__)) === 0 ) {

            $registry_list .= $fn . "\n";
        }
    }

    Yes3::logDebugMessage( 0, $registry_list, 'yes3 exporter servive functions' );

    return $registry_list;
}

 
?>