<?php
/*******************************************************************************
 *
 * LEIDEN OPEN VARIATION DATABASE (LOVD)
 *
 * Created     : 2010-02-01
 * Modified    : 2013-11-07
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

function lovd_AJAX_deleteLogEntry (sViewListID, sID)
{
    // Create HTTP request object.
    var objHTTP = lovd_createHTTPRequest();
    objElement = document.getElementById(sID);
    objElement.style.cursor = 'progress';
    if (objHTTP) {
        objHTTP.onreadystatechange = function ()
        {
            if (objHTTP.readyState == 4) {
                objElement.style.cursor = '';
                if (objHTTP.status == 200) {
                    if (objHTTP.responseText.substring(0, 1) == '1') {
                        // Object successfully deleted.
                        lovd_AJAX_viewListHideRow(sViewListID, sID);
                        document.forms['viewlistForm_' + sViewListID].total.value --;
                        lovd_AJAX_viewListUpdateEntriesString(sViewListID);
                        lovd_AJAX_viewListAddNextRow(sViewListID);
                        return true;
                    } else if (objHTTP.responseText == '8') {
                        window.alert('Lost your session. Please log in again.');
                    } else if (objHTTP.responseText == '9') {
                        window.alert('Error while sending data. Please try again.');
                    } else if (!objHTTP.responseText || objHTTP.responseText == '0') {
                        // Silent failure.
                        return false;
                    } else {
                        window.alert('Unknown response :' + objHTTP.responseText);
                    }
                } else {
                    // FIXME; Maybe we should remove this...?
                    window.alert('Server error: ' + objHTTP.status);
                }
            }
        }
        objHTTP.open('GET', '<?php echo lovd_getInstallURL() . 'ajax/delete_log.php?id='; ?>' + escape(sID), true);
        objHTTP.send(null);
    }
}