<?php

namespace Yale\Yes3FieldMapper;

use Exception;
use REDCap;
use ExternalModules\ExternalModules;

/*
 * Table to hold debug log messages. Must be created by dba, see logDebugMessage() below.
 */
define( 'DEBUG_LOG_TABLE', "ydcclib_debug_messages");

/**
 * 
 * class: Yale\Yes3\Yes3
 * 
 * @package Yale\Yes3
 * @author Peter Charpentier, CRI Web Tools LLC
 */
class Yes3 {

   static function helloWorld() {
      return "hello world!";
   }

    // too bad this logic is private in ExternalModules
    public static function getREDCapProjectId()
    {
        if (defined('PROJECT_ID')) {
            return (int) PROJECT_ID;
        }
        if (isset($_GET['pid'])) {
            return (int) $_GET['pid'];
        }
        return 0;
    }

    // the framework getDAG crashes for longitudinal studies
    // this version accepts ANY event_id and assumes no database issues
    public static function getGroupIdForRecord($recordId, $pid=0){

        if ( !$pid ){

            $pid = self::getREDCapProjectId();
        }

        if ( !$pid ){

            return null;
        }

        return self::fetchValue('select value from redcap_data where project_id = ? and record = ? and field_name = ? limit 1', [$pid, $recordId, '__GROUPID__']);
    }

   public static function query($sql, $parameters = [])
   {    
      return ExternalModules::query($sql, $parameters);
   }

   public static function recordGenerator( $sql, $parameters = [] )
   {
        $resultSet = self::query($sql, $parameters);
    
        while ($row = $resultSet->fetch_assoc()) {

            yield $row;
        }
   }

   public static function fetchRecords($sql, $parameters = [])
   {

      $rows = [];
      $resultSet = self::query($sql, $parameters);
      if ( $resultSet->num_rows > 0 ) {
         while ($row = $resultSet->fetch_assoc()) {
            $rows[] = $row;
         }
      }

      return $rows;
   }

   private static function sql_limit_1( $sql )
   {

      if ( stripos($sql, "LIMIT 1") === false ) {
         return $sql . " LIMIT 1";
      } else {
         return $sql;
      }

   }

   public static function fetchRecord($sql, $parameters = [])
   {

      return self::query(self::sql_limit_1($sql), $parameters)->fetch_assoc();

   }

   public static function fetchValue($sql, $parameters = [])
   {
      return self::query(self::sql_limit_1($sql), $parameters)->fetch_row()[0];
   }

   public static function tableExists($table_name)
   {
      $dbname = self::fetchValue("SELECT DATABASE() AS DB");
      if ( !$dbname ) return false;
      $sql = "SELECT COUNT(*) FROM information_schema.tables"
         ." WHERE table_schema=?"
         ." AND table_name=?"
      ;
      return self::fetchValue($sql, [$dbname, $table_name]);
   }

   public static function json_encode_pretty( $x )
   {
      return json_encode( $x, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT );
   }

   public static function is_json_encodable( $x )
   {
       if ( json_encode( $x )===false ) return false;
       return true;
   }

   public static function is_json_decodable( $s )
   {
       if ( json_decode( $s)===null ) return false;
       return true;
   }

   public static function getFirstREDCapEventId(int $project_id=null)
   {
      if ( !$project_id ){
         $project_id = self::getREDCapProjectId();
      }

      $sql = "SELECT e.event_id
      FROM redcap_events_metadata e
        INNER JOIN redcap_events_arms a ON a.arm_id=e.arm_id
      WHERE a.project_id=?
      ORDER BY e.day_offset, e.event_id
      LIMIT 1";

      return self::fetchValue($sql, [$project_id]);
   }

