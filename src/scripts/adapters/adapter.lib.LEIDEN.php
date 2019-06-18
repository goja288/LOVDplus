<?php
// Leiden specific adapter settings.
$_INSTANCE_CONFIG['attachments'] = false;
$_INSTANCE_CONFIG['conversion']['suffixes']['vep'] = 'directvep.data.lovd';
$_INSTANCE_CONFIG['conversion']['create_meta_file_if_missing'] = false;
$_INSTANCE_CONFIG['conversion']['enforce_hgnc_gene'] = false;
$_INSTANCE_CONFIG['conversion']['verbosity_other'] = 9;

$_INSTANCE_CONFIG['viewlists'] = array(
    // If set to true, ViewLists are not allowed to be downloaded, except specifically
    //  enabled as 'allow_download_from_level' in the ViewLists's settings below.
    'restrict_downloads' => true,

    // The screenings data listing on the individual's detailed view.
    'Screenings_for_I_VE' => array(
        'cols_to_show' => array(
            // Select these columns for the screenings listing on the individual's page.
            // Note, that you also need to define the hidden columns that
            //  are to be active, since LOVD+ might be filtering on them.
            // You can change the order of columns to any order you like.
            'id',
            'individualid', // Hidden, but needed for search.
            'Screening/Panel_coverage/Fraction',
            'Screening/Father/Panel_coverage/Fraction',
            'Screening/Mother/Panel_coverage/Fraction',
            'curation_progress_',
            'variants_found_',
            'analysis_status',
        )
    ),
    // The data analysis results data listing.
    'CustomVL_AnalysisRunResults_for_I_VE' => array(
        // Even when downloading ViewLists is restricted, allow downloading from LEVEL_MANAGER.
        'allow_download_from_level' => LEVEL_MANAGER,
        'cols_to_show' => array(
            // Select these columns for the analysis results table.
            // Note, that you also need to define the hidden columns that
            //  are to be active, since LOVD+ might be filtering on them.
            // By default, these columns are sorted by object type, but you can change the order to any order you like.
            'curation_status_',
            'curation_statusid',
            'variantid',
            'vog_effect',
            'chromosome',
            'allele_',
            'VariantOnGenome/DNA',
            'VariantOnGenome/Alamut',
            'VariantOnGenome/Conservation_score/PhyloP',
            'VariantOnGenome/HGMD/Association',
            'VariantOnGenome/Sequencing/Quality',
            'VariantOnGenome/Sequencing/GATKcaller',
            'obs_variant',
            'obs_var_ind_ratio',
            'obs_disease',
            'obs_var_dis_ind_ratio',

            'VariantOnGenome/Sequencing/Depth/Alt/Fraction',
            'VariantOnGenome/Sequencing/Father/Depth/Alt/Fraction',
            'VariantOnGenome/Sequencing/Mother/Depth/Alt/Fraction',

            'gene_disease_names',
            'VariantOnTranscript/DNA',
            'VariantOnTranscript/Protein',
            'VariantOnTranscript/GVS/Function',
            'gene_OMIM_',

            'runid',

            'gene_panels',
        )
    )
);

$_INSTANCE_CONFIG['observation_counts'] = array(
    'genepanel' => array(
        'columns' => array(
            'value' => 'Gene Panel',
            'total_individuals' => 'Total # Individuals',
            'percentage' => 'Percentage (%)'
        ),
        'categories' => array(
            'all',
            'gender',
        ),
        'show_decimals' => 1,
    ),
    'general' => array(
        // if columns is empty, use default columns list
        'columns' => array(
            'label' => 'Category',
            'value' => 'Value',
            'percentage' => 'Percentage (%)'
        ),
        'categories' => array(
            'all',
            'Individual/Gender',
        ),
        'show_decimals' => 1,
        'min_population_size' => 100,
    ),
);





class LOVD_LeidenDataConverter extends LOVD_DefaultDataConverter {
    // Contains the overloaded functions that we want different from the default.

    function cleanHeaders ($aHeaders)
    {
        // Leiden's headers can be appended by the Miracle ID.
        // Clean this off, and verify the identity of this file.
        // Check the child's Miracle ID with that we have in the meta data file, and die if there is a mismatch.
        foreach ($aHeaders as $key => $sHeader) {
            if (preg_match('/(Child|Patient|Father|Mother)_(\d+)$/', $sHeader, $aRegs)) {
                // If Child, check ID.
                if (!empty($this->aScriptVars['nMiracleID']) && in_array($aRegs[1], array('Child', 'Patient')) && $aRegs[2] != $this->aScriptVars['nMiracleID']) {
                    // Here, we won't try and remove the temp file. We need it for diagnostics, and it will save us from running into the same error over and over again.
                    die('Fatal: Miracle ID of ' . $aRegs[1] . ' (' . $aRegs[2] . ') does not match that from the meta file (' . $this->aScriptVars['nMiracleID'] . ')' . "\n");
                }
                // Clean ID from column.
                $aHeaders[$key] = substr($sHeader, 0, -(strlen($aRegs[2]) + 1));
                // Also clean "Child" and "Patient" off.
                $aHeaders[$key] = preg_replace('/_(Child|Patient)$/', '', $aHeaders[$key]);
            }
        }

        return $aHeaders;
    }





