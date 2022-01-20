
let FMAPR = {
    maxZ: 1000,
    specification_index: 0,
    maxRawREDCapDataElementNumber: 0,
    specifications: []
}
 
 FMAPR.conditionUserInput = function( s ){
    //return s.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    return s;
 }
 
 FMAPR.noop = function(){}
 
 FMAPR.requestService = function( params, doneFn, json ) {
 
    json = json || false;
    doneFn = doneFn || FMAPR.noop;
 
    //console.log('requestService', params);
 
    $.ajax({
 
       url: yes3ModuleProperties.serviceUrl,
       type: "POST",
       dataType: ( json ) ? "json":"html",
       data: params
 
    })
    .done(
       doneFn
    )
    .fail(function(jqXHR, textStatus, errorThrown) {
 
       console.log(jqXHR);
       FMAPR.postMessage(jqXHR.responseText);
  
    });
 
 }
 
 FMAPR.isEmptyArray = function( x ){
    //console.log('isEmptyArray', typeof x, x);
    if ( typeof x === "undefined" ) return true;
    return !x.length;
 
 }
 
 FMAPR.isTruthy = function( x ){
    //console.log('isEmptyArray', typeof x, x);
    if ( typeof x === "undefined" ) return false;
    return x;
 
 }
 
 
 FMAPR.postMessage = function( msg ){
    if ( $('div#yes3-message') ) {
       $('#yes3-message').html(msg).show();
    } else {
       alert(msg);
    }
 }
 
 FMAPR.postAjaxMessage = function( msg ){
    FMAPR.postMessage(msg);
 }
 
 FMAPR.postAPIResponse = function(response){
    FMAPR.postMessage(response);
 }
 
 FMAPR.getProjectSettings = function() {
    FMAPR.requestService({'request':'get_project_settings'}, FMAPR.getProjectSettingsCallback, true);
 }
 
 FMAPR.getProjectSettingsCallback = function(response) {

    console.log( 'getProjectSettingsCallback', response );

    FMAPR.settings = response;
 
    // handler must defined in plugin JS
    $(document).trigger('yes3-fmapr.settings');
 
 }
 
 /*
  * refresh project settings from NIAFMAPR, then call getProjectSettings to fetch them
  */
 FMAPR.updateProjectSettings = function() {
    FMAPR.postMessage("plz wait..");
    FMAPR.requestService({'request':'update_project_settings'}, FMAPR.updateProjectSettingsCallback, true);
 }
 
 FMAPR.updateProjectSettingsCallback = function(response) {
    FMAPR.postMessage(response.message);
    console.log('updateProjectSettingsCallback', response);
    if ( response.result==="success" ) {
       FMAPR.getProjectSettings();
    } 
 }
 
 FMAPR.buildAndPopulateFieldMapperTables = function() {
    let parentDiv = $('div#yes3-fmapr-wrapper');
    let specTable = null;
    let typeId = "";
    let j=0;
    let k=0;
    let elementInputId = "";
    let yes3_fmapr_data_element_name = "";
    let redcap_field_name = "";
    let redcap_event_id = "";
    let lovInputId = "";
    let yes3_fmapr_lov_value = "";
    let redcap_field_value = "";
    let elementInput = null;
    let lovInput = null;
 
    let elementEventId = "";
    let elementEventSelect = null;
 
    $('table.yes3-fmapr-specification').remove();
 
    for (let i=0; i<FMAPR.settings.yes3_fmapr_participants.length; i++ ){
 
       FMAPR.buildFieldMapperTable();
 
       typeId = FMAPR.typeInputId();
       $('select#' + typeId).val( FMAPR.settings.field_map[i].participant_type );
 
       for ( j=0; j<FMAPR.settings.field_map[i].elements.length; j++ ) {
 
          yes3_fmapr_data_element_name = FMAPR.settings.field_map[i].elements[j].yes3_fmapr_data_element_name;
          redcap_field_name = FMAPR.settings.field_map[i].elements[j].redcap_field_name;
          redcap_event_id = FMAPR.settings.field_map[i].elements[j].redcap_event_id;
 
          elementInputId = FMAPR.elementInputId( yes3_fmapr_data_element_name );
          elementInput =  $('input#' + elementInputId);
          elementInput.val(redcap_field_name);
 
          elementEventId = FMAPR.elementEventId( yes3_fmapr_data_element_name );
          elementEventSelect =  $('select#' + elementEventId);
          elementEventSelect.val(redcap_event_id).trigger('change');
 
          FMAPR.setValuePickers( yes3_fmapr_data_element_name, redcap_field_name );
 
          if ( FMAPR.settings.field_map[i].elements[j].values ) {
             for ( k=0; k<FMAPR.settings.field_map[i].elements[j].values.length; k++ ){
 
                yes3_fmapr_lov_value = FMAPR.settings.field_map[i].elements[j].values[k].yes3_fmapr_lov_value;
                redcap_field_value = FMAPR.settings.field_map[i].elements[j].values[k].redcap_field_value;
                lovInputId = FMAPR.lovInputId( yes3_fmapr_data_element_name, yes3_fmapr_lov_value );
                lovInput = $('input#' + lovInputId);
                lovInput.val(redcap_field_value);
                if ( redcap_field_value !== "*" ){
                   lovInput.trigger('change');
                }
 
             }
          }
 
          FMAPR.setLovTogglePriorities(yes3_fmapr_data_element_name );
       }
       
       FMAPR.displayOrHideNonRequiredElements( FMAPR.settings.yes3_fmapr_participants[i].specification_index );
    }
 
 }

 FMAPR.specificationTableBodyId = function(specification_index)
 {
     return 'yes3-fmapr-tbody-' + specification_index;
 }
 
 FMAPR.specificationSelect = function()
 {
     let specNum = $('select#yes3-fmapr-specification').val();
     if ( specNum.length ){
         FMAPR.specification_index = specNum;
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
    return `yes3_fmapr_data_element-${FMAPR.specification_index}-${data_element_name}`;
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

    let tableId = FMAPR.specificationTableId( FMAPR.specification_index );
    let bodyId = FMAPR.specificationTableBodyId( FMAPR.specification_index );

    let rowId = "";

    FMAPR.specificationValuesets = {};

    html += `<table class='yes3-fmapr yes3-fmapr-specification yes3-fmapr-item yes3-scrollable' data-specification_index='${FMAPR.specification_index}' id='${tableId}'>`;
    
    html += "<thead>";

    html += "<tr>";
    html += "<th class='yes3-header yes3-3'>Specification data element</th>";
    html += "<th class='yes3-header yes3-3'>REDCap study field</th>";
    html += "<th class='yes3-header yes3-3'>REDCap event(s)</th>";
    html += "<th class='yes3-header yes3-gutter-right'>&nbsp;</th>";
    html += "</tr>";
    
    html += "</thead>";

    html += `<tbody data-specification_index='${FMAPR.specification_index}' id='${bodyId}' >`;
 
    for( i=0; i<yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements.length; i++ ){

        rowId = FMAPR.dataElementRowId(yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name);

        req = yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].required;
        if ( typeof req === 'undefined' ) req = '0';

        element_origin = yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].element_origin;
        if ( typeof element_origin === 'undefined' ) element_origin = 'specification';
  
       if ( yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].format==="valueset" ) {
          lovToggleHtml = `<a class='yes3-fmapr-lov-toggler' href='javascript:FMAPR.toggleLovDisplay("${yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name}");'><i class='fas fa-plus'></i></a>`;
       } else {
          lovToggleHtml = "&nbsp;";
       }
 
       elementInputHtml = FMAPR.getElementInputHtml( yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name, element_origin);
       eventSelectHtml = FMAPR.getElementEventHtml( yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name, element_origin);
 
       html += `<tr id='${rowId}' data-yes3_fmapr_data_element_name='${yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name}' data-required='${req}' data-element_origin='${element_origin}' class='yes3-fmapr-data-element yes3-fmapr-sortable'>`;
       html += `<td class='yes3-3 yes3-td-left' title='${yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].description}'>${yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name}</td>`;
       html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;
       html += `<td class='yes3-3 yes3-td-middle'>${eventSelectHtml}</td>`;
       html += `<td class='yes3-gutter-right-top yes3-td-right'>${lovToggleHtml}</td>`;
       html += "</tr>";

        if ( yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].format==="valueset" ) {

            FMAPR.specificationValuesets[yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name]
                = yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].valueset;

        }

       if ( yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].format==="valueset" ) {
 
            for (j=0; j<yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].valueset.length; j++ ){
 
                value = yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].valueset[j].value;
                label = yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].valueset[j].label;
                yes3_fmapr_data_element_name = yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name;
 
                lovInputHtml = FMAPR.getLovInputHtml( yes3_fmapr_data_element_name, value );
 
                html += `<tr id='yes3_fmapr_lov_value-${FMAPR.specification_index}-${yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].name}-${value}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-yes3_fmapr_lov_value='${value}' data-required='${yes3ModuleProperties.specifications[FMAPR.specification_index].data_elements[i].required}' class='yes3-fmapr-lov'>`;
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
 
    FMAPR.setFieldAutoselectInputs();
    FMAPR.setLovTogglePriorities();
    FMAPR.setLovInputListeners();

    FMAPR.resizeFieldMapperTable();

    FMAPR.makeSortable( $(`tbody#${bodyId}`));

    FMAPR.nowLoaded();

    FMAPR.populateFieldMapperTable();
}

