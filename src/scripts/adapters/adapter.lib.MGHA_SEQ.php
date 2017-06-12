<?php
$_INSTANCE_CONFIG['viewlists']['Screenings_for_I_VE']['cols_to_show'] = array(
    // Invisible.
    'individualid',

    // Visible.
    'id',
    'Screening/Tumor/Sample_ID',
    'Screening/Normal/Sample_ID',
    'Screening/Pipeline/Path',
    'variants_found_',
    'analysis_status'
);

$_INSTANCE_CONFIG['viewlists']['CustomVL_AnalysisRunResults_for_I_VE']['cols_to_show'] = array(
    // Invisible.
    'runid',
    'curation_statusid',
    'variantid',


    // Visible.
    'curation_status_',
    'VariantOnGenome/Sequencing/Somatic/Status',
    'chromosome',
    'symbol',
    'preferred_transcripts',
    'VariantOnGenome/DNA',
    'VariantOnTranscript/DNA',
    'VariantOnTranscript/Protein',
    'VariantOnGenome/Consequence',
    'VariantOnGenome/DbSNP_IDs',
    'obs_variant',
    'obs_var_ind_ratio',
    'VariantOnGenome/Frequency/1000G/VEP',
    'VariantOnGenome/Frequency/EVS/VEP/European_American',
    'VariantOnGenome/COSMIC_IDs',
    'VariantOnGenome/Sequencing/Tumour/Genotype/Quality',
    'VariantOnGenome/Sequencing/Normal/BI/Depth/Total',
    'VariantOnGenome/Sequencing/Tumour/BI/Depth/Total',
    'VariantOnGenome/Sequencing/Normal/BI/Allele/Frequency',
    'VariantOnGenome/Sequencing/Tumour/BI/Allele/Frequency',
    'VariantOnGenome/Sequencing/Normal/BI/Depth/Ref',
    'VariantOnGenome/Sequencing/Tumour/BI/Depth/Ref',
    'VariantOnGenome/Sequencing/Normal/BI/Depth/Alt',
    'VariantOnGenome/Sequencing/Tumour/BI/Depth/Alt',
    'VariantOnGenome/Sequencing/Somatic/Score',
    'VariantOnGenome/Sequencing/Fisher/Germline',
    'VariantOnGenome/Sequencing/Fisher/Somatic',
    'VariantOnGenome/Sequencing/Quality',
    'VariantOnGenome/Sequencing/Normal/Indel/Reads',
    'VariantOnGenome/Sequencing/Normal/Total_Coverage',
    'VariantOnGenome/Sequencing/Normal/Indel/Mismatches/Average',
    'VariantOnGenome/Sequencing/Normal/Indel/Mapping_Quality',
    'VariantOnGenome/Sequencing/Tumour/Indel/Reads',
    'VariantOnGenome/Sequencing/Tumour/Total_Coverage',
    'VariantOnGenome/Sequencing/Tumour/Indel/Mismatches/Average',
    'VariantOnGenome/Sequencing/Tumour/Indel/Mapping_Quality',
    'VariantOnTranscript/SIFT',
    'VariantOnTranscript/PolyPhen',
    'VariantOnGenome/Sequencing/Normal/Genotype/Quality',
    'vog_effect',
);

$_INSTANCE_CONFIG['attachments'] = array(
        'igv' => array(
            'linked_to' => 'variant',
            'label' => 'IGV screenshot'),
        'ucsc' => array(
            'linked_to' => 'summary_annotation',  // This file is stored using the Summary Annotation Record DBID.
            'label' => 'UCSC screenshot (Summary Annotation)'),
        'confirmation' => array(
            'linked_to' => 'variant',
            'label' => 'Confirmation screenshot'),
        'workfile' => array(
            'linked_to' => 'variant',
            'label' => 'Excel file')
);


$_INSTANCE_CONFIG['conversion'] = array(
    'max_annotation_error_allowed' => 20,
    'exit_on_annotation_error' => false,
    'enforce_hgnc_gene' => false,
    'check_indel_description' => false
);


class LOVD_MghaSeqDataConverter extends LOVD_DefaultDataConverter {

    static $sAdapterName = 'MGHA_SEQ';

