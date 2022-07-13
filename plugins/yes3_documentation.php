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

$githubCssUrl = $module->getUrl("css/github-markdown-v2.css");
$yes3CssUrl = $module->getUrl("css/yes3_documentation.css");
$yes3JsUrl = $module->getUrl("js/yes3_documentation.js");

$yes3SquareLogoUrl_light = $module->getUrl("images/YES3_Logo_Square_White.png");
$yes3SquareLogoUrl_dark  = $module->getUrl("images/YES3_Logo_Square_Black.png");

$yes3HorizLogoUrl_light = $module->getUrl("images/YES3_Logo_Horizontal_White_v3_500.png");
$yes3HorizLogoUrl_dark  = $module->getUrl("images/YES3_Logo_Horizontal_Black_v3_500.png");

$html_readme = "";
$html_changelog = "";
$html_technical = "";
$html_userguide = "";

$toc_readme = "";
$toc_changelog = "";
$toc_technical = "";
$toc_userguide = "";

buildHtmlForDoc("README"   , $html_readme   , $toc_readme   );
buildHtmlForDoc("changelog", $html_changelog, $toc_changelog);
buildHtmlForDoc("userguide", $html_userguide, $toc_userguide);
buildHtmlForDoc("technical", $html_technical, $toc_technical);

function buildHtmlForDoc(  $docName, &$html, &$toc){
    global $module;

    if ( $docName === "README" ){

        $urlDoc = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/README.md";
    }
    else {

        $filename = $docName . ".md";
        $urlDoc = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/documents/" . $filename;
    }

    $markdown = file_get_contents( $urlDoc );

    // the media folder holds any embedded images.
    $urlMediaFolder = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/documents/media/";

    // we only allow image tags
    $markdown = strip_tags( str_replace( ["media/", "\r"], [$urlMediaFolder, ""], $markdown), "<img><br><b><a>");

    //exit($markdown);

    // table of contents HTML
    $toc = "";

    buildTOC($markdown, $toc);

    $Parsedown = new \Parsedown();

    // only image tags are allowed for YES3 documents
    $html = $Parsedown->text( $markdown );
}

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

        <title>YES3 Documentation</title>

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

        <script>

            let Yes3LogoUrl = {
                "light": "<?= $yes3SquareLogoUrl_light ?>",
                "dark": "<?= $yes3SquareLogoUrl_dark ?>"
            }

            let Yes3BannerUrl = {
                "light": "<?= $yes3HorizLogoUrl_light ?>",
                "dark": "<?= $yes3HorizLogoUrl_dark ?>"
            }

            let html_readme = `<?= $html_readme ?>`;
            let toc_readme  = `<?= $toc_readme ?>`;

            let html_changelog = `<?= $html_changelog ?>`;
            let toc_changelog  = `<?= $toc_changelog ?>`;

            let html_technical = `<?= $html_technical ?>`;
            let toc_technical  = `<?= $toc_technical ?>`;

            let html_userguide = `<?= $html_userguide ?>`;
            let toc_userguide  = `<?= $toc_userguide ?>`;

        </script>

        <script src="<?= $yes3JsUrl ?>" ></script>

    </head>

    <body>

    <div class="container-fluid">

        <div class="row" id="docViewer">

            <div class="col-sm-3 hidden-xs" id="stub">

                <div id="stub-wrapper hidden-xs">

                    <div class='yes3-flex-container'> 
                        <img src='<?= $yes3SquareLogoUrl_light ?>' class='yes3-flex-vcenter-hleft yes3-square-logo' title='click to return to the top of the document' height=50px onclick='scrollDocTo("top");' />
                        <div class='yes3-flex-vcenter-hleft logo-title'>Exporter</div>
                    </div>

                    <div id="doc-options">

                        <input type="radio" class="balloon" value="userguide" name="doc" id="doc-userguide" onclick="openUserGuide()" />
                        <label for="doc-userguide">User Guide</label>
                        <br/>

                        <input type="radio" class="balloon" value="technical" name="doc" id="doc-technical" onclick="openTechnical()" />
                        <label for="doc-technical">Technical Guide</label>
                        <br />

                        <input type="radio" class="balloon" value="changelog" name="doc" id="doc-changelog" onclick="openChangelog()" />
                        <label for="doc-changelog">Change Log</label>
                        <br/>

                        <input type="radio" class="balloon" value="readme" name="doc" id="doc-readme" onclick="openReadme()" />
                        <label for="doc-readme">readme</label>

                    </div>

                    <div id="toc">

                        <div class="toc-header">
                            <div class='toc-title'>Table of Contents</div>
                        </div>

                        <div class="toc-entries" id="toc-entries"></div>
                    </div>

                    <div class="authors hidden-xs" id="stub-footer">

                        <p>Wrought by the REDCap@Yale team</p>
                        <p>REDCap@yale.edu</p>
                        <!--p id="changelogLink"><a href="javascript:openChangeLog();">Change Log</a></p>
                        <p id="technicalDocumentationLink"><a href="javascript:openTechDoc();">Technical Documentation</a></p-->

                    </div>
                </div>
            </div>

            <div class="col-sm-9" id="content">
                <article class="markdown-body" id="yes3-document"></article>
            </div>
        </div>
    </div>

    </body>

</html>