    function getInputFilePrefixPattern ()
    {
        // Returns the regex pattern of the prefix of variant input file names.
        // The prefix is often the sample ID or individual ID, and can be formatted to your liking.
        // Data files must be named "prefix.suffix", using the suffixes as defined in the conversion script.

        // If using sub patterns, make sure they are not counted, like so:
        //  (?:subpattern)
        return '(?:Child|Patient)_(?:\d+)';
    }





    function getRequiredHeaderColumns ()
    {
        // Returns an array of required variant input file column headers.
        // The order of these columns does NOT matter.

        return array(
            'chromosome',
            'position',
            'REF',
            'ALT',
            'QUAL',
            'FILTERvcf',
            'GATKCaller',
            'GT',
            'SYMBOL',
            'Feature',
        );
    }





    function prepareGeneAliases ()
    {
        // Return an array of gene aliases, with the gene symbol as given by VEP
        //  as the key, and the symbol as known by LOVD/HGNC as the value.
        // Example:
        // return array(
        //     'C4orf40' => 'PRR27',
        // );

        return array(
            // This list needs to be replaced now and then.
            // Added 2018-08-14, expire 2019-08-14.
            'C10orf137' => 'EDRF1',
            'C11orf93' => 'COLCA2',
            'C12orf52' => 'RITA1',
            'C13orf45' => 'LMO7DN',
            'C1orf63' => 'RSRP1',
            'C20orf201' => 'LKAAEAR1',
            'C3orf37' => 'HMCES',
            'C3orf43' => 'SMCO1',
            'C6orf229' => 'ARMH2',
            'C6orf70' => 'ERMARD',
            'C7orf41' => 'MTURN',
            'C9orf123' => 'DMAC1',
            'CXorf48' => 'CT55',
            'CXorf61' => 'CT83',
            'CXXC11' => 'RTP5',
            'KIAA1704' => 'GPALPP1',
            'PHF15' => 'JADE2',
            'PHF16' => 'JADE3',
            'PLAC1L' => 'OOSP2',
            'PNMA6C' => 'PNMA6A',
            'PRAC' => 'PRAC1',
            'RPS17L' => 'RPS17',
            'SCXB' => 'SCX',
            'SELRC1' => 'COA7',
            'SGK196' => 'POMK',
            'SMCR7' => 'MIEF2',
            'SPANXB2' => 'SPANXB1',
            'SPATA31A2' => 'SPATA31A1',
            'UQCC' => 'UQCC1',

            // 2018-09-11; Expire 2019-09-11.
            'AATK-AS1' => 'PVALEF',
            'ADC' => 'AZIN2',
            'AIM1L' => 'CRYBG2',
            'APITD1' => 'CENPS',
            'APITD1-CORT' => 'CENPS-CORT',
            'ATPIF1' => 'ATP5IF1',
            'BAI2' => 'ADGRB2',
            'BAIAP2-AS1' => 'BAIAP2-DT',
            'C15orf59-AS1' => 'INSYN1-AS1',
            'C16orf98' => 'PYCARD-AS1',
            'C19orf52' => 'TIMM29',
            'C19orf82' => 'ZNF561-AS1',
            'C1orf170' => 'PERM1',
            'C1orf86' => 'FAAP20',
            'C20orf166-AS1' => 'MIR1-1HG-AS1',
            'CCDC163P' => 'CCDC163',
            'CCDC23' => 'SVBP',
            'CCDC74B-AS1' => 'MED15P9',
            'CPSF3L' => 'INTS11',
            'CRAT40' => 'LINC02563',
            'DIGIT' => 'GSC-DT',
            'DKFZP434L187' => 'LINC02249',
            'DLX2-AS1' => 'DLX2-DT',
            'EFTUD1P1' => 'EFL1P1',
            'EIF3J-AS1' => 'EIF3J-DT',
            'FAM132A' => 'C1QTNF12',
            'FAM213B' => 'PRXL2B',
            'FAM27L' => 'FAM27E5',
            'FAM46B' => 'TENT5B',
            'FLJ10038' => 'GABPB1-IT1',
            'FLJ26245' => 'LINC02167',
            'FLJ30403' => 'LINC01530',
            'GLTPD1' => 'CPTP',
            'GLTSCR2-AS1' => 'NOP53-AS1',
            'GOLGA6L5' => 'GOLGA6L5P',
            'HDGFRP2' => 'HDGFL2',
            'HNRNPCP5' => 'HNRNPCL2',
            'HOXD-AS1' => 'HAGLR',
            'KIAA0125' => 'FAM30A',
            'KIAA1751' => 'CFAP74',
            'LEPRE1' => 'P3H1',
            'LINC00116' => 'MTLN',
            'LINC00338' => 'SNHG20',
            'LINC00657' => 'NORAD',
            'LINC00675' => 'TMEM238L',
            'LINC01158' => 'PANTR1',
            'LINC01314' => 'CTXND1',
            'LINC01384' => 'LNCNEF',
            'LINC02081' => 'SCAT1',
            'LPPR2' => 'PLPPR2',
            'LPPR3' => 'PLPPR3',
            'MAFG-AS1' => 'MAFG-DT',
            'MAGOH2' => 'MAGOH2P',
            'MGC45922' => 'LINC01869',
            'MGC57346' => 'ARF2P',
            'MIR203' => 'MIR203A',
            'MIR3545' => 'MIR203B',
            'MIR4433' => 'MIR4433A',
            'MIR549' => 'MIR549A',
            'MMP24-AS1' => 'MMP24OS',
            'PAPL' => 'ACP7',
            'PPY2' => 'PPY2P',
            'PRAMEF23' => 'PRAMEF5',
            'PRCAT47' => 'ARLNC1',
            'PTCHD2' => 'DISP3',
            'RNASEK-C17ORF49' => 'RNASEK-C17orf49',
            'RSG1' => 'CPLANE2',
            'SELV' => 'SELENOV',
            'SEPN1' => 'SELENON',
            'SLC35E2' => 'SLC35E2A',
            'SNORA39' => 'SNORA71E',
            'SNORD85' => 'SNORD103C',
            'TCONS_00024492' => 'LINC01082',
            'TISP43' => 'PRSS40A',
            'TMEM57' => 'MACO1',
            'TRAPPC2P1' => 'TRAPPC2B',
            'TRNAA3' => 'TRA-AGC8-1',
            'TRNAA4' => 'TRA-CGC3-1',
            'TRNAC12' => 'TRC-GCA5-1',
            'TRNAC24' => 'TRC-GCA8-1',
            'TRNAC29' => 'TRC-GCA2-4',
            'TRNAC30' => 'TRC-GCA14-1',
            'TRNAC9' => 'TRC-GCA2-3',
            'TRNAD17' => 'TRD-GTC2-11',
            'TRNAE11' => 'TRE-TTC2-2',
            'TRNAE1' => 'TRE-TTC3-1',
            'TRNAE26' => 'TRE-TTC1-1',
            'TRNAF10' => 'TRF-GAA1-6',
            'TRNAG16' => 'TRG-GCC2-5',
            'TRNAG18' => 'TRG-TCC3-1',
            'TRNAG19' => 'TRG-GCC2-2',
            'TRNAG20' => 'TRG-GCC5-1',
            'TRNAG22' => 'TRG-CCC2-1',
            'TRNAG2' => 'TRG-GCC2-6',
            'TRNAG3' => 'TRG-TCC1-1',
            'TRNAG4' => 'TRG-CCC2-2',
            'TRNAG5' => 'TRG-GCC3-1',
            'TRNAG6' => 'TRG-GCC2-4',
            'TRNAH1' => 'TRH-GTG1-7',
            'TRNAH2' => 'TRH-GTG1-8',
            'TRNAH6' => 'TRH-GTG1-9',
            'TRNAI13' => 'TRI-TAT2-1',
            'TRNAI15' => 'TRI-AAT5-4',
            'TRNAI18' => 'TRI-TAT1-1',
            'TRNAI23' => 'TRI-AAT5-5',
            'TRNAI24' => 'TRI-AAT4-1',
            'TRNAK13' => 'TRK-CTT1-1',
            'TRNAK15' => 'TRK-CTT3-1',
            'TRNAK17' => 'TRK-TTT1-1',
            'TRNAK1' => 'TRK-TTT3-5',
            'TRNAK21' => 'TRK-CTT5-1',
            'TRNAK26' => 'TRK-CTT2-5',
            'TRNAK4' => 'TRK-CTT6-1',
            'TRNAK6' => 'TRK-CTT1-2',
            'TRNAK9' => 'TRK-CTT4-1',
            'TRNAL11' => 'TRL-CAG2-2',
            'TRNAL13' => 'TRL-CAG2-1',
            'TRNAL2' => 'TRL-TAG1-1',
            'TRNAL7' => 'TRL-TAG3-1',
            'TRNAM13' => 'TRM-CAT6-1',
            'TRNAM14' => 'TRX-CAT1-8',
            'TRNAM9' => 'TRM-CAT2-1',
            'TRNAN17' => 'TRN-GTT2-5',
            'TRNAN20' => 'TRN-GTT2-6',
            'TRNAP12' => 'TRP-CGG1-2',
            'TRNAP19' => 'TRP-TGG3-3',
            'TRNAP21' => 'TRP-TGG3-4',
            'TRNAP5' => 'TRP-AGG1-1',
            'TRNAP8' => 'TRP-CGG1-3',
            'TRNAP9' => 'TRP-AGG2-7',
            'TRNAQ1' => 'TRQ-CTG1-5',
            'TRNAQ24' => 'TRQ-CTG1-4',
            'TRNAQ2' => 'TRQ-TTG1-1',
            'TRNAR18' => 'TRR-CCT3-1',
            'TRNAR19' => 'TRR-CCG1-3',
            'TRNAR1' => 'TRR-TCT2-1',
            'TRNAR20' => 'TRR-CCT2-1',
            'TRNAR21' => 'TRR-ACG1-3',
            'TRNAR22' => 'TRR-TCG1-1',
            'TRNAR23' => 'TRR-CCT5-1',
            'TRNAR25' => 'TRR-TCG3-1',
            'TRNAR4' => 'TRR-CCG2-1',
            'TRNAR8' => 'TRR-CCT1-1',
            'TRNAS14' => 'TRS-AGA2-6',
            'TRNAS18' => 'TRS-GCT4-2',
            'TRNAS23' => 'TRS-GCT4-3',
            'TRNAS6' => 'TRS-CGA1-1',
            'TRNAT10' => 'TRT-CGT4-1',
            'TRNAT22' => 'TRT-AGT5-1',
            'TRNAT4' => 'TRT-AGT1-3',
            'TRNAT6' => 'TRT-CGT2-1',
            'TRNAT8' => 'TRT-AGT1-1',
            'TRNAT9' => 'TRT-AGT1-2',
            'TRNAU1' => 'TRU-TCA1-1',
            'TRNAV32' => 'TRV-CAC3-1',
            'TRNAW3' => 'TRW-CCA3-3',
            'TRNAW4' => 'TRW-CCA1-1',
            'TRNAW7' => 'TRW-CCA2-1',
            'TRNAY4' => 'TRY-GTA2-1',
            'TRNAY9' => 'TRY-ATA1-1',
            'UTP11L' => 'UTP11',
            'WTH3DI' => 'RAB6D',
            'ZAK' => 'MAP3K20',
            'ZNF542' => 'ZNF542P',

            // 2018-12-20; Expire 2019-12-20. Don't commit, users may want the original symbols.
            'ACRC' => 'GCNA',
            'ADCK3' => 'COQ8A',
            'ADRBK1' => 'GRK2',
            'ADRBK2' => 'GRK3',
            'AGPAT9' => 'GPAT3',
            'AIM1' => 'CRYBG1',
            'ALPPL2' => 'ALPG',
            'ALS2CR11' => 'C2CD6',
            'ANKRD32' => 'SLF1',
            'ANXA8L2' => 'ANXA8L1',
            'ATHL1' => 'PGGHG',
            'ATP5A1' => 'ATP5F1A',
            'ATP5F1' => 'ATP5PB',
            'ATP5G2' => 'ATP5MC2',
            'ATP5H' => 'ATP5PD',
            'ATP5I' => 'ATP5ME',
            'ATP5L2' => 'ATP5MGL',
            'ATP5L' => 'ATP5MG',
            'ATP5SL' => 'DMAC2',
            'AZI1' => 'CEP131',
            'B3GALTL' => 'B3GLCT',
            'BAI1' => 'ADGRB1',
            'BAI3' => 'ADGRB3',
            'BCMO1' => 'BCO1',
            'BRE' => 'BABAM2',
            'BZRAP1' => 'TSPOAP1',
            'C10orf107' => 'CABCOCO1',
            'C10orf112' => 'MALRD1',
            'C10orf118' => 'CCDC186',
            'C10orf11' => 'LRMDA',
            'C10orf128' => 'TMEM273',
            'C10orf131' => 'CC2D2B',
            'C10orf54' => 'VSIR',
            'C10orf76' => 'ARMH3',
            'C11orf30' => 'EMSY',
            'C11orf63' => 'JHY',
            'C11orf73' => 'HIKESHI',
            'C11orf82' => 'DDIAS',
            'C11orf83' => 'UQCC3',
            'C11orf85' => 'MAJIN',
            'C12orf23' => 'TMEM263',
            'C12orf5' => 'TIGAR',
            'C12orf79' => 'LINC01619',
            'C13orf35' => 'ATP11AUN',
            'C14orf159' => 'DGLUCY',
            'C14orf166B' => 'LRRC74A',
            'C15orf26' => 'CFAP161',
            'C15orf27' => 'TMEM266',
            'C15orf52' => 'CCDC9B',
            'C15orf57' => 'CCDC32',
            'C15orf59' => 'INSYN1',
            'C16orf11' => 'PRR35',
            'C16orf52' => 'MOSMO',
            'C16orf62' => 'VPS35L',
            'C17orf103' => 'NATD1',
            'C17orf62' => 'CYBC1',
            'C17orf70' => 'FAAP100',
            'C18orf56' => 'TYMSOS',
            'C19orf59' => 'MCEMP1',
            'C19orf68' => 'ZSWIM9',
            'C19orf69' => 'ERICH4',
            'C1orf101' => 'CATSPERE',
            'C1orf106' => 'INAVA',
            'C1orf168' => 'FYB2',
            'C1orf173' => 'ERICH3',
            'C1orf177' => 'LEXM',
            'C1orf192' => 'CFAP126',
            'C1orf228' => 'ARMH1',
            'C1orf27' => 'ODR4',
            'C1QTNF9B-AS1' => 'PCOTH',
            'C20orf112' => 'NOL4L',
            'C20orf166' => 'MIR1-1HG',
            'C20orf196' => 'SHLD1',
            'C20orf26' => 'CFAP61',
            'C22orf29' => 'RTL10',
            'C2orf62' => 'CATIP',
            'C2orf71' => 'PCARE',
            'C2orf82' => 'SNORC',
            'C3orf17' => 'NEPRO',
            'C3orf27' => 'LINC01565',
            'C3orf55' => 'PQLC2L',
            'C3orf83' => 'MKRN2OS',
            'C4orf21' => 'ZGRF1',
            'C4orf22' => 'CFAP299',
            'C4orf26' => 'ODAPH',
            'C4orf29' => 'ABHD18',
            'C5orf42' => 'CPLANE1',
            'C5orf45' => 'MRNIP',
            'C5orf55' => 'EXOC3-AS1',
            'C6orf10' => 'TSBP1',
            'C6orf25' => 'MPIG6B',
            'C7orf10' => 'SUGCT',
            'C7orf34' => 'LLCFC1',
            'C7orf55' => 'FMC1',
            'C7orf60' => 'BMT2',
            'C7orf62' => 'TEX47',
            'C7orf63' => 'CFAP69',
            'C7orf73' => 'STMP1',
            'C8orf46' => 'VXN',
            'C8orf47' => 'ERICH5',
            'C9orf114' => 'SPOUT1',
            'C9orf171' => 'CFAP77',
            'CARKD' => 'NAXD',
            'CASC5' => 'KNL1',
            'CASP16' => 'CASP16P',
            'CCBL2' => 'KYAT3',
            'CCDC101' => 'SGF29',
            'CCDC108' => 'CFAP65',
            'CCDC111' => 'PRIMPOL',
            'CCDC129' => 'ITPRID1',
            'CCDC132' => 'VPS50',
            'CCDC135' => 'DRC7',
            'CCDC147' => 'CFAP58',
            'CCDC176' => 'BBOF1',
            'CCDC19' => 'CFAP45',
            'CCDC42B' => 'CFAP73',
            'CCDC64' => 'BICDL1',
            'CCRN4L' => 'NOCT',
            'CD97' => 'ADGRE5',
            'CECR1' => 'ADA2',
            'CECR5' => 'HDHD5',
            'CIRH1A' => 'UTP4',
            'CNIH' => 'CNIH1',
            'CRAMP1L' => 'CRAMP1',
            'CSRP2BP' => 'KAT14',
            'CXorf30' => 'CFAP47',
            'CXorf36' => 'DIPK2B',
            'DDX26B' => 'INTS6L',
            'DFNA5' => 'GSDME',
            'DFNB31' => 'WHRN',
            'DFNB59' => 'PJVK',
            'DGCR14' => 'ESS2',
            'DHFRL1' => 'DHFR2',
            'DOPEY2' => 'DOP1B',
            'DPCR1' => 'MUCL3',
            'DUX2' => 'DUX4L8',
            'DYX1C1' => 'DNAAF4',
            'EFCAB4B' => 'CRACR2A',
            'EMR1' => 'ADGRE1',
            'EMR2' => 'ADGRE2',
            'ENTHD2' => 'TEPSIN',
            'EPT1' => 'SELENOI',
            'ERBB2IP' => 'ERBIN',
            'FAM101A' => 'RFLNA',
            'FAM103A1' => 'RAMAC',
            'FAM115C' => 'TCAF2',
            'FAM134B' => 'RETREG1',
            'FAM150B' => 'ALKAL2',
            'FAM154B' => 'SAXO2',
            'FAM159A' => 'SHISAL2A',
            'FAM175B' => 'ABRAXAS2',
            'FAM178A' => 'SLF2',
            'FAM179B' => 'TOGARAM1',
            'FAM188B' => 'MINDY4',
            'FAM194A' => 'ERICH6',
            'FAM211A' => 'LRRC75A',
            'FAM212B' => 'INKA2',
            'FAM21A' => 'WASHC2A',
            'FAM21B' => 'WASHC2A',
            'FAM35A' => 'SHLD2',
            'FAM46A' => 'TENT5A',
            'FAM5C' => 'BRINP3',
            'FAM60A' => 'SINHCAF',
            'FAM63A' => 'MINDY1',
            'FAM63B' => 'MINDY2',
            'FAM65A' => 'RIPOR1',
            'FAM65B' => 'RIPOR2',
            'FAM65C' => 'RIPOR3',
            'FAM86A' => 'EEF2KMT',
            'FAM96A' => 'CIAO2A',
            'FBXO18' => 'FBH1',
            'FTSJ2' => 'MRM2',
            'FYB' => 'FYB1',
            'GAREM' => 'GAREM1',
            'GAREML' => 'GAREM2',
            'GATS' => 'CASTOR3',
            'GBAS' => 'NIPSNAP2',
            'GCN1L1' => 'GCN1',
            'GLTSCR1L' => 'BICRAL',
            'GPER' => 'GPER1',
            'GPR112' => 'ADGRG4',
            'GPR116' => 'ADGRF5',
            'GPR123' => 'ADGRA1',
            'GPR124' => 'ADGRA2',
            'GPR125' => 'ADGRA3',
            'GPR128' => 'ADGRG7',
            'GPR133' => 'ADGRD1',
            'GPR144' => 'ADGRD2',
            'GPR56' => 'ADGRG1',
            'GPR64' => 'ADGRG2',
            'GPR97' => 'ADGRG3',
            'GPR98' => 'ADGRV1',
            'GRAMD3' => 'GRAMD2B',
            'GSG2' => 'HASPIN',
            'GUCY1B3' => 'GUCY1B1',
            'GYLTL1B' => 'LARGE2',
            'HDHD1' => 'PUDP',
            'HEATR2' => 'DNAAF5',
            'HEXDC' => 'HEXD',
            'HIATL1' => 'MFSD14B',
            'HMHA1' => 'ARHGAP45',
            'HMP19' => 'NSG2',
            'HN1' => 'JPT1',
            'IFLTD1' => 'LMNTD1',
            'IKBKAP' => 'ELP1',
            'INADL' => 'PATJ',
            'JHDM1D' => 'KDM7A',
            'KAL1' => 'ANOS1',
            'KIAA0101' => 'PCLAF',
            'KIAA0141' => 'DELE1',
            'KIAA0247' => 'SUSD6',
            'KIAA0368' => 'ECPAS',
            'KIAA0430' => 'MARF1',
            'KIAA0907' => 'KHDC4',
            'KIAA1009' => 'CEP162',
            'KIAA1024' => 'MINAR1',
            'KIAA1033' => 'WASHC4',
            'KIAA1161' => 'MYORG',
            'KIAA1239' => 'NWD2',
            'KIAA1244' => 'ARFGEF3',
            'KIAA1429' => 'VIRMA',
            'KIAA1430' => 'CFAP97',
            'KIAA1432' => 'RIC1',
            'KIAA1456' => 'TRMT9B',
            'KIAA1462' => 'JCAD',
            'KIAA1468' => 'RELCH',
            'KIAA1524' => 'CIP2A',
            'KIAA1598' => 'SHTN1',
            'KIAA1644' => 'SHISAL1',
            'KIAA1683' => 'IQCN',
            'KIAA1731' => 'CEP295',
            'KIAA1804' => 'MAP3K21',
            'KIAA1967' => 'CCAR2',
            'KIAA1984' => 'CCDC183',
            'KIAA2018' => 'USF3',
            'KIAA2022' => 'NEXMIF',
            'LACE1' => 'AFG1L',
            'LARGE' => 'LARGE1',
            'LCA10' => 'CEP290',
            'LEPREL1' => 'P3H2',
            'LINC01660' => 'FAM230J',
            'LINC01662' => 'FAM230E',
            'LPHN1' => 'ADGRL1',
            'LPHN3' => 'ADGRL3',
            'LPPR5' => 'PLPPR5',
            'LRRC16A' => 'CARMIL1',
            'LRRC16B' => 'CARMIL3',
            'LRRC48' => 'DRC3',
            'MEF2BNB' => 'BORCS8',
            'MEF2BNB-MEF2B' => 'BORCS8-MEF2B',
            'METTL10' => 'EEF1AKMT2',
            'METTL13' => 'EEF1AKNMT',
            'MFSD4' => 'MFSD4A',
            'MGEA5' => 'OGA',
            'MKI67IP' => 'NIFK',
            'MKL1' => 'MRTFA',
            'MRE11A' => 'MRE11',
            'MST4' => 'STK26',
            'MUT' => 'MMUT',
            'NAPRT1' => 'NAPRT',
            'NARG2' => 'ICE2',
            'NAT6' => 'NAA80',
            'NBPF16' => 'NBPF15',
            'NBPF24' => 'NBPF11',
            'NIM1' => 'NIM1K',
            'NOTCH2NL' => 'NOTCH2NLA',
            'NRD1' => 'NRDC',
            'NUPL1' => 'NUP58',
            'OBFC1' => 'STN1',
            'PAK7' => 'PAK5',
            'PAPD5' => 'TENT4B',
            'PARK2' => 'PRKN',
            'PCDP1' => 'CFAP221',
            'PCNXL2' => 'PCNX2',
            'PCNXL4' => 'PCNX4',
            'PDDC1' => 'GATD1',
            'PET112' => 'GATB',
            'PLK1S1' => 'KIZ',
            'PPAP2A' => 'PLPP1',
            'PPAP2B' => 'PLPP3',
            'PPAP2C' => 'PLPP2',
            'PPAPDC1A' => 'PLPP4',
            'PPAPDC3' => 'PLPP7',
            'PPP2R4' => 'PTPA',
            'PRAMEF33P' => 'PRAMEF33',
            'PRKCDBP' => 'CAVIN3',
            'PTPLAD1' => 'HACD3',
            'PTPLAD2' => 'HACD4',
            'PVRL2' => 'NECTIN2',
            'PVRL3' => 'NECTIN3',
            'PVRL4' => 'NECTIN4',
            'QTRTD1' => 'QTRT2',
            'REXO1L1' => 'REXO1L1P',
            'RFWD2' => 'COP1',
            'RGAG1' => 'RTL9',
            'RLTPR' => 'CARMIL2',
            'RNMTL1' => 'MRM3',
            'RQCD1' => 'CNOT9',
            'RTDR1' => 'RSPH14',
            'SDCCAG3' => 'ENTR1',
            'SELO' => 'SELENOO',
            'SEP15' => 'SELENOF',
            'SGK110' => 'SBK3',
            'SGK223' => 'PRAG1',
            'SLC22A20' => 'SLC22A20P',
            'SLMO1' => 'PRELID3A',
            'SOGA2' => 'MTCL1',
            'SQRDL' => 'SQOR',
            'TBC1D27' => 'TBC1D27P',
            'TBC1D29' => 'TBC1D29P',
            'TCEB1' => 'ELOC',
            'TCEB3B' => 'ELOA2',
            'TCEB3C' => 'ELOA3',
            'TCEB3CL' => 'ELOA3B',
            'TCEB3' => 'ELOA',
            'TENC1' => 'TNS2',
            'TEX40' => 'CATSPERZ',
            'TMEM110' => 'STIMATE',
            'TMEM180' => 'MFSD13A',
            'TMEM194B' => 'NEMP2',
            'TMEM2' => 'CEMIP2',
            'TMEM55A' => 'PIP4P2',
            'TMEM5' => 'RXYLT1',
            'TROVE2' => 'RO60',
            'TSSC1' => 'EIPR1',
            'TTC40' => 'CFAP46',
            'TTLL13' => 'TTLL13P',
            'UFD1L' => 'UFD1',
            'UPK3BL' => 'UPK3BL1',
            'VPRBP' => 'DCAF1',
            'VWA9' => 'INTS14',
            'WASH1' => 'WASHC1',
            'WBSCR17' => 'GALNT17',
            'WBSCR22' => 'BUD23',
            'WBSCR27' => 'METTL27',
            'WDR16' => 'CFAP52',
            'WDR52' => 'CFAP44',
            'WDR65' => 'CFAP57',
            'WDR96' => 'CFAP43',
            'WHSC1L1' => 'NSD3',
            'WHSC1' => 'NSD2',
            'WIBG' => 'PYM1',
            'WISP2' => 'CCN5',
            'ZASP' => 'LDB3',
            'ZCCHC11' => 'TUT4',
            'ZFP112' => 'ZNF112',
            'ZNF812' => 'ZNF812P',
        );
    }