    function formatEmptyColumn ($aLine, $sVEPColumn, $sLOVDColumn, $aVariant)
    {
        // Returns how we want to represent empty data in $aVariant array given a LOVD column name.
        if (isset($aLine[$sVEPColumn]) && ($aLine[$sVEPColumn] === 0 || $aLine[$sVEPColumn] === '0')) {
            $aVariant[$sLOVDColumn] = 0;
        } else {
            $aVariant[$sLOVDColumn] = '';
        }

        return $aVariant;
    }





    function prepareMappings()
    {
        // Returns an array that map VEP columns to LOVD columns.

        $aColumnMappings = array(
            'CHROM' => 'chromosome',
            'POS' => 'position',
            'ID' => 'VariantOnGenome/dbSNP',
            'REF' => 'ref',
            'ALT' => 'alt',
            'QUAL' => 'VariantOnGenome/Sequencing/Quality',
            'SYMBOL' => 'symbol',

            'Feature' => 'transcriptid',
            'HGVSc' => 'VariantOnTranscript/DNA',
            'HGVSp' => 'VariantOnTranscript/Protein',

            // Re-use from MGHA
            'FILTER' => 'VariantOnGenome/Sequencing/Filter',
            'vcf_AC' => 'VariantOnGenome/Sequencing/Allele/Count',
            'vcf_AF' => 'VariantOnGenome/Sequencing/Allele/Frequency',
            'vcf_AN' => 'VariantOnGenome/Sequencing/Allele/Total',
            'vcf_BaseQRankSum' => 'VariantOnGenome/Sequencing/Base_Qualities_Score',

            'vcf_ClippingRankSum' => 'VariantOnGenome/Sequencing/Num_Hard_Clipped_Bases',
            'vcf_ExcessHet' => 'VariantOnGenome/Sequencing/Excess_Heterozygosity',
            'vcf_SOR' => 'VariantOnGenome/Sequencing/Symmetric_Odds_Ratio',

            'vcf_DP' => 'VariantOnGenome/Sequencing/Depth/Unfiltered_All',
            'vcf_FS' => 'VariantOnGenome/Sequencing/Fisher_Strand_Bias',
            'vcf_MLEAC' => 'VariantOnGenome/Sequencing/Max_Likelihood_Exp_Allele_Count',
            'vcf_MLEAF' => 'VariantOnGenome/Sequencing/Max_Likelihood_Exp_Allele_Freq',
            'vcf_MQ' => 'VariantOnGenome/Sequencing/Mapping_Quality',
            'vcf_MQRankSum' => 'VariantOnGenome/Sequencing/Mapping_Quality_Score',
            'vcf_QD' => 'VariantOnGenome/Sequencing/Quality_by_depth',
            'vcf_ReadPosRankSum' => 'VariantOnGenome/Sequencing/Read_Position_Bias_Score',
            'GMAF' => 'VariantOnGenome/Frequency/1000G/VEP',
            'EUR_MAF' => 'VariantOnGenome/Frequency/1000G/VEP/European',
            'AFR_MAF' => 'VariantOnGenome/Frequency/1000G/VEP/African',
            'AMR_MAF' => 'VariantOnGenome/Frequency/1000G/VEP/American',
            'AA_MAF' => 'VariantOnGenome/Frequency/EVS/VEP/African_American',
            'EA_MAF' => 'VariantOnGenome/Frequency/EVS/VEP/European_American',

            'CANONICAL' => 'VariantOnTranscript/Canonical_Transcript',
            'ENSP' => 'VariantOnTranscript/Embsembl_Protein_Identifier',
            'EXON' => 'VariantOnTranscript/Exon',
            'INTRON' => 'VariantOnTranscript/Intron',
            'cDNA_position' => 'VariantOnTranscript/cDNA_Position',
            'CDS_position' => 'VariantOnTranscript/Position',
            'Protein_position' => 'VariantOnTranscript/Protein_Position',
            'Amino_acids' => 'VariantOnTranscript/Amino_Acids',
            'Codons' => 'VariantOnTranscript/Alternative_Codons',
            'PUBMED' => 'VariantOnTranscript/Pubmed',
            'BIOTYPE' => 'VariantOnTranscript/Biotype',
            'CLIN_SIG' => 'VariantOnTranscript/Clinical_Significance',
            'vcf_SOMATIC' => 'VariantOnTranscript/Somatic_Status',
            'STRAND' => 'VariantOnTranscript/DNA_Strand',
            'Feature_type' => 'VariantOnTranscript/Feature_Type',


            // Normal
            'normal:DP' => 'VariantOnGenome/Sequencing/Normal/Depth/Total',
            'normal:GT' => 'VariantOnGenome/Sequencing/Normal/GenoType',
            'normal:PL' => 'VariantOnGenome/Sequencing/Normal/Phredscaled_Likelihoods',
            'normal:PMCAD' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Alt',
            'normal:PMCADF' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Alt/Forward',
            'normal:PMCADR' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Alt/Reverse',
            'normal:PMCBDIR' => 'VariantOnGenome/Sequencing/Normal/BI/Bidirectional',
            'normal:PMCDP' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Total',
            'normal:PMCFREQ' => 'VariantOnGenome/Sequencing/Normal/BI/Allele/Frequency',
            'normal:PMCRD' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Ref',
            'normal:PMCRDF' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Ref/Forward',
            'normal:PMCRDR' => 'VariantOnGenome/Sequencing/Normal/BI/Depth/Ref/Reverse',
            'Consequence' => 'VariantOnGenome/Consequence',
            'Gene' => 'VariantOnGenome/Gene_ID',
            'DISTANCE' => 'VariantOnTranscript/Distance',
            'dbSNP_ids' => 'VariantOnGenome/DbSNP_IDs',
            'COSMIC_ids' => 'VariantOnGenome/COSMIC_IDs',
            'PolyPhen' => 'VariantOnTranscript/PolyPhen',
            'SIFT' => 'VariantOnTranscript/SIFT',
            'ASN_MAF' => 'VariantOnGenome/Frequency/1000G/VEP/Asian',
            'MOTIF_SCORE_CHANGE' => 'VariantOnTranscript/TFBP/Motif_Score_Change',

            // Tumour
            'tumour:DP' => 'VariantOnGenome/Sequencing/Tumour/Depth/Total',
            'tumour:GT' => 'VariantOnGenome/Sequencing/Tumour/GenoType',
            'tumour:PL' => 'VariantOnGenome/Sequencing/Tumour/Phredscaled_Likelihoods',
            'tumour:PMCAD' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Alt',
            'tumour:PMCADF' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Alt/Forward',
            'tumour:PMCADR' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Alt/Reverse',
            'tumour:PMCBDIR' => 'VariantOnGenome/Sequencing/Tumour/BI/Bidirectional',
            'tumour:PMCDP' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Total',
            'tumour:PMCFREQ' => 'VariantOnGenome/Sequencing/Tumour/BI/Allele/Frequency',
            'tumour:PMCRD' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Ref',
            'tumour:PMCRDF' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Ref/Forward',
            'tumour:PMCRDR' => 'VariantOnGenome/Sequencing/Tumour/BI/Depth/Ref/Reverse',

            // Tummour Normal Combined
            'vcf_GPV' => 'VariantOnGenome/Sequencing/Fisher/Germline',
            'vcf_Identified' => 'VariantOnGenome/Sequencing/VCF_Source',

            'vcf_LSEQ' => 'VariantOnGenome/Sequencing/5_Prime_Flanking_Seq',
            'vcf_MSI' => 'VariantOnGenome/Sequencing/MicroSattelite',
            'vcf_MSILEN' => 'VariantOnGenome/Sequencing/MSI_Length',
            'vcf_RSEQ' => 'VariantOnGenome/Sequencing/3_Prime_Flanking_Seq',
            'vcf_SAMPLE' => 'VariantOnGenome/Sequencing/Sample_Name',
            'vcf_SHIFT3' => 'VariantOnGenome/Sequencing/3_Prime_Shift',

            'vcf_SL_N_DP_VARDICT' => 'VariantOnGenome/Sequencing/Normal/Seqliner/Coverage/Vardict',
            'vcf_SL_T_DP_VARDICT' => 'VariantOnGenome/Sequencing/Tumour/Seqliner/Coverage/Vardict',

            'vcf_SSF' => 'VariantOnGenome/Sequencing/P_Value',
            'vcf_STATUS' => 'VariantOnGenome/Sequencing/Status',
            'vcf_TYPE' => 'VariantOnGenome/Sequencing/Type',

            'normal:ADJAF' => 'VariantOnGenome/Sequencing/Normal/Allele/Alt/Frequency/Adjusted',
            'normal:AF' => 'VariantOnGenome/Sequencing/Normal/Allele/Alt/Frequency',
           'normal:BIAS' => 'VariantOnGenome/Sequencing/Normal/Bias',

            'normal:HIAF' => 'VariantOnGenome/Sequencing/Normal/Allele/Frequency/High_Quality',
            'normal:MQ' => 'VariantOnGenome/Sequencing/Normal/Mean_Mapping_Quality',
            'normal:NM' => 'VariantOnGenome/Sequencing/Normal/Mean_Mismatches',
            'normal:ODDRATIO' => 'VariantOnGenome/Sequencing/Normal/Oddratio',

            'normal:PMEAN' => 'VariantOnGenome/Sequencing/Normal/Mean_Position',
            'normal:PSTD' => 'VariantOnGenome/Sequencing/Normal/Position_STD',
            'normal:QSTD' => 'VariantOnGenome/Sequencing/Normal/Quality_Score_STD',
            'normal:QUAL' => 'VariantOnGenome/Sequencing/Normal/Mean_Quality',

            'normal:SBF' => 'VariantOnGenome/Sequencing/Normal/Strand_Bias_Fisher_P_Value',
            'normal:SN' => 'VariantOnGenome/Sequencing/Normal/Signal_To_Noise',
            'normal:VD' => 'VariantOnGenome/Sequencing/Normal/Variant_Depth',

            'tumour:ADJAF' => 'VariantOnGenome/Sequencing/Tumour/Allele/Alt/Frequency/Adjusted',
            'tumour:AF' => 'VariantOnGenome/Sequencing/Tumour/Allele/Alt/Frequency',
            'tumour:BIAS' => 'VariantOnGenome/Sequencing/Tumour/Bias',

            'tumour:HIAF' => 'VariantOnGenome/Sequencing/Tumour/Allele/Frequency/High_Quality',
            'tumour:MQ' => 'VariantOnGenome/Sequencing/Tumour/Mean_Mapping_Quality',
            'tumour:NM' => 'VariantOnGenome/Sequencing/Tumour/Mean_Mismatches',
            'tumour:ODDRATIO' => 'VariantOnGenome/Sequencing/Tumour/Oddratio',

            'tumour:PMEAN' => 'VariantOnGenome/Sequencing/Tumour/Mean_Position',
            'tumour:PSTD' => 'VariantOnGenome/Sequencing/Tumour/Position_STD',
            'tumour:QSTD' => 'VariantOnGenome/Sequencing/Tumour/Quality_Score_STD',
            'tumour:QUAL' => 'VariantOnGenome/Sequencing/Tumour/Mean_Quality',

            'tumour:SBF' => 'VariantOnGenome/Sequencing/Tumour/Strand_Bias_Fisher_P_Value',
            'tumour:SN' => 'VariantOnGenome/Sequencing/Tumour/Signal_To_Noise',
            'tumour:VD' => 'VariantOnGenome/Sequencing/Tumour/Variant_Depth',

            'vcf_SPV' => 'VariantOnGenome/Sequencing/Fisher/Somatic',
            'vcf_SS' => 'VariantOnGenome/Sequencing/Somatic/Status',
            'vcf_SSC' => 'VariantOnGenome/Sequencing/Somatic/Score',
            'normal:DP4' => 'VariantOnGenome/Sequencing/Normal/Strand/Count',
            'normal:FREQ' => 'VariantOnGenome/Sequencing/Normal/Allele/Frequency',
            'normal:RD' => 'VariantOnGenome/Sequencing/Normal/Depth/Ref',
            'tumour:DP4' => 'VariantOnGenome/Sequencing/Tumour/Strand/Count',
            'tumour:FREQ' => 'VariantOnGenome/Sequencing/Tumour/Allele/Frequency',
            'tumour:RD' => 'VariantOnGenome/Sequencing/Tumour/Depth/Ref',

            // Columns we add.
            'allele' => 'allele',

            'Normal_Depth_Ref' => 'VariantOnGenome/Sequencing/Normal/Allele/Depth/Ref', // Derived from Normal:AD.
            'Normal_Depth_Alt' => 'VariantOnGenome/Sequencing/Normal/Allele/Depth/Alt', // Derived from Normal:AD.

            'Tumour_Depth_Ref' => 'VariantOnGenome/Sequencing/Tumour/Allele/Depth/Ref', // Derived from Tumour:AD.
            'Tumour_Depth_Alt' => 'VariantOnGenome/Sequencing/Tumour/Allele/Depth/Alt', // Derived from Tumour:AD.

            'Normal_Seqliner_Varscan_Depth_Ref' => 'VariantOnGenome/Sequencing/Normal/Seqliner/Varscan/Depth/Ref', // Derived from vcf_SL_N_AD_VARSCAN.
            'Normal_Seqliner_Varscan_Depth_Alt' => 'VariantOnGenome/Sequencing/Normal/Seqliner/Varscan/Depth/Alt', // Derived from vcf_SL_N_AD_VARSCAN.

            'Normal_Seqliner_Vardict_Depth_Ref' => 'VariantOnGenome/Sequencing/Normal/Seqliner/Vardict/Depth/Ref', // Derived from vcf_SL_N_AD_VARDICT.
            'Normal_Seqliner_Vardict_Depth_Alt' => 'VariantOnGenome/Sequencing/Normal/Seqliner/Vardict/Depth/Alt', // Derived from vcf_SL_N_AD_VARDICT.

            'Tumour_Seqliner_Varscan_Depth_Ref' => 'VariantOnGenome/Sequencing/Tumour/Seqliner/Varscan/Depth/Ref', // Derived from vcf_SL_T_AD_VARSCAN.
            'Tumour_Seqliner_Varscan_Depth_Alt' => 'VariantOnGenome/Sequencing/Tumour/Seqliner/Varscan/Depth/Alt', // Derived from vcf_SL_T_AD_VARSCAN.

            'Tumour_Seqliner_Vardict_Depth_Ref' => 'VariantOnGenome/Sequencing/Tumour/Seqliner/Vardict/Depth/Ref', // Derived from vcf_SL_T_AD_VARDICT.
            'Tumour_Seqliner_Vardict_Depth_Alt' => 'VariantOnGenome/Sequencing/Tumour/Seqliner/Vardict/Depth/Alt', // Derived from vcf_SL_T_AD_VARDICT.

        );

        return $aColumnMappings;
    }





