FMAPR.insertionRowId = "";

FMAPR.selectionRowId = "";

FMAPR.rowCount = 0;

FMAPR.dashboard_option = "settings";

FMAPR.reloadParms = {
    "export_uuid": "",
    "wayback": false
}

FMAPR.constrainedAutocompleteSource = [];

FMAPR.tooltips = {

    "event_select": "After a REDCap field or form is selected, this drop-down will include all the REDCap events assigned to it.",
    "row_selector": "Click to select single row; crtl-click to select multiple rows; shift-click to select range; right-click for cut/paste/delete menu",
    "row_editor": "Click to edit this export item.",
    "row_trashcan": "Click to remove this export item."

}

FMAPR.rapidEntryFormRowId = "yes3-fmapr-new-field-form";

/**
 * YES3 FUNCTIONS FOR FMAPR
 * 
 * These functions are called via the YES3 'action icon' mechanism,
 * so they are registered in the YES3 namespace.
 * 
 * Invocation is through Function.prototype.call(),
 * which supplies a 'this' value that functions can use to determine context.
 * 
 * Be sure to use the full YES3.Functions namespace
 */
YES3.Functions.Help_formInsertion = function()
{
    let thePanel = YES3.openPanel("yes3-fmapr-form-insertion-help-panel", true);

    if ( YES3.moduleProperties.isLongitudinal ){
        thePanel.find(".yes3-fmapr-longitudinal-only").show();
        thePanel.find(".yes3-fmapr-crossectional-only").hide(); 
    }
    else {
        thePanel.find(".yes3-fmapr-longitudinal-only").hide();
        thePanel.find(".yes3-fmapr-crossectional-only").show(); 
    }
}

YES3.Functions.Help_criterionValue = function()
{
    let thePanel = YES3.openPanel("yes3-fmapr-criterion-value-help-panel", true);
    let export_criterion_field = $('input#export_criterion_field').val() || "";
    let export_criterion_value = $('input#export_criterion_value').val() || "";
    let m = {};
    let field_type = "";
    let field_valueset_list = "";

    if ( !export_criterion_field.length ){

        thePanel.find("td[property=field_name]").html("(no field selected)");
        thePanel.find("tr.yes3-fmapr-criterion-field-defined").hide();
    }
    else {
        m = FMAPR.project.field_metadata[FMAPR.project.field_index[export_criterion_field]];
        field_type = m.field_type;
        if ( m.field_validation ){

            field_type += " (" + m.field_validation + ")";
        }
        thePanel.find("tr[property=field_name] td.propvalue").html(export_criterion_field);       
        thePanel.find("tr[property=field_type] td.propvalue").html(field_type);
        thePanel.find("tr[property=field_label] td.propvalue").html(m.field_label);
        thePanel.find("tr.yes3-fmapr-criterion-field-defined").show();
        
        field_valueset_list = FMAPR.valueSetList( m.field_type, m.field_valueset );

        if ( field_valueset_list.length ){

            thePanel.find('tr[property=field_valueset]')
                .show()
                .find("td.propvalue table tbody").html(field_valueset_list)
            ;
        }
        else {

            thePanel.find('tr[property=field_valueset]').hide();
        }
    }
}

FMAPR.closeHelpCriterionValueForm = function()
{
    YES3.closePanel("yes3-fmapr-criterion-value-help-panel");
}

FMAPR.closeHelpFormInsertionForm = function()
{
    YES3.closePanel("yes3-fmapr-form-insertion-help-panel");
}

FMAPR.valueSetList = function( field_type, field_valueset )
{
    if ( field_type === "yesno" ){

        return "<tr><td>1</td><td>Yes</td></tr><tr><td>0</td><td>No</td></tr>";
    }

    if ( field_type === "truefalse" ){

        return "<tr><td>1</td><td>True</td></tr><tr><td>0</td><td>False</td></tr>";
    }

    if ( YES3.isEmptyArray(field_valueset) ){

        return "";
    }

    let s = "";

    for (let i=0; i<field_valueset.length; i++ ){

        s += `<tr><td>${field_valueset[i].value}</td><td>${field_valueset[i].label}</td></tr>`;
    }

    return s;
}

YES3.Functions.expandSettingsPanel = function()
{
    YES3.contentExpanded = true;
    $("div.yes3-expanded").show();
    YES3.displayActionIcons();
    FMAPR.displayActionIcons();
    $(window).trigger("resize");
}
 
YES3.Functions.collapseSettingsPanel = function()
{
    YES3.contentExpanded = false;
    $("div.yes3-expanded").hide();
    YES3.displayActionIcons();
    FMAPR.displayActionIcons();
    $(window).trigger("resize");
}

YES3.Functions.NewExport_openPanel = function()
{
    YES3.openPanel("yes3-fmapr-new-export-form");

    if ( YES3.moduleProperties.isLongitudinal ){

        //$("input#yes3-fmapr-new-export-layout-h").prop("checked", true);
    }
    else {

        $("table#yes3-fmapr-new-export .yes3-longitudinal-only").hide();
        //$("input#yes3-fmapr-new-export-layout-v").prop("checked", true);
    }
}

FMAPR.NewExport_closePanel = function()
{
    YES3.closePanel("yes3-fmapr-new-export-form");
}

FMAPR.exportNameAlreadyExists = function(export_name)
{
    let dupes = 0;

    $('select#export_uuid option').each(function(){

        if ( $(this).text().toLowerCase() === export_name.toLowerCase() ){

            dupes++;
            return false;
        }
    })

    return dupes;  
}

FMAPR.NewExport_execute = function()
{
    let new_export_uuid = YES3.uuidv4();
    let new_export_name = $("input#new_export_name").val();
    let new_export_layout = $("input[type=radio][name=new_export_layout]:checked").val();

    if ( !new_export_name || !new_export_layout ){

        YES3.hello("Please enter both the export name and the export layout.");
        return false;
    }

    let dupes = 0;

    $('select#export_uuid option').each(function(){

        if ( $(this).text().toLowerCase() === new_export_name.toLowerCase() ){

            dupes++;
            return false;
        }
    })

    if ( FMAPR.exportNameAlreadyExists(new_export_name) ){

        YES3.hello(`No can do: an export named '${new_export_name}' aleady exists.`);
        return false;
    }
    
    FMAPR.reloadParms.export_uuid = new_export_uuid

    /**
     * Note that the same callback function is shared by 
     * saveExportSpecification() and newExportSpecification().
     * 
     * This callback will load the specification identified in FMAPR.reloadParms,
     * and perform the required UI prep.
     */
    YES3.requestService( 
        {
            "request": "addExportSpecification",
            "export_uuid": new_export_uuid,
            "export_name": new_export_name,
            "export_layout": new_export_layout
        }, 
        FMAPR.saveExportSpecificationCallback, 
        false 
    );

    FMAPR.NewExport_closePanel();
}

YES3.Functions.newExportSpecification = function() 
{
    YES3.YesNo("Would you like to add a new Export Specification?", FMAPR.newExportSpecificationExecute );
}

FMAPR.newExportSpecificationExecute = function()
{
    let new_export_uuid = YES3.uuidv4();
    
    FMAPR.reloadParms.export_uuid = new_export_uuid

    /**
     * Note that the same callback function is shared by 
     * saveExportSpecification() and newExportSpecification().
     * 
     * This callback will load the specification identified in FMAPR.reloadParms,
     * and perform the required UI prep.
     */
    YES3.requestService( 
        {
            "request": "addExportSpecification",
            "export_uuid": new_export_uuid
        }, 
        FMAPR.saveExportSpecificationCallback, 
        false 
    );
}

YES3.Functions.uSpecEditor_openForm = function()
{
    //console.log('uSpecEditor_openForm' this);

    let editor = FMAPR.uSpecEditor();

    let export_name = $("input[name=export_name]").val();

    let export_uspec_json = $("textarea[name=export_uspec_json]").val();

    editor.find("textarea").val( export_uspec_json );

    editor.find("span#yes3-fmapr-uspec-editor-export-name").html(export_name);

    YES3.openPanel("yes3-fmapr-uspec-editor");
}
 
YES3.Functions.openDownloadForm = function()
{
    let thePanel = YES3.openPanel("yes3-fmapr-download-panel");

    if ( FMAPR.project.user_data_downloads_disabled ){

        thePanel.find(".yes3-fmapr-data-download-enabled").hide();
        thePanel.find(".yes3-fmapr-data-download-disabled").show();
    }
    else {

        thePanel.find(".yes3-fmapr-data-download-enabled").show();
        thePanel.find(".yes3-fmapr-data-download-disabled").hide();
    }
}

FMAPR.closeDownloadForm = function()
{
    YES3.closePanel('yes3-fmapr-download-panel');
}

/**
 * sets: FMAPR.insertionRowId
 * 
 * @param {*} rowId 
 * @param {*} field_name 
 * @param {*} event_name
 */
YES3.Functions.openFieldInsertionForm_lagacy = function(rowId, field_name, event_name)
{
    rowId = rowId || "";
    field_name = field_name || "";
    event_name = event_name || "";
    
    let thePanel = $("div#yes3-fmapr-fieldinsertion-panel");
    let theParent = thePanel.parent();
    let theRow = null;

    // clear the progress bar
    $("div#yes3-fmapr-bulk-insertion-progress").css("width", 0);

    /**
     * if rowId is not provided and if only one row is selected, make that the insertion point
     */

    /**
     * if called with no params, set up for append
     */
    if ( !rowId.length ){

        theRow = $('tr.yes3-fmapr-data-element').last();

        if ( theRow ){

            FMAPR.insertionRowId = theRow.attr('id');
            field_name = theRow.find('input.yes3-fmapr-input-element').first().val();
            let theEvent = theRow.find('select.yes3-fmapr-event-select').first();
            event_name = theEvent.find("option:selected").text();
            FMAPR.scrollExportItemsTableToBottom();
        }
        else {

            FMAPR.insertionRowId = "";
        }
    }
    else {

        FMAPR.insertionRowId = rowId;
        theRow = $(`tr#${rowId}`);
    }

    /**
     * If no 'insert after' element row is found, 
     * this is the first element and so insertion panel will be centered.
     * YES3.openPanel will center if (x, y) === (0, 0)
     */

    let x = 0;
    let y = 0;

    if ( theRow.length ) {

        x = theRow.offset().left - theParent.offset().left;
        y = theRow.offset().top - theParent.offset().top;

        //YES3.showRedPointer( theRow );

        if ( y > $(window).innerHeight()/2 ) {
            y = y - thePanel.outerHeight();
        }
        else {
            y = y + theRow.outerHeight();
        }

        YES3.contextMenuClose(null, true);
    }

    YES3.openPanel("yes3-fmapr-fieldinsertion-panel", false, x, y);

    FMAPR.fieldInsertionFormReady(field_name, event_name);
}

YES3.Functions.openFieldInsertionForm = function()
{
    YES3.openPanel("yes3-fmapr-fieldinsertion-panel", false);

    FMAPR.fieldInsertionFormReady();
}


FMAPR.uSpecEditor = function()
{
    return $("div#yes3-fmapr-uspec-editor");
}

FMAPR.uSpecEditor_closeForm = function()
{
    FMAPR.uSpecEditor().find("textarea").val("");
    YES3.closePanel("yes3-fmapr-uspec-editor");
}

FMAPR.uSpecEditor_saveAndClose = function()
{
    let editor = FMAPR.uSpecEditor();

    let exportSettingsContainer = FMAPR.getExportSettingsContainer();

    exportSettingsContainer.find('textarea[name=export_uspec_json]').val( editor.find("textarea").val() );

    FMAPR.validateAndReportExportUspecJson();

    FMAPR.markAsDirty();
    
    FMAPR.uSpecEditor_closeForm();
}


FMAPR.closeFieldInsertionForm = function()
{
    YES3.closePanel('yes3-fmapr-fieldinsertion-panel');
    YES3.hideRedPointer();
}

FMAPR.countNewFieldRows = function()
{
    return $("tr.yes3-fmapr-redcap-field.yes3-fmapr-new-field").length;
}

/**
 * DEPRECATED Insert a 'new field' row if none is present AND there isn't a spec error.
 * As a perhaps inefficient precaution, any 
 */
 FMAPR.ensureNewFieldRowAtEnd = function()
 {
     return true;
 
     // make sure constraints applied before row is inserted
     if ( FMAPR.export_specification.export_layout==="r" ){
 
         FMAPR.setRepeatLayoutConstraints();
     }    
     
     // get rid of any new field row that has become 'trapped' by a bulk insertion
     //$('tr.yes3-fmapr-redcap-field.yes3-fmapr-new-field:not(:last-child)').remove();
 
     // get rid of all of em
     $('tr.yes3-fmapr-redcap-field.yes3-fmapr-new-field').remove();
     
     //if ( !FMAPR.countNewFieldRows() && !FMAPR.someBadSettings() ){
     if ( !FMAPR.someBadSettings() ){
 
         YES3.Functions.addRawREDCapField({}, {}, false, true, true);
     }
 }
  
FMAPR.setRepeatLayoutConstraints = function()
{
    let form_name = "";

    if ( FMAPR.isRepeatedLayout() ){

        let currentConstraint = FMAPR.formNameConstraint;

        let field_name = $('tr.yes3-fmapr-data-element[data-object_type=field]:first').attr('data-object_name');

        if ( !YES3.isEmpty(field_name) ){

            form_name = FMAPR.getFormForField( field_name );
        }
        
        if ( YES3.isEmpty(form_name) ){

            form_name = field_name = $('tr.yes3-fmapr-data-element[data-object_type=form]:first').attr('data-object_name');
        }

        if ( !YES3.isEmpty(form_name) ){

            if ( form_name !== FMAPR.formNameConstraint ) {
            
                FMAPR.formNameConstraint = form_name;
                FMAPR.postMessage(`Note: all further additions must be from the '${FMAPR.formNameConstraint}' form.`, true);
            }
        }
        else {

            FMAPR.formNameConstraint = "";
            FMAPR.clearMessage();
        }

        if ( FMAPR.formNameConstraint !== currentConstraint || FMAPR.constrainedAutocompleteSource.length === 0 ){
            
            FMAPR.setConstrainedAutocompleteSource();
            //FMAPR.ensureNewFieldRowAtEnd(); // ensure 'add field' item has the right autocomplete source
            FMAPR.displayActionIcons(); // make sure the bulk insertion icon is disabled, if constraint active
            
            // constrain the autocompleters
            $("input.yes3-fmapr-listener-set.ui-autocomplete-input").each(function(){

                FMAPR.setExportItemFieldAutoselectInput( $(this) );
            });
        }

    }
    else {

        FMAPR.formNameConstraint = "";
    }

    return FMAPR.formNameConstraint;
}

FMAPR.setConstrainedAutocompleteSource = function(){

    FMAPR.constrainedAutocompleteSource = [];

    if ( !FMAPR.isRepeatedLayout() ){

        return true;
    }

    let okay = 0;
    let form_name = "";
    let isRepeating = false;

    for (let i=0; i<FMAPR.project.field_metadata.length; i++){

        form_name = FMAPR.project.field_metadata[i].form_name;

        okay = 0;

        if ( FMAPR.formNameConstraint.length > 0 ){

            okay = ( form_name === FMAPR.formNameConstraint ) ? 1 : 0;
        }
        else {

            try {

                okay = FMAPR.project.form_metadata[FMAPR.project.form_index[form_name]].form_repeating;
            } catch(error){

                console.log('setConstrainedAutocompleteSource:ERROR resolving form_repeating property for form [' + form_name + '].');
                okay = 0;
            }
        }

        if ( okay===1 ){

            FMAPR.constrainedAutocompleteSource.push({
                "value": FMAPR.project.field_metadata[i].field_name,
                "label": "[" + FMAPR.project.field_metadata[i].field_name + "] " + FMAPR.project.field_metadata[i].field_label
            });
        }
    }

    console.log('setConstrainedAutocompleteSource', FMAPR.constrainedAutocompleteSource.length + "items");
}

FMAPR.rowSelectorHtml = function()
{
    return '<label class="yes3-checkmarkContainer">'
        + '<input type="checkbox" class="yes3-row-selector">'
        + '<span class="yes3-checkmark"></span></label>'
    ;
}

YES3.Functions.appendExportItem = function()
{
    FMAPR.appendExportItem();
}

YES3.Functions.addRawREDCapField = function( element, theRowBeforeWhich, batchMode, noTrashCan, noScroll )
{   

    element = element || {};
    theRowBeforeWhich = theRowBeforeWhich || {};
    batchMode = batchMode || false;
    noTrashCan = noTrashCan || false;
    noScroll = noScroll || false;

    // if there is already a blank field, scroll to it
    if ( FMAPR.countNewFieldRows() && !batchMode ){

        FMAPR.scrollExportItemsTableToNewField();
        return true;
    }

    /**
     * not allowed if there are spec settings errors
     */
    if ( FMAPR.someBadSettings() ){

        return false;
    }

    //console.log('addRawREDCapField: theRowBeforeWhich = [' + theRowBeforeWhich + ']');

    let tbl = FMAPR.getExportItemsTable();
     
    let fmaprBody = tbl.find('tbody');

    let yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');

    let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let trashCanClass = 'yes3-visible';

    let elementClass = ( $.isEmptyObject(element) ) ? 'yes3-fmapr-new-field' : '';

    let html = "";

    let tooltip = ( noTrashCan ) ? "" : FMAPR.tooltips.row_selector;

    html += `<tr class='yes3-fmapr-redcap-field yes3-fmapr-data-element yes3-fmapr-sortable ${elementClass}' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" data-yes3_fmapr_data_element_description='(non-specification) REDCap field' id="${rowId}" data-required="0" data-element_origin="redcap" data-object_type="field">`;
    html += `<td class='yes3-fmapr-row-number' title='${tooltip}'>&nbsp;</td>`;
    html += `<td class='yes3-3 yes3-td-left' title="(non-specification) REDcap field"><span class='yes3-fmapr-redcap-element'>add a REDCap field to the export</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;

    //-xxxif ( FMAPR.export_specification.export_layout==="h" ){

        html += `<td class='yes3-3 yes3-td-middle'><span class="yes3-fmapr-horizontal-only-xxx">${eventSelectHtml}</span></td>`;
    //}
    //else {

    //   html += `<td class='yes3-3 yes3-td-middle'>&nbsp;</td>`;
    //}

    if ( noTrashCan ){

        //html += `<td class='yes3-gutter-right-top yes3-td-right'>&nbsp;</td>`;
        trashCanClass = 'yes3-invisible';
    }

    html += `<td class='yes3-gutter-right-top yes3-td-right'><i class='far fa-trash-alt ${trashCanClass}' onclick='FMAPR.removeDataElement("${yes3_fmapr_data_element_name}");'></i></td>`;

    html += "</tr>";

    /**
     * If there is no "row before" the table has no rows so we append.
     */
    if ( $.isEmptyObject(theRowBeforeWhich) ){

        fmaprBody.append( html );
    }
    else {

        $( html ).insertAfter( theRowBeforeWhich );
    }

    /**
     * If appending an empty element then this is a new, blank field.
     * Otherwise initialize it using the element properties
     */
    if ( $.isEmptyObject(element) ){

        FMAPR.doExportItemsTableHousekeeping();

        FMAPR.renumberRows();

        if ( !noScroll ) {

            FMAPR.scrollExportItemsTableToBottom();

            $('input#'+FMAPR.elementInputId( yes3_fmapr_data_element_name )).focus();
        }
     }
    else {

        let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

        let itemREDCapField = elementRow.find('input[data-mapitem=redcap_field_name]:first');

        let itemLabel = FMAPR.project.field_metadata[FMAPR.project.field_index[element.redcap_field_name]].field_label;

        elementRow.prop("title", "[" + element.redcap_field_name + "] " + itemLabel);

        itemREDCapField.val( element.redcap_field_name );

        FMAPR.REDCapFieldOnChange( itemREDCapField, true );

        //-xxx if ( FMAPR.export_specification.export_layout==="h" ){
        
            let itemREDCapEvent = elementRow.find('select[data-mapitem=redcap_event_id]:first');

            /**
             * handles the case where 'all events' are requested but the form has just one event
             */
            if ( itemREDCapEvent.find("option").length === 1 ){

                itemREDCapEvent.val(itemREDCapEvent.find("option").first().val());
            }
            else {

                itemREDCapEvent.val(element.redcap_event_id);
            }

            FMAPR.REDCapEventOnChange(itemREDCapEvent, batchMode);
        //}
    }

    return yes3_fmapr_data_element_name;
}

FMAPR.addREDCapFormV2 = function( form_name, event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, unsaved )
{   
    //theRowBeforeWhich = theRowBeforeWhich || FMAPR.getNewFieldRow();
    yes3_fmapr_data_element_name = yes3_fmapr_data_element_name || "";
    unsaved = unsaved || false;

    let unsavedClass = ( unsaved ) ? "yes3-unsaved" : "";
    let form_index = -1;
    let form_label = "all forms";

    if ( YES3.isEmpty( theRowBeforeWhich ) ){
        theRowBeforeWhich = FMAPR.getNewFieldRow();
    }
    mode = ( YES3.isEmpty(theRowBeforeWhich) ) ? "append" : "insert";

    //console.log("addREDCapFormV2:theRowBeforeWhich", theRowBeforeWhich, mode, FMAPR.getNewFieldRow(), YES3.isEmpty( theRowBeforeWhich ));

    if ( form_name !== "all" ){

        form_index = FMAPR.project.form_index[form_name];

        if ( typeof form_index !== "number" && form_name !== "all" ){
            console.log("addREDCapFormV2: invalid form name", form_name);
            return false;
        }
        else {
            form_label = FMAPR.project.form_metadata[form_index].form_label;
        }
    }

    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    if ( !yes3_fmapr_data_element_name ) {

        yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);
    }

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let field_count = (form_name==="all") ? FMAPR.project.field_metadata.length : FMAPR.project.form_metadata[form_index].form_fields.length;

    //let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');
    //let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let html = `<tr class='yes3-fmapr-redcap-form yes3-fmapr-data-element yes3-fmapr-sortable ${unsavedClass}' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" data-yes3_fmapr_data_element_description="REDCap form" id="${rowId}" data-element_origin="redcap" data-object_type="form" data-object_name="${form_name}" data-object_event="${event}">`;
    html += `<td class='yes3-fmapr-row-number' title='${FMAPR.tooltips.row_selector}'>&nbsp;</td>`;
    html += `<td class='yes3-fmapr-redcap-object-editor' title='${FMAPR.tooltips.row_editor}'><i class='far fa-edit yes3-fmapr-item-editor' onclick='FMAPR.editREDCapExportItem("${yes3_fmapr_data_element_name}");'></i></td>`;
    html += `<td class='yes3-fmapr-redcap-object-type'>form</td>`;
    if ( FMAPR.project.is_longitudinal ){
        html += `<td class='yes3-fmapr-redcap-object-event'>${FMAPR.exportItemRowEventLabel(event)}</td>`;
    }
    html += `<td class='yes3-fmapr-redcap-object-name' title="REDcap form">${form_label}</td>`;
    html += `<td class='yes3-gutter-right-top yes3-td-right'><i class='far fa-trash-alt' onclick='FMAPR.removeDataElement("${yes3_fmapr_data_element_name}");'></i></td>`;
    html += "</tr>";

    if ( mode==="insert" ){
        $( html ).insertBefore( theRowBeforeWhich );
    }
    else {
        fmaprBody.append( html );
    }

    let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

    FMAPR.setRowSelectorListenerV2( elementRow );

    FMAPR.setRepeatLayoutConstraints();

    return yes3_fmapr_data_element_name;
}

