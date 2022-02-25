let FMAPR = {
    specification_index: 0,
    maxRawREDCapDataElementNumber: 0,
    dirty: false,
    buildInProgress: false,
    mapperLoaded: false,
    specificationElements: [],
    insertionElements: [],
    insertionRowId: "",
    specifications: [],
    map_record: {},
    settings: {}
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
 
       url: YES3.moduleProperties.serviceUrl,
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

 FMAPR.displayHelpPanel = function()
 {
     YES3.hello("Sorry bud, you're on your own.");
 }
 
 
 FMAPR.postMessage = function( msg, urgent ){

    urgent = urgent || false;

    let msgDiv = $('div#yes3-message');
    
    if ( msgDiv ) {

        let msgClass = ["yes3-fmapr-msgclass-normal", "yes3-fmapr-msgclass-urgent"];

        let msgClassIndex = (urgent) ? 1 : 0;

        if ( !msgDiv.hasClass( msgClass[msgClassIndex]) ) {

            msgDiv.removeClass( msgClass[1-msgClassIndex] ).addClass( msgClass[msgClassIndex] );

        }

        msgDiv.html(msg).show();

    } else {
       alert(msg);
    }
 }

 FMAPR.clearMessage = function(){
    if ( $('div#yes3-message') ) {
       $('#yes3-message').html("").show();
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

    FMAPR.displayActionIcons();
 
    // handler must defined in plugin JS
    $(document).trigger('yes3-fmapr.settings');
 
 }

/*** HELP ***/

/**
 * This function is called via the YES3 'action icon' mechanism,
 * so it is registered in the YES3 namespace
 */
YES3.Functions.Help_openPanel = function()
{
    YES3.openPanel('yes3-fmapr-help-panel', true)
}

FMAPR.Help_closePanel = function()
{
    YES3.closePanel('yes3-fmapr-help-panel');
}

FMAPR.Help_openReadMe = function()
{
    YES3.openPopupWindow( YES3.moduleProperties.documentationUrl ); 
}

/*** ACTION ICONS ***/


FMAPR.displayActionIcons = function()
{
    if ( !FMAPR.mapperLoaded ){
        $('i.yes3-fmapr-loaded').addClass('yes3-action-disabled');
    }
    else {

        $('i.yes3-action-icon:not(.yes3-fmapr-clean)').removeClass('yes3-action-disabled');

        if ( FMAPR.dirty ){
            $('i.yes3-fmapr-display-when-clean').addClass('yes3-action-disabled');
            $('i.yes3-fmapr-display-when-dirty').removeClass('yes3-action-disabled');
            $('i#yes3-fmapr-save-control').addClass('yes3-fmapr-dirty');
        }
        else {
            $('i.yes3-fmapr-display-when-clean').removeClass('yes3-action-disabled');
            $('i.yes3-fmapr-display-when-dirty').addClass('yes3-action-disabled');
            $('i#yes3-fmapr-save-control').removeClass('yes3-fmapr-dirty');
        }

        if ( FMAPR.specification_settings && FMAPR.specification_settings.export_layout=== "r" ){

            $('i.yes3-fmapr-display-when-not-repeating').addClass('yes3-action-disabled');

            /**
             * only one repeating form allowed
             */
            if ( $('tr.yes3-fmapr-data-element').length > 1 ){

                $('i.yes3-fmapr-bulk-insert').addClass('yes3-action-disabled');
            }
            else {

                $('i.yes3-fmapr-bulk-insert').removeClass('yes3-action-disabled');
            }
        }
    }

    YES3.setActionIconListeners( YES3.container() );

    FMAPR.reportStatus();
}

FMAPR.markAsClean = function()
{
    if ( FMAPR.dirty ) {

        FMAPR.dirty = false;
        FMAPR.displayActionIcons();
    }
}

FMAPR.markAsDirty = function()
{
    if ( FMAPR.buildInProgress ){
        return true;
    }
    
    if ( !FMAPR.dirty ) {
        FMAPR.dirty = true;
        FMAPR.displayActionIcons();
        FMAPR.postMessage("Be sure to save your changes.", true);
    }

    FMAPR.reportStatus();
}

FMAPR.pointAt = function( theRow )
{
    let theContainer = $("div#yes3-container").parent();

    let x = theRow.offset().left - theParent.offset().left;
    let y = theRow.offset().top - theParent.offset().top;

    let py = theRow.outerHeight() + y - 2;

}

$( function () {
})


