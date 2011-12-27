<?php
/*******************************************************************************
 *
 * LEIDEN OPEN VARIATION DATABASE (LOVD)
 *
 * Created     : 2011-12-22
 * Modified    : 2011-12-27
 * For LOVD    : 3.0-beta-01
 *
 * Copyright   : 2004-2011 Leiden University Medical Center; http://www.LUMC.nl/
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
require ROOT_PATH . 'inc-init.php';

if ($_AUTH) {
    // If authorized, check for updates.
    require ROOT_PATH . 'inc-upgrade.php';
}





if (!empty($_PATH_ELEMENTS[1]) && ctype_digit($_PATH_ELEMENTS[1])) {
    // URL: /pedigree/00000001
    // View pedigree tree of a certain individual.

    // FIXME; should check for the existence of the correct needed custom columns.

    $nID = sprintf('%08d', $_PATH_ELEMENTS[1]);
    define('PAGE_TITLE', 'View pedigree for individual #' . $nID);
    require ROOT_PATH . 'inc-top-clean.php';
    lovd_printHeader(PAGE_TITLE);

    lovd_includeJS('inc-js-tooltip.php'); // For the mouseover.
    require ROOT_PATH . 'class/pedigree.php';
    $_PED = new Pedigree($nID);
    // FIXME; call new function to highlight the individual called?
    $_PED->drawHTML();

    require ROOT_PATH . 'inc-bot-clean.php';
    exit;
}