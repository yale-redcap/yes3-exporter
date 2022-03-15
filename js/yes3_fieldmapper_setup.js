
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

YES3.Functions.MappingsEditor_openForm = function()
{
    //console.log('MappingsEditor_openForm' this);

    let specNum = FMAPR.getParentSpecNum($(this));

    let editor = FMAPR.mappingsEditor();

    let specTbl = FMAPR.exportSpecificationTable(specNum);

    let export_name = specTbl.find("input[data-setting=export_name]").val();
    let mapping_specification = specTbl.find("textarea[data-setting=mapping_specification]").val();

    editor.attr({"data-specnum": specNum})
        .find("textarea").val( mapping_specification )
    ;

    editor.find("span#yes3-fmapr-mappings-editor-export-name").html(export_name);

    YES3.openPanel("yes3-fmapr-mappings-editor");
}

YES3.Functions.ExportSpecificationEditor_expand = function()
{
    let specNum = FMAPR.getParentSpecNum( $(this) );
    FMAPR.toggleExportSpecification(specNum, 1);
}

YES3.Functions.ExportSpecificationEditor_collapse = function()
{
    let specNum = FMAPR.getParentSpecNum( $(this) );
    FMAPR.toggleExportSpecification(specNum, 0);
}

YES3.Functions.Exportspecifications_saveSettings = function()
{
    //console.log('MappingsEditor_undoSettings' this);

    let errors = FMAPR.inspectExportSpecificationSettings() +
                 FMAPR.inspectEventPrefixes();

    if ( errors ){
        FMAPR.postMessage("NOT saved: please correct the indicated errors.", true);
    }
    else {
        FMAPR.postMessage("No errors detected.", false);
        FMAPR.saveExportSettings();
    }
}

YES3.Functions.Exportspecifications_undoSettings = function()
{
    //console.log('MappingsEditor_undoSettings' );

    YES3.YesNo("Are you SURE you would like to roll back all changes since the last save?", FMAPR.getExportSettings, null);
}

YES3.Functions.removeExportSpecificationToggle = function()
{
    let specTbl = FMAPR.getParentTable( $(this) );
    
    let removed = ( specTbl.find("input[data-setting=removed]").val() === "1" ) ? "0" : "1";

    FMAPR.setRemovedStatus(specTbl, removed);
}

FMAPR.setRemovedStatus = function( specTbl, removed, hide_if_removed )
{
    if ( removed==="1" ){

        specTbl.addClass('yes3-removed');
    }
    else {

        specTbl.removeClass('yes3-removed');
    }

    specTbl.find("input[data-setting=removed]").val(removed);

    if ( removed==="1" && hide_if_removed ){
        specTbl.hide();
    }
}

FMAPR.showRemoved = function()
{
    //return true;
    
    let show_removed = $("input#yes3-fmapr-show-removed").prop("checked");

    FMAPR.exportSpecificationTables().each(function(){

        let removed = $(this).find("input[data-setting=removed]").val();

        if ( show_removed || removed==="0" ){

            $(this).show();
        }
        else {

            $(this).hide();
        }
    })
}


/* === FMAPR FUNCTIONS === */
 
