FMAPR.useYes3Functions = true; // override FMAPR function if function of same name exists in YES3 namespace

FMAPR.export_specification = {};

FMAPR.SHOW_REMOVED = false;

FMAPR.exportTable = function(){

    return $("table#yes3-fmapr-export-table");
}

FMAPR.whatIsItYesOrNo = function( whatIsIt ){

    if ( YES3.isEmpty( whatIsIt ) ) return "no";

    if ( whatIsIt ===  1  ) return "yes";
    if ( whatIsIt === "1" ) return "yes";
    if ( whatIsIt === "y" ) return "yes";
    if ( whatIsIt === "Y" ) return "yes";

    return "no";
}

FMAPR.layoutLabel = function( layout ){

    if ( !layout ) return "?";
    if ( layout === "v" ) return "vertical";
    if ( layout === "h" ) return "horizontal";
    if ( layout === "r" ) return "repeating";
    return "?";
}

FMAPR.refreshExportTable = function( response )
{
    YES3.debugMessage('refreshExportTable', response);
    
    let $exportTableBody = FMAPR.exportTable().find("tbody#yes3-fmapr-export-tbody");

    $exportTableBody.empty();

    for (let i=0; i<response.length; i++){

        response[i].export_order = i+1;

        const $tr = $( FMAPR.exportTableRowHtml( response[i] ) );

        // the trash cell is rendered separately because it needs to be updated when the export is removed or restored
        FMAPR.renderExportTrashcanCell( $tr );

        $exportTableBody.append( $tr );
    }

    FMAPR.setExportTableSortHandler();

    FMAPR.renderExportVisibilityCell();

    //exportTableRowCount.html( `${response.exportTable.length} exports` );

    FMAPR.exportTable().show();

    $(window).trigger('resize');
}

FMAPR.setExportTableSortHandler = function(){

    FMAPR.exportTable().find("tbody")
    .sortable({
        items: 'tr',
        cursor: 'grab',
        axis: 'y',
        dropOnEmpty: false,
        start: function (e, ui) {
            ui.item.addClass("yes3-fmapr-row-selected");
        },
        stop: function (e, ui) {
            ui.item.removeClass("yes3-fmapr-row-selected");
            FMAPR.exportTableAfterUpdate();
        }
    });
}

FMAPR.exportTableAfterUpdate = function(){

    // get the collection of rows in the table
    const $rows = FMAPR.exportTable().find("tbody tr");

    const updates = [];

    // iterate through the rows and update the export_order attribute

    let unremovedOrder = 1;
    let removedOrder = 9001;

    $rows.each( function(i, row){

        const removed = $(row).attr('data-removed') || "0";

        const order = removed === "0" ? unremovedOrder++ : removedOrder++;

        const log_id = parseInt($(row).data('log_id'));

        $(row).attr('data-export_order', order.toString());

        updates.push({"log_id": log_id, "key": "export_order", "value": order});
        updates.push({"log_id": log_id, "key": "removed", "value": removed});
    });

    YES3.debugMessage('exportTableAfterUpdate', updates);
  
    YES3.requestService( { 
        "request": "update_export_table_settings", 
        "updates": updates
    }, FMAPR.exportTableAfterUpdateCallback, true );
}

FMAPR.exportTableAfterUpdateCallback = function( response ){

    YES3.debugMessage('exportTableAfterUpdateCallback', response);
}

FMAPR.exportTableRowHtml = function( data ){

    data.removed = data.removed || "0";

    const trashIcon = data.removed === "0" ? "far fa-trash-alt" : "fas fa-trash-restore-alt";
    const trashAction = data.removed === "0" ? "1" : "0";

    return `
        <tr id="yes3-fmapr-export-${data.log_id}" data-export_uuid="${data.export_uuid}" data-log_id="${data.log_id}" data-export_order="${data.export_order}" data-removed="${data.removed}">
            <td class="yes3-cw10 yes3-halign-center yes3-required-column"><i class="fas fa-edit" onclick="FMAPR.editExport('${data.log_id}')"></i></td>
            <td class="yes3-cw10 yes3-halign-center yes3-required-column"><i class="fas fa-download" onclick="FMAPR.downloadExport('${data.log_id}')"></i></td>
            <td class="yes3-cw15 yes3-halign-left yes3-required-column">${data.export_name}</td>
            <td class="yes3-cw25 yes3-halign-left">${data.export_label}</td>
            <td class="yes3-cw10 yes3-halign-center">${FMAPR.layoutLabel(data.export_layout)}</td>
            <td class="yes3-cw10 yes3-halign-center">${FMAPR.whatIsItYesOrNo(data.export_batch)}</td>
            <td class="yes3-cw10 yes3-halign-center">${data.column_count}</td>
            <td class="yes3-cw10 yes3-halign-center yes3-required-column yes3-fmapr-trash-cell"></i></td>
        </tr>
    `;
}

