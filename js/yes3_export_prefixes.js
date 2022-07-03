
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

YES3.Functions.Exportspecifications_saveSettings = function()
{
    //console.log('MappingsEditor_undoSettings' this);

    let errors = FMAPR.inspectEventPrefixes();

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

/* === FMAPR FUNCTIONS === */

FMAPR.eventPrefixesTable = function()
{
    return $("table#yes3-fmapr-setup-events");
}

FMAPR.restoreToDefaultValues = function()
{
    YES3.requestService({"request": "getDefaultEventPrefixes"}, FMAPR.restoreToDefaultValuesCallback, true);
}

FMAPR.restoreToDefaultValuesCallback = function(response)
{
    console.log("restoreToDefaultValuesCallback",response);

    FMAPR.event_settings = response;

    FMAPR.populateAllSettings();
    
    FMAPR.markAsDirty();
}

FMAPR.saveExportSettings = function()
{
    let events = [];

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

    let requestParams = {
        "request": "saveEventSettings",
        "events": JSON.stringify(events)
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

    YES3.requestService({"request": "getEventSettings"}, FMAPR.getExportSettingsCallback, true);  
}

FMAPR.getExportSettingsCallback = function(response)
{
    console.log('getExportSettingsCallback:', response);

    FMAPR.event_settings = response;

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

    FMAPR.markAsClean(true);

    FMAPR.postMessage("All event prefix settings loaded.");
}

FMAPR.populateSetupEventTable = function()
{
    let html = "";

    let event_id = "";

    let event_name = "";

    let event_prefix = "";

    for ( let e=0; e<FMAPR.event_settings.length; e++){

        event_id = FMAPR.event_settings[e].event_id;
        event_name = FMAPR.event_settings[e].event_name;
        event_prefix = FMAPR.event_settings[e].event_prefix;

        html += `<tr id='event-${event_id}'><td data-setting='event_name'>${event_name}</td><td><input type='text' class='yes3-input-integer' data-setting='event_prefix' value='${event_prefix}' /></td></tr>`;
    }

    FMAPR.eventPrefixesTable().find('tbody').empty().append(html);
}

FMAPR.specToRemove = -1;

FMAPR.setExportEventPrefixListeners = function() {

    FMAPR.eventPrefixesTable().find('input')
        .off()
        .on('change', function(){

            let prefix = '' + $(this).val();

            if ( !prefix ){

                $(this.closest('tr')).find('td,input').addClass('yes3-error');
            }
            else {

                prefix = prefix.trim().toLowerCase();

                if ( !prefix.isValidFieldname() ){

                    $(this.closest('tr')).find('td,input').addClass('yes3-error');

                    //$(this).val("");

                    YES3.hello(`Hold on there: '${prefix}' is an invalid prefix. A prefix must start with an alphabetic character followed by zero or more alphanumeric characters.`);
                }
                else {

                    $(this).val(prefix);
                    $(this.closest('tr')).find('td,input').removeClass('yes3-error');
                }
            }

            FMAPR.markAsDirty();
        })
    ;
}

FMAPR.reportStatus = function() {
    // just a stub, required by FMAPR.markAsDirty()
}

/* === AUDITS === */

FMAPR.inspectEventPrefixes = function()
{
    FMAPR.eventPrefixesTable().find(".yes3-error").removeClass("yes3-error");

    let errors = 0;
    let prefixes = [];

    FMAPR.eventPrefixesTable().find("input").each(function(){

        let x = $(this).val();

        if ( !x ){
            FMAPR.markAsBad( $(this) );
            errors++;
        }
        else {

            if ( !x.isValidFieldname() ) {
                FMAPR.markAsBad( $(this) );
                errors++;
            }
            else {

                if ( prefixes.includes(x) ){

                    FMAPR.markAsBad( $(this) );
                    errors++;    
                }
                else {

                    prefixes.push(x);
                    $(this).val(x.toLowerCase());
                }
            }

        }
    })

    return errors;
}

FMAPR.markAsBad = function( element )
{
    element.closest('tr').find('td:not(.yes3-gutter-right-center), input, select, span, label, i').addClass('yes3-error');
}

FMAPR.markAsGood = function( element )
{
    element.closest('tr').find('td, input, select, span, label, i').removeClass('yes3-error');
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
     * but is required by FMAPR.displayActionIconsAndInputs (located in FMAPR.yes3_fieldmapper_common.js)
     */
    FMAPR.mapperLoaded = true;


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

    // get the list of service functions so we can build the registry and live taint-free
    YES3.listServiceFunctions();


})