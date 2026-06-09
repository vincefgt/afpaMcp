<?php
/**
 * L'Exception est lance des qu'une erreur sur une prise de contact
 *
 * @author Koenig Mathieu, <mathieu@belenvol.fr>
 * @package Exceptions
 * 
 */

/* user defined includes */

/**
 * L'Exception est lance des qu'une erreur sur le pkg Financial
 *
 * @author Koenig Mathieu, <mathieu@belenvol.fr>
 * 
 */
require_once($_SERVER["CORE_PATH"].'Exceptions/class.Financial.php');

 /**
 * Contient tous les messages d'erreur pour le pkg Financial
 *
 * @author Koenig Mathieu, <mathieu@belenvol.fr>
 * 
 */
 require_once($_SERVER["CORE_PATH"].'Lang/Financial/class.Financial.php');
 

/**
 * Envoye lors d'une erreur Exceptions_Financial_Pub_PriseContact
 *
 * 
 * @author Vincent Fringant <vincent.fringant@premista.fr>
 * @package Exceptions
 */
class ExceptionsLeadCritere extends Exceptions_Financial
{
    // --- ATTRIBUTES ---
    /**
     * Version of the class
     *
     *
     * @var String
     */
    protected $VERSION="1.0.0";

    // --- OPERATIONS ---

    /**
     * Constructuer de l'exception. On lui passe le numero du message d'erreur
     * sera alors affiche
     *
     * @param int $msgErreur num message d'erreur
     * @param String $complement Message compl�mentaire
     */
    public function __construct($msgErreur, $complement){
    	$this->logPath = "leadCritere.log";
        $this->complement = $complement;
        parent::__construct (self::getMsg($msgErreur, $complement));
    }

    public static function getMsg($num, $complement){
        $returnValue = null;
        
        if (is_array($complement) || is_object($complement)) {
            $complement = print_r($complement, true);
        }

        switch ($num) {
            case 1: $returnValue = "Aucun critere trouvé pour cette src"; break;
            case 2: $returnValue = "SQL ERROR"; break;
            case 3: $returnValue = "impossible de mettre à jour"; break;
            case 4: $returnValue = "suppression impossible critere en cours d'écriture"; break;
            case 5: $returnValue = "suppression impossible"; break;
            case 6: $returnValue = "objet id non conforme"; break;
            case 7: $returnValue = "ERROR getModifiedValues"; break;
            case 8: $returnValue = "Aucune modification de donnee"; break;
            case 9: $returnValue = "activation/désactivation impossible"; break;
            case 10: $returnValue = "Nombre de ligne critére > 1"; break;
            default: // si erreur autre 
                $returnValue = "Impossible d'afficher le message";
        }
        if (isset($complement) && !empty($complement))
            $returnValue .= " : " . $complement;

        return $returnValue;
    }

} 
