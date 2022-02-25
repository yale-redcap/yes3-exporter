<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();
use Yale\Yes3\Yes3;

$module->updateEventPrefixes();

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

/**
 * getCodeFor will: 
 *   (1) output html tags and code for js and css libraries named [param1]
 *   (2) if [param2] is true, output /html/yes3.html (yes3 dialog panels)
 *   (3) output js code to build the global yes3ModuleProperties object
 */

$module->getCodeFor("yes3_fieldmapper_setup", true);

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
                        <i class="far fa-save yes3-action-icon"></i>
                    </td>
                    <td>
                        Save all the settings on this page.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-undo yes3-action-icon"></i>
                    </td>
                    <td>
                        Restore all the settings on this page to their stored values (undo).
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-compress-alt yes3-action-icon"></i>
                    </td>
                    <td>
                        Collapse an export specification to a single row.
                    </td>
                </tr>
               
                <tr>
                    <td>
                        <i class="fas fa-expand-alt yes3-action-icon"></i>
                    </td>
                    <td>
                        Expand a collapsed export specification.
                    </td>
                </tr>
               
                <tr>
                    <td>
                        <i class="far fa-map yes3-action-icon"></i>
                    </td>
                    <td>
                        Edit an optional mapping specification.
                    </td>
                </tr>
               
                <tr>
                    <td>
                        <i class="far fa-trash-alt yes3-action-icon"></i>
                    </td>
                    <td>
                        Remove an export specification.
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>

    <div class='yes3-panel-row yes3-information'>

        <p>
            <span class='yes3-information-em'>Layout:</span> the export layout can be  <em>horizontal</em> (H), <em>vertical</em> (V) or <em>repeating form</em> (R).
            A <em>horizontal</em> layout will have one row per record, and one column for each field and event combination.
            A <em>vertical</em> layout will have one row for each record and event combination, and one column per field.
            A <em>repeating form</em> layout will have one row for each record, event and instance combination, and one column per field.
        </p>
        <p>
            <span class='yes3-information-em'>Selection Criterion (optional):</span>
            By default, all records in the project will be included in the export.
            If you would like a subset of records to be included - for example, all enrolled participants - you may enter a selection criterion
            based on a single field (field name, event, value). If compound logic is required to select records,
            create a suitable REDCap calculated field and use that for the selection criterion.
        </p>
        <p>
            <span class='yes3-information-em'>Mapping specification (optional):</span> 
            An optional JSON string that defines pre-specified variable names and value categorizations to which REDCap fields and values should be mapped.
            For example, a collection of demographics or recruitment indicators required in a specific structure for NIH reporting. See the README documentation for more information.
        </p>
        <p>
            <span class='yes3-information-em'>A note on repeating forms:</span> 
            A repeating form must have its own export specification, and must be the only form on that specification.
        </p>

    </div>

    <div class='yes3-panel-row'>
        <span class='yes3-information-em'>Need more help?</span> 
        Click <a href="javascript:FMAPR.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
    </div>

    <div class='yes3-panel-row'>
        You may leave this help panel open as you use the Yes3 Exporter. 
        Grab it on the top row to drag it out of the way.
    </div>
   
</div>

<div id="yes3-fmapr-mappings-editor" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left" id="yes3-fmapr-mappings-editor-title">
            <span class="yes3-panel-title">Export Mapping Specification</span>
            &nbsp;<span class="yes3-panel-subtitle" id="yes3-fmapr-mappings-editor-export-name"></span>
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.MappingsEditor_closeForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class='yes3-panel-row yes3-information'>
        Create or paste the mapping specification blah blah
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">

    <textarea id="mapping_specification">foo</textarea>

    </div>

    <div class="yes3-panel-row">
      <div style='float:left'>
         <input type="button" value="done" onClick="FMAPR.MappingsEditor_saveAndClose();" class="yes3-panel-button" title="Close the Export Mappings Editor" />
      </div>
      <div style='float:right'>
         <input type="button" value="cancel" onClick="FMAPR.MappingsEditor_closeForm();" class="yes3-panel-button" title="Discard any changes and close." />
      </div>
   </div>
   
</div>