FMAPR.saveExportSettings = function()
{
    let events = [];
    let specifications = [];

    /**
     * event id, name, prefix
     */
    FMAPR.eventPrefixesTable().find('tr:not(.yes3-fmapr-event-prefixes-header)').each(function(){
        let event_id = $(this).attr('id').split('-')[1];
        let event_name = $(this).find("td[data-setting=event_name]").text();
        let event_prefix = $(this).find("input[data-setting=event_prefix]").val();

        events.push({
            "event_id": event_id,
            "event_name": event_name,
            "event_prefix": event_prefix
        });
    })

    FMAPR.exportSpecificationTables().each(function(){

        let export_uuid = $(this).find("input[data-setting=export_uuid]").val();
        let removed = $(this).find("input[data-setting=removed]").val();
        let export_name = $(this).find("input[data-setting=export_name]").val();
        let export_layout = $(this).find("input[data-setting=export_layout]:checked").val() || "";
        let export_selection = $(this).find("input[data-setting=export_selection]:checked").val() || "";
        
        let export_target = $(this).find("input[data-setting=export_target]:checked").val() || "";
        let export_target_folder = $(this).find("input[data-setting=export_target_folder]").val() || "";

        let export_criterion_field = ( export_selection === "2" ) ? $(this).find("input[data-setting=export_criterion_field]").val() || "" : "";
        let export_criterion_event = ( export_selection === "2" ) ? $(this).find("select[data-setting=export_criterion_event]").val() || "" : "";
        let export_criterion_value = ( export_selection === "2" ) ? $(this).find("input[data-setting=export_criterion_value]").val() || "" : "";

        let mapping_specification_json = $(this).find("textarea[data-setting=mapping_specification]").val() || "";
        let mapping_specification = [];

        if ( mapping_specification_json.length > 2 ) {
            try {

                mapping_specification = JSON.parse( mapping_specification_json );
            } catch (e) {
                
                mapping_specification = [];
            }
        }

        specifications.push({
            "export_uuid": export_uuid,
            "export_name": export_name,
            "export_layout": export_layout,
            "export_selection": export_selection,
            "export_criterion_field": export_criterion_field,
            "export_criterion_event": export_criterion_event,
            "export_criterion_value": export_criterion_value,
            "export_target": export_target,
            "export_target_folder": export_target_folder,
            "mapping_specification": mapping_specification,
            //"field_mappings": [],
            "removed": removed
        });
    });

    console.log('saveExportSettings', specifications);

    let requestParams = {
        "request": "saveExportSettings",
        "events": JSON.stringify(events),
        "specifications": JSON.stringify(specifications)
    }

    YES3.requestService(requestParams, FMAPR.saveExportSettingsCallback, false);

    //console.log(events); 
    //console.log(specifications); 
}

FMAPR.saveExportSettingsCallback = function(response)
{
    FMAPR.postMessage(response);
    FMAPR.markAsClean();
}

FMAPR.getExportSettings = function()
{

    YES3.requestService({"request": "getExportSettings"}, FMAPR.getExportSettingsCallback, true);  
}

FMAPR.getExportSettingsCallback = function(response)
{
    //console.log('getExportSettingsCallback:', response);

    FMAPR.stored_export_settings = response;

    //if ( !FMAPR.stored_export_settings.event_settings ){
        
        //FMAPR.stored_export_settings.event_settings = [];
    //}

    //if ( !FMAPR.stored_export_settings.specification_settings ){
        
        //FMAPR.stored_export_settings.specification_settings = [];
    //}

    FMAPR.populateAllSettings();
}

FMAPR.populateAllSettings = function()
{

    if ( YES3.moduleProperties.isLongitudinal ){

        $('.yes3-fmapr-longitudinal-only').show();

        FMAPR.populateSetupEventTable();

        FMAPR.setExportEventPrefixListeners();
    }
    else {

        $('.yes3-fmapr-longitudinal-only').hide();
    }

    YES3.setActionIconListeners( YES3.container() );

    FMAPR.populateExportSpecificationsTables();

    FMAPR.markAsClean();

    FMAPR.postMessage("All settings loaded.");
}

FMAPR.populateSetupEventTable = function()
{
    let html = "";

    let event_id = "";

    let event_name = "";

    let event_prefix = "";

    for ( let e=0; e<FMAPR.stored_export_settings.event_settings.length; e++){

        event_id = FMAPR.stored_export_settings.event_settings[e].event_id;
        event_name = FMAPR.stored_export_settings.event_settings[e].event_name;
        event_prefix = FMAPR.stored_export_settings.event_settings[e].event_prefix;

        html += `<tr id='event-${event_id}'><td data-setting='event_name'>${event_name}</td><td><input type='text' data-setting='event_prefix' value='${event_prefix}' /></td></tr>`;
    }

    FMAPR.eventPrefixesTable().find('tbody').empty().append(html);

    //FMAPR.resizeSetupEventTable();

    //FMAPR.setInputListeners();
}

