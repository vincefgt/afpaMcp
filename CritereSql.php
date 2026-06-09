<?php

use phpDocumentor\Reflection\Types\Boolean;

require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Mapping/LeadCritMapping.php');
require_once ($_SERVER["CORE_PATH"] . 'SQL/class.Abstract_SQL_Factory.php');


/**
 * Generates secure SQL statements for lead criteria.
 */
class CritereSQL extends Abstract_SQL_Factory {
    const VERSION = "1.0.0";

    const TABLEDATA = LeadCritMapping::TABLE_CF;
    const TABLESUPPORT = LeadCritMapping::TABLE_SUPPORT;

    /**
     * Select query by ID critere
     * @param array $args
     */
    public static function createFindQuery($args) {
        $id = (int) $args['id']; 
        $query = sprintf(                 // Select by source actif
                    "SELECT * FROM %s AS t
                    WHERE t.%s = %d",
                    self::TABLEDATA,
                    LeadCritMapping::COLUMN['ID'],
                    $id
                );
                if (isset($args['mode']) && $args['mode'] === 'UPDATE') {
                    $query .= " FOR UPDATE";
                }
        // print_r($query);
        return $query;
    }

    /**
     * Select query ALL CRITERES (actif et inactif) by ID SUPPORT (int)
     * @param int $idSupport
     */
    public static function createFindQueryByIdSource($idSupport) {
        $query = sprintf(                 // Select by source actif
                    "SELECT * FROM %s AS t
                    INNER JOIN %s AS fs ON fs.%s = t.%s
                    WHERE t.%s = %d AND (t.%s = TRUE OR t.%s = FALSE)",
                    self::TABLEDATA,
                    self::TABLESUPPORT,
                    LeadCritMapping::COLUMN['ID_SUPPORT'],
                    LeadCritMapping::COLUMN['ID_SUPPORT'],
                    LeadCritMapping::COLUMN['ID_SUPPORT'],
                    $idSupport,
                    LeadCritMapping::COLUMN['ACTIF_CRITERE'],
                    LeadCritMapping::COLUMN['ACTIF_CRITERE']
                );
                Debug::trace($query, false, 'leadCritere.log');
        return $query;
    }

    /**
    * Select query ALL CRITERES (actif et inactif) by SRC (string support)
    * @param string $src
    */
    public static function createFindQueryBySrc($src) {
        $query = sprintf(                 // Select by source actif
                    "SELECT * FROM %s AS t
                    INNER JOIN %s AS fs ON fs.%s = t.%s
                    WHERE fs.%s = '%s' AND t.%s = TRUE",
                    self::TABLEDATA,
                    self::TABLESUPPORT,
                    LeadCritMapping::COLUMN['ID_SUPPORT'],
                    LeadCritMapping::COLUMN['ID_SUPPORT'],
                    LeadCritMapping::COLUMN['SRC'],
                    $src,
                    LeadCritMapping::COLUMN['ACTIF_CRITERE']
                );
                // print_r($query);
        return $query;
    }

    /**
     * @inheritDoc
     * @param array $args
     * Select query ALL CRITERES (actif et inactif) Optional choose if SUPPORT if only ACTIF or BOTH
     */
    public static function createFindAllQueryActifSupport($args){
        $query = sprintf(                 // All sources
                    "SELECT * FROM %s flc
                    INNER JOIN %s fs ON fs.%s = flc.%s
                    WHERE 1=1",
                    self::TABLEDATA,
                    self::TABLESUPPORT,
                    LeadCritMapping::COLUMN['ID_SUPPORT'],
                    LeadCritMapping::COLUMN['ID_SUPPORT']
                );
                if ($args[0] === 'Y') {
                    $query .= sprintf(" AND %s ='Y' ORDER BY %s ASC",
                    LeadCritMapping::COLUMN['ACTIF']);
                }
        // Debug::trace($query, false, 'leadCritere.log'); // log query
        return $query;
    }

    /**
     * Select ALL CRITERES Without condition
     */
    public static function createFindAllQuery(){
        $query = sprintf(                 // All active criteres actifs
                    "SELECT * FROM %s AS t
                    WHERE %s = %s ORDER BY %s ASC",
                    self::TABLEDATA,
                    LeadCritMapping::COLUMN['ACTIF_CRITERE'],
                    1,                                            // 1 = true
                    LeadCritMapping::COLUMN['ID']
                );
        return $query;
    }