FMAPR.renderExportTrashcanCell = function( $tr ){

    const log_id = $tr.data('log_id');
    const removed = $tr.attr('data-removed') || "0";

    const trashIcon = removed === "0" ? "far fa-trash-alt" : "fas fa-trash-restore-alt";
    const trashAction = removed === "0" ? "1" : "0";

    YES3.debugMessage('renderExportTrashcanCell', log_id, removed, trashIcon, trashAction);

    $tr.find('td.yes3-fmapr-trash-cell').html(`<i class="${trashIcon}" onclick="FMAPR.toggleRemovedState('${log_id}', '${trashAction}')"></i>`);
}

FMAPR.renderExportVisibilityCell = function(){

    const $visibilityCell = $("td#yes3-fmapr-visibility-control");
    const removedExportCount = FMAPR.exportTable().find('tr[data-removed="1"]').length;

    $visibilityCell.empty();

    if ( removedExportCount > 0 && !FMAPR.SHOW_REMOVED ){

        $visibilityCell.html(`<i class="fas fa-eye" title="show ${removedExportCount} removed export(s)" onclick="FMAPR.showRemovedExports(true)"></i>`);
    }
    else if ( removedExportCount > 0 && FMAPR.SHOW_REMOVED ){

        $visibilityCell.html(`<i class="fas fa-eye-slash" title="hide ${removedExportCount} removed export(s)" onclick="FMAPR.showRemovedExports(false)"></i>`);
    }
    else {

        $visibilityCell.html('');
    }
}

FMAPR.showRemovedExports = function( show ){

    FMAPR.SHOW_REMOVED = show;
    FMAPR.renderExportTable();
    FMAPR.renderExportVisibilityCell();
}

FMAPR.toggleRemovedState = function( log_id ){

    const $tr = $(`tr#yes3-fmapr-export-${log_id}`);

    // toggle the 'removed' attribute of the export table row
    const removed = $tr.attr('data-removed') === "0" ? "1" : "0";

    // set the 'removed' attribute of the export table row to the specified value
    $tr.attr('data-removed', removed);

    // update the trashcan icon in the row
    FMAPR.renderExportTrashcanCell( $tr );

    // potentially hide the row if the 'show removed' option is not enabled
    FMAPR.renderExportTable();

    // update the contens of the visibility control cell (icon to show or hide removed exports)
    FMAPR.renderExportVisibilityCell();

    // renumber the export_order attribute of each row, and save the new order to the database
    FMAPR.exportTableAfterUpdate();
}

