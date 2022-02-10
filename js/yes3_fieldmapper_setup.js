
FMAPR.populateSetupEventTable = function()
{
    let html = "";

    for ( var prop in yes3ModuleProperties.eventPrefixes ){

        if (Object.prototype.hasOwnProperty.call(yes3ModuleProperties.eventPrefixes, prop)) {
            
            html += `<tr id='event-${prop}'><td>${yes3ModuleProperties.eventPrefixes[prop].event_name}</td><td><input type='text' value='${yes3ModuleProperties.eventPrefixes[prop].event_prefix}' /></td></tr>`;

        }
    
    }

    $('table#yes3-fmapr-setup-events tbody').empty().append(html);

    //FMAPR.resizeSetupEventTable();

    FMAPR.setInputListeners();
}

FMAPR.resizeSetupEventTable = function()
{
    let scrollbarWidth = 20;

    let fmaprTable = $('table#yes3-fmapr-setup-events');

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
        - (parentSection.outerHeight() - parentSection.height())
    ;

    // position() returns offset relative to parent object (the table)
    let bodyHeight = tableHeight - fmaprTableBody.position().top;

    //let tableWidth = $('div#yes3-fmapr-wrapper').width();
    let tableWidth = fmaprTable.width();

    let cellWidth2 = (tableWidth - scrollbarWidth) / 2;

    //fmaprTable.css({'width': tableWidth+'px', 'height': tableHeight+'px'});
    fmaprTable.css({'height': tableHeight+'px'});

    fmaprTableBody.css({'height': bodyHeight+'px'});

    fmaprTable.find('th, td').css({'width': cellWidth2+'px', 'max-width': cellWidth2+'px'});
}

FMAPR.populateExportSpecificationsTable = function()
{
    FMAPR.appendBlankExportSpecification();
}

FMAPR.specToRemove = -1;

FMAPR.removeExportSpecification = function(specNum)
{
    let exportName = FMAPR.exportSpecificationTable(specNum).find("input[data-setting=export_name]").val();

    let msg = `Are you SURE you want to remove the specification '${exportName}'? This will make any work you have done with this export specification unavailable.`
     + "<br><br>Note: the specification won't be permanently removed until you save the settings. Until that point, you may click undo to roll back any changes you have made since the last save."
     ;
    FMAPR.specToRemove = specNum;
    YES3.YesNo(msg, FMAPR.removeExportSpecificationExecute);
}

FMAPR.removeExportSpecificationExecute = function()
{
    FMAPR.exportSpecificationTable(FMAPR.specToRemove).remove();
}

FMAPR.Exportspecs_collapseAll = function()
{
    $('table.yes3-fmapr-expanded').removeClass('yes3-fmapr-expanded').addClass('yes3-fmapr-collapsed');
    FMAPR.showOrHideCollapsibles();
}

FMAPR.showOrHideCollapsibles = function()
{
    $('table.yes3-fmapr-collapsed .yes3-fmapr-if-expanded').hide();
    $('table.yes3-fmapr-collapsed .yes3-fmapr-if-collapsed').show();
    $('table.yes3-fmapr-expanded .yes3-fmapr-if-collapsed').hide();
    $('table.yes3-fmapr-expanded .yes3-fmapr-if-expanded').show();
}

FMAPR.setExportSpecificationEventSelectOptions = function(specNum)
{
    let html = "<option value=''>select a REDCap event</option>";

    for ( var prop in yes3ModuleProperties.eventPrefixes ){

        if (Object.prototype.hasOwnProperty.call(yes3ModuleProperties.eventPrefixes, prop)) {
            
            html += `<option value='${yes3ModuleProperties.eventPrefixes[prop].event_name}'>${yes3ModuleProperties.eventPrefixes[prop].event_name}</option>`;
        }
    }

    $(`table[data-specnum=${specNum}] select[data-setting='export_criterion_event']`).empty().append(html);
}

FMAPR.setTemplateEventSelectOptions = function()
{
    let html = "<option value=''>select a REDCap event</option>";

    for ( var prop in yes3ModuleProperties.eventPrefixes ){

        if (Object.prototype.hasOwnProperty.call(yes3ModuleProperties.eventPrefixes, prop)) {
            
            html += `<option value='${yes3ModuleProperties.eventPrefixes[prop].event_name}'>${yes3ModuleProperties.eventPrefixes[prop].event_name}</option>`;
        }
    }

    $(`table[data-specnum=9999] select[data-setting='export_criterion_event']`).empty().append(html);
}

