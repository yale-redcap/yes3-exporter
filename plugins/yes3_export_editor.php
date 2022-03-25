<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

$module->getCodeFor("yes3_export_editor", true);

?>

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
                
                <!--tr>
                    <td>
                        <i class="fas fa-print yes3-action-icon"></i>
                    </td>
                    <td>
                        Print the specification.
                    </td>
                </tr-->
                
                <tr>
                    <td>
                        <i class="fas fa-download yes3-action-icon"></i>
                    </td>
                    <td>
                        Download the data dictionary or a csv file based on this specification.
                    </td>
                </tr>
                
            </tbody>
        </table>
   </div>

   <div class='yes3-panel-row'>
       Click <a href="javascript:YES3.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
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

<div id="yes3-fmapr-export-panel" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left">
            YES3 Exporter: Export Options
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.closeExportForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">
            Please indicate what you would like to do:
    </div>

    <div class="yes3-panel-row">

        <input type="radio" class="balloon" value="datadictionary" name="yes3-fmapr-export" id="yes3-fmapr-export-datadictionary" checked />
        <label for="yes3-fmapr-export-datadictionary">Download the data dictionary file</label>

        <br />

        <input type="radio" class="balloon" value="data" name="yes3-fmapr-export" id="yes3-fmapr-export-data" />
        <label for="yes3-fmapr-export-data">Download the data file</label>

        <br />

        <input type="radio" class="balloon" value="zip" name="yes3-fmapr-export" id="yes3-fmapr-export-zip" />
        <label for="yes3-fmapr-export-zip">Download a zip of the data dictionary and data files</label>
        
        <br class="yes3-fmapr-target-filesystem" />

        <input type="radio" class="balloon yes3-fmapr-target-filesystem" value="filesystem" name="yes3-fmapr-export" id="yes3-fmapr-export-filesystem" />
        <label for="yes3-fmapr-export-filesystem" class="yes3-fmapr-target-filesystem">Export the data dictionary and data files to host file system</label>

    </div>

    <div class="yes3-panel-row">

        <div class="yes3-flex-container-evenly-distributed">

            <div class="yes3-flex-vcenter-hleft">
                <input type="button" value="make it so" onClick="FMAPR.exportExecute();" class="yes3-panel-button" />
            </div>

            <div class="yes3-flex-vcenter-hleft yes3-panel-between-the-buttons">
                <div>Hi Mom</div>
            </div>

            <div class="yes3-flex-vcenter-hright">
                <input type="button" value="nah" onClick="FMAPR.closeExportForm();" class="yes3-panel-button" />
            </div>

        </div>
    </div>
</div>

<div id="yes3-fmapr-fieldinsertion-panel" class="yes3-panel yes3-draggable" style="display:none">

   <div class="yes3-panel-header-row">
      <div class="yes3-panel-row-left" id="yes3-fmapr-fieldinsertion-panel-title">
         YES3 Exporter - bulk insertion
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
                    Insertion options:
                </td>
                <td>
                    <div class="yes3-panel-row">
                        <input type="radio" class="balloon" value="default" name="yes3-fmapr-fieldinsertion-option" id="yes3-fmapr-fieldinsertion-option-default" checked />
                        <label for="yes3-fmapr-fieldinsertion-option-default">as above</label>&nbsp;
                        <input type="radio" class="balloon" value="forms" name="yes3-fmapr-fieldinsertion-option" id="yes3-fmapr-fieldinsertion-option-forms" />
                        <label for="yes3-fmapr-fieldinsertion-option-forms">expand to forms</label>
                        <input type="radio" class="balloon" value="fields" name="yes3-fmapr-fieldinsertion-option" id="yes3-fmapr-fieldinsertion-option-fields" />
                        <label for="yes3-fmapr-fieldinsertion-option-fields">expand to fields</label>
                    </div>
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

   <div class="yes3-flex-container-evenly-distributed">

      <div class="yes3-flex-vcenter-hleft">
         <input type="button" value="make it so" onClick="FMAPR.fieldInsertionExecute();" class="yes3-panel-button" />
      </div>

      <div class="yes3-flex-vcenter-hleft yes3-panel-between-the-buttons">
          <div id="yes3-fmapr-bulk-insertion-progress"></div>
      </div>

      <div class="yes3-flex-vcenter-hright">
         <input type="button" value="nah" onClick="FMAPR.closeFieldInsertionForm();" class="yes3-panel-button" />
      </div>

   </div>

</div> <!-- injector -->

<!-- **** USPEC EDITOR **** -->

