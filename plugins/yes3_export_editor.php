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

<!-- NEW EXPORT FORM -->

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

<!-- HELP -->

<div id="yes3-help-panel" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left" id="yes3-help-panel-title">
            Here's some help
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: YES3.Help_closePanel()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class='yes3-panel-row'>
        Access to YES3 Export Editor features is through clicking on 'action icons'.
        Each action icon is listed below, along with the feature it invokes.
    </div>

   <div class="yes3-panel-row" style="margin-top: 20px !important">
        <table>
            <tbody>

                <tr>
                    <td>
                        <i class="fas fa-list-alt yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Add a new export specification.
                    </td>
                </tr>

                <tr>
                    <td>
                        <i class="fas fa-plus yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Add a REDCap field to the export specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-plus-square yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Add a REDCap form (or all forms) to the export specification.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="far fa-save yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Save the export specification (no unsaved changes).
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="far fa-save yes3-action-icon yes3-dirty yes3-nohandler"></i>
                    </td>
                    <td>
                    Save the export specification (unsaved changes detected).
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-undo yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Restore the export specification to a prior version.
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
                        <i class="fas fa-download yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Download the data dictionary and/or data spreadsheet.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <i class="fas fa-file-export yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Export data dictionary and data spreadsheet to the file system (if configured; requires super user and IT support).
                    </td>
                </tr>
                
                <tr class="yes3-expanded--xxx">
                    <td>
                        <i class="fas fa-angle-double-up yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Hide the top settings section, and expand the lower fields/forms section.
                    </td>
                </tr>
                
                <tr class="yes3-collapsed--xxx">
                    <td>
                        <i class="fas fa-angle-double-down yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Show the top settings section, and shrink the lower fields/forms section.
                    </td>
                </tr>

                <tr>
                    <td>
                        <i class="fas fa-question yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Display a Help panel (like this one).
                    </td>
                </tr>
                            
                <tr class="yes3-light-theme-only--xxx">
                    <td>
                        <i class="fas fa-moon yes3-action-icon yes3-nohandler"></i>
                    </td>
                    <td>
                        Switch to dark theme.
                    </td>
                </tr>   
                
                <tr class="yes3-dark-theme-only--xxx">
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

   <div class='yes3-panel-row'>
        <span class='yes3-information-em'>Need more help?</span> 
            Click <a href="javascript:YES3.Help_openReadMe();">here</a> for the Yes3 Exporter documentation.
    </div>

    <!--div class='yes3-panel-row'>
        Click <a href="javascript:YES3.Help_openChangeLog();">here</a> to view the development change log.
    </div-->

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

<!-- FORM INSERTION HELP -->

<div id="yes3-fmapr-form-insertion-help-panel" class="yes3-panel yes3-help-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left">
            Adding Forms to the Export Specification
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.closeHelpFormInsertionForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class="yes3-information ">

    <p class="yes3-fmapr-crossectional-only">
            Use this panel to add one (or all) forms to the export.
        </p>

        <p class="yes3-fmapr-longitudinal-only">
            Use this panel to add one (or all) forms to the export, and also to specify the events for which form data should be exported.
        </p>

        <p class="yes3-fmapr-crossectional-only">
            By default, all forms are pre-selected. 
            Instead of all forms, you may select a specific form to add to the export.
        </p>

        <p class="yes3-fmapr-longitudinal-only">
            By default, all forms and events are pre-selected.
            Instead of all forms and events, you may select event(s) for a specific form, or form(s) for a specific event to add to the export.
        </p>

        <p class="yes3-fmapr-longitudinal-only">
            <span class="yes3-information-em">Selecting events(s) for a specific form:</span>
            To select one (or all) events for a specific form, do the following:
            <ol>
                <li><span class="yes3-information-em-light">Make sure the event selection is "all events"</span> (it will be if this panel was just opened).</li>
                <li><span class="yes3-information-em-light">Select a specific form.</span> This will repopulate the event selector with those events to which the selected form as been assigned.</li>
                <li><span class="yes3-information-em-light">Select the event</span>, or leave as "all events" to add all events for the selected form.</li>
            </ol>
        </p>

        <p class="yes3-fmapr-longitudinal-only">
            <span class="yes3-information-em">Selecting form(s) for a specific event:</span>
            To select one (or all) forms for a specific event, do the following:
            <ol>
                <li><span class="yes3-information-em-light">Make sure the form selection is "all forms"</span> (it will be if this panel was just opened).</li>
                <li><span class="yes3-information-em-light">Select the specific event.</span> This will repopulate the form selector with just those forms assigned to the event.</li>
                <li><span class="yes3-information-em-light">Select the form</span>, or leave as "all forms" to add all forms assigned to the selected event.</li>
            </ol>
        </p>

        <p>
            <span class="yes3-information-em">Insert options: </span>
            You will have up to three options for adding your selection to the Export.
            <ol>
            <li><span class="yes3-information-em-light">As a single item.</span>
                    The selection will be entered into the Export specs as a single form/event item, e.g. "MMSE , 3 month followup".
                </li>
                <li><span class="yes3-information-em-light">As forms.</span>
                    The selection will be entered into the Export specs as one item per form.
                    You might select this option if you intend to (1) remove one or more form items after insertion, which might be easier than adding 
                    each form individually; or (2) you intend to rearrange the form order after insertion.
                </li>
                <li><span class="yes3-information-em-light">As fields.</span>
                    The selection will be entered into the Export specs as one item per field.
                    You might select this option if you intend to remove or rearrange fields after insertion.
                </li>
            </ol>
        </p>

    </div>
