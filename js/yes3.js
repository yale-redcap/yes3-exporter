let YES3 = {
    maxZ: 1000,
    Functions: {}
};
 
String.prototype.truncateAt = function( n ){
    if ( this.length > n-3 ) return this.substr(0, n-3) + "...";
    else if ( this.length > n ) return this.substr(0, n);
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
          this.getSeconds()].join(':');
};
 
 /*
  * replaces REDCap's escapeHtml which crashes (in this context anyway)
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

    let y = ( atTheTop ) ? 10 : Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + $(window).scrollTop() - dy/2);

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

    console.log(url,windowName);

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
       //console.log("hello: executing function");
       YES3.helloFunction();
   }
};

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

    if ( !nonmodal ) $('#yes3-screen-cover').css({'z-index':YES3.maxZ-1}).show(); // places the full-screen overlay just below the panel -->
    
    if ( x || y ) {
       panel.situate( x, y );
   } else {
       panel.center(theParent.offset().left, theParent.offset().top, atTheTop, toTheLeft);
   }

    panel.css({'z-index': YES3.maxZ}).show();
    //if ( nonmodal ) {
    //    hideOnClickOutside(panel);
    //}
};
 
YES3.closePanel = function(panelName) {
    $(`#${panelName}`).hide();
    $('#yes3-screen-cover').hide();
};

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

YES3.setActionIconListeners = function(parentElement)
{
    actionIcons = parentElement.find("i.yes3-action-icon");

    $("i.yes3-action-icon").off();

    $("i.yes3-action-icon:not(.yes3-action-disabled)").on("click", function(){

        let action = $(this).attr("action");

        if ( typeof YES3.Functions[action] === "function" ) {
            YES3.Functions[action].call(this);
        }
        else {
            YES3.hello(`No can do: the feature 'YES3.Functions.${action}' has not been implemented yet.`);
        }    
    })
}



/*
    === THEME ===

    https://dev.to/ananyaneogi/create-a-dark-light-mode-switch-with-css-variables-34l8
*/

//determines if the user has a set theme
YES3.detectColorScheme = function(){
    var theme="light";    //default to light

    //local storage is used to override OS theme settings
    if(localStorage.getItem("theme")){
        if(localStorage.getItem("theme") == "dark"){
            var theme = "dark";
        }
    } else if(!window.matchMedia) {
        //matchMedia method not supported
        return false;
    } else if(window.matchMedia("(prefers-color-scheme: dark)").matches) {
        //OS theme setting detected as dark
        var theme = "dark";
    }

    //dark theme preferred, set document with a `data-theme` attribute
    if (theme=="dark") {
        document.documentElement.setAttribute("data-theme", "dark");
    } else {
        document.documentElement.setAttribute("data-theme", "light");
    }

    YES3.applyThemeBackgroundToParent();
}

YES3.switchTheme = function(e) {
    if (e.target.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark'); //add this
    }
    else {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light'); //add this
    }

    YES3.applyThemeBackgroundToParent();
}

YES3.applyThemeBackgroundToParent = function(){
    $("#yes3-container").parent().css("background-color", $("#yes3-container").css("background-color"));
}

/* === UUID GENERATOR === */

// https://stackoverflow.com/questions/105034/how-to-create-a-guid-uuid/2117523#2117523
YES3.uuidv4 = function() {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
      (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

 /*
 * the approved alternative to $(document).ready()
 */
$( function () {

    YES3.detectColorScheme();

    const toggleSwitch = document.querySelector('.yes3-theme-switch input[type="checkbox"]');

    toggleSwitch.addEventListener('change', YES3.switchTheme, false);

    const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

    if (currentTheme) {
        document.documentElement.setAttribute('data-theme', currentTheme);

        if (currentTheme === 'dark') {
            toggleSwitch.checked = true;
        }
    }

    /*
    attach the csrf token to every AJAX request
    https://stackoverflow.com/questions/22063612/adding-csrftoken-to-ajax-request
    */
    $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
       jqXHR.setRequestHeader('X-CSRF-Token', redcap_csrf_token);
    });
 
    $(".yes3-draggable").draggable({"handle": ".yes3-panel-header-row, .yes3-panel-handle, .yes3-drag-handle"});

})

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

YES3.isEmptyArray = function( x )
{
   //console.log('isEmptyArray', typeof x, x);
   if ( typeof x === "undefined" ) return true;
   return !x.length;
}

YES3.isTruthy = function( x )
{
   //console.log('isEmptyArray', typeof x, x);
   if ( typeof x === "undefined" ) return false;
   return x;
}