   public static function getREDCapEventIdForField( string $field_name, int $project_id=null )
   {
      return self::getREDCapEventIdForForm( self::getREDCapFormForField($field_name, $project_id) );
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
    public static function getREDCapEventIdForForm( string $form_name, int $project_id=null )
    {
       if ( !$project_id ){
          $project_id = self::getREDCapProjectId();
       }
 
       if ( REDCap::isLongitudinal() ) {     
          $sql = "SELECT e.event_id
          FROM redcap_events_metadata e
            INNER JOIN redcap_events_arms a ON a.arm_id=e.arm_id
            INNER JOIN redcap_events_forms ef ON ef.form_name=? AND ef.event_id=e.event_id
          WHERE a.project_id=?
          ORDER BY e.day_offset, e.event_id
          LIMIT 1";

          return self::fetchValue($sql, [$form_name, $project_id]) ?? 0;
       }
       
       return self::getFirstREDCapEventId($project_id);
   }

   public static function getEventIdForDescription( int $project_id, string $descrip)
   {
      return (int) self::fetchValue(
         "SELECT e.event_id
         from redcap_events_metadata e
         INNER join redcap_events_arms a on a.arm_id=e.arm_id
         where a.project_id=? and e.descrip=?",
         [$project_id, $descrip]
      );
   }

   public static function getREDCapFormForField( string $field_name, int $project_id=null )
   {
      if ( !$project_id ){
         $project_id = self::getREDCapProjectId();
      }

      $sql = "SELECT m.form_name
      FROM redcap_metadata m
      WHERE m.project_id=? AND m.field_name=?
      LIMIT 1";

      return self::fetchValue($sql, [$project_id, $field_name]);
   }

   public static function getREDCapValue( string $record, string $field_name, int $event_id=null, $instance=1 )
   {
      $project_id = self::getREDCapProjectId();

      if ( !$event_id ) {
         $event_id = self::getREDCapEventIdForField($field_name, $project_id);
      }
      $sql = "
SELECT `value` 
FROM `redcap_data` 
WHERE `project_id`=? AND `event_id`=? AND `record`=? AND `field_name`=? AND ifnull(instance, 1)=? LIMIT 1
";
      return self::fetchValue($sql, [$project_id, $event_id, $record, $event_id, $instance]);
   }

   /**
    * A more friendly record retrieval
    * 
    * function: getREDCapDataForRecord
    * 
    * @param string  $REDCapRecordId
    * @param array   $fields
    * @param int     $event_id
    * @param string  $return_format
    * 
    * @return array  if event_id is specified, an associative array of values [field1=>value1, ...] 
    *                otherwise, a 2-level array [event_id][field1=>value1, ...]
    */
   public static function getREDCapDataForRecord(string $REDCapRecordId, array $fields, int $event_id=0, string $return_format='array'): array
   {
      $params = [

         'project_id'=>self::getREDCapProjectId(),
         'records'=>$REDCapRecordId,
         'return_format'=>$return_format,
         'fields'=>$fields

      ];

      if ( $event_id ){

         $params['events'] = $event_id;
      }

      $data = REDCap::getData( $params );
      
      if ( $event_id ) {
         return $data[$REDCapRecordId][$event_id];
      }

      return $data[$REDCapRecordId];
   }

   public static function isRepeatingInstrument(string $form_name)
   {
        $sql = "SELECT COUNT(*) AS k
                FROM redcap_events_repeat er
                    INNER JOIN redcap_events_metadata em ON em.event_id=er.event_id
                    INNER JOIN redcap_events_arms ea ON ea.arm_id=em.arm_id
                WHERE ea.project_id=? AND er.form_name=?";
        
        return self::fetchValue($sql, [self::getREDCapProjectId(), $form_name]);
   }

   public static function saveREDCapDataForRecord(string $REDCapRecordId, array $x, int $event_id=0): int
   {    
        if ( !$event_id && REDCap::isLongitudinal() ){

            $event_id = self::getFirstREDCapEventId();
        }

        $params = [

            'project_id'=>self::getREDCapProjectId(),
            'records'=>$REDCapRecordId,
            'dataFormat'=>'array',
            'data'=>[
                $REDCapRecordId=>[
                    $event_id => $x
                ]    
            ],
            'overwritebehavior'=>'overwrite',
            'dataLogging'=>TRUE,
            'commitData'=>TRUE
        ];

        $rc = REDCap::saveData( $params );

        if ( !is_array($rc['ids']) ){
            return -1;
        }

        return count($rc['ids']);
    }

   public static function REDCapDateTimeString()
   {
      return strftime("%Y-%m-%d %H:%M");
   }

   public static function timeStampString()
   {
      return strftime("%y%m%d%H%M%S");
   }

    public static function inoffensiveFieldName( $s )
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
    * @return string
    */
    public static function normalized_string( $s )
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
       return preg_replace("/[^a-z0-9_]+/", "", strtolower(str_replace(' ', '_', $s)));
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
    public static function straightQuoter( $s ):string
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
    public static function inoffensiveText( $s, $maxLen=0 ):string
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }
        
        $s = preg_replace('/[\x00-\x1F\x7F]/u', ' ', self::straightQuoter( strip_tags($s)) ); 

        if ( $maxLen ) return self::truncate($s, $maxLen);

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
    public static function alphaNumericString( $s ):string
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
    public static function printableEscHtmlString( $s, $maxLen=0)
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        $s = preg_replace('/[\x00-\x1F\x7F]/u', '', strip_tags($s));
 
        if ( $maxLen ) return self::truncate($s, $maxLen);

        return $s;       
    }

    public static function escapeHtml( $s )
    {
        if ( is_null($s) ){

            return "";
        }

        if ( !strlen($s) ){

            return "";
        }

        return REDCap::escapeHtml($s);
    }

    public static function ellipsis( $s, $len=64 )
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

    public static function truncate( $s, $len=64 )
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

    public static function sql_string($x)
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

    public static function sql_datetime_string($x)
    {
        if (!$x) {
            return "null";
        } else {
            return "'" . strftime("%F %T", strtotime($x)) . "'";
        }
    }

    public static function sql_date_string($x)
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

   public static function sql_timestamp_string()
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
   public static function logDebugMessage($project_id, $msg, $msgcat="") 
   {
      if ( !self::tableExists(DEBUG_LOG_TABLE) ) return false;

      $sql = "INSERT INTO `".DEBUG_LOG_TABLE."` (project_id, debug_message, debug_message_category) VALUES (?,?,?)";

      return self::query($sql, [$project_id, $msg, $msgcat]);
   }

   /**
   * 
   * 
   * function: REDCapAPI
   * 
   * @param mixed $host
   * @param mixed $params
   * @param string $module_prefix
   * @param string $module_page
   * @param bool $noauth
   * 
   * @return string|bool
   */
   public static function REDCapAPI( $host, $params, $module_prefix="", $module_page="", $noauth=false )
   {
      /*
       * The regexp just removes any trailing slash from the provided host.
       */
      $url = preg_replace('^/$^', '', $host) . "/api/";

      /*
       * API endpoint call?
       */
      if ( $module_prefix ){

         $url .= "?type=module&prefix={$module_prefix}&page={$module_page}";

         if ( $noauth ) $url .= "&NOAUTH";
         
      }

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_AUTOREFERER, true);
      curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

      $response = curl_exec($ch);

      $error_message = (curl_errno($ch)) ? curl_error($ch) : "";

      curl_close($ch);

      if ( $error_message ) throw new Exception( "REDCap API error: " . $error_message );

      return $response;
   }

}