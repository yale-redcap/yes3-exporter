<?php

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

$module->getCodeFor("yes3_export_editor", true);

?>

<div id="yes3-fmapr-new-export-form" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">

        <div class="yes3-panel-row-left" id="yes3-help-panel-title">
            New Export
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.NewExport_closePanel()"><i class="fas fa-times fa-2x"></i></a>
        </div>
        
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">
    
        <p class="yes3-panel-subtitle">
            Please provide a name and and layout for the export to be added.
        </p>
        <p>Note that while you can change the name later, you cannot change the layout once the export has been created.</p>
    
        <table class="yes3-settings" id="yes3-fmapr-new-export">

            <tr>
                <td>
                    Export NAME
                </td>

                <td>
                    <input type="text" id="new_export_name" value="new export" class="" placeholder="enter an export name">
                </td>
            </tr>

            <tr>
                <td>
                    Export LAYOUT
                </td>

                <td>

                    <input class="balloon yes3-longitudinal-only" type="radio" class="balloon" value="h" name="new_export_layout" id="yes3-fmapr-new-export-layout-h">
                    <label class="yes3-longitudinal-only" for="yes3-fmapr-new-export-layout-h" title="Horizontal layout (longitudinal studies: one row per record)">Horizontal (longitudinal projects: one row per record)</label>

                    <br class="yes3-longitudinal-only">

                    <input type="radio" class="balloon" value="v" name="new_export_layout" id="yes3-fmapr-new-export-layout-v">
                    <label for="yes3-fmapr-new-export-layout-v" title="Vertical layout (one row per record+event)">Vertical (one row per record+event)</label>

                    <br>

                    <input type="radio" class="balloon" value="r" name="new_export_layout" id="yes3-fmapr-new-export-layout-r">
                    <label for="yes3-fmapr-new-export-layout-r" title="Repeating Form layout (one row per record+event+instance)">Repeating Form (one row per record+event+instance)</label>

                </td>
            </tr>

        </table>

    </div>

    <!--div class="yes3-panel-row">
        <em>Note: you cannot change the export layout once you have added the export.</em>
    </div-->

    <div class="yes3-panel-row">

        <div class="yes3-flex-container-evenly-distributed">

            <div class="yes3-flex-vcenter-hleft">
                <input type="button" onClick="FMAPR.NewExport_execute();" class="yes3-panel-button yes3-button-caption-okay" />
            </div>

            <div class="yes3-flex-vcenter-hright">
                <input type="button" onClick="FMAPR.NewExport_closePanel();" class="yes3-panel-button yes3-button-caption-cancel" />
            </div>

        </div>
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
                        <i class="fas fa-list-alt yes3-action-icon"></i>
                    </td>
                    <td>
                        Add a new export specification.
                    </td>
                </tr>

                <tr>
                    <td>
                        <i class="fas fa-plus yes3-action-icon"></i>
                    </td>
                    <td>
                        Add a REDCap field to the specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-plus-square yes3-action-icon"></i>
                    </td>
                    <td>
                        Add a REDCap form (or all forms) to the specification.
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
                        Download the data dictionary and/or data based on this specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-file-export yes3-action-icon"></i>
                    </td>
                    <td>
                        Export the data and data disctionary to the host file system (if configured; requires super user and IT support).
                    </td>
                </tr>
                
                <tr class="yes3-expanded">
                    <td>
                        <i class="fas fa-angle-double-up yes3-action-icon"></i>
                    </td>
                    <td>
                        Collapse (hide) the top settings section, and expand the lower fields/forms section.
                    </td>
                </tr>
                
                <tr class="yes3-collapsed">
                    <td>
                        <i class="fas fa-angle-double-down yes3-action-icon"></i>
                    </td>
                    <td>
                        Expand the top settings section, and shrink the lower fields/forms section.
                    </td>
                </tr>

                <tr>
                    <td>
                        <i class="fas fa-question yes3-action-icon"></i>
                    </td>
                    <td>
                        Display this Help panel.
                    </td>
                </tr>
                            
                <tr class="yes3-light-theme-only">
                    <td>
                        <i class="fas fa-moon yes3-action-icon"></i>
                    </td>
                    <td>
                        Switch to dark theme.
                    </td>
                </tr>   
                
                <tr class="yes3-dark-theme-only">
                    <td>
                        <i class="fas fa-sun yes3-action-icon"></i>
                    </td>
                    <td>
                        Switch to light theme.
                    </td>
                </tr>   
                
            </tbody>
        </table>
   </div>

    <div class='yes3-panel-row'>
        <span class='yes3-information-em'>Need more help?</span> 
            Click <a href="javascript:YES3.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
    </div>

    <div class='yes3-panel-row'>
        You may leave this help panel open as you use the Yes3 Exporter. Grab it on the top row to drag it out of the way.
    </div>

    <div class='yes3-panel-row yes3-help-panel-got-it'>
        <label class="yes3-checkmarkContainer">
            <input type="checkbox" name="yes3_got_it" onclick="YES3.Help_setGotIt()">
            <span class="yes3-checkmark"></span>Got it! Do not automatically display this panel again.
        </label>
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
         <input type="button" onClick="FMAPR.Wayback_Execute();" class="yes3-panel-button yes3-button-caption-okay" />
      </div>
      <div style='float:right'>
         <input type="button" onClick="FMAPR.Wayback_closeForm();" class="yes3-panel-button yes3-button-caption-cancel" />
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
                <input type="button" onClick="FMAPR.exportExecute();" class="yes3-panel-button yes3-button-caption-okay" />
            </div>

            <div class="yes3-flex-vcenter-hright">
                <input type="button" onClick="FMAPR.closeExportForm();" class="yes3-panel-button yes3-button-caption-cancel" />
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

   <div id="yes3-fmapr-bulkinsert-where" class="yes3-panel-row" style="margin-top: 20px !important">
   </div>

   <div class="yes3-panel-row" style="margin-top: 20px !important">

        <table><tbody>

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

            <tr id='yes3-fmapr-fieldinsertion-event-block' class='yes3-fmapr-fieldinsertion-block yes3-fmapr-horizontal-only-xxx'>
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
                    Insert as:
                </td>
                <td>
                    <div class="yes3-panel-row">
                        <input type="radio" class="balloon" value="default" name="yes3-fmapr-fieldinsertion-option" id="yes3-fmapr-fieldinsertion-option-default" checked />
                        <label for="yes3-fmapr-fieldinsertion-option-default">a single item</label>&nbsp;
                        <input type="radio" class="balloon yes3-fmapr-allforms-only" value="forms" name="yes3-fmapr-fieldinsertion-option" id="yes3-fmapr-fieldinsertion-option-forms" />
                        <label for="yes3-fmapr-fieldinsertion-option-forms" class="yes3-fmapr-allforms-only">individual forms</label>
                        <input type="radio" class="balloon" value="fields" name="yes3-fmapr-fieldinsertion-option" id="yes3-fmapr-fieldinsertion-option-fields" />
                        <label for="yes3-fmapr-fieldinsertion-option-fields">fields</label>
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
         <input type="button" onClick="FMAPR.fieldInsertionExecute();" class="yes3-panel-button yes3-button-caption-okay" />
      </div>

      <div class="yes3-flex-vcenter-hleft yes3-panel-between-the-buttons">
          <div id="yes3-fmapr-bulk-insertion-progress"></div>
      </div>

      <div class="yes3-flex-vcenter-hright">
         <input type="button" onClick="FMAPR.closeFieldInsertionForm();" class="yes3-panel-button yes3-button-caption-cancel" />
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
         <input type="button" value="done" onClick="FMAPR.uSpecEditor_saveAndClose();" class="yes3-panel-button yes3-button-caption-done" title="Close the Export Mappings Editor" />
      </div>
      <div style='float:right'>
         <input type="button" onClick="FMAPR.uSpecEditor_closeForm();" class="yes3-panel-button yes3-button-caption-cancel" title="Discard any changes and close." />
      </div>
   </div>
   
