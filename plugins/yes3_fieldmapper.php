<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();
use Yale\Yes3\Yes3;

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

/**
 * getCodeFor will: 
 *   (1) output html tags and code for js and css libraries named [param1]
 *   (2) if [param2] is true, output /html/yes3.html (yes3 dialog panels)
 *   (3) output js code to build the global yes3ModuleProperties object
 */

 $module->getCodeFor("yes3_fieldmapper", true);

?>


<div id="yes3-fmapr-help-panel" class="yes3-panel yes3-draggable" style="display:none">

   <div class="yes3-panel-header-row">
      <div class="yes3-panel-row-left" id="yes3-fmapr-help-panel-title">
         Here's some help
      </div>
      <div class="yes3-panel-row-right">
         <a href="javascript: FMAPR.Help_closePanel()"><i class="fas fa-times fa-2x"></i></a>
      </div>
   </div>

   <div class="yes3-panel-row" style="margin-top: 20px !important">
        <table>
            <tbody>

                <tr>
                    <td>
                        <i class="fas fa-plus yes3-action-icon"></i>
                    </td>
                    <td>
                        Add a single REDCap field to the specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-plus-square yes3-action-icon"></i>
                    </td>
                    <td>
                        Add multiple REDCap fields to the specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="far fa-save yes3-action-icon"></i>
                    </td>
                    <td>
                        Save the specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-undo yes3-action-icon"></i>
                    </td>
                    <td>
                        Restore the specification to a prior version (undo).
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-print yes3-action-icon"></i>
                    </td>
                    <td>
                        Print the specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-download yes3-action-icon"></i>
                    </td>
                    <td>
                        Export a csv file based on this specification.
                    </td>
                </tr>
                
            </tbody>
        </table>
   </div>

   <div class='yes3-panel-row'>
       Click <a href="javascript:FMAPR.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
   </div>

   <div class='yes3-panel-row'>
       You may leave this help panel open as you use the Yes3 Exporter. Grab it on the top row to drag it out of the way.
   </div>
   
</div>

<div id="yes3-fmapr-wayback-panel" class="yes3-panel yes3-draggable" style="display:none">

   <div class="yes3-panel-header-row">
      <div class="yes3-panel-row-left" id="yes3-fmapr-wayback-panel-title">
         Wayback Machine
      </div>
      <div class="yes3-panel-row-right">
         <a href="javascript: FMAPR.Wayback_closeForm()"><i class="fas fa-times fa-2x"></i></a>
      </div>
   </div>

   <div class="yes3-panel-row" style="margin-top: 20px !important">
        <select id="yes3-fmapr-wayback-select" class="yes3-select"></select>
   </div>

   <div class="yes3-panel-row">
      <div style='float:left'>
         <input type="button" value="make it so" onClick="FMAPR.Wayback_Execute();" class="yes3-panel-button" />
      </div>
      <div style='float:right'>
         <input type="button" value="nah" onClick="FMAPR.Wayback_closeForm();" class="yes3-panel-button" />
      </div>
   </div>

</div>


