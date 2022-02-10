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
                        <i class="far fa-save yes3-fmapr-action-icon"></i>
                    </td>
                    <td>
                        Save all settings on this page.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-undo yes3-fmapr-action-icon"></i>
                    </td>
                    <td>
                        Restore all settings on this page to their stored values (undo).
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
        <span class='yes3-information-em'>Mapping specification:</span> An optional JSON string that defines pre-specified variable names and value categorizations to which REDCap fields and values should be mapped.
            For example, a collection of demographics or recruitment indicators required in a specific structure for NIH reporting. See the README documentation for more information.
        </p>
        <p>
            <span class='yes3-information-em'>A note on repeating forms:</span> Each repeating form must have its own export specification, and must be the only form on that specification.
            Mapping specifications are not allowed for a repeating form export.
        </p>

    </div>

   <div class='yes3-panel-row'>
       Click <a href="javascript:FMAPR.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
   </div>

   <div class='yes3-panel-row'>
       You may leave this help panel open as you use the Yes3 Exporter. Grab it on the top row to drag it out of the way.
   </div>
   
</div>

<div class="container" id="yes3-fmapr-container">

    <div class="row yes3-fmapr-controls">

        <div class="col-md-4 yes3-flex-vcenter-hleft">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Exporter Settings</span>
            </div>

        </div>

        <div class="col-md-4 yes3-flex-vcenter-hcenter">

            <div id="yes3-message"></div>

        </div>

        <div class="col-md-4  yes3-flex-vcenter-hright">

            <i class="far fa-save yes3-fmapr-action-icon" id="yes3-fmapr-save-control" action="saveSettings" title="Save all settings on this page."></i>
            <i class="fas fa-undo yes3-fmapr-action-icon yes3-fmapr-display-when-dirty" action="undoSettings" title="Restore all settings on thios page to their stored values (undo)."></i>
            <i class="fas fa-question yes3-fmapr-action-icon" action="Help_openPanel" title="get some help"></i>

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
                <h1>Export specifications</h1>
                <p>
                    YES3 Exporter can support any number of export specifications.
                    For each export, provide a succinct <em>name</em>, the <em>export layout</em>, the <em>selection criterion</em> and an optional <em>mapping specification</em>.
                </p>
            </div>

            <div class="yes3-information">

                <div class="yes3-float-left">
                    Click <i class="fas fa-question yes3-fmapr-action-icon yes3-fmapr-action-icon-inline" action="Help_openPanel"></i> for more information.
                </div>

                <div class="yes3-float-right">
                    <a href="javascript:FMAPR.Exportspecs_collapseAll()">collapse all</a>
                    <!--i class="fas fa-compress-arrows-alt yes3-fmapr-action-icon yes3-fmapr-action-icon-inline" title="collapse all export specifications" action="Exportspecs_collapseAll"></i-->
                </div>

            </div>

        </div>

        <div class="col-md-6 yes3-fmapr-longitudinal-only yes3-fmapr-setup-settings">

            <div class="yes3-information">
                <h1>Event prefixes</h1>
                <p>
                For horizontal export layouts the YES3 Exporter attaches event prefixes to output column names.
                Below are pre-generated prefixes, which you are welcome to change.
                We suggest that you keep the prefixes as short as you can manage.
                </p>
            </div>

            <table id="yes3-fmapr-setup-events" class="yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-dashboard">

                <thead>

                    <tr>
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

    <table  id="yes3-fmapr-settings-template" data-specnum="9999" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-expanded">

        <thead>

            <tr>
                <th colspan="2" class="yes3-fmapr-export-header">New export definition</th>
                                
                <th class="yes3-fmapr-export-specification yes3-gutter-right-center">
                    <i class="fas fa-expand-alt yes3-fmapr-if-collapsed yes3-fmapr-gutter-icon" action="expand" title="expand the form to see all fields" style="display: none;"></i>
                    <i class="fas fa-compress-alt yes3-fmapr-if-expanded yes3-fmapr-gutter-icon" action="collapse" title="collapse the form to one line"></i>
                </th>

            </tr>

        </thead>

        <tbody class="yes3-fmapr-export-specification">

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Export name:</td>
                
                <td class="yes3-fmapr-export-specification"><input type="text" data-setting="export_name" value="" class="yes3-fmapr-spec-lastrow-item" placeholder="enter an export name"></td>
               
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">
                    <i class="far fa-trash-alt yes3-fmapr-gutter-icon yes3-fmapr-spec-lastrow-item" action="remove" title="remove this export specification"></i>
                </td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Export layout:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">
                    <input type="radio" class="balloon" value="h" data-setting="export_layout" name="yes3-fmapr-export-layout-9999" id="yes3-fmapr-export-layout-9999-h">
                        <label for="yes3-fmapr-export-layout-9999-h" title="Horizontal layout">Horiz</label>&nbsp;
                    <input type="radio" class="balloon" value="v" data-setting="export_layout" name="yes3-fmapr-export-layout-9999" id="yes3-fmapr-export-layout-9999-v">
                        <label for="yes3-fmapr-export-layout-9999-v" title="Vertical layout">Vert</label>&nbsp;
                    <input type="radio" class="balloon" value="r" data-setting="export_layout" name="yes3-fmapr-export-layout-9999" id="yes3-fmapr-export-layout-9999-r">
                        <label for="yes3-fmapr-export-layout-9999-r" title="Repeating Form laout">Repeat</label>&nbsp;
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Selection criterion: field</td>
                
                <td class="yes3-fmapr-export-specification">
                    <input type="text" data-setting="export_criterion_field" value="" placeholder="start typing...">
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>
           
            <tr class="yes3-fmapr-if-expanded yes3-fmapr-select-event">

                <td class="yes3-fmapr-export-specification">Selection criterion: event</td>
                
                <td class="yes3-fmapr-export-specification">
                    <select data-setting="export_criterion_event" class="yes3-fmapr-select-event" placeholder="select an event"></select>
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Selection criterion: value</td>
                
                <td class="yes3-fmapr-export-specification">
                    <input type="text" data-setting="export_criterion_value" placeholder="value">
                </td>
                
                <td class="yes3-fmapr-export-specification yes3-gutter-right-center">&nbsp;</td>
            
            </tr>

            <tr class="yes3-fmapr-if-expanded">

                <td class="yes3-fmapr-export-specification">Specification mappings:</td>
                
                <td class="yes3-fmapr-export-specification yes3-fmapr-mappings-link">
                    <textarea class="yes3-fmapr-hidden" data-setting="export_mappings" id="yes3-fmapr-export-mappings-9999">mighty JSON</textarea>
                    <a href="javascript: FMAPR.editMappingSpecification(9999);" title="view or create the optional mappings JSON string">view/enter</a>
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