FMAPR.setObjectInsertMode = function( theRowBeforeWhich )
{
    if ( typeof theRowBeforeWhich === "object" && theRowBeforeWhich.length > 0 ) {
        return "insert"
    };
    return "append";
}

FMAPR.addREDCapFieldV2 = function( field_name, event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, unsaved, batch )
{   
    //theRowBeforeWhich = theRowBeforeWhich || FMAPR.getNewFieldRow();
    yes3_fmapr_data_element_name = yes3_fmapr_data_element_name || "";
    unsaved = unsaved || false;
    batch = batch || false;

    let unsavedClass = ( unsaved ) ? "yes3-unsaved" : "";

    let field_index = FMAPR.project.field_index[field_name];

    if ( typeof field_index !== "number" && field_name !== "all" ){
        console.log("addREDCapFieldV2: invalid field name", field_name);
        return false;
    }

    //mode = "append";

    //console.log("addREDCapFieldV2:theRowBeforeWhich", theRowBeforeWhich, mode, FMAPR.getNewFieldRow(), YES3.isEmpty( theRowBeforeWhich ));

    let field_label = FMAPR.project.field_metadata[field_index].field_label;
 
    if ( !yes3_fmapr_data_element_name ) {
        
        yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);
    }

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let html = `<tr class='yes3-fmapr-redcap-field yes3-fmapr-data-element yes3-fmapr-sortable ${unsavedClass}' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" data-yes3_fmapr_data_element_description="REDCap field" id="${rowId}" data-element_origin="redcap" data-object_type="field" data-object_name="${field_name}" data-object_event="${event}">`;
    html += `<td class='yes3-fmapr-row-number' title='${FMAPR.tooltips.row_selector}'>&nbsp;</td>`;
    html += `<td class='yes3-fmapr-redcap-object-editor' title='${FMAPR.tooltips.row_editor}'><i class='far fa-edit yes3-fmapr-item-editor' onclick='FMAPR.editREDCapExportItem("${yes3_fmapr_data_element_name}");'></i></td>`;
    html += `<td class='yes3-fmapr-redcap-object-type'>field</td>`;
    if ( FMAPR.project.is_longitudinal ){
        html += `<td class='yes3-fmapr-redcap-object-event'>${FMAPR.exportItemRowEventLabel(event)}</td>`;
    }
    html += `<td class='yes3-fmapr-redcap-object-name' title="REDcap field">${field_name}<span class='yes3-fmapr-field-label'> - ${field_label}</span></td>`;
    html += `<td class='yes3-gutter-right-top yes3-td-right' title='${FMAPR.tooltips.row_trashcan}'><i class='far fa-trash-alt' onclick='FMAPR.removeDataElement("${yes3_fmapr_data_element_name}");'></i></td>`;
    html += "</tr>";

    if ( batch ){

        return html;
    }
    
    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    if ( YES3.isEmpty( theRowBeforeWhich ) ){
        theRowBeforeWhich = FMAPR.getNewFieldRow();
    }
    mode = ( YES3.isEmpty(theRowBeforeWhich) ) ? "append" : "insert";

    if ( mode==="insert" ){
        $( html ).insertBefore( theRowBeforeWhich );
    }
    else {
        fmaprBody.append( html );
    }

    let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

    FMAPR.setRowSelectorListenerV2( elementRow );

    FMAPR.setRepeatLayoutConstraints();

    return yes3_fmapr_data_element_name;
}

FMAPR.ensureNewFieldRowAtEndV2 = function()
{    
    // form already there?
    if ( $(`tr#${FMAPR.rapidEntryFormRowId}`).length ){

       return true;
    }
 
    const fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');
    const cols2span = ( FMAPR.project.is_longitudinal ) ? 2 : 1;

    let html = `<tr class='yes3-fmapr-new-field-form' id="${FMAPR.rapidEntryFormRowId}">`;
    html += `<td>&nbsp;</td>`;
    html += `<td>&nbsp;</td>`;
    html += `<td><em>new field</em></td>`;
    html += `<td colspan=${cols2span}>-- form will go here --</td>`;
    html += `<td class='yes3-gutter-right-top yes3-td-right'>&nbsp</td>`;
    html += "</tr>";

    fmaprBody.append(html);
}

FMAPR.getNewFieldRow = function()
{
    return $(`tr#${FMAPR.rapidEntryFormRowId}`);
}

FMAPR.exportItemRowEventLabel = function(event)
{
    if ( !FMAPR.project.is_longitudinal ) {

        return "--"; // n/a
    }

    if ( event==="all" ){

        return "all events";
    }

    return FMAPR.project.event_metadata[event].event_label + " event";
}


FMAPR.addREDCapForm = function( form_name, event, theRowBeforeWhich )
{   
    theRowBeforeWhich = theRowBeforeWhich || {};

    let form_index = FMAPR.project.form_index[form_name];

    if ( typeof form_index !== "number" && form_name !== "all" ){
        return false;
    }
     
    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    let yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let eventSelectHtml  = FMAPR.getFormEventHtml( form_name, yes3_fmapr_data_element_name );

    let field_count = (form_name==="all") ? FMAPR.project.field_metadata.length : FMAPR.project.form_metadata[form_index].form_fields.length;

    //let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');
    //let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let html = `<tr class='yes3-fmapr-redcap-form yes3-fmapr-data-element yes3-fmapr-sortable' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" data-yes3_fmapr_data_element_description="REDCap form" id="${rowId}" data-required="0" data-element_origin="redcap" data-object_type="form" data-form_name="${form_name}">`;
    html += `<td class='yes3-fmapr-row-number' title='${FMAPR.tooltips.row_selector}'>&nbsp;</td>`;
    html += `<td class='yes3-3 yes3-td-left' title="REDcap form"><span class='yes3-fmapr-redcap-element'>${yes3_fmapr_data_element_name}</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>up to ${field_count} fields</td>`;
    html += `<td class='yes3-3 yes3-td-middle'><span class="yes3-fmapr-horizontal-only-xxx">${eventSelectHtml}</span></td>`;
    html += `<td class='yes3-gutter-right-top yes3-td-right'><i class='far fa-trash-alt' onclick='FMAPR.removeDataElement("${yes3_fmapr_data_element_name}");'></i></td>`;
    html += "</tr>";

    /**
     * If there is no "row before" the table has no rows so we append.
     */
    if ( $.isEmptyObject(theRowBeforeWhich) ){
        fmaprBody.append( html );
    }
    else {
        $( html ).insertAfter( theRowBeforeWhich );
    }

    let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

    let itemREDCapEvent = elementRow.find('select[data-mapitem=redcap_event_id]:first');

    FMAPR.REDCapEventOnChange(itemREDCapEvent);

    /**
     * handling for case where 'all events' is requested but there is just one event for the form
     */
    if ( event==='all' && itemREDCapEvent.find('option').length===1 ){

        itemREDCapEvent.val( itemREDCapEvent.find('option').first().val() );
    }
    else {

        itemREDCapEvent.val(event);
    }

    return yes3_fmapr_data_element_name;
}

YES3.Functions.saveExportSpecification = function(auditOnly) 
{
    auditOnly = auditOnly || false;
    
    let settingsContainer = FMAPR.getExportSettingsContainer();

    FMAPR.reloadParms.export_uuid = FMAPR.getExportUUID();

    let specItems = {};

    let setting = "";

    let postParams = {
        "request": "saveExportSpecification",
        "export_uuid": FMAPR.getExportUUID(),
        "export_uspec_json": FMAPR.getExportUspecJSONV2(),
        "export_items_json": FMAPR.getExportItemsJSONV2()
    }

    FMAPR.markAllGood(); // clear errors (yes3-error)

    FMAPR.clearMessage();

    settingsContainer.find('input, select, textarea:not([data-setting=export_uspec_json])').each( function(){

        if ( $(this).attr('data-setting') ){

            setting = $(this).attr('data-setting');

            if ( typeof specItems[setting] === 'undefined' ){

                specItems[setting] = {
                    "completed": false, 
                    "visible": $(this).is(":visible"), 
                    "optional": $(this).hasClass("yes3-optional"),
                    "value": ""
                };
            }

            if ( $(this).attr('type') === 'checkbox' ) {

                specItems[setting].completed = true;
                specItems[setting].value = ( $(this).is(':checked') ) ? "1" : "0";
            }
            else if ( $(this).attr('type') !== 'radio' || $(this).is(':checked') ){

                specItems[setting].value = $(this).val() || '';
                specItems[setting].completed = ( specItems[setting].value.length );
             }
        }
    });

    /**
     * each setting becomes a POST parameter for the save request
     */
    for (const settingName in specItems){

        if ( typeof postParams[settingName] === "undefined" ){

            postParams[settingName] =  specItems[settingName].value;
        }

        if ( specItems[settingName].visible && !specItems[settingName].optional && !specItems[settingName].completed ) {

            FMAPR.markAsBad( settingsContainer.find(`[data-setting=${settingName}]`) );
        }
    }

    FMAPR.validateAndReportExportUspecJsonV2();

    if ( FMAPR.someBadSettings() ){

        YES3.hello("WARNING: at least one error was detected (a required entry is blank, an invalid entry etc). You must fix the indicated error(s) before you may save the export specificaytion.");

        FMAPR.postMessage("You must fix the indicated error(s) before adding fields or saving.", true);

        return true;
    }
 
    if ( !auditOnly ){

        YES3.isBusy( YES3.captions.wait_saving_specification );
        
        console.log( 'saveExportSpecification', postParams );

        YES3.requestService( postParams, FMAPR.saveExportSpecificationCallback, false );
    }
 }
 
FMAPR.saveExportSpecificationCallback = function( response ){
  
    console.log( 'saveExportSpecificationCallback', response );

    YES3.notBusy();

    if ( FMAPR.reloadParms.export_uuid.length > 0 ){

        console.log( 'saveExportSpecificationCallback:reload', FMAPR.reloadParms.export_uuid );
        FMAPR.loadSpecifications();
    }

    FMAPR.postMessage( response );

    if ( response.indexOf('Success') > -1 ){

        FMAPR.markAsClean(true);
    }
}

/*** EXPORT ***/
FMAPR.downloadExecute = function()
{
    let exportOption = $("input[type=radio][name=yes3-fmapr-export]:checked").val();

    if ( exportOption==="datadictionary"){
        FMAPR.downloadDataDictionary();
    }

    else if ( exportOption==="data"){
        FMAPR.downloadData();
    }

    else if ( exportOption==="zip"){
        FMAPR.downloadZip();
    }

    FMAPR.closeDownloadForm();
}

FMAPR.downloadDataDictionary = function()
{
    let url = YES3.moduleProperties.serviceUrl
        + "&request=downloadDataDictionary"
        + "&export_uuid=" + encodeURIComponent(FMAPR.getExportUUID())
        + "&csrf_token=" + encodeURIComponent(redcap_csrf_token)
    ;

    //console.log(url);

    window.open(url);
}

FMAPR.downloadData = function()
{
    let url = YES3.moduleProperties.serviceUrl
        + "&request=downloadData"
        + "&export_uuid=" + encodeURIComponent(FMAPR.getExportUUID())
        + "&csrf_token=" + encodeURIComponent(redcap_csrf_token)
    ;

    //console.log(url);

    window.open(url);
}

FMAPR.downloadZip = function()
{
    let url = YES3.moduleProperties.serviceUrl
        + "&request=downloadZip"
        + "&export_uuid=" + encodeURIComponent(FMAPR.getExportUUID())
        + "&csrf_token=" + encodeURIComponent(redcap_csrf_token)
    ;

    //console.log(url);

    window.open(url);
}

FMAPR.downloadDataDictionaryCallback = function( response )
{
  console.log(response);
}

YES3.Functions.exportToHost = function()
{
    YES3.YesNo("This action will replace the data for this export currently stored on the file system. Okay to proceed?",
    FMAPR.exportToHostGo );
}

FMAPR.exportToHostGo = function()
{     
    YES3.isBusy( YES3.captions.wait_exporting_data );

    FMAPR.postMessage("Export underway...");

    YES3.requestService(
        {
            'request': 'exportData',
            'export_uuid': FMAPR.getExportUUID()
        }, FMAPR.exportDataCallback
    );  
}

FMAPR.exportDataCallback = function( response )
{
  YES3.hello(response);
  FMAPR.clearMessage();
  YES3.notBusy();
}

/*** WAYBACK ***/