FMAPR.renderExportTable = function()
{
    if ( !FMAPR.exportTable().is(":visible") ) return;

    const ROW_WIDTH_CUTOFF = 700;

    const scrollbarWidth = 20;

    const $exportTable = FMAPR.exportTable();

    const $exportTableBody = $("tbody#yes3-fmapr-export-tbody");

    const $parentSection = $("div#yes3-container").parent();

    //$exportTable.show();

    // optionally show or hide the removed exports
    if ( !FMAPR.SHOW_REMOVED ) $exportTableBody.find('tr[data-removed="1"]').hide();
    else $exportTableBody.find('tr[data-removed="1"]').show();

    const windowHeight = $(window).innerHeight();

    /**
     * The REDCap section div (#center) is programmatically forced to have vertical padding,
     * so we need to take that into account in the table height calculation.
     * The expression (parentSection.outerHeight() - parentSection.height()) returns the sum of top and bottom padding.
     */
    let tableHeight = windowHeight 
        - $exportTable.offset().top 
        - scrollbarWidth 
        - ($parentSection.outerHeight() - $parentSection.height())
    ;

    // position() returns offset relative to parent object (the table)
    let bodyHeight = tableHeight - $exportTableBody.position().top;

    //let tableWidth = $('div#yes3-fmapr-wrapper').width();
    const tableWidth = $exportTable.width();
    const rowWidth = tableWidth - scrollbarWidth;

    const $exportRowsToDisplay = FMAPR.SHOW_REMOVED ? $exportTableBody.find('tr') : $exportTableBody.find('tr[data-removed="0"]');

    const $requiredHeaderCells = FMAPR.exportTable().find('thead tr th.yes3-required-column');
    const $requiredFooterCells = FMAPR.exportTable().find('tfoot tr td.yes3-required-column');
    const $requiredTbodyCells  = $exportRowsToDisplay.find('td.yes3-required-column');
$
    const $notRequiredHeaderCells = FMAPR.exportTable().find('thead tr th:not(.yes3-required-column)');
    const $notRequiredFooterCells = FMAPR.exportTable().find('tfoot tr td:not(.yes3-required-column)');
    const $notRequiredTbodyCells  = $exportRowsToDisplay.find('td:not(.yes3-required-column)');

    const requiredColumnCount = $requiredHeaderCells.length;

    //exportTable.css({'height': tableHeight+'px'});
    //exportTableBody.css({'height': bodyHeight+'px'});

    if ( rowWidth < ROW_WIDTH_CUTOFF ){

        const cellWidth = rowWidth / requiredColumnCount;
            
        $notRequiredHeaderCells.hide();
        $notRequiredTbodyCells.hide();
        $notRequiredFooterCells.hide();
$
        $requiredHeaderCells.css({'width': cellWidth+'px', 'max-width': cellWidth+'px'}).show();
        $requiredTbodyCells.css({'width': cellWidth+'px', 'max-width': cellWidth+'px'}).show();
        $requiredFooterCells.css({'width': cellWidth+'px', 'max-width': cellWidth+'px'}).show();
    }
    else {

        const cw05 = 0.05 * rowWidth;
        const cw10 = 0.10 * rowWidth;
        const cw15 = 0.15 * rowWidth;
        const cw20 = 0.20 * rowWidth;
        const cw25 = 0.25 * rowWidth;
        const cw30 = 0.30 * rowWidth;
        const cw35 = 0.35 * rowWidth;
        const cw40 = 0.40 * rowWidth;
        const cw50 = 0.50 * rowWidth;
        const cw80 = 0.80 * rowWidth;
        const cw85 = 0.85 * rowWidth;
        const cw90 = 0.90 * rowWidth;
        const cw95 = 0.95 * rowWidth;

        $exportTable.find('.yes3-cw05').css({'width': cw05+'px', 'max-width': cw05+'px'}).show();
        $exportTable.find('.yes3-cw10').css({'width': cw10+'px', 'max-width': cw10+'px'}).show();
        $exportTable.find('.yes3-cw15').css({'width': cw15+'px', 'max-width': cw15+'px'}).show();
        $exportTable.find('.yes3-cw20').css({'width': cw20+'px', 'max-width': cw20+'px'}).show();
        $exportTable.find('.yes3-cw25').css({'width': cw25+'px', 'max-width': cw25+'px'}).show();
        $exportTable.find('.yes3-cw30').css({'width': cw30+'px', 'max-width': cw30+'px'}).show();
        $exportTable.find('.yes3-cw35').css({'width': cw35+'px', 'max-width': cw35+'px'}).show();
        $exportTable.find('.yes3-cw40').css({'width': cw40+'px', 'max-width': cw40+'px'}).show();
        $exportTable.find('.yes3-cw50').css({'width': cw50+'px', 'max-width': cw50+'px'}).show();
        $exportTable.find('.yes3-cw80').css({'width': cw80+'px', 'max-width': cw80+'px'}).show();
        $exportTable.find('.yes3-cw85').css({'width': cw85+'px', 'max-width': cw85+'px'}).show();
        $exportTable.find('.yes3-cw90').css({'width': cw90+'px', 'max-width': cw90+'px'}).show();
        $exportTable.find('.yes3-cw95').css({'width': cw95+'px', 'max-width': cw95+'px'}).show();
    }

    //exportTableBody.scrollTop(exportTableBody.prop('scrollHeight') - exportTableBody.height());
}

FMAPR.loadSpecifications = function( get_removed )
{
    YES3.debugMessage('loadSpecifications');

    get_removed = get_removed || 0;
    
    YES3.requestService( { 
        "request": "getExportSpecificationList", 
        "get_removed": get_removed
    }, FMAPR.loadSpecificationsCallback, true );
}

FMAPR.loadSpecificationsCallback = function( response )
{
    YES3.debugMessage('loadSpecificationsCallback', response, typeof response);

    FMAPR.refreshExportTable( response );
}

$( function(){

    YES3.contentLoaded = false;

    YES3.displayActionIcons();

    FMAPR.loadSpecifications( 1 ); // include removed exports

    $(window).resize( function(){

        FMAPR.renderExportTable();
    });

})