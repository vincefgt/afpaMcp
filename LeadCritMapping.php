<?php

/**
 * Mapping class for the financial_lead_critere table.
 * Generates column whitelists and handles table validation.
 */
class LeadCritMapping {
    const VERSION = "1.0.0";
        /** Table name */
    const TABLE_CF = 'financial_lead_critere';
    const TABLE_SUPPORT = 'financial_support';

    const COLUMN = array(
    // propriété PHP      → colonne BDD
    /** Column names */
    'ID'                     => 'id', // autoincrement data
    'ID_SUPPORT'             => 'id_support',  // FK id_support

    /* ---- joinable table */
    'NOM_SOURCE'             => 'libelle', //jointTable financial_support.libelle
    'SRC'                    => 'src',     // jointTable financial_support.src
    'TYPE_PUB'               => 'type_pub',  // jointTable financial_support.tupe_pub
    'ACTIF_SUPPORT'          => 'actif', // jointTable financial_support.actif
    /* -----------------*/

    'ACTIF_CRITERE'          => 'actif_crit',
    'RECETTE'                => 'recette',
    'LEAD_HS'                => 'lead_HS', //(bool)
    'YOUNITED_REFUS_RAC'     => 'younitedRefusRac', //(bool)

    //pro
    'AGE_MIN_PRO'            => 'ageMinPro', //(int)
    'AGE_MAX_PRO'            => 'ageMaxPro', //(int)
    'CDI'                    => 'only_cdi', //(bool)
    'PRO'                    => 'proprietaire', //(bool)
    'NB_MIN_PRET_PRO'        => 'nb_pret_min_pro',  //(int)
    'NB_MIN_PRET_IMMO_PRO'   => 'nb_pret_min_immo_pro', //(int)
    'NB_MIN_PRET_CONSO_PRO'  => 'nb_pret_min_conso_pro', //(int)
    'CRD_MIN_PRO'            => 'crd_min_pro', //(int)
    'CRD_MAX_PRO'            => 'crd_max_pro', //(int)
    'CRD_MIN_IMMO_PRO'       => 'min_crd_immo_pro', //(int)
    'CRD_MAX_IMMO_PRO'       => 'max_crd_immo_pro', //(int)
    'CRD_MIN_CONSO_PRO'      => 'min_crd_conso_pro', //(int)
    'CRD_MAX_CONSO_PRO'      => 'max_crd_conso_pro', //(int)
    'REV_MIN_PRO'            => 'rev_min_pro', //(int)
    'REV_MIN_FOYER_PRO'      => 'revenuFoyerMinPro', //(int)
    'MAF_MIN_PRO'            => 'min_maf_pro', //(int)
    'MAF_MAX_PRO'            => 'max_maf_pro', //(int)
    'TX_ENDETTEMENT_MIN_PRO' => 'taux_endettement_min_pro', //(int)
    'TX_ENDETTEMENT_MAX_PRO' => 'taux_endettement_max_pro', //(int)
    'NEW_REV_PRO'            => 'newRevenuPro', //(bool)

    //loc
    'AGE_MIN_LOC'            => 'ageMinLoc', //(int)
    'AGE_MAX_LOC'            => 'ageMaxLoc', //(int)
    'LOC'                    => 'locataire', //(bool)
    'NB_MIN_PRET_LOC'        => 'nb_pret_min_loc',  //(int)
    'NB_MIN_PRET_CONSO_LOC'  => 'nb_pret_min_conso_loc', //(int)
    'NB_MIN_PRET_IMMO_LOC'   => 'nb_pret_min_immo_loc', //(int)
    'CRD_MIN_LOC'            => 'crd_min_loc', //(int)
    'CRD_MAX_LOC'            => 'crd_max_loc', //(int)
    'CRD_MIN_IMMO_LOC'       => 'min_crd_immo_loc', //(int)
    'CRD_MAX_IMMO_LOC'       => 'max_crd_immo_loc', //(int)
    'CRD_MIN_CONSO_LOC'      => 'min_crd_conso_loc', //(int)
    'CRD_MAX_CONSO_LOC'      => 'max_crd_conso_loc', //(int)
    'REV_MIN_LOC'            => 'rev_min_loc', //(int)
    'REV_MIN_FOYER_LOC'      => 'revenuFoyerMinLoc', //(int)
    'MAF_MIN_LOC'            => 'min_maf_loc', //(int)
    'MAF_MAX_LOC'            => 'max_maf_loc', //(int)
    'NEW_REV_LOC'            => 'newRevenuLoc', //(int)
    'TX_ENDETTEMENT_MIN_LOC' => 'taux_endettement_min_loc', //(int)
    'TX_ENDETTEMENT_MAX_LOC' => 'taux_endettement_max_loc', //(int)

    // 'ONLY_CRD_CONSO'         => 'only_crd_conso', //(bool)
    // 'ONLY_PRET_CONSO'        => 'only_pret_conso', //(bool)
    'DOUBLON_DUREE'          => 'doublon_duree', //(int)
    'INCLUS_TRESO'           => 'inclus_treso', //(bool)
    'TRESO_MIN'              => 'treso_min', //(int)
    'HEBERGEMENT_FREE'       => 'allowFreeRenter', //(bool)
    'MUT'                    => 'mutualisation',
    'REFUS_COORD'            => 'refus_fausses_coordonnees',
    'PROFESSION'             => 'profession',
    'HISTO_MUT'              => 'historique_mutualisation',
    'IS_DOMTOM'              => 'is_domtom', //(bool) 
    'COMMENTS'               => 'commentaires',
    'NB_MIN_PRET_CONSO'      => 'nb_pret_min_conso', //(int)
    'NB_MIN_PRET_IMMO'       => 'nb_pret_min_immo', //(int)
    'PROF_LIB_MIN_DUREE'     => 'prof_lib_min_duree', //(int)
    'ANON'                   => 'anonymisation' //(bool)

    );

    /**
     * auto generation property for class critere
     */
    // public static function generateProperties(array $mapping){
    //     $code = '';

    //     foreach ($mapping as $property => $column) {
    //         $camel = lcfirst(
    //             str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($property))))
    //         );

    //         $code .= "public \$$camel;\n";
    //     }
    //     return $code;
    // }

    }