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

use Yale\Yes3\Yes3;

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();

$cssUrl = $module->getUrl("css/github-markdown-v2.css");

$markdown = file_get_contents( $module->getModulePath() . 'README.md' );

$urlBase = APP_PATH_WEBROOT_FULL . "modules/" . $module->getModuleDirectoryName() . "/media/";

//exit ( $urlBase );

$markdown = str_replace( "(media/", "(" . $urlBase, $markdown);

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

        /*Simple css to style toggle switch*/
        .theme-switch-wrapper {

            display: flex;
            align-items: center;

        }

        .theme-switch-wrapper em {
            font-size: 1rem;
            margin-left: 15px
        }

        .theme-switch {
            display: inline-block;
            height: 34px;
            position: relative;
            width: 60px;
        }

        .theme-switch input {
            display:none;
        }

        .slider {
            background-color: #ccc;
            bottom: 0;
            cursor: pointer;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            transition: .4s;
        }

        .slider:before {
            background-color: #fff;
            bottom: 4px;
            content: "";
            height: 26px;
            left: 4px;
            position: absolute;
            transition: .4s;
            width: 26px;
        }

        input:checked + .slider {
            background-color: #66bb6a;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

    </style>

    <article class="markdown-body">

        <!--div class="theme-switch-wrapper">
            <label class="theme-switch" for="checkbox">
                <input type="checkbox" id="checkbox" />
                <div class="slider round"></div>
            </label>
            <em></em>
        </div-->
        
        <?= $html ?>

    </article>

    <script>

        /*
         * https://dev.to/ananyaneogi/create-a-dark-light-mode-switch-with-css-variables-34l8
         */

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

        /*

        const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');

        function switchTheme(e) {
            if (e.target.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
            else {
                document.documentElement.setAttribute('data-theme', 'light');
            }    
        }

        toggleSwitch.addEventListener('change', switchTheme, false);

        function switchTheme(e) {
            if (e.target.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark'); //add this
            }
            else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light'); //add this
            }    
        }

        */

        const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

        if (currentTheme) {
            document.documentElement.setAttribute('data-theme', currentTheme);
            /*
            if (currentTheme === 'dark') {
                toggleSwitch.checked = true;
            }
            */
        }

    </script>

    </body>

</html>