YES3.Functions.Wayback_openForm = function()
{
    YES3.openPanel('yes3-fmapr-wayback-panel');

    let wrapper = FMAPR.getExportSettingsContainer();
    //let wrapper = $("div#yes3-container");

    let y = wrapper.offset().top;

    let wayback = $('div#yes3-fmapr-wayback-panel');

    wayback.css('opacity', 0).animate(
        {
            top: y,
            opacity: 1
        }, 
        2000, 
        function() {

        }
    );

    $({deg: 0}).animate({deg: 360}, {
        duration: 2000,
        step: function(now) {
            // in the step-callback (that is fired each step of the animation),
            // you can use the `now` paramter which contains the current
            // animation-position (`0` up to `angle`)
            wayback.css({
                transform: 'rotate(-' + now + 'deg)'
            });

            wrapper.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    });

    YES3.requestService(
        {
            'request': 'get_wayback_html',
            'export_uuid': FMAPR.getExportUUID()
        }, FMAPR.Wayback_openFormCallback
    );
}

FMAPR.Wayback_openFormCallback = function( response )
{
    console.log('Wayback_openFormCallback', response);
    $("select#yes3-fmapr-wayback-select").empty().append(response);
}

FMAPR.Wayback_Execute = function()
{
    let log_id = $("select#yes3-fmapr-wayback-select").val();

    FMAPR.reloadParms.wayback = true;

    FMAPR.loadSpecification(log_id);

    FMAPR.Wayback_closeForm(log_id);
}

FMAPR.Wayback_closeForm = function(log_id)
{
    log_id = log_id || 0;

    let y = $(window).innerHeight()/2;

    let wayback = $('div#yes3-fmapr-wayback-panel');

    wayback.animate(
        {
            top: y,
            opacity: 0
        }, 
        2000, 
        function() {
            YES3.closePanel('yes3-fmapr-wayback-panel');

        }
    );

    $({deg: 0}).animate({deg: 360}, {
        duration: 2000,
        step: function(now) {
            // in the step-callback (that is fired each step of the animation),
            // you can use the `now` paramter which contains the current
            // animation-position (`0` up to `angle`)
            wayback.css({
                transform: 'rotate(' + now + 'deg)'
            });
        }
    });

    if ( log_id ){

        YES3.hello("The selected backup has been loaded. You must now save it if you want to accept it as the export specification.");
    }
}

 /*
  * refresh project settings from NIAFMAPR, then call getProjectSettings to fetch them
  */
 FMAPR.updateProjectSettings = function() {
    FMAPR.postMessage("plz wait..");
    YES3.requestService({'request':'update_project_settings'}, FMAPR.updateProjectSettingsCallback, true);
 }
 
 FMAPR.updateProjectSettingsCallback = function(response) {
    FMAPR.postMessage(response.message);
    console.log('updateProjectSettingsCallback', response);
    if ( response.result==="success" ) {
       FMAPR.getProjectSettings();
    } 
 }
 
 FMAPR.specificationTableBodyId = function(export_uuid)
 {
     return 'yes3-fmapr-tbody-' + export_uuid;
 }
 
FMAPR.specificationSelect = function()
{
    let export_uuid = $('select#export_uuid').val();

    if ( export_uuid.length ){

        console.log('specificationSelect', FMAPR.specification_index, FMAPR.export_specification, export_uuid);

    }
}
 
FMAPR.specificationSave = function()
{
    let timestamp = new Date().ymdhms();
    FMAPR.postMessage("Specification saved at " + timestamp + ".");
}

FMAPR.dataElementRowId = function(data_element_name)
{
    return `yes3_fmapr_data_element-${data_element_name}`;
}

FMAPR.markAsBuildInProgress = function()
{
    YES3.contentLoaded = false;
    FMAPR.markAsClean();
    FMAPR.clearMessage();
}

FMAPR.markAsBuildCompleted = function()
{
    YES3.contentLoaded = true;
}

FMAPR.buildIsInProgress = function()
{
    return !YES3.contentLoaded;
}

FMAPR.rowsToMove = [];

FMAPR.makeSortable = function( parentElement )
{
    parentElement.sortable({
        items: 'tr.yes3-fmapr-sortable:not(.yes3-fmapr-new-field)',
        cursor: 'grab',
        axis: 'y',
        dropOnEmpty: false,
        start: function (e, ui) {
            ui.item.addClass("yes3-fmapr-row-selected");
            let data_element_name = ui.item.data('yes3_fmapr_data_element_name');
            FMAPR.rowsToMove = $(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name='${data_element_name}']`);
        },
        stop: function (e, ui) {
            ui.item.removeClass("yes3-fmapr-row-selected");
            //console.log('sortable', FMAPR.rowsToMove);
            for (let j=FMAPR.rowsToMove.length; j>0; j--){
                ui.item.after(FMAPR.rowsToMove[j-1]);
            }
            FMAPR.markAsDirty();
            FMAPR.renumberRows();
            FMAPR.resizeExportItemsTable();
        }
    });

}

FMAPR.nowLoaded = function()
{
    $('.yes3-when-loaded').show();
}

FMAPR.nowUnLoaded = function()
{
    $('.yes3-when-loaded').hide();
}
  
FMAPR.RawREDCapDataElementName = function(n) 
{
    if ( n===0 ){
        FMAPR.maxRawREDCapDataElementNumber++;
        n = FMAPR.maxRawREDCapDataElementNumber;
    }
    return 'redcap_element_' + n;
}

FMAPR.isRawREDCapDataElement = function( elementName )
{
    return ( elementName.substring(0, 15) === 'redcap_element_'  );
}
  
FMAPR.RawREDCapDataElementNumber = function( elementName ) 
{
    if ( !FMAPR.isRawREDCapDataElement(elementName)) {
        return 0;
    }

    return parseInt(elementName.split('_')[2]);
}

FMAPR.doExportItemsTableHousekeeping = function( isClean )
{
    isClean = isClean || false;

    FMAPR.resizeExportItemsTable();

    //FMAPR.setExportItemFieldAutoselectInputs();

    //FMAPR.setEventSelectListeners();

    //FMAPR.setRowSelectorListeners();

    //FMAPR.setREDCapElementListeners();

    if ( isClean ){
        FMAPR.markAsClean();
        FMAPR.markAsBuildCompleted();
        FMAPR.clearMessage();
        YES3.displayActionIcons();
        FMAPR.displayActionIcons();
    }
}

FMAPR.removeDataElement = function(element_name)
{
    $(`tr[data-yes3_fmapr_data_element_name='${element_name}']`).remove();
    FMAPR.markAsDirty();
    // enforce a refresh in case this is a repeating export layout

    YES3.displayActionIcons();
    FMAPR.displayActionIcons();
    FMAPR.renumberRows();
    FMAPR.setRepeatLayoutConstraints();
}

FMAPR.scrollExportItemsTableToBottom = function()
{
    let tbl = FMAPR.getExportItemsTable();

    let bodyId = tbl.find('tbody').attr('id');

    let domObj = document.getElementById(bodyId);
    domObj.scrollTop = domObj.scrollHeight; 
}

FMAPR.scrollExportItemsTableToNewField = function()
{
    FMAPR.scrollExportItemsTableToBottom();

    $('tr.yes3-fmapr-new-field input.yes3-fmapr-input-element').focus();
}

FMAPR.resizeExportItemsTable = function()
{
    let fmaprTable = FMAPR.getExportItemsTable();

    if ( !fmaprTable.is(':visible') ){

        return true;
    }

    let gutterWidth = 30;
    let scrollbarWidth = 20;

    let fmaprFooter = $('div#yes3-fmapr-footer');

    let parentSection = $('div#yes3-container').parent();

    if ( !fmaprTable.length ){
        return false;
    }

    let fmaprTableBody = fmaprTable.find('tbody').first();

    let windowHeight = $(window).innerHeight();

    /**
     * The REDCap section div (#center) is programmatically forced to have vertical padding,
     * so we need to take that into account in the table height calculation.
     * The expression (parentSection.outerHeight() - parentSection.height()) returns the sum of top and bottom padding.
     */
    let tableHeight = windowHeight 
        - fmaprTable.offset().top 
        - scrollbarWidth 
        - fmaprFooter.outerHeight()
        - (parentSection.outerHeight() - parentSection.height())
    ;

    // position() returns offset relative to parent object (the table)
    let bodyHeight = tableHeight - fmaprTableBody.position().top;

    //let tableWidth = $('div#yes3-fmapr-wrapper').width();
    let tableWidth = fmaprTable.width();

    let nameWidth  = (tableWidth - scrollbarWidth - 12*gutterWidth);
 
    //fmaprTable.css({'width': tableWidth+'px', 'height': tableHeight+'px'});

    fmaprTable.css({'height': tableHeight+'px'});

    fmaprTableBody.css({'height': bodyHeight+'px'});

    //console.log("resizeExportItemsTable: halfSplit="+halfSplit);

    fmaprTable.find('td.yes3-fmapr-redcap-object-name').css({'width': nameWidth+'px', 'max-width': nameWidth+'px'});
    //fmaprTable.find('td.yes3-fmapr-redcap-object-event').css({'width': eventWidth+'px', 'max-width': eventWidth+'px'});
}

  
FMAPR.toggleLovDisplay = function(yes3_fmapr_data_element_name) {

    let tbl = FMAPR.getExportItemsTable();

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let parentRow = $(`tr#${rowId}`);

    let lov_rows = tbl.find(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'].yes3-fmapr-lov`);
    
    let icon = tbl.find(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'].yes3-fmapr-data-element i`);

    if ( icon.hasClass('fa-plus') ){

        lov_rows.show();

        parentRow.removeClass('yes3-fmapr-sortable');

        icon.removeClass('fa-plus').addClass('fa-minus');
    } 
    else {

        lov_rows.hide();

        parentRow.addClass('yes3-fmapr-sortable');

        icon.removeClass('fa-minus').addClass('fa-plus');
    }
}
 
 FMAPR.toggleValuePickerDisplay = function(yes3_fmapr_data_element_name, lov_value) {
 
    let tbl = FMAPR.getExportItemsTable(); 

    let value_picker_wrapper = tbl.find(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'] div.yes3-fmapr-value-picker-wrapper`);
    let icon = tbl.find(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'] i`);
 
    if (  icon.hasClass('fa-plus')  ){

        let w = 0.9 * value_picker_wrapper.parent().width() + 'px';
 
        value_picker_wrapper
            .css({
                'width': w,
                'max-width': w
            })
            .show()
        ;

        icon.removeClass('fa-plus').addClass('fa-minus');
    } else {
       value_picker_wrapper.hide();
       icon.removeClass('fa-minus').addClass('fa-plus');
    }
 }
 
 FMAPR.normalizeString = function( s ){
    if ( typeof s !== 'string' ) {
       return s;
    }
    return s.replace(/[^a-zA-Z0-9]/g,'_').toLowerCase();
 }
 
 FMAPR.specificationTableId = function( export_uuid )
 {
    return 'specification-' + export_uuid;
 }
 
 FMAPR.elementInputId = function( yes3_fmapr_data_element_name ){
    return 'element-' + FMAPR.normalizeString(yes3_fmapr_data_element_name);
 }
 
 FMAPR.elementEventId = function( yes3_fmapr_data_element_name ){
    return 'element-' + FMAPR.normalizeString(yes3_fmapr_data_element_name + '-event');
 }
 
 FMAPR.lovInputId = function( yes3_fmapr_data_element_name, value ){
    return 'lov' +
       '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name) +
       '-' + FMAPR.normalizeString(value)
       ;
 }
 
 FMAPR.pickerWrapperId = function(yes3_fmapr_data_element_name, yes3_fmapr_lov_value ){
    return 'picker' + 
       '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name) +
       '-' + FMAPR.normalizeString(yes3_fmapr_lov_value)
       ;
 }
 
 FMAPR.typeInputId = function(){
    return 'type';
 }

 FMAPR.itemTypeClass = function(element_origin)
 {
     return ( element_origin==='redcap' ) ? 'yes3-fmapr-redcap' : 'yes3-fmapr-specification';
 }
 
 FMAPR.getElementInputHtml = function( yes3_fmapr_data_element_name, element_origin ){
    element_origin = element_origin || 'specification';
    let typeClass = FMAPR.itemTypeClass(element_origin);
    let id = FMAPR.elementInputId( yes3_fmapr_data_element_name );
    let html =`<input type='text' id=${id} class='yes3_fmapr_field_autocomplete ${typeClass} yes3-fmapr-input-element yes3-fmapr-item' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_field_name' data-element_origin='${element_origin}' placeholder='Start typing or spacebar for all' />`;
    return html;
 }
 
FMAPR.getElementEventHtml = function( yes3_fmapr_data_element_name, element_origin ){
    element_origin = element_origin || 'specification';
    let typeClass = FMAPR.itemTypeClass(element_origin);
    let id = FMAPR.elementEventId( yes3_fmapr_data_element_name );
    //let html = `<select id=${id} class='yes3-fmapr-event-select yes3-fmapr-item' data-export_uuid='${export_uuid}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id'/>${FMAPR.project.event_select_options_html}</select>`;
    let html = `<select id=${id} class='yes3-fmapr-event-select ${typeClass} yes3-fmapr-item yes3-fmapr-field-event' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id' data-element_origin='${element_origin}' title="${FMAPR.tooltips['event_select']}"/></select>`;
    return html;
}
 
FMAPR.getFormEventHtml = function( form_name, yes3_fmapr_data_element_name ){

    let element_origin = 'redcap';

    let optionsHtml = '';

    let formEvents = [];

    if ( form_name === "all" ){

        formEvents = FMAPR.project.project_event_metadata;
    }
    else {

        let form_index = FMAPR.project.form_index[form_name];

        if ( typeof form_index === "number" ){

            formEvents = FMAPR.project.form_metadata[form_index].form_events;
        }
    }

    if ( formEvents.length > 1 ){

        optionsHtml = '<option value="all">all events for form</option>';
    }

    for ( let e=0; e<formEvents.length; e++ ){
        optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].event_label}</option>`;                 
    }

    let typeClass = FMAPR.itemTypeClass(element_origin);

    let id = FMAPR.elementEventId( form_name );

    let html = `<select id=${id} class='yes3-fmapr-event-select ${typeClass} yes3-fmapr-item yes3-fmapr-form-event' data-export_uuid='${FMAPR.getExportUUID()}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id' data-element_origin='${element_origin}'/>${optionsHtml}</select>`;
    
    return html;
}

/**
 * returns select options html for all events
 */
FMAPR.getAllEventOptionsHtml = function()
{
    let events = FMAPR.project.project_event_metadata;
    let optionsHtml = "";

    if ( events.length > 1 ){

        optionsHtml = '<option value="all">all events</option>';
    }

    for ( let e=0; e<events.length; e++ ){
        optionsHtml += `<option value=${events[e].event_id}>${events[e].event_label}</option>`;                 
    }

    return optionsHtml;
}

FMAPR.formApprovedForLayout = function( form_name )
{
    try {

        let form_index = FMAPR.project.form_index[form_name];
        let form = FMAPR.project.form_metadata[form_index];
        
        // repeating layout
        if ( FMAPR.export_specification.export_layout==="r" ){

            if ( !form.form_repeating ){

                return false;
            }

            // okay, form is repeating but is it the right form for this export?
            if ( FMAPR.formNameConstraint.length > 0 && FMAPR.formNameConstraint !== form_name ){

                return false;
            }

            return true;
        }
        // not repeating layout, only pass non-repeating forms
        else {

            if ( form.form_repeating ){

                return false;
            }

            return true;
        }

    } catch (e) {

        console.log("formApprovedForLayout ERROR", form_name, e);

        return false;
    }
}

FMAPR.getFormOptionsHtmlForEvent = function( event )
{
    event = event || "all";

    let forms = FMAPR.project.form_metadata;
    let optionsHtml = "";
    let accepted = false;
    let j = 0;
    let kount = 0;

    console.log("getFormOptionsHtmlForEvent", event);

    for ( let i=0; i<forms.length; i++ ){

        // first make sure the form is appropriate for the layout
        if ( !FMAPR.formApprovedForLayout( forms[i].form_name )){

            console.log("-->failed by formApprovedForLayout: ", event, forms[i].form_name);
            continue;
        }

        accepted = false;

        // bypass event confirmation is project is not longitudinal
        if ( event==="all" || !FMAPR.project.is_longitudinal ){

            accepted = true;
        }
        // accept if form is defined on the event 
        else {

            for (j=0; j<forms[i].form_events.length; j++){

                if ( forms[i].form_events[j].event_id === event ){

                    accepted = true;
                    break;
                }
            }
        }

        if ( accepted ){

            kount++;
            optionsHtml += `<option value=${forms[i].form_name}>${forms[i].form_label}</option>`;    
        }             
    }

    if ( kount > 1 && !FMAPR.isRepeatedLayout() ){

        optionsHtml = "<option value='all'>all forms</option>" + optionsHtml;
    }

    return optionsHtml;
}

FMAPR.getFieldAutoCompleteSource = function(event)
{
    event = event || "all";

    let accepted = false;
    let form_name = "";
    let form_index = 0;
    let j = 0;
    let events = [];
    let acSource = [];

    for (let i=0; i<FMAPR.project.field_metadata.length; i++){

        form_name = FMAPR.project.field_metadata[i].form_name;

        // first make sure the form is appropriate for the layout
        if ( !FMAPR.formApprovedForLayout( form_name )){

            continue;
        }

        accepted = false;

        try {

            form_index = FMAPR.project.form_index[form_name];
            events = FMAPR.project.form_metadata[form_index].form_events;

            // no need to check events if project is not longitudinal, or if events is "all"
            if ( event === "all" || !FMAPR.project.is_longitudinal ){

                accepted = true;
            }
            else {
                for (j=0; j<events.length; j++){

                    if ( events[j].event_id===event ){

                        accepted = true;
                        break;
                    }
                }
            }
        } catch(e){

            console.log("getFieldAutoCompleteSource ERROR", event, e);
            accepted = false;
        }

        if ( accepted ){

            acSource.push({
                "value": FMAPR.project.field_metadata[i].field_name,
                "label": "[" + FMAPR.project.field_metadata[i].field_name + "] " + FMAPR.project.field_metadata[i].field_label
            });
        }
    }

    console.log("getFieldAutoCompleteSource", acSource);

    return acSource;
}
 
 FMAPR.getLovInputHtml = function( yes3_fmapr_data_element_name, value ){
    let id = FMAPR.lovInputId( yes3_fmapr_data_element_name, value );
    let pickerWrapperId = FMAPR.pickerWrapperId( yes3_fmapr_data_element_name, value );
    return `<input type='text' id='${id}' class='yes3-fmapr-input-lov yes3-fmapr-item' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-yes3_fmapr_lov_value='${value}' data-mapitem='redcap_field_value' /><div class='yes3-fmapr-value-picker-wrapper' id='${pickerWrapperId}'></div>`;
 }
 
 FMAPR.setLovInputListeners = function() {
 
    let tbl = FMAPR.getExportItemsTable();

    let lovInputs = tbl.find('input[type=text].yes3-fmapr-input-lov');
 
    lovInputs
       .off()
       .on('change', function () {
          let yes3_fmapr_data_element_name = $(this).data('yes3_fmapr_data_element_name');
          let yes3_fmapr_lov_value = $(this).data('yes3_fmapr_lov_value');
          let parentCtl = $( 'input#' + FMAPR.elementInputId( yes3_fmapr_data_element_name) );
          let currentValue = parentCtl.val();
          let value = $(this).val();
 
          if ( value === '*' ) {
 
             FMAPR.params = {
                "export_uuid": FMAPR.getExportUUID(),
                "yes3_fmapr_data_element_name": yes3_fmapr_data_element_name,
                "lov_value": yes3_fmapr_lov_value,
                "parentCtl": parentCtl
             }
 
             YES3.YesNo( `Okay to make '${yes3_fmapr_lov_value}' the response for all exports?`, FMAPR.setLovConstantExecute );
 
          } else {
 
             if ( value ) {
                let pickerWrapperId = FMAPR.pickerWrapperId(yes3_fmapr_data_element_name, yes3_fmapr_lov_value);
                let values = value.split(',');
                $(`div#${pickerWrapperId} input[type=checkbox]`).prop('checked', false);
                for ( let i=0; i<values.length; i++ ){
                   $(`div#${pickerWrapperId} input[type=checkbox][value=${values[i]}]`).prop('checked', true);
                }
             }

             FMAPR.markAsDirty();
 
             FMAPR.setLovTogglePriorities(yes3_fmapr_data_element_name);
          }
 
          //console.log('LovInputListener', yes3_fmapr_data_element_name, value );
       })
    ;
}

FMAPR.getFormForField = function( field_name )
{
    let field_index = FMAPR.project.field_index[field_name];

    if ( typeof field_index === "number" ){

        return FMAPR.project.field_metadata[field_index].form_name;
    }

    return "";
}

FMAPR.rowIsCut = function(theRow)
{
    return theRow.hasClass('yes3-row-cut');
}

FMAPR.cutRow = function(theRow)
{
    theRow.addClass('yes3-row-cut');
}

FMAPR.setRowSelectorListenersV2 = function(){
    FMAPR.getExportItemRows().each(function(){

        FMAPR.setRowSelectorListenerV2( $(this) );
    });
}

FMAPR.setRowSelectorListenerV2 = function( theRow )
{
    theRow.find('td.yes3-fmapr-row-number')
        .off()
        .on("contextmenu", function(e){
            //console.log( 'contextmenu', e );

            FMAPR.REDCapFieldContextMenu($(this), e);

            return false;
        })
        .on("click", function(e){

            //console.log( 'click', e );

            let theRow = $(this).parent();

            if ( e.shiftKey ){

                e.preventDefault();

                //FMAPR.toggleSelected( $(this) );

                if ( FMAPR.isSelectionRangeStart(theRow) && FMAPR.isSelectionRangeEnd(theRow) ) {

                    FMAPR.clearSelectionRange( true );
                }
                else {

                    FMAPR.markRowSelected( theRow );
                    
                    if ( !FMAPR.selectionRangeStartDefined() ){
                        FMAPR.markSelectionRangeStart( theRow );
                    }
                    else {
                        FMAPR.markSelectionRangeEnd( theRow );
                    }

                    FMAPR.markSelectionRange();
                }

                e.stopPropagation();

                //YES3.contextMenuClose();

                return false;
            }
            else if ( e.ctrlKey ){

                e.preventDefault();

                FMAPR.toggleSelected( theRow, true );

                //YES3.contextMenuClose();

                e.stopPropagation();

                return false;
            }
            else {

                if ( FMAPR.cutRowCount() + FMAPR.copiedRowCount() > 0 ) {

                    FMAPR.markInsertionRow( theRow );
                }
                else {

                    // ignore the last 'new field' row
                    if ( !theRow.hasClass('yes3-fmapr-new-field') ){

                        FMAPR.clearSelections( true );
                        FMAPR.markRowSelected( theRow );
                        FMAPR.markSelectionRangeStart( theRow );
                    }
                }
            }

            return true;
            //return false;
        })
    ;

}

/**
 * DEPRECATED
 */
FMAPR.setRowSelectorListeners = function()
{
    return true;

    $('td.yes3-fmapr-row-number')
        .off()
        .on("contextmenu", function(e){
            //console.log( 'contextmenu', e );

            FMAPR.REDCapFieldContextMenu($(this), e);

            return false;
        })
        .on("click", function(e){

            //console.log( 'click', e );

            let theRow = $(this).parent();

            if ( e.shiftKey ){

                e.preventDefault();

                //FMAPR.toggleSelected( $(this) );

                if ( FMAPR.isSelectionRangeStart(theRow) && FMAPR.isSelectionRangeEnd(theRow) ) {

                    FMAPR.clearSelectionRange( true );
                }
                else {

                    FMAPR.markRowSelected( theRow );
                    
                    if ( !FMAPR.selectionRangeStartDefined() ){
                        FMAPR.markSelectionRangeStart( theRow );
                    }
                    else {
                        FMAPR.markSelectionRangeEnd( theRow );
                    }

                    FMAPR.markSelectionRange();
                }

                e.stopPropagation();

                //YES3.contextMenuClose();

                return false;
            }
            else if ( e.ctrlKey ){

                e.preventDefault();

                FMAPR.toggleSelected( theRow, true );

                //YES3.contextMenuClose();

                e.stopPropagation();

                return false;
            }
            else {

                if ( FMAPR.cutRowCount() + FMAPR.copiedRowCount() > 0 ) {

                    FMAPR.markInsertionRow( theRow );
                }
                else {

                    // ignore the last 'new field' row
                    if ( !theRow.hasClass('yes3-fmapr-new-field') ){

                        FMAPR.clearSelections( true );
                        FMAPR.markRowSelected( theRow );
                        FMAPR.markSelectionRangeStart( theRow );
                    }
                }
            }

            return true;
            //return false;
        })
    ;

}

FMAPR.setSpecialKeyListeners = function() {

    $(document).keydown(function(e) {

        if ( !e.ctrlKey ){ 
            
            return  true; 
        }

        var ch = String.fromCharCode(e.which).toUpperCase();

        if ( ch === 'X') {

            if ( FMAPR.selectedRowCount()===0 ){

                return true;
            }

            FMAPR.contextMenuCutRowSelections();

            //console.log('Ctrl+X pressed');
        }
        /*
        else if ( ch === 'C') {

            if ( FMAPR.selectedRowCount()===0 ){

                return true;
            }

            FMAPR.contextMenuCopyRowSelections();

            console.log('Ctrl+C pressed');
        }
        */
        else if ( ch === 'V') {

            if ( FMAPR.selectedRowCount()===0 ){

                return true;
            }

            FMAPR.contextMenuPasteRowSelections();

            //console.log('Ctrl+V pressed');
        }

        else if ( ch === 'Z') {

            if ( FMAPR.selectedRowCount()===0 ){

                return true;
            }

            FMAPR.contextMenuClearRowSelections();

            //console.log('Ctrl+Z pressed');
        }

        else {

            return true;
        }

        e.preventDefault();
    });
}

FMAPR.markSelectionRange = function()
{

    // start by clearing the range while preserving boundaries
    FMAPR.clearSelectionRange();

    let allRows = $('tr.yes3-fmapr-data-element');

    let startRow = $('tr.yes3-selection-range-start').first();
    let endRow = $('tr.yes3-selection-range-end').first();

    if ( !startRow.length || !endRow.length ) {
        return 0;
        //console.log('markSelectionRange: bailing');
    }
    
    let inRange = false;
    let endOfRange = false;
    let row = null;

    for (i=0; i<allRows.length; i++) {
 
        row = $(allRows[i]);

        if ( FMAPR.isSelectionRangeBoundary(row) ){
            if ( !inRange ) {
                inRange = true;
                if ( FMAPR.isSelectionRangeStart(row) && FMAPR.isSelectionRangeEnd(row) ) {
                    endOfRange = true;
                }
            }
            else {
                endOfRange = true;
            }
        }

        if ( inRange ){
            //console.log('markSelectionRange: marking as selected:', row);
            FMAPR.markRowSelected( row );
        }

        if ( endOfRange ){
            break;
        }
       
    }

    return FMAPR.selectedRowCount();
}

FMAPR.clearSelectionRange = function( boundariesToo )
{
    boundariesToo = boundariesToo || false;

    let theSelector = "tr.yes3-row-selected:not(.yes3-row-sticky)";

    if ( !boundariesToo ){
        theSelector += ":not(.yes3-selection-range-start):not(.yes3-selection-range-end)";
    }

    $(theSelector).removeClass('yes3-row-selected');

    if ( boundariesToo ){
        $('tr.yes3-selection-range-start').removeClass('yes3-selection-range-start');
        $('tr.yes3-selection-range-end').removeClass('yes3-selection-range-end');
    }

    FMAPR.clearInsertionRow();

    //else {
    //    FMAPR.markRowSelected( $('tr.yes3-selection-range-start') );
    //    FMAPR.markRowSelected( $('tr.yes3-selection-range-end') );
    //}
}

FMAPR.removeSelections = function( stickyToo )
{
    $("tr.yes3-row-selected").off().remove();

    FMAPR.clearInsertionRow();

    FMAPR.renumberRows();

    FMAPR.markAsDirty();
    
    FMAPR.setRepeatLayoutConstraints();
}

FMAPR.clearSelections = function( stickyToo )
{
    stickyToo = stickyToo || false;

    FMAPR.clearSelectionRangeBoundaries();

    $("tr.yes3-row-selected")
    .removeClass("yes3-row-selected")
    .removeClass("yes3-row-cut")
    .removeClass("yes3-row-copied")
    ;  

    if ( stickyToo ){

        $("tr.yes3-row-sticky").removeClass("yes3-row-sticky");       
    }

    FMAPR.clearInsertionRow();
}

FMAPR.clearSelectionRangeBoundaries = function() {

    $("tr.yes3-selection-range-start").removeClass("yes3-selection-range-start");
    $("tr.yes3-selection-range-end").removeClass("yes3-selection-range-end");
}

FMAPR.clearRowTagsButLeaveSelected = function()
{
    $("tr.yes3-selection-range-start").removeClass("yes3-selection-range-start");
    $("tr.yes3-selection-range-end").removeClass("yes3-selection-range-end");
    $("tr.yes3-row-cut").removeClass("yes3-row-cut");
    $("tr.yes3-row-copied").removeClass("yes3-row-copied");
}

FMAPR.selectionRangeStartDefined = function()
{
    return $('tr.yes3-selection-range-start').length;
}

FMAPR.selectionRangeEndDefined = function()
{
    return $('tr.yes3-selection-range-end').length;
}

FMAPR.isSelectionRangeBoundary = function( ele )
{
    return ( FMAPR.isSelectionRangeStart( ele ) || FMAPR.isSelectionRangeEnd( ele ) );
}

FMAPR.isSelectionRangeStart = function( ele )
{
    return ( ele.hasClass("yes3-selection-range-start") );
}

FMAPR.isSelectionRangeEnd = function( ele )
{
    return ( ele.hasClass("yes3-selection-range-end") );
}

FMAPR.removeSelectionRangeBoundaryMark = function( ele )
{
    if ( ele.hasClass("yes3-selection-range-start") ) {
        ele.removeClass("yes3-selection-range-start")
    }
    if ( ele.hasClass("yes3-selection-range-end") ) {
        ele.removeClass("yes3-selection-range-end")
    }
}

FMAPR.markSelectionRangeStart = function( ele )
{
    $("tr.yes3-selection-range-start").removeClass("yes3-selection-range-start");

    ele.addClass("yes3-selection-range-start");

    console.log('markSelectionRangeStart', ele);
}

FMAPR.markSelectionRangeEnd = function( ele )
{
    $("tr.yes3-selection-range-end").removeClass("yes3-selection-range-end");

    ele.addClass("yes3-selection-range-end");

    console.log('markSelectionRangeEnd', ele);
}

FMAPR.toggleSelected = function( ele, sticky )
{
    sticky = sticky || false;

    if ( FMAPR.rowIsSelected( ele ) ){

        FMAPR.markRowUnSelected( ele, sticky );
    }
    else {

        FMAPR.markRowSelected( ele, sticky );
    }
}

FMAPR.rowIsSticky = function( ele )
{
    return ele.hasClass( 'yes3-row-sticky' );
}

FMAPR.rowIsSelected = function( ele )
{  
    if ( !ele ){
        return false;
    }

    return ele.hasClass( 'yes3-row-selected' );
}

FMAPR.clearInsertionRow = function() 
{
    $('tr.yes3-fmapr-insertion-row').removeClass('yes3-fmapr-insertion-row');
    FMAPR.insertionRowId = "";
}

FMAPR.markInsertionRow = function( theRow )
{
    if ( FMAPR.rowIsSelected(theRow) ){

        return true;
    }
    
    FMAPR.clearInsertionRow();
    theRow.addClass('yes3-fmapr-insertion-row');
    FMAPR.insertionRowId = theRow.attr('id');
}

FMAPR.markRowSelected = function( ele, sticky )
{
    sticky = sticky || false;

    ele.addClass( 'yes3-row-selected' );

    if ( sticky ){
        ele.addClass( 'yes3-row-sticky' );
    }

    FMAPR.clearInsertionRow();
}

FMAPR.markRowUnSelected = function( ele, sticky )
{
    sticky = sticky || false;

    FMAPR.removeSelectionRangeBoundaryMark( ele );

    if ( !FMAPR.rowIsSticky( ele ) || sticky ) {

        ele.removeClass('yes3-row-selected');
        ele.removeClass('yes3-row-sticky');
    }
}

FMAPR.isVerticalLayout = function()
{
    if ( typeof FMAPR.export_specification === "undefined" ){

        return false;
    }
    else return ( FMAPR.export_specification.export_layout === "v" );
}

FMAPR.isHorizontalLayout = function()
{
    if ( typeof FMAPR.export_specification === "undefined" ){

        return false;
    }
    else return ( FMAPR.export_specification.export_layout === "h" );
}

FMAPR.isRepeatedLayout = function()
{
    if ( typeof FMAPR.export_specification === "undefined" ){

        return false;
    }
    else return ( FMAPR.export_specification.export_layout === "r" );
}

FMAPR.fieldInsertionFormReady = function()
{
    let insertionRow = null;
    let insertionRowObjName = "";
    let insertionRowObjType = "";
    let insertionRowEvent = "";
    let insertionLocation = "";

    let formSelector = $("select#yes3-fmapr-fieldinsertion-form");
    let eventSelector = $("select#yes3-fmapr-fieldinsertion-event");

    // clear the progress bar
    $("div#yes3-fmapr-bulk-insertion-progress").css("width", 0);

    if ( FMAPR.selectedRowCount()===1 ){

        insertionRow = FMAPR.firstSelectedRow();
        insertionRowObjType = insertionRow.data("object_type");
        insertionLocation = "The insertion will be <em>before</em> item #" + insertionRow.find("td.yes3-fmapr-row-number").text();

        if ( insertionRowObjType==="form"){

            insertionLocation += ` (form '${insertionRow.data("form_name")}').`;
        }
        else if ( insertionRowObjType==="field"){

            insertionLocation += ` (field '${insertionRow.find("input.yes3-fmapr-input-element").val()}'`;
            insertionLocation += `, event '${insertionRow.find("select.yes3-fmapr-field-event option:selected").val()}').`;
        }
    }

    if ( !insertionRow ){

        insertionRow = theRow = $('tr.yes3-fmapr-data-element').last();
        insertionLocation = "The insertion will be at end of the list.";
    }

    FMAPR.insertionRowId = insertionRow.attr('id');
    
    $("div#yes3-fmapr-bulkinsert-where").html(insertionLocation);

    //$("div#yes3-fmapr-bulk-insertion-progress").css('visibility', 'hidden');
    let progBar = $("div#yes3-fmapr-bulk-insertion-progress");
    progBar.parent().css({"visibility": "hidden"});

    formSelector.empty().append( FMAPR.getFormOptionsHtml() );
    eventSelector.empty().off();
    
    FMAPR.fieldInsertionSetFormSelectListener();
    FMAPR.fieldInsertionSetEventSelectListener();

    formSelector.trigger('change');

    FMAPR.fieldInsertionSetCounterListeners();
    FMAPR.fieldInsertionReportCounts();
}

FMAPR.fieldInsertionExecute = function()
{
    let insertOption = $("input[name=yes3-fmapr-fieldinsertion-option]:checked").val();
    let theRowAfter = null;
    let theRowBeforeWhich = null;

    /**
     * unlike copy/paste, the selected row is the row after the insertion
     */
    if ( FMAPR.insertionRowId ){
        theRowAfter = $(`tr#${FMAPR.insertionRowId}`);
        if ( theRowAfter.length ){

            theRowBeforeWhich = theRowAfter.prev();
            if ( !theRowBeforeWhich.length ){

                theRowBeforeWhich = null;
            }
        }
    }

    let form_name = $("select#yes3-fmapr-fieldinsertion-form").val();
    let event = $("select#yes3-fmapr-fieldinsertion-event").val();

    //console.log('fieldInsertionExecute: FMAPR.insertionRowId=', FMAPR.insertionRowId);
    //console.log('fieldInsertionExecute: theRowBeforeWhich=', theRowBeforeWhich);

    let forms = FMAPR.formsAllowedForEvent( event );

    if ( insertOption==="fields" ) {

        FMAPR.insertFields( theRowBeforeWhich, FMAPR.insertionWrapup );
        return true;
    }

    if ( insertOption === "forms" && form_name === "all" ) {

        FMAPR.insertAllFormsForEvent( event, theRowBeforeWhich );
    }
    else {
            
        FMAPR.addREDCapForm( form_name, event, theRowBeforeWhich);
    }

    FMAPR.insertionWrapup();
}

FMAPR.insertionWrapup = function()
{
    FMAPR.closeFieldInsertionForm();

    FMAPR.renumberRows();

    FMAPR.doExportItemsTableHousekeeping( true );

    FMAPR.enumerateSpecificationElements();

    FMAPR.updateStatus();

    FMAPR.markAsDirty();

    FMAPR.ensureNewFieldRowAtEnd();

    //FMAPR.setRepeatLayoutConstraints();
}

FMAPR.insertFields = function(theRowBeforeWhich, callback)
{
    let i = 0;
    let progBar = $("div#yes3-fmapr-bulk-insertion-progress");
    let progBarParent = progBar.parent();
    let progBarWidth = progBarParent.width();
    let batchSize = 10;

    progBarParent.css({"visibility": "visible"});

    insertNextBatchOfFields();

    function insertNextBatchOfFields()
    {
        let iEnd = Math.min( FMAPR.insertionElements.length, i+batchSize );
        let yes3_fmapr_data_element_name = "";

        while ( i < iEnd ){

            yes3_fmapr_data_element_name = YES3.Functions.addRawREDCapField( FMAPR.insertionElements[i], theRowBeforeWhich, true );

            theRowBeforeWhich = $(`tr#${FMAPR.dataElementRowId(yes3_fmapr_data_element_name)}`);
    
            FMAPR.insertionElements[i].element_name = yes3_fmapr_data_element_name;
            
            i++;
        }

        progBar
            .width(progBarWidth*i/FMAPR.insertionElements.length)
            .html("&nbsp;" + parseInt(100*i/FMAPR.insertionElements.length) + "%")
        ;

        if ( i < FMAPR.insertionElements.length ){
            setTimeout(insertNextBatchOfFields, 0);
        }
        else {
            callback();
        }  
    }
}

FMAPR.insertAllFormsForEvent = function(event, theRowBeforeWhich)
{
    let yes3_fmapr_data_element_name = "";

    let forms = FMAPR.formsAllowedForEvent(event);

    for (let k=0; k<forms.length; k++){

        yes3_fmapr_data_element_name = FMAPR.addREDCapForm( 
            forms[k], 
            event, 
            theRowBeforeWhich
        );

        theRowBeforeWhich = $(`tr#${FMAPR.dataElementRowId(yes3_fmapr_data_element_name)}`);
    }
}

FMAPR.fieldInsertionSetCounterListeners = function()
{
     
    $('select#yes3-fmapr-fieldinsertion-form, select#yes3-fmapr-fieldinsertion-event').on("change", function(){
       
        FMAPR.fieldInsertionReportCounts();
    });
}

FMAPR.fieldInsertionReportCounts = function()
{
    let form_name = $('select#yes3-fmapr-fieldinsertion-form').val();
    let event_id = $('select#yes3-fmapr-fieldinsertion-event').val();

    let statusDiv = $('div#yes3-fmapr-fieldinsertion-counts');

    let counts = FMAPR.enumerateInsertionElements(form_name, event_id);

    statusDiv.html(`Up to ${counts.forms} forms, ${counts.fields} fields and ${counts.columns} columns.`);

}

FMAPR.fieldInsertionSetFormSelectListener = function()
{
    $('select#yes3-fmapr-fieldinsertion-form').off().on("change", function(){

        //-xxxif ( !FMAPR.isVerticalLayout() ){
    
            $("select#yes3-fmapr-fieldinsertion-event").empty().append( FMAPR.getEventOptionsHtml($(this).val()) );
        //}

        if ( $(this).val()==="all" ){

            $(".yes3-fmapr-allforms-only").show();
        }
        else {

            $(".yes3-fmapr-allforms-only").hide();
        }
    });
}

FMAPR.fieldInsertionSetEventSelectListener = function()
{
    $('select#yes3-fmapr-fieldinsertion-event').off().on("change", function(){

        $("select#yes3-fmapr-fieldinsertion-form").empty().append( FMAPR.getFormOptionsHtml($(this).val()) );
    });    
}

FMAPR.enumerateInsertionElements = function(form_name, event_id)
{
    //-xxxif ( FMAPR.isVerticalLayout() ){
    //    event_id = "all";
    //}
    
    if ( !form_name || !event_id ) {
        return {'fields':0, 'columns':0};
    }

    let field_count = 0;
    let column_count = 0;
    let this_form_name = "";
    let i = 0;
    let j = 0;
    let k = 0;
    let forms = [form_name];
    let form_index = 0;

    /**
     * 'all forms' really means 'all forms for the selected event'
     */
    if ( form_name === "all" ) {

        forms = FMAPR.formsAllowedForEvent( event_id );
    }

    FMAPR.insertionElements = [];

    FMAPR.insertionForms = [];

    if ( FMAPR.isVerticalLayout() ){

        column_count = 1;
    }
    else if ( FMAPR.isRepeatedLayout() ){

        column_count = 2;
    }

    for (i=0; i<forms.length; i++){

        this_form_name = forms[i];
        form_index = FMAPR.project.form_index[this_form_name];
        fields = FMAPR.project.form_metadata[form_index].form_fields;
        events = FMAPR.project.form_metadata[form_index].form_events;

        FMAPR.insertionForms.push({
            redcap_form_name: this_form_name,
            redcap_event_id: event_id,
            events: events
        });

        for(j=0; j<fields.length; j++){

            field_name = fields[j];
            
            if ( field_name !== YES3.moduleProperties.RecordIdField ){

                FMAPR.insertionElements.push({
                    redcap_form_name: this_form_name,
                    redcap_field_name: field_name,
                    redcap_event_id: event_id/*,
                    events: events*/
                });
                field_count++;

                if ( event_id==="all" && FMAPR.isHorizontalLayout()  ){

                    column_count += events.length;
                }
                else {

                    column_count++;
                }
            }
        }
        
    }

    return {
        'forms': FMAPR.insertionForms.length, 
        'fields': FMAPR.insertionElements.length, 
        'columns': column_count
    };
}

FMAPR.isSpecificationElement = function(field_name, event_id)
{
    for (let i=0; i<FMAPR.specificationElements.length; i++){

        if ( FMAPR.specificationElements[i].redcap_field_name === field_name
                && FMAPR.specificationElements[i].redcap_event_id === event_id ){

            return true;
        }
    }

    return false;
}

/**
 * populates the FMAPR.specificationElements array (1 row for each output column)
 * 
 * Only one of form_name and field_name are provided,
 * e.g. either 1 field or all fields for the specified form
 * 
 * returns #items added
 * 
 * @param {*} data_element_name 
 * @param {*} form_name 
 * @param {*} field_name 
 * @param {*} event_id_option 
 * @returns 
 */
FMAPR.addREDcapObjectToSpecification = function(data_element_name, form_name, field_name, event_id_option)
{
    let j = 0;
    let k = 0;
    let items = 0;

    //console.log('addREDcapObjectToSpecification', data_element_name, form_name, field_name, event_id_option);

    /**
     * add a single item (redcap field)
     */
    if ( !form_name ){

        if ( typeof FMAPR.project.field_index[field_name] === "number" ){

            form_name = FMAPR.project.field_metadata[FMAPR.project.field_index[field_name]].form_name;
        }

        if (form_name && field_name){

            FMAPR.addREDcapItemToSpecification(data_element_name, form_name, field_name, event_id_option);
        }
        
        return 1;
    }

    if ( form_name !== "all" ){

        let form_index = FMAPR.project.form_index[form_name];

        if ( typeof form_index === "number" ){

            for (k=0; k<FMAPR.project.form_metadata[form_index].form_fields.length; k++){

                items++;
                FMAPR.addREDcapItemToSpecification(data_element_name, form_name, FMAPR.project.form_metadata[form_index].form_fields[k], event_id_option);
            }
        }

        return items;
    }

    /**
     * Add one or more entire forms
     */
    for (j=0; j<FMAPR.project.form_metadata.length; j++){

        // gotta fix this; the entire field list is processed for each form
        for (k=0; k<FMAPR.project.field_metadata.length; k++){

            if ( FMAPR.project.field_metadata[k].form_name === FMAPR.project.form_metadata[j].form_name ){

                items++;
                FMAPR.addREDcapItemToSpecification(data_element_name, FMAPR.project.field_metadata[k].form_name, FMAPR.project.field_metadata[k].field_name, event_id_option);
            }
        }
    }

    return items;
}

FMAPR.addREDcapItemToSpecification = function(data_element_name, form_name, field_name, event_id_option)
{
    let form_index = FMAPR.project.form_index[form_name];

    if ( typeof form_index === "number" ){

        if ( FMAPR.export_specification.export_layout !== "h") {

            FMAPR.specificationElements.push({
                data_element_name: data_element_name,
                data_element_origin: "redcap",
                redcap_field_name: field_name,
                redcap_event_id: ""
            });
        }
        else {
            for (let j=0; j<FMAPR.project.form_metadata[form_index].form_events.length; j++){

                if ( event_id_option === "all" || event_id_option == FMAPR.project.form_metadata[form_index].form_events[j].event_id || FMAPR.export_specification.export_layout !== "h" ){

                    if ( !FMAPR.isSpecificationElement(field_name, FMAPR.project.form_metadata[form_index].form_events[j].event_id) ){

                        FMAPR.specificationElements.push({
                            data_element_name: data_element_name,
                            data_element_origin: "redcap",
                            redcap_field_name: field_name,
                            redcap_event_id: FMAPR.project.form_metadata[form_index].form_events[j].event_id
                        });
                    }
                }
            }
        }
    }
}

FMAPR.getExportItemRows = function()
{
    return $("tr.yes3-fmapr-data-element");
}

FMAPR.exportItemAlreadyExists = function( object_type, object_name, object_event )
{
    let allRows = FMAPR.getExportItemRows();

    let this_object_event = "";
    let this_object_type = "";
    let this_object_name = "";
    let exists = false;

    //console.log("exportItemAlreadyExists", object_type, object_name, object_event, typeof object_event);

    for (let i=0; i<allRows.length; i++){

        try {

            this_object_event = allRows.eq(i).attr("data-object_event");
            this_object_type = allRows.eq(i).attr("data-object_type");
            this_object_name = allRows.eq(i).attr("data-object_name");

            //console.log("---> checking", this_object_type, this_object_name, this_object_event, typeof this_object_event);
            
            if ( this_object_event===object_event && this_object_name===object_name && this_object_type===object_type ){

                return true;
            }
        } catch(e) {

            console.log("exportItemAlreadyExists ERROR", e);
        }
    }

    return false;
}

FMAPR.enumerateSpecificationElements = function()
{
    let allRows = FMAPR.getExportItemRows();

    let data_element_name = "";
    let field_name = "";
    let form_name = "";
    let object_event = "";
    let object_type = "";
    let object_name = "";

    let j = 0;

    FMAPR.specificationElements = [];

    for (let i=0; i<allRows.length; i++){

        form_name = "";

        data_element_name = allRows.eq(i).data("yes3_fmapr_data_element_name");
        data_element_origin = allRows.eq(i).data("element_origin");
        object_event = allRows.eq(i).data("object_event");
        object_type = allRows.eq(i).data("object_type");
        object_name = allRows.eq(i).data("object_name");

        field_name = allRows.eq(i).find('input.yes3-fmapr-input-element').first().val();

        if ( data_element_origin === "redcap" ) {

            if ( object_type === "form" ) {

                field_name = "";
                form_name = object_name;
            }
            else if ( object_type === "field" ) {

                field_name = object_name;
                form_name = "";
                //form_name = FMAPR.getFormForField(field_name);
            }

            //console.log('enumerateSpecificationElements', data_element_name, form_name, field_name, object_event);

            FMAPR.addREDcapObjectToSpecification(data_element_name, form_name, field_name, object_event);
        }
    }

    return {
        'columns': FMAPR.specificationElements.length,
        'elements': allRows.length
    }
}

FMAPR.reportStatus = function()
{
    let s = "";

    if ( YES3.contentLoaded ){

        let counts = FMAPR.enumerateSpecificationElements();

        //s+= `[${FMAPR.project.specification_settings[FMAPR.export_uuid].specification_key}]:`;

        s += `Timestamp: ${FMAPR.export_specification.timestamp}`;

        s += "<br>";

        s += `User: ${FMAPR.export_specification.export_username}`;
        
        s += "<br>";

        s += `Export LogId: ${FMAPR.export_specification.log_id}`;
        
        s += "<br>";

        s += `Export UUID: ${FMAPR.export_specification.export_uuid}`;

        s += "<br>";

        s += ` ${FMAPR.rowCount} item(s), approx ${counts.columns} export column(s)`; 
    }

    $('div#yes3-fmapr-status').html(s);
}

FMAPR.showLayoutItems = function()
{
    if ( FMAPR.isHorizontalLayout() ){
        $(".yes3-fmapr-horizontal-only").css("visibility", "visible");
    }
    else {
        $(".yes3-fmapr-horizontal-only").css("visibility", "hidden");
    }
}

FMAPR.updateStatus = function ()
{
    FMAPR.reportStatus();
    FMAPR.showLayoutItems();
}

FMAPR.formsAllowedForEvent = function(event_id)
{
    event_id = event_id || 'all';

    if ( !FMAPR.project.is_longitudinal ){

        event_id = "all";
    }
    let allowed = true;
    let j = 0;
    let forms = [];

    for (let i=0; i<FMAPR.project.form_metadata.length; i++){

        if ( event_id !== 'all' ){

            allowed = false;

            for (j=0; j<FMAPR.project.form_metadata[i].form_events.length; j++){

                if ( FMAPR.project.form_metadata[i].form_events[j].event_id==event_id ){

                    allowed = true;
                    break;
                }
            }
        }
        
        if ( allowed ){

            forms.push(FMAPR.project.form_metadata[i].form_name);
        }
    }
    return forms;
}

FMAPR.fieldsAllowedForForm = function(form_name)
{
    let fields = [];

    let form_index = FMAPR.project.form_index[form_name];

    if ( typeof form_index !== "number") {

        console.log("fieldsAllowedForForm: invalid form name", form_name)
        return [];
    }

    for (let i=0; i<FMAPR.project.form_metadata[form_index].form_fields.length; i++){

        fields.push(FMAPR.project.form_metadata[form_index].form_fields[i]);
    }

    return fields;
}

FMAPR.getFormOptionsHtml = function(event_id)
{
    event_id = event_id || 'all';

    //console.log('getFormOptionsHtml', 'event_id='+event_id)

    let allowed = true;
    let j = 0;
    let optionHtml = "";

    if ( FMAPR.export_specification.export_layout !== "r" ){

        optionHtml = "\n<option value='all'>all forms</option>";
    }
    else {

        optionHtml = "\n<option value='' disabled>select a form</option>";
    }

    for (let i=0; i<FMAPR.project.form_metadata.length; i++){

        if ( FMAPR.project.form_metadata[i].form_repeating && FMAPR.export_specification.export_layout !== "r"){

            allowed = false;
        }
        else if ( !FMAPR.project.form_metadata[i].form_repeating && FMAPR.export_specification.export_layout === "r" ){
            allowed = false;
        }
        else {

            allowed = ( event_id==="all" );

            if ( !allowed ){
    
                for (j=0; j<FMAPR.project.form_metadata[i].form_events.length; j++){

                    if ( FMAPR.project.form_metadata[i].form_events[j].event_id==event_id ){
                        allowed = true;
                        //break;
                    }
                }
            }
        }

        if ( allowed ){

            optionHtml += `\n<option value='${FMAPR.project.form_metadata[i].form_name}'>${FMAPR.project.form_metadata[i].form_label}</option>`;
        }
    }

    return optionHtml;
}

FMAPR.getEventOptionsHtml = function(form_name)
{
    form_name = form_name || 'all';

    let optionHtml = "<option value='all'>all events for form</option>";

    if ( form_name==="all" ) {

        let eventIDs = Object.getOwnPropertyNames( FMAPR.project.event_metadata );
        
        for (let i=0; i<eventIDs.length; i++){
    
            optionHtml += `\n<option value='${eventIDs[i]}'>${FMAPR.project.event_metadata[eventIDs[i]].event_label}</option>`;
    
        }
    }
    else {

        let form_index = FMAPR.project.form_index[form_name];

        if ( typeof form_index === "number" ){

            if ( FMAPR.project.form_metadata[form_index].form_events.length < 2 ){

                optionHtml = "";
            }

            for (let i=0; i<FMAPR.project.form_metadata[form_index].form_events.length; i++){

                optionHtml += `\n<option value='${FMAPR.project.form_metadata[form_index].form_events[i].event_id}'>${FMAPR.project.form_metadata[form_index].form_events[i].descrip}</option>`;
            }
        }
    }

    return optionHtml;
}

FMAPR.setREDCapElementListeners = function()
{
    $('span.yes3-fmapr-redcap-element').on("click", function(e){
        e.stopPropagation();
        FMAPR.REDCapFieldContextMenu($(this), e);
    })
}

FMAPR.REDCapFieldContextMenu = function( element, e )
{
    //console.log( e );

    let theMenuPanel = YES3.getContextMenuElement();

    let theParent = theMenuPanel.parent();
    let theParentOffset = theParent.offset();
    let theParentWidth = theParent.width();

    let thisRow = element.closest('tr');

    let theMenu = YES3.getContextMenuContentElement();

    let field_name = thisRow.find('input.yes3-fmapr-input-element').first().val();

    let element_name = thisRow.find('span.yes3-fmapr-redcap-element').first().text();

    let event_name = thisRow.find('select.yes3-fmapr-event-select option:selected').first().text();

    let rowSelected = thisRow.hasClass('yes3-row-selected');

    let html = "";

    //$('tr.yes3-row-focused').removeClass('yes3-row-focused');
    //thisRow.addClass('yes3-row-focused');

    //console.log( theParentOffset );

    e.preventDefault();

    html = FMAPR.REDCapFieldContextMenuContent( thisRow.prop('id'), field_name, element_name, event_name, rowSelected );

    if ( !html.length ){

        return false;
    }

    theMenuPanel.css({'top': 0, 'left': 0});

    theMenu.html(html);

    let h = theMenuPanel.outerHeight();
    let w = theMenuPanel.outerWidth();
    let x = e.pageX - theParentOffset.left + 10; // just to the right of the cursor
    let y = e.pageY - theParentOffset.top - h - 3; // just above the cursor

    //console.log('contextmenu: h=' + h + '; w=' + w);

    if ( x+w > theParentWidth ) {
        x = theParentWidth - w - 10; // right-justified
    }

    if ( y < h ) {
        y = e.pageY - theParentOffset.top + 3; // just below the cursor
    }

    YES3.contextMenuOpen(x,y);

    //YES3.showRedPointer( thisRow );

    return false;
}

FMAPR.REDCapFieldContextMenuContent = function( rowId, field_name, element_name, event_name, rowSelected )
{
    let k = FMAPR.cutRowCount();

    if ( !rowSelected && rowId !== FMAPR.insertionRowId ){

        return "";
    }

    let theRow = $(`tr#${rowId}`);

    let theNexRow = theRow.next('tr');

    let html = "";

    html += "<div id='yes3-contextmenu-panel-title' class='yes3-contextmenu-panel-row yes3-drag-handle'>";

    //html += "<div class='yes3-float-left'>" + redcap_objname + "</div><div class='yes3-float-right yes3-ellipsis' style='max-width: 150px'>" + event_name + "</div>";

    html += "command menu</div>";

    html += "<div class='yes3-contextmenu-panel-row'>";

    html += "<table><tbody>";

    html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";

    if ( !rowSelected ){

        html += `<tr class='yes3-command-disabled'><td>cut selection</td><td>ctrl+x</td></tr>`;
        html += `<tr><td><a href="javascript:FMAPR.contextMenuPasteRowSelections();">paste selection</a></td><td>ctrl+v</td></tr>`;
        html += `<tr class='yes3-command-disabled'><td>delete selection</td><td>&nbsp;</td></tr>`;
        html += `<tr class='yes3-command-disabled'><td>clear selection</td><td>ctrl+z</td></tr>`;
    }
    else {
        if ( k===0 ){
            html += `<tr><td><a href="javascript:FMAPR.contextMenuCutRowSelections();">cut selection</a></td><td>ctrl+x</td></tr>`;
        }
        else {

            html += `<tr class='yes3-command-disabled'><td>cut selection</td><td>ctrl+x</td></tr>`;
        }
        html += `<tr class='yes3-command-disabled'><td>paste selection</td><td>ctrl+v</td></tr>`;
        html += `<tr><td><a href="javascript:FMAPR.contextMenuRemoveRowSelections();">delete selection</a></td><td>&nbsp;</td></tr>`;
        html += `<tr><td><a href="javascript:FMAPR.contextMenuClearRowSelections();">clear selection</a></td><td>ctrl+z</td></tr>`;
    }

    html += "</tbody></table>";

    html += "</div>";

    return html;   
}


FMAPR.REDCapFieldContextMenuContent_v050 = function( rowId, field_name, element_name, event_name, rowSelected )
{
    let k = FMAPR.selectedRowCount();

    let theRow = $(`tr#${rowId}`);

    let theNexRow = theRow.next('tr');

    let theNextRowIsSelected = FMAPR.rowIsSelected( theNexRow );

    let redcap_objname = ( field_name ) ? field_name : element_name;

    let html = "";

    html += "<div id='yes3-contextmenu-panel-title' class='yes3-contextmenu-panel-row yes3-drag-handle'>";

    html += "<div class='yes3-float-left'>" + redcap_objname + "</div><div class='yes3-float-right yes3-ellipsis' style='max-width: 150px'>" + event_name + "</div>";

    html += "</div>";

    html += "<div class='yes3-contextmenu-panel-row'>";

    html += "<table><tbody>";

    html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";

    if ( rowSelected){

        html += `<tr><td><a href="javascript:FMAPR.contextMenuDeSelectRow('${rowId}');">unselect field</a></td><td>ctrl+click</td></tr>`;
    }
    else {

        html += `<tr><td><a href="javascript:FMAPR.contextMenuSelectRow('${rowId}', true);">select field</a></td><td>ctrl+click</td></tr>`;
    }

    if ( FMAPR.selectionRangeStartDefined() ) {
        html += `<tr><td><a href="javascript:FMAPR.contextMenuSetRangeEnd('${rowId}');">select field range end</a></td><td>shift+click</td></tr>`;
    }
    else {
        html += `<tr><td><a href="javascript:FMAPR.contextMenuSetRangeStart('${rowId}');">select field range start</td><td>shift+click</td></tr>`;
    }

    if ( k > 0 ){


        if ( !theNextRowIsSelected ){

            html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
            html += `<tr><td><a href="javascript:FMAPR.contextMenuPasteRowSelections('${rowId}');">move ${k} selected field(s)</a></td><td>&nbsp;</td></tr>`;
         
            //html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
            //html += `<tr><td>remove ${k} selected field(s)</td><td>&nbsp;</td></tr>`;
        }

        html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
        html += `<tr><td><a href="javascript:FMAPR.contextMenuRemoveRowSelections();">remove ${k} field selection(s)</a></td><td>&nbsp;</td></tr>`;
        html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
        html += `<tr><td><a href="javascript:FMAPR.contextMenuClearRowSelections();">clear ${k} field selection(s)</a></td><td>&nbsp;</td></tr>`;
    }
    else {

        if ( !rowSelected ){

            html += "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
            //html += `<tr><td><a href="javascript:YES3.Functions.openFieldInsertionForm('${rowId}', '${field_name}', '${event_name}', 'above');">insert form fields above</a></td><td>&nbsp;</td></tr>`;
            html += `<tr><td><a href="javascript:YES3.Functions.openFieldInsertionForm('${rowId}', '${redcap_objname}', '${event_name}', 'below');">insert form fields</a></td><td>&nbsp;</td></tr>`;

        }
    }

    html += "</tbody></table>";

    html += "</div>";

    return html;   
}

FMAPR.contextMenuPasteRowSelections = function(rowId)
{
    let theRows = $('tr.yes3-row-selected');

    let theRow = null;

    let theRowAfter = $(`tr#${FMAPR.insertionRowId}`);

    for (let i=0; i<theRows.length; i++){

        theRow = $(theRows[i]);
        theRow.insertBefore( theRowAfter );
    }

    FMAPR.clearInsertionRow();

    // clear range, cut and copied tags bu leave rows selected
    FMAPR.clearRowTagsButLeaveSelected();

    //console.log('contextMenuPasteRowSelections', theRows);

    FMAPR.markAsDirty();

    YES3.contextMenuClose();

    FMAPR.renumberRows();
}

FMAPR.contextMenuRemoveRowSelections = function()
{
    FMAPR.removeSelections();

    FMAPR.enumerateSpecificationElements();

    FMAPR.updateStatus();

    FMAPR.ensureNewFieldRowAtEnd();

    YES3.contextMenuClose();
}

FMAPR.contextMenuClearRowSelections = function()
{
    FMAPR.clearSelections(true);

    YES3.contextMenuClose();
}

FMAPR.contextMenuCutRowSelections = function()
{
    $('tr.yes3-row-selected')
    .removeClass('yes3-row-copied')
    .addClass('yes3-row-cut');

    YES3.contextMenuClose();
}

FMAPR.contextMenuCopyRowSelections = function()
{
    $('tr.yes3-row-selected')
    .removeClass('yes3-row-cut')
    .addClass('yes3-row-copied');

    YES3.contextMenuClose();
}

FMAPR.contextMenuSelectRow = function( rowId )
{
    let theRow = $(`tr#${rowId}`);

    FMAPR.selectRow( theRow, true );

    YES3.contextMenuClose();
}

FMAPR.contextMenuDeSelectRow = function( rowId )
{
    let theRow = $( `tr#${rowId}` );

    FMAPR.deSelectRow( theRow );

    YES3.contextMenuClose();
}

FMAPR.contextMenuSetRangeStart = function( rowId )
{
    let theRow = $(`tr#${rowId}`);

    FMAPR.selectRow( theRow );

    FMAPR.markSelectionRangeStart( theRow );

    FMAPR.markSelectionRange();

    YES3.contextMenuClose();
}

FMAPR.contextMenuSetRangeEnd = function( rowId )
{
    let theRow = $(`tr#${rowId}`);

    FMAPR.selectRow( theRow );

    FMAPR.markSelectionRangeEnd( theRow );

    FMAPR.markSelectionRange();

    YES3.contextMenuClose();
}

FMAPR.selectRow = function( theRow, sticky )
{
    theRow.addClass('yes3-row-selected');
     
    if ( sticky ) {

        theRow.addClass('yes3-row-sticky');
    }
}

FMAPR.deSelectRow = function( theRow )
{
    theRow.removeClass('yes3-row-selected');

    if ( theRow.hasClass('yes3-row-sticky' )){

        theRow.addClass('yes3-row-sticky');
    }

    YES3.contextMenuClose();
}

FMAPR.firstSelectedRow = function()
{
    return $('tr.yes3-row-selected:first');
}

FMAPR.selectedRowCount = function()
{
    return $('tr.yes3-row-selected').length;
}

FMAPR.cutRowCount = function()
{
    return $('tr.yes3-row-cut').length;
}

FMAPR.copiedRowCount = function()
{
    return $('tr.yes3-row-copied').length;
}

 FMAPR.setValuePickers = function( yes3_fmapr_data_element_name, redcap_field_name ) {
 
    let tbl = FMAPR.getExportItemsTable();

    console.log('setValuePickers', yes3_fmapr_data_element_name, redcap_field_name);
 
    if ( !FMAPR.project.field_metadata[FMAPR.project.field_index[redcap_field_name]].field_valueset.length ) {
       tbl.find(`tr[data-yes3_fmapr_data_element_name=${yes3_fmapr_data_element_name}].yes3-fmapr-lov a.yes3-fmapr-value-picker-toggler`).hide();
       return true;
    }
 
    let value = "";
    let label = "";
 
    let picker_wrappers = tbl.find(`tr[data-yes3_fmapr_data_element_name=${yes3_fmapr_data_element_name}].yes3-fmapr-lov div.yes3-fmapr-value-picker-wrapper`);

    //console.log('setValuePickers:picker_wrappers=', picker_wrappers);
 
    let pkrHtml = "";
 
    for (let i = 0; i < FMAPR.project.field_metadata[FMAPR.project.field_index[redcap_field_name]].field_valueset.length; i++) {
       value = FMAPR.project.field_metadata[FMAPR.project.field_index[redcap_field_name]].field_valueset[i].value;
       label = FMAPR.project.field_metadata[FMAPR.project.field_index[redcap_field_name]].field_valueset[i].label;
       if (i) {
          pkrHtml += "<br />";
       }
       pkrHtml += `<label><input type='checkbox' value='${value}' />${value}. ${label}</label>`;
    }

    //console.log('setValuePickers:pkrHtml=', pkrHtml);
 
    picker_wrappers.html(pkrHtml);
 
    picker_wrappers.find('input[type=checkbox]')
       .off()
       .on('click', function () {
 
          let ctl = $(this).closest("tr").find("input[type=text].yes3-fmapr-input-lov").first();
          let thisVal = $(this).val();
          let checked = $(this).is(':checked');
          let ctlVal = ctl.val();
          let ctlValues = ctlVal.split(',');
 
          if (checked) {
             if (!ctlVal.length) {
                ctlVal = thisVal;
             } else {
                ctlValues.push(thisVal)
                ctlVal = ctlValues.join(',');
             }
          } else {
             if (ctlVal === thisVal) {
                ctlVal = "";
             } else {
                if (ctlValues.length > 1) {
                   let i = ctlValues.indexOf(thisVal);
                   if (i > -1) {
                      ctlValues.splice(i, 1);
                      ctlVal = ctlValues.join(',');
                   }
                }
             }
          }
 
          ctl.val(ctlVal).trigger('change');
 
          //console.log('CLICK', checked, ctlVal, ctlValues);
 
       })
    ;
    tbl.find(`tr[data-yes3_fmapr_data_element_name=${yes3_fmapr_data_element_name}].yes3-fmapr-lov a.yes3-fmapr-value-picker-toggler`).show();
 
 }

FMAPR.setExportItemFieldAutoselectInput_legacy = function( exportItemField ) {

    exportItemField
        .addClass("yes3-fmapr-listener-set")    
        .autocomplete({
            source: ( FMAPR.isRepeatedLayout() ) ? FMAPR.constrainedAutocompleteSource : FMAPR.project.field_autoselect_source,
            minLength: 1,
            select: function(event, ui) {

                if (!ui.item) {
                    exportItemField
                        .val("")
                        .prop("title", "")
                    ;
                    return false;
                }

                exportItemField
                    .val(ui.item.value)
                    .prop("title", ui.item.label)
                ;

                return false;
            }
            , change: function(event, ui){

                exportItemField.prop("title", ui.item.label);

            //    if (ui.item == null || ui.item == undefined) {

            //        exportItemField.val("");
            //        YES3.hello("That is not a valid field name.");
            //    }
            //    else {

            //        FMAPR.REDCapFieldOnChange( exportItemField );
            //    }
            }
        })
        .on("change", function(){

            let fieldName = exportItemField.val();

            if ( !fieldName ){

                return false;
            }

            if ( !FMAPR.project.field_index[fieldName] && fieldName.indexOf('constant:')===-1) {

                YES3.hello(`'${exportItemField.val()}' is not a valid field name.`);
                exportItemField.val("");
            }
            else {

                FMAPR.REDCapFieldOnChange( exportItemField );
            }

        })
    ;
}

FMAPR.setExportItemFieldAutoselectInput = function( exportItemField ) {

    exportItemField
        .addClass("yes3-fmapr-listener-set")    
        .autocomplete({
            source: ( FMAPR.isRepeatedLayout() ) ? FMAPR.constrainedAutocompleteSource : FMAPR.project.field_autoselect_source,
            minLength: 1,
            select: function(event, ui) {

                if (!ui.item) {
                    exportItemField
                        .val("")
                        .prop("title", "")
                    ;
                    return false;
                }

                exportItemField
                    .val(ui.item.value)
                    .prop("title", ui.item.label)
                ;

                return false;
            }
        }
    );
}


FMAPR.setExportItemFieldAutoselectInputs = function() {

    let tbl = FMAPR.getExportItemsTable();

    tbl.find('input[type=text].yes3_fmapr_field_autocomplete:not(.yes3-fmapr-listener-set)')
    .off()
    .on('change', function(){

        let fieldName = $(this).val();

        if ( !fieldName ){

            return false;
        }

        if ( !FMAPR.project.field_index[fieldName] && fieldName.indexOf('constant:')===-1) {

            YES3.hello(`'${$(this).val()}' is not a valid field name.`);
            
            // blank out both the field and the event
            $(this).val("");
            $(this).closest("tr").find("select[data-mapitem=redcap_event_id]").val("");
        }
        else {

            FMAPR.REDCapFieldOnChange( $(this) );
        }
    })    
    .each(function () {
        
        $(this).addClass('yes3-fmapr-listener-set');

        FMAPR.setExportItemFieldAutoselectInput($(this));
    })
}

 FMAPR.setRawREDCapPseudoElementName = function(yes3_fmapr_data_element_name)
 {
    let theRow = $(`tr#${FMAPR.dataElementRowId(yes3_fmapr_data_element_name)}`);

    let row_number = theRow.find("td.yes3-fmapr-row-number").text();
    
    let object_type = theRow.data("object_type");
    
    let object_name = ( object_type==="form" ) ? theRow.data("form_name") : theRow.find('input.yes3-fmapr-input-element').first().val();

    let event_id = theRow.find('select.yes3-fmapr-event-select').first().val();

    let pseudoName = "";

    let editControl = "";

    if ( object_type==="form" ) {

        pseudoName = "form: " + object_name;
    }
    else if ( FMAPR.export_specification.export_layout !== "h" ){

        pseudoName = "field: " + object_name;
    }
    else if ( event_id && object_name ){

        if ( event_id === "all" ){

            pseudoName = "field: " + "*_" + object_name;
        }
        else {

            pseudoName = "field: " + FMAPR.rawRawREDCapElementName( object_name, event_id );
        }
    }

    if ( typeof pseudoName === "string" ){

        if ( pseudoName.length ){

            editControl = `<i class="far fa-edit yes3-fmapr-item-editor" onclick="FMAPR.editExportItem('${row_number}', '${object_type}', '${object_name}', '${event_id}');"></i>`;

            theRow.find('span.yes3-fmapr-redcap-element').html( editControl + "&nbsp;&nbsp;" + pseudoName );
        }
    }
    else {

        console.log('setRawREDCapPseudoElementName: null string', yes3_fmapr_data_element_name);
    }
 }

 FMAPR.rawRawREDCapElementName = function( field_name, event_id )
 {
    if ( field_name === YES3.moduleProperties.RecordIdField || !YES3.moduleProperties.isLongitudinal ){

        return field_name;
    }

    return FMAPR.eventPrefixForEventId(event_id) + "_" + field_name;
 }

 FMAPR.eventPrefixForEventId = function(event_id)
 {
    for ( let j=0; j<FMAPR.event_settings.length; j++) {
        if ( event_id===FMAPR.event_settings[j].event_id ) {
            return FMAPR.event_settings[j].event_prefix;
        }
     }

     return "?";
 }

 FMAPR.eventNameForEventId = function(event_id)
 {
    for ( let j=0; j<FMAPR.event_settings.length; j++) {
        if ( event_id===FMAPR.event_settings[j].event_id ) {
            return FMAPR.event_settings[j].event_name;
        }
     }

     return "?";
 }

 FMAPR.setEventSelectListeners = function()
 {
     $('select.yes3-fmapr-event-select:not(.yes3-fmapr-listener-set)')
     .addClass('yes3-fmapr-listener-set')
     .off().on("change", function(){

        FMAPR.REDCapEventOnChange( $(this) );
     })
 }

 FMAPR.REDCapEventOnChange = function(evnt)
 {
    let yes3_fmapr_data_element_name = evnt.attr('data-yes3_fmapr_data_element_name');

    FMAPR.setRawREDCapPseudoElementName( yes3_fmapr_data_element_name );

    // block this call while building the page
    if ( !FMAPR.buildIsInProgress() ){
        FMAPR.markAsDirty();
    }
 }

 FMAPR.REDCapFieldOnChange = function( fld )
 {
 
    let yes3_fmapr_data_element_name = fld.data('yes3_fmapr_data_element_name');

    let field_name = fld.val();

    let theRow = fld.parent().parent();
    
    let isRawREDCapField = ( theRow.find('span.yes3-fmapr-redcap-element').length > 0 );

    let eventSelect = theRow.find('select.yes3-fmapr-event-select');

    if ( isRawREDCapField ){

        eventSelect.empty();
    }

    if ( !field_name ){

        return false;
    }

    // this is no longer a new field
    if ( theRow.hasClass('yes3-fmapr-new-field') ) {

        theRow.removeClass('yes3-fmapr-new-field');
        theRow.find('i.yes3-invisible').removeClass('yes3-invisible').addClass('yes3-visible');
    }

    // if this hasn't been triggered by an export load then signal the 'save' alert
    if ( !FMAPR.buildIsInProgress()) {

        FMAPR.markAsDirty();
        //FMAPR.setRepeatLayoutConstraints();
        FMAPR.ensureNewFieldRowAtEnd();
    }

    let field_index = FMAPR.project.field_index[field_name];

    if ( typeof field_index !== "number" ){
        return false;
    }

    let form_name = FMAPR.project.field_metadata[field_index].form_name;

    if ( !form_name ){
        return false;
    }

    let form_index = FMAPR.project.form_index[form_name];

    if ( typeof form_index !== "number" ){
        return false;
    }

    //-xxxif ( !isRawREDCapField || FMAPR.export_specification.export_layout==="h" ){
    
        let formEvents = FMAPR.project.form_metadata[form_index].form_events;

        let optionsHtml = "";

        if ( isRawREDCapField && formEvents.length > 1 ){
            optionsHtml += '<option value="all">all events for field</option>';
        }

        for ( let e=0; e<formEvents.length; e++ ){
            optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].descrip}</option>`;                 
        }

        eventSelect.append(optionsHtml);

        if ( !isRawREDCapField ) {

            FMAPR.setValuePickers( yes3_fmapr_data_element_name, field_name );
        }
    //}

    if ( isRawREDCapField ){

        FMAPR.setRawREDCapPseudoElementName( yes3_fmapr_data_element_name );
    }
 }
 
 FMAPR.setLovTogglePriorities = function(yes3_fmapr_data_element_name) {
 
    yes3_fmapr_data_element_name = yes3_fmapr_data_element_name || '';
 
    var dataElementRows;

    let tbl = FMAPR.getExportItemsTable();
 
    if ( yes3_fmapr_data_element_name ) {
       dataElementRows = tbl.find(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);
    } else {
       dataElementRows = tbl.find(`tr.yes3-fmapr-data-element`);
    }
 
    dataElementRows.each(function () {
       let yes3_fmapr_data_element_name = $(this).data('yes3_fmapr_data_element_name');
       let icon = $(this).find('i.fas:first');
 
       if ( icon ) {
          let inputCtl = $(this).find('input[type=text].yes3_fmapr_field_autocomplete:first');
          let entries = 0;
 
          let inputs = tbl.find(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'] input[type=text].yes3-fmapr-input-lov`);
          for (let i = 0; i < inputs.length; i++) {
             if (inputs[i].value.length) {
                entries++;
             }
          }
 
          if (!entries) {
             if (!icon.hasClass('yes3-alert')) {
                icon.addClass('yes3-alert');
             }
          } else {
             if (icon.hasClass('yes3-alert')) {
                icon.removeClass('yes3-alert');
             }
          }
 
       }
 
    })
 }
 
 FMAPR.setLovConstant = function( yes3_fmapr_data_element_name, lov_value ){
 
    let tbl = FMAPR.getExportItemsTable();
    
    let lovCtl = tbl.find(`input[type=text][data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'].yes3-fmapr-input-lov:first` );
 
    lovCtl.val('*').trigger('change');
 }
 /**
  *
  */
