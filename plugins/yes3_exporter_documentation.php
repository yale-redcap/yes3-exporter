<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Yale\Yes3\Yes3;

$module = new \Yale\Yes3FieldMapper\Yes3FieldMapper();

$cssUrl = $module->getUrl("css/github-markdown-v2.css");

$markdown = file_get_contents( $module->getModulePath() . 'README.md' );

$urlBase = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/images/";

//exit ( $urlBase );

$markdown = str_replace( "(images/", "(" . $urlBase, $markdown);

//$markdown = file_get_contents($fileName);

$md = new \Parsedown();

$html = $md->text( $markdown );

//exit( $html );

?>

<!DOCTYPE html>

<html>
    <head>

        <title>YES3 Exporter Readme</title>

        <meta name="color-scheme" content="light dark" />

        <link rel='stylesheet' type='text/css' href='<?= $cssUrl ?>' />

    </head>

    <body>

    <style>

        .markdown-body {
            box-sizing: border-box;
            min-width: 200px;
            max-width: 980px;
            margin: 0 auto;
            padding: 45px;
        }

        @media (max-width: 767px) {
            .markdown-body {
                padding: 15px;
            }
        }
    </style>

    <article class="markdown-body">
        
        <?= $html ?>

        <label id="theme-switch" class="theme-switch" for="checkbox_theme">
            <input type="checkbox" id="checkbox_theme">&nbsp;toggle theme
        </label>

    </article>

    <script>

        //determines if the user has a set theme
        function detectColorScheme(){
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
        }

        detectColorScheme();

        //identify the toggle switch HTML element
        const toggleSwitch = document.querySelector('#theme-switch input[type="checkbox"]');

        //function that changes the theme, and sets a localStorage variable to track the theme between page loads
        function switchTheme(e) {
            if (e.target.checked) {
                localStorage.setItem('theme', 'dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                toggleSwitch.checked = true;
            } else {
                localStorage.setItem('theme', 'light');
                document.documentElement.setAttribute('data-theme', 'light');
                toggleSwitch.checked = false;
            }    
        }

        //listener for changing themes
        toggleSwitch.addEventListener('change', switchTheme, false);

        //pre-check the dark-theme checkbox if dark-theme is set
        if (document.documentElement.getAttribute("data-theme") == "dark"){
            toggleSwitch.checked = true;
        }

    </script>

    </body>

</html>