FMAPR.populateExportSpecificationsTables = function()
{
    let specTbl = null;

    let export_name = "";
    let export_uuid = "";
    let removed = "";
    let export_layout = "";
    let export_selection = "";
    let export_criterion_field = "";
    let export_criterion_event = "";
    let export_criterion_value = "";
    let export_target = "";
    let export_target_folder = "";
    let mapping_specification = [];
    let mapping_specification_json = "";
    let specNum = "";

    FMAPR.removeAllExportSpecificationTables();

    for ( let s=0; s<FMAPR.stored_export_settings.specification_settings.length; s++ ){
        
        specTbl = FMAPR.appendBlankExportSpecification();

        specNum = specTbl.attr("data-specnum"); // required for some function calls

        FMAPR.clearLastRowItemFlag(); // prevents auto append of blank specTable on change

        //console.log('populateExportSpecificationsTables TOP, s='+s+', len='+FMAPR.stored_export_settings.specification_settings.length);

        export_name = FMAPR.stored_export_settings.specification_settings[s].export_name || "";
        export_uuid = FMAPR.stored_export_settings.specification_settings[s].export_uuid || "???";
        export_layout = FMAPR.stored_export_settings.specification_settings[s].export_layout || "";
        removed = FMAPR.stored_export_settings.specification_settings[s].removed || "0";
        export_selection = FMAPR.stored_export_settings.specification_settings[s].export_selection || "";
        export_criterion_field = FMAPR.stored_export_settings.specification_settings[s].export_criterion_field || "";
        export_criterion_event = FMAPR.stored_export_settings.specification_settings[s].export_criterion_event || "";
        export_criterion_value = FMAPR.stored_export_settings.specification_settings[s].export_criterion_value || "";

        export_target = FMAPR.stored_export_settings.specification_settings[s].export_target || "";
        export_target_folder = FMAPR.stored_export_settings.specification_settings[s].export_target_folder || "";

        mapping_specification = FMAPR.stored_export_settings.specification_settings[s].mapping_specification || [];

        specTbl.attr({"data-export_uuid": export_uuid});

        if ( !mapping_specification.length ){

            mapping_specification_json = "";
        }
        else {

            mapping_specification_json = JSON.stringify(mapping_specification, null, 4);        
        }

        specTbl.find('input[data-setting=export_name]').val(export_name);

        specTbl.find('input[data-setting=export_uuid]').val(export_uuid);
        specTbl.find('input[data-setting=removed]').val(removed);

        specTbl.find(`input[data-setting=export_layout][value=${export_layout}]`).prop('checked', true);
        specTbl.find(`input[data-setting=export_selection][value=${export_selection}]`).prop('checked', true);
        
        //console.log('populateExportSpecificationsTables WATCH, s='+s+', len='+FMAPR.stored_export_settings.specification_settings.length);

        specTbl.find('input[data-setting=export_criterion_field]').val(export_criterion_field).trigger('change');
        specTbl.find('select[data-setting=export_criterion_event]').val(export_criterion_event);
        specTbl.find('input[data-setting=export_criterion_value]').val(export_criterion_value);

        specTbl.find(`input[data-setting=export_target][value=${export_target}]`).prop('checked', true);
        specTbl.find('input[data-setting=export_target_folder]').val(export_target_folder);

        specTbl.find('textarea[data-setting=mapping_specification]').val(mapping_specification_json);

        FMAPR.reportExportMappingsLength( specNum );

        FMAPR.setCriterionEventSelectOptions( specTbl );

        FMAPR.exportSpecificationTableSkipper( specTbl );

        //FMAPR.setExportLayoutListeners( specTbl );

        FMAPR.setExportTargetListeners( specTbl );

        FMAPR.setRemovedStatus( specTbl, removed, true );

        FMAPR.toggleExportSpecification( specNum, 0 ); // collapse the table


    }

    FMAPR.appendBlankExportSpecification();
}