    function prepareVariantData(&$aLine)
    {
        // Reformat a line of raw variant data into the format that works for this instance.

        global $_LINE_AGGREGATED;
        $_LINE_AGGREGATED = array();

        $aLine = $this->prepareFrequencyColumns($aLine);
        $aLine = $this->prepareAlleleDepthColumns($aLine);

        return $aLine;
    }





    function prepareFrequencyColumns($aLine)
    {
        // Reformat frequency datas and map them into new columns that were not in the input file.

        global $_LINE_AGGREGATED;

        // FREQUENCIES
        // Make all bases uppercase.
        $sRef = strtoupper($aLine['REF']);
        $sAlt = strtoupper($aLine['ALT']);

        // 'Eat' letters from either end - first left, then right - to isolate the difference.
        while (strlen($sRef) > 0 && strlen($sAlt) > 0 && $sRef{0} == $sAlt{0}) {
            $sRef = substr($sRef, 1);
            $sAlt = substr($sAlt, 1);
        }

        while (strlen($sRef) > 0 && strlen($sAlt) > 0 && $sRef[strlen($sRef) - 1] == $sAlt[strlen($sAlt) - 1]) {
            $sRef = substr($sRef, 0, -1);
            $sAlt = substr($sAlt, 0, -1);
        }

        // Insertions/duplications, deletions, inversions, indels.
        // We do not want to display the frequencies for these, set frequency columns to empty.
        if (strlen($sRef) != 1 || strlen($sAlt) != 1) {
            $sAlt = '';
        }

        // Set frequency columns array, this is using the column names from the file before they are mapped to LOVD columns names.
        $aFreqColumns = array(
            'GMAF',
            'AFR_MAF',
            'AMR_MAF',
            'EUR_MAF',
            'EA_MAF',
            'AA_MAF',
            'ASN_MAF'

        );

        // Array of frequency columns used for variant priority calculation. The maximum frequency of all these columns is used.
        $aFreqCalcColumns = array(
            'GMAF',
            'EA_MAF',
            'ExAC_MAF'
        );

        $aFreqCalcValues = array();

        foreach($aFreqColumns as $sFreqColumn) {

            if ($aLine[$sFreqColumn] == 'unknown' || $aLine[$sFreqColumn] == '' || $sAlt == '' || empty($sAlt) || strlen($sAlt) == 0) {
                $aLine[$sFreqColumn] = '';

            } else {
                $aFreqArr = explode("&", $aLine[$sFreqColumn]);
                $aFreqValArray = array();


                foreach ($aFreqArr as $freqData) {

                    if (preg_match('/^(\D+)\:(.+)$/', $freqData, $freqCalls)) {
                        $sFreqPrefix = $freqCalls[1];

                        if ($sFreqPrefix == $sAlt && is_numeric($freqCalls[2])){
                            array_push($aFreqValArray, $freqCalls[2]);
                        }

                    }
                }
                // Check there are values in the array before taking max.
                $sFreqCheck = array_filter($aFreqValArray);

                if (!empty($sFreqCheck)){
                    $aLine[$sFreqColumn] = max($aFreqValArray);
                } else {
                    $aLine[$sFreqColumn] = '';
                }
            }

            // If column is required for calculating variant priority then add to array.
            if(in_array($sFreqColumn,$aFreqCalcColumns)){
                array_push($aFreqCalcValues,$aLine[$sFreqColumn]);
            }
        }



        // Get maximum frequency.
        $sMaxFreq = max($aFreqCalcValues);

        $_LINE_AGGREGATED['MaxFreq'] = $sMaxFreq;

        return $aLine;
    }





