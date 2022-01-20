<?php


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$module = new Yale\Yes3FieldMapper\Yes3FieldMapper();
use Yale\Yes3\Yes3;

$HtmlPage = new HtmlPage();
$HtmlPage->ProjectHeader();

/**
 * getCodeFor will: 
 *   (1) output html tags and code for js and css libraries named [param1]
 *   (2) if [param2] is true, output /html/yes3.html (yes3 dialog panels)
 *   (3) output js code to build the global yes3ModuleProperties object
 *       (all non-private properties)
 */
$module->getCodeFor("yes3_fieldmapper", true);

/*
 * bail if EM not set up
 */
if ( !$module->specifications ){
   exit("ERROR: The specifications are missing or fargled.");
}

/**
 * select options for specification
 */
$specOptionHtml = "";
$specNum = 0;
foreach( $module->specifications as $specification ){
    $specName = $specification['name'];
    $specOptionHtml .= "<option value='{$specNum}'>{$specName}</option>";
    $specNum++;
}

?>

<div class="container" id="yes3-fmapr-container">

    <div class="row">

        <div class="col-md-4 yes3-block">

            <div>
                <span class="yes3-fmapr-title">YES3</span>&nbsp;<span class="yes3-fmapr-subtitle">Field&nbsp;Mapper</span>
            </div>

        </div>

        <div class="col-md-4 yes3-block">

            <select id="yes3-fmapr-specification" onchange="FMAPR.specificationSelect()">
                
                <option value=''>select a specification</option>
                <?= $specOptionHtml ?>

            </select>

        </div>

        <div class="col-md-4 yes3-block">

            <input type="button" class="yes3-when-loaded" value="SAVE MAPPINGS" id="yes3-fmapr-save-specification" onclick="FMAPR.saveFieldMappings()">

        </div>

    </div>

    <!-- **** FIELD MAPPER **** -->

    <div class="row yes3-fmapr">

        <div class="col-md-12 yes3-divider" id="yes3-fmapr-wrapper"></div>

    </div>

    <div class="row" id="yes3-fmapr-footer">

        <div class="col-md-4 yes3-block">

            <input type='button' class='yes3-when-loaded' value="add a REDCap field" onclick="FMAPR.addRawREDCapField();" />

        </div>

        <div class="col-md-8 yes3-block">

            <div id="yes3-message"></div>

        </div>

    </div>

</div> <!-- container -->

<script>

    (function(){



    })

</script>