    /**
     * @inheritDoc
     * Delete query By ID CRITERE
     */
    public static function createRemoveQuery($id){
        $query = sprintf(
            "DELETE FROM %s WHERE %s = %d",
            self::TABLEDATA,
            LeadCritMapping::COLUMN['ID'],
            (int) $id // cast pour éviter toute injection
        );
        return $query;
    }

    /**
     * Update query BY ARG(2) = ID CRITERE and STATE > define in DAO
     * Update only critere, not lead source
     * @param array $args
     */
    public static function createUnactifQuery($args){ 
        $query = sprintf(
            "UPDATE %s SET %s = %d WHERE %s = %d",
            self::TABLEDATA,
            LeadCritMapping::COLUMN['ACTIF_CRITERE'],
            $args['state'],
            LeadCritMapping::COLUMN['ID'],
            (int) $args['id'] // cast pour éviter toute injection
        );
        // Debug::trace($query,false,'leadCritere.log'); // verify query
        return $query;
    }

    /**
     * @inheritDoc
     * @param LeadCritere $critere
     * Update query By critere(objet)
     */
    public static function createUpdateQuery($critere){ 
        $data = LeadCritereHydrator::hydrateToArray($critere);
        // print_r($data);
        unset ($data[LeadCritMapping::COLUMN['ID']], 
                $data[LeadCritMapping::COLUMN['ID_SUPPORT']],
                $data[LeadCritMapping::COLUMN['SRC']],             // delete if joint table support
                $data[LeadCritMapping::COLUMN['TYPE_PUB']],        // delete if joint table support
                $data[LeadCritMapping::COLUMN['RECETTE']], 
                $data[LeadCritMapping::COLUMN['NOM_SOURCE']],      // delete if joint table support
                $data[LeadCritMapping::COLUMN['ACTIF']]            // delete if joint table support
                );
        // $data[LeadCritMapping::COLUMN['ACTIF_CRITERE']]=TRUE;
        $query = static::createUpdateQueryFromData(self::TABLEDATA, $critere->get('id'), $data); // update only critere
        return $query;
    }

    /**
     * @inheritDoc
     * @param LeadCritere $critere
     * INSERT query by critere(objet)
     */
    public static function createInsertQuery($critere){
        $data = LeadCritereHydrator::hydrateToArray($critere);
        // print_r($data);
        unset($data[LeadCritMapping::COLUMN['ID']],
                $data[LeadCritMapping::COLUMN['SRC']],             // delete if joint table support
                $data[LeadCritMapping::COLUMN['TYPE_PUB']],        // delete if joint table support
                $data[LeadCritMapping::COLUMN['NOM_SOURCE']],      // delete if joint table support
                $data[LeadCritMapping::COLUMN['ACTIF']]   
            ); 
            //auto increment data
        $query = static::createInsertQueryFromData(self::TABLEDATA, $data);
        // print_r($query); 
        // Debug::trace($query,false,'leadCritere.log'); // verify query
        return $query;
    }   

    /**
     * Validates table name against whitelist
     */
    // public static function assertValidTable($table) {
    //     if (!in_array($table, LeadCritMapping::getAllowedTables(), true)) {
    //         throw new InvalidArgumentException("Invalid table name: " . $table);
    //     }
    // } 

    /**
     * Creates an update query from the given data.
     *
     * @param string  $table The table name
     * @param string  $table2 The table name
     * @param int     $id    The identifier
     * @param array   $data  The data to update
     *
     * @return string The query
     */
    protected static function createUpdateQueryFromDataJointTable($table,$table2, $id, array $data){
        if (empty($data)) {
            throw new \InvalidArgumentException('No values to update.');
        }
        $query   = [];
        $query[] = 'UPDATE';
        $query[] = $table;
        $query[] = 'INNER JOIN ' .$table2 .' AS t2 ON t2.' .$data[LeadCritMapping::COLUMN['SRC']] .' = t.' .$data[LeadCritMapping::COLUMN['SRC']];
        $query[] = 'SET';
        $values = [];
        foreach ($data as $column => $value) {$value = self::convertValueToDatabase($value);
            $values[] = "$column = $value";
        }
        $query[] = implode(', ', $values);
        $query[] = 'WHERE ' . static::IDENTIFIER_KEY . ' = ' . self::convertValueToDatabase($id);
        $query = implode(' ', $query);
        return $query;
    }
}