FMAPR.setLovConstantExecute = function() {
     
    let tbl = FMAPR.getExportItemsTable();

    inputs = tbl.find(`input[type=text][data-yes3_fmapr_data_element_name='${FMAPR.params.yes3_fmapr_data_element_name}'].yes3-fmapr-input-lov`);

    inputs.each( function () {

        if ( $(this).data('yes3_fmapr_lov_value') !== FMAPR.params.lov_value ){
            $(this).val("");
        }
    })

    FMAPR.markAsDirty();

    FMAPR.params.parentCtl.val( `constant: '${FMAPR.params.lov_value}'` );

    FMAPR.setLovTogglePriorities(FMAPR.params.yes3_fmapr_data_element_name);

//console.log('setLovConstantExecute', FMAPR.params);
}

FMAPR.loadEventSettings = function()
{     
    YES3.requestService( { 
        "request": "getEventSettings"
    }, FMAPR.loadloadEventSettingsCallback, true );
}

FMAPR.loadloadEventSettingsCallback = function( response )
{
    console.log('loadloadEventSettingsCallback', response);

    FMAPR.event_settings = response;

    /**
     * now we load the list of specifications and prepare the editor
     */
     FMAPR.loadSpecifications();
}

FMAPR.loadSpecifications = function( get_removed )
{
    YES3.isBusy( YES3.captions.wait_loading_specifications );

    console.log('loadSpecifications');

    get_removed = get_removed || 0;
    
    YES3.requestService( { 
        "request": "getExportSpecificationList", 
        "get_removed": get_removed
    }, FMAPR.loadSpecificationsCallback, true );
}

