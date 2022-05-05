FMAPR.insertionRowId = "";

FMAPR.reloadParms = {
    "export_uuid": "",
    "wayback": false
}

FMAPR.tooltips = {

    "event_select": "After a REDCap field or form is selected, this drop-down will include all the REDCap events assigned to it."

}

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

    if ( dupes ){

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
 
YES3.Functions.openExportForm = function()
{
    YES3.openPanel("yes3-fmapr-export-panel");

    if ( FMAPR.export_specification.export_target==="filesystem" && YES3.userRights.export===1 ){

        $(".yes3-fmapr-target-filesystem").show();
    }
    else {
        
        $(".yes3-fmapr-target-filesystem").hide();
    }
}

FMAPR.closeExportForm = function()
{
    YES3.closePanel('yes3-fmapr-export-panel');
}

/**
 * sets: FMAPR.insertionRowId
 * 
 * @param {*} rowId 
 * @param {*} field_name 
 * @param {*} event_name
 */
YES3.Functions.openFieldInsertionForm = function(rowId, field_name, event_name)
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
 * Insert a 'new field' row if none is present AND there isn't a spec error.
 * As a perhaps inefficient precaution, any 
 */
FMAPR.ensureNewFieldRowAtEnd = function()
{
    // not relevant for repeating layouts
    if ( FMAPR.export_specification.export_layout==="r" ){

        return false;
    }    
    
    // get rid of any new field row that has become 'trapped' by a bulk insertion
    $('tr.yes3-fmapr-redcap-field.yes3-fmapr-new-field:not(:last-child)').remove();
    
    if ( !FMAPR.countNewFieldRows() && !FMAPR.someBadSettings() ){

        YES3.Functions.addRawREDCapField({}, {}, false, true, true);
    }
}

YES3.Functions.addRawREDCapField = function( element, theRowBefore, batchMode, noTrashCan, noScroll )
{   

    element = element || {};
    theRowBefore = theRowBefore || {};
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

    //console.log('addRawREDCapField: theRowBefore = [' + theRowBefore + ']');

    let tbl = FMAPR.getExportItemsTable();
     
    let fmaprBody = tbl.find('tbody');

    let yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');

    let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let trashCanClass = 'yes3-visible';

    let elementClass = ( $.isEmptyObject(element) ) ? 'yes3-fmapr-new-field' : '';

    let html = `<tr class='yes3-fmapr-redcap-field yes3-fmapr-data-element yes3-fmapr-sortable ${elementClass}' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" data-yes3_fmapr_data_element_description='(non-specification) REDCap field' id="${rowId}" data-required="0" data-element_origin="redcap" data-object_type="field">`;
    html += `<td class='yes3-3 yes3-td-left' title="(non-specification) REDcap field"><span class='yes3-fmapr-redcap-element'>add a REDCap field to the export</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;

    if ( FMAPR.export_specification.export_layout==="h" ){

        html += `<td class='yes3-3 yes3-td-middle'><span class="yes3-fmapr-horizontal-only">${eventSelectHtml}</span></td>`;
    }
    else {

        html += `<td class='yes3-3 yes3-td-middle'>&nbsp;</td>`;
    }

    if ( noTrashCan ){

        //html += `<td class='yes3-gutter-right-top yes3-td-right'>&nbsp;</td>`;
        trashCanClass = 'yes3-invisible';
    }

    html += `<td class='yes3-gutter-right-top yes3-td-right'><i class='far fa-trash-alt ${trashCanClass}' onclick='FMAPR.removeDataElement("${yes3_fmapr_data_element_name}");'></i></td>`;

    html += "</tr>";

    /**
     * If there is no "row before" the table has no rows so we append.
     */
    if ( $.isEmptyObject(theRowBefore) ){

        fmaprBody.append( html );
    }
    else {

        $( html ).insertAfter( theRowBefore );
    }

    /**
     * If appending an empty element then this is a new, blank field.
     * Otherwise initialize it using the element properties
     */
    if ( $.isEmptyObject(element) ){

        FMAPR.doExportItemsTableHousekeeping();

        if ( !noScroll ) {

            FMAPR.scrollExportItemsTableToBottom();

            $('input#'+FMAPR.elementInputId( yes3_fmapr_data_element_name )).focus();
        }
     }
    else {

        let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

        let itemREDCapField = elementRow.find('input[data-mapitem=redcap_field_name]:first');

        itemREDCapField.val( element.redcap_field_name );

        FMAPR.REDCapFieldOnChange( itemREDCapField, true );

        if ( FMAPR.export_specification.export_layout==="h" ){
        
            let itemREDCapEvent = elementRow.find('select[data-mapitem=redcap_event_id]:first');

            itemREDCapEvent.val(element.redcap_event_id);

            FMAPR.REDCapEventOnChange(itemREDCapEvent, batchMode);
        }
    }

    return yes3_fmapr_data_element_name;
}

FMAPR.addREDCapForm = function( form_name, event, theRowBefore )
{   
    theRowBefore = theRowBefore || {};

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
    html += `<td class='yes3-3 yes3-td-left' title="REDcap form"><span class='yes3-fmapr-redcap-element'>${yes3_fmapr_data_element_name}</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>up to ${field_count} fields</td>`;
    html += `<td class='yes3-3 yes3-td-middle'><span class="yes3-fmapr-horizontal-only">${eventSelectHtml}</span></td>`;
    html += `<td class='yes3-gutter-right-top yes3-td-right'><i class='far fa-trash-alt' onclick='FMAPR.removeDataElement("${yes3_fmapr_data_element_name}");'></i></td>`;
    html += "</tr>";

    /**
     * If there is no "row before" the table has no rows so we append.
     */
    if ( $.isEmptyObject(theRowBefore) ){
        fmaprBody.append( html );
    }
    else {
        $( html ).insertAfter( theRowBefore );
    }

    let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

    let itemREDCapEvent = elementRow.find('select[data-mapitem=redcap_event_id]:first');

    FMAPR.REDCapEventOnChange(itemREDCapEvent);

    itemREDCapEvent.val(event);

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
        "export_uspec_json": FMAPR.getExportUspecJSON(),
        "export_items_json": FMAPR.getExportItemsJSON()
    }

    FMAPR.markAllGood();

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

    for (const settingName in specItems){

        if ( typeof postParams[settingName] === "undefined" ){

            postParams[settingName] =  specItems[settingName].value;
        }

        if ( specItems[settingName].visible && !specItems[settingName].optional && !specItems[settingName].completed ) {

            FMAPR.markAsBad( settingsContainer.find(`[data-setting=${settingName}]`) );
        }
    }

    // special audit: the uSpec JSON string
    FMAPR.validateAndReportExportUspecJson();

    if ( FMAPR.someBadSettings() ){

        YES3.hello("WARNING: at least one error was detected (a required entry is blank, an invalid entry etc). You must fix the indicated error(s) before you may save the export specificaytion.");

        FMAPR.postMessage("You must fix the indicated error(s) before adding fields or saving.", true);

        return true;
    }
 
    if ( !auditOnly ){
        
        console.log( 'saveExportSpecification', postParams );

        YES3.requestService( postParams, FMAPR.saveExportSpecificationCallback, false );
    }
 }
 
FMAPR.saveExportSpecificationCallback = function( response ){
  
    console.log( 'saveExportSpecificationCallback', response );

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
FMAPR.exportExecute = function()
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

    else if ( exportOption==="filesystem"){
        FMAPR.exportData();
    }

    FMAPR.closeExportForm();
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

FMAPR.exportData = function()
{    
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

    FMAPR.Wayback_closeForm();
}

FMAPR.Wayback_closeForm = function()
{

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

    FMAPR.setExportItemFieldAutoselectInputs();

    FMAPR.setEventSelectListeners();
        
    FMAPR.setContextMenuListeners();

    FMAPR.setREDCapElementListeners();

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
    let gutterWidth = 30;
    let scrollbarWidth = 20;

    let fmaprTable = FMAPR.getExportItemsTable();

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

    let cellWidth3 = (tableWidth - scrollbarWidth - gutterWidth) / 3;

    //fmaprTable.css({'width': tableWidth+'px', 'height': tableHeight+'px'});
    fmaprTable.css({'height': tableHeight+'px'});

    fmaprTableBody.css({'height': bodyHeight+'px'});

    fmaprTable.find('th.yes3-3, td.yes3-3').css({'width': cellWidth3+'px', 'max-width': cellWidth3+'px'});
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

    let optionsHtml = '<option value="all">all events</option>';

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

    for ( let e=0; e<formEvents.length; e++ ){
        optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].event_label}</option>`;                 
    }

    let typeClass = FMAPR.itemTypeClass(element_origin);

    let id = FMAPR.elementEventId( form_name );

    let html = `<select id=${id} class='yes3-fmapr-event-select ${typeClass} yes3-fmapr-item yes3-fmapr-form-event' data-export_uuid='${FMAPR.getExportUUID()}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id' data-element_origin='${element_origin}'/>${optionsHtml}</select>`;
    
    return html;
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

FMAPR.setContextMenuListeners = function()
{
    $('tr.yes3-fmapr-redcap-field:not(.yes3-fmapr-new-field), tr.yes3-fmapr-redcap-form')
        .off()
        .on("contextmenu", function(e){
            //console.log( 'contextmenu', e );

            FMAPR.REDCapFieldContextMenu($(this), e);

            return false;
        })
        .on("click", function(e){

            //console.log( 'click', e );

            if ( e.shiftKey ){

                e.preventDefault();

                //FMAPR.toggleSelected( $(this) );

                if ( FMAPR.isSelectionRangeStart($(this)) && FMAPR.isSelectionRangeEnd($(this)) ) {

                    FMAPR.clearSelectionRange( true );
                }
                else {

                    FMAPR.markRowSelected( $(this) );
                    
                    if ( !FMAPR.selectionRangeStartDefined() ){
                        FMAPR.markSelectionRangeStart( $(this) );
                    }
                    else {
                        FMAPR.markSelectionRangeEnd( $(this) );
                    }

                    FMAPR.markSelectionRange();
                }

                e.stopPropagation();

                YES3.contextMenuClose();

                return false;
            }
            else if ( e.ctrlKey ){

                e.preventDefault();

                FMAPR.toggleSelected( $(this), true );

                YES3.contextMenuClose();

                e.stopPropagation();

                return false;
            }

            return true;
            //return false;
        })
    ;
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
    //else {
    //    FMAPR.markRowSelected( $('tr.yes3-selection-range-start') );
    //    FMAPR.markRowSelected( $('tr.yes3-selection-range-end') );
    //}
}

FMAPR.removeSelections = function( stickyToo )
{
    $("tr.yes3-row-selected").off().remove();

    FMAPR.markAsDirty();  
}

FMAPR.clearSelections = function( stickyToo )
{
    stickyToo = stickyToo || false;

    FMAPR.clearSelectionRangeBoundaries();

    $("tr.yes3-row-selected").removeClass("yes3-row-selected");  

    if ( stickyToo ){

        $("tr.yes3-row-sticky").removeClass("yes3-row-sticky");       
    }
}

FMAPR.clearSelectionRangeBoundaries = function() {

    $("tr.yes3-selection-range-start").removeClass("yes3-selection-range-start");
    $("tr.yes3-selection-range-end").removeClass("yes3-selection-range-end");
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

FMAPR.markRowSelected = function( ele, sticky )
{
    sticky = sticky || false;

    ele.addClass( 'yes3-row-selected' );

    if ( sticky ){
        ele.addClass( 'yes3-row-sticky' );
    }
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

FMAPR.fieldInsertionFormReady = function(field_name, event_name)
{
    if ( FMAPR.isVerticalLayout() ){

        let formSelector = $("select#yes3-fmapr-fieldinsertion-form");

        let eventSelector = $("select#yes3-fmapr-fieldinsertion-event");

        eventSelector.prop('disabled', true);
        $("input[type=radio][name='yes3-fmapr-fieldinsertion-org']").prop('disabled', true);

        formSelector.empty().append( FMAPR.getFormOptionsHtml() );
        eventSelector.empty().off();

        $(".yes3-fmapr-horizontal-only").hide();

        FMAPR.fieldInsertionSetCounterListeners();
        FMAPR.fieldInsertionReportCounts();
    }
    else {

        $(".yes3-fmapr-horizontal-only").show();

        $("select#yes3-fmapr-fieldinsertion-event").prop('disabled', false);
        $("input[type=radio][name='yes3-fmapr-fieldinsertion-org']").prop('disabled', false);

        $("div#yes3-fmapr-fieldinsertion-direction").html(`[${field_name}, ${event_name}]`);

        $("input[type=radio][name='yes3-fmapr-fieldinsertion-org']")
            .off()
            .on("click", function(){

                let orgBlock = $('tr#yes3-fmapr-fieldinsertion-org-block');
    
                let formBlock = $('tr#yes3-fmapr-fieldinsertion-form-block');
                
                let eventBlock = $('tr#yes3-fmapr-fieldinsertion-event-block');
                
                let org = $("input[type=radio][name='yes3-fmapr-fieldinsertion-org']:checked").val();

                let formSelector = $("select#yes3-fmapr-fieldinsertion-form");

                let eventSelector = $("select#yes3-fmapr-fieldinsertion-event");
                
                if ( org==="form"){
                    formBlock.insertAfter( orgBlock );
                    eventBlock.insertAfter( formBlock );
                    formSelector.empty().append( FMAPR.getFormOptionsHtml() );
                    eventSelector.empty().off();
                    FMAPR.fieldInsertionSetFormSelectListener();
                    formSelector.trigger('change');
                }
                else {
                    eventBlock.insertAfter( orgBlock );                
                    formBlock.insertAfter( eventBlock );
                    eventSelector.empty().append( FMAPR.getEventOptionsHtml() );
                    formSelector.off().empty();
                    FMAPR.fieldInsertionSetEventSelectListener();
                    eventSelector.trigger('change');
                }

                FMAPR.fieldInsertionSetCounterListeners();
                FMAPR.fieldInsertionReportCounts();
            })
        ;

        $('input[type=radio]#yes3-fmapr-fieldinsertion-org-form').trigger("click");
    }
}

FMAPR.fieldInsertionExecute = function()
{
    let organization = $("input[name=yes3-fmapr-fieldinsertion-org]:checked").val();
    let insertOption = $("input[name=yes3-fmapr-fieldinsertion-option]:checked").val();
    let theRowBefore = null;

    if ( FMAPR.insertionRowId ){
        theRowBefore = $(`tr#${FMAPR.insertionRowId}`); // set by openFieldInsertionForm
    }

    let form_name = $("select#yes3-fmapr-fieldinsertion-form").val();
    let event = $("select#yes3-fmapr-fieldinsertion-event").val();

    if ( insertOption==="fields" ) {

        FMAPR.insertFields( theRowBefore, FMAPR.insertionWrapup );
        return true;
    }

    if ( insertOption === "forms" && form_name === "all" ) {

        FMAPR.insertAllFormsForEvent( event, theRowBefore );
    }
    else {
            
        FMAPR.addREDCapForm( form_name, event, theRowBefore);
    }

    FMAPR.insertionWrapup();
}

FMAPR.insertionWrapup = function()
{
    FMAPR.closeFieldInsertionForm();

    FMAPR.doExportItemsTableHousekeeping( true );

    FMAPR.enumerateSpecificationElements();

    FMAPR.updateStatus();

    FMAPR.markAsDirty();

    FMAPR.ensureNewFieldRowAtEnd();
}

FMAPR.insertFields = function(theRowBefore, callback)
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

            yes3_fmapr_data_element_name = YES3.Functions.addRawREDCapField( FMAPR.insertionElements[i], theRowBefore, true );

            theRowBefore = $(`tr#${FMAPR.dataElementRowId(yes3_fmapr_data_element_name)}`);
    
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

FMAPR.insertAllFormsForEvent = function(event, theRowBefore)
{
    let yes3_fmapr_data_element_name = "";
    let allowed = false;
    let j=0;

    for (let k=0; k<FMAPR.project.form_metadata.length; k++){

        // insert form if registered for this event
        allowed = ( event === "all" );

        //if ( !allowed ){

            for (j=0; j<FMAPR.project.form_metadata[k].form_events.length && !allowed; j++){

                if ( event == FMAPR.project.form_metadata[k].form_events[j].event_id ) {
                    allowed = true;
                }
            }
        //}

        if ( allowed ){

            yes3_fmapr_data_element_name = FMAPR.addREDCapForm( 
                FMAPR.project.form_metadata[k].form_name, 
                event, 
                theRowBefore
            );

            theRowBefore = $(`tr#${FMAPR.dataElementRowId(yes3_fmapr_data_element_name)}`);
        }
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

    // statusDiv.html(`up to ${counts.fields} fields, ${counts.columns} export columns will be inserted.`);

}

FMAPR.fieldInsertionSetFormSelectListener = function()
{
    $('select#yes3-fmapr-fieldinsertion-form').off().on("change", function(){

        if ( !FMAPR.isVerticalLayout() ){
    
            $("select#yes3-fmapr-fieldinsertion-event").empty().append( FMAPR.getEventOptionsHtml($(this).val()) );
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
    if ( FMAPR.isVerticalLayout() ){
        event_id = "all";
    }
    
    if ( !form_name || !event_id ) {
        return {'fields':0, 'columns':0};
    }

    let fields = 0;
    let columns = 0;
    let this_field_name = "";
    let this_event_id = "";
    let this_form_name = "";
    let this_form_index = 0;
    let element_events = [];
    let element_event_id = "";
    let k = 0;

    FMAPR.insertionElements = [];

    for (let i=0; i<FMAPR.project.field_metadata.length; i++){

        this_field_name = FMAPR.project.field_metadata[i].field_name;
        this_form_name = FMAPR.project.field_metadata[i].form_name;
        this_form_index = FMAPR.project.form_index[this_form_name];

        if ( form_name === this_form_name || form_name === "all" ){

            element_events = [];

            for (j=0; j<FMAPR.project.form_metadata[this_form_index].form_events.length; j++){

                this_event_id = FMAPR.project.form_metadata[this_form_index].form_events[j].event_id;

                if ( event_id === this_event_id || event_id === "all" ){

                    element_events.push(this_event_id);
                }
            }

            if ( element_events.length ){

                /**
                 * if there is only one event for this field, 'all' is not allowed
                 */
                element_event_id = ( element_events.length===1 ) ? element_events[0] : event_id;

                if ( !FMAPR.isSpecificationElement(this_field_name, element_event_id) ){

                    fields++;

                    if ( FMAPR.isVerticalLayout() ){

                        columns++;
                    }
                    else {

                        columns += element_events.length;
                    }

                    FMAPR.insertionElements.push({
                        redcap_field_name: this_field_name,
                        redcap_event_id: element_event_id,
                        events: element_events
                    });
                }
            }
        }
    }

    return {'fields': fields, 'columns': columns};
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

FMAPR.enumerateSpecificationElements = function()
{
    let allRows = $("tr.yes3-fmapr-data-element");

    let data_element_name = "";
    let field_name = "";
    let event_id_option = "";
    let form_name = "";
    let object_type = "";

    let j = 0;

    FMAPR.specificationElements = [];

    for (let i=0; i<allRows.length; i++){

        form_name = "";

        data_element_name = allRows.eq(i).data("yes3_fmapr_data_element_name");

        data_element_origin = allRows.eq(i).data("element_origin");

        field_name = allRows.eq(i).find('input.yes3-fmapr-input-element').first().val();

        event_id_option = allRows.eq(i).find('select.yes3-fmapr-event-select').first().val();

        if ( data_element_origin === "redcap" ) {

            object_type = allRows.eq(i).data("object_type");

            if ( object_type === "form" ) {

                field_name = "";
                form_name = allRows.eq(i).data("form_name");
            }

            //console.log('enumerateSpecificationElements', data_element_name, form_name, field_name, event_id_option);

            FMAPR.addREDcapObjectToSpecification(data_element_name, form_name, field_name, event_id_option);
        }
        else {

            FMAPR.specificationElements.push({
                data_element_name: data_element_name,
                data_element_origin: data_element_origin,
                redcap_field_name: field_name,
                redcap_event_id: event_id_option,
            });
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

        s += ` ${counts.elements} element(s), approx ${counts.columns} export column(s)`; 
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

    let optionHtml = "<option value='all'>all events</option>";

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

    YES3.showRedPointer( thisRow );

    return false;
}

FMAPR.REDCapFieldContextMenuContent = function( rowId, field_name, element_name, event_name, rowSelected )
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
            html += `<tr><td><a href="javascript:FMAPR.contextMenuMoveRowSelections('${rowId}');">move ${k} selected field(s)</a></td><td>&nbsp;</td></tr>`;
         
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

FMAPR.contextMenuMoveRowSelections = function(rowId)
{
    let theRows = $('tr.yes3-row-selected');

    let theRow = null;

    let theRowBefore = $(`tr#${rowId}`);

    for (let i=0; i<theRows.length; i++){

        theRow = $(theRows[i]);
        theRow.insertAfter( theRowBefore );
        theRowBefore = theRow;
    }

    // clear range tags bu leave rows selected
    FMAPR.clearSelectionRangeBoundaries();

    //console.log('contextMenuMoveRowSelections', theRows);

    FMAPR.markAsDirty();

    YES3.contextMenuClose();
}

FMAPR.contextMenuRemoveRowSelections = function()
{
    FMAPR.removeSelections();

    FMAPR.enumerateSpecificationElements();

    FMAPR.updateStatus();

    YES3.contextMenuClose();
}

FMAPR.contextMenuClearRowSelections = function()
{
    FMAPR.clearSelections(true);

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

FMAPR.selectedRowCount = function()
{
    return $('tr.yes3-row-selected').length;
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

FMAPR.setExportItemFieldAutoselectInput = function( exportItemField ) {

    exportItemField
        .addClass("yes3-fmapr-listener-set")    
        .autocomplete({
            source: FMAPR.project.field_autoselect_source,
            minLength: 1,
            select: function(event, ui) {

                if (!ui.item) {
                    exportItemField.val("");
                    return false;
                }

                exportItemField.val(ui.item.value);

                return false;
            }
            //, change: function(event, ui){

            //    if (ui.item == null || ui.item == undefined) {

            //        exportItemField.val("");
            //        YES3.hello("That is not a valid field name.");
            //    }
            //    else {

            //        FMAPR.REDCapFieldOnChange( exportItemField );
            //    }
            //}
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

FMAPR.setExportItemFieldAutoselectInputs = function() {

    let tbl = FMAPR.getExportItemsTable();

    tbl.find('input[type=text].yes3_fmapr_field_autocomplete:not(.yes3-fmapr-listener-set)')    
    .each(function () {
        
        $(this).addClass('yes3-fmapr-listener-set');

        FMAPR.setExportItemFieldAutoselectInput($(this));
    })
}

 FMAPR.setRawREDCapPseudoElementName = function(yes3_fmapr_data_element_name)
 {
    let theRow = $(`tr#${FMAPR.dataElementRowId(yes3_fmapr_data_element_name)}`);
    
    let object_type = theRow.data("object_type");
    
    let object_name = ( object_type==="form" ) ? theRow.data("form_name") : theRow.find('input.yes3-fmapr-input-element').first().val();

    let event_id = theRow.find('select.yes3-fmapr-event-select').first().val();

    let pseudoName = "";

    if ( object_type==="form" ) {

        pseudoName = "form: " + object_name;
    }
    else if ( FMAPR.export_specification.export_layout !== "h" ){

        pseudoName = object_name;
    }
    else if ( event_id && object_name ){

        if ( event_id === "all" ){

            pseudoName = "*_" + object_name;
        }
        else {

            pseudoName = FMAPR.rawRawREDCapElementName( object_name, event_id );
        }
    }

    if ( typeof pseudoName === "string" ){

        if ( pseudoName.length ){

            theRow.find('span.yes3-fmapr-redcap-element').html( pseudoName );
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

    if ( !isRawREDCapField || FMAPR.export_specification.export_layout==="h" ){
    
        let formEvents = FMAPR.project.form_metadata[form_index].form_events;

        let optionsHtml = "";

        if ( isRawREDCapField && formEvents.length > 1 ){
            optionsHtml += '<option value="all">all events</option>';
        }

        for ( let e=0; e<formEvents.length; e++ ){
            optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].descrip}</option>`;                 
        }

        eventSelect.append(optionsHtml);

        if ( !isRawREDCapField ) {

            FMAPR.setValuePickers( yes3_fmapr_data_element_name, field_name );
        }
    }

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

    if ( FMAPR.reloadParms.export_uuid.length ){

        select.val(FMAPR.reloadParms.export_uuid).trigger("change");

        FMAPR.reloadParms.export_uuid = "";
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

FMAPR.loadSpecification = function( log_id )
{  
    log_id = log_id || 0;
    
    YES3.requestService( { 
        "request": "getExportSpecification", 
        "export_uuid": $('select#export_uuid').val(),
        "log_id": log_id
    }, FMAPR.loadSpecificationCallback, true );
}

FMAPR.loadSpecificationCallback = function( response )
{
    console.log('loadSpecificationCallback', response, typeof response);

    if ( typeof response === "object" ){

        if ( response.permission !== "allowed" ){

            YES3.hello("PERMISSION DENIED: You do not have permission to export at least one form or field that is included in this export specification.");
            return false;
        }

        FMAPR.populateSpecificationTables( response );
    }
}

FMAPR.populateSpecificationTables = function( specification )
{
    FMAPR.markAsBuildInProgress();

    FMAPR.export_specification = specification;

    FMAPR.populateSettingsTable( specification );

    FMAPR.emptyExportItemsTable();

    /**
     * Use the save function to audit the settings just loaded
     * (no save request will be issued)
     */
    YES3.Functions.saveExportSpecification(true);

    FMAPR.populateExportItemsTable( specification );

    FMAPR.markAsClean( true );
    FMAPR.updateStatus();

    FMAPR.resizeExportItemsTable();

    FMAPR.postMessage("Export specification loaded.");

    FMAPR.markAsBuildCompleted();

    if ( FMAPR.reloadParms.wayback ){

        FMAPR.markAsDirty();

        FMAPR.reloadParms.wayback=false;
    }

    YES3.displayActionIcons();
    FMAPR.displayActionIcons();

    /**
     * disable element insertion actions if there are settings errors
     */
    if ( FMAPR.someBadSettings() ){

        $('i.yes3-fmapr-settings-okay').addClass('yes3-action-disabled');
    }
    else {

        FMAPR.scrollExportItemsTableToNewField();
    }
}

FMAPR.displayActionIcons = function()
{
    if ( FMAPR.export_specification.export_layout==="r" ){

        $('i.yes3-fmapr-display-when-not-repeating').addClass('yes3-action-disabled');

        
    }
    else {

        $('i.yes3-fmapr-display-when-not-repeating').removeClass('yes3-action-disabled');
    }

    if ( FMAPR.export_specification.export_layout==="r" && $("tr[data-object_type=form]").length > 0 ){

        $('i.yes3-fmapr-bulk-insert').addClass('yes3-action-disabled');
    }
    else {

        $('i.yes3-fmapr-bulk-insert').removeClass('yes3-action-disabled');
    }
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

    if ( FMAPR.project.beta ){

        $('.yes3-fmapr-beta').show();
    }

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

    FMAPR.buildExportItemsTableUspecRows( specification );

    FMAPR.populateExportItemRows( specification );

    tbl.show();

    FMAPR.doExportItemsTableHousekeeping();

    FMAPR.markAsBuildCompleted();

    FMAPR.ensureNewFieldRowAtEnd();

    YES3.displayActionIcons();
    FMAPR.displayActionIcons();

    return true;
}

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

FMAPR.getExportUUID = function()
{
    return $('select#export_uuid').val();
}

FMAPR.getExportUspecJSON = function()
{
    return $('textarea[data-setting=export_uspec_json').val();
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

    for (i=0; i<itemRows.length; i++){

        itemRow = itemRows.eq(i);

        item = Object.create(itemProto);

        item.export_item_name = itemRow.data("yes3_fmapr_data_element_name");

        item.export_item_origin = itemRow.data("element_origin");

        item.uspec_element_value_map = [];

        if ( item.export_item_origin==="redcap" ){

            item.redcap_object_type = itemRow.data("object_type");

            if ( item.redcap_object_type==="form" ){

                item.redcap_form_name = itemRow.data("form_name");
            }
            else {

                item.redcap_field_name = itemRow.find("input.yes3-fmapr-input-element").val();
            }

            /**
             * events are recorded only for horiz layouts
             */
            if ( FMAPR.isHorizontalLayout() ) {

                item.redcap_event_id = itemRow.find("select.yes3-fmapr-event-select").val();
            }
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

        items.push( item );
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

/* === AUDITS === */

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
    FMAPR.exportSettingsTableSkipper();

    /**
     * Start the AJAX chain by loading the event prefixes
     */
     FMAPR.loadEventSettings();
})

$(window).resize( function() {

    FMAPR.resizeExportItemsTable();
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

    YES3.contentLoaded = false;

    /**
     * located in common.js:
     * (1) Populates FMAPR.project (project, form and field metadata)
     * (2) runs YES3.displayActionIcons
     * (3) Triggers 'yes3-fmapr.settings' event
     */
    FMAPR.getProjectSettings(); 
})
 

 