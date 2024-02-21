<?php

namespace Yale\Yes3FieldMapper;

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$module = new Yes3FieldMapper();
use Yale\Yes3\Yes3;
use REDCap;
use HtmlPage;

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

$module->getCodeFor("yes3_export_manager", true);

?>


<!-- HELP PANEL -->

<div id="yes3-help-panel" class="yes3-panel yes3-help-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left" id="yes3-help-panel-title">
            Here's some help
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: YES3.Help_closePanel()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">
    </div>

    <div class='yes3-panel-row yes3-information'>

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
                <span class="yes3-title">YES3</span>&nbsp;<span class="yes3-subtitle">Export&nbsp;Manager</span>
            </div>

        </div>

        <div class="col-lg-4 yes3-flex-vcenter-hcenter">

            <div id="yes3-message"></div>

        </div>

        <div class="col-lg-4 yes3-flex-vcenter-hright">

            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="get some help"></i>

            <i class="fas fa-moon yes3-action-icon yes3-action-icon-controlpanel yes3-light-theme-only" action="Theme_dark" title="Switch to the dark side"></i>
            <i class="fas fa-sun yes3-action-icon yes3-action-icon-controlpanel yes3-dark-theme-only" action="Theme_light" title="Switch to the light theme"></i>

            <img class="yes3-square-logo yes3-logo" alt="YES3 Logo" title="More about YES3..." />

        </div>

    </div>

    <!-- SELECTION BAR -->

    <!--div class="row yes3-controls yes3-look-out-below">

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

    </div-->

    <!-- DATA TABLE -->

    <div class="row">

        <div class="col-lg-12">

            <table class="yes3-dashboard" id="yes3-fmapr-export-table">

                <thead>
                    <tr>
                        <th class="yes3-cw10 yes3-header yes3-halign-center yes3-required-column" title="edit the selected export, or add a new export">edit/add</th>
                        <th class="yes3-cw10 yes3-header yes3-halign-center yes3-required-column" title="download or export the selected export">export</th>
                        <th class="yes3-cw15 yes3-header yes3-halign-left   yes3-required-column" title="export name">name</th>
                        <th class="yes3-cw25 yes3-header yes3-halign-left  " title="export label">label</th>
                        <th class="yes3-cw10 yes3-header yes3-halign-center" title="export layout">layout</th>
                        <th class="yes3-cw10 yes3-header yes3-halign-center" title="export included in daily batch (cron) job">batch</th>
                        <th class="yes3-cw10 yes3-header yes3-halign-center" title="approximate column count">columns</th>
                        <th class="yes3-cw10 yes3-header yes3-halign-center yes3-required-column" title="remove the export (can be restored later)">remove</th>
                    </tr>
                </thead>

                <tbody id="yes3-fmapr-export-tbody"></tbody>

                <tfoot>
                    <tr>
                        <td class="yes3-cw10 yes3-halign-center yes3-required-column" title="click to add a new export specification"><i class="fas fa-plus"></i></td>
                        <td class="yes3-cw10 yes3-halign-center yes3-required-column"></td>
                        <td class="yes3-cw15 yes3-halign-left   yes3-required-column"><em>new export</em></td>
                        <td class="yes3-cw25 yes3-halign-left"></td>
                        <td class="yes3-cw10 yes3-halign-left"></td>
                        <td class="yes3-cw10 yes3-halign-left"></td>
                        <td class="yes3-cw10 yes3-halign-left"></td>
                        <td class="yes3-cw10 yes3-halign-center yes3-required-column" id="yes3-fmapr-visibility-control" title="click to show removed exports, which can then be restored">
                            show xx<br>removed
                        </td>
                    </tr>
                </tfoot>

            </table>

        </div>

    </div>

</div>

    <!-- nl2br(print_r( $module->getExportLogs("0349f75f-7682-43de-a136-23bd475f28ac"), true )) -->

<script>

    (function(){



    })

</script>