FMAPR.loadSpecificationsCallback = function( response )
{
    console.log('loadSpecificationsCallback', response, typeof response);

    let select = FMAPR.getExportUUIDSelect();

    let html = "";

    if ( typeof response === 'object' ){

        html = "<option disabled selected value=''>select an export</option>";

        for (let i=0; i<response.length; i++){

            html += `<option value='${response[i].export_uuid}'>${response[i].export_name}</option>`;
        }
    }
    else {

        html = "<option disabled selected value=''>no exports are defined</option>";
    }

    select.empty().append(html);

    FMAPR.displayInitializationElements();

    YES3.notBusy();

    if ( FMAPR.reloadParms.export_uuid.length ){

        select.val(FMAPR.reloadParms.export_uuid).trigger("change");
    }

    if ( !YES3.initial_help_offered ) {

        YES3.Functions.Help_openPanel( true ); // open HELP popup 'only if hasn't got it'
        YES3.initial_help_offered = true;
    }
}

FMAPR.getExportUUIDSelect = function()
{
    return $('select#export_uuid');
}

/**
 * displays or hides elements that depend on whether there are exports defined
 */
FMAPR.displayInitializationElements = function()
{
    let select = FMAPR.getExportUUIDSelect();

    // there is always at least one option element
    if ( select.find('option').length > 1 ){

        $(".yes3-fmapr-when-initialized").show();
        $(".yes3-fmapr-when-uninitialized").hide(); 
    }
    else {

        $(".yes3-fmapr-when-initialized").hide();
        $(".yes3-fmapr-when-uninitialized").show(); 
    }
}

