<?php

use SebastianBergmann\Environment\Console;

require_once ($_SERVER["FRAMEWORKS_PATH"] . 'SQL/Exceptions/class.SQL.php');
require_once ($_SERVER["CORE_PATH"] . 'SQL/class.Requete.php');
require_once ($_SERVER["FRAMEWORKS_PATH"].'SQL/class.SQL.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Mapping/LeadCritMapping.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/SQL/CritereSql.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Model/LeadCritere.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Hydrator/LeadCritereHydrator.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Exception/ExceptionLeadCritere.php');
require_once ($_SERVER["CORE_PATH"] . 'Financial/Pub/class.Support_DAO.php');

/* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
Gestion création ou modification BDD
(ajout) colonne
      BACK / core/back
        |  
        |- Mapping ((modif)+(ajout))
        |       |
        |       |- hard add x1 > line mapping (add modif)
        |
        |- LeadCritere (ajout)
        |       |
        |       |- hard add x2
        |       |       |- add variable 
        |       |       +
        |       |       |- add > private $types = array(type)  (add modif)
        |       |       
        |       |- getter / setter auto (nothing to do)
        |
        |- Triggers (regenerer triggers table) (ajout)+(modif)
        |       |
        |       |- hard update x2
        |       |           |- delete (add modif) (optional because we never delete critere) this triggers could be unexistant
        |       |           |- update (add modif)
        |       |
        |       |- Helper to generated auto tirgger > CAll "procedure" >
        |       |                                      |
        |       |                                      |-copier/colle dans un script > Execute
        |
*/

/**
 * DAO for lead critere
 * @author Vincent Fringant <vincent.fringant@premista.fr>
 */
class LeadCritereDAO extends SQL_SQL{
    const VERSION = "1.0.0";

    public function __construct() {
    }

    private static function init_object($row){
        return LeadCritereHydrator::hydrateToObj(new LeadCritere(), $row);
    }

    private static function cleanItem($row){
        $resultRow = array_filter($row, function ($value) { return $value !== null;}); // Remove all null values
        // return $resultRow; // return array all 

        // $resultRowClean = array_filter($resultRow, function ($value, $key) {return !is_int($key);}, ARRAY_FILTER_USE_BOTH);
        // clean tableau associatif > tableau simple keep only string key, delete serialization key
        $resultRowClean = array_filter($resultRow, function ($value, $key) 
        {
            return !is_int($key);
        }, ARRAY_FILTER_USE_BOTH);

        return $resultRowClean; // return array clean
    }
                 
    /**
     * Retrieve a critere by src
     */
    public static function getCritereDAO($src) {
        $result = null;
        if (!is_string($src) || $src === '') {
            throw new InvalidArgumentException("No source value.");
        }

        $sql = SQL_SQL::getInstance();
        try {
            //$result = $sql->select("SELECT * FROM financial_lead_critere WHERE id_support = 844");
			$result = $sql->select(CritereSQL::createFindQueryBySrc($src));
        } catch (Exceptions_SQL $e) {
            throw new ExceptionsLeadCritere(2,"GET BY SRC : " .$src);
        }
        
        if ( $result->getNumRows() != 1 ){
			return null;
		} else {
            // Récupérer la ligne sous forme de tableau associatif
            $resultRow = $result->getRow();
            // self::cleanItem($resultRow);
            return self::init_object(self::cleanItem($resultRow)); // return Objet
		}
    }

    public static function getCritereDAOByIdSrc($id) {
        $result = null;
        if (!is_numeric($id) || $id === '') {
            throw new InvalidArgumentException("No id value.");
        }

        $sql = SQL_SQL::getInstance();
        try {
			$result = $sql->select(CritereSQL::createFindQueryByIdSource($id));
        } catch (Exceptions_SQL $e) {
            throw new ExceptionsLeadCritere(1,$id);
        }
         if ($result->getNumRows() > 1){
             throw new ExceptionsLeadCritere(10,$id);
         }

         if ($result->getNumRows() > 0){
            // Récupérer la ligne sous forme de tableau associatif
            $resultRow = $result->getRow();
            return self::init_object($resultRow); // return Objet
         } 
    }

