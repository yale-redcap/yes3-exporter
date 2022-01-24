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
 *       (all non-private properties)
 */
$module->getCodeFor("yes3_fieldmapper", true);

/*
 * bail if EM not set up
 */
if ( !$module->specifications ){
   exit("ERROR: The specifications are missing or fargled.");
}

/**
 * select options for specification
 */
$specOptionHtml = "";
$specNum = 0;
foreach( $module->specifications as $specification ){
    $specName = $specification['name'];
    $specOptionHtml .= "<option value='{$specNum}'>{$specName}</option>";
    $specNum++;
}

?>

<div id="yes3-fmapr-red-pointer">&#9654;</div>

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

            <tr id='yes3-fmapr-fieldinsertion-org-block'>
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
                    <select id="yes3-fmapr-fieldinsertion-form">
                        <option value="">&nbsp;</option>
                    </select>
                </td>
            </tr>

            <tr id='yes3-fmapr-fieldinsertion-event-block' class='yes3-fmapr-fieldinsertion-block'>
                <td>
                    Event(s):
                </td>
                <td>
                    <select id="yes3-fmapr-fieldinsertion-event">
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

<div class="container" id="yes3-fmapr-container">

    <div class="row">

        <div class="col-md-4 yes3-block">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Exporter</span>
            </div>

        </div>

        <div class="col-md-4 yes3-block">

            <select id="yes3-fmapr-specification" onchange="FMAPR.specificationSelect()">
                
                <option value=''>select a specification</option>
                <?= $specOptionHtml ?>

            </select>

        </div>

        <div class="col-md-4 yes3-block">

            <!--input type="button" class="yes3-when-loaded" value="SAVE MAPPINGS" id="yes3-fmapr-save-specification" onclick="FMAPR.saveFieldMappings()"-->

            <i class="fas fa-plus yes3-fmapr-action-icon yes3-fmapr-loaded" action="addRawREDCapField" title="add a single REDCap field to the specification"></i>
            <i class="fas fa-plus-square yes3-fmapr-action-icon yes3-fmapr-loaded" action="openFieldInsertionForm" title="add multiple REDCap fields to the specification"></i>
            <i class="far fa-save yes3-fmapr-action-icon yes3-fmapr-loaded" id="yes3-fmapr-save-control" action="saveFieldMappings" title="save the specification"></i>
            <i class="fas fa-undo yes3-fmapr-action-icon yes3-fmapr-loaded" action="restoreSpecification" title="restore the specification from a stored backup"></i>
            <i class="fas fa-print yes3-fmapr-action-icon yes3-fmapr-loaded yes3-fmapr-clean" action="printSpecification" title="print the specifications"></i>
            <i class="fas fa-download yes3-fmapr-action-icon yes3-fmapr-loaded yes3-fmapr-clean" action="exportData" title="export a csv file based on this specification"></i>
            <i class="fas fa-question yes3-fmapr-action-icon" action="displayHelpPanel" title="get some help"></i>
    
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




