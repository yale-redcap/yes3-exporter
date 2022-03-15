FMAPR.useYes3Functions = true; // override FMAPR function if function of same name exists in YES3 namespace

/**
 * defined in YES3 namespace because handled by YES action icon listener
 */
YES3.Functions.downloadExportLog = function()
{
    let url = YES3.moduleProperties.serviceUrl
    + "&request=downloadExportLog"
    + "&export_uuid=" + encodeURIComponent($("select#export_uuid").val())
    + "&csrf_token=" + encodeURIComponent(redcap_csrf_token)
    ;

    //console.log(url);

    window.open(url);
}

FMAPR.downloadExportLogCallback = function( response )
{
    console.log('downloadExportLogCallback', response);
}

FMAPR.exportUUIDSelect = function()
{
    YES3.requestService(
        {
            "request":"getExportLogData",
            "export_uuid": $("select#export_uuid").val(),
            "username": "",
            "date0": "",
            "date1": "",
            "limit": 500
        }, 
        FMAPR.exportUUIDSelectCallback,
        true
    );

    YES3.contentLoaded = true;

    YES3.displayActionIcons();
}

FMAPR.exportConstraintSelect = function()
{
    YES3.requestService(
        {
            "request":"getExportLogData",
            "export_uuid": $("select#export_uuid").val(),
            "username": $("select#export_username").val(),
            "date0": $("input#export_date0").val(),
            "date1": $("input#export_date1").val(),
            "limit": 500
        }, 
        FMAPR.exportUUIDSelectCallback,
        true
    );
}

FMAPR.exportUUIDSelectCallback = function( response )
{
    console.log( 'exportUUIDSelectCallback', response );

    if ( response.message ){

        YES3.hello( response.message );
    }

    let fmaprTable = $("table#yes3-fmapr-export-log-table");

    let fmaprTableBody = $("tbody#yes3-fmapr-export-log-tbody");

    $("input#export_date0").val( response.observed_date0 );
    $("input#export_date1").val( response.observed_date1 );

    let userHtml = "<option value=''>all users</option>";

    for (let u=0; u<response.observed_usernames.length; u++){

        userHtml += `<option value='${response.observed_usernames[u]}'>${response.observed_usernames[u]}</option>`;
    }

    $("select#export_username").empty().append(userHtml);

    let rowHtml = "";

    for (let i=0; i<response.data.length; i++){

        rowHtml += `<tr id="yes3-fmapr-export-log-${response.data[i].log_id}">`;

        rowHtml += `<td class="yes3-td-left   yes3-cw05"><i class="fas fa-eye" onclick="FMAPR.inspectLogRecord(${response.data[i].log_id})"></i></td>`;

        rowHtml += `<td class="yes3-td-middle yes3-cw10">${response.data[i].log_id}</td>`;

        rowHtml += `<td class="yes3-td-middle yes3-cw15">${response.data[i].timestamp}</td>`;

        rowHtml += `<td class="yes3-td-middle yes3-cw15">${response.data[i].username}</td>`;

        rowHtml += `<td class="yes3-td-middle yes3-cw20">${response.data[i].destination}</td>`;

        rowHtml += `<td class="yes3-td-right  yes3-cw35">${response.data[i].message}</td>`;

        rowHtml += "</tr>";
    }

    fmaprTableBody.empty().append(rowHtml);

    FMAPR.resizeExportLogTable();
}

FMAPR.inspectLogRecord = function(log_id)
{
    let theRow = $(`tr#yes3-fmapr-export-log-${log_id}`);

    $(".yes3-selected").removeClass("yes3-selected");

    theRow.addClass("yes3-selected").find("i").addClass("yes3-selected");

    YES3.requestService(
        {
            "request":"getExportLogRecord",
            "log_id": log_id
        }, 
        FMAPR.inspectLogRecordCallback,
        true
    );
}

FMAPR.inspectLogRecordCallback = function( response )
{
    console.log(response);

    let tr = {};

    $("table#yes3-export-record tr").hide();

    for (const item in response){

        tr = $(`tr#yes3-export-${item}`);

        if ( tr.length && response[item] ){

            tr.show().find("td").eq(1)
                .css({
                    "color": "var(--yes3-input-color)"
                })
                .html( escapeHTML( ''+response[item] ) );
        }

        console.log(item, response[item]);
    }


    YES3.openPanel("yes3-record-inspector");
}


FMAPR.resizeExportLogTable = function()
{
    let gutterWidth = 30;
    let scrollbarWidth = 20;

    let fmaprTable = $("table#yes3-fmapr-export-log-table");

    let fmaprTableBody = $("tbody#yes3-fmapr-export-log-tbody");

    let parentSection = $("div#yes3-container").parent();

    if ( !fmaprTable.length ){
        return false;
    }

    let windowHeight = $(window).innerHeight();

    /**
     * The REDCap section div (#center) is programmatically forced to have vertical padding,
     * so we need to take that into account in the table height calculation.
     * The expression (parentSection.outerHeight() - parentSection.height()) returns the sum of top and bottom padding.
     */
    let tableHeight = windowHeight 
        - fmaprTable.offset().top 
        - scrollbarWidth 
        - (parentSection.outerHeight() - parentSection.height())
    ;

    // position() returns offset relative to parent object (the table)
    let bodyHeight = tableHeight - fmaprTableBody.position().top;

    //let tableWidth = $('div#yes3-fmapr-wrapper').width();
    let tableWidth = fmaprTable.width();

    let cw05 = 0.05 * (tableWidth - scrollbarWidth);
    let cw10 = 0.10 * (tableWidth - scrollbarWidth);
    let cw15 = 0.15 * (tableWidth - scrollbarWidth);
    let cw20 = 0.20 * (tableWidth - scrollbarWidth);
    let cw25 = 0.25 * (tableWidth - scrollbarWidth);
    let cw30 = 0.30 * (tableWidth - scrollbarWidth);
    let cw35 = 0.35 * (tableWidth - scrollbarWidth);
    let cw40 = 0.40 * (tableWidth - scrollbarWidth);
    let cw50 = 0.50 * (tableWidth - scrollbarWidth);

    //fmaprTable.css({'width': tableWidth+'px', 'height': tableHeight+'px'});
    fmaprTable.css({'height': tableHeight+'px'});

    fmaprTableBody.css({'height': bodyHeight+'px'});

    fmaprTable.find('.yes3-cw05').css({'width': cw05+'px', 'max-width': cw05+'px'});
    fmaprTable.find('.yes3-cw10').css({'width': cw10+'px', 'max-width': cw10+'px'});
    fmaprTable.find('.yes3-cw15').css({'width': cw15+'px', 'max-width': cw15+'px'});
    fmaprTable.find('.yes3-cw20').css({'width': cw20+'px', 'max-width': cw20+'px'});
    fmaprTable.find('.yes3-cw25').css({'width': cw25+'px', 'max-width': cw25+'px'});
    fmaprTable.find('.yes3-cw30').css({'width': cw30+'px', 'max-width': cw30+'px'});
    fmaprTable.find('.yes3-cw35').css({'width': cw35+'px', 'max-width': cw35+'px'});
    fmaprTable.find('.yes3-cw40').css({'width': cw40+'px', 'max-width': cw40+'px'});
    fmaprTable.find('.yes3-cw50').css({'width': cw50+'px', 'max-width': cw50+'px'});

    fmaprTable.show();
}

$( function(){

    $(window).resize( function(){

        FMAPR.resizeExportLogTable();
    });

    YES3.contentLoaded = false;

    YES3.displayActionIcons();

    FMAPR.getExportSettings();
})