<div class="container" id="yes3-container">

    <div class="row yes3-fmapr-controls">

        <div class="col-md-4 yes3-flex-vcenter-hleft">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Export Settings</span>
            </div>

        </div>

        <div class="col-md-4 yes3-flex-vcenter-hcenter">

            <div id="yes3-message"></div>

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hright">

            <i class="far fa-save yes3-action-icon yes3-action-icon-controlpanel" id="yes3-fmapr-save-control" action="Exportspecifications_saveSettings" title="Save all settings on this page."></i>
            <i class="fas fa-undo yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-display-when-dirty" action="Exportspecifications_undoSettings" title="Restore all settings on this page to their stored values (undo)."></i>
            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="get some help"></i>

            <label class="yes3-theme-switch yes3-override" for="yes3-theme-checkbox">
                <input type="checkbox" id="yes3-theme-checkbox" />
                <div class="yes3-theme-slider round yes3-override"></div>
            </label>

        </div>

    </div>

    <!-- **** FIELD MAPPER SETUP **** -->

    <div class="row yes3-fmapr">

        <div class="col-md-6 yes3-fmapr-setup-settings" id="yes3-fmapr-export-specifications">

            <div class="yes3-information">
                <h1>Export settings</h1>
                <p>
                    YES3 Exporter can support any number of export specifications.
                    For each export, provide a succinct <em>name</em>, the <em>export layout</em>, the <em>selection criterion</em> and an optional <em>mapping specification</em>.
                </p>
                <p>
                    Once you have defined the basic export settings on this page, you must open
                    the <em>YES3 Export Specifications</em> link to identify the REDCap forms, fields and events for each export.
                </p>
            </div>

            <div class="yes3-information yes3-flex-container-evenly-distributed">

                <div class="yes3-flex-vcenter-hleft">
                    Click&nbsp;<i class="fas fa-question yes3-action-icon yes3-action-icon-inline" action="Help_openPanel"></i>&nbsp;for some help.
                </div>

                <div class="yes3-flex-vcenter-hcenter">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" id="yes3-fmapr-show-removed" name="yes3-fmapr-show-removed" onclick="FMAPR.showRemoved()" />
                        <span class="yes3-checkmark"></span> 
                        show removed
                    </label>
                </div>

                <div class="yes3-flex-vcenter-hright">
                    <a href="javascript:FMAPR.Exportspecs_collapseAll()">collapse all</a>
                    <!--i class="fas fa-compress-arrows-alt yes3-action-icon yes3-action-icon-inline" title="collapse all export specifications" action="Exportspecs_collapseAll"></i-->
                </div>

            </div>

        </div>

        <div class="col-md-6 yes3-fmapr-longitudinal-only yes3-fmapr-setup-settings">

            <div class="yes3-information">
                <h1>Event prefixes</h1>
                <p>
                For horizontal export layouts the YES3 Exporter attaches event prefixes to column names.
                Below are pre-generated prefixes, which you can edit.
                Keep the prefixes as short as you can manage.
                </p>
            </div>

            <table id="yes3-fmapr-setup-events" class="yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-dashboard">

                <thead>

                    <tr class='yes3-fmapr-event-prefixes-header'>
                        <th>Event</th>
                        <th>Prefix</th>
                    </tr>

                </thead>

                <tbody>


                </tbody>

            </table>

        </div>

    </div>

</div> <!-- container -->

