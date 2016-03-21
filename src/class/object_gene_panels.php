<?php
/*******************************************************************************
 *
 * LEIDEN OPEN VARIATION DATABASE (LOVD)
 *
 * Created     : 2016-03-01
 * Modified    : 2016-03-21
 * For LOVD    : 3.0-13
 *
 * Copyright   : 2004-2016 Leiden University Medical Center; http://www.LUMC.nl/
 * Programmers : Anthony Marty <anthony.marty@unimelb.edu.au>
 *               Ing. Ivo F.A.C. Fokkema <I.F.A.C.Fokkema@LUMC.nl>
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

// Don't allow direct access.
if (!defined('ROOT_PATH')) {
    exit;
}
// Require parent class definition.
require_once ROOT_PATH . 'class/objects.php';





class LOVD_GenePanel extends LOVD_Object {
    // This class extends the basic Object class and it handles the GenePanel object.
    var $sObject = 'Gene_Panel';





    function __construct ()
    {
        // Default constructor.

        // SQL code for loading an entry for an edit form.
        $this->sSQLLoadEntry = 'SELECT gp.*, ' .
                               'GROUP_CONCAT(DISTINCT gp2d.diseaseid ORDER BY gp2d.diseaseid SEPARATOR ";") AS _active_diseases ' .
                               'FROM ' . TABLE_GENE_PANELS . ' AS gp ' .
                               'LEFT OUTER JOIN ' . TABLE_GP2DIS . ' AS gp2d ON (gp.id = gp2d. genepanelid) ' .
                               'WHERE gp.id = ? ' .
                               'GROUP BY gp.id';

        // SQL code for viewing an entry.
        $this->aSQLViewEntry['SELECT']   = 'gp.*, ' .
//            'GROUP_CONCAT(DISTINCT d.id, ";", d.symbol ORDER BY (d.symbol != "" AND d.symbol != "-") DESC, d.symbol, d.name SEPARATOR ";;") AS __diseases, ' .
            'GROUP_CONCAT(DISTINCT d.id, ";", IFNULL(d.id_omim, 0), ";", IF(CASE d.symbol WHEN "-" THEN "" ELSE d.symbol END = "", d.name, d.symbol), ";", d.name ORDER BY (d.symbol != "" AND d.symbol != "-") DESC, d.symbol, d.name SEPARATOR ";;") AS __diseases, ' .
            'uc.name AS created_by_,' .
            'ue.name AS edited_by_';
        $this->aSQLViewEntry['FROM']     = TABLE_GENE_PANELS . ' AS gp ' .
            'LEFT OUTER JOIN ' . TABLE_GP2DIS . ' AS gp2d ON (gp.id = gp2d. genepanelid) ' .
            'LEFT OUTER JOIN ' . TABLE_DISEASES . ' AS d ON (gp2d.diseaseid = d.id) ' .
            'LEFT OUTER JOIN ' . TABLE_USERS . ' AS uc ON (gp.created_by = uc.id) ' .
            'LEFT OUTER JOIN ' . TABLE_USERS . ' AS ue ON (gp.edited_by = ue.id) ';
        $this->aSQLViewEntry['GROUP_BY'] = 'gp.id';

        // SQL code for viewing the list of gene panels
        $this->aSQLViewList['SELECT']   = 'gp.*, ' .
                                          'GROUP_CONCAT(DISTINCT IF(CASE d.symbol WHEN "-" THEN "" ELSE d.symbol END = "", d.name, d.symbol) ORDER BY (d.symbol != "" AND d.symbol != "-") DESC, d.symbol, d.name SEPARATOR ", ") AS diseases_, ' .
                                          'COUNT(DISTINCT gp2g.geneid) AS genes';
        $this->aSQLViewList['FROM']     = TABLE_GENE_PANELS . ' AS gp ' .
                                          'LEFT OUTER JOIN ' . TABLE_GP2DIS . ' AS gp2d ON (gp.id = gp2d.genepanelid) ' .
                                          'LEFT OUTER JOIN ' . TABLE_GP2GENE . ' AS gp2g ON (gp.id = gp2g.genepanelid) ' .
                                          'LEFT OUTER JOIN ' . TABLE_DISEASES . ' AS d ON (gp2d.diseaseid = d.id)';
        $this->aSQLViewList['GROUP_BY'] = 'gp.id';

        // List of columns and (default?) order for viewing an entry.
        $this->aColumnsViewEntry =
            array(
                'name' => 'Gene panel name',
                'description' => 'Description',
                'remarks' => 'Remarks',
                'type_' => 'Type',
                'cohort' => 'Cohort',
                'phenotype_group' => 'Phenotype group',
                'pmid_mandatory_' => 'PMID Mandatory',
                'diseases_' => 'Associated with diseases',
                'created_by_' => 'Created by',
                'created_date' => 'Created date',
                'edited_by_' => 'Edited by',
                'edited_date' => 'Edited date',
            );

        // List of columns and (default?) order for viewing a list of entries.
        $this->aColumnsViewList =
            array(
                'id_' => array(
                    'view' => array('ID', 60),
                    'db'   => array('gp.id', 'ASC', true)),
                'name' => array(
                    'view' => array('Name', 150),
                    'db'   => array('gp.name', 'ASC', true),
                    'legend' => array('The name of the gene panel.','')),
                'description' => array(
                    'view' => array('Description', 50),
                    'db'   => array('gp.description', 'ASC', true),
                    'legend' => array('The gene panel description.')),
                'type_' => array(
                    'view' => array('Type', 60),
                    'db'   => array('gp.type', 'ASC', true),
                    'legend' => array('The gene panel type of Gene Panel, Blacklist or Mendeliome','The gene panel type:<ul><li>Gene Panel - A panel of genes that will include variants during filtering</li><li>Blacklist - A panel of genes that will exclude variants during filtering</li><li>Mendeliome - A panel of genes with known disease causing variants</li></ul>')),
                'cohort' => array(
                    'view' => array('Cohort', 50),
                    'db'   => array('gp.cohort', 'ASC', true),
                    'legend' => array('The cohort the gene panel belongs to.')),
                'phenotype_group' => array(
                    'view' => array('Phenotype Group', 50),
                    'db'   => array('gp.phenotype_group', 'ASC', true),
                    'legend' => array('The phenotype group this gene panel belongs to. These groups are combined to calculate observation counts.')),
                'genes' => array(
                    'view' => array('Genes', 60),
                    'db'   => array('genes', 'DESC', 'INT_UNSIGNED'),
                    'legend' => array('The number of genes in this gene panel.')),
                'diseases_' => array(
                    'view' => array('Associated with diseases', 150),
                    'db'   => array('diseases_', false, 'TEXT'),
                    'legend' => array('The diseases associated with this gene panel.')),
                'created_date' => array(
                    'view' => array('Created Date', 110),
                    'db'   => array('gp.created_date', 'DESC', true),
                    'legend' => array('The date the gene panel was created.')),
            );
        $this->sSortDefault = 'id_';

        parent::__construct();
    }





    function checkFields ($aData, $zData = false)
    {
        // Checks fields before submission of data.
        global $_DB;

        // Mandatory fields.
        $this->aCheckMandatory =
            array(
                'name',
                'description',
            );

        parent::checkFields($aData);
    }





    function getForm ()
    {
        // Build the form.
        // If we've built the form before, simply return it. Especially imports will repeatedly call checkFields(), which calls getForm().
        if (!empty($this->aFormData)) {
            return parent::getForm();
        }
        global $_DB, $_AUTH;

        // Get Panel of diseases.
        $aDiseasesForm = $_DB->query('SELECT id, IF(CASE symbol WHEN "-" THEN "" ELSE symbol END = "", name, CONCAT(symbol, " (", name, ")")) FROM ' . TABLE_DISEASES . ' WHERE id > 0 ORDER BY (symbol != "" AND symbol != "-") DESC, symbol, name')->fetchAllCombine();
        $nDiseases = count($aDiseasesForm);
        if (!$nDiseases) {
            $aDiseasesForm = array('' => 'No disease entries available');
            $nDiseasesFormSize = 1;
        } else {
            $aDiseasesForm = array_combine(array_keys($aDiseasesForm), array_map('lovd_shortenString', $aDiseasesForm, array_fill(0, $nDiseases, 75)));
            $nDiseasesFormSize = ($nDiseases < 15? $nDiseases : 15);
        }

        $aSelectType = array(
            'gene_panel' => 'Gene Panel',
            'blacklist' => 'Blacklist',
            'mendeliome' => 'Mendeliome'
        );

        $this->aFormData =
            array(
                array('POST', '', '', '', '50%', '14', '50%'),
                array('', '', 'print', '<B>General information</B>'),
                'hr',
                array('Name', '', 'text', 'name', 30),
                array('Description', '', 'text', 'description', 70),
'gene_panel_type' => array('Type', 'Please note:<BR>Gene Panel - Genes will be included when filtering<BR>Blacklist - Genes will be excluded when filtering<BR>Mendeliome - All genes from all gene panels', 'select', 'type', 1, $aSelectType, '', false, false),
                array('Remarks (optional)', '', 'textarea', 'remarks', 70, 3),
                array('Cohort (optional)', '', 'text', 'cohort', 30),
                array('Phenotype group (optional)', '', 'text', 'phenotype_group', 30),
'pmid_mandatory' => array('PMID Mandatory', 'Require every gene added to have a supporting PubMed article', 'checkbox', 'pmid_mandatory', 1),
                'hr','skip',
                array('', '', 'print', '<B>Relation to diseases (optional)</B>'),
                'hr',
                array('This gene panel has been linked to these diseases', 'Listed are all disease entries currently configured in LOVD.', 'select', 'active_diseases', $nDiseasesFormSize, $aDiseasesForm, false, true, false),
                'hr',
                'skip'
            );

        if (ACTION != 'create') {
            // Only allow the setting of the gene panel type when it is first created. We do not want to be able to change the gene panel type afterwards.
            unset($this->aFormData['gene_panel_type']);
        }

        if ($_AUTH['level'] < LEVEL_MANAGER) {
            unset($this->aFormData['pmid_mandatory']);
        }

        return parent::getForm();
    }





    function prepareData ($zData = '', $sView = 'list')
    {
        // Prepares the data by "enriching" the variable received with links, pictures, etc.

        if (!in_array($sView, array('list', 'entry'))) {
            $sView = 'list';
        }
        // Makes sure it's an array and htmlspecialchars() all the values.
        $zData = parent::prepareData($zData, $sView);

        if ($sView == 'list') {
            $zData['created_date'] = substr($zData['created_date'], 0, 10);
        } else {
            // Associated with diseases...
            $zData['diseases_'] = '';
            $zData['disease_omim_'] = '';
            foreach($zData['diseases'] as $aDisease) {
                list($nID, $nOMIMID, $sSymbol, $sName) = $aDisease;
                // Link to disease entry in LOVD.
                $zData['diseases_'] .= (!$zData['diseases_']? '' : ', ') . '<A href="diseases/' . $nID . '">' . $sSymbol . '</A>';
                if ($nOMIMID) {
                    // Add link to OMIM for each disease that has an OMIM ID.
                    $zData['disease_omim_'] .= (!$zData['disease_omim_'] ? '' : '<BR>') . '<A href="' . lovd_getExternalSource('omim', $nOMIMID, true) . '" target="_blank">' . $sSymbol . ($sSymbol == $sName? '' : ' (' . $sName . ')') . '</A>';
                }
            }
            $zData['pmid_mandatory_'] = ($zData['pmid_mandatory']? 'Yes' : 'No');
        }
        $zData['type_'] = ucwords(str_replace('_', ' ', $zData['type']));

        return $zData;
    }





    function setDefaultValues ()
    {
        $_POST['pmid_mandatory'] = 1;
        return true;
    }
}
?>