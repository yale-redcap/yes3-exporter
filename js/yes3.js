let YES3 = {
    maxZ: 1000
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
 
YES3.contextMenuClose = function( Fn ) 
{
    YES3.closePanel('yes3-contextmenu-panel');
    YES3.hideRedPointer();

    if ( typeof Fn === "function" ) {
       //console.log("contextmenu: executing function");
       Fn();
    }
};

YES3.showRedPointer = function( theRow )
{
     //let theContainer = $("div#yes3-fmapr-container").parent();
 
     let thePointer = $('div#yes3-red-pointer');
 
     let theContainer = thePointer.parent();
 
     let x = theRow.offset().left - theContainer.offset().left - thePointer.outerWidth() - 2;
     let y = theRow.offset().top - theContainer.offset().top + theRow.outerHeight() - thePointer.outerHeight()/2;
 
     thePointer.css({top: y, left: x}).show();
}
 
YES3.hideRedPointer = function(x, y)
{
    $('div#yes3-red-pointer').hide();
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

 /*
 * the approved alternative to $(document).ready()
 */
$( function () {
 
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