    function prepareAlleleDepthColumns($aLine)
    {
        // Reformat columns related to allele depth and map them into new columns that were not in the input file.

        $aADColumns = array(
            'normal:AD' => 'Normal_Depth_',
            'tumour:AD' => 'Tumour_Depth_',

            'vcf_SL_N_AD_VARSCAN' => 'Normal_Seqliner_Varscan_Depth_',
            'vcf_SL_N_AD_VARDICT' => 'Normal_Seqliner_Vardict_Depth_',

            'vcf_SL_T_AD_VARSCAN' => 'Tumour_Seqliner_Varscan_Depth_',
            'vcf_SL_T_AD_VARDICT' => 'Tumour_Seqliner_Vardict_Depth_',
        );

        foreach ($aADColumns as $sVepCol => $sNewCol) {
            if (!isset($aLine[$sVepCol])) {
                continue;
            }

            $aValues = explode(',', $aLine[$sVepCol]);
            if (count($aValues) < 2) {
                continue;
            }

            list($sRef, $sAlt) = $aValues;
            $aLine[$sNewCol . 'Ref'] = $sRef;
            $aLine[$sNewCol . 'Alt'] = $sAlt;
        }

        return $aLine;
    }





    function prepareGeneAliases()
    {
        // Prepare the $aGeneAliases array with a site specific gene alias list.
        // The convert and merge script will provide suggested gene alias key value pairs to add to this array.
        $aGeneAliases = array();
        return $aGeneAliases;
    }