FMAPR.populateFieldMapperTable = function() {
 
    FMAPR.requestService( { 
        "request": "get_field_mappings", 
        "specification_index": FMAPR.specification_index
    }, FMAPR.populateFieldMapperTableCallback, true );
}

FMAPR.populateFieldMapperTableCallback = function( response ) {

    console.log('getFieldMappingsCallback', response);

    if ( typeof response.field_mappings.elements === 'undefined' ){
        return false;
    }

    let elementRow = null;

    let itemREDCapField = null;

    let yes3_fmapr_data_element_name = '';

    for ( let i=0; i<response.field_mappings.elements.length; i++ ) {

        if ( FMAPR.isRawREDCapDataElement(response.field_mappings.elements[i].yes3_fmapr_data_element_name) ) {
            
            yes3_fmapr_data_element_name = FMAPR.addRawREDCapField( true );

            console.log('populateFieldMapperTableCallback:raw', yes3_fmapr_data_element_name);

        }
        else {

            yes3_fmapr_data_element_name = response.field_mappings.elements[i].yes3_fmapr_data_element_name;

        }

        elementRow = $(`tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);

        itemREDCapField = elementRow.find('input[data-mapitem=redcap_field_name]:first');

        itemREDCapField.val( response.field_mappings.elements[i].redcap_field_name );

        FMAPR.REDCapFieldOnChange(itemREDCapField);

        elementRow.find('select[data-mapitem=redcap_event_id]:first')
            .val(response.field_mappings.elements[i].redcap_event_id)
        ;

    }

    FMAPR.doFieldMapperTableHousekeeping();
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
    return 'redcap_field_' + n;
}

FMAPR.isRawREDCapDataElement = function( elementName )
{
    return ( elementName.substring(0, 13) === 'redcap_field_'  );
}
  
FMAPR.RawREDCapDataElementNumber = function( elementName ) 
{
    if ( !FMAPR.isRawREDCapDataElement(elementName)) {
        return 0;
    }

    return parseInt(elementName.split('_')[2]);
}

FMAPR.addRawREDCapField = function( appendOnly )
{   
    appendOnly = appendOnly || false;
    
    let fmaprBody = $('table.yes3-fmapr-specification').first().find('tbody');

    let specification_index = FMAPR.specification_index;

    let yes3_fmapr_data_element_name = FMAPR.RawREDCapDataElementName(0);

    let elementInputHtml = FMAPR.getElementInputHtml( yes3_fmapr_data_element_name, 'redcap');
    let eventSelectHtml  = FMAPR.getElementEventHtml( yes3_fmapr_data_element_name, 'redcap');

    let html = `<tr class='yes3-fmapr-redcap-field yes3-fmapr-data-element yes3-fmapr-sortable' data-yes3_fmapr_data_element_name="${yes3_fmapr_data_element_name}" id="yes3_fmapr_data_element-${specification_index}-${yes3_fmapr_data_element_name}" data-required="0" data-element_origin="redcap">`;
    html += `<td class='yes3-3 yes3-td-left' title='(non-specification) REDcap field'><span class='yes3-redcap-element'>REDCap&nbsp;field&nbsp;&nbsp;options</span></td>`;
    html += `<td class='yes3-3 yes3-td-middle'>${elementInputHtml}</td>`;
    html += `<td class='yes3-3 yes3-td-middle'>${eventSelectHtml}</td>`;
    html += `<td class='yes3-gutter-right-top yes3-td-right'><i class='far fa-trash-alt'></i></td>`;
    html += "</tr>";

    fmaprBody.append(html);

    if ( !appendOnly ){

        FMAPR.doFieldMapperTableHousekeeping();

        FMAPR.scrollFieldMapperTableToBottom();

        $('input#'+FMAPR.elementInputId( yes3_fmapr_data_element_name )).focus();
    
    }

    return yes3_fmapr_data_element_name;
}

FMAPR.doFieldMapperTableHousekeeping = function()
{
    FMAPR.resizeFieldMapperTable();

    FMAPR.setFieldAutoselectInputs();
        
    FMAPR.setContextMenuListeners();

    FMAPR.setREDCapElementListeners();
}

FMAPR.scrollFieldMapperTableToBottom = function()
{
    let bodyId = FMAPR.specificationTableBodyId( FMAPR.specification_index );
    let domObj = document.getElementById(bodyId);
    domObj.scrollTop = domObj.scrollHeight; 
}

FMAPR.resizeFieldMapperTable = function()
{
    let gutterWidth = 30;
    let scrollbarWidth = 20;

    let fmaprTable = $('table.yes3-fmapr-specification').first();

    let fmaprFooter = $('div#yes3-fmapr-footer');

    let parentSection = $('div#yes3-fmapr-container').parent();

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
 
    let tableId = FMAPR.specificationTableId( FMAPR.specification_index );

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
 
    let tableId = FMAPR.specificationTableId( FMAPR.specification_index )
 
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
 
 FMAPR.specificationTableId = function( specification_index ){
     specification_index = specification_index || 1;
    return 'specification-' + specification_index;
 }
 
 FMAPR.elementInputId = function( yes3_fmapr_data_element_name ){
    return 'element-' + FMAPR.specification_index + '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name);
 }
 
 FMAPR.elementEventId = function( yes3_fmapr_data_element_name ){
    return 'element-' + FMAPR.specification_index + '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name + '-event');
 }
 
 FMAPR.lovInputId = function( yes3_fmapr_data_element_name, value ){
    return 'lov-' + FMAPR.specification_index +
       '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name) +
       '-' + FMAPR.normalizeString(value)
       ;
 }
 
 FMAPR.pickerWrapperId = function(yes3_fmapr_data_element_name, yes3_fmapr_lov_value ){
    return 'picker-' + FMAPR.specification_index +
       '-' + FMAPR.normalizeString(yes3_fmapr_data_element_name) +
       '-' + FMAPR.normalizeString(yes3_fmapr_lov_value)
       ;
 }
 
 FMAPR.typeInputId = function(){
    return 'type-' + FMAPR.specification_index;
 }

 FMAPR.itemTypeClass = function(element_origin)
 {
     return ( element_origin==='redcap' ) ? 'yes3-fmapr-redcap' : 'yes3-fmapr-specification';
 }
 
 FMAPR.getElementInputHtml = function( yes3_fmapr_data_element_name, element_origin ){
    element_origin = element_origin || 'specification';
    let typeClass = FMAPR.itemTypeClass(element_origin);
    let id = FMAPR.elementInputId( yes3_fmapr_data_element_name );
    let html =`<input type='text' id=${id} class='yes3_fmapr_field_autocomplete ${typeClass} yes3-fmapr-input-element yes3-fmapr-item' data-specification_index='${FMAPR.specification_index}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_field_name' data-element_origin='${element_origin}' placeholder='Start typing or spacebar for all' />`;
    return html;
 }
 
 FMAPR.getElementEventHtml = function( yes3_fmapr_data_element_name, element_origin ){
    element_origin = element_origin || 'specification';
    let typeClass = FMAPR.itemTypeClass(element_origin);
    let id = FMAPR.elementEventId( yes3_fmapr_data_element_name );
    //let html = `<select id=${id} class='yes3-fmapr-event-select yes3-fmapr-item' data-specification_index='${specification_index}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id'/>${FMAPR.settings.event_select_options_html}</select>`;
    let html = `<select id=${id} class='yes3-fmapr-event-select ${typeClass} yes3-fmapr-item' data-specification_index='${FMAPR.specification_index}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-mapitem='redcap_event_id' data-element_origin='${element_origin}'/></select>`;
    return html;
 }
 
 FMAPR.getLovInputHtml = function( yes3_fmapr_data_element_name, value ){
    let id = FMAPR.lovInputId( yes3_fmapr_data_element_name, value );
    let pickerWrapperId = FMAPR.pickerWrapperId( yes3_fmapr_data_element_name, value );
    return `<input type='text' id='${id}' class='yes3-fmapr-input-lov yes3-fmapr-item' data-specification_index='${FMAPR.specification_index}' data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}' data-yes3_fmapr_lov_value='${value}' data-mapitem='redcap_field_value' /><div class='yes3-fmapr-value-picker-wrapper' id='${pickerWrapperId}'></div>`;
 }
 
 FMAPR.setLovInputListeners = function() {
 
    let tableId = FMAPR.specificationTableId( FMAPR.specification_index );
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
                "specification_index": FMAPR.specification_index,
                "yes3_fmapr_data_element_name": yes3_fmapr_data_element_name,
                "lov_value": yes3_fmapr_lov_value,
                "parentCtl": parentCtl
             }
 
             FMAPR.YesNo( `Okay to make '${yes3_fmapr_lov_value}' the response for all exports?`, FMAPR.setLovConstantExecute );
 
          } else {
 
             if ( value ) {
                let pickerWrapperId = FMAPR.pickerWrapperId(yes3_fmapr_data_element_name, yes3_fmapr_lov_value);
                let values = value.split(',');
                $(`div#${pickerWrapperId} input[type=checkbox]`).prop('checked', false);
                for ( let i=0; i<values.length; i++ ){
                   $(`div#${pickerWrapperId} input[type=checkbox][value=${values[i]}]`).prop('checked', true);
                }
             }
 
             FMAPR.setLovTogglePriorities(yes3_fmapr_data_element_name);
 
          }
 
          //console.log('LovInputListener', yes3_fmapr_data_element_name, value );
       })
    ;
 }