</div>

<!-- **** MAIN CONTENT CONTAINER **** -->

<div class="container" id="yes3-container">

    <div class="row yes3-fmapr-controls">

        <div class="col-md-3 yes3-flex-vcenter-hleft" style="padding-left:0">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Export&nbsp;Editor</span>
            </div>
            
            <!--input type="button" value="new export" class="yes3-display-when-clean" onclick="YES3.Functions.newExportSpecification()" /-->

        </div>

        <div class="col-md-5 yes3-flex-vcenter-hcenter">

            <select id="export_uuid" class="yes3-fmapr-when-initialized yes3-flex-vcenter-hleft" onchange="FMAPR.loadSpecification()">
            </select>

            <i class="fas fa-list-alt yes3-action-icon yes3-flex-vcenter-hleft yes3-action-icon-controlpanel yes3-display-when-clean yes3-designer-only" action="NewExport_openPanel" title="Add a new export specification."></i>

            <div class="yes3-flex-vcenter-hleft">            
                &nbsp;new&nbsp;export
            </div>  

            <!--div class="yes3-fmapr-when-uninitialized yes3-single-spaced-vcenter">

                Click&nbsp;

                <i class="fas fa-list-alt yes3-action-icon yes3-action-icon-inline yes3-flex-vcenter-hleft yes3-action-icon-controlpanel yes3-display-when-clean yes3-fmapr-when-uninitialized" action="NewExport_openPanel" title="Add a new export specification."></i>

                &nbsp;to add a new Export Specification.

            </div-->

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hright" style="padding-right:0">

            <i class="fas fa-plus yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-fmapr-display-when-not-repeating yes3-designer-only yes3-fmapr-settings-okay" action="addRawREDCapField" title="Add a single REDCap field to the export specification."></i>
            
            <i class="fas fa-plus-square yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-fmapr-bulk-insert yes3-designer-only yes3-fmapr-settings-okay" action="openFieldInsertionForm" title="Add a form (or all forms) to the export specification."></i>
            
            <i class="far fa-save yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-designer-only yes3-save-control" id="yes3-fmapr-save-control" action="saveExportSpecification" title="Save the export specification."></i>
            
            <i class="fas fa-undo yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-designer-only" action="Wayback_openForm" title="Restore the specification from a stored backup."></i>
            
            <i class="fas fa-download yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-display-when-clean yes3-exporter-only yes3-fmapr-settings-okay" action="openExportForm" title="Download a data dictionary or export data based on this specification."></i>
            
            <i class="fas fa-file-export yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-display-when-clean yes3-exporter-only yes3-fmapr-settings-okay yes3-fmapr-if-host-filesystem" action="exportToHost" title="Export to host file system (if configured)."></i>

            <i class="fas fa-angle-double-down yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-collapsed yes3-fmapr-settings-okay" action="expandSettingsPanel"   title="Expand the upper settings panel" style="display:none"></i>
            
            <i class="fas fa-angle-double-up   yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-expanded yes3-fmapr-settings-okay"  action="collapseSettingsPanel" title="Collapse the upper settings panel"></i>

            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="Get some help"></i>

            <i class="fas fa-moon yes3-action-icon yes3-action-icon-controlpanel yes3-light-theme-only" action="Theme_dark" title="Switch to the dark side"></i>
            <i class="fas fa-sun yes3-action-icon yes3-action-icon-controlpanel yes3-dark-theme-only" action="Theme_light" title="Switch to the sunny side"></i>

            <img class="yes3-square-logo yes3-logo" alt="YES3 Logo" title="More about YES3..." />

        </div>

    </div>

    <!-- **** SETTINGS FORM **** -->

    <div id="yes3-fmapr-settings" class="yes3-expanded" >

        <div class="row yes3-fmapr yes3-editor">

            <div class="col-md-6">

                <table  id="yes3-fmapr-settings-1" name="yes3-fmapr-settings" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-expanded yes3-fmapr-settings-block">

                    <tbody class="yes3-fmapr-export-specification">

                        <tr>

                            <td class="yes3-fmapr-export-specification">Export name:</td>
                            
                            <td class="yes3-fmapr-export-specification">
                                <input type="text"   name="export_name" data-setting="export_name" value="" class="" placeholder="enter an export name">
                                <input type="hidden" name="export_uuid" data-setting="export_uuid" value="" />
                                <input type="hidden" name="removed"     data-setting="removed"     value="0" />
                            </td>                    
                        </tr>

                        <tr>

                            <td class="yes3-fmapr-export-specification">Export layout (read only):</td>
                            
                            <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">
                                <input disabled  type="radio" class="balloon" value="h" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-h">
                                    <label  for="yes3-fmapr-export-layout-h" title="Horizontal layout">Horiz</label>&nbsp;
                                <input disabled type="radio" class="balloon" value="v" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-v">
                                    <label  for="yes3-fmapr-export-layout-v" title="Vertical layout">Vert</label>&nbsp;
                                <input disabled type="radio" class="balloon" value="r" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-r">
                                    <label for="yes3-fmapr-export-layout-r" title="Repeating Form layout">Repeat</label>&nbsp;
                            </td>                       
                        </tr>

                        <tr>

                            <td class="yes3-fmapr-export-specification">Include:</td>
                            
                            <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">

                                <input type="radio" class="balloon" value="1" name="export_selection" data-setting="export_selection" id="yes3-fmapr-export-selection-1">
                                <label for="yes3-fmapr-export-selection-1" title="Include all records">All records</label>&nbsp;

                                <input type="radio" class="balloon" value="2" name="export_selection" data-setting="export_selection" id="yes3-fmapr-export-selection-2">
                                <label for="yes3-fmapr-export-selection-2" title="Selected records">Selected records</label>&nbsp;

                            </td>               
                        </tr>

                        <tr class="yes3-fmapr-if-selected yes3-fmapr-skipped-over">

                            <td class="yes3-fmapr-export-specification">Selection criterion: field</td>
                            
                            <td class="yes3-fmapr-export-specification">
                                <input type="text" name="export_criterion_field" data-setting="export_criterion_field" id="export_criterion_field" value="" placeholder="start typing...">
                            </td>                   
                        </tr>
                    
                        <tr class="yes3-fmapr-if-selected yes3-fmapr-skipped-over yes3-fmapr-select-event">

                            <td class="yes3-fmapr-export-specification">Selection criterion: event</td>
                            
                            <td class="yes3-fmapr-export-specification">
                                <select name="export_criterion_event" data-setting="export_criterion_event" id="export_criterion_event" class="yes3-fmapr-select-event" placeholder="select an event"></select>
                            </td>
                        </tr>

                        <tr class="yes3-fmapr-if-selected yes3-fmapr-skipped-over">

                            <td class="yes3-fmapr-export-specification">Selection criterion: value</td>
                            
                            <td class="yes3-fmapr-export-specification yes3-input-integer">
                                <input type="text" name="export_criterion_value" data-setting="export_criterion_value" class="yes3-input-integer" placeholder="value">
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- *** SECOND SETTINGS FORM *** -->
            <div class="col-md-6">

                <table  id="yes3-fmapr-settings-2" name="yes3-fmapr-settings" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-expanded yes3-fmapr-settings-block">
                    
                    <tbody class="yes3-fmapr-export-specification">

                        <tr>

                            <td class="yes3-fmapr-export-specification">Export target:</td>

                            <td class="yes3-fmapr-export-specification yes3-fmapr-export-target">

                                <input type="radio" class="balloon" value="download"   name="export_target" data-setting="export_target" id="yes3-fmapr-export-target-download">
                                <label for="yes3-fmapr-export-target-download" title="Download to client computer">Download</label>&nbsp;

                                <input type="radio" class="balloon" value="filesystem" name="export_target" data-setting="export_target" id="yes3-fmapr-export-target-filesystem">
                                <label for="yes3-fmapr-export-target-filesystem" title="Host file system: file, automount or symlink">Host file system</label>&nbsp;

                            </td>
                        </tr>

                        <!--tr class="yes3-fmapr-target-filesystem-only yes3-fmapr-skipped-over">

                            <td class="yes3-fmapr-export-specification">Folder or automount name:</td>

                            <td class="yes3-fmapr-export-specification">
                                <input type="text" name="export_target_folder" data-setting="export_target_folder" value="" class="" placeholder="enter a folder or automount name">
                            </td>
                        </tr-->

                        <tr>

                            <td class="yes3-fmapr-export-specification">Max label length (optional):</td>

                            <td class="yes3-fmapr-export-specification">
                                <input type="text" name="export_max_label_length" data-setting="export_max_label_length" value="" class="yes3-input-integer yes3-optional" placeholder="max #characters">
                            </td>
                        </tr>

                        <tr>

                            <td class="yes3-fmapr-export-specification">Max text length (optional):</td>

                            <td class="yes3-fmapr-export-specification">
                                <input type="text" name="export_max_text_length" data-setting="export_max_text_length" value="" class="yes3-input-integer yes3-optional" placeholder="max #characters">
                            </td>
                            </tr>

                        <!--tr>

                            <td class="yes3-fmapr-export-specification">Inoffensive text values:</td>

                            <td class="yes3-fmapr-export-specification">
                                <label class="yes3-checkmarkContainer">
                                        <input type="checkbox" name="export_inoffensive_text" data-setting="export_inoffensive_text" value="1" />
                                        <span class="yes3-checkmark"></span> 
                                        Strip offensive chars from all text
                                </label>
                            </td>
                        </tr-->

                        <tr class="yes3-fmapr-beta">

                            <td class="yes3-fmapr-export-specification">Upload crosswalk specifications:</td>

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

        <!-- **** FILTERING FORM **** -->

        <div class="row yes3-fmapr yes3-editor">

            <div class="col-md-3">
                    
                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_remove_phi" data-setting="export_remove_phi" value="1" />
                        <span class="yes3-checkmark"></span>Remove tagged identifiers
                    </label>
                </div>

                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_remove_dates" data-setting="export_remove_dates" value="1" />
                        <span class="yes3-checkmark"></span>Remove date/time fields
                    </label>
                </div>
            </div>

            <div class="col-md-3">
 
                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_remove_freetext" data-setting="export_remove_freetext" value="1" />
                        <span class="yes3-checkmark"></span>Remove all freetext fields
                    </label>
                </div>

                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_remove_largetext" data-setting="export_remove_largetext" value="1" />
                        <span class="yes3-checkmark"></span>Remove note/paragraph fields
                    </label>
                </div>
            </div>

            <div class="col-md-3">
                
                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_shift_dates" data-setting="export_shift_dates" value="1" />
                        <span class="yes3-checkmark"></span>Coded (shifted) dates
                    </label>
                </div>
                
                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_hash_recordid" data-setting="export_hash_recordid" value="1" />
                        <span class="yes3-checkmark"></span>Coded (hashed) record id values
                    </label>
                </div> 
                        
            </div>

            <div class="col-md-3">
                
                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_inoffensive_text" data-setting="export_inoffensive_text" value="1" />
                        <span class="yes3-checkmark"></span>Sanitize text values
                    </label>
                </div>
                        
            </div>

        </div>

    </div> <!-- settings container -->

    <!-- **** FIELD MAPPER **** -->

    <div class="row yes3-fmapr yes3-divider yes3-designer-only-xxx yes3-editor" id="yes3-fmapr-wrapper" style="margin-top:10px">

        <table class='yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-scrollable yes3-dashboard' id='yes3-fmapr-export-items-table'>
            
            <thead>

                <tr>
                    <th class='yes3-header yes3-fmapr-row-number'>&nbsp;</th>
                    <th class='yes3-header yes3-3'>Export specification element</th>
                    <th class='yes3-header yes3-3'>REDCap study field</th>
                    <th class='yes3-header yes3-3'><span class='yes3-fmapr-horizontal-only-xxx'>REDCap event(s)</span></th>
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