FMAPR.setAutocompleteFieldnameInput = function(specNum)
{
    $(`table#yes3-fmapr-export-specification-${specNum} input[data-setting=export_criterion_field]`)
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

        let optionsHtml = FMAPR.eventSelectOptionsForField( $(this).val() );

        let specificationParent = $(this).closest("table");

        specificationParent.find("select[data-setting=export_criterion_event]").empty().append(optionsHtml);

        FMAPR.markAsDirty();
     })
     ;
}

FMAPR.eventSelectOptionsForField = function( field_name )
{
    let form_events = FMAPR.settings.form_metadata[FMAPR.settings.field_metadata[FMAPR.settings.field_index[field_name]].form_name].form_events;

    let html = "";

    for (let i=0; i<form_events.length; i++){

        html += `<option value='${yes3ModuleProperties.eventPrefixes[form_events[i].event_id].event_name}'>${yes3ModuleProperties.eventPrefixes[form_events[i].event_id].event_name}</option>`;
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
    return that.hasClass('yes3-fmapr-spec-lastrow-item');
}

FMAPR.clearLastRowItemFlag = function()
{
    FMAPR.exportSpecificationsParentElement().find('.yes3-fmapr-spec-lastrow-item').removeClass('yes3-fmapr-spec-lastrow-item');
}

FMAPR.giveThemNames = function()
{
    $("table.yes3-fmapr-export-specification").each(function(){
        let export_name = $(this).find("input[data-setting=export_name").val();
        if ( export_name ){
            $(this).find("th.yes3-fmapr-export-header").html(export_name);
        }
    })
}

FMAPR.appendBlankExportSpecification = function()
{
    let html = "";

    let specNum = -1;

    $('table.yes3-fmapr-export-specification').each(function(){
        let rowSpecNum = parseInt($(this).data('specnum'));
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
            "data-export_uuid": YES3.uuidv4()
        })
        .addClass("yes3-fmapr-export-specification")
    ;

    newTable.appendTo( FMAPR.exportSpecificationsParentElement() );

    FMAPR.setAutocompleteFieldnameInput(specNum);

    FMAPR.setInputListeners();

    FMAPR.showOrHideCollapsibles();

    FMAPR.giveThemNames();
}

FMAPR.reportStatus = function() {
    // just a stub, required by FMAPR.markAsDirty()
}

FMAPR.getParentSpecNum = function( that )
{
    return that.closest("table.yes3-fmapr-export-specification").data("specnum");
}

FMAPR.setInputListeners = function() {

    // change handlers for all input and select elements EXCEPT autocompletes; they have their own
    $('div#yes3-fmapr-container input:not(.ui-autocomplete-input), div#yes3-fmapr-container select')
        .off()
        .on('change', function(){

            let specNum = FMAPR.getParentSpecNum( $(this) );
    
            FMAPR.markAsDirty();

            if ( FMAPR.isLastRowItem( $(this) ) ){

                FMAPR.toggleExportSpecification( specNum, 1);

                FMAPR.appendBlankExportSpecification();

            }
            else if ( $(this).data("setting")==="export_name" ) {
                FMAPR.giveThemNames();
            }
        })
    ;
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

FMAPR.setTemplateEventHandlers = function()
{
    let tTable = $('table#yes3-fmapr-settings-template');

    tTable.find("i.yes3-fmapr-gutter-icon").on("click", function(){

        let specNum = FMAPR.getParentSpecNum( $(this) );

        let action = $(this).attr("action");

        console.log('templateEventHandler', action, specNum);

        if ( action==="remove" ){

            FMAPR.removeExportSpecification(specNum);

        }
        else if ( action==="expand" ){

            FMAPR.toggleExportSpecification(specNum, 1);
        }
        else if ( action==="collapse" ) {

            FMAPR.toggleExportSpecification(specNum, 0);
        }
    })
}

$(window).resize( function() {
    
    //FMAPR.resizeSetupEventTable();

})

/**
 * things to do when the settings are loaded
 */
$(document).on('yes3-fmapr.settings', function(){

    FMAPR.populateExportSpecificationsTable();

    if ( yes3ModuleProperties.isLongitudinal ){

        $('.yes3-fmapr-longitudinal-only').show();

        FMAPR.populateSetupEventTable();

    }
})
 
$( function () {

    FMAPR.mapperLoaded = true; // not relevant for this page and required by displayActionIcons

    //FMAPR.setTemplateEventSelectOptions();

    FMAPR.setTemplateEventHandlers();

    FMAPR.getProjectSettings(); // will trigger yes3-fmapr.settings event

})