<div id="yes3-fmapr-fieldinsertion-panel" class="yes3-panel yes3-draggable" style="display:none">

   <div class="yes3-panel-header-row">
      <div class="yes3-panel-row-left" id="yes3-fmapr-fieldinsertion-panel-title">
         YES3 FIELD INJECTOR
      </div>
      <div class="yes3-panel-row-right">
         <a href="javascript: FMAPR.closeFieldInsertionForm()"><i class="fas fa-times fa-2x"></i></a>
      </div>
   </div>

   <div class="yes3-panel-row" style="margin-top: 20px !important">

        <table><tbody>

            <tr>
                <td class="yes3-fmapr-fieldinsertion-td-left">
                    Insert fields after:
                </td>
                <td class="yes3-fmapr-fieldinsertion-td-right">
                    <div class="yes3-panel-row" id="yes3-fmapr-fieldinsertion-direction">
                    </div>
                </td>
            </tr>

            <tr id='yes3-fmapr-fieldinsertion-org-block' class="yes3-fmapr-horizontal-only">
                <td>
                    Organize by:
                </td>
                <td>
                    <div class="yes3-panel-row">
                        <input type="radio" class="balloon" value="form" name="yes3-fmapr-fieldinsertion-org" id="yes3-fmapr-fieldinsertion-org-form" checked />
                        <label for="yes3-fmapr-fieldinsertion-org-form">Form</label>&nbsp;
                        <input type="radio" class="balloon" value="event" name="yes3-fmapr-fieldinsertion-org" id="yes3-fmapr-fieldinsertion-org-event" />
                        <label for="yes3-fmapr-fieldinsertion-org-event">Event</label>
                    </div>
                </td>
            </tr>

            <tr id='yes3-fmapr-fieldinsertion-form-block' class='yes3-fmapr-fieldinsertion-block'>
                <td>
                    Form(s):
                </td>
                <td>
                    <select id="yes3-fmapr-fieldinsertion-form" class="yes3-select">
                        <option value="">&nbsp;</option>
                    </select>
                </td>
            </tr>

            <tr id='yes3-fmapr-fieldinsertion-event-block' class='yes3-fmapr-fieldinsertion-block yes3-fmapr-horizontal-only'>
                <td>
                    Event(s):
                </td>
                <td>
                    <select id="yes3-fmapr-fieldinsertion-event" class="yes3-select">
                        <option value="">&nbsp;</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    &nbsp;
                </td>
                <td>
                    <div id="yes3-fmapr-fieldinsertion-counts"></div>
                </td>
            </tr>

        </tbody></table>

   </div>

   <div class="yes3-panel-row">
      <div style='float:left'>
         <input type="button" value="make it so" onClick="FMAPR.fieldInsertionExecute();" class="yes3-panel-button" />
      </div>
      <div style='float:right'>
         <input type="button" value="nah" onClick="FMAPR.closeFieldInsertionForm();" class="yes3-panel-button" />
      </div>
   </div>

</div> <!-- injector -->

<div class="container" id="yes3-container">

    <div class="row yes3-fmapr-controls">

        <div class="col-md-4 yes3-flex-vcenter-hleft">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Exporter</span>
            </div>

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hcenter">

            <select id="export_uuid" onchange="FMAPR.specificationSelect()">
            </select>

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hright">

            <i class="fas fa-plus yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded" action="addRawREDCapField" title="Add a single REDCap field to the specification."></i>
            <i class="fas fa-plus-square yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded" action="openFieldInsertionForm" title="Add multiple REDCap fields to the specification."></i>
            <i class="far fa-save yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded" id="yes3-fmapr-save-control" action="saveFieldMappings" title="Save the specification."></i>
            <i class="fas fa-undo yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded" action="Wayback_openForm" title="Restore the specification from a stored backup."></i>
            <i class="fas fa-print yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded yes3-fmapr-display-when-clean" action="printSpecification" title="Print the specification."></i>
            <i class="fas fa-download yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded yes3-fmapr-display-when-clean" action="exportData" title="Export a csv file based on this specification."></i>
            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="get some help"></i>

            <label class="yes3-theme-switch yes3-override" for="yes3-theme-checkbox">
                <input type="checkbox" id="yes3-theme-checkbox" />
                <div class="yes3-theme-slider round yes3-override"></div>
            </label>

        </div>

    </div>

    <!-- **** FIELD MAPPER **** -->

    <div class="row yes3-fmapr">

        <div class="col-md-12 yes3-divider" id="yes3-fmapr-wrapper"></div>

    </div>

    <div class="row" id="yes3-fmapr-footer">

        <div class="col-md-6">

            <div id="yes3-fmapr-status"></div>

        </div>

        <div class="col-md-6">

            <div id="yes3-message"></div>

        </div>

    </div>

</div> <!-- container -->

<script>

    (function(){



    })

</script>