FMAPR.exportUUIDOnSelect = function()
{
    if ( YES3.dirty ){

        YES3.YesNo(
            "WARNING! There are unsaved changes that will be lost if you open another export. Are you sure that you would like to proceed, and open another export?"
            , FMAPR.proceedWithExportSelection
            , FMAPR.cancelExportSelection
        );
    }
    else {

        FMAPR.proceedWithExportSelection();
    }
}

FMAPR.proceedWithExportSelection = function()
{
    FMAPR.loadSpecification();
}

FMAPR.cancelExportSelection = function()
{
    $("select#export_uuid").val(FMAPR.export_specification.export_uuid);
}

FMAPR.loadSpecification = function( log_id )
{  
    log_id = log_id || 0;

    if ( FMAPR.reloadParms.export_uuid.length ) {

        FMAPR.reloadParms.export_uuid = "";
    }

    YES3.isBusy( YES3.captions.wait_loading_specification );
    
    YES3.requestService( { 
        "request": "getExportSpecification", 
        "export_uuid": $('select#export_uuid').val(),
        "log_id": log_id
    }, FMAPR.loadSpecificationCallback, true );
}

FMAPR.loadSpecificationCallback = function( response )
{
    console.log('loadSpecificationCallback', response, typeof response);

    YES3.notBusy();

    if ( YES3.isEmptyObject(response) ){

        YES3.hello("ERROR: This export specification is empty or corrupted. Cannot open.");
        return false;
    }

    if ( typeof response === "object" ){

        if ( response.permission !== "allowed" ){

            YES3.hello("PERMISSION DENIED: You do not have permission to export at least one form or field that is included in this export specification.");
            return false;
        }

        FMAPR.showDashboardHead();

        //if ( FMAPR.countExportItemsFromSpecification(response) ){

        //    yes3_dashboard_option = 'items';
        //}

        FMAPR.displayDashboardOption(FMAPR.dashboard_option);

        FMAPR.populateSpecificationTables( response );
    }

    return true;
}

FMAPR.displayDashboardOption = function( yes3_dashboard_option )
{
    $(`input[name=yes3-dashboard-options][value=${yes3_dashboard_option}]`).trigger('click');
}

FMAPR.countExportItemsFromSpecification = function( specification )
{
    if ( !specification.export_items_json ){

        return 0;
    }

    try {

        items = JSON.parse( specification.export_items_json );

    } catch (e) {

        return 0;
    }

    return items.length;
}

FMAPR.dashboardOptionHandler = function()
{
    let yes3_dashboard_option = $("input[name=yes3-dashboard-options]:checked").val();

    FMAPR.dashboard_option = yes3_dashboard_option;

    let optionTitle = {
        "settings": "EXPORT SETTINGS",
        "items": "FORMS AND FIELDS TO EXPORT (click&nbsp;<i class='far fa-edit yes3-fmapr-item-editor'></i>&nbsp;to edit)"
    }

    if ( yes3_dashboard_option==="items" ){

        FMAPR.showExportItems();
        FMAPR.hideExportSettings();
        FMAPR.resizeExportItemsTable();
    }
    else if ( yes3_dashboard_option==="settings" ){

        FMAPR.hideExportItems();
        FMAPR.showExportSettings();
    }
    
    $('div.yes3-dashboard-title').html( optionTitle[yes3_dashboard_option] );

    if ( typeof FMAPR.export_specification !== "undefined" && typeof FMAPR.export_specification.export_layout !== "undefined" ){

        YES3.displayActionIcons();
        FMAPR.displayActionIcons();
    }
}

FMAPR.showDashboardHead = function() {
    $("div#yes3-fmapr-dashboard-head").show();
}

FMAPR.hideDashboardHead = function() {
    $("div#yes3-fmapr-dashboard-head").hide();
}

FMAPR.showExportItems = function() {
    FMAPR.getExportItemsTable().show();
}

FMAPR.hideExportItems = function() {
    FMAPR.getExportItemsTable().hide();
}

FMAPR.showExportSettings = function() {
    FMAPR.getExportSettingsContainer().show();
}

FMAPR.hideExportSettings = function() {
    FMAPR.getExportSettingsContainer().hide();
}


FMAPR.populateSpecificationTables = function( specification )
{
    let errors = 0;
    
    FMAPR.markAsBuildInProgress();

    FMAPR.export_specification = specification;

    //try {
        FMAPR.setConstrainedAutocompleteSource(); // alternate source for repeaters
        FMAPR.populateSettingsTable( specification );
        FMAPR.emptyExportItemsTable();
        /**
         * Use the save function to audit the settings just loaded, and mark any blank or bad entries
         * (no save request will be issued)
         */        
        YES3.Functions.saveExportSpecification(true);
        FMAPR.populateExportItemsTable( specification );
    //} catch(e) {
    //    errors++;
    //}

    FMAPR.markAsClean( true );
    FMAPR.updateStatus();
    FMAPR.resizeExportItemsTable();
    FMAPR.markAsBuildCompleted();

    if ( errors ) {

        YES3.hello("Error loading the export specification. Please use the Wayback (undo) to restore an earlier copy.");
        FMAPR.postMessage("Error loading export specification.");
        YES3.notBusy();
    }
    else {

        FMAPR.postMessage("Export specification loaded.");

        if ( FMAPR.reloadParms.wayback ){

            FMAPR.markAsDirty();
            FMAPR.reloadParms.wayback=false;          
        }
    }

    /**
     * disable element insertion actions if there are settings errors
     */
    if ( FMAPR.someBadSettings() || errors ){

        $('i.yes3-fmapr-settings-okay').addClass('yes3-action-disabled');
    }
    else {

        FMAPR.scrollExportItemsTableToNewField();
    }
    
    YES3.displayActionIcons();
    FMAPR.displayActionIcons();
}