FMAPR.setContextMenuListeners = function()
{
    $('tr.yes3-fmapr-redcap-field').off().on("contextmenu", function(e){
        console.log( e );

        FMAPR.REDCapFieldContextMenu($(this), e);

        return false;
    });
}

FMAPR.setREDCapElementListeners = function()
{
    $('span.yes3-redcap-element').on("click", function(e){
        e.stopPropagation();
        FMAPR.REDCapFieldContextMenu($(this), e);
    })
}

FMAPR.REDCapFieldContextMenu = function( element, e )
{
    console.log( e );

    let panelParentOffset = $('div#yes3-contextmenu-panel').parent().offset();

    let field_name = element.closest('tr').find('input.yes3-fmapr-input-element').first().val();

    let html = "";

    html = field_name
        + "<br>"
        + "<br>insert form fields above"
        + "<br>insert form fields below"
        + "<br>"
        + "<br>insert all remaining fields above"
        + "<br>insert all remaining fields below"
    ;

    $('tr.yes3-row-selected').removeClass('yes3-row-selected');

    element.closest('tr').addClass('yes3-row-selected');

    //console.log( panelParentOffset );

    e.preventDefault();

    $('div#yes3-contextmenu-content').html(html);

    YES3.openPanel("yes3-contextmenu-panel", true
        , e.pageX - panelParentOffset.left + 10
        , e.pageY - panelParentOffset.top - $('div#yes3-contextmenu-panel').outerHeight() - 10
    );

    return false;

}

 FMAPR.setValuePickers = function( yes3_fmapr_data_element_name, redcap_field_name ) {
 
    let tblId = FMAPR.specificationTableId( FMAPR.specification_index );
    let tbl = $('table#' + tblId);

    //console.log('setValuePickers', FMAPR.specification_index, yes3_fmapr_data_element_name, redcap_field_name);
 
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
 
    $(`input[type=text][data-specification_index=${FMAPR.specification_index}].yes3_fmapr_field_autocomplete`).each(function () {
 
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
       })
       ;
 
    })
 
 }

 FMAPR.REDCapFieldOnChange = function( fld )
 {

    fld.parent().parent().find('select.yes3-fmapr-event-select').empty();

    let field_name = fld.val();

    if ( !field_name ){
        return false;
    }

    let yes3_fmapr_data_element_name = fld.data('yes3_fmapr_data_element_name');
    let optionsHtml = "";
    let formEvents = FMAPR.settings.form_metadata[FMAPR.settings.field_metadata[FMAPR.settings.field_index[field_name]].form_name].form_events;
    
    let raw = FMAPR.isRawREDCapDataElement(yes3_fmapr_data_element_name);

    if ( raw && formEvents.length > 1 ){
        optionsHtml += '<option value="0">all events</option>';
    }

    for ( let e=0; e<formEvents.length; e++ ){
        optionsHtml += `<option value=${formEvents[e].event_id}>${formEvents[e].descrip}</option>`;                 
    }

    if ( !raw ) {
        FMAPR.setValuePickers( yes3_fmapr_data_element_name, field_name );
    }

    fld.parent().parent().find('select.yes3-fmapr-event-select').append(optionsHtml);
 }
 
 FMAPR.setLovTogglePriorities = function(yes3_fmapr_data_element_name) {
 
    yes3_fmapr_data_element_name = yes3_fmapr_data_element_name || '';
 
    var dataElementRows;
 
    if ( yes3_fmapr_data_element_name ) {
       dataElementRows = $(`table.yes3-fmapr[data-specification_index=${FMAPR.specification_index}] tr.yes3-fmapr-data-element[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);
    } else {
       dataElementRows = $(`table.yes3-fmapr[data-specification_index=${FMAPR.specification_index}] tr.yes3-fmapr-data-element`);
    }
 
    dataElementRows.each(function () {
       let yes3_fmapr_data_element_name = $(this).data('yes3_fmapr_data_element_name');
       let icon = $(this).find('i.fas:first');
 
       if ( icon ) {
          let inputCtl = $(this).find('input[type=text].yes3_fmapr_field_autocomplete:first');
          let entries = 0;
 
          let inputs = $(`table.yes3-fmapr[data-specification_index=${FMAPR.specification_index}] tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'] input[type=text].yes3-fmapr-input-lov`);
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
    let lovCtl = $( `input[type=text][data-specification_index='${FMAPR.specification_index}'][data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}'][data-yes3_fmapr_lov_value='${lov_value}'].yes3-fmapr-input-lov:first` );
 
    lovCtl.val('*').trigger('change');
 }
 /**
  *
  */
 FMAPR.setLovConstantExecute = function() {
 
    inputs = $(`input[type=text][data-specification_index=${FMAPR.params.specification_index}][data-yes3_fmapr_data_element_name='${FMAPR.params.yes3_fmapr_data_element_name}'].yes3-fmapr-input-lov`);
 
    inputs.each( function () {
 
       if ( $(this).data('yes3_fmapr_lov_value') !== FMAPR.params.lov_value ){
          $(this).val("");
       }
 
    })
 
    FMAPR.params.parentCtl.val( `constant: '${FMAPR.params.lov_value}'` );
 
    FMAPR.setLovTogglePriorities(FMAPR.params.yes3_fmapr_data_element_name);
 
    //console.log('setLovConstantExecute', FMAPR.params);
 }

FMAPR.saveFieldMappings = function() {

    let tableId = FMAPR.specificationTableId( FMAPR.specification_index );

    let specTable = $(`table#${tableId}`);

    let specification = {
        "specification_index": FMAPR.specification_index,
        "elements": []
    };

    let elementRows = specTable.find('tr.yes3-fmapr-data-element');

    elementRows.each( function() {

        let yes3_fmapr_data_element_name = $(this).data('yes3_fmapr_data_element_name');

        let redcap_field_name = $(this).find('input[data-mapitem=redcap_field_name]:first').val();
        let redcap_event_id = $(this).find('select[data-mapitem=redcap_event_id]:first').val();

        if ( redcap_field_name ) {

            let element = {
                "yes3_fmapr_data_element_name": yes3_fmapr_data_element_name,
                "redcap_event_id": redcap_event_id,
                "redcap_field_name": redcap_field_name,
                "values": []
            }

            let valueRows = specTable.find(`tr.yes3-fmapr-lov[data-yes3_fmapr_data_element_name='${yes3_fmapr_data_element_name}']`);
            
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

            specification.elements.push( element );

        } // redcap field name nonblank

    }) // elementRows
 
    //console.log( 'saveFieldMappings', specification );
 
    FMAPR.requestService( { 
        "request": "save_field_mappings", 
        "specification_index": FMAPR.specification_index,
        "field_mappings": specification 
    }, FMAPR.saveFieldMappingsCallback, false );
 }
 
 FMAPR.saveFieldMappingsCallback = function( response ){
    console.log( 'saveFieldMappingsCallback', response );
    FMAPR.postMessage( response );
 }
 
 $(document).on('yes3-fmapr.settings', function () {
 
})

$(window).resize( function() {

    FMAPR.resizeFieldMapperTable();

})
 
$( function () {

    FMAPR.getProjectSettings();

})



 

 