<?php

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();
use Yale\Yes3\Yes3;

//$module->emailDailyLog();

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

/**
 * build the export options
 */



/**
 * getCodeFor will: 
 *   (1) output html tags and code for js and css libraries named [param1]
 *   (2) if [param2] is true, output /html/yes3.html (yes3 dialog panels)
 *   (3) output js code to build the global yes3ModuleProperties object
 */

$module->getCodeFor("yes3_export_logs", true);

?>

<!--style>
    table#yes3-fmapr-export-log-table td i:not(.yes3-selected) {
        color: var(--yes3-td-color);
    }
    
    table#yes3-fmapr-export-log-table td i:hover {
        color: turquoise;
    }

    table#yes3-fmapr-export-log-table {
        display: none;
    }
</style-->

<div class="yes3-panel yes3-draggable" id="yes3-record-inspector">

   <div class="yes3-panel-header-row">

      <div class="yes3-panel-row-left">
         YES3 Export Log Record
      </div>

      <div class="yes3-panel-row-right">
         <a href="javascript: YES3.closePanel('yes3-record-inspector')"><i class="fas fa-times fa-2x"></i></a>
      </div>

   </div>

<div class='yes3-panel-row' id='yes3-record-content'>

        <table id="yes3-export-record">
            
            <tr id="yes3-export-project_id"><td>Project ID</td><td></td></tr>
                    
            <tr id="yes3-export-log_id"><td>Log ID</td><td></td></tr>
                
            <tr id="yes3-export-export_uuid"><td>Export UUID</td><td></td></tr>
                
            <tr id="yes3-export-timestamp"><td>Time Stamp</td><td></td></tr>
                
            <tr id="yes3-export-username"><td>User</td><td></td></tr>
                
            <tr id="yes3-export-message"><td>Message</td><td></td></tr>
                
            <tr id="yes3-export-destination"><td>Destination</td><td></td></tr>
                
            <tr id="yes3-export-log_entry_type"><td>Log Entry Type</td><td></td></tr>
                
            <tr id="yes3-export-filename_data"><td>Export filename</td><td></td></tr>
                
            <tr id="yes3-export-filename_data_dictionary"><td>Data Dictionary filename</td><td></td></tr>
                
            <tr id="yes3-export-exported_bytes"><td>Bytes written</td><td></td></tr>
                
            <tr id="yes3-export-exported_items"><td>Cells written</td><td></td></tr>
                
            <tr id="yes3-export-exported_rows"><td>Rows</td><td></td></tr>
                
            <tr id="yes3-export-exported_columns"><td>Columns</td><td></td></tr>

        </table>
    </div>

</div>

<div id="yes3-help-panel" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left" id="yes3-help-panel-title">
            Here's some help
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: YES3.Help_closePanel()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">
        <table>
            <tbody>               
            <tr>
                    <td>
                    <i class="fas fa-download yes3-action-icon yes3-action-icon-inline yes3-nohandler" action="downloadExportLog" title="Download all logs for the selected export specification."></i>
                    </td>
                    <td>
                        Download all the logs for the selected export.
                    </td>
                </tr>

                <tr>
                    <td>
                        <i class="fas fa-eye yes3-nohandler"></i>
                    </td>
                    <td>
                        View the selected export log record.
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>

    <div class='yes3-panel-row yes3-information'>

        <p>
            Every export and download action is permanently logged.
            The export log record includes
            <ul>
            <li>
                    A time stamp
                </li>
                <li>
                    The user who requested the export
                </li>
                <li>
                    The export destination (download or file system)
                </li>
                <li>
                    The export file name, if relevant
                </li>
                <li>
                    The export size (bytes)
                </li>
                <li>
                    Row and column counts
                </li>
            </ul>
        </p>

        <p>
            The display can be filtered by user and/or date range.
        </p>

        <p>
            You may download a spreadsheet containing all of the export log records by clicking the 
            <i class="fas fa-download yes3-action-icon yes3-action-icon-inline yes3-loaded" action="downloadExportLog" title="Download all logs for the selected export specification."></i> 
            icon at the top right.
        </p>

        <p>
            <span class='yes3-information-em'>Need more help?</span> 
            Click <a href="javascript:YES3.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
        </p>

        <div class='yes3-panel-row'>
            You may leave this help panel open as you use the Yes3 Exporter. 
            Grab it on the top row to drag it out of the way.
        </div>

    </div>

</div>

<div class="container" id="yes3-container">


    <!-- CONTROL BAR -->

    <div class="row yes3-controls yes3-flex-container-evenly-distributed yes3-look-out-below">

        <div class="col-lg-4 yes3-flex-vcenter-hleft">

            <div>
                <span class="yes3-title">YES3</span>&nbsp;<span class="yes3-subtitle">Exporter&nbsp;Logs</span>
            </div>

        </div>

        <div class="col-lg-4 yes3-flex-vcenter-hcenter">

            <div id="yes3-message"></div>

        </div>

        <div class="col-lg-4 yes3-flex-vcenter-hright">

            <i class="fas fa-download yes3-action-icon yes3-action-icon-controlpanel yes3-loaded" action="downloadExportLog" title="Download all logs for the selected export specification."></i>

            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="get some help"></i>

            <i class="fas fa-moon yes3-action-icon yes3-action-icon-controlpanel yes3-light-theme-only" action="Theme_dark" title="Switch to the dark side"></i>
            <i class="fas fa-sun yes3-action-icon yes3-action-icon-controlpanel yes3-dark-theme-only" action="Theme_light" title="Switch to the light theme"></i>

            <img class="yes3-square-logo yes3-logo" alt="YES3 Logo" title="More about YES3..." />

        </div>

    </div>

    <!-- SELECTION BAR -->

    <div class="row yes3-controls yes3-look-out-below">

        <div class="col-lg-3">
            <select id="export_uuid" onchange="FMAPR.exportUUIDSelect()"></select>
        </div>

        <div class="col-lg-2">
            <select id="export_username" onchange="FMAPR.exportConstraintSelect()">
                <option value="">all users</option>
            </select>
        </div>

        <div class="col-lg-5">

            <div class="yes3-fmapr-flex-range-container">

                <div>
                    from&nbsp;<input type="date" id="export_date0" onchange="FMAPR.exportConstraintSelect()" title="Start of date range"/>
                </div>

                <div>
                    to&nbsp;<input type="date" id="export_date1" onchange="FMAPR.exportConstraintSelect()" title="End of date range" />
                </div>
            </div>
        </div>

        <div class="col-lg-2 yes3-flex-vcenter-hright" id="yes3-fmapr-row-count"></div>

    </div>

    <!-- DATA TABLE -->

    <div class="row">

        <div class="col-lg-12">

        <table class="yes3-scrollable yes3-dashboard" id="yes3-fmapr-export-log-table">

            <thead>
                <tr>
                    <th class="yes3-header yes3-cw05"><i class="fas fa-eye"></i></th>
                    <th class="yes3-header yes3-cw10">log id</th>
                    <th class="yes3-header yes3-cw15">timestamp</th>
                    <th class="yes3-header yes3-cw15">user</th>
                    <th class="yes3-header yes3-cw35">message</th>
                    <th class="yes3-header yes3-cw20">destination</th>
                </tr>
            </thead>

            <tbody id="yes3-fmapr-export-log-tbody">
            </tbody>

        </table>



        </div>

    </div>

</div>

    <!-- nl2br(print_r( $module->getExportLogs("0349f75f-7682-43de-a136-23bd475f28ac"), true )) -->

<script>

    (function(){



    })

</script>




