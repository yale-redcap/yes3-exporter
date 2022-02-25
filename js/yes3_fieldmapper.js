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
    if ( !rowId ){
        theRow = $('tr.yes3-fmapr-data-element').last();
        FMAPR.insertionRowId = theRow.attr('id');
        field_name = theRow.find('input.yes3-fmapr-input-element').first().val();
        let theEvent = theRow.find('select.yes3-fmapr-event-select').first();
        event_name = theEvent.find("option:selected").text();
        FMAPR.scrollFieldMapperTableToBottom();
    }
    else {
        FMAPR.insertionRowId = rowId;
        theRow = $(`tr#${rowId}`);
    }

    let x = theRow.offset().left - theParent.offset().left;
    let y = theRow.offset().top - theParent.offset().top;

    YES3.showRedPointer( theRow );

    if ( y > $(window).innerHeight()/2 ) {
        y = y - thePanel.outerHeight();
    }
    else {
        y = y + theRow.outerHeight();
    }

    YES3.contextMenuClose();

    YES3.openPanel("yes3-fmapr-fieldinsertion-panel", false, x, y);

    FMAPR.fieldInsertionFormReady(rowId, field_name, event_name);
}

FMAPR.closeFieldInsertionForm = function()
{
    YES3.closePanel('yes3-fmapr-fieldinsertion-panel');
    YES3.hideRedPointer();
}