    // FIXME: This function does not have a clearly matching name.
    function prepareMappings ()
    {
        // Returns an array that map VEP columns to LOVD columns.

        $aColumnMappings = array(
            'chromosome' => 'chromosome',
            'position' => 'position', // lovd_getVariantDescription() needs this.
            'QUAL' => 'VariantOnGenome/Sequencing/Quality',
            'FILTERvcf' => 'VariantOnGenome/Sequencing/Filter',
            'Consequence' => 'VariantOnTranscript/GVS/Function', // Will be translated.
            'GATKCaller' => 'VariantOnGenome/Sequencing/GATKcaller',
            'Feature' => 'transcriptid',
            'CDS_position' => 'VariantOnTranscript/Position',
            'HGVSc' => 'VariantOnTranscript/DNA',
            'HGVSp' => 'VariantOnTranscript/Protein',
            'Grantham' => 'VariantOnTranscript/Prediction/Grantham',
            'INDB_COUNT_UG' => 'VariantOnGenome/InhouseDB/Count/UG',
            'INDB_COUNT_HC' => 'VariantOnGenome/InhouseDB/Count/HC',
            'GLOBAL_VN' => 'VariantOnGenome/InhouseDB/Position/Global/Samples_w_coverage',
            'GLOBAL_VF_HET' => 'VariantOnGenome/InhouseDB/Count/Global/Heterozygotes',
            'GLOBAL_VF_HOM' => 'VariantOnGenome/InhouseDB/Count/Global/Homozygotes',
            'WITHIN_PANEL_VN' => 'VariantOnGenome/InhouseDB/Position/InPanel/Samples_w_coverage',
            'WITHIN_PANEL_VF_HET' => 'VariantOnGenome/InhouseDB/Count/InPanel/Heterozygotes',
            'WITHIN_PANEL_VF_HOM' => 'VariantOnGenome/InhouseDB/Count/InPanel/Homozygotes',
            'OUTSIDE_PANEL_VN' => 'VariantOnGenome/InhouseDB/Position/OutOfPanel/Samples_w_coverage',
            'OUTSIDE_PANEL_VF_HET' => 'VariantOnGenome/InhouseDB/Count/OutOfPanel/Heterozygotes',
            'OUTSIDE_PANEL_VF_HOM' => 'VariantOnGenome/InhouseDB/Count/OutOfPanel/Homozygotes',
            'AF1000G' => 'VariantOnGenome/Frequency/1000G',
            'rsID' => 'VariantOnGenome/dbSNP',
            'AFESP5400' => 'VariantOnGenome/Frequency/EVS', // Will be divided by 100 later.
            'CALC_GONL_AF' => 'VariantOnGenome/Frequency/GoNL',
            'AFGONL' => 'VariantOnGenome/Frequency/GoNL_old',
            'EXAC_AF' => 'VariantOnGenome/Frequency/ExAC',
            'MutationTaster_pred' => 'VariantOnTranscript/Prediction/MutationTaster',
            'MutationTaster_score' => 'VariantOnTranscript/Prediction/MutationTaster/Score',
            'Polyphen2_HDIV_score' => 'VariantOnTranscript/PolyPhen/HDIV',
            'Polyphen2_HVAR_score' => 'VariantOnTranscript/PolyPhen/HVAR',
            'SIFT_score' => 'VariantOnTranscript/Prediction/SIFT',
            'CADD_raw' => 'VariantOnGenome/CADD/Raw',
            'CADD_phred' => 'VariantOnGenome/CADD/Phred',
            'HGMD_association' => 'VariantOnGenome/HGMD/Association',
            'HGMD_reference' => 'VariantOnGenome/HGMD/Reference',
            'phyloP' => 'VariantOnGenome/Conservation_score/PhyloP',
            'scorePhastCons' => 'VariantOnGenome/Conservation_score/Phast',
            'GT' => 'allele',
            'GQ' => 'VariantOnGenome/Sequencing/GenoType/Quality',
            'DP' => 'VariantOnGenome/Sequencing/Depth/Total',
            'DPREF' => 'VariantOnGenome/Sequencing/Depth/Ref',
            'DPALT' => 'VariantOnGenome/Sequencing/Depth/Alt',
            'ALTPERC' => 'VariantOnGenome/Sequencing/Depth/Alt/Fraction', // Will be divided by 100 later.
            'GT_Father' => 'VariantOnGenome/Sequencing/Father/GenoType',
            'GQ_Father' => 'VariantOnGenome/Sequencing/Father/GenoType/Quality',
            'DP_Father' => 'VariantOnGenome/Sequencing/Father/Depth/Total',
            'ALTPERC_Father' => 'VariantOnGenome/Sequencing/Father/Depth/Alt/Fraction', // Will be divided by 100 later.
            'ISPRESENT_Father' => 'VariantOnGenome/Sequencing/Father/VarPresent',
            'GT_Mother' => 'VariantOnGenome/Sequencing/Mother/GenoType',
            'GQ_Mother' => 'VariantOnGenome/Sequencing/Mother/GenoType/Quality',
            'DP_Mother' => 'VariantOnGenome/Sequencing/Mother/Depth/Total',
            'ALTPERC_Mother' => 'VariantOnGenome/Sequencing/Mother/Depth/Alt/Fraction', // Will be divided by 100 later.
            'ISPRESENT_Mother' => 'VariantOnGenome/Sequencing/Mother/VarPresent',

            // Mappings for fields used to process other fields but not imported into the database.
            'SYMBOL' => 'symbol',
            'HGNC_ID' => 'id_hgnc',
            'REF' => 'ref',
            'ALT' => 'alt',
            'Existing_variation' => 'existing_variation'
        );

        return $aColumnMappings;
    }
}