<div id="yes3-fmapr-uspec-editor" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left" id="yes3-fmapr-uspec-editor-title">
            <span class="yes3-panel-title">Export Upload Specification</span>
            &nbsp;<span class="yes3-panel-subtitle" id="yes3-fmapr-uspec-editor-export-name"></span>
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.uSpecEditor_closeForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class='yes3-panel-row yes3-information'>
        Create or paste the mapping specification blah blah
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">

        <textarea id="_export_uspec_json">foo</textarea>

    </div>

    <div class="yes3-panel-row">
      <div style='float:left'>
         <input type="button" value="done" onClick="FMAPR.uSpecEditor_saveAndClose();" class="yes3-panel-button" title="Close the Export Mappings Editor" />
      </div>
      <div style='float:right'>
         <input type="button" value="cancel" onClick="FMAPR.uSpecEditor_closeForm();" class="yes3-panel-button" title="Discard any changes and close." />
      </div>
   </div>
   
</div>

<!-- **** MAIN CONTENT CONTAINER **** -->

<div class="container" id="yes3-container">

    <div class="row yes3-fmapr-controls">

        <div class="col-md-4 yes3-flex-vcenter-hleft">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Export Editor</span>
            </div>
            
            <!--input type="button" value="new export" class="yes3-fmapr-display-when-clean" onclick="YES3.Functions.newExportSpecification()" /-->

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hcenter">

            <select id="export_uuid" class="yes3-fmapr-when-initialized yes3-flex-vcenter-hcenter" onchange="FMAPR.loadSpecification()">
            </select>

            <div class="yes3-fmapr-when-uninitialized yes3-single-spaced-vcenter">

                Click&nbsp;

                <i class="fas fa-plus-circle yes3-action-icon yes3-action-icon-inline yes3-flex-vcenter-hleft yes3-action-icon-controlpanel yes3-fmapr-display-when-clean yes3-fmapr-when-uninitialized" action="newExportSpecification" title="Add a new export specification."></i>

                &nbsp;to add a new Export Specification.

            </div>

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hright">

            <i class="fas fa-plus yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded yes3-fmapr-display-when-not-repeating" action="addRawREDCapField" title="Add a single REDCap field to the specification."></i>
            <i class="fas fa-plus-square yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded yes3-fmapr-bulk-insert" action="openFieldInsertionForm" title="Add multiple REDCap fields to the specification."></i>
            <i class="fas fa-plus-circle yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-display-when-clean" action="newExportSpecification" title="Add a new export specification."></i>
            <i class="far fa-save yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded" id="yes3-fmapr-save-control" action="saveExportSpecification" title="Save the export specification."></i>
            <i class="fas fa-undo yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded" action="Wayback_openForm" title="Restore the specification from a stored backup."></i>
            <!--i class="fas fa-print yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded yes3-fmapr-display-when-clean" action="printSpecification" title="Print the specification."></i-->
            <i class="fas fa-download yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-loaded yes3-fmapr-display-when-clean" action="openExportForm" title="Download a data dictionary or export data based on this specification."></i>
            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="get some help"></i>

            <label class="yes3-theme-switch yes3-override" for="yes3-theme-checkbox">
                <input type="checkbox" id="yes3-theme-checkbox" />
                <div class="yes3-theme-slider round yes3-override"></div>
            </label>

        </div>

    </div>

    <!-- **** SETTINGS FORM **** -->

    <div class="row yes3-fmapr" id="yes3-fmapr-settings">

        <div class="col-md-6">

            <table  id="yes3-fmapr-settings-1" name="yes3-fmapr-settings" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-expanded">

                <tbody class="yes3-fmapr-export-specification">

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Export name:</td>
                        
                        <td class="yes3-fmapr-export-specification">
                            <input type="text"   name="export_name" data-setting="export_name" value="" class="" placeholder="enter an export name">
                            <input type="hidden" name="export_uuid" data-setting="export_uuid" value="" />
                            <input type="hidden" name="removed"     data-setting="removed"     value="0" />
                        </td>                    
                    </tr>

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Export layout:</td>
                        
                        <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">
                            <input type="radio" class="balloon" value="h" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-h">
                                <label for="yes3-fmapr-export-layout-h" title="Horizontal layout">Horiz</label>&nbsp;
                            <input type="radio" class="balloon" value="v" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-v">
                                <label for="yes3-fmapr-export-layout-v" title="Vertical layout">Vert</label>&nbsp;
                            <input type="radio" class="balloon" value="r" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-r">
                                <label for="yes3-fmapr-export-layout-r" title="Repeating Form layout">Repeat</label>&nbsp;
                        </td>
                    
                    </tr>

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Include:</td>
                        
                        <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">

                            <input type="radio" class="balloon" value="1" name="export_selection" data-setting="export_selection" id="yes3-fmapr-export-selection-1">
                            <label for="yes3-fmapr-export-selection-1" title="Include all records">All records</label>&nbsp;

                            <input type="radio" class="balloon" value="2" name="export_selection" data-setting="export_selection" id="yes3-fmapr-export-selection-2">
                            <label for="yes3-fmapr-export-selection-2" title="Selected records">Selected records</label>&nbsp;

                        </td>
                    
                    </tr>

                    <tr class="yes3-fmapr-if-expanded yes3-fmapr-if-selected yes3-fmapr-skipped-over">

                        <td class="yes3-fmapr-export-specification">Selection criterion: field</td>
                        
                        <td class="yes3-fmapr-export-specification">
                            <input type="text" name="export_criterion_field" data-setting="export_criterion_field" id="export_criterion_field" value="" placeholder="start typing...">
                        </td>                   
                    </tr>
                
                    <tr class="yes3-fmapr-if-expanded yes3-fmapr-if-selected yes3-fmapr-skipped-over yes3-fmapr-select-event">

                        <td class="yes3-fmapr-export-specification">Selection criterion: event</td>
                        
                        <td class="yes3-fmapr-export-specification">
                            <select name="export_criterion_event" data-setting="export_criterion_event" id="export_criterion_event" class="yes3-fmapr-select-event" placeholder="select an event"></select>
                        </td>
                    </tr>

                    <tr class="yes3-fmapr-if-expanded yes3-fmapr-if-selected yes3-fmapr-skipped-over">

                        <td class="yes3-fmapr-export-specification">Selection criterion: value</td>
                        
                        <td class="yes3-fmapr-export-specification">
                            <input type="text" name="export_criterion_value" data-setting="export_criterion_value" placeholder="value">
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- *** SECOND SETTINGS FORM *** -->
        <div class="col-md-6">

            <table  id="yes3-fmapr-settings-2" name="yes3-fmapr-settings" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-expanded">
                
                <tbody class="yes3-fmapr-export-specification">

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Export target:</td>

                        <td class="yes3-fmapr-export-specification yes3-fmapr-export-target">

                            <input type="radio" class="balloon" value="download"   name="export_target" data-setting="export_target" id="yes3-fmapr-export-target-download">
                            <label for="yes3-fmapr-export-target-download" title="Download to client computer">Download</label>&nbsp;

                            <input type="radio" class="balloon" value="filesystem" name="export_target" data-setting="export_target" id="yes3-fmapr-export-target-filesystem">
                            <label for="yes3-fmapr-export-target-filesystem" title="Host file system: file, automount or symlink">Host file system</label>&nbsp;

                        </td>
                    </tr>

                    <tr class="yes3-fmapr-if-expanded yes3-fmapr-target-filesystem-only yes3-fmapr-skipped-over">

                        <td class="yes3-fmapr-export-specification">Folder or automount name:</td>

                        <td class="yes3-fmapr-export-specification">
                            <input type="text" name="export_target_folder" data-setting="export_target_folder" value="" class="" placeholder="enter a folder or automount name">
                        </td>
                    </tr>

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Max label length (optional):</td>

                        <td class="yes3-fmapr-export-specification">
                            <input type="text" name="export_max_label_length" data-setting="export_max_label_length" value="" class="yes3-input-integer yes3-optional" placeholder="max #characters">
                        </td>
                    </tr>

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Max text length (optional):</td>

                        <td class="yes3-fmapr-export-specification">
                            <input type="text" name="export_max_text_length" data-setting="export_max_text_length" value="" class="yes3-input-integer yes3-optional" placeholder="max #characters">
                        </td>
                        </tr>

                    <tr class="yes3-fmapr-if-expanded">

                        <td class="yes3-fmapr-export-specification">Inoffensive text values:</td>

                        <td class="yes3-fmapr-export-specification">
                            <label class="yes3-checkmarkContainer">
                                    <input type="checkbox" name="export_inoffensive_text" data-setting="export_inoffensive_text" value="1" />
                                    <span class="yes3-checkmark"></span> 
                                    Strip offensive chars from all text
                            </label>
                        </td>
                    </tr>

                    <tr class="yes3-fmapr-if-expanded yes3-fmapr-beta">

                        <td class="yes3-fmapr-export-specification">BETA upload specification:</td>

                        <td class="yes3-fmapr-export-specification yes3-fmapr-uspec-link">
                            <textarea class="yes3-fmapr-hidden" name="export_uspec_json" data-setting="export_uspec_json"></textarea>
                            <i class="far fa-map yes3-action-icon yes3-action-icon-gutter" action="uSpecEditor_openForm" title="View, create or paste in the optional upload spec JSON string."></i>
                            <span class="yes3-fmapr-export-uspec-json-length"></span>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

    <!-- **** FIELD MAPPER **** -->

    <div class="row yes3-fmapr yes3-divider" id="yes3-fmapr-wrapper">

        <table class='yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-scrollable yes3-dashboard' id='yes3-fmapr-export-items-table'>
            
            <thead>

                <tr>
                    <th class='yes3-header yes3-3'>Specification data element</th>
                    <th class='yes3-header yes3-3'>REDCap study field</th>
                    <th class='yes3-header yes3-3'><span class='yes3-fmapr-horizontal-only'>REDCap event(s)</span></th>
                    <th class='yes3-header yes3-gutter-right'>&nbsp;</th>
                </tr>
                
            </thead>

            <tbody id='yes3-fmapr-export-items-tbody'>

            </tbody>

        </table>

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



