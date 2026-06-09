<?php
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Model/LeadCritere.php');

class LeadCritereHydrator { 
    const VERSION='1.0.0';
    /**
     * Hydrate l'objet LeadCritere à partir d'un tableau BDD
     * mapping SQL -> Obj
     */
    public static function hydrateToObj(LeadCritere $obj,$row){
        // var_dump($row);
        // Debug::trace('Before Hydrator',false,'leadCritere.log');
        // Debug::trace($row,false,'leadCritere.log');
        // Debug::trace($obj,false,'leadCritere.log');
        foreach (LeadCritMapping::COLUMN as $property => $column) {
            if (array_key_exists($column, $row)) {
                $camelProperty = self::convertNamingBddToObj($property);
                $obj->set($camelProperty, $row[$column]);
            }
        }
        // Debug::trace("After hydrator Obj",false,'leadCritere.log'); // verif hydrator log 
        // Debug::trace($obj,false,'leadCritere.log');
        return $obj;
    }

    /**
     * // mapping -> SQL
     */
    public static function hydrateToArray($obj){
        $data = [];
        foreach (LeadCritMapping::COLUMN as $property => $column) {
            $camelProperty = self::convertNamingBddToObj($property);
            $value = $obj->get($camelProperty);
            $data[$column] = $value;
        }
        return $data;
    }

    private static function convertNamingBddToObj($string){
        $string = strtolower($string);
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        $string = lcfirst($string);
        return $string;
    }
}