    public static function getCritereDAOById($args) {
        $result = null;
        $id = (int) $args['id'];
        if (!isset($id) || !is_numeric($id)) {
            throw new InvalidArgumentException("No id value.");
        }

        $sql = SQL_SQL::getInstance();
        try {
			$result = $sql->select(CritereSQL::createFindQuery($args));
        } catch (Exceptions_SQL $e) {
            throw new ExceptionsLeadCritere(1,$id.$e);
        }
        // Récupérer la ligne sous forme de tableau associatif
        Debug::trace($result, false, 'leadCritere.log');
        $resultRow = $result->getRow();
        self::cleanItem($resultRow);
        return self::init_object($resultRow); // return Objet
	
    }

    /**  
     * @author Vincent Fringant <vincent.fringant@premista.fr> 
     */
    public static function getAll(){
        // do not implement
    }

    /**
     * @author Vincent Fringant <vincent.fringant@premista.fr>
     * Get all sources (active or not)
     */
    public static function getAllCriteres($actif = 'N') {
        $returnValue = array();
    	//Retreive the SQL object
		$sql = SQL_SQL::getInstance(); //db
		//SQL Process
		try{
   
			$result = $sql->select(CritereSQL::createFindAllQueryActifSupport($actif));
            
		}catch(Exceptions_SQL $e){
			throw new ExceptionsLeadCritere(2,"GET ALL_SOURCES".$e);
		}

		while ( $row = $result->getRow() ){
            // Debug::trace("getAll", false,'leadCritere.log');
            // Debug::trace($row,false,'leadCritere.log');
			$returnValue[] = self::init_object($row); // To Obj
		}
        // Debug::trace('DAO', false, 'leadCritere.log'); // log all critere (obj)
        // Debug::trace($returnValue, false, 'leadCritere.log'); // log all critere (obj)
		return $returnValue;
    }

    /**
     * @author Vincent Fringant <vincent.fringant@premista.fr>
     * Insert a critere
     * @param Object to insert
    */
    public static function insertCritereDb($data) {
        global $RULES,$USER;

        $id_support = $data["idSupport"];
        if (!$data || !$id_support) {
            throw new ExceptionsLeadCritere(6, $data);
        }
    	//Retreive the SQL object
		$sql = SQL_SQL::getInstance();
        //SQL Process
        $hasTransaction = $sql->hasTransaction();
		if( !$hasTransaction ){
			$sql->startTransaction();
		}
		//SQL Process
		try{
            $support = Pub_Support_DAO::getObject($id_support); //load support from BDD support
            $critereExistant = self::getCritereDAOByIdSrc($support->getId());
            if($critereExistant !== null){
                // echo json_encode(["critere already exists! : id_support" => $id_support,
                //                  "id critere" => $critereExistant->get('id')]
                //                  );
                self::updateCritereDb($data); // switch to update proccess
            } else {
                $objToInsert = LeadCritere::getObj($data);
                $objToInsert->set('idSupport',$id_support); // import id > GET to Obj / Do not delete
                // $objToInsert = self::init_object($data); // creation obj critere from data
                $newId = $sql->transaction(CritereSQL::createInsertQuery($objToInsert)); // keep proccesed insertion
            }
		}catch(Exceptions_SQL $e){
            throw new ExceptionsLeadCritere(3,"insertion " .$id_support);
		}

		if( !$hasTransaction ){
			$sql->commit();
        }
    }

