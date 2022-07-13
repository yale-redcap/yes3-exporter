function openChangelog()
{
    loadDocument( html_changelog, toc_changelog );
}

function openTechnical()
{
    loadDocument( html_technical, toc_technical );
}

function openReadme()
{
    loadDocument( html_readme, toc_readme );
    removeReadmeDocViewerAdvisory();
}

function openUserGuide()
{
    loadDocument( html_userguide, toc_userguide );
}

/*
    === THEME ===

    https://dev.to/ananyaneogi/create-a-dark-light-mode-switch-with-css-variables-34l8
*/

//determines if the user has a set theme
function detectColorScheme(){
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

    setTheme(theme);
}

function setTheme(theme)
{
    $('html').attr('data-theme', theme);
    localStorage.setItem('theme', theme);
    setThemeObjects();
}

function setThemeObjects()
{
    theme = localStorage.getItem('theme');

    $("img.yes3-square-logo").attr('src', Yes3LogoUrl[theme]);

    $("img.yes3-horizontal-logo").attr('src', Yes3BannerUrl[theme]);
}

function resizeTheViewer()
{
    let theDocWrapper = $("#yes3-document");
    let theAuthors = $("#authors");
    let stubFooter = $('#stub-footer');
    let tocEntries = $('#toc-entries');
    let toc = $("#toc");

    let windowHeight = $(window).innerHeight();
    let stubFooterHeight = stubFooter.outerHeight();
    let tocHeight = toc.outerHeight();

    let docHeight = windowHeight 
        - theDocWrapper.offset().top
        - 15
    ;

    let stubY = docHeight
        - stubFooter.outerHeight()
        - 15
    ;

    let tocEntriesHeight = docHeight 
        - stubFooter.outerHeight()
        - tocEntries.offset().top
    ;

    //if ( stubY < tocHeight ) stubY = tocHeight;

    theDocWrapper.css({'height': docHeight+'px'});

    //if ( $("#stub").is(":visible") ){
        
        stubFooter.css({'top': stubY + 'px'});
        $("#stub").height(theDocWrapper.height());
    //}

    tocEntries.css({'height': tocEntriesHeight + 'px'})
}

function scrollDocTo( anchor_name ){

    if ( anchor_name==="top" ){
        let element = document.getElementById("yes3-document");
        element.scrollTop = 0;
        return true;
    }

    let element = document.querySelector(`a[name="${anchor_name}"]`);
    //element.scrollIntoView({ behavior: 'smooth', block: 'start'});      
    element.scrollIntoView();      
}

function buildTOC()
{
    $("div.toc-entry").off("click").on("click", function(){
        $("div.toc-entry.selected").removeClass("selected");
        $(this).addClass("selected");
        scrollDocTo($(this).attr("data-anchor_name"));
    })
}

function loadDocument(docHtml, tocHtml)
{
    $('article#yes3-document').html(docHtml);
    $('div#toc-entries').html(tocHtml);

    buildTOC();

    resizeTheViewer();

    scrollDocTo("top");
}

function removeReadmeDocViewerAdvisory()
{
    // remove any blockquote about viewing in doc plugin
    // (which is not relevant since this is the doc viewer)
    $("blockquote:contains('For better formatting')").first().remove();
}

$(window).resize( function() {

    resizeTheViewer();
})

$( function() {

    $("input[type=radio]#doc-userguide").trigger("click");

    // disable the changelog link if the loaded doc is the changelog
    /*
    if ( doc.includes("changelog") ){

        $("p#changelogLink").hide();
    }
    */

    detectColorScheme();
    resizeTheViewer();
})