<div id='yes3-fmapr-settings-templates'>

    <table  id="yes3-fmapr-settings-template" data-specnum="9999" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-spec-lastrow-item yes3-fmapr-expanded">

        <thead>

            <tr>
                <th class="yes3-fmapr-export-header">New export specification</th>

                <th class="yes3-fmapr-export-header"><span class="yes3-if-removed">REMOVED</span></th>
                                
                <th class="yes3-fmapr-export-specification yes3-gutter-right-center">
                    <i class="fas fa-expand-alt yes3-fmapr-if-collapsed yes3-action-icon yes3-action-icon-light yes3-action-icon-gutter" action="ExportSpecificationEditor_expand" title="expand the form to see all fields" style="display: none;"></i>
                    <i class="fas fa-compress-alt yes3-fmapr-if-expanded yes3-action-icon yes3-action-icon-light yes3-action-icon-gutter" action="ExportSpecificationEditor_collapse" title="collapse the form to one line"></i>
                </th>

            </tr>

        </thead>

        <tbody class="yes3-fmapr-export-specification">

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Export name:</td>
                
                <td class="yes3-fmapr-export-specification">
                    <input type="text" data-setting="export_name" value="" class="yes3-fmapr-spec-lastrow-item" placeholder="enter an export name">
                    <input type="hidden" name="export_uuid" data-setting="export_uuid" value="" />
                    <input type="hidden" name="removed" data-setting="removed" value="0" />
                </td>
               
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">
                    <i class="far fa-trash-alt yes3-action-icon yes3-action-icon-gutter yes3-fmapr-spec-lastrow-item" action="removeExportSpecificationToggle" title="remove this export specification"></i>
                </td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Export layout:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">
                    <input type="radio" class="balloon" value="h" data-setting="export_layout" name="yes3-fmapr-export-layout-9999" id="yes3-fmapr-export-layout-9999-h" checked>
                        <label for="yes3-fmapr-export-layout-9999-h" title="Horizontal layout">Horiz</label>&nbsp;
                    <input type="radio" class="balloon" value="v" data-setting="export_layout" name="yes3-fmapr-export-layout-9999" id="yes3-fmapr-export-layout-9999-v">
                        <label for="yes3-fmapr-export-layout-9999-v" title="Vertical layout">Vert</label>&nbsp;
                    <input type="radio" class="balloon" value="r" data-setting="export_layout" name="yes3-fmapr-export-layout-9999" id="yes3-fmapr-export-layout-9999-r">
                        <label for="yes3-fmapr-export-layout-9999-r" title="Repeating Form layout">Repeat</label>&nbsp;
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <!--tr class="yes3-fmapr-if-expanded yes3-fmapr-repeating-only">

                <td class="yes3-fmapr-export-specification">Repeating form name:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">
                    <select data-setting="repeating_form_name" name="repeating-form-name"></select>
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr-->

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Include:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">

                    <input type="radio" class="balloon" value="1" data-setting="export_selection" name="yes3-fmapr-export-selection-9999" id="yes3-fmapr-export-selection-9999-1" checked>
                    <label for="yes3-fmapr-export-selection-9999-1" title="Include all records">All records</label>&nbsp;

                    <input type="radio" class="balloon" value="2" data-setting="export_selection" name="yes3-fmapr-export-selection-9999" id="yes3-fmapr-export-selection-9999-2">
                    <label for="yes3-fmapr-export-selection-9999-2" title="Selected records">Selected records</label>&nbsp;

                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded yes3-fmapr-if-selected yes3-fmapr-skipped-over">

                <td class="yes3-fmapr-export-specification">Selection criterion: field</td>
                
                <td class="yes3-fmapr-export-specification">
                    <input type="text" data-setting="export_criterion_field" value="" placeholder="start typing...">
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>
           
            <tr class="yes3-fmapr-if-expanded yes3-fmapr-if-selected yes3-fmapr-skipped-over yes3-fmapr-select-event">

                <td class="yes3-fmapr-export-specification">Selection criterion: event</td>
                
                <td class="yes3-fmapr-export-specification">
                    <select data-setting="export_criterion_event" class="yes3-fmapr-select-event" placeholder="select an event"></select>
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded yes3-fmapr-if-selected yes3-fmapr-skipped-over">

                <td class="yes3-fmapr-export-specification">Selection criterion: value</td>
                
                <td class="yes3-fmapr-export-specification">
                    <input type="text" data-setting="export_criterion_value" placeholder="value">
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Export target:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-export-target">

                    <input type="radio" class="balloon" value="download" data-setting="export_target" name="yes3-fmapr-export-target-9999" id="yes3-fmapr-export-target-9999-download" checked>
                    <label for="yes3-fmapr-export-target-9999-download" title="Download to client computer">Download</label>&nbsp;

                    <input type="radio" class="balloon" value="filesystem" data-setting="export_target" name="yes3-fmapr-export-target-9999" id="yes3-fmapr-export-target-9999-filesystem">
                    <label for="yes3-fmapr-export-target-9999-filesystem" title="Host file system: file, automount or symlink">Host file system</label>&nbsp;

                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded yes3-fmapr-target-filesystem-only yes3-fmapr-skipped-over">

                <td class="yes3-fmapr-export-specification">Folder or automount name:</td>
                
                <td class="yes3-fmapr-export-specification">
                    <input type="text" data-setting="export_target_folder" value="" class="yes3-fmapr-spec-lastrow-item" placeholder="enter a folder or automount name">
                </td>
               
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">OPTIONAL mapping specification:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-mappings-link">
                    <textarea class="yes3-fmapr-hidden" data-setting="mapping_specification"></textarea>
                    <i class="far fa-map yes3-action-icon yes3-action-icon-gutter" action="MappingsEditor_openForm" title="View, create or paste in the optional mappings JSON string."></i>
                    <span class="yes3-fmapr-export-mappings-length"></span>
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

        </tbody>
    </table>
</div>

<script>

    (function(){



    })

</script>