FMAPR.displayActionIcons = function()
{
    let yes3_dashboard_option = $("input[name=yes3-dashboard-options]:checked").val();

    if ( FMAPR.export_specification.export_layout==="r" ){

        $('i.yes3-fmapr-display-when-not-repeating').addClass('yes3-action-disabled');
    }
    else {

        $('i.yes3-fmapr-display-when-not-repeating').removeClass('yes3-action-disabled');
    }

    if ( FMAPR.export_specification.export_layout==="r" && $("tr.yes3-fmapr-data-element:not(.yes3-fmapr-new-field)").length > 0 ){

        $('i.yes3-fmapr-bulk-insert').addClass('yes3-action-disabled');
    }
    else {

        $('i.yes3-fmapr-bulk-insert').removeClass('yes3-action-disabled');
    }

    // disable based on permissions
    if ( !YES3.userRights.isDesigner ){

        $('i.yes3-designer-only:not(.yes3-action-disabled)').addClass('yes3-action-disabled');
    }

    // disable based on view
    if ( yes3_dashboard_option!=="items" ){

        $('i.yes3-fmapr-item-view').addClass('yes3-action-disabled');
    }

    if ( !YES3.userRights.exporter ){

        $('i.yes3-exporter-only:not(.yes3-action-disabled)').addClass('yes3-action-disabled');
    }

    // export to host file system
    if ( !FMAPR.project.host_filesystem_exports_enabled ){

        $('i.yes3-export-to-host-filesystem-enabled:not(.yes3-action-disabled)').addClass('yes3-action-disabled');
    }

    // make sure disabled icons are unbound
    YES3.setActionIconListeners( YES3.container() );
}

FMAPR.someBadSettings = function()
{
    
    let exportSettingsContainer = FMAPR.getExportSettingsContainer();

    return exportSettingsContainer.find(".yes3-error").length;
}

FMAPR.clearSettingsTables = function()
{
    let exportSettingsContainer = FMAPR.getExportSettingsContainer();

    let setting = '';

    exportSettingsContainer.find('input, select, textarea').each(function(){

        if ( $(this).attr('data-setting') ) {

            setting = $(this).attr('data-setting');
            
            if ( $(this).attr('type')==='checkbox' ) {

                $(this).prop('checked', false);
            }

            else if( $(this).attr('type') === "radio" ){

                $(this).prop('checked', false);
            }

            else if( $(this).attr('type') !== "radio" ){

                $(this).val('');

                if ( setting==="export_criterion_event" ){

                    $(this).empty();
                }
            }
        }
    })
}

FMAPR.populateSettingsTable = function( specification )
{
    FMAPR.clearSettingsTables();
    
    let exportSettingsContainer = FMAPR.getExportSettingsContainer();

    let setting = "";

    $('input[data-setting=export_uuid]').val( FMAPR.getExportUUID() );

    exportSettingsContainer.find('input, select, textarea').each(function(){

        if ( $(this).attr('data-setting') ) {

            setting = $(this).attr('data-setting');

            if ( $(this).attr('type')==='checkbox' ) {

                if ( specification[setting]==="1" ){

                    $(this).prop('checked', true);
                }
            }

            else if( $(this).attr('type') === "radio" && $(this).val() === specification[setting] ){

                $(this).prop('checked', true);
            }

            else if( $(this).attr('type') !== "radio" ){

                $(this).val( specification[setting] );

                if ( setting==="export_criterion_field" ){

                    $(this).trigger('change');
                }
            }
        }
    })
    
    exportSettingsContainer.find('.yes3-fmapr-settings-block').show();
    exportSettingsContainer.find('.hidden').hide();

    let export_layout_text = "";

    if ( specification['export_layout'] === "h" ){
        export_layout_text = "Horizontal";
    }
    if ( specification['export_layout'] === "v" ){
        export_layout_text = "Vertical";
    }
    if ( specification['export_layout'] === "r" ){
        export_layout_text = "Repeating form";
    }
    $(".yes3-fmapr-export-layout-text").html(export_layout_text);

    /*
    if ( FMAPR.project.beta ){

        $('.yes3-fmapr-beta').show();
    }
    */

    FMAPR.validateAndReportExportUspecJson();

    FMAPR.exportSettingsTableSkipper();
}

FMAPR.emptyExportItemsTable = function()
{
    let tbl = FMAPR.getExportItemsTable();
    tbl.find('tbody').empty();
}

FMAPR.buildExportItemsTableUspecRows = function( specification )
{
    if ( typeof specification.export_uspec_json !== "string" ){

        return true;
    }

    if ( !specification.export_uspec_json.length ){

        return true;
    }

    try {

        let export_uspec_length = JSON.parse( specification.export_uspec_json ).elements.length;

    } catch (e) {

        YES3.hello("Could not parse the upload specification JSON string, so ignoring.");

        return false;
    }
    
    let uSpec = JSON.parse(specification.export_uspec_json);

    console.log(uSpec);

    let tbl = FMAPR.getExportItemsTable();

    let html = "";
    let rowId = "";
    let req = "";
    let element_origin = "specification";
    let lovToggleHtml = "";
    let elementInputHtml = "";
    let eventSelectHtml = "";

     
    for( let i=0; i<uSpec.elements.length; i++ ){

        rowId = FMAPR.dataElementRowId(uSpec.elements[i].name);

        req = uSpec.elements[i].required;
        if ( typeof req === 'undefined' ) req = '0';

        if ( uSpec.elements[i].type==="nominal" ) {
            lovToggleHtml = `<a class='yes3-fmapr-lov-toggler' href='javascript:FMAPR.toggleLovDisplay("${uSpec.elements[i].name}");'><i class='fas fa-plus'></i></a>`;
        } else {
            lovToggleHtml = "&nbsp;";
        }
    
        elementInputHtml = FMAPR.getElementInputHtml( uSpec.elements[i].name, element_origin);
        eventSelectHtml  = FMAPR.getElementEventHtml( uSpec.elements[i].name, element_origin);
    
        html += `<tr id='${rowId}' data-yes3_fmapr_data_element_name='${uSpec.elements[i].name}' data-yes3_fmapr_data_element_description="${uSpec.elements[i].label}" data-spec_type='${uSpec.elements[i].type}' data-required='${req}' data-element_origin='${element_origin}' class='yes3-fmapr-data-element yes3-fmapr-specmap'>`;
        html += `<td class='yes3-fmapr-row-number'>&nbsp;</td>`;
        html += `<td class='yes3-3 yes3-td-left' title='${uSpec.elements[i].label}'><span class='yes3-fmapr-specmap-element'>${uSpec.elements[i].name}</span></td>`;
        html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;
        html += `<td class='yes3-3 yes3-td-middle'>${eventSelectHtml}</td>`;
        html += `<td class='yes3-gutter-right-top yes3-td-right'>${lovToggleHtml}</td>`;
        html += "</tr>";

        if ( uSpec.elements[i].type==="nominal" ) {
    
            for (j=0; j<uSpec.elements[i].valueset.length; j++ ){

                value = uSpec.elements[i].valueset[j].value;
                label = uSpec.elements[i].valueset[j].label;
                yes3_fmapr_data_element_name = uSpec.elements[i].name;

                lovInputHtml = FMAPR.getLovInputHtml( yes3_fmapr_data_element_name, value );

                html += `<tr id="yes3_fmapr_lov_value-${uSpec.elements[i].name}-${value}" data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-yes3_fmapr_lov_value="${value}" data-yes3_fmapr_lov_label="${label}" data-required='${req}'}' class='yes3-fmapr-lov'>`;
                html += `<td class='yes3-fmapr-row-number'>&nbsp;</td>`;
                html += `<td class='yes3-3 yes3-td-left yes3-fmapr-lov' title="${label}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="Make this the value for all FMAPR submissions" href="javascript:FMAPR.setLovConstant('${yes3_fmapr_data_element_name}', '${value}')">${label}</a></td>`;
                html += `<td class='yes3-3 yes3-td-middle yes3-fmapr-lov'>${lovInputHtml}</td>`;
                html += `<td class='yes3-3 yes3-td-middle yes3-fmapr-lov'></td>`;
                html += `<td class='yes3-gutter-right-top yes3-td-right'><a class='yes3-fmapr-value-picker-toggler' href='javascript:FMAPR.toggleValuePickerDisplay("${yes3_fmapr_data_element_name}", "${value}");'><i class='fas fa-plus'></i></a></td>`;
                html += "</tr>";
            }
        }  
    }

    tbl.find('tbody').append(html);

    FMAPR.setLovInputListeners();
}

FMAPR.populateExportItemsTable = function( specification )
{
    let tbl = FMAPR.getExportItemsTable();

    FMAPR.markAsBuildInProgress();

    /**
     * beta feature
     */
    /*
    if ( FMAPR.project.beta ){

        FMAPR.buildExportItemsTableUspecRows( specification );
    }
    */

    //FMAPR.populateExportItemRows( specification );
    FMAPR.populateExportItemRowsV2( specification );

    FMAPR.ensureNewFieldRowAtEndV2(); // the last row is a rapid field form

    FMAPR.resizeExportItemsTable();

    FMAPR.markAsBuildCompleted();

    FMAPR.setRepeatLayoutConstraints();

    FMAPR.renumberRows();

    YES3.displayActionIcons();

    FMAPR.displayActionIcons();

    return true;
}

FMAPR.populateExportItemRowsV2 = function( specification )
{
    let items = null;
    let item = null;
    let tbl = FMAPR.getExportItemsTable();
    let row = null;
    let itemREDCapField = null;
    let itemREDCapValue = null;
    let lovRows = null;
    let lovRow = null;
    let i = 0;
    let j = 0;
    let k = 0;

    if ( !specification.export_items_json ){

        return true;
    }

    try {

        items = JSON.parse( specification.export_items_json );

    } catch (e) {

        return false;
    }

    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    html = "";

    for (i=0; i<items.length; i++){

        item = items[i];

        //console.log("populateExportItemRowsV2", item);

        if ( item.export_item_origin==="redcap" ){

            //object_name = (item.redcap_object_type==="field") ? item.redcap_field_name:item.redcap_form_name;
            if ( FMAPR.exportItemAlreadyExists(
                item.redcap_object_type,
                (item.redcap_object_type==="field") ? item.redcap_field_name:item.redcap_form_name,
                item.redcap_event_id )){

                console.log("Duplicate item not added: ", item.redcap_object_type, item.redcap_form_name, item.redcap_field_name, item.redcap_event_id);
            }
            else {
                //field_name, event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, unsaved, batch
                if ( item.redcap_object_type === "field" ){

                    html += FMAPR.addREDCapFieldV2(item.redcap_field_name, item.redcap_event_id, {}, "", "append", false, true);
                }
                else if ( item.redcap_object_type === "form" ){

                    html += FMAPR.addREDCapFormV2(item.redcap_form_name, item.redcap_event_id, {}, "", "append", false, true);
                }
            }
        }
    }

    fmaprBody.append(html);
    FMAPR.setRowSelectorListenersV2();
}

FMAPR.resetExportItemEditor = function(mode, yes3_fmapr_data_element_name)
{
    mode = mode || "unknown";
    yes3_fmapr_data_element_name = yes3_fmapr_data_element_name || "";
    
    let ed = FMAPR.getExportItemEditor();

    ed.find("input[type='text'], input[type='hidden'], select").val("");
    ed.find("input[type=radio]").prop("checked", false);
    ed.find("input[name=mode]").val(mode);
    ed.find("div#yes3-fmapr-item-editor-mode").html(mode);
    ed.find(".yes3-save-button").css("visibility", "hidden");

    if ( yes3_fmapr_data_element_name ){

        ed.find("input[name=yes3_fmapr_data_element_name]").val(yes3_fmapr_data_element_name);
    }
}

FMAPR.appendExportItem = function()
{
    let mode = "append";
    let yes3_fmapr_data_element_name = "";

    if ( FMAPR.selectedRowCount() === 1 ) {

        yes3_fmapr_data_element_name = $('tr.yes3-row-selected').attr('data-yes3_fmapr_data_element_name');
        mode = "insert";
    }
    
    FMAPR.resetExportItemEditor(mode, yes3_fmapr_data_element_name);

    let theForm = YES3.openPanel("yes3-fmapr-item-editor-panel");
    theForm.find(".yes3-hide-on-open").hide();
}

