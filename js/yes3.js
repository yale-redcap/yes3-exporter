let YES3 = {
    maxZ: 1000,
    Functions: {},
    contentLoaded: true,
    contentExpanded: true,
    dirty: false,
    initial_help_offered: false,
    busy: false,
    projectUrl: "https://portal.redcap.yale.edu/news/redcapyale-team-secures-nih-funding-support-redcap-external-modules",

    captions: {
        "yes" : "yes",
        "okay" : "make it so",
        "done" : "done",
        "no": "no",
        "cancel": "cancel",
        "close": "close",
        "proceed": "proceed",
        "wait": "PLEASE WAIT",
        "wait_loading_specifications": "PLEASE WAIT: loading all export specifications",
        "wait_loading_specification": "PLEASE WAIT: loading the export specification",
        "wait_reloading_specification": "PLEASE WAIT: reloading the export specification",
        "wait_saving_specification": "PLEASE WAIT: saving the export specification",
        "wait_exporting_data": "PLEASE WAIT: exporting data",
    }
};

String.prototype.truncateAt = function( n ){
    if ( this.length > n-3 ) return this.substring(0, n-3) + "...";
    else if ( this.length > n ) return this.substring(0, n);
    else return this;
}
 
 /**
  * Escapes html elements within string, including quotation marks. 
  * Can be used to condition user input or data prior to display.
  * 
  * @returns string
  */
 String.prototype.escapeHTML = function() {
     return this.replace(
         /[&<>'"]/g,
         function(chr){
             return {'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[chr] || chr;
        }
     )
}
  
 // formats date as mm-dd-yyyy
 Date.prototype.mdy = function() {
    var mm = this.getMonth() + 1; // getMonth() is zero-based(!)
    var dd = this.getDate();
    return [
       (mm>9 ? '' : '0') + mm,
       (dd>9 ? '' : '0') + dd,
       this.getFullYear()
    ].join('-');
};
 
 // formats date as ISO (yyyy-mm-dd)
 Date.prototype.ymd = function() {
    var mm = this.getMonth() + 1; // getMonth() is zero-based(!)
    var dd = this.getDate();
    return [
       this.getFullYear(),
       (mm>9 ? '' : '0') + mm,
       (dd>9 ? '' : '0') + dd
    ].join('-');
};
 
 // formats date as ISO (yyyy-mm-dd hh:mm:ss)
 Date.prototype.ymdhms = function() {
    var mm = this.getMonth() + 1; // getMonth() is zero-based(!)
    var dd = this.getDate();
    var hh = this.getHours();
    var nn = this.getMinutes();
    var ss = this.getSeconds();
    var ymd = [
       this.getFullYear(),
       (mm>9 ? '' : '0') + mm,
       (dd>9 ? '' : '0') + dd
    ].join('-');
    var hms = [
       (hh>9 ? '' : '0') + hh,
       (nn>9 ? '' : '0') + nn,
       (ss>9 ? '' : '0') + ss
    ].join(':');
 
    return [ymd, hms].join(' ');
};
 
 // formats date as m/d/y hh:mm:ss
 Date.prototype.mdyhms = function() {
 
    return [this.getMonth()+1,
            this.getDate(),
            this.getFullYear()].join('/')+' '+
            [this.getHours(),
                this.getMinutes(),
                this.getSeconds()
            ].join(':')
    ;
};
 
 /*
  * replaces REDCap's escapeHtml which crashes (in this context anyway)
  * probably deprecated, since we added this function to the string prototype
  */
 const escapeHTML = str =>
    str.replace(
       /[&<>'"]/g,
       tag =>
          ({
             '&': '&amp;',
             '<': '&lt;',
             '>': '&gt;',
             "'": '&#39;',
             '"': '&quot;'
         }[tag] || tag)
    );
 
 // centers an element on screen
 jQuery.fn.center = function (dx, dy, atTheTop, toTheLeft) 
 {
    atTheTop = atTheTop || false;
    toTheLeft = toTheLeft || false;

    let x = ( toTheLeft ) ? 10 : Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + $(window).scrollLeft() - dx/2);

    // we can't let the panel be located too high up, lest the close button be obscured by the bootstrap menu overlay
    let y = ( atTheTop ) ? 30 : Math.max(30, (($(window).height() - $(this).outerHeight()) / 2) + $(window).scrollTop() - dy/2);

    this.css("position","absolute");

    this.css("top", y + "px");

    this.css("left", x + "px");

    return this;
};
 
 // positions an element on screen
 jQuery.fn.situate = function (x, y) {
    this.css("position","absolute");
    this.css("top", Math.max(0, y +
       $(window).scrollTop()) + "px");
    this.css("left", Math.max(0, x +
       $(window).scrollLeft()) + "px");
    return this;
};
// === POPUP ============================================================================================

// based on stackOverflow window centering threads
YES3.openPopupWindow = function(url, w, h, windowNamePrefix) {

    w = w || 1160;
    h = h || 700;
    windowNamePrefix = windowNamePrefix || "YES3Window";

    YES3.windowNumber++;

    let windowName = windowNamePrefix+YES3.windowNumber;

    //console.log(url,windowName);

    // Fixes dual-screen position                         Most browsers      Firefox
    let dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
    let dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

    let width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    let height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    let left = ((width / 2) - (w / 2)) + dualScreenLeft;
    let top = ((height / 2) - (h / 2)) + dualScreenTop;
    let newWindow = window.open(url, windowName, 'width=' + w + ',height=' + h + ',top=' + top + ',left=' + left);

    if(!newWindow || newWindow.closed || typeof newWindow.closed=='undefined')   {
        YES3.hello("It looks like popups from REDCap are blocked on your computer.<br />Please call the data management team to enable REDCap popups.")
    }

    // Puts focus on the newWindow
    if (window.focus) {
        //newWindow.focus();
    }

    //return false;
};

// === PANEL FUNCTIONS ==================================================================================

YES3.hello = function(msg, fn, nonmodal) {
    if ( fn ) {
    YES3.helloFunction = fn;
} else {
    YES3.helloFunction = null;
}
    $('#yes3-hello-message').html(msg);
    YES3.openPanel('yes3-hello-panel', nonmodal);
};
 
YES3.helloClose = function() {
    YES3.closePanel('yes3-hello-panel');
    if ( typeof YES3.helloFunction == "function" ) {
        YES3.helloFunction();
    }
};

YES3.isBusy = function(message) 
{
    YES3.busy = true; 
    
    YES3.getYes3ParentElement().css({'cursor': 'wait'});
    
    YES3.openPanel("yes3-busy").html(message).css("z-index", "2000");
}

YES3.notBusy = function() 
{
    YES3.busy = false;  

    YES3.closePanel("yes3-busy");

    YES3.getYes3ParentElement().css({'cursor': 'default'});
}

YES3.getContextMenuElement = function()
{
    return $("div#yes3-contextmenu-panel");
}

YES3.getContextMenuContentElement = function()
{
    return $("div#yes3-contextmenu-content");
}
 
YES3.contextMenuOpen = function(x, y) 
{
    x = x || 0;
    y = y || 0;

    YES3.openPanel('yes3-contextmenu-panel', true, x, y);
};
 
YES3.contextMenuClose = function( Fn, keepPointer ) 
{
    keepPointer = keepPointer || false;

    YES3.closePanel('yes3-contextmenu-panel');

    //console.log("contextMenuClose", Fn, typeof Fn);

    if ( typeof Fn === "function" ) {

       Fn();
    }

    if ( !keepPointer ) {

        YES3.hideRedPointer();
    }
};

YES3.showRedPointer_alt = function( theRow )
{
 
     let thePointer = $('div#yes3-red-pointer');
 
     let theContainer = thePointer.parent();
 
     let x = theRow.offset().left - theContainer.offset().left - thePointer.outerWidth() - 2;
     let y = theRow.offset().top - theContainer.offset().top + theRow.outerHeight() - thePointer.outerHeight()/2;
 
     thePointer.css({top: y, left: x}).show();
}
 
YES3.hideRedPointer_alt = function()
{
    $('div#yes3-red-pointer').hide();
}

/**
 * replaced the actual red pointer with a red bottom border 3/2/2022
 * 
 * @param {*} theRow 
 */
YES3.showRedPointer = function( theRow )
{
    theRow.addClass('yes3-rowPointer');
}
 
YES3.hideRedPointer = function()
{
    $('.yes3-rowPointer').removeClass('yes3-rowPointer');
}

YES3.YesNo = function(question, fnYes, fnNo) {
    YES3.yesFunction = fnYes;
    YES3.noFunction = fnNo;
    $('#yes3-yesno-message').html(question);
    YES3.openPanel('yes3-yesno-panel');
};
 
 YES3.Yes = function() {
    YES3.closePanel('yes3-yesno-panel');
    if ( typeof YES3.yesFunction == "function" ) {
       YES3.yesFunction();
   } else {
       window[YES3.yesFunction]();
   }
};
 
 YES3.No = function() {
    YES3.closePanel('yes3-yesno-panel');
    if ( typeof YES3.noFunction == "function" ) {
       YES3.noFunction();
   }
};
 
 /**
  * 
  * @param {*} panelName 
  * @param {*} nonmodal 
  * @param {*} x 
  * @param {*} y 
  */
YES3.openPanel = function(panelName, nonmodal, x, y, atTheTop, toTheLeft) 
{
    atTheTop = atTheTop || false;
    toTheLeft = toTheLeft || false;
    
    nonmodal = nonmodal || false;
    x = x || 0;
    y = y || 0;
    
    let panel = $('#'+panelName);

    let theParent = $('#'+panelName).parent();

    YES3.maxZ += 1;

    if ( !nonmodal ) {
        
        YES3.startModalState(); // places the full-screen overlay just below the panel -->
    }

    if ( x || y ) {
        panel.situate( x, y );
    } else {
        panel.center(theParent.offset().left, theParent.offset().top, atTheTop, toTheLeft);
    }

    panel.css({'z-index': YES3.maxZ}).show();

    return panel;
};
 
YES3.closePanel = function(panelName) {
    let panel = $(`#${panelName}`);
    panel.hide();
    YES3.endModalState();
    return panel;
};

YES3.startModalState = function(){

    $('#yes3-screen-cover').css({'z-index':YES3.maxZ-1}).show();
}

YES3.endModalState = function(){

    $('#yes3-screen-cover').hide();
}

YES3.hideContextMenuOnClickOutside = function()
{

    $(document).on("click", function(e){

        if ( $('div#yes3-contextmenu-panel').is(":visible") ) {

            let p = $(e.target).closest("div#yes3-contextmenu-panel");

            if ( !p.length ){
                YES3.contextMenuClose();
            }

            //console.log('hideContextMenuOnClickOutside:', e, p);
        }
    })
}

/* === CONTAINER ELEMENT === */

YES3.container = function()
{
    return $('div#yes3-container');
}

/* === ACTION ICON SUPPORT === */

YES3.displayActionIcons = function( listenersLater )
{
    listenersLater = listenersLater || false;

    $('i.yes3-action-disabled').removeClass('yes3-action-disabled');

    if ( !YES3.contentLoaded ){

        $('i.yes3-loaded').addClass('yes3-action-disabled');
    }
    else {

        $('i.yes3-action-icon:not(.yes3-clean)').removeClass('yes3-action-disabled');

        if ( YES3.dirty ){

            $('i.yes3-display-when-clean').addClass('yes3-action-disabled');
            $('i.yes3-display-when-dirty').removeClass('yes3-action-disabled');
            $('i.yes3-save-control').addClass('yes3-dirty');
        }
        else {

            $('i.yes3-display-when-clean').removeClass('yes3-action-disabled');
            $('i.yes3-display-when-dirty').addClass('yes3-action-disabled');
            $('i.yes3-save-control').removeClass('yes3-dirty');
        }
    }

    if ( YES3.contentExpanded && YES3.contentLoaded ){

        //$('i.yes3-collapsed').addClass('yes3-action-disabled');
        //$('i.yes3-expanded').removeClass('yes3-action-disabled');
        $('i.yes3-expanded').show();
        $('i.yes3-collapsed').hide();
    }
    else if ( !YES3.contentExpanded && YES3.contentLoaded ){

        //$('i.yes3-collapsed').removeClass('yes3-action-disabled');
        //$('i.yes3-expanded').addClass('yes3-action-disabled');
        $('i.yes3-expanded').hide();
        $('i.yes3-collapsed').show();
    }
    else {

        //$('i.yes3-collapsed').removeClass('yes3-action-disabled');
        //$('i.yes3-expanded').addClass('yes3-action-disabled');
        $('i.yes3-collapsed').hide();
        $('i.yes3-expanded').show().addClass('yes3-action-disabled');
    }

    // disable based on permissions
    if ( !YES3.userRights.isDesigner ){

        $('i.yes3-designer-only:not(.yes3-action-disabled)').addClass('yes3-action-disabled');

        $('.yes3-designer-only:not(.yes3-action-disabled)').hide();
        $('.yes3-editor input, .yes3-editor select').off().prop('disabled', true);
        $('.yes3-editor i').addClass('yes3-action-disabled').off();
    }

    if ( !YES3.userRights.exporter ){

        $('i.yes3-exporter-only:not(.yes3-action-disabled)').addClass('yes3-action-disabled');
    }

    if ( localStorage.getItem('theme')==='dark'){

        $('.yes3-light-theme-only').hide();
        $('.yes3-dark-theme-only').show();
    }
    else {

        $('.yes3-light-theme-only').show();
        $('.yes3-dark-theme-only').hide();
    }

    if ( !listenersLater ) {

        YES3.setActionIconListeners( YES3.container() );
    }
}

YES3.setActionIconListeners = function(parentElement)
{
    actionIcons = parentElement.find("i.yes3-action-icon");

    $("i.yes3-action-icon").off();

    $("i.yes3-action-icon:not(.yes3-action-disabled):not(.yes3-nohandler)").on("click", function(){

        let action = $(this).attr("action");

        if ( typeof YES3.Functions[action] === "function" ) {
            YES3.Functions[action].call(this);
        }
        else {
            YES3.hello(`No can do: the feature 'YES3.Functions.${action}' has not been implemented yet.`);
        }    
    })
}

/*** REGISTER NAMESPACE */
YES3.RegisterApplicationNameSpace = function( nameSpace )
{
    YES3.applicationNameSpace = nameSpace;
}

/*** HELP ***/

/**
 * This function is called via the YES3 'action icon' mechanism,
 * so it is registered in the YES3 namespace
 */
YES3.Functions.Help_openPanel = function( onlyIfNotGotIt )
{
    onlyIfNotGotIt = onlyIfNotGotIt || false;

    if ( onlyIfNotGotIt && YES3.Help_hasGotIt() ){

        return true;
    }
    
    YES3.openPanel('yes3-help-panel', true);

    if ( onlyIfNotGotIt ){

        $('div#yes3-help-panel div.yes3-help-panel-got-it').show();
    }
    else {

        $('div#yes3-help-panel div.yes3-help-panel-got-it').hide();
    }

    if ( YES3.contentExpanded && YES3.contentLoaded ){

        $('div#yes3-help-panel .yes3-expanded').show();
        $('div#yes3-help-panel .yes3-collapsed').hide();
    }
    else if ( !YES3.contentExpanded && YES3.contentLoaded ){

        $('div#yes3-help-panel .yes3-expanded').hide();
        $('div#yes3-help-panel .yes3-collapsed').show();
    }
    else {

        $('div#yes3-help-panel .yes3-expanded').show();
        $('div#yes3-help-panel .yes3-collapsed').show();
    }
}
 
YES3.Help_closePanel = function()
{
    YES3.closePanel('yes3-help-panel');
}

YES3.Help_setGotIt = function()
{
    localStorage.setItem(`Yes3HelpGotIt_${YES3.applicationNameSpace}`, 'got-it');
}

YES3.Help_hasGotIt = function()
{
    let gotIt = localStorage.getItem(`Yes3HelpGotIt_${YES3.applicationNameSpace}`);

    return (typeof gotIt === "string" && gotIt === 'got-it') ? true : false;
}
 
YES3.Help_openReadMe = function()
{
    YES3.openPopupWindow( YES3.moduleProperties.documentationUrl ); 
} 
 
YES3.Help_openChangeLog = function()
{
    YES3.openPopupWindow( YES3.moduleProperties.changelogUrl ); 
} 

/*
    === THEME ===

    https://dev.to/ananyaneogi/create-a-dark-light-mode-switch-with-css-variables-34l8
*/

//determines if the user has a set theme
YES3.detectColorScheme = function(){
    var theme="light";    //default to light

    //local storage is used to override OS theme settingsvgb 
    if(localStorage.getItem("theme")){
        if(localStorage.getItem("theme") == "dark"){
            var theme = "dark";
        }
    } else if(window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
        //OS theme setting detected as dark
        var theme = "dark";
    }

    YES3.setTheme(theme);
}

YES3.Functions.Theme_light = function()
{
    YES3.setTheme('light');
    YES3.displayActionIcons();
}

YES3.Functions.Theme_dark = function()
{
    YES3.setTheme('dark');
    YES3.displayActionIcons();
}

YES3.setTheme = function(theme)
{
    YES3.getYes3ParentElement().attr('data-theme', theme);
    localStorage.setItem('theme', theme);
    YES3.setThemeObjects();
    YES3.applyThemeBackgroundToParent();
}

YES3.switchTheme = function(e) {

    if (e.target.checked) {

        //document.documentElement.setAttribute('data-theme', 'dark');
        YES3.getYes3ParentElement().attr('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
    else {

        //document.documentElement.setAttribute('data-theme', 'light');
        YES3.getYes3ParentElement().attr('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    }

    YES3.setThemeObjects();
    YES3.applyThemeBackgroundToParent();
}

YES3.setThemeObjects = function(theme)
{

    theme = localStorage.getItem('theme');

    $("img.yes3-square-logo").attr('src', YES3.moduleProperties.imageUrl[theme].logo_square);

    $("img.yes3-horizontal-logo").attr('src', YES3.moduleProperties.imageUrl[theme].logo_horizontal);

    $("img.yes3-logo").off().on("click", function(){

        window.open(YES3.projectUrl, "popup=yes");
    })
}

YES3.applyThemeBackgroundToParent = function()
{
    YES3.getYes3ParentElement().css("background-color", $("#yes3-container").css("background-color"));
}

/* === UUID GENERATOR === */

// https://stackoverflow.com/questions/105034/how-to-create-a-guid-uuid/2117523#2117523
YES3.uuidv4 = function() {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
      (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

function listAllEventListeners() {
    const allElements = Array.prototype.slice.call(document.querySelectorAll('*'));
    allElements.push(document);
    allElements.push(window);
  
    const types = [];
  
    for (let ev in window) {
      if (/^on/.test(ev)) types[types.length] = ev;
    }
  
    let elements = [];
    for (let i = 0; i < allElements.length; i++) {
      const currentElement = allElements[i];
      for (let j = 0; j < types.length; j++) {
        if (typeof currentElement[types[j]] === 'function') {
          elements.push({
            "node": currentElement,
            "type": types[j],
            "func": currentElement[types[j]].toString(),
          });
        }
      }
    }
  
    return elements.sort(function(a,b) {
      return a.type.localeCompare(b.type);
    });
  }

/* === AJAX === */

YES3.noop = function(){}
 
YES3.requestService = function( params, doneFn, json ) 
{
   json = json || false;
   doneFn = doneFn || YES3.noop;

   //console.log('requestService', params);

   $.ajax(
    {
      url: YES3.moduleProperties.serviceUrl,
      type: "POST",
      dataType: ( json ) ? "json":"html",
      data: params
   })
   .done(
      doneFn
   )
   .fail(function(jqXHR, textStatus, errorThrown) 
   {
      console.log(jqXHR);
      alert('AJAX error: ' + jqXHR.responseText);
   });
}

YES3.isEmpty = function( x )
{
    if ( x===null ) return true;
    if ( typeof x === "undefined" ) return true;
    if ( typeof x === "object" ) return YES3.isEmptyObject( x );
    if ( typeof x === "string" ) return ( x.length === 0 );
    return false;
}

YES3.isEmptyArray = function( x )
{
    return YES3.isEmptyObject( x );
}

YES3.isEmptyObject = function( x )
{
    if ( typeof x !== "object" ) return true;
    if ( typeof x.length !== "undefined" ) return ( x.length === 0 );
    if ( Array.isArray(x) ) return ( !x.length );
    return ( !Object.keys(x).length );
}

YES3.isNonEmptyObject = function( o )
{
    return ( typeof o === "object" && Object.keys(o).length > 0 );
}

YES3.isNonEmptyArray = function( o )
{
    return ( typeof o === "object" && Array.isArray(o) && o.length > 0 );
}

YES3.getYes3ParentElement = function()
{
    return $("div#yes3-container").parent();
}

YES3.setCaptions = function()
{
    $("input[type=button].yes3-button-caption-yes").val(YES3.captions.yes);
    $("input[type=button].yes3-button-caption-okay").val(YES3.captions.okay);
    $("input[type=button].yes3-button-caption-done").val(YES3.captions.done);
    $("input[type=button].yes3-button-caption-no").val(YES3.captions.no);
    $("input[type=button].yes3-button-caption-cancel").val(YES3.captions.cancel);
    $("input[type=button].yes3-button-caption-close").val(YES3.captions.close);
    $("input[type=button].yes3-button-caption-proceed").val(YES3.captions.proceed);
}


/*** DEBUGGING ***/

window.onerror = function(message, source, lineno, error)
{ 
    // make sure the damn cursor is not spinning
    YES3.notBusy();

    // de-modalize
    YES3.endModalState();

    let msg = "A Javascript error was encountered! Please take a screen shot and write down exactly what preceded this sorry state."
        + "<br>-------"
        + "<br><br>message: " + message
        + "<br><br>source: " + source
        + "<br><br>lineno: " + lineno
        + "<br><br>error: " + error
    ;

    YES3.hello( msg );
}


/*
* the approved alternative to $(document).ready()
*/
$( function () {

    YES3.setCaptions();

    YES3.detectColorScheme();

    /*
    attach the csrf token to every AJAX request
    https://stackoverflow.com/questions/22063612/adding-csrftoken-to-ajax-request
    */
    $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
        jqXHR.setRequestHeader('X-CSRF-Token', redcap_csrf_token);
    });

    $(".yes3-draggable").draggable({"handle": ".yes3-panel-header-row, .yes3-panel-handle, .yes3-drag-handle"});
})