/**
 * deprecated but has useful code
 * 
 * @returns void
 */
FMAPR.resizeSetupEventTable = function()
{
    let scrollbarWidth = 20;

    let fmaprTable = FMAPR.eventPrefixesTable();

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
        - (parentSection.outerHeight() - parentSection.height())
    ;

    // position() returns offset relative to parent object (the table)
    let bodyHeight = tableHeight - fmaprTableBody.position().top;

    let tableWidth = fmaprTable.width();

    let cellWidth2 = (tableWidth - scrollbarWidth) / 2;

    //fmaprTable.css({'width': tableWidth+'px', 'height': tableHeight+'px'});
    fmaprTable.css({'height': tableHeight+'px'});

    fmaprTableBody.css({'height': bodyHeight+'px'});

    fmaprTable.find('th, td').css({'width': cellWidth2+'px', 'max-width': cellWidth2+'px'});
}

FMAPR.specToRemove = -1;

FMAPR.Exportspecs_collapseAll = function()
{
    $('table.yes3-fmapr-expanded').removeClass('yes3-fmapr-expanded').addClass('yes3-fmapr-collapsed');
    FMAPR.showOrHideCollapsibles();
}

/**
 * Uses CSS classes to show or hide UI elements (table rows mostly).
 * The .yes3-fmapr-skipped-over and .yes3-fmapr-beta class blocks display of UI elements that
 * are hidden by skip pattern or beta access rules 
 * (e.g., selection criterion field, event, value for 'all records' selection)
 */
FMAPR.showOrHideCollapsibles = function()
{
    $('table.yes3-fmapr-collapsed .yes3-fmapr-if-expanded').hide();
    $('table.yes3-fmapr-expanded .yes3-fmapr-if-collapsed').hide();

    if ( FMAPR.project.beta===1 ){

        $('table.yes3-fmapr-collapsed .yes3-fmapr-if-collapsed:not(.yes3-fmapr-skipped-over)').show();
        $('table.yes3-fmapr-expanded .yes3-fmapr-if-expanded:not(.yes3-fmapr-skipped-over)').show();
    }
    else {

        $('table.yes3-fmapr-collapsed .yes3-fmapr-if-collapsed:not(.yes3-fmapr-skipped-over):not(.yes3-fmapr-beta)').show();
        $('table.yes3-fmapr-expanded .yes3-fmapr-if-expanded:not(.yes3-fmapr-skipped-over):not(.yes3-fmapr-beta)').show();
        $('.yes3-fmapr-beta').hide();
    }
}

FMAPR.setExportSpecificationEventSelectOptions = function(specNum)
{
    let html = "<option value=''>select a REDCap event</option>";

    for ( var prop in FMAPR.project.event_metadata ){

        if (Object.prototype.hasOwnProperty.call(FMAPR.project.event_metadata, prop)) {
            
            html += `<option value='${FMAPR.project.event_metadata[prop].event_name}'>${FMAPR.project.event_metadata[prop].event_name}</option>`;
        }
    }

    $(`table[data-specnum=${specNum}] select[data-setting='export_criterion_event']`).empty().append(html);
}

FMAPR.setTemplateEventSelectOptions = function()
{
    let html = "<option value=''>select a REDCap event</option>";

    for ( var prop in FMAPR.project.event_metadata ){

        if (Object.prototype.hasOwnProperty.call(FMAPR.project.event_metadata, prop)) {
            
            html += `<option value='${FMAPR.project.event_metadata[prop].event_name}'>${FMAPR.project.event_metadata[prop].event_name}</option>`;
        }
    }

    $(`table[data-specnum=9999] select[data-setting='export_criterion_event']`).empty().append(html);
}

FMAPR.setExportSpecificationListeners = function(tbl)
{
    FMAPR.setExportSpecificationInputListeners(tbl);
    FMAPR.setExportSpecificationNameListener(tbl);
    FMAPR.setExportSpecificationCriterionFieldListener(tbl);
}

