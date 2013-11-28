<?php
/*******************************************************************************
 *
 * LEIDEN OPEN VARIATION DATABASE (LOVD)
 *
 * Created     : 2013-11-05
 * Modified    : 2013-11-28
 * For LOVD    : 3.0-09
 *
 * Copyright   : 2004-2013 Leiden University Medical Center; http://www.LUMC.nl/
 * Programmer  : Ing. Ivo F.A.C. Fokkema <I.F.A.C.Fokkema@LUMC.nl>
 *
 *
 * This file is part of LOVD.
 *
 * LOVD is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * LOVD is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with LOVD.  If not, see <http://www.gnu.org/licenses/>.
 *
 *************/

define('ROOT_PATH', './');
header('Content-type: text/javascript; charset=UTF-8');
header('Expires: ' . date('r', time()+(180*60)));
require ROOT_PATH . 'inc-lib-init.php';

// Find out whether or not we're using SSL.
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' && !empty($_SERVER['SSL_PROTOCOL'])) {
    // We're using SSL!
    define('PROTOCOL', 'https://');
} else {
    define('PROTOCOL', 'http://');
}
?>
function lovd_resetAfterFailedRun (nAnalysisID)
{
    // Resets the table after a failed run attempt.

    if (typeof(nAnalysisID) == 'undefined') {
        return false;
    }

    $('#analysis_' + nAnalysisID).attr('class', 'analysis analysis_not_run');
    $('#analysis_' + nAnalysisID + '_message td')
        .html($('#analysis_' + nAnalysisID + '_message td').attr('htmlold'))
        .attr('htmlold', '');
    $('#analysis_' + nAnalysisID)
        .attr('onclick', $('#analysis_' + nAnalysisID).attr('onclickold'))
        .attr('onclickold', '');
    return true;
}





function lovd_runAnalysis (nIndividualID, nAnalysisID)
{
    // Starts the analysis of the given individual.

    if (typeof(nIndividualID) == 'undefined' || typeof(nAnalysisID) == 'undefined') {
        alert('Incorrect argument(s) passed to runAnalysis function.');
        return false;
    }

    $.get('<?php echo lovd_getInstallURL(); ?>ajax/run_analysis.php?individualid=' + escape(nIndividualID) + '&analysisid=' + escape(nAnalysisID),
        function () {
            // Remove onClick handler and change class of table, to visually show that it's running.
            $('#analysis_' + nAnalysisID)
                .attr('onclickold', $('#analysis_' + nAnalysisID).attr('onclick'))
                .attr('onclick', '');
            $('#analysis_' + nAnalysisID).attr('class', 'analysis analysis_running');
            $('#analysis_' + nAnalysisID + '_message td')
                .attr('htmlold', $('#analysis_' + nAnalysisID + '_message td').html())
                .html('Running analysis... <IMG src="gfx/ajax_analysis_running.gif" alt="Running analysis..." width="16" height="11" style="position: relative; top: 2px; right: -10px;">');
        })
        .done(
            function (data) {
                if (data == '0') {
                    // Failure, reset table.
                    lovd_resetAfterFailedRun(nAnalysisID);
                    alert('Individual not valid or no authorization to start a new analysis. Refreshing the page...');
                    location.reload();
                    return false;
                } else if (oRegExp = /1\s(\d+)/.exec(data)) {
                    // Success! We're running...
                    // Now call the script that will start filtering.
                    var nRunID = oRegExp[1];
                    return lovd_runNextFilter(nAnalysisID, nRunID);
                } else if (data == '8') {
                    // Failure, reset table.
                    lovd_resetAfterFailedRun(nAnalysisID);
                    alert('Lost your session. Please log in again.');
                } else if (data == '9') {
                    // Failure, reset table.
                    lovd_resetAfterFailedRun(nAnalysisID);
                    alert('Error while sending data. Please try again.\nIf this error persists, please contact support.');
                } else {
                    // Some other error.
                    lovd_resetAfterFailedRun(nAnalysisID);
                    alert(data);
                }})
        .fail(
            function (data) {
                // Failure, reset table.
                lovd_resetAfterFailedRun(nAnalysisID);
                alert('Failed to start analysis. Please try again.\nIf this error persists, please contact support.');
                return false;
            });
    return true;
}





