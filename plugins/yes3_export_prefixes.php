<?php

namespace Yale\Yes3FieldMapper;

use HtmlPage;

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$module = new Yes3FieldMapper();

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

/**
 * getCodeFor will: 
 *   (1) output html tags and code for js and css libraries named [param1]
 *   (2) if [param2] is true, output /html/yes3.html (yes3 dialog panels)
 *   (3) output js code to build the global yes3ModuleProperties object
 */

$module->getCodeFor("yes3_export_prefixes", true);

?>

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
        <table>
            <tbody>  

                <tr>
                    <td>
                        <i class="far fa-save yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Save the export event prefixes.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-undo yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Restore all the settings on this page to their stored values (undo).
                    </td>
                </tr>   

                <tr>
                    <td>
                        <i class="fas fa-question yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Display this Help panel.
                    </td>
                </tr>
                
                <tr class="yes3-light-theme-only">
                    <td>
                        <i class="fas fa-moon yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Switch to dark theme.
                    </td>
                </tr>   
                
                <tr class="yes3-dark-theme-only">
                    <td>
                        <i class="fas fa-sun yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Switch to light theme.
                    </td>
                </tr>   

            </tbody>
        </table>
    </div>

    <div class='yes3-panel-row yes3-information'>

        <p>
            <span class='yes3-information-em'>Export Event Prefix:</span>
            The YES3 Exporter supports a 'horizontal layout' for longitudinal projects,
            in which fields for forms that are placed on multiple events are all 
            included on the same row.
        </p><p> 
            To make this possible, the field names are altered by prefixing them
            with the event abbreviations displayed on this page. The export field name
            will have the form 
        </p><p>
            <pre>[event prefix]_[REDCap field name]</pre>
        </p><p>
            You should inspect and edit the default prefixes, for both brevity and clarity.
        </p>
        <p>
            <span class='yes3-information-em'>Field names:</span>
            As you design a project, you should be aware of any field name length limits 
            imposed by the statistical package(s) that will process the exported data.
        </p>

        <p>
        <span class='yes3-information-em'>Need more help?</span> 
        Click <a href="javascript:YES3.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.            
        </p>

    </div>

    <div class='yes3-panel-row'>
        You may leave this help panel open as you use the Yes3 Exporter. 
        Grab it on the top row to drag it out of the way.
    </div>
   
</div>

<div class="container" id="yes3-container">

    <div class="row yes3-fmapr-controls">

        <div class="col-lg-6 yes3-flex-vcenter-hleft">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Exporter&nbsp;Event&nbsp;Prefixes</span>
            </div>

        </div>

        <div class="col-lg-3 yes3-flex-vcenter-hcenter">

            <div id="yes3-message"></div>

        </div>

        <div class="col-lg-3  yes3-flex-vcenter-hright">

            <i class="far fa-save yes3-action-icon yes3-action-icon-controlpanel yes3-designer-only yes3-save-control" id="yes3-fmapr-save-control" action="Exportspecifications_saveSettings" title="Save all settings on this page."></i>
            <i class="fas fa-undo yes3-action-icon yes3-action-icon-controlpanel yes3-fmapr-display-when-dirty yes3-designer-only" action="Exportspecifications_undoSettings" title="Restore all settings on this page to their stored values (undo)."></i>
            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="get some help"></i>

            <i class="fas fa-moon yes3-action-icon yes3-action-icon-controlpanel yes3-light-theme-only" action="Theme_dark" title="Switch to the dark side"></i>
            <i class="fas fa-sun yes3-action-icon yes3-action-icon-controlpanel yes3-dark-theme-only" action="Theme_light" title="Switch to the sunny side"></i>

            <img class="yes3-square-logo yes3-logo" alt="YES3 Logo" title="More about YES3..." />

        </div>

    </div>

    <!-- **** FIELD MAPPER SETUP **** -->

    <div class="row yes3-fmapr">

        <div class="col-lg-2">&nbsp;</div>

        <div class="col-lg-8 yes3-fmapr-longitudinal-only yes3-fmapr-setup-settings">

            <div class="yes3-information">
                <h1>Event prefixes</h1>
                <p>
                For horizontal export layouts the YES3 Exporter attaches event prefixes to column names.
                Keep the prefixes as short as you can manage. 
                <i class="fas fa-question yes3-action-icon yes3-action-icon-inline" action="Help_openPanel" title="Click for more information on event prefixes and REDCap field name considerations."></i>
                </p>
                <p>
                    Click <a href="javascript:FMAPR.restoreToDefaultValues()">here</a> to restore your event prefixes to their default values.
                </p>
            </div>

            <table id="yes3-fmapr-setup-events" class="yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-dashboard yes3-editor">

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

        <div class="col-lg-2">&nbsp;</div>

    </div>

</div> <!-- container -->


<script>

    (function(){



    })

</script>