    function prepareGenesToIgnore()
    {
        // Prepare the $aGenesToIgnore array with a site specific gene list.
        $aGenesToIgnore = array();
        return $aGenesToIgnore;
    }





    function ignoreTranscript($sTranscriptId)
    {
        // Check if we want to skip importing the annotation for this transcript.

        if (parent::ignoreTranscript($sTranscriptId)) {
            return true;
        }

        // If transcript is NOT ignored by parent adapter, then we check further here.
        // Prepare the transcript ID prefixes that we want to ignore.
        $aTranscriptsPrefixToIgnore = array(
            'NR_'
        );

        foreach ($aTranscriptsPrefixToIgnore as $sPrefix) {
            if (strpos($sTranscriptId, $sPrefix) === 0) {
                return true;
            }
        }

        return false;
    }





    function prepareScreeningID($aMetaData)
    {
        // Returns the screening ID.

        return 1;
    }





    function getInputFilePrefixPattern()
    {
        // Returns the regex pattern of the prefix of variant input file names.

        return '.+';
    }





    function getRequiredHeaderColumns()
    {
        // Returns an array of required input variant file column headers. The order of these columns does NOT matter.

        return array(
            'CHROM',
            'POS',
            'ID',
            'REF',
            'ALT',
            'QUAL',
            'FILTER'
        );
    }
}