YES3.Functions.addRawREDCapField = function( element, theRowBefore, batchMode )
{   
    batchMode = batchMode || false;

    element = element || {};

    theRowBefore = theRowBefore || {};
     
    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    let export_uuid = FMAPR.export_uuid;

    let yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');
    let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let html = `<tr class='yes3-fmapr-redcap-field yes3-fmapr-data-element yes3-fmapr-sortable' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" id="${rowId}" data-required="0" data-element_origin="redcap" data-object_type="field">`;
    html += `<td class='yes3-3 yes3-td-left' title='(non-specification) REDcap field'><span class='yes3-fmapr-redcap-element'>&nbsp;</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;
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

    if ( $.isEmptyObject(element) ){

        FMAPR.doFieldMapperTableHousekeeping();

        FMAPR.scrollFieldMapperTableToBottom();

        //FMAPR.markAsDirty();

        $('input#'+FMAPR.elementInputId( yes3_fmapr_data_element_name )).focus();
    }
    else {

        let elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

        let itemREDCapField = elementRow.find('input[data-mapitem=redcap_field_name]:first');

        let itemREDCapEvent = elementRow.find('select[data-mapitem=redcap_event_id]:first');

        itemREDCapField.val( element.redcap_field_name );

        FMAPR.REDCapFieldOnChange(itemREDCapField, batchMode);

        itemREDCapEvent.val(element.redcap_event_id);

        FMAPR.REDCapEventOnChange(itemREDCapEvent, batchMode);
    }

    return yes3_fmapr_data_element_name;
}

FMAPR.addREDCapForm = function( form_name, event, theRowBefore )
{   
    theRowBefore = theRowBefore || {};

    let form_index = FMAPR.settings.form_index[form_name];

    if ( typeof form_index !== "number" && form_name !== "all" ){
        return false;
    }
     
    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    let export_uuid = FMAPR.export_uuid;

    let yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let eventSelectHtml  = FMAPR.getFormEventHtml( form_name, yes3_fmapr_data_element_name );

    let field_count = (form_name==="all") ? FMAPR.settings.field_metadata.length : FMAPR.settings.form_metadata[form_index].form_fields.length;

    //let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');
    //let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let html = `<tr class='yes3-fmapr-redcap-form yes3-fmapr-data-element yes3-fmapr-sortable' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" id="${rowId}" data-required="0" data-element_origin="redcap" data-object_type="form" data-form_name="${form_name}">`;
    html += `<td class='yes3-3 yes3-td-left' title='REDcap form'><span class='yes3-fmapr-redcap-element'>${yes3_fmapr_data_element_name}</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>${field_count} fields</td>`;
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

YES3.Functions.saveFieldMappings = function() {

    let tableId = FMAPR.specificationTableId( FMAPR.export_uuid );

    let specTable = $(`table#${tableId}`);

    let specification = {
        "export_uuid": FMAPR.export_uuid,
        "elements": []
    };

    let elementRows = specTable.find('tr.yes3-fmapr-data-element');

    elementRows.each( function() {

        let yes3_fmapr_data_element_name = $(this).data('yes3_fmapr_data_element_name');
        let element_origin = $(this).data('element_origin');
        let redcap_event_id = $(this).find('select[data-mapitem=redcap_event_id]:first').val();
        let redcap_field_name = $(this).find('input[data-mapitem=redcap_field_name]:first').val();

        let element = {
            "yes3_fmapr_data_element_name": $(this).data('yes3_fmapr_data_element_name'),
            "element_origin": $(this).data('element_origin'),
            "redcap_object_type": "",
            "redcap_event_id": ( redcap_event_id ) ? redcap_event_id : "",
            "redcap_field_name": ( redcap_field_name ) ? redcap_field_name : "",
            "redcap_form_name": "",
            "values": []
        }

        if ( element_origin === "redcap" ){

            element.redcap_object_type = $(this).data('object_type');

            if ( element.redcap_object_type === "form" ){

                element.redcap_form_name = $(this).data('form_name');
            }
        }
        else if ( element_origin === "specification" && element.redcap_field_name.length > 0 && element.redcap_event_id.length > 0) {

            let valueRows = specTable.find(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name='${element.yes3_fmapr_data_element_name}']`);
            
            valueRows.each(function () {

                let redcap_field_value = $(this).find('input[data-mapitem=redcap_field_value]:first').val();
                let yes3_fmapr_lov_value = $(this).find('input[data-mapitem=redcap_field_value]:first').data('yes3_fmapr_lov_value');

                if ( redcap_field_value ){

                    element.values.push({
                        "yes3_fmapr_lov_value": yes3_fmapr_lov_value,
                        "redcap_field_value": redcap_field_value
                    })

                } // redcap value nonblank
            }) // valueRows
        } // redcap specification

        if ( element.redcap_field_name.length > 0 || element.redcap_form_name.length > 0 ){

            specification.elements.push( element );
        }
    }) // elementRows
 
    //console.log( 'saveFieldMappings', specification );

    let field_mappings_json = JSON.stringify(specification);

    console.log('saveFieldMappings: JSON string length = ' + field_mappings_json.length);
 
    YES3.requestService( { 
        "request": "save_field_mappings", 
        "export_uuid": FMAPR.export_uuid,
        "field_mappings_json": field_mappings_json
    }, FMAPR.saveFieldMappingsCallback, false );
 }
 
FMAPR.saveFieldMappingsCallback = function( response ){
    console.log( 'saveFieldMappingsCallback', response );

    FMAPR.postMessage( response );

    if ( response.indexOf('Success') > -1 ){
        FMAPR.markAsClean();
    }
}

/*** WAYBACK ***/

YES3.Functions.Wayback_openForm = function()
{
    YES3.openPanel('yes3-fmapr-wayback-panel');

    let wrapper = $("div#yes3-fmapr-wrapper");

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
            'export_uuid': FMAPR.export_uuid
        }, FMAPR.Wayback_openFormCallback
    );
}

FMAPR.Wayback_openFormCallback = function( response )
{
    console.log(response);
    $("select#yes3-fmapr-wayback-select").empty().append(response);
}

FMAPR.Wayback_Execute = function()
{
    let log_id = $("select#yes3-fmapr-wayback-select").val();

    FMAPR.specificationElements = [];
    FMAPR.insertionElements = [];
    FMAPR.markAsBuildInProgress();

    YES3.requestService(
        {
            "request": "get_field_mappings",
            "log_id": log_id
        }, FMAPR.populateFieldMapperTableCallback, true
    );
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

        FMAPR.export_uuid = export_uuid;

        FMAPR.setSpecificationStructures(); // FMAPR.specification_index, FMAPR.specification_settings
        
        FMAPR.buildFieldMapperTable();
    }
}
 
FMAPR.specificationSave = function()
{
    let timestamp = new Date().ymdhms();
    FMAPR.postMessage("Specification saved at " + timestamp + ".");
}

FMAPR.dataElementRowId = function(data_element_name)
{
    return `yes3_fmapr_data_element-${FMAPR.export_uuid}-${data_element_name}`;
}

FMAPR.markAsBuildInProgress = function()
{
    FMAPR.buildInProgress = true;
    FMAPR.markAsClean();
    FMAPR.clearMessage();
}

FMAPR.markAsBuildCompleted = function()
{
    FMAPR.buildInProgress = false;
    FMAPR.mapperLoaded = true;
    FMAPR.displayActionIcons();
}

FMAPR.setSpecificationStructures = function()
{
    FMAPR.specification_index = -1;
    FMAPR.specification_settings = {};

    for (let i=0; i<FMAPR.stored_export_settings.specification_settings.length; i++){

        if ( FMAPR.export_uuid === FMAPR.stored_export_settings.specification_settings[i].export_uuid ){

            FMAPR.specification_index = i;
            FMAPR.specification_settings = FMAPR.stored_export_settings.specification_settings[i];
            break;
        }
    }
}
  
 FMAPR.buildFieldMapperTable = function() 
 {
    let parentDiv = $('div#yes3-fmapr-wrapper');
    let html = "";
    let value = "";
    let label = "";
    let yes3_fmapr_data_element_name = "";
    let j = 0;
    let elementInputHtml = "";
    let eventSelectHtml = "";
    let lovInputHtml = "";
    let element_origin = "";
    let lovToggleHtml = "";

    let tableId = FMAPR.specificationTableId( FMAPR.export_uuid );
    let bodyId = FMAPR.specificationTableBodyId( FMAPR.export_uuid );

    let rowId = "";

    FMAPR.markAsBuildInProgress();

    FMAPR.specificationValuesets = {};

    html += `<table class='yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-scrollable yes3-dashboard' data-export_uuid='${FMAPR.export_uuid}' id='${tableId}'>`;
    
    html += "<thead>";

    html += "<tr>";
    html += "<th class='yes3-header yes3-3'>Specification data element</th>";
    html += "<th class='yes3-header yes3-3'>REDCap study field</th>";
    html += "<th class='yes3-header yes3-3'><span class='yes3-fmapr-horizontal-only'>REDCap event(s)</span></th>";
    html += "<th class='yes3-header yes3-gutter-right'>&nbsp;</th>";
    html += "</tr>";
    
    html += "</thead>";

    html += `<tbody data-export_uuid='${FMAPR.export_uuid}' id='${bodyId}' >`;
 
    for( i=0; i<FMAPR.specification_settings.mapping_specification.length; i++ ){

        rowId = FMAPR.dataElementRowId(FMAPR.specification_settings.mapping_specification[i].name);

        req = FMAPR.specification_settings.mapping_specification[i].required;
        if ( typeof req === 'undefined' ) req = '0';

        element_origin = FMAPR.specification_settings.mapping_specification[i].element_origin;
        if ( typeof element_origin === 'undefined' ) element_origin = 'specification';
  
       if ( FMAPR.specification_settings.mapping_specification[i].format==="valueset" ) {
          lovToggleHtml = `<a class='yes3-fmapr-lov-toggler' href='javascript:FMAPR.toggleLovDisplay("${FMAPR.specification_settings.mapping_specification[i].name}");'><i class='fas fa-plus'></i></a>`;
       } else {
          lovToggleHtml = "&nbsp;";
       }
 
       elementInputHtml = FMAPR.getElementInputHtml( FMAPR.specification_settings.mapping_specification[i].name, element_origin);
       eventSelectHtml = FMAPR.getElementEventHtml( FMAPR.specification_settings.mapping_specification[i].name, element_origin);
 
       html += `<tr id='${rowId}' data-yes3_fmapr_data_element_name='${FMAPR.specification_settings.mapping_specification[i].name}' data-required='${req}' data-element_origin='${element_origin}' class='yes3-fmapr-data-element yes3-fmapr-specmap yes3-fmapr-sortable'>`;
       html += `<td class='yes3-3 yes3-td-left' title='${FMAPR.specification_settings.mapping_specification[i].description}'><span class='yes3-fmapr-specmap-element'>${FMAPR.specification_settings.mapping_specification[i].name}</span></td>`;
       html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;
       html += `<td class='yes3-3 yes3-td-middle'>${eventSelectHtml}</td>`;
       html += `<td class='yes3-gutter-right-top yes3-td-right'>${lovToggleHtml}</td>`;
       html += "</tr>";

        if ( FMAPR.specification_settings.mapping_specification[i].format==="valueset" ) {

            FMAPR.specificationValuesets[FMAPR.specification_settings.mapping_specification[i].name]
                = FMAPR.specification_settings.mapping_specification[i].valueset;

        }

       if ( FMAPR.specification_settings.mapping_specification[i].format==="valueset" ) {
 
            for (j=0; j<FMAPR.specification_settings.mapping_specification[i].valueset.length; j++ ){
 
                value = FMAPR.specification_settings.mapping_specification[i].valueset[j].value;
                label = FMAPR.specification_settings.mapping_specification[i].valueset[j].label;
                yes3_fmapr_data_element_name = FMAPR.specification_settings.mapping_specification[i].name;
 
                lovInputHtml = FMAPR.getLovInputHtml( yes3_fmapr_data_element_name, value );
 
                html += `<tr id='yes3_fmapr_lov_value-${FMAPR.export_uuid}-${FMAPR.specification_settings.mapping_specification[i].name}-${value}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-yes3_fmapr_lov_value='${value}' data-required='${req}'}' class='yes3-fmapr-lov'>`;
                html += `<td class='yes3-3 yes3-td-left yes3-fmapr-lov' title='${label}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="Make this the value for all FMAPR submissions" href="javascript:FMAPR.setLovConstant('${yes3_fmapr_data_element_name}', '${value}')">${label}</a></td>`;
                html += `<td class='yes3-3 yes3-td-middle yes3-fmapr-lov'>${lovInputHtml}</td>`;
                html += `<td class='yes3-3 yes3-td-middle yes3-fmapr-lov'></td>`;
                html += `<td class='yes3-gutter-right-top yes3-td-right'><a class='yes3-fmapr-value-picker-toggler' href='javascript:FMAPR.toggleValuePickerDisplay("${yes3_fmapr_data_element_name}", "${value}");'><i class='fas fa-plus'></i></a></td>`;
                html += "</tr>";
          }
       }
       
    }
 
    html += "</tbody>";
    html += "</table>";
 
    parentDiv.html(html);

    $('.yes3-fmapr-table-open').css('visibility', 'visible');
 
    //FMAPR.setFieldAutoselectInputs();

    FMAPR.setLovTogglePriorities();

    FMAPR.setLovInputListeners();

    //FMAPR.resizeFieldMapperTable();

    FMAPR.makeSortable( $(`tbody#${bodyId}`));

    FMAPR.nowLoaded();

    FMAPR.populateFieldMapperTable();
}

FMAPR.populateFieldMapperTable = function() {
 
    YES3.requestService( { 
        "request": "get_field_mappings", 
        "export_uuid": FMAPR.export_uuid
    }, FMAPR.populateFieldMapperTableCallback, true );
}

FMAPR.populateFieldMapperTableCallback = function( response ) {

    console.log('populateFieldMapperTableCallback', response);

    FMAPR.map_record = {

        'log_id': response.log_id,
        'user': response.user,
        'timestamp': response.timestamp,
        'formatted_time': response.formatted_time,
        'export_uuid': response.export_uuid

    };

    if ( response.field_mappings === null ){

        // null soec; add recordId
        
        let element = {
            "redcap_field_name": FMAPR.settings.field_metadata[0].field_name,
            "redcap_event_id": FMAPR.settings.project_event_metadata[0].event_id
        };
        YES3.addRawREDCapField( element )

        FMAPR.doFieldMapperTableHousekeeping( true );

        return false;
    }

    if ( typeof response.field_mappings.elements === 'undefined' ){
        FMAPR.doFieldMapperTableHousekeeping( true );
        return false;
    }

    let elementRow = null;

    let itemREDCapField = null;

    let yes3_fmapr_data_element_name = '';

    let values = [];

    $("tr.yes3-fmapr-redcap-field.yes3-fmapr-data-element").remove();

    let j = 0;

    for ( let i=0; i<response.field_mappings.elements.length; i++ ) {

        if ( response.field_mappings.elements[i].element_origin==="redcap" && response.field_mappings.elements[i].redcap_object_type==="field" ) {
            
            yes3_fmapr_data_element_name = YES3.Functions.addRawREDCapField( response.field_mappings.elements[i], null, true );
        }
        else if ( response.field_mappings.elements[i].element_origin==="redcap" && response.field_mappings.elements[i].redcap_object_type==="form" ) {
            
            yes3_fmapr_data_element_name = FMAPR.addREDCapForm( response.field_mappings.elements[i].redcap_form_name, response.field_mappings.elements[i].redcap_event_id, null, true );
        }
        else if ( response.field_mappings.elements[i].element_origin==="specification" ) {

            yes3_fmapr_data_element_name = response.field_mappings.elements[i].yes3_fmapr_data_element_name;

            elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

            itemREDCapField = elementRow.find('input[data-mapitem=redcap_field_name]:first');
    
            itemREDCapField.val( response.field_mappings.elements[i].redcap_field_name );
    
            FMAPR.REDCapFieldOnChange(itemREDCapField);
    
            elementRow.find('select[data-mapitem=redcap_event_id]:first')
                .val(response.field_mappings.elements[i].redcap_event_id)
            ;

            /**
             * see if REDCap values are mapped to specification values
             */

            values = response.field_mappings.elements[i].values;

            if ( values.length ){
                //console.log('populateFieldMapperTableCallback: values=', values);
                for (j=0; j<values.length; j++){
                    $(`input.yes3-fmapr-input-lov[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${values[j].yes3_fmapr_lov_value}']`).val(`${values[j].redcap_field_value}`).trigger("change")
                }
            }
    
        }
    }

    FMAPR.doFieldMapperTableHousekeeping( true );

    if ( $("div#yes3-fmapr-wayback-panel").is(":visible") ){
        FMAPR.Wayback_closeForm();
        FMAPR.markAsDirty();
    }
}

FMAPR.rowsToMove = [];

FMAPR.makeSortable = function( tbody )
{
    tbody.sortable({
        items: 'tr.yes3-fmapr-sortable',
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

FMAPR.doFieldMapperTableHousekeeping = function( isClean )
{
    isClean = isClean || false;

    FMAPR.resizeFieldMapperTable();

    FMAPR.setFieldAutoselectInputs();

    FMAPR.setEventSelectListeners();
        
    FMAPR.setContextMenuListeners();

    FMAPR.setREDCapElementListeners();

    if ( isClean ){
        FMAPR.markAsClean();
        FMAPR.markAsBuildCompleted();
        FMAPR.clearMessage();
    }
}

FMAPR.removeDataElement = function(element_name)
{
    $(`tr[data-yes3_fmapr_data_element_name='${element_name}']`).remove();
    FMAPR.markAsDirty();
    // enforce a refresh in case this is a repeating export layout
    FMAPR.displayActionIcons();
}

FMAPR.scrollFieldMapperTableToBottom = function()
{
    let bodyId = FMAPR.specificationTableBodyId( FMAPR.export_uuid );
    let domObj = document.getElementById(bodyId);
    domObj.scrollTop = domObj.scrollHeight; 
}

FMAPR.resizeFieldMapperTable = function()
{
    let gutterWidth = 30;
    let scrollbarWidth = 20;

    let fmaprTable = $('table.yes3-fmapr-specification').first();

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
 
    let tableId = FMAPR.specificationTableId( FMAPR.export_uuid );

    let rowId = FMAPR.dataElementRowId(yes3_fmapr_data_element_name);

    let parentRow = $(`tr#${rowId}`);
 
    let lov_rows = $(`table#${tableId} tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'].yes3-fmapr-lov`);
    let icon = $(`table#${tableId} tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'].yes3-fmapr-data-element i`);
  
    if ( icon.hasClass('fa-plus') ){

       lov_rows.show();

       parentRow.removeClass('yes3-fmapr-sortable');
 
       icon.removeClass('fa-plus').addClass('fa-minus');
    } else {

       lov_rows.hide();

       parentRow.addClass('yes3-fmapr-sortable');

       icon.removeClass('fa-minus').addClass('fa-plus');
    }
 }
 
 FMAPR.toggleValuePickerDisplay = function(yes3_fmapr_data_element_name, lov_value) {
 
    let tableId = FMAPR.specificationTableId( FMAPR.export_uuid )
 
    let value_picker_wrapper = $(`table#${tableId} tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'] div.yes3-fmapr-value-picker-wrapper`);
    let icon = $(`table#${tableId} tr[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'] i`);
 
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
    return 'element-' + FMAPR.export_uuid + '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name);
 }
 
 FMAPR.elementEventId = function( yes3_fmapr_data_element_name ){
    return 'element-' + FMAPR.export_uuid + '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name + '-event');
 }
 
 FMAPR.lovInputId = function( yes3_fmapr_data_element_name, value ){
    return 'lov-' + FMAPR.export_uuid +
       '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name) +
       '-' + FMAPR.normalizeString(value)
       ;
 }
 
 FMAPR.pickerWrapperId = function(yes3_fmapr_data_element_name, yes3_fmapr_lov_value ){
    return 'picker-' + FMAPR.export_uuid +
       '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name) +
       '-' + FMAPR.normalizeString(yes3_fmapr_lov_value)
       ;
 }
 
 FMAPR.typeInputId = function(){
    return 'type-' + FMAPR.export_uuid;
 }

 FMAPR.itemTypeClass = function(element_origin)
 {
     return ( element_origin==='redcap' ) ? 'yes3-fmapr-redcap' : 'yes3-fmapr-specification';
 }
 
 FMAPR.getElementInputHtml = function( yes3_fmapr_data_element_name, element_origin ){
    element_origin = element_origin || 'specification';
    let typeClass = FMAPR.itemTypeClass(element_origin);
    let id = FMAPR.elementInputId( yes3_fmapr_data_element_name );
    let html =`<input type='text' id=${id} class='yes3_fmapr_field_autocomplete ${typeClass} yes3-fmapr-input-element yes3-fmapr-item' data-export_uuid='${FMAPR.export_uuid}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_field_name' data-element_origin='${element_origin}' placeholder='Start typing or spacebar for all' />`;
    return html;
 }
 
FMAPR.getElementEventHtml = function( yes3_fmapr_data_element_name, element_origin ){
    element_origin = element_origin || 'specification';
    let typeClass = FMAPR.itemTypeClass(element_origin);
    let id = FMAPR.elementEventId( yes3_fmapr_data_element_name );
    //let html = `<select id=${id} class='yes3-fmapr-event-select yes3-fmapr-item' data-export_uuid='${export_uuid}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id'/>${FMAPR.settings.event_select_options_html}</select>`;
    let html = `<select id=${id} class='yes3-fmapr-event-select ${typeClass} yes3-fmapr-item yes3-fmapr-field-event' data-export_uuid='${FMAPR.export_uuid}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id' data-element_origin='${element_origin}'/></select>`;
    return html;
}
 
FMAPR.getFormEventHtml = function( form_name, yes3_fmapr_data_element_name ){

    let element_origin = 'redcap';

    let optionsHtml = '<option value="all">all events</option>';

    let formEvents = [];

    if ( form_name === "all" ){

        formEvents = FMAPR.settings.project_event_metadata;
    }
    else {

        let form_index = FMAPR.settings.form_index[form_name];

        if ( typeof form_index === "number" ){

            formEvents = FMAPR.settings.form_metadata[form_index].form_events;
        }

    }

    for ( let e=0; e<formEvents.length; e++ ){
        optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].event_label}</option>`;                 
    }

    let typeClass = FMAPR.itemTypeClass(element_origin);

    let id = FMAPR.elementEventId( form_name );

    let html = `<select id=${id} class='yes3-fmapr-event-select ${typeClass} yes3-fmapr-item yes3-fmapr-form-event' data-export_uuid='${FMAPR.export_uuid}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id' data-element_origin='${element_origin}'/>${optionsHtml}</select>`;
    
    return html;
}
 
 FMAPR.getLovInputHtml = function( yes3_fmapr_data_element_name, value ){
    let id = FMAPR.lovInputId( yes3_fmapr_data_element_name, value );
    let pickerWrapperId = FMAPR.pickerWrapperId( yes3_fmapr_data_element_name, value );
    return `<input type='text' id='${id}' class='yes3-fmapr-input-lov yes3-fmapr-item' data-export_uuid='${FMAPR.export_uuid}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-yes3_fmapr_lov_value='${value}' data-mapitem='redcap_field_value' /><div class='yes3-fmapr-value-picker-wrapper' id='${pickerWrapperId}'></div>`;
 }
 
 FMAPR.setLovInputListeners = function() {
 
    let tableId = FMAPR.specificationTableId( FMAPR.export_uuid );
    let lovInputs = $(`table#${tableId} input[type=text].yes3-fmapr-input-lov`);
 
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
                "export_uuid": FMAPR.export_uuid,
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
    $('tr.yes3-fmapr-redcap-field, tr.yes3-fmapr-redcap-form')
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
    if ( typeof FMAPR.specification_settings === "undefined" ){

        return false;
    }
    else return ( FMAPR.specification_settings.export_layout === "v" );
}

FMAPR.isHorizontalLayout = function()
{
    if ( typeof FMAPR.specification_settings === "undefined" ){

        return false;
    }
    else return ( FMAPR.specification_settings.export_layout === "h" );
}

FMAPR.isRepeatedLayout = function()
{
    if ( typeof FMAPR.specification_settings === "undefined" ){

        return false;
    }
    else return ( FMAPR.specification_settings.export_layout === "r" );
}

FMAPR.fieldInsertionFormReady = function(rowId, field_name, event_name)
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
    let theRowBefore = $(`tr#${FMAPR.insertionRowId}`); // set by openFieldInsertionForm

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

    FMAPR.doFieldMapperTableHousekeeping( true );

    FMAPR.markAsDirty();
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

    for (let k=0; k<FMAPR.settings.form_metadata.length; k++){

        // insert form if registered for this event
        allowed = ( event === "all" );

        //if ( !allowed ){

            for (j=0; j<FMAPR.settings.form_metadata[k].form_events.length && !allowed; j++){

                if ( event == FMAPR.settings.form_metadata[k].form_events[j].event_id ) {
                    allowed = true;
                }
            }
        //}

        if ( allowed ){

            yes3_fmapr_data_element_name = FMAPR.addREDCapForm( 
                FMAPR.settings.form_metadata[k].form_name, 
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

    //if ( !counts.fields ){

    //    statusDiv.html('');
    //}
    //else {

        statusDiv.html(`${counts.fields} fields, ${counts.columns} export columns will be inserted.`);
    //}
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

    for (let i=0; i<FMAPR.settings.field_metadata.length; i++){

        this_field_name = FMAPR.settings.field_metadata[i].field_name;
        this_form_name = FMAPR.settings.field_metadata[i].form_name;
        this_form_index = FMAPR.settings.form_index[this_form_name];

        if ( form_name === this_form_name || form_name === "all" ){

            element_events = [];

            for (j=0; j<FMAPR.settings.form_metadata[this_form_index].form_events.length; j++){

                this_event_id = FMAPR.settings.form_metadata[this_form_index].form_events[j].event_id;

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

    console.log('addREDcapObjectToSpecification', data_element_name, form_name, field_name, event_id_option);

    /**
     * add a single item (redcap field)
     */
    if ( !form_name ){

        if ( typeof FMAPR.settings.field_index[field_name] === "number" ){

            form_name = FMAPR.settings.field_metadata[FMAPR.settings.field_index[field_name]].form_name;
        }

        if (form_name && field_name){

            FMAPR.addREDcapItemToSpecification(data_element_name, form_name, field_name, event_id_option);
        }
        
        return 1;
    }

    if ( form_name !== "all" ){

        let form_index = FMAPR.settings.form_index[form_name];

        if ( typeof form_index === "number" ){

            for (k=0; k<FMAPR.settings.form_metadata[form_index].form_fields.length; k++){

                items++;
                FMAPR.addREDcapItemToSpecification(data_element_name, form_name, FMAPR.settings.form_metadata[form_index].form_fields[k], event_id_option);
            }
        }

        return items;
    }

    /**
     * Add one or more entire forms
     */
    for (j=0; j<FMAPR.settings.form_metadata.length; j++){

        // gotta fix this; the entire field list is processed for each form
        for (k=0; k<FMAPR.settings.field_metadata.length; k++){

            if ( FMAPR.settings.field_metadata[k].form_name === FMAPR.settings.form_metadata[j].form_name ){

                items++;
                FMAPR.addREDcapItemToSpecification(data_element_name, FMAPR.settings.field_metadata[k].form_name, FMAPR.settings.field_metadata[k].field_name, event_id_option);
            }
        }
    }

    return items;
}

FMAPR.addREDcapItemToSpecification = function(data_element_name, form_name, field_name, event_id_option)
{
    let form_index = FMAPR.settings.form_index[form_name];

    if ( typeof form_index === "number" ){

        for (let j=0; j<FMAPR.settings.form_metadata[form_index].form_events.length; j++){

            if ( event_id_option === "all" || event_id_option == FMAPR.settings.form_metadata[form_index].form_events[j].event_id ){

                if ( !FMAPR.isSpecificationElement(field_name, FMAPR.settings.form_metadata[form_index].form_events[j].event_id) ){

                    FMAPR.specificationElements.push({
                        data_element_name: data_element_name,
                        data_element_origin: "redcap",
                        redcap_field_name: field_name,
                        redcap_event_id: FMAPR.settings.form_metadata[form_index].form_events[j].event_id,
                    });
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

            console.log('enumerateSpecificationElements', data_element_name, form_name, field_name, event_id_option);

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

    if ( FMAPR.mapperLoaded ){

        let counts = FMAPR.enumerateSpecificationElements();

        //s+= `[${FMAPR.settings.specification_settings[FMAPR.export_uuid].specification_key}]:`;

        s += ` ${counts.elements} data elements, ${counts.columns} export columns.`;

        s += "<br>";

        s += `Map record #${FMAPR.map_record.log_id} saved on ${FMAPR.map_record.formatted_time} by ${FMAPR.map_record.user}.`;
        
    }

    $('div#yes3-fmapr-status').html(s);

    FMAPR.showLayoutItems();
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

FMAPR.getFormOptionsHtml = function(event_id)
{
    event_id = event_id || 'all';

    //console.log('getFormOptionsHtml', 'event_id='+event_id)

    let allowed = true;
    let j = 0;
    let optionHtml = "";

    if ( FMAPR.specification_settings.export_layout !== "r" ){

        optionHtml = "\n<option value='all'>all forms</option>";
    }
    else {

        optionHtml = "\n<option value='' disabled>select a form</option>";
    }

    for (let i=0; i<FMAPR.settings.form_metadata.length; i++){

        if ( FMAPR.settings.form_metadata[i].form_repeating && FMAPR.specification_settings.export_layout !== "r"){

            allowed = false;
        }
        else if ( !FMAPR.settings.form_metadata[i].form_repeating && FMAPR.specification_settings.export_layout === "r" ){
            allowed = false;
        }
        else {

            allowed = ( event_id==="all" );

            if ( !allowed ){
    
                for (j=0; j<FMAPR.settings.form_metadata[i].form_events.length; j++){

                    if ( FMAPR.settings.form_metadata[i].form_events[j].event_id==event_id ){
                        allowed = true;
                        //break;
                    }
                }
            }
        }

        if ( allowed ){

            optionHtml += `\n<option value='${FMAPR.settings.form_metadata[i].form_name}'>${FMAPR.settings.form_metadata[i].form_label}</option>`;
        }
    }

    return optionHtml;
}

FMAPR.getEventOptionsHtml = function(form_name)
{
    form_name = form_name || 'all';

    let optionHtml = "<option value='all'>all events</option>";

    if ( form_name==="all" ) {

        let eventIDs = Object.getOwnPropertyNames( FMAPR.settings.event_metadata );
        
        for (let i=0; i<eventIDs.length; i++){
    
            optionHtml += `\n<option value='${eventIDs[i]}'>${FMAPR.settings.event_metadata[eventIDs[i]].event_label}</option>`;
    
        }
    }
    else {

        let form_index = FMAPR.settings.form_index[form_name];

        if ( typeof form_index === "number" ){

            if ( FMAPR.settings.form_metadata[form_index].form_events.length < 2 ){

                optionHtml = "";
            }

            for (let i=0; i<FMAPR.settings.form_metadata[form_index].form_events.length; i++){

                optionHtml += `\n<option value='${FMAPR.settings.form_metadata[form_index].form_events[i].event_id}'>${FMAPR.settings.form_metadata[form_index].form_events[i].descrip}</option>`;
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

    let event_name = thisRow.find('select.yes3-fmapr-event-select option:selected').first().text();

    let rowSelected = thisRow.hasClass('yes3-row-selected');

    let html = "";

    //$('tr.yes3-row-focused').removeClass('yes3-row-focused');
    //thisRow.addClass('yes3-row-focused');

    //console.log( theParentOffset );

    e.preventDefault();

    html = FMAPR.REDCapFieldContextMenuContent( thisRow.prop('id'), field_name, event_name, rowSelected );

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

FMAPR.REDCapFieldContextMenuContent = function( rowId, field_name, event_name, rowSelected )
{
    let k = FMAPR.selectedRowCount();

    let theRow = $(`tr#${rowId}`);

    let theNexRow = theRow.next('tr');

    let theNextRowIsSelected = FMAPR.rowIsSelected( theNexRow );

    let html = "";

    html += "<div id='yes3-contextmenu-panel-title' class='yes3-contextmenu-panel-row yes3-drag-handle'>";

    html += "<div class='yes3-float-left'>" + field_name + "</div><div class='yes3-float-right yes3-ellipsis' style='max-width: 150px'>" + event_name + "</div>";

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
            html += `<tr><td><a href="javascript:YES3.Functions.openFieldInsertionForm('${rowId}', '${field_name}', '${event_name}', 'below');">insert form fields</a></td><td>&nbsp;</td></tr>`;

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

    YES3.contextMenuClose();
}

FMAPR.contextMenuRemoveRowSelections = function()
{
    FMAPR.removeSelections();

    FMAPR.enumerateSpecificationElements();

    FMAPR.reportStatus();

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
 
    let tblId = FMAPR.specificationTableId( FMAPR.export_uuid );
    let tbl = $('table#' + tblId);

    //console.log('setValuePickers', FMAPR.export_uuid, yes3_fmapr_data_element_name, redcap_field_name);
 
    if ( !FMAPR.settings.field_metadata[FMAPR.settings.field_index[redcap_field_name]].field_valueset.length ) {
       tbl.find(`tr[data-yes3_fmapr_data_element_name=${yes3_fmapr_data_element_name}].yes3-fmapr-lov a.yes3-fmapr-value-picker-toggler`).hide();
       return true;
    }
 
    let value = "";
    let label = "";
 
    let picker_wrappers = tbl.find(`tr[data-yes3_fmapr_data_element_name=${yes3_fmapr_data_element_name}].yes3-fmapr-lov div.yes3-fmapr-value-picker-wrapper`);

    //console.log('setValuePickers:picker_wrappers=', picker_wrappers);
 
    let pkrHtml = "";
 
    for (let i = 0; i < FMAPR.settings.field_metadata[FMAPR.settings.field_index[redcap_field_name]].field_valueset.length; i++) {
       value = FMAPR.settings.field_metadata[FMAPR.settings.field_index[redcap_field_name]].field_valueset[i].value;
       label = FMAPR.settings.field_metadata[FMAPR.settings.field_index[redcap_field_name]].field_valueset[i].label;
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
 
 FMAPR.setFieldAutoselectInputs = function() {
 
    $(`input[type=text][data-export_uuid=${FMAPR.export_uuid}].yes3_fmapr_field_autocomplete`).each(function () {
 
       $(this)
       .autocomplete({
          source: FMAPR.settings.field_autoselect_source,
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

            FMAPR.REDCapFieldOnChange( $(this) );
       });
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
    else if ( FMAPR.specification_settings.export_layout !== "h" ){

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

    if ( pseudoName.length ){

        theRow.find('span.yes3-fmapr-redcap-element').html( pseudoName );
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
    for ( let j=0; j<FMAPR.stored_export_settings.event_settings.length; j++) {
        if ( event_id===FMAPR.stored_export_settings.event_settings[j].event_id ) {
            return FMAPR.stored_export_settings.event_settings[j].event_prefix;
        }
     }

     return "?";
 }

 FMAPR.eventNameForEventId = function(event_id)
 {
    for ( let j=0; j<FMAPR.stored_export_settings.event_settings.length; j++) {
        if ( event_id===FMAPR.stored_export_settings.event_settings[j].event_id ) {
            return FMAPR.stored_export_settings.event_settings[j].event_name;
        }
     }

     return "?";
 }

 FMAPR.setEventSelectListeners = function()
 {
     $('select.yes3-fmapr-event-select').on("change", function(){

        FMAPR.REDCapEventOnChange( $(this) );
     })
 }

 FMAPR.REDCapEventOnChange = function(evnt, batchMode)
 {
    batchMode = batchMode || false;

    //if ( evnt.hasClass('yes3-fmapr-field-event') ) {

        let yes3_fmapr_data_element_name = evnt.attr('data-yes3_fmapr_data_element_name');

        FMAPR.setRawREDCapPseudoElementName( yes3_fmapr_data_element_name );
    //}

    // block this call while adding blocks of fields -- very costly
    if ( !batchMode ){
        FMAPR.markAsDirty();
    }
 }

 FMAPR.REDCapFieldOnChange = function( fld, batchMode )
 {
    batchMode = batchMode || false;

    let yes3_fmapr_data_element_name = fld.data('yes3_fmapr_data_element_name');

    let field_name = fld.val();

    fld.parent().parent().find('select.yes3-fmapr-event-select').empty();

    if ( !field_name ){
        return false;
    }

    let form_name = FMAPR.settings.field_metadata[FMAPR.settings.field_index[field_name]].form_name;

    if ( !form_name ){
        return false;
    }

    let form_index = FMAPR.settings.form_index[form_name];

    if ( typeof form_index !== "number" ){
        return false;
    }

    let optionsHtml = "";
    
    let formEvents = FMAPR.settings.form_metadata[form_index].form_events;
    
    let raw = FMAPR.isRawREDCapDataElement(yes3_fmapr_data_element_name);

    if ( raw && formEvents.length > 1 ){
        optionsHtml += '<option value="all">all events</option>';
    }

    for ( let e=0; e<formEvents.length; e++ ){
        optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].descrip}</option>`;                 
    }

    if ( !raw ) {
        FMAPR.setValuePickers( yes3_fmapr_data_element_name, field_name );
    }

    fld.parent().parent().find('select.yes3-fmapr-event-select').append(optionsHtml);

    FMAPR.setRawREDCapPseudoElementName( yes3_fmapr_data_element_name );

    if ( !batchMode ){
        FMAPR.markAsDirty();
    }

 }
 
 FMAPR.setLovTogglePriorities = function(yes3_fmapr_data_element_name) {
 
    yes3_fmapr_data_element_name = yes3_fmapr_data_element_name || '';
 
    var dataElementRows;
 
    if ( yes3_fmapr_data_element_name ) {
       dataElementRows = $(`table.yes3-fmapr[data-export_uuid=${FMAPR.export_uuid}] tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);
    } else {
       dataElementRows = $(`table.yes3-fmapr[data-export_uuid=${FMAPR.export_uuid}] tr.yes3-fmapr-data-element`);
    }
 
    dataElementRows.each(function () {
       let yes3_fmapr_data_element_name = $(this).data('yes3_fmapr_data_element_name');
       let icon = $(this).find('i.fas:first');
 
       if ( icon ) {
          let inputCtl = $(this).find('input[type=text].yes3_fmapr_field_autocomplete:first');
          let entries = 0;
 
          let inputs = $(`table.yes3-fmapr[data-export_uuid=${FMAPR.export_uuid}] tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'] input[type=text].yes3-fmapr-input-lov`);
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
    let lovCtl = $( `input[type=text][data-export_uuid='${FMAPR.export_uuid}'][data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'].yes3-fmapr-input-lov:first` );
 
    lovCtl.val('*').trigger('change');
 }
 /**
  *
  */
 FMAPR.setLovConstantExecute = function() {
 
    inputs = $(`input[type=text][data-export_uuid=${FMAPR.params.export_uuid}][data-yes3_fmapr_data_element_name='${FMAPR.params.yes3_fmapr_data_element_name}'].yes3-fmapr-input-lov`);
 
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

FMAPR.getExportSettings = function()
{

    YES3.requestService({"request": "getExportSettings"}, FMAPR.getExportSettingsCallback, true);  
}

FMAPR.getExportSettingsCallback = function(response)
{
    console.log('getExportSettingsCallback:', response);

    FMAPR.stored_export_settings = response;
    
    FMAPR.populateExportSpecificationSelect();
}

FMAPR.populateExportSpecificationSelect = function()
{
    let html = "<option value='' disabled selected>select a specification</option>";
    let spec = {};

    for (let s=0; s<FMAPR.stored_export_settings.specification_settings.length; s++){

        spec = FMAPR.stored_export_settings.specification_settings[s];

        spec.export_name = escapeHTML( spec.export_name );

        if ( spec.removed === "0" ){

            html += `<option value='${spec.export_uuid}'>${spec.export_name} (${spec.export_layout})</option>`;
        }
        $("select#export_uuid").empty().append(html);
    }
}

/*** ANONYMOUS TIME ***/

/**
 * things to do when the settings are loaded
 */
 $(document).on('yes3-fmapr.settings', function(){

    console.log("on.yes3-fmapr.settings");

    /**
     * now we fetch the export settings
     */
    FMAPR.getExportSettings();
})

$(window).resize( function() {

    FMAPR.resizeFieldMapperTable();
})
 
$( function () {

    YES3.hideContextMenuOnClickOutside();

    FMAPR.getProjectSettings();
})



 

 