</div>

<!-- CRITERION VALUE HELP -->

<div id="yes3-fmapr-criterion-value-help-panel" class="yes3-panel yes3-help-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left">
            Selection criterion: value(s)
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.closeHelpCriterionValueForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class="yes3-information">
        <p>
            This entry is used to select records based on the value of the criterion field.
        </p>
        <p>
            You may enter a single value, in which case only records having this value will be downloaded and/or exported.
        </p>
        <p>
            You may also enter a <em>criterion expression</em> to select based on a range of values.
            <br>Here are examples of valid criterion expression syntax: 
        </p>
        <ul>
            <li><span class="yes3-information-em">1</span> - select records having a value of 1</li>
            <li><span class="yes3-information-em">3,1,4,5,9</span> - select records having a value of 3, 1, 4, 5 or 9</li>
            <li><span class="yes3-information-em">< 10</span> - select records having a non-blank value less than 10</li>
            <li><span class="yes3-information-em"><= 10</span> - select records having a non-blank value less than or equal to 10</li>
            <li><span class="yes3-information-em">> 10</span> - select records having a non-blank value greater than 10</li>
            <li><span class="yes3-information-em">>= 10</span> - select records having a non-blank value greater than or equal to 10</li>
            <li><span class="yes3-information-em"><> 10</span> - select records having a non-blank value not equal to 10</li>
        </ul>

        <p>
            <span class="yes3-information-em-light">string values</span>: if your criterion value is non-numeric, do not enclose it in quotes.
            <br>Use this: <span class="yes3-information-em-light">apple, table, penny</span>,
            <br>not this: <span class="yes3-information-em-light">'apple', 'table', 'penny'</span>.
        </p>
        
        <p>
            <span class="yes3-information-em-light">date values</span>: Dates behave like strings, but you must use the format yyyy-mm-dd.
            For example, to select based on a date being on or after June 5, 1952 use this expression:
            <br><span class="yes3-information-em-light">>= 1952-06-05</span>.
        </p>

        <p>
            <span class="yes3-information-em-light">Criterion field properties</span>: below is a table of REDCap properties for the criterion field you have selected.
            Your selection criterion must be consistent with the field type and, if noted, the possible values that the field can take on.
        </p>

    </div>

    <div class="yes3-panel-bottom-row yes3-panel-row-border-top">

        <table class='yes3-fmapr-panel-table'>
            <tbody>
        
                <tr property="field_name">
                    <td>
                        REDCap field name
                    </td>
                    <td class="propvalue"></td>
                </tr>
       
                <tr property="field_label">
                    <td>
                        REDCap field label
                    </td>
                    <td class="propvalue"></td>
                </tr>
                 
                <tr property="field_type" class="yes3-fmapr-criterion-field-defined">
                    <td>
                        REDCap field type
                    </td>
                    <td class="propvalue"></td>
                </tr>
                 
                <tr property="field_valueset" class="yes3-fmapr-criterion-field-defined yes3-fmapr-nominal">
                    <td>
                        Values
                    </td>
                    <td class="propvalue">
                        <table class="yes3-scrollable yes3-fmapr-help-valueset">
                            <tbody style="height:8rem" >

                            </tbody>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>

    </div>

</div>

<!-- WAYBACK -->

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

<!-- DOWNLOAD -->