FMAPR.editREDCapExportItem = function( yes3_fmapr_data_element_name )
{
    FMAPR.clearSelections(true); // deselect all rows
    FMAPR.resetExportItemEditor("edit");

    let theRow = $(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

    let object_type  = theRow.attr("data-object_type");
    let object_name  = theRow.attr("data-object_name");
    let object_event = theRow.attr("data-object_event");

    FMAPR.selectRow(theRow);

    console.log("editREDCapExportItem", object_type, object_event, object_name);

    let theForm = YES3.openPanel("yes3-fmapr-item-editor-panel");

    theForm.find(".yes3-hide-on-open").hide();

    let row_number = theRow.find("td.yes3-fmapr-row-number").text();

    theForm.find("input[name='yes3_fmapr_data_element_name']").val(yes3_fmapr_data_element_name);
    theForm.find("input[name='row_number']").val(row_number);

    theForm.find(`input[type=radio][name='object_type'][value='${object_type}']`)
        .trigger('click')
    ;

    theForm.find(`select[name='object_event']`).val(object_event)
        .trigger('change')
    ;

    theForm.find(`[name='redcap_${object_type}']`).val(object_name);
}

FMAPR.closeItemEditorForm = function()
{
    YES3.closePanel("yes3-fmapr-item-editor-panel");
}

FMAPR.markAsRemoved = function( theRow ) 
{
    theRow.addClass('yes3-fmapr-remove');
}

FMAPR.removeMarkedRows = function() 
{
    $('tr.yes3-fmapr-remove').remove();
}

FMAPR.prepareExportItemEditorForm = function()
{
    let theForm = FMAPR.getExportItemEditor();

    //theForm.find("select[name=object_event]").empty().append(FMAPR.getAllEventOptionsHtml());

    // save button

    theForm.find("input[type='button'].yes3-button-caption-okay").on("click", function(){

        let theForm = FMAPR.getExportItemEditor();

        let object_type = theForm.find("input[name=object_type]:checked").val();
        let object_event = theForm.find("select[name=object_event]").val();
        let object_name = theForm.find(`[name='redcap_${object_type}']`).val();
        let yes3_fmapr_data_element_name = theForm.find(`input[name='yes3_fmapr_data_element_name']`).val();
        let row_number = theForm.find(`input[name='row_number']`).val();
        let mode = theForm.find(`input[name='mode']`).val();
        let insert_as = theForm.find("input[name=insert_as]:checked").val();

        let theRow = {};
        let theRowBeforeWhich = {};

        let saveResult = "";

        // set the 'insert_as' option for field
        if ( object_type==="field" ){

            insert_as = "item";
        }

        console.log(
            "object_type=" + object_type,
            "object_event=" + object_event,
            "object_name=" + object_name,
            "object_name=" + object_name,
            "yes3_fmapr_data_element_name=" + yes3_fmapr_data_element_name,
            "row_number=" + row_number,
            "insert_as=" + insert_as,
            "mode=" + mode
        )

        if ( FMAPR.exportItemAlreadyExists(object_type, object_name, object_event) ) {

            YES3.hello("No can do: this item is already in the export specification.");

            return false;
        }

        FMAPR.closeItemEditorForm();

        //YES3.hello("just checking");

        YES3.isBusy("Please wait.. processing your request.");

        //return true;

        /**
         * In edit mode the same row tag is used for the generated html,
         * so 'theRowBeforeWhich' is removed after insertion, effectively replacing it.
         */
         if ( yes3_fmapr_data_element_name && mode==="edit"){

            theRowBeforeWhich = $(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

            if ( theRowBeforeWhich.length ){

                FMAPR.markAsRemoved( theRowBeforeWhich );
            }
            else {
                // error fallback is to append with new row tag
                theRowBeforeWhich = {};
                yes3_fmapr_data_element_name = "";
            }
        }

        /**
         * in insert mode the 'theRowBeforeWhich' row tag is not used in the generated html,
         * so it's emptied after being used to establish the insertion point.
         */
        else if ( yes3_fmapr_data_element_name && mode==="insert"){

            theRowBeforeWhich = $(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

            if ( !theRowBeforeWhich.length ){

                theRowBeforeWhich = {}; // now we are appending
            }
            yes3_fmapr_data_element_name = "";
        }
        // the fallback is append row with a new row tag
        else {

            mode = "append";
            yes3_fmapr_data_element_name = "";
            theRowBeforeWhich = {};
        }

        //return true;

        if ( insert_as==="item" && object_type==="form" ){

            saveResult = FMAPR.exportItemEditorSave_form(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode);
        }
        else if ( insert_as==="item" && object_type==="field" ){

            saveResult = FMAPR.exportItemEditorSave_field(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode);
        }
        else if ( insert_as==="forms" ){

            saveResult = FMAPR.exportItemEditorSave_forms(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode);
        }
        else if ( insert_as==="fields" ){

            saveResult = FMAPR.exportItemEditorSave_fields(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode);
        }

        console.log("save result:", saveResult);
        
        FMAPR.removeMarkedRows();

        FMAPR.renumberRows();

        //theRow = $(`tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

        FMAPR.clearSelections();

        //FMAPR.selectRow( theRow );

        FMAPR.markAsDirty("Be sure to save your changes ( * - unsaved).");

        FMAPR.resizeExportItemsTable();

        if ( mode==="append" ){

            FMAPR.scrollExportItemsTableToBottom();
        }

        FMAPR.resetExportItemEditor();

        YES3.notBusy();
    })

    // cancel button

    theForm.find("input[type='button'].yes3-button-caption-cancel").on("click", function(){

        FMAPR.closeItemEditorForm();
    })

    // redcap object type selector

    theForm.find("input[type='radio'][name=object_type]").on("click", function(){

        let theTable = $(this).closest("table");
        let object_type = $(this).val();

        if ( object_type==="form" ){

            theTable.find(".yes3-fmapr-form-only").show();
            theTable.find(".yes3-fmapr-field-only").hide();
            theTable.find("input#yes3-fmapr-insert-as-item").prop("checked", true);
        }
        else if ( object_type==="field" ){

            theTable.find(".yes3-fmapr-form-only").hide();
            theTable.find(".yes3-fmapr-field-only").show();
        }

        if ( FMAPR.project.is_longitudinal ){
            theTable.find("tr.yes3-longitudinal-only").show();
        }

        $(this).closest("div.yes3-panel").find(".yes3-save-button").css("visibility", "visible");

        theTable.find("select[name=object_event]")
            .empty()
            .append(FMAPR.getAllEventOptionsHtml())
            .trigger('change')
        ;

        if ( theTable.find(`[name=redcap_${object_type}]`).val() ){

            theTable.find(`[name=redcap_${object_type}]`).trigger("change");
        }
    })

    // event selector
    theForm.find("select[name=object_event]").on("change", function(){

        let objectType = $(this)
            .closest("table")
            .find("input[name=object_type]:checked").val()
        ;

        if ( typeof objectType !== "string" ) return false;

        let event = $(this).val();

        console.log(objectType, event);

        if ( objectType==="form") {
        
            $(this).closest("table").find("select[name=redcap_form]")
                .empty().append(FMAPR.getFormOptionsHtmlForEvent(event));
        }
        else if ( objectType==="field") {
        
            $(this).closest("table").find("input[name=redcap_field]")
                .autocomplete({
                    source: FMAPR.getFieldAutoCompleteSource(event),
                    minLength: 1,
                    select: function(event, ui) {

                        if (!ui.item) {
                            $(this)
                                .val("")
                                .prop("title", "")
                            ;
       
                            return false;
                        }

                        $(this)
                            .val(ui.item.value)
                            .prop("title", ui.item.label)
                        ;

                        return false;
                    }
                });
        }   
    })

    // field selector
    theForm.find("input[name=redcap_field]").on("change", function(){

        let fieldName = $(this).val();

        if ( !fieldName ){

            return false;
        }

        if ( !FMAPR.project.field_index[fieldName] ) {

            YES3.hello(`'${$(this).val()}' is not a valid field name.`);
            $(this).val("");
        }
   })

    // form selector
    theForm.find("select[name=redcap_form]").on("change", function(){

        let thePanel = $(this).closest("div.yes3-panel");
        let form_name = $(this).val();

        if ( !form_name ){

            thePanel.find(".yes3-save-button").css("visibility", "hidden");
            thePanel.find(".yes3-fmapr-all-forms-indicated").hide();   
            return false;
        }
        else {

            if ( form_name==="all" ){

                thePanel.find(".yes3-fmapr-all-forms-indicated").show();   
            }
            else {

                thePanel.find(".yes3-fmapr-all-forms-indicated").hide();   
            }
        }
    })
}

FMAPR.exportItemEditorSave_form = function(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode)
{           
    yes3_fmapr_data_element_name = FMAPR.addREDCapFormV2(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, true);
    return "form item saved as " + yes3_fmapr_data_element_name;
}

FMAPR.exportItemEditorSave_field = function(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, batch)
{           
    return FMAPR.addREDCapFieldV2(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, true, batch);
}

FMAPR.exportItemEditorSave_forms = function(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode)
{     
    let forms = FMAPR.formsAllowedForEvent(object_event);
    let k = 0;
    let d = 0;
    let result = "";

    YES3.isBusy("Please wait.. adding form items to the export.");

    for (let i=0; i<forms.length; i++){
        if ( FMAPR.exportItemAlreadyExists("form", forms[i], object_event) ){
            d++;
            console.log("Duplicate form item not added: ", forms[i], object_event);
        }
        else {
            k++;
            FMAPR.exportItemEditorSave_form(forms[i], object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode);
        }
    }

    YES3.notBusy();

    result = `${k} form(s) were added to the specification. ${d} were ignored because they were already in the export specification.`;

    YES3.hello(result);

    return k;
}

FMAPR.exportItemEditorSave_fields = function(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode)
{     
    let forms = [];
    let k = 0;
    let d = 0;
    let result = "";
    let j=0;
    let fields = [];
    let html = "";

    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    if ( object_name==="all" ) {

        forms = FMAPR.formsAllowedForEvent(object_event);
    }
    else {

        forms = [object_name];
    }

    for (let i=0; i<forms.length; i++){

        if ( FMAPR.formApprovedForLayout( forms[i]) ){

            fields = FMAPR.fieldsAllowedForForm(forms[i]);

            for (j=0; j<fields.length; j++){

                object_name = fields[j];

                if ( FMAPR.exportItemAlreadyExists("field", object_name, object_event) ){
                    d++;
                    console.log("Duplicate field item not added: ", object_name, object_event);
                }
                else {
                    k++;
                    //html += FMAPR.exportItemEditorSave_field(fields[j], object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, true);
                    //FMAPR.exportItemEditorSave_field(fields[j], object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, true);
                    html += FMAPR.addREDCapFieldV2(object_name, object_event, theRowBeforeWhich, yes3_fmapr_data_element_name, mode, true, true);
                }
            }
        }
    }
    
    if ( YES3.isEmpty( theRowBeforeWhich ) ){
        theRowBeforeWhich = FMAPR.getNewFieldRow();
    }
    mode = ( YES3.isEmpty(theRowBeforeWhich) ) ? "append" : "insert";

    if ( mode==="insert" ){
        $( html ).insertBefore( theRowBeforeWhich );
    }
    else {
        fmaprBody.append( html );
    }

    FMAPR.setRowSelectorListenersV2();

    FMAPR.setRepeatLayoutConstraints();

    result = `${k} fields(s) were added to the specification. ${d} was/were ignored because they were already in the export specification.`;

    YES3.hello(result);

    return k;
}

/**
 * DEPRECATED
 * 
 * @param specification 
 * @returns 
 */
FMAPR.populateExportItemRows = function( specification )
{
    let items = null;
    let item = null;
    let tbl = FMAPR.getExportItemsTable();
    let row = null;
    let itemREDCapField = null;
    let itemREDCapValue = null;
    let lovRows = null;
    let lovRow = null;
    let i = 0;
    let j = 0;
    let k = 0;


    if ( !specification.export_items_json ){

        return true;
    }

    try {

        items = JSON.parse( specification.export_items_json );

    } catch (e) {

        return false;
    }

    for (i=0; i<items.length; i++){

        item = items[i];

        if ( item.export_item_origin==="specification" && item.redcap_field_name.length > 0 ){

            row = tbl.find(`tr[data-yes3_fmapr_data_element_name="${item.uspec_element_name}"]`);

            if ( row ){

                itemREDCapField = row.find('input[data-mapitem=redcap_field_name]');

                FMAPR.setExportItemFieldAutoselectInput( itemREDCapField );
                //FMAPR.REDCapFieldOnChange(itemREDCapField, true);

                itemREDCapField.val(item.redcap_field_name).trigger("change");

                if ( item.redcap_event_id ) {

                    row.find('select[data-mapitem=redcap_event_id]').val(item.redcap_event_id);
                }

                if ( item.uspec_element_value_map.length ) {

                    for (j=0; j<item.uspec_element_value_map.length; j++){

                        lovRow = tbl.find(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name="${item.uspec_element_name}"][data-yes3_fmapr_lov_value="${item.uspec_element_value_map[j].uspec_value}"]`);

                        if ( lovRow.length ){

                            itemREDCapValue = lovRow.find(`input[data-mapitem="redcap_field_value"]`);

                            if ( itemREDCapValue ){

                                itemREDCapValue.val(item.uspec_element_value_map[j].redcap_field_value);

                                if ( item.uspec_element_value_map[j].redcap_field_value!=="*" ){

                                    itemREDCapValue.trigger("change");
                                }
                            }
                        }
                    }
                }
            }
        }

        // redcap item
        else {
            if ( item.redcap_object_type === "field" ){

                YES3.Functions.addRawREDCapField(item);
            }
            else if ( item.redcap_object_type === "form" ){

                FMAPR.addREDCapForm(item.redcap_form_name, item.redcap_event_id);
            }
        }
    }
}

FMAPR.setExportSettingsListeners = function()
{
    let settings = FMAPR.getExportSettingsContainer();
    
    settings.find('input, select')
        .off()
        .on('change', function(){

            FMAPR.markAsDirty();

            FMAPR.exportSettingsTableSkipper();
        })
    ;

    FMAPR.setExportSettingCriterionFieldListener(); 
    FMAPR.setExportSettingsLayoutListeners();
    FMAPR.setExportNameListener();
}
/*
FMAPR.setExportSettingsInputListeners = function(parentElement) {

    parentElement.find('input, select')
        .off()
        .on('change', function(){
    
            FMAPR.markAsDirty();

            FMAPR.exportSettingsTableSkipper();
        })
    ;
}

FMAPR.setExportSettingNameListener = function() {

    // change handlers for all input and select elements EXCEPT autocompletes; they have their own
    $('input[data-setting=export_name]')
        .on('change', function(){

            FMAPR.giveThemNames();
        })
    ;
}
*/
FMAPR.setExportSettingCriterionFieldListener = function()
{
    $(`input[data-setting=export_criterion_field]`)
    .autocomplete({
        source: FMAPR.project.field_autoselect_source,
        minLength: 1,
        select: function(event, ui) {

          if (!ui.item) {
              $(this).val("");
              return false;
          }

          $(this).val(ui.item.value);

           return false;
        }
     })
     .change(function(){

        let specificationParent = $(this).closest("table");

        FMAPR.setExportSettingCriterionEventSelectOptions( specificationParent );

        //FMAPR.markAsDirty();
     })
     ;
}

FMAPR.setExportSettingCriterionEventSelectOptions = function()
{
    let eventSelect = $("select#export_criterion_event");

    eventSelect.empty();

    let export_criterion_field = $('input#export_criterion_field').val() || "";

    if ( export_criterion_field ) {

        let optionsHtml = FMAPR.eventSelectOptionsForField( export_criterion_field );

        eventSelect.append(optionsHtml);
    }
}

FMAPR.setExportNameListener = function()
{
    $('input[name=export_name]').on("change", function(){

        let new_export_name = $(this).val();

        // name has changed and exists in the specs list
        if ( new_export_name !== FMAPR.export_specification.export_name 
            && FMAPR.exportNameAlreadyExists(new_export_name) ){

            YES3.hello(`No can do: the export name '${new_export_name}' already exists for this project.`);
            $(this).val(FMAPR.export_specification.export_name);
            return true;
        }
    })
    return false;
}

FMAPR.setExportSettingsLayoutListeners = function() {

    $('input[data-setting=export_layout]')
        .off()
        .on('click', function(){

            //console.log('setExportSettingsLayoutListeners', $(this).val());

            if ( $(this).val()==="r" ){

                $('.yes3-fmapr-repeating-only').show();
            }
            else {

                $('.yes3-fmapr-repeating-only').hide();
            }
    
            FMAPR.markAsDirty();
        })
    ;
}

FMAPR.exportSettingsTableSkipper = function ()
{
    let export_selection = $('input[data-setting=export_selection]:checked').val() || "0";
    //let export_target    = $('input[data-setting=export_target]:checked').val() || "";

    $('table[name=yes3-fmapr-settings').each(function(){
    
        if ( export_selection==="2" ) {
    
            $(this).find(".yes3-fmapr-if-selected").show().removeClass('yes3-fmapr-skipped-over');
        }
        else {
    
            $(this).find(".yes3-fmapr-if-selected").hide().addClass('yes3-fmapr-skipped-over');
        }

        if ( FMAPR.project.host_filesystem_exports_enabled === 1 ){

            $('input[data-setting=export_target]').attr('disabled', false);
        }
        else {

            $('input[data-setting=export_target][value=download]').prop('checked', true);
            $('input[data-setting=export_target]').attr('disabled', true);
        }
        /*   
        if ( export_target==="filesystem" ) {
    
            $(this).find(".yes3-fmapr-target-filesystem-only").show().removeClass('yes3-fmapr-skipped-over');
        }
        else {
    
            $(this).find(".yes3-fmapr-target-filesystem-only").hide().addClass('yes3-fmapr-skipped-over');
        }
    */
    })    
}

FMAPR.renumberRows = function()
{
    //FMAPR.rowCount = $('tr.yes3-fmapr-data-element:not(.yes3-fmapr-new-field)').length;
    
    FMAPR.rowCount = 0;

    $('tr.yes3-fmapr-data-element:not(.yes3-fmapr-new-field)').each(function(){

        FMAPR.rowCount++;
        $(this).find('td.yes3-fmapr-row-number').html(FMAPR.rowCount);
    })
    
}

FMAPR.getExportUUID = function()
{
    return $('select#export_uuid').val();
}

FMAPR.getExportUspecJSON = function()
{
    return $('textarea[data-setting=export_uspec_json').val();
}

FMAPR.getExportUspecJSONV2 = function()
{
    //return $('textarea[data-setting=export_uspec_json').val();
    return "[]";
}

FMAPR.getExportItemsJSONV2 = function()
{
    let items = [];

    const itemProto = {

        export_item_name: "",
        export_item_description: "",
        export_item_origin: "",

        redcap_object_type: "",
        redcap_field_name: "",
        redcap_form_name: "",
        redcap_event_id: "",

        uspec_element_name: "",
        uspec_element_value_map: []
        
    };

    const vMapProto = {
        uspec_value: "",
        redcap_field_value: ""
    }

    let item = null;
    let tbl = FMAPR.getExportItemsTable();
    let itemRows = FMAPR.getExportItemRows();
    let itemRow = null;
    let lovRows = null;
    let lovRow = null;
    let i = 0;
    let j = 0;
    let vMap = null;
    let origin = "";
    let itemError = false;

    //console.log("getExportItemsJSONV2: itemRows", itemRows);

    itemRows.each(function(){

        //console.log("getExportItemsJSONV2:itemRow", $(this));

        item = Object.create(itemProto);
        itemError = false;
        item.export_item_name = $(this).data("yes3_fmapr_data_element_name");
        item.export_item_origin = $(this).data("element_origin");
        item.uspec_element_value_map = [];

        if ( item.export_item_origin==="redcap" ){

            item.redcap_object_type = $(this).data("object_type");

            if ( item.redcap_object_type==="form" ){

                item.redcap_form_name = $(this).data("object_name");

                // must have a form name
                if ( !item.redcap_form_name ){

                    itemError = true;
                }
            }
            else if ( item.redcap_object_type==="field" ){

                item.redcap_field_name =  $(this).data("object_name");

                // must have a form name
                if ( !item.redcap_field_name ){

                    itemError = true;
                }
            }

            item.redcap_event_id =  $(this).data("object_event");

            if ( !item.redcap_event_id && FMAPR.project.is_longitudinal ){

                    itemError = true;
            }

            if ( !itemError ){

                items.push( item );
            }
        }
    });

    console.log('getExportItemsJSON', items);

    return JSON.stringify( items );
}


FMAPR.getExportItemsJSON = function()
{
    let items = [];

    const itemProto = {

        export_item_name: "",
        export_item_description: "",
        export_item_origin: "",

        redcap_object_type: "",
        redcap_field_name: "",
        redcap_form_name: "",
        redcap_event_id: "",

        uspec_element_name: "",
        /*
        uspec_element_type: "",
        uspec_element_label: "",
        */
        uspec_element_value_map: []
        
    };

    const vMapProto = {
        uspec_value: "",
        redcap_field_value: ""
    }

    let item = null;
    let tbl = FMAPR.getExportItemsTable();
    let itemRows = tbl.find('tr.yes3-fmapr-data-element:not(.yes3-fmapr-new-field');
    let itemRow = null;
    let lovRows = null;
    let lovRow = null;
    let i = 0;
    let j = 0;
    let vMap = null;
    let origin = "";
    let itemError = false;

    for (i=0; i<itemRows.length; i++){

        itemRow = itemRows.eq(i);
        item = Object.create(itemProto);
        itemError = false;
        item.export_item_name = itemRow.data("yes3_fmapr_data_element_name");
        item.export_item_origin = itemRow.data("element_origin");
        item.uspec_element_value_map = [];

        if ( item.export_item_origin==="redcap" ){

            item.redcap_object_type = itemRow.data("object_type");

            if ( item.redcap_object_type==="form" ){

                item.redcap_form_name = itemRow.data("form_name");

                // must have a form name
                if ( !item.redcap_form_name ){

                    itemError = true;
                }
            }
            else {

                item.redcap_field_name = itemRow.find("input.yes3-fmapr-input-element").val();

                // must have a form name
                if ( !item.redcap_field_name ){

                    itemError = true;
                }
            }

            /**
             * events are recorded only for horiz layouts
             */
            //-xxxif ( FMAPR.isHorizontalLayout() ) {

                item.redcap_event_id = itemRow.find("select.yes3-fmapr-event-select").val();

                // must have an event
                if ( !item.redcap_event_id ){

                    itemError = true;
                }
            //}
        }
        // uSpec elements
        else {

            item.uspec_element_name  = itemRow.data("yes3_fmapr_data_element_name");
            item.redcap_field_name = itemRow.find("input.yes3-fmapr-input-element").val();
            item.redcap_event_id = itemRow.find("select.yes3-fmapr-event-select").val();

            /**
             * list of values (lov)
             */
            lovRows = tbl.find(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name="${item.uspec_element_name}"]`);

            //console.log(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name="${item.uspec_element_name}"]`);

            //console.log('lovRows -->', lovRows);

            for (j=0; j<lovRows.length; j++){

                lovRow = lovRows.eq(j);

                //console.log('lovRow ---->', lovRow);

                vMap = Object.create(vMapProto);

                vMap.uspec_value = '' + lovRow.data("yes3_fmapr_lov_value");
                vMap.redcap_field_value = '' + lovRow.find("input.yes3-fmapr-input-lov").val();

                if ( vMap.redcap_field_value ){

                    item.uspec_element_value_map.push( vMap );
                }
            }

            /*
            item.uspec_element_type  = itemRow.data("spec_type");
            item.uspec_element_label = itemRow.data("yes3_fmapr_data_element_description");
            */
        }

        if ( !itemError ){

            items.push( item );
        }
    }

    console.log('getExportItemsJSON', items);

    return JSON.stringify( items );
}

FMAPR.eventSelectOptionsForField = function( field_name )
{
    let field_index = FMAPR.project.field_index[field_name];

    let html = "";

    if ( typeof field_index === "number" ){

        let form_name = FMAPR.project.field_metadata[field_index].form_name;
        let form_index = FMAPR.project.form_index[form_name];

        let form_events = FMAPR.project.form_metadata[form_index].form_events;

        for (let i=0; i<form_events.length; i++){

            html += `<option value='${form_events[i].event_id}'>${FMAPR.project.event_metadata[form_events[i].event_id].event_name}</option>`;
        }
    }

    return html;
}

FMAPR.getExportSettingsContainer = function(){

    return $('div#yes3-fmapr-settings');
}

FMAPR.getExportItemsTable = function()
{
    return $("table#yes3-fmapr-export-items-table");
}

FMAPR.getExportItemsTableBody = function()
{
    return $("tbody#yes3-fmapr-export-items-tbody");
}

FMAPR.getExportItemEditor = function()
{
    return $("div#yes3-fmapr-item-editor-panel");
}

/* === AUDITS === */

FMAPR.validateAndReportExportUspecJsonV2 = function()
{
    return true;
}

FMAPR.validateAndReportExportUspecJson = function()
{
    let specTbl = FMAPR.getExportSettingsContainer();

    let specMap = specTbl.find("textarea[name=export_uspec_json]");

    let reportElement = specTbl.find("span.yes3-fmapr-export-uspec-json-length");

    /**
     * specMap is a beta feature (March 2022), and won't be visible if 'beta' is checked 'No' in the EM config. 
     * This prevents errors caused by scanning a bad JSON string saved when 'beta' was checked on in EM config.
     */
    if ( !reportElement.is(':visible') ){

        return true;
    }

    let export_uspec_json = specMap.val();

    if ( !export_uspec_json ){
        reportElement.text( "no upload crosswalk specifications" );
        FMAPR.markAsGood( reportElement );
        return true;
    }
    else {

        try {
            export_uspec_length = JSON.parse( export_uspec_json ).elements.length;
            reportElement.text( export_uspec_length + " elements" );
            FMAPR.markAsGood( reportElement );
            return true;
       } catch (e) {
            reportElement.html( "<span class='yes3-alert'>JSON error!</span>" );
            FMAPR.markAsBad( reportElement );
            return false;
        }
    }
}

$(window).resize( function() {

    FMAPR.resizeExportItemsTable();
})

/*** ANONYMOUS TIME ***/

/**
 * things to do when the settings are loaded
 */
$(document).on('yes3-fmapr.settings', function(){

    console.log("on.yes3-fmapr.settings");

    //return true;

    /**
     * set the settings input listeners
     */
    FMAPR.setExportSettingsListeners();
    FMAPR.prepareExportItemEditorForm();

    /**
     * Start the AJAX chain by loading the event prefixes
     */
     FMAPR.loadEventSettings();
})

/**
 * -------------------------------------------
 * STARTUP ACTIONS - yes3_export_editor plugin
 * -------------------------------------------
 * 
 * getCodeFor() actions
 * --------------------
 * The $module->getCodeFor() function is found in Yes3Trait.php
 * and is applied to all plugin pages
 * 
 * (1) Outputs JS and CSS code to the page in the following orders:
 *     yes3.js, common.js, [plugin name].js
 *     yes3.css, common.css, [plugin name].css
 * 
 * (2) Outputs JS code to create YES3.moduleObject,
 *     which is the the REDCap 'JavaScriptModuleObject' (see REDCap EM dev documentation). 
 *     Includes JS equivalents to the EM Framework getUrl(), log(), tt() and tt_add() functions.
 * 
 * (3) Outputs JS code to create YES3.moduleProperties, which is an
 *     object that includes all public properties from the instantiated 
 *     EM class (Yes3FieldMapper). Mainly useful for service and readme Urls, username,
 *     EM version.
 * 
 * (4) Outputs html/yes3.html, YES3 html relevant to most plugins.
 *     Mainly the standard dialogs hello, yesno and contextmenu.
 * 
 * --------------
 * ONLOAD ACTIONS
 * --------------
 * 
 * (IIFEs from the JS libraries loaded by getCodeFor())
 * 
 * yes3.js
 * -------
 * (1) Color scheme detected, theme toggle listener established
 * (2) csrf token added to jQuery ajax header
 * (3) jQuery draggables set up
 * 
 * common.js
 * ---------
 * (no onload actions)
 * 
 * yes3_export_editor.js (this plugin's script)
 * ------------------------------------------
 * (1) getProjectSettings (located in common.js )
 *      - YES3 startup housekeeping
 *      - triggers 'yes3-fmapr.settings' event
 * 
 * (2) 'yes3-fmapr.settings' event handler (this script)
 *      - local (FMAPR) startup housekeeping
 *      - starts an AJAX chain that retrieves event prefixes
 *        and export specifications, then populates the editors
 *     
 */
$( function () {

    YES3.RegisterApplicationNameSpace('FMAPR');

    YES3.hideContextMenuOnClickOutside();

    FMAPR.makeSortable( FMAPR.getExportItemsTableBody() );

    FMAPR.setSpecialKeyListeners();

    YES3.contentLoaded = false;

    /**
     * located in common.js:
     * (1) Populates FMAPR.project (project, form and field metadata)
     * (2) runs YES3.displayActionIcons
     * (3) Triggers 'yes3-fmapr.settings' event
     */
    FMAPR.getProjectSettings(); 
})
 

 