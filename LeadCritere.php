<?php

use function PHPSTORM_META\type;

require_once ($_SERVER["CORE_PATH"] . 'Passerelle/DAO/LeadCritereDAO.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Mapping/LeadCritMapping.php');

class LeadCritere{
    const VERSION = "1.0.0";

    public $id;
    public $idSupport;

    public $libelle;
    public $src;
    public $typePub;
    public $actifCritere;
    public $recette;
    public $doublonDuree;
    public $leadHs;
    public $younitedRefusRac;
    public $cdi;
    public $actifSupport;

    public $pro;
    public $ageMinPro;
    public $ageMaxPro;
    public $nbMinPretPro;
    public $nbMinPretConsoPro;
    public $nbMinPretImmoPro;
    public $crdMinPro;
    public $crdMaxPro;
    public $crdMinImmoPro;
    public $crdMaxImmoPro;
    public $crdMinImmoLoc;
    public $crdMaxImmoLoc;
    public $crdMinConsoPro;
    public $crdMaxConsoPro;
    public $revMinPro;
    public $revMinFoyerPro;
    public $mafMinPro;
    public $mafMaxPro;
    public $txEndettementMinPro;
    public $txEndettementMaxPro;
    public $newRevPro;

    public $loc;
    public $ageMinLoc;
    public $ageMaxLoc;
    public $nbMinPretLoc;
    public $nbMinPretConsoLoc;
    public $nbMinPretImmoLoc;
    public $crdMinLoc;
    public $crdMaxLoc;
    public $crdMinConsoLoc;
    public $crdMaxConsoLoc;
    public $revMinLoc;
    public $revMinFoyerLoc;
    public $mafMinLoc;
    public $mafMaxLoc;
    public $newRevLoc;
    public $txEndettementMinLoc;
    public $txEndettementMaxLoc;
    public $hebergementFree;

    // public $onlyCrdConso;
    // public $onlyPretConso;
    public $inclusTreso;
    public $tresoMin;
    public $mut;
    public $refusCoord;
    public $profession;
    public $histoMut;
    public $isDomtom;
    public $comments;
    public $nbMinPretConso;
    public $nbMinPretImmo;
    public $profLibMinDuree;
    public $anon;
  
    private $types = array(
        'id'                 => 'int',
        'idSupport'          => 'int',

        'nomSource'          => 'string',
        'src'                => 'string',
        'typePub'            => 'string',
        'libelle'            => 'string',
        'actifSupport'       => 'bool',

        'actifCritere'       => 'bool',
        'recette'            => 'bool',
        'doublonDuree'       => 'int',
        'leadHs'             => 'bool',
        'younitedRefusRac'   => 'bool',
        'cdi'                => 'bool',
        

        'pro'                => 'bool',
        'ageMinPro'             => 'int',
        'ageMaxPro'             => 'int',
        'nbMinPretPro'       => 'int',
        'nbMinPretConsoPro'  => 'int',
        'nbMinPretImmoPro'   => 'int',
        'crdMinPro'          => 'int',
        'crdMaxPro'          => 'int',
        'crdMinImmoPro'      => 'int',
        'crdMaxImmoPro'      => 'int',
        'crdMinConsoPro'     => 'int',
        'crdMaxConsoPro'     => 'int',
        'revMinPro'          => 'int',
        'newRevPro'          => 'bool',   
        'revMinFoyerPro'     => 'int',
        'mafMinPro'          => 'int',
        'mafMaxPro'          => 'int',
        'txEndettementMinPro'   => 'float',
        'txEndettementMaxPro'   => 'float',

        'loc'                => 'bool',
        'ageMinLoc'          => 'int',
        'ageMaxLoc'          => 'int',
        'nbMinPretLoc'       => 'int',
        'nbMinPretConsoLoc'  => 'int',
        'nbMinPretImmoLoc'   => 'int',
        'crdMinLoc'          => 'int',
        'crdMaxLoc'          => 'int',
        'crdMinImmoLoc'      => 'int',
        'crdMaxImmoLoc'      => 'int',
        'crdMinConsoLoc'     => 'int',
        'crdMaxConsoLoc'     => 'int',
        'revMinLoc'          => 'int',
        'revMinFoyerLoc'     => 'int',
        'mafMinLoc'          => 'int',
        'mafMaxLoc'          => 'int',
        'newRevLoc'          => 'bool',
        'txEndettementMinLoc'   => 'float',
        'txEndettementMaxLoc'   => 'float',

        // 'onlyCrdConso'       => 'bool',
        // 'onlyPretConso'      => 'bool',
        'inclusTreso'        => 'bool',
        'tresoMin'           => 'int',
        'hebergementFree'    => 'bool',
        'mut'                => 'bool',
        'refusCoord'         => 'bool',
        'profession'         => 'string',
        'histoMut'           => 'string',
        'isDomtom'           => 'bool',
        'comments'           => 'string',
        'profLibMinDuree'    => 'int',
        'anon'               => 'bool'
    );

    public function __construct(){
    }

    // -----------------------
    //        SETTERS
    // -----------------------
    // public function setCritere($value){ $this-}
    public function set($property, $value){
        if (!property_exists($this, $property)) {
            return false;
        }
        
        if ($value === null) {
            $this->$property = null;
            return true;
        }

        if ($property === 'types') {
            return false; 
        }

        // Setter spécifique prioritaire
        $method = 'set' . ucfirst($property);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        if (isset($this->types[$property])) {
            switch ($this->types[$property]) {
                case 'int':
                    if ($value === '' || !is_numeric($value)) {
                        return false;
                    }
                    $value = (int)$value;
                    break;

                case 'bool':  // compatible DB (0/1, "0"/"1", true/false)
                    if ($value === '0' || $value === 0 || $value === false) {
                        $value = false;
                    } elseif ($value === '1' || $value === 1 || $value === true) {
                        $value = true;
                    } else {
                        return false;
                    }
                    break;

                case 'float':
                    if ($value === '' || !is_numeric($value)) {
                        return false;
                    }
                    $value = (float)$value;
                    break;

                case 'string':
                    if (!is_scalar($value)) {
                        return false;
                    }
                    $value = (string)$value;
                    break;

            }
        }
        $this->$property = $value;
        return true;
    }
    
    // -----------------------
    //        GETTERS
    // -----------------------
    public function get($property){
        if (!is_string($property)) {
            return null;
        }
        // Getter spécifique prioritaire
        $method = 'get' . ucfirst($property);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        // Fallback direct
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }


    public function getArray()
    {
        $data = [];
        foreach (LeadCritMapping::COLUMN as $property => $propertyName) {

            $propertyName = str_replace(['_', ' '], '', ucwords(strtolower($property), '_ '));
            $value = $this->get(lcfirst($propertyName));

            // if (is_string($value) && trim($value) === '') {
            //     continue;
            // }

            $data[lcfirst($propertyName)] = $value;
        }

        return $data;
    }

    public static function getObj($data) {
        $obj = new LeadCritere();

        foreach ($data as $key => $value) {
            $obj->set(lcfirst($key), $value);
        }

        return $obj;
    }
    
}