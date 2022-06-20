<?php
/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/
/**
 * REDCap constants referenced in this file.
 * We conditionally define them here to keep code checkers happy.
 */
defined('APP_PATH_WEBROOT_FULL') or define('APP_PATH_WEBROOT_FULL', '');

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();

use \Parsedown;

$githubCssUrl = $module->getUrl("css/github-markdown-v2.css");
$yes3CssUrl = $module->getUrl("css/yes3_documentation.css");
//$yes3JsUrl = $module->getUrl("js/yes3_documentation.js");

$yes3SquareLogoUrl_light = $module->getUrl("images/YES3_Logo_Square_White.png");
$yes3SquareLogoUrl_dark  = $module->getUrl("images/YES3_Logo_Square_Black.png");

$yes3HorizLogoUrl_light = $module->getUrl("images/YES3_Logo_Horizontal_White_v3_500.png");
$yes3HorizLogoUrl_dark  = $module->getUrl("images/YES3_Logo_Horizontal_Black_v3_500.png");

$filename = $_GET["doc"] . ".md";

$urlDoc = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/" . $filename;

//exit( $urlDoc );

$markdown = file_get_contents( $urlDoc );

//exit( $markdown );

// the media folder holds any embedded images.
$urlMediaFolder = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/media/";

//exit ( $urlBase );

//$markdown = str_replace( ["(media/", "\r"], ["(" . $urlMediaFolder, ""], $markdown);
$markdown = str_replace( ["media/", "\r"], [$urlMediaFolder, ""], $markdown);

//exit($markdown);

// table of contents HTML
$toc = "";

buildTOC($markdown, $toc);

//$markdown = file_get_contents($fileName);

$md = new \Parsedown();

$html = $md->text( $markdown );

//exit( $html );

/**
 * 
 * Sweeps through the document looking for header markdown. Then:
 * 
 * (1) inserts an html anchor into the markdown to tag the location
 * (2) builds the TOC html
 * 
 * function: buildTOC
 * 
 * @param mixed $markdown - the md README
 * @param mixed $toc - 
 * 
 * @return mixed
 */
function buildTOC( &$markdown, &$toc )
{
    if ( $markdown[0]==="#" ) {

        $i = 0;
    }
    else {
        $i = strpos( $markdown, "\n#");
        if ( $i===false ) return $markdown;
    }

    $toc = "";

    while ( $i !== false ){
    
        $j = strpos( $markdown, " ", $i);
        if ( $j===false ) return;

        $k = strpos( $markdown, "\n", $j);
        if ( $k===false ) return;

        $level = $j-$i-1;

        $toc_title = htmlentities(trim(substr($markdown, $j, $k-$j)), ENT_QUOTES);

        $anchor_name = strtolower(str_replace(" ", "-", $toc_title));

        $anchor = "<a name='{$anchor_name}' toc-level='{$level}'></a>";

        $toc .= "\n<div class='toc-entry level-{$level} yes3-ellipsis' data-anchor_name='{$anchor_name}'>{$toc_title}</div>";

        $markdown = substr($markdown, 0, $k) . $anchor . substr($markdown, $k);

        $k += strlen( $anchor );
        
        $i = strpos( $markdown, "\n#", $k);
    }
}

?>

<!DOCTYPE html>

<html data-theme="light">

    <head>

        <title>YES3 Exporter Readme</title>

        <meta name="color-scheme" content="light dark" />

        <script
            src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
            crossorigin="anonymous">
        </script>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>

        <link rel='stylesheet' type='text/css' href='<?= $githubCssUrl ?>' />

        <link rel='stylesheet' type='text/css' href='<?= $yes3CssUrl ?>' />

    </head>

    <body>

    <div class="container-fluid">

        <div class="row" id="docViewer">

            <div class="col-sm-3 hidden-xs" id="stub">

                <div id="stub-wrapper hidden-xs">

                    <div id="toc">
                        <div class="toc-header">
                            <div class='yes3-flex-container'> 
                                <img src='<?= $yes3SquareLogoUrl_light ?>' class='yes3-flex-vcenter-hleft yes3-square-logo' title='click to return to the top of the document' height=50px onclick='scrollDocTo("top");' />
                                <div class='yes3-flex-vcenter-hleft logo-title'>Exporter</div>
                            </div>
                            <div class='toc-title'>Table of Contents</div>
                        </div>

                        <div class="toc-entries">
                            <?= $toc ?>
                        </div>
                    </div>

                    <div class="authors hidden-xs" id="stub-footer">

                        <p>Wrought by the REDCap@Yale team</p>
                        <p>REDCap@yale.edu</p>
                        <p id="changelogLink"><a href="javascript:openChangeLog();">change log</a></p>

                    </div>
                </div>
            </div>

            <div class="col-sm-9" id="content">
            <article class="markdown-body" id="yes3-document">
                    <?= $html ?>
                </article>
            </div>
        </div>
    </div>

        <script>

            let windowNumber = 0;

            let doc = "<?= $_GET['doc'] ?>";

            let Yes3LogoUrl = {
                "light": "<?= $yes3SquareLogoUrl_light ?>",
                "dark": "<?= $yes3SquareLogoUrl_dark ?>"
            }

            let Yes3BannerUrl = {
                "light": "<?= $yes3HorizLogoUrl_light ?>",
                "dark": "<?= $yes3HorizLogoUrl_dark ?>"
            }

            function openPopupWindow(url) 
            {
                let w = 1160;
                let h = 700;
                const windowNamePrefix = "YES3Window";

                windowNumber++;

                let windowName = windowNamePrefix+windowNumber;

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
                    alert("It looks like popups from REDCap are blocked on your computer.<br />Please call the data management team to enable REDCap popups.")
                }

                // Puts focus on the newWindow
                if (window.focus) {
                    //newWindow.focus();
                }

                //return false;
            };

            function openChangeLog()
            {
                let url="<?= $module->changelogUrl ?>";

                openPopupWindow( url );
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

                if ( stubY < tocHeight ) stubY = tocHeight;

                theDocWrapper.css({'height': docHeight+'px'});

                if ( $("#stub").is(":visible") ){
                    
                    stubFooter.css({'top': stubY + 'px'});
                    $("#stub").height(theDocWrapper.height());
                }
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

            $("div.toc-entry").on("click", function(){
                $("div.toc-entry.selected").removeClass("selected");
                $(this).addClass("selected");
                //console.log("onClick",$(this).attr("data-anchor_name"))
                scrollDocTo($(this).attr("data-anchor_name"));
            })

            $(window).resize( function() {

                resizeTheViewer();
            })

            $( function() {

                // disable the changelog link if the loaded doc is the changelog
                if ( doc.includes("changelog") ){

                    $("p#changelogLink").hide();
                }

                detectColorScheme();
                resizeTheViewer();
            })

        </script>

    </body>

</html>