FMAPR.setExportSpecificationInputListeners = function(tbl) {

    tbl.find('input, select')
        .off()
        .on('change', function(){

            let parentTable = FMAPR.getParentTable( $(this) );
    
            FMAPR.markAsDirty();

            FMAPR.exportSpecificationTableSkipper( parentTable );

            if ( FMAPR.isLastRowItem( $(this) ) ){

                FMAPR.appendBlankExportSpecification();
            }
        })
    ;
}

FMAPR.setExportSpecificationNameListener = function(tbl) {

    // change handlers for all input and select elements EXCEPT autocompletes; they have their own
    tbl.find('input[data-setting=export_name]')
        .on('change', function(){

            FMAPR.giveThemNames();
        })
    ;
}

FMAPR.setExportSpecificationCriterionFieldListener = function(tbl)
{
    tbl.find(`input[data-setting=export_criterion_field]`)
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

        FMAPR.setCriterionEventSelectOptions( specificationParent );

        //FMAPR.markAsDirty();
     })
     ;
}

FMAPR.setCriterionEventSelectOptions = function( tbl )
{
    let eventSelect = tbl.find("select[data-setting=export_criterion_event]");

    eventSelect.empty();

    let export_criterion_field = tbl.find('input[data-setting=export_criterion_field]').val() || "";

    if ( export_criterion_field ) {

        let optionsHtml = FMAPR.eventSelectOptionsForField( export_criterion_field );

        eventSelect.append(optionsHtml);
    }
}

FMAPR.setExportEventPrefixListeners = function() {

    FMAPR.eventPrefixesTable().find('input')
        .off()
        .on('change', function(){
    
            FMAPR.markAsDirty();
        })
    ;
}

FMAPR.setExportLayoutListeners = function( tbl ) {

    tbl.find('input[data-setting=export_layout]')
        .off()
        .on('click', function(){

            //console.log('setExportLayoutListeners', $(this).val());

            if ( $(this).val()==="r" ){

                $(this).closest('table').find('tr.yes3-fmapr-repeating-only').show();
            }
            else {

                $(this).closest('table').find('tr.yes3-fmapr-repeating-only').hide();
            }
    
            FMAPR.markAsDirty();
        })
    ;
}

FMAPR.setExportTargetListeners = function( tbl ) {

    tbl.find('input[data-setting=export_target]')
        .off()
        .on('click', function(){

            let parentTable = FMAPR.getParentTable( $(this) );
    
            FMAPR.markAsDirty();

            FMAPR.exportSpecificationTableSkipper( parentTable );

            //console.log('setExportLayoutListeners', $(this).val());
/*
            if ( $(this).val()==="filesystem" ){

                $(this).closest('table').find('tr.yes3-fmapr-target-filesystem-only').show();
            }
            else {

                $(this).closest('table').find('tr.yes3-fmapr-target-filesystem-only').hide();
            }
    
            FMAPR.markAsDirty();
            */
        })
    ;
}

