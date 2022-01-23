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
 jQuery.fn.center = function (dx, dy) {
    this.css("position","absolute");
    this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
       $(window).scrollTop() - dy/2) + "px");
    this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
       $(window).scrollLeft() - dx/2) + "px");
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

YES3.contextmenu = function(content, x, y, fn) {
    if ( fn ) {
       YES3.contextmenuFunction = fn;
   } else {
       YES3.contextmenuFunction = null;
   }
    $('#yes3-contextmenu-content').html(content);
    YES3.openPanel('yes3-contextmenu-panel', true, x, y);
};
 
 YES3.contextmenuClose = function() {
    YES3.closePanel('yes3-contextmenu-panel');
    if ( typeof YES3.contextmenuFunction == "function" ) {
       //console.log("contextmenu: executing function");
       YES3.contextmenuFunction();
   }
};
 
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
 
 // opens panel in screen center, with a random diddle
 YES3.openPanel = function(panelName, nonmodal, x, y) 
 {
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
       panel.center(theParent.offset().left, theParent.offset().top);
   }

    panel.css({'z-index': YES3.maxZ}).show();
    if ( nonmodal ) {
        hideOnClickOutside(panel);
    }
};
 
 YES3.closePanel = function(panelName) {
    $(`#${panelName}`).hide();
    $('#yes3-screen-cover').hide();
};

// https://stackoverflow.com/questions/152975/how-do-i-detect-a-click-outside-an-element/3028037#3028037
function hideOnClickOutside(selector) {
    const outsideClickListener = (event) => {
      const $target = $(event.target);
      if (!$target.closest(selector).length && $(selector).is(':visible')) {
          $(selector).hide();
          removeClickListener();
          $('.yes3-row-focused').removeClass('yes3-row-focused');
        }
    }
  
    const removeClickListener = () => {
      document.removeEventListener('click', outsideClickListener)
    }
  
    document.addEventListener('click', outsideClickListener)
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