    public static function updateCritereDb($data) {
		global $USER, $RULES;

        $id = $data["id"];

        if (!is_numeric($id)) {
            throw new InvalidArgumentException("Invalid ID value.");
        }
        
        $sql = SQL_SQL::getInstance();
		$hasTransaction = $sql->hasTransaction();
		if( !$hasTransaction ){
			$sql->startTransaction();
		}
		//SQL Process
		try{
            $args = ['id' => $id,'mode' => 'UPDATE'];
            $oldCritere = self::getCritereDAOById($args); // load object from Db
            $oldData = $oldCritere->getArray(); // obj -> mapping PHP
            $modifiedValue = self::getModifiedValues($oldData,$data);
            
            // Debug::trace($oldCritere,false,'leadCritere.log');
            // Debug::trace($oldData,false,'leadCritere.log');
            // Debug::trace('data',false,'leadCritere.log');
            // Debug::trace($data,false,'leadCritere.log');
            // Debug::trace($modifiedValue,false,'leadCritere.log');

            if (!$oldCritere) {
                throw new ExceptionsLeadCritere(8, "Critere Not found");
            }
            if (!empty($modifiedValue)){
                self::applyDataToObject($oldCritere,$modifiedValue); // set data to object

                $newCritere = $oldCritere;

                $sql->transaction(CritereSQL::createUpdateQuery($newCritere)); // 1. Obj -> mapping SQL / 2. send object to BDD
                echo json_encode(["status" => "updated", "id" => $id, "values Updated" => $modifiedValue]);
            } else {
                if (!$hasTransaction) {
                    $sql->rollback();
                }
                throw new ExceptionsLeadCritere(8,"");
            }
		}catch(Exceptions_SQL $e){
			throw new ExceptionsLeadCritere(3,'id: '.$id);
		}

		if( !$hasTransaction ){
			$sql->commit();
		} 
    }

    /**
     * Disable (set actif_crit = 0)
     */
    public static function deleteCritereDb($id) {
        global $USER, $RULES;

        if (!is_numeric($id)) {
            throw new InvalidArgumentException("Invalid ID value.");
        }
        //Retreive the SQL object
        $sql            = SQL_SQL::getInstance();
        $hasTransaction = $sql->hasTransaction();
        if (!$hasTransaction) {
            $sql->startTransaction();
        }
        //SQL Process
        try {
            $args = ['id' => $id, 'mode' => 'UPDATE'];
            $result = self::getCritereDAOById($args);                   // test if exist
            if (!$result) {
                $sql->rollback();
                throw new ExceptionsLeadCritere(4, "Do not exist: " .$id);
            }
            $args['state'] = !$result->get('actifCritere');             // state inversion
            // $sql->transaction(CritereSQL::createRemoveQuery($id));   // real delete action
            $sql->transaction(CritereSQL::createUnactifQuery($args));   // fake delete action
            // echo json_encode(["status" => "deleted", "id" => $id]);
        } catch (Exceptions_SQL $e) {
            throw new ExceptionsLeadCritere(5, $id);
        }

        if (!$hasTransaction) {
            $sql->commit();
        }
    }

    private static function getModifiedValues(array $old, array $new)
    {
        $modified = [];

        foreach ($new as $key => $newValue) {

            // Champs ignorés
            if (in_array($key, ['id', 'idSupport', 'src'], true)) {
                continue;
            }

            $keyExists = array_key_exists($key, $old);
            $oldValue = $keyExists ? $old[$key] : null;

            if (!$keyExists) {
                $modified[$key] = $newValue;
                continue;
            }

            if ($oldValue !== $newValue) {
                $modified[$key] = $newValue;
            }
        }

        return $modified;
    }


    private static function applyDataToObject($obj, array $data){
        try{
            foreach ($data as $key => $value) {
                // transforme doublon_duree → setDoublonDuree
                $property = $key;
                if (property_exists($obj, $property)) {$obj->set($key,$value);}
            }
        } catch (Exceptions_SQL $e){
            throw new Exception(8,$obj);
        }
        }

    
        private static function normalize($value){
        if ($value === null) { return null; }            // null reste null
        if (is_bool($value)) { return (int) $value; }    // bool → int (true = 1, false = 0)
        if (is_numeric($value)) { return $value + 0; }   // numeric string / int / float → nombre réel
        if (is_string($value)) { return trim($value); }  // string → trim
        return $value;
}
}