FMAPR.exportSpecificationTableSkipper = function ( tbl )
{
    let export_selection = tbl.find('input[data-setting=export_selection]:checked').val() || "0";
    let export_target    = tbl.find('input[data-setting=export_target]:checked').val() || "";

    if ( export_selection==="2" ) {

        tbl.find(".yes3-fmapr-if-selected").show().removeClass('yes3-fmapr-skipped-over');
    }
    else {

        tbl.find(".yes3-fmapr-if-selected").hide().addClass('yes3-fmapr-skipped-over');
    }

    if ( export_target==="filesystem" ) {

        tbl.find(".yes3-fmapr-target-filesystem-only").show().removeClass('yes3-fmapr-skipped-over');
    }
    else {

        tbl.find(".yes3-fmapr-target-filesystem-only").hide().addClass('yes3-fmapr-skipped-over');
    }
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

FMAPR.exportSpecificationTableId = function(specNum)
{
    return "yes3-fmapr-export-specification-" + specNum;
}

FMAPR.exportSpecificationTable = function(specNum)
{
    return $("table#" + FMAPR.exportSpecificationTableId(specNum));
}

FMAPR.exportSpecificationTables = function()
{
    return $("table.yes3-fmapr-export-specification:not(.yes3-fmapr-spec-lastrow-item");
}

FMAPR.removeAllExportSpecificationTables = function()
{
    $("table.yes3-fmapr-export-specification").remove();
}

FMAPR.eventPrefixesTable = function()
{
    return $("table#yes3-fmapr-setup-events");
}

FMAPR.exportSpecificationsTemplate = function()
{
    return $(`table#yes3-fmapr-settings-template`);
}

FMAPR.exportSpecificationsParentElement = function()
{
    return $("div#yes3-fmapr-export-specifications");
}

FMAPR.isLastRowItem = function( that )
{
    return that.closest('table').hasClass('yes3-fmapr-spec-lastrow-item');
}

FMAPR.clearLastRowItemFlag = function()
{
    FMAPR.exportSpecificationsParentElement().find('.yes3-fmapr-spec-lastrow-item').removeClass('yes3-fmapr-spec-lastrow-item');
}

FMAPR.giveThemNames = function()
{
    $("table.yes3-fmapr-export-specification").each(function(){
        let export_name = $(this).find("input[data-setting=export_name]").val();
        if ( export_name ){
            $(this).find("th:nth-child(1).yes3-fmapr-export-header").text( export_name.escapeHTML() );
        }
    })
}

FMAPR.appendBlankExportSpecification = function()
{
    let html = "";

    let specNum = -1;

    let export_uuid = YES3.uuidv4();

    $('table.yes3-fmapr-export-specification').each(function(){
        let rowSpecNum = parseInt($(this).attr("data-specnum"));
        if ( rowSpecNum > specNum ){
            specNum = rowSpecNum;
        }
    });

    specNum++;

    FMAPR.clearLastRowItemFlag();

    let newTable = FMAPR.exportSpecificationsTemplate()
        .clone(true)
        .attr({
            id: FMAPR.exportSpecificationTableId(specNum),
            "data-specnum": specNum,
            "data-export_uuid": export_uuid
        })
        .addClass("yes3-fmapr-export-specification")
    ;

    newTable.find('input[data-setting=export_uuid]').val(export_uuid);
    newTable.find('input[data-setting=removed]').val("0");

    newTable.find('input[data-setting=export_layout][value=h]').attr({'id': `yes3-fmapr-export-layout-${specNum}-h`, 'name': `yes3-fmapr-export-layout-${specNum}`});
    newTable.find('input[data-setting=export_layout][value=v]').attr({'id': `yes3-fmapr-export-layout-${specNum}-v`, 'name': `yes3-fmapr-export-layout-${specNum}`});
    newTable.find('input[data-setting=export_layout][value=r]').attr({'id': `yes3-fmapr-export-layout-${specNum}-r`, 'name': `yes3-fmapr-export-layout-${specNum}`});

    newTable.find('input[data-setting=export_selection][value=1]').attr({'id': `yes3-fmapr-export-selection-${specNum}-1`, 'name': `yes3-fmapr-export-selection-${specNum}`});
    newTable.find('input[data-setting=export_selection][value=2]').attr({'id': `yes3-fmapr-export-selection-${specNum}-2`, 'name': `yes3-fmapr-export-selection-${specNum}`});

    newTable.find('label[for=yes3-fmapr-export-selection-9999-1]').attr({"for": `yes3-fmapr-export-selection-${specNum}-1`});
    newTable.find('label[for=yes3-fmapr-export-selection-9999-2]').attr({"for": `yes3-fmapr-export-selection-${specNum}-2`});

    newTable.find('input[data-setting=export_target][value=download]').attr({'id': `yes3-fmapr-export-target-${specNum}-download`, 'name': `yes3-fmapr-export-target-${specNum}`});
    newTable.find('input[data-setting=export_target][value=filesystem]').attr({'id': `yes3-fmapr-export-target-${specNum}-filesystem`, 'name': `yes3-fmapr-export-target-${specNum}`});

    newTable.find('label[for=yes3-fmapr-export-target-9999-download]').attr({"for": `yes3-fmapr-export-target-${specNum}-download`});
    newTable.find('label[for=yes3-fmapr-export-target-9999-filesystem]').attr({"for": `yes3-fmapr-export-target-${specNum}-filesystem`});

    newTable.find('label[for=yes3-fmapr-export-layout-9999-h]').attr({"for": `yes3-fmapr-export-layout-${specNum}-h`});
    newTable.find('label[for=yes3-fmapr-export-layout-9999-v]').attr({"for": `yes3-fmapr-export-layout-${specNum}-v`});
    newTable.find('label[for=yes3-fmapr-export-layout-9999-r]').attr({"for": `yes3-fmapr-export-layout-${specNum}-r`});

    FMAPR.setExportSpecificationListeners( newTable );

    newTable.appendTo( FMAPR.exportSpecificationsParentElement() );

    FMAPR.reportExportMappingsLength( specNum );

    //FMAPR.setAutocompleteFieldnameInput(specNum);

    //FMAPR.setInputListeners();

    FMAPR.showOrHideCollapsibles();

    FMAPR.giveThemNames();

    return newTable;
}

FMAPR.reportStatus = function() {
    // just a stub, required by FMAPR.markAsDirty()
}

FMAPR.getParentSpecNum = function( that )
{
    return that.closest("table.yes3-fmapr-export-specification").attr("data-specnum");
}

FMAPR.getParentTable = function( that )
{
    return that.closest("table");
}

FMAPR.toggleExportSpecification = function(specNum, expand)
{
    let specTable = $(`table#yes3-fmapr-export-specification-${specNum}`);

    if ( expand ){

        if ( specTable.hasClass('yes3-fmapr-collapsed')) {

            specTable.removeClass('yes3-fmapr-collapsed').addClass('yes3-fmapr-expanded');
        }
    }
    else {

        if ( specTable.hasClass('yes3-fmapr-expanded')) {
            
            specTable.removeClass('yes3-fmapr-expanded').addClass('yes3-fmapr-collapsed');
        }
    }

    FMAPR.showOrHideCollapsibles();
}

FMAPR.mappingsEditor = function()
{
    return $("div#yes3-fmapr-mappings-editor");
}

FMAPR.MappingsEditor_closeForm = function()
{
    FMAPR.mappingsEditor().find("textarea").val("");
    YES3.closePanel("yes3-fmapr-mappings-editor");
}

FMAPR.MappingsEditor_saveAndClose = function()
{
    let editor = FMAPR.mappingsEditor();

    let specNum = editor.attr("data-specnum");

    let specTbl = FMAPR.exportSpecificationTable(specNum);

    let mapping_specification = editor.find("textarea").val();

    specTbl.find("textarea[data-setting=mapping_specification]").val( mapping_specification );

    FMAPR.reportExportMappingsLength(specNum);

    FMAPR.markAsDirty();
    
    FMAPR.MappingsEditor_closeForm();
}

/* === AUDITS === */

FMAPR.reportExportMappingsLength = function(specNum)
{
    let specTbl = FMAPR.exportSpecificationTable(specNum);

    let specMap = specTbl.find("textarea[data-setting=mapping_specification]");

    let reportElement = specTbl.find("span.yes3-fmapr-export-mappings-length");

    /**
     * specMap is a beta feature (March 2022), and won't be visible if 'beta' is checked 'No' in the EM config. 
     * This prevents errors caused by scanning a bad JSON string saved when 'beta' was checked on in EM config.
     */
    if ( !reportElement.is(':visible') ){

        return true;
    }

    let mapping_specification_json = specMap.val();

    if ( !mapping_specification_json ){
        reportElement.text( "no mappings" );
        FMAPR.markAsGood( reportElement );
        return true;
    }
    else {

        try {
            mapping_specification_length = JSON.parse( mapping_specification_json ).length;
            reportElement.text( mapping_specification_length + " mappings" );
            FMAPR.markAsGood( reportElement );
            return true;
       } catch (e) {
            reportElement.html( "<span class='yes3-alert'>JSON error!</span>" );
            FMAPR.markAsBad( reportElement );
            return false;
        }
    }
}

FMAPR.inspectEventPrefixes = function()
{
    FMAPR.eventPrefixesTable().find(".yes3-error").removeClass("yes3-error");

    let errors = 0;

    FMAPR.eventPrefixesTable().find("input").each(function(){

        if ( !$(this).val() ){
            FMAPR.markAsBad( $(this) );
            errors++;
        }
    })

    return errors;
}

FMAPR.inspectExportSpecificationSettings = function()
{
    let specTables = $("table.yes3-fmapr-export-specification:not(.yes3-fmapr-spec-lastrow-item):not(.yes3-removed)");

    let errors = 0;

    specTables.find(".yes3-error").removeClass("yes3-error");

    specTables.each(function(){

        let specNum = $(this).attr("data-specnum");

        //console.log("inspectExportSpecificationSettings: #" + specNum);

        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_name");
        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_layout");
        
        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_target");
        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_target_folder");

        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_selection");
        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_criterion_field");
        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_criterion_event");
        errors += FMAPR.checkBlankSpecificationSetting( $(this), "export_criterion_value");

        errors += ( FMAPR.reportExportMappingsLength(specNum) ? 0:1 );
    })

    return errors;
}

FMAPR.checkBlankSpecificationSetting = function( specTable, settingName )
{
    let setting = specTable.find(`[data-setting=${settingName}]`);

    if ( !setting.is(':visible') ) {
        return 0;
    }

    let type = setting.attr("type") || "select";

    let value = "";

    if ( type==="radio" ){
        for ( let i=0; i<setting.length; i++){
            if ( $(setting[i]).prop("checked") ) {
                value = $(setting[i]).val();
                break;
            }
        }
    }
    else {
        value = setting.val();
    }

    if ( !value ){
        FMAPR.markAsBad( setting );
        return 1;
    }

    return 0;
}

FMAPR.markAsBad = function( element )
{
    element.closest('tr').find('td:not(.yes3-gutter-right-center), input, select, span, label, i').addClass('yes3-error');
}

FMAPR.markAsGood = function( element )
{
    element.closest('tr').find('td, input, select, span, label, i').removeClass('yes3-error');
}

FMAPR.setTemplateEventHandlers = function()
{
    let tTable = $('table#yes3-fmapr-settings-template');

    YES3.setActionIconListeners( tTable );
}

$(window).resize( function() {
    
    //FMAPR.resizeSetupEventTable();

})

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
 
$( function () {

    /**
     * This variable is not relevant to this plugin
     * but is required by FMAPR.displayActionIcons (located in FMAPR.yes3_fieldmapper_common.js)
     */
    FMAPR.mapperLoaded = true;

    //xxxxFMAPR.setTemplateEventSelectOptions();

    FMAPR.setTemplateEventHandlers();

    /**
     * Setup chain
     * 
     * (0) yes3_fieldmapper_setup.php (plugin main page): The getCodeFor() EM method outputs JS, CSS and HTML
     *      appropriate for the plugin. This includes the YES3 JS object YES3.moduleProperties,
     *      which includes all the non-private properties of the instantiated EM class.
     * 
     * (1) FMAPR.getProjectSettings (located in FMAPR.yes3_fieldmapper_common.js)
     *      - issues service (ajax) request for REDCap project metadata
     * 
     * (2) FMAPR.getProjectSettingsCallback
     *      - populates FMAPR.project with reorganized REDCap project metadata (fields, forms, events)
     *      - triggers 'yes3-fmapr-settings' event when completed
     * 
     * (3) 'yes3-fmapr-settings' event handler
     *      - calls FMAPR.getExportSettings which issues service request for Export settings
     * 
     * (4) FMAPR.getExportSettingsCallback
     *      - populates event prefix and export specifications UI
     */

    FMAPR.getProjectSettings();

})