<div id="yes3-fmapr-download-panel" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left">
            YES3 Exporter: Download Options
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.closeDownloadForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div class="yes3-panel-row yes3-fmapr-data-download-enabled" style="margin-top: 20px !important">
        Please indicate what you would like to do:
    </div>

    <div class="yes3-panel-row yes3-fmapr-data-download-disabled" style="margin-top: 20px !important">
        Note: User data downloads are disabled for this project. Only filesystem exports are enabled.
    </div>

    <div class="yes3-panel-row">

        <input type="radio" class="balloon" value="datadictionary" name="yes3-fmapr-export" id="yes3-fmapr-export-datadictionary" checked />
        <label for="yes3-fmapr-export-datadictionary">Download the data dictionary file</label>


        <br class="yes3-fmapr-data-download-enabled">

        <input type="radio" class="balloon yes3-fmapr-data-download-enabled" value="data" name="yes3-fmapr-export" id="yes3-fmapr-export-data" />
        <label for="yes3-fmapr-export-data" class="yes3-fmapr-data-download-enabled">Download the data file</label>

        <br class="yes3-fmapr-data-download-enabled">

        <input type="radio" class="balloon yes3-fmapr-data-download-enabled" value="zip" name="yes3-fmapr-export" id="yes3-fmapr-export-zip" />
        <label for="yes3-fmapr-export-zip" class="yes3-fmapr-data-download-enabled">Download a zip of the data dictionary and data files</label>

    </div>

    <div class="yes3-panel-row">

        <div class="yes3-flex-container-evenly-distributed">

            <div class="yes3-flex-vcenter-hleft">
                <input type="button" onClick="FMAPR.downloadExecute();" class="yes3-panel-button yes3-button-caption-okay" />
            </div>

            <div class="yes3-flex-vcenter-hright">
                <input type="button" onClick="FMAPR.closeDownloadForm();" class="yes3-panel-button yes3-button-caption-close" />
            </div>

        </div>
    </div>
</div>

<!-- BULK INSERTION -->

<div id="yes3-fmapr-fieldinsertion-panel" class="yes3-panel yes3-draggable" style="display:none">

    <div class="yes3-panel-header-row">
        <div class="yes3-panel-row-left" id="yes3-fmapr-fieldinsertion-panel-title">
            Add Form(s) to the Export
        </div>
        <div class="yes3-panel-row-right">
            <a href="javascript: FMAPR.closeFieldInsertionForm()"><i class="fas fa-times fa-2x"></i></a>
        </div>
    </div>

    <div id="yes3-fmapr-bulkinsert-where" class="yes3-panel-row" style="margin-top: 20px !important">
    </div>

    <div class="yes3-panel-row" style="margin-top: 20px !important">
        For help on using this panel, click:&nbsp;
        <i class="fas fa-question yes3-action-icon yes3-action-icon-inline" action="Help_formInsertion" title="Guidance for adding forms to the export."></i>
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

        <div class="col-xl-3 yes3-flex-vcenter-hleft" style="padding-left:0">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Exporter&nbsp;Main</span>
            </div>
            
            <!--input type="button" value="new export" class="yes3-display-when-clean" onclick="YES3.Functions.newExportSpecification()" /-->

        </div>

        <div class="col-xl-5 yes3-flex-vcenter-hleft">

            <select id="export_uuid" class="yes3-fmapr-when-initialized yes3-flex-vcenter-hleft" onchange="FMAPR.exportUUIDOnSelect()">
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

        <div class="col-xl-4" style="padding-right:0">

            <div class="yes3-flex-vcenter-hright">

            <i class="fas fa-plus yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-designer-only yes3-fmapr-settings-okay" action="addRawREDCapField" title="Add a single REDCap field to the export specification."></i>
            
            <i class="fas fa-plus-square yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-fmapr-bulk-insert yes3-designer-only yes3-fmapr-settings-okay" action="openFieldInsertionForm" title="Add a form (or all forms) to the export specification."></i>
            
            <i class="far fa-save yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-designer-only yes3-save-control" id="yes3-fmapr-save-control" action="saveExportSpecification" title="Save the export specification."></i>
            
            <i class="fas fa-undo yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-designer-only" action="Wayback_openForm" title="Restore the specification from a stored backup."></i>
            
            <i class="fas fa-download yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-display-when-clean yes3-exporter-only yes3-fmapr-settings-okay" action="openDownloadForm" title="Download the data dictionary and/or data."></i>
            
            <i class="fas fa-file-export yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-display-when-clean yes3-exporter-only yes3-fmapr-settings-okay  yes3-export-to-host-filesystem-enabled" action="exportToHost" title="Export to host file system."></i>

            <i class="fas fa-angle-double-down yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-collapsed yes3-fmapr-settings-okay" action="expandSettingsPanel"   title="Expand the upper settings panel" style="display:none"></i>
            
            <i class="fas fa-angle-double-up   yes3-action-icon yes3-action-icon-controlpanel yes3-loaded yes3-expanded yes3-fmapr-settings-okay"  action="collapseSettingsPanel" title="Collapse the upper settings panel"></i>

            <i class="fas fa-question yes3-action-icon yes3-action-icon-controlpanel" action="Help_openPanel" title="Get some help"></i>

            <i class="fas fa-moon yes3-action-icon yes3-action-icon-controlpanel yes3-light-theme-only" action="Theme_dark" title="Switch to the dark side"></i>
            <i class="fas fa-sun yes3-action-icon yes3-action-icon-controlpanel yes3-dark-theme-only" action="Theme_light" title="Switch to the sunny side"></i>

            <img class="yes3-square-logo yes3-logo" alt="YES3 Logo" title="More about YES3..." />

            </div>

        </div>

    </div>

    <!-- **** SETTINGS FORM **** -->

    <div id="yes3-fmapr-settings" class="yes3-expanded" >

        <div class="row yes3-fmapr yes3-editor">

            <div class="col-xl-6">

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

                            <td class="yes3-fmapr-export-specification">Selection criterion: value(s)</td>
                            
                            <td class="yes3-fmapr-export-specification yes3-input-integer">
                                <input type="text" name="export_criterion_value" data-setting="export_criterion_value" class="yes3-input-integer" placeholder="value">
                                <i class="fas fa-question yes3-action-icon yes3-action-icon-inline" action="Help_criterionValue" title="Guidance for entering the criterion value expression."></i>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- *** SECOND SETTINGS FORM *** -->
            <div class="col-xl-6">

                <table  id="yes3-fmapr-settings-2" name="yes3-fmapr-settings" class="yes3-fmapr yes3-fmapr-item yes3-dashboard yes3-fmapr-expanded yes3-fmapr-settings-block">
                    
                    <tbody class="yes3-fmapr-export-specification">

                        <tr>
                            <td class="yes3-fmapr-export-specification">Export layout:</td>
                            
                            <td class="yes3-fmapr-export-specification yes3-fmapr-layout-options">
                                <!-- input control is hidden from view -->
                                <div style="display:none" class="hidden">
                                    <input disabled  type="radio" class="balloon" value="h" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-h">
                                        <label  for="yes3-fmapr-export-layout-h" title="Horizontal layout">Horiz</label>&nbsp;
                                    <input disabled type="radio" class="balloon" value="v" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-v">
                                        <label  for="yes3-fmapr-export-layout-v" title="Vertical layout">Vert</label>&nbsp;
                                    <input disabled type="radio" class="balloon" value="r" name="export_layout" data-setting="export_layout" id="yes3-fmapr-export-layout-r">
                                        <label for="yes3-fmapr-export-layout-r" title="Repeating Form layout">Repeat</label>&nbsp;
                                </div>
                                <div class="yes3-fmapr-export-layout-text"></div>
                            </td>                       
                        </tr>

                        <!--tr>

                            <td class="yes3-fmapr-export-specification">Export target:</td>

                            <td class="yes3-fmapr-export-specification yes3-fmapr-export-target">

                                <input type="radio" class="balloon" value="download"   name="export_target" data-setting="export_target" id="yes3-fmapr-export-target-download">
                                <label for="yes3-fmapr-export-target-download" title="Download to client computer">Download</label>&nbsp;

                                <input type="radio" class="balloon" value="filesystem" name="export_target" data-setting="export_target" id="yes3-fmapr-export-target-filesystem">
                                <label for="yes3-fmapr-export-target-filesystem" title="Host file system: file, automount or symlink">Host file system</label>&nbsp;

                            </td>
                        </tr-->

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

            <div class="col-xl-3">
                    
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

            <div class="col-xl-3">
 
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

            <div class="col-xl-3">
                
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

            <div class="col-xl-3">
                
            <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block">
                    <label class="yes3-checkmarkContainer">
                        <input type="checkbox" name="export_inoffensive_text" data-setting="export_inoffensive_text" value="1" />
                        <span class="yes3-checkmark"></span>Sanitize text values
                    </label>
                </div>
                
                <div class="yes3-fmapr-filter-option yes3-fmapr-settings-block yes3-dashboard-td">
                    Note: Labels are always sanitized.
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

        <div class="col-xl-6">

            <div id="yes3-fmapr-status"></div>

        </div>

        <div class="col-xl-6">

            <div id="yes3-message"></div>

        </div>

    </div>

</div> <!-- container -->

<script>

    (function(){



    })

</script>