function lovd_runNextFilter (nAnalysisID, nRunID)
{
    // Calls the script to run the next filter.

    if (typeof(nAnalysisID) == 'undefined' || typeof(nRunID) == 'undefined') {
        alert('Incorrect argument(s) passed to runNextFilter function.');
        return false;
    }

    $.get('<?php echo lovd_getInstallURL(); ?>ajax/run_next_filter.php?runid=' + escape(nRunID))
        .done(
            function (data) {
                if (data == '0') {
                    // Failure, we're in trouble, reload view.
                    alert('Filter step not valid or no authorization to start a new filter step. Refreshing the page...');
                    location.reload();

                } else if (oRegExp = /1\s(\w+)\s(\d+)\s([^\s]+)(\sdone)?$/.exec(data)) {
                    // Success! Mark line and continue to the next, or stop if we're done...
                    var sFilterID     = oRegExp[1];
                    var nVariantsLeft = oRegExp[2];
                    var nTime         = oRegExp[3];
                    var bDone         = (typeof(oRegExp[4]) != 'undefined');
                    oTR = $('#analysis_' + nAnalysisID + '_filter_' + sFilterID);
                    oTR.attr('class', 'filter_completed');
                    oTR.children('td:eq(1)').html(nTime);
                    oTR.children('td:eq(2)').html(nVariantsLeft);

                    if (!bDone) {
                        return lovd_runNextFilter(nAnalysisID, nRunID);
                    }

                    // And also, we're done...
                    $('#analysis_' + nAnalysisID).attr('class', 'analysis analysis_run');
                    $('#analysis_' + nAnalysisID + '_message td').html('Click to see results');

                    // Table should get a new ID, maybe also the filter lines (although those IDs are not used anymore).
                    $('#analysis_' + nAnalysisID).attr('id', 'run_' + nRunID);
                    // Also fix onclick.
                    $('#run_' + nRunID).attr('onclick', 'lovd_showAnalysisResults(\'' + nRunID + '\');');
                    $('#run_' + nRunID).attr('onclickold', '');

                    // Now load the VL.
                    lovd_showAnalysisResults(nRunID);
                    return true;

                } else if (data == '8') {
                    // Failure, we're in trouble, reload view.
                    alert('Lost your session. Please log in again.');
                    location.reload();

                } else if (data == '9') {
                    // Failure, we're in trouble, reload view.
                    alert('Error while sending data. Please try again.\nIf this error persists, please contact support.');
                    location.reload();

                } else {
                    // Some other error.
                    alert(data);
                    location.reload();
                }})
        .fail(
            function (data) {
                // Failure, we're in trouble, reload view.
                alert('Failed to start analysis. Please try again.\nIf this error persists, please contact support.');
                location.reload();
            });
    return false;
}





function lovd_showAnalysisResults (nRunID)
{
    // Calls the ViewList to refresh and load the given Run ID's results.

    // Set loading image, and show VL Div.
    $('#viewlistDiv_CustomVL_AnalysisRunResults_for_I_VE').html('<IMG src="gfx/ajax_loading.gif" alt="Loading..." width="100" height="100">');
    $('#analysis_results_VL').show();

    // Set new search value.
    $('#viewlistForm_CustomVL_AnalysisRunResults_for_I_VE').children('input[name="search_runid"]').attr('value', nRunID);
    // Sometimes it's disabled...
    $('#viewlistForm_CustomVL_AnalysisRunResults_for_I_VE').children('input[name="search_runid"]')[0].disabled = false;

    // The ViewList normally loads only hidden input fields for skipped columns (like runid). But we want
    // to have a default filter on variant effect, and can only do that if we have a search field.
    if (!$('#viewlistForm_CustomVL_AnalysisRunResults_for_I_VE').children('input[name="search_vog_effect"]').length) {
        $('#viewlistForm_CustomVL_AnalysisRunResults_for_I_VE').prepend('<INPUT type="hidden" name="search_vog_effect" value="!-">');
    } else {
        $('#viewlistForm_CustomVL_AnalysisRunResults_for_I_VE').children('input[name="search_vog_effect"]').attr('value', '!-');
    }

    // Mark the currently selected filter.
    $('#analysesTable td').css('background', '');
    $('#run_' + nRunID).parent().css('background', '#DDD');

    lovd_AJAX_viewListSubmit('CustomVL_AnalysisRunResults_for_I_VE');
}
