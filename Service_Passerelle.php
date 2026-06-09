<?php
// -----------------------------------Lead_Emulation-------------------------------------//
// ------------------------------------------------------------------------------------//
use Central\Covid\CovidCloser;
use Central\PasserelleQueue\PasserelleQueue;
use Core\ApiTracking\Checkpoint\LeadReject\Bdf;
use Core\ApiTracking\Checkpoint\LeadReject\DoubledLead;
use Core\ApiTracking\Checkpoint\LeadReject\Idq;
use Core\ApiTracking\Checkpoint\LeadReject\MaxConsummationAmount;
use Core\ApiTracking\Checkpoint\LeadReject\MaxLoanAmount;
use Core\ApiTracking\Checkpoint\LeadReject\MinConsummationAmount;
use Core\ApiTracking\Checkpoint\LeadReject\MinLoanAmount;
use Core\ApiTracking\Checkpoint\LeadReject\MinLoanCount;
use Core\ApiTracking\Checkpoint\LeadReject\MinRevenue;
use Core\ApiTracking\Checkpoint\LeadReject\MinDebtRatio;
use Core\ApiTracking\Checkpoint\LeadReject\MaxDebtRatio;
use Core\ApiTracking\Checkpoint\LeadReject\MinTresorerie;
use Core\ApiTracking\Checkpoint\LeadReject\NotCdi;
use Core\ApiTracking\Checkpoint\LeadReject\NotOwner;
use Core\ApiTracking\Checkpoint\LeadReject\NotRetired;
use Core\ApiTracking\Checkpoint\LeadReject\NullOwnerAmount;
use Core\ApiTracking\Checkpoint\LeadReject\NoFreeRenter;
use Core\ApiTracking\Checkpoint\LeadReject\MaxMafAmount;
use Core\ApiTracking\Checkpoint\LeadReject\MinMafAmount;
use Core\ApiTracking\Checkpoint\LeadReject\MinAge;
use Core\ApiTracking\Checkpoint\LeadReject\MaxAge;
use Core\ApiTracking\Checkpoint\LeadReject\MinAnciennete;
use Core\ApiTracking\Checkpoint\LeadReject\NotDomTom;
use Core\ApiTracking\Checkpoint\LeadReject\LeadHs;
use Core\ApiTracking\Checkpoint\LeadReject\NotTenant;
use Core\Container\ContainerSingleton;
use Core\MailJet\MailJetClient;
use Core\MailJet\Template\MailJetMailDepotLead;
use SQL_Requete_Financial;
#use Core\Financial\Priorite\Calcul\PrioriteCalcul_Premium;

require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Mapping/LeadCritMapping.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/SQL/CritereSql.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Model/LeadCritere.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/DAO/LeadCritereDAO.php');

/****** CONFIGURATION *******/
global $path, $LOCAL, $DEV, $USER_DEV_PATH, $MAIL_PROD, $USER;
define('LEAD_SERVICE_NAME', 'Service_Passerelle');

//Lecture de la configuration
include_once(__DIR__ . '/../Include/config.inc.php');

if ($DEV) {
    $path = $USER_DEV_PATH . "central/";
} else {
    $path = "/home/financial/www/";
}

include_once($_SERVER["CORE_PATH"] . 'Include/config.inc.php');
require_once $_SERVER['CORE_PATH'] . 'ApiTracking/getTracker.php';
require_once($_SERVER["FRAMEWORKS_PATH"] . 'Debug/class.Debug.php');
if (php_sapi_name() != 'cli') {
    include_once(__DIR__ . '/gatewayService.php');
}
require_once($_SERVER["FRAMEWORKS_PATH"] . 'Debug/class.Debug.php');
require_once ($_SERVER["CORE_PATH"] . 'Passerelle/Mapping/LeadCritMapping.php');
require_once($path . 'PasserelleQueue/PasserelleQueue.php');

/****************************/
// Initialize le tracking du lead
$leadTracker = getTracker();
$trackingId = null;

$cleanPost = [];
if (!empty($_POST)) {
    $cleanPost = $_POST;
    unset($cleanPost['TRACKING_KEY']);
}

if (!empty($cleanPost) || !empty($_GET)) {
    $src = null;
    if (is_array($cleanPost) && array_key_exists('src', $cleanPost)) {
        $src = $cleanPost['src'];
    } elseif (array_key_exists('src', $_GET)) {
        $src = $_GET['src'];
    } elseif (!empty($_GET['devisprox'])) {
        $src = 'devisprox';
    } elseif (!empty($_GET['devisprox_premium'])) {
        $src = 'devisprox_premium';
    } elseif (!empty($_GET['devisprox_exclu'])) {
        $src = 'devisprox_exclu';
    }

    $trackingId = $leadTracker->getTrackingKey();
    $leadTracker->startLeadTracking(LEAD_SERVICE_NAME, $src, $cleanPost);
}

/**
 * Service permettant de gerer Le service Financial pour l'integration des BFI
 *
 * @author Marc Richard, <marc@belenvol.fr>
 */
require_once($_SERVER["CORE_PATH"] . 'Services/origin.Service_Financial.php');

/**
 * Mail de depot Patrimial
 *
 * @author  Adrien Ballarano, <adrien.ballarano@centralfinances.fr>
 * @version 2.0.0
 */
require_once($path . 'Central/Classeur/Mail/Traitement/Patrimial/Client/class.BFI_Depot.php');

/**
 * Cette classe contient une m&eacute;thode par requete, celle-ci renvoie alors de leur
 * un tableau contenant le r&eacute;sultat de la requ&ecirc;te, ou l'obj ad&eacute;quat.
 *
 *
 */
require_once($path . "SQL/class.Requete.php");

/**
 * Action_Document DAO
 *
 * @author  Richard Marc, <marc@belenvol.fr>
 *
 */
require_once($_SERVER["CORE_PATH"] . 'Financial/Classeur/Commercial/class.Commercial_DossierRefinancement_DAO.php');

/**
 * Traitement sur un dossier
 *
 * @author  Adrien Ballarano, <adrien.ballarano@centralfinances.fr>
 *
 */
require_once($path . 'Central/Classeur/class.DossierRefinancement_Traitement_Central_DAO.php');

/**
 * Class for the repartition
 *
 * @author  Adrien Ballarano, <adrien.ballarano@centralfinances.fr>
 * @version 2.0.0
 */
require_once($path . 'Central/Classeur/class.DossierRefinancement_Repartition_Central.php');

/**
 * Classe permettant la generation de la proposition Patrimial
 *
 * @author  Adrien Ballarano, <adrien.ballarano@centralfinances.fr>
 * @version 2.0.0
 */
require_once($path . 'Central/Classeur/Mail/Traitement/Client/Proposition/class.Proposition_Patrimial.php');

/**
 * Service permettant de gerer Le service Financial pour l'integration des BFI
 *
 * @author  Richard Marc, <marc@belenvol.fr>
 *
 */
class Service_Passerelle extends Service_Financial_Origin
{
    const PROF_LIB = 'profession libérale';
    const ANCIENNETE_DEFAUT = -1; // Ancienneté contrat par défaut

    /**
     * Liste des segmentations datascience à ne pas envoyer à Kiamo
     */
    const FILTRE_CALL = ['filtre_crise'];
    const PROF_NC = "inconnu";
    const CIV_DEFAULT = 'M'; //Service_Passerelle::CIV_DEFAULT; //"M";
    const CIV_W = "Mme";
    const IDQ_DEVISPROX = 18;
    /**
     * Admin User
     *
     *
     * @var User_Financial
     */
    protected $admin = null;

    /**
     * @var stdClass
     */
    protected $segmentCetelem = null;

    /**
     * Class constructor
     *
     *
     */
    function __construct()
    {
    }

    /**
     * Return the PasserelleQueue Service
     * @return PasserelleQueue
     */
    private function getPasserelleQueue()
    {
        $container = ContainerSingleton::getContainer();
        return $container->get('passerelle.queue');
    }

    /**
     * @return MailJetClient
     */
    private function getMailJetClient()
    {
        return ContainerSingleton::getContainer()->get('mailjet.client');
    }

    //Fonction pour exclure les département domtom pour les devisprox
    function isDomtomForDevisprox($tmp)
    {
        //département domtom à exclure
        $DEPARTEMENTS_DOMTOM = [971, 972, 973, 974, 975, 976, 977, 978, 986, 987, 988];

        $horsDomtom = false;
        if (isset($tmp['cp']) && in_array(
            substr($tmp['cp'], 0, 3),
            $DEPARTEMENTS_DOMTOM
        )) {
            $horsDomtom = true;
        }
        return $horsDomtom;
    }

    //Fonction pour exclure quelques professions 
    function isCDIFilterForDevisprox($tmp)
    {
        $filterCDIOK = false;
        if (
            !empty($tmp['profession_vous'])
            && !in_array($tmp['profession_vous'], ["Recherche d'emploi", 'Etudiant', 'Sans profession', 'Autre'])
            || (
                !empty($tmp['profession_conjoint'])
                && !in_array($tmp['profession_conjoint'], ["Recherche d'emploi", 'Etudiant', 'Sans profession', 'Autre'])
            )
        ) {
            $filterCDIOK = true;
        }
        return $filterCDIOK;
    }

    function initDevisprox($trackingId)
    // function initDevisprox($trackingId, $critere)
    {
        $this->initDB();
        $PARAM = Parametre_DAO::getObject(0);
        $LOCAL = 1;
        $USER  = $this->admin;

        $date = new DateTime();
        $date->modify($this->getCheckPeriod($_POST['src']));
        $date = $date->format("Y-m-d");



        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);

        if ($this->isDoublonMail($date)) {
            $leadTracker->rejectLead(new DoubledLead($_POST['mail'], $date));
            return "0";
        }

        if ($PARAM->hasKey("PASSERELLE_QUEUE_ENABLED") && $PARAM->PASSERELLE_QUEUE_ENABLED) {
            $this->checkDossier();
            $data = [
                'POST' => $_POST,
                'GET'  => $_GET,
                'ARGS' => [1, true, true],
                'trackingId' => $trackingId
            ];
            $xml  = $this->getPasserelleQueue()->insert(serialize($data), Service_Passerelle::class);
        } else {
            $xml = $this->enterDossier(1, true, true, $trackingId);
        }
        return $xml;
    }

    /**
     * Init the DB
     *
     *
     */
    public function initDB()
    {
        global $LOCAL, $DEV, $MAIL_PROD;
        Mail_Mail_Dev::$IS_DEV   = true;
        Mail_Mail_Dev::$ONLY_DEV = false;
        Mail_Mail_Dev::$DEV_MAIL = new Mail_Adresse("Service Informatique", $MAIL_PROD);
        /** DEFINE DB LOCALISATION **/
        switch ($_POST['cnf']) {
            case static::URI_CENTRALFINANCE:
                $_SERVER["DB_NAME"]  = "financial_cf";
                $_SERVER["DB_LOGIN"] = $_SERVER["DB_NAME"];
                break;
            case static::URI_PATRIMIAL:
                $_SERVER["DB_NAME"]  = "financial_pat";
                $_SERVER["DB_LOGIN"] = $_SERVER["DB_NAME"];
                break;
            case "test.fr":
            case "testcf.fr":
                $_SERVER["DB_NAME"]  = "financial_testcf";
                $_SERVER["DB_LOGIN"] = $_SERVER["DB_NAME"];
                break;
            default:
                throw new Exceptions_Financial_Systeme(826, $_POST['cnf']);
        }

        getTracker()->debug('Selected database', ['db_name' => $_SERVER['DB_NAME'], 'db_login' => $_SERVER['DB_LOGIN']]);
        $this->admin = User_Financial_DAO::getObject(100); //ADMIN fix security LOCAL
    }

    /**
     * Return the support
     *
     * @return Pub_Support or Null
     *
     */
    protected function retrieveSupport()
    {
        $support = null;

        if (!empty($_POST['src'])) {

            $support = Pub_Support_DAO::getAllBySrc($_POST['src'], true);

            if (empty($support)) {
                $support = Pub_Support_DAO::getAllBySrc('src_unknown', true);
            }
        }

        if (empty($support)) {
            $support = Pub_Support_DAO::getObject(4); //INTERNET (naturel)
        }

        return $support;
    }

    /**
     * Web service to add a dossier to Financial
     *
     * @throws Exceptions_Financial_Systeme Data error
     *
     * @param      $valid     1 if valide (affiliation)
     * @param      $send_mail Bool true to send email
     * @param Bool $return    True if the value must be return, false = echo
     *
     * @return Classeur_DossierRefinancement
     */
    public function enterDossier($valid = 1, $send_mail = true, $return = false, $trackingId = "", $src = '', $checkDossier = true)
    {
        global $PARAM, $DEV, $LOCAL, $USER, $SESSION, $POST;

        $affiliationAction='';

        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $leadTracker->debug('enter dossier');

        try {
            $container = ContainerSingleton::getContainer();

            $this->initDB();
            if ($checkDossier) {
                $this->checkDossier();
            }

            $LOCAL = 1;
            $PARAM = Parametre_DAO::getObject(0);

            $_POST['acc_finalite'] = true;
            $_POST['acc_partenaires'] = true;
            $_POST['emailing'] = true;
            $_POST['prospect_sms'] = true;
            $_POST['assurance_optin'] = true;
            $_POST['financement_optin'] = true;

            if (!empty($PARAM->SMTP_TRANSACTIONNEL)) { //Activation du SMTP Transactionnel
                require_once($_SERVER["FRAMEWORKS_PATH"] . 'Mail/class.Mail.php');

                Mail_Mail::$SMTP_SECURE = Mail_Mail::SMTP_SECURE_TYPE_TLS;
                Mail_Mail::$SMTP_HOST = $PARAM->SMTP_TRANSACTIONNEL_HOST;
                Mail_Mail::$SMTP_PASSWORD = $PARAM->SMTP_TRANSACTIONNEL_PASSWORD;
                Mail_Mail::$SMTP_USERNAME = $PARAM->SMTP_TRANSACTIONNEL_USERNAME;
                Mail_Mail::$SMTP_PORT = 587;
            }

            //check passerelle autorisation
            $support = $this->retrieveSupport();
            $dossier = $this->insertDossierFinancial();
            $dossier = Classeur_DossierRefinancement_DAO::getObject($dossier->getId());

            $leadTracker->dossierCreated($dossier, $support);

            //Enregistrement des informations d'affiliation
            $complement = "";
            if (!empty($_POST['ed'])) {
                $complement = $_POST['ed'];
            }
            $externalId = null;
            if (!empty($_POST['externalId'])) {
                $externalId = $_POST['externalId'];
            }

            $logData = [
                'support id' => $support->getId(),
                'mail' => trim($_POST['mail']),
                'date' => date("Y-m-d"),
                'valid' => $valid,
                'dossier_id' => $dossier->getId(),
                'complement' => $complement,
                'formulaire' => trim(@$_POST['formulaire']),
                $externalId
            ];
            $leadTracker->debug('Dossier created', array_filter($logData));
            if (!empty($_POST['TRACKING_KEY'])) {
                Debug::trace(
                    sprintf(
                        "%s : Lead injection with DB data (%s): \n\tDATA(%s)",
                        $_POST['TRACKING_KEY'],
                        __FILE__,
                        json_encode($logData)
                    ),
                    false,
                    'ServicePasserelle.log'
                );
            }

            if ($PARAM->hasKey('LEAD_FREEZE_DATA') && $PARAM->LEAD_FREEZE_DATA && !empty($dossier)) {
                $message = [
                    'trackingId' => $trackingId,
                    'dossierId' => $dossier->getId()
                ];

                parent::sendMessageToRabbitMQ(static::DOMAIN_CENTRALFINANCE, $message, 'RABBITMQ_QUEUE_LEAD_DOSSIER');
            }

            $sql = SQL_SQL::getInstance();
            $sql->transaction(SQL_Requete_Financial::getInsertRequete(112, array(
                $support->getId(),
                trim($_POST['mail']),
                date("Y-m-d"),
                $valid,
                $dossier->getId(),
                $complement,
                trim(@$_POST['formulaire']),
                $externalId,
                $affiliationAction,
                $trackingId
            )));

            //Vérification si traitement en équipe
            $teamKey = mb_strtoupper('EQUIPE_' . $src);
            $teamId = ($PARAM->hasKey($teamKey) && $PARAM->$teamKey) ? $PARAM->$teamKey : null;

            if (!is_null($teamId)) {
                $typeRepartition = mb_strtoupper('REPARTITION_' . $src);
                $this->repartitionAuto($dossier, $teamId, $typeRepartition);
            } else {
                //POSTTRAITEMENT UNE FOIS LE DOSSIER INSERER - DOIT ETRE RETIRER DE CETTE CLASSE LORS DE LA REFONTE

                //La fonction Traitement BFI effectue tout le traitement
                // -> Teste si traitement propositions, sinon on fait le traitement classique
                // -> Sinon effectue le transfert vers le bon groupe Discofone
                // Retourne true s'il faut envoyer le mail via SendMail

                $SESSION = Session_Session::createSession(
                    @$_SERVER["SESSION_NAME"],
                    null,
                    addslashes($USER->getLogin()) . microtime()
                );

                if (empty($PARAM->PARAM_PATRIMIAL)) { //CAS D'UN DOSSIER CHEZ CF
                    try {
                        $kiamoCall = true;
                        $disablingEmailing = false;
                        if(!empty($_POST['tuniform']) && $_POST['tuniform'] == 1) {
                            $kiamoCall = false;
                            $disablingEmailing = true;
                        }
                        $isCetelem = false;
                        $isVidata = false;
                        if (
                            ContainerSingleton::getContainer()->getParameter('SEGMENTATION_CETELEM_ENABLED') == "true"
                        ) {
                            $segment = $container->get('segmentation.client');
                            $segmentCetelem = $segment->getSegmentation($dossier, static::DOMAIN_CETELEM, 1);
                            $this->segmentCetelem = json_decode($segmentCetelem);

                            if ($this->segmentCetelem->data === strtoupper(static::DOMAIN_CETELEM)) {
                                $isCetelem = true;
                                $disablingEmailing = true;
                                $leadTracker->debug("cetelem traitement : No kiamo call");
                                $leadTracker->debug('Emailing will be disabled');
                            }
                        }
                        if (
                            (empty($_POST['tuniform']) || $_POST['tuniform'] !== 1) &&
                            !empty($PARAM->TRANSFERT_RACCOON_ENABLED) && $PARAM->TRANSFERT_RACCOON_ENABLED
                        ) {
                            $segment = $container->get('segmentation.client');
                            $segment1 = $segment->getSegmentation($dossier, static::SEGMENTATION_1, 1);
                            $segment1 = json_decode($segment1);
                            
                            if ($segment1->data === 'PR23') {
                                $prioriteCalcul = new PrioriteCalcul_Profil($dossier);
                                $segment1->data = $prioriteCalcul->calculePriorite();
                            }

                            if ($segment1->data === 'PR3') {
                                $getSegmentation = $container->get('segmentation.client');
                                $getSegmentation = $getSegmentation->getSegmentation($dossier, $_POST['cnf'], $LOCAL);
                                Debug::trace('post segmentation ' . var_export($getSegmentation, true), false, 'ServiceRaccoon.log');
                                $priority = json_decode($getSegmentation, true);

                                $appetenceRacProprietaire = [];
                                $type = $dossier->getTypeDossier();
                                if ($type == Classeur_DossierRefinancement::PROPRIETAIRE) {
                                    try {
                                        $tresoDatascience = $dossier->getDonneeDossierEmprunteur()->getComplement()?$dossier->getDonneeDossierEmprunteur()->getComplement()->getTresorerie():-1;
                                        $getAppetenceRacProprietaire = $container->get('segmentation.datascience');
                                        $getAppetenceRacProprietaire = $getAppetenceRacProprietaire->getAppetenceRacProprietaire($dossier, $tresoDatascience, $priority['data']);
                                        $appetenceRacProprietaire = json_decode($getAppetenceRacProprietaire, true);
                        
                                        Debug::trace('post appetence segmentation ', false, "ServiceRaccoon.log");
                                        Debug::trace($appetenceRacProprietaire, false, "ServiceRaccoon.log");
                                    } catch (Exception $e) {
                                        Debug::trace('Erreur segmentation Datascience appetence pro dossier : ' . $dossier->getId().' '.$e->getMessage(), false, 'ServiceRaccoon.log');
                                    }
                                }
                            }

                            $dossierRefinancementTraitement = new Classeur_DossierRefinancement_Traitement($dossier);

                            if (
                                $dossierRefinancementTraitement->getNbTotalCreditImmo() > 0 && 
                                (
                                    $segment1->data === 'IR' 
                                    /*|| ( // On risque de nous le refaire changer très vite (10/07/2025) BM-598
                                        $segment1->data === 'PR3'
                                        && is_array($appetenceRacProprietaire) 
                                        && isset($appetenceRacProprietaire['appetence_rac'])
                                        && isset($appetenceRacProprietaire['appetence_rac']['appetence_rac'])
                                        && $appetenceRacProprietaire['appetence_rac']['appetence_rac'] == 'Profil Non RAC'
                                    )*/
                                )
                            ) {
                                $disablingEmailing = true;
                                $leadTracker->debug("Raccoon traitement : No kiamo call");
                                $leadTracker->debug('Emailing will be disabled');
                                if (!$DEV) {
                                    $raccoon = $container->get('api_raccoon.client');
                                    try {
                                        $raccoon->callApi($dossier, $segment1->data);
                                    } catch (\Exception $e) {
                                        $leadTracker->handleException($e);
                                    }
                                }
                            }
                        }
                        $ignoredSupportsVidata = $PARAM->hasKey('VIDATA_IGNORED_SUPPORTS') ? explode(',', $PARAM->VIDATA_IGNORED_SUPPORTS) : []; // Disable VIDATA for PRI Sources
                        if (
                            !$disablingEmailing &&
                            !in_array($support->getId(), $ignoredSupportsVidata) && // Disable VIDATA for PRI Sources
                            ContainerSingleton::getContainer()->getParameter('VIDATA_ENABLED') == "true"
                        ) {
                            $prioriteCalcul = new PrioriteCalcul_Premium($dossier);
                            $segment1 = $prioriteCalcul->calculePriorite();

                            if (in_array($segment1, ["COEUR DE CIBLE", "RAC +200K", "RAC PREMIUM", "RAC2"/*, "PR1", "PR2"*/])) {
                                if (!$DEV) {
                                    $vidata = $container->get('api_vidata.client');
                                    try {
                                        $vidata->callApi($dossier, $segment1);
                                    } catch (\Exception $e) {
                                        $leadTracker->handleException($e);
                                    }
                                }
                                $isVidata = true;
                                $leadTracker->debug('VIDATA treatment : Kiamo call still enabled');
                                $leadTracker->debug('VIDATA treatment : Emailing will be disabled');
                            }
                        }
                        $leadTracker->debug('Try to send email for CF');
                        if ($disablingEmailing) {
                            $send_mail = false;
                            $leadTracker->debug('Emailing has been disabled');
                        } else {
                            $send_mail = Classeur_DossierRefinancement_Traitement_Central_DAO::TraitementBFI($dossier, false, $kiamoCall, $isCetelem, $isVidata);
                        }
                        if ($isVidata) {
                            $send_mail = false;
                        }
                        $leadTracker->debug('Email will be sent successfully (except if disabled previously)');
                    } catch (Exception $e) {
                        Debug::trace(
                            "LOGS Classeur_DossierRefinancement_Traitement_Central_DAO::TraitementBFI :",
                            false,
                            "debug-affi.log"
                        );
                        Debug::trace($e, false, "debug-affi.log");
                        $leadTracker->handleException($e);
                    }
                } else { //CAS D'UN DOSSIER CHEZ PATRIMIAL
                    if (!$DEV || ($DEV && !empty($_POST['test_dev']))) {
                        $user_dest = Classeur_DossierRefinancement_Repartition_Central::getUserPatrimial($dossier);

                        $proposition = false;
                        if (empty($user_dest)) {
                            $proposition = true;
                            //envoyer Proposition sur RC fictif
                            $user_dest = User_Financial_DAO::getObject($PARAM->PARAM_USER_PROPOSITION);
                            new Classeur_Mail_Client_Proposition_Patrimial($dossier, $user_dest);

                            Debug::trace('Proposition - Passerelle ', false, 'debug-bfi-patrimial.log');
                            $leadTracker->debug('Sending email for patrimial');
                        }

                        $dossier->setStatus(Classeur_DossierRefinancement::EN_COURS_COMMERCIAL);
                        Classeur_DossierRefinancement_DAO::update_status_db($dossier);

                        if (!empty($user_dest)) {
                            if (!$proposition) {
                                $send_mail = true;
                                new Classeur_Mail_Traitement_Patrimial_Client_BFI_Depot(
                                    $dossier->getEmprunteur()->getContact()->getMail(),
                                    $dossier,
                                    $user_dest
                                );
                                Classeur_DossierRefinancement_Repartition_Central::increaseQuotaPatrimial(
                                    $user_dest->getId(),
                                    $dossier
                                );
                            }
                            $remarque = "Affectation BFI envoyé de centralfinances.fr";
                            if ($proposition) {
                                $remarque .= " - Proposition Auto";
                            }
                            Classeur_DossierRefinancement_Traitement_DAO::transfertDossier(
                                $dossier->getId(),
                                $user_dest,
                                date("Y-m-d"),
                                $remarque
                            );

                            $leadTracker->debug($remarque, array_filter(['user_id' => $user_dest->getId()]));
                        } else {
                            Debug::trace('ERR liste Repartition Vide - Passerelle ', false, 'debug-bfi-patrimial.log');
                        }
                    }
                }

                //On détruit la session
                $SESSION->destroy();
            }

            $container = ContainerSingleton::getContainer();
            $logger = $container->get('logger')->withName('MAILJET.SENDER');

            if ($send_mail) {
                try {
                    if (!$PARAM->hasKey("PARAM_CONTACT_MAIL") || !$PARAM->hasKey("MAILJET_TEMPLATE_DEPOT_LEAD")) {
                        $leadTracker->debug("Default mail");

                        $this->sendMail();
                    }
                } catch (Exception $exception) {
                    $leadTracker->handleException($exception);
                    $logger->error('Mailjet : failed to send mail in Service_Passerelle', [$exception->getMessage()]);
                }
            }

            $sql = SQL_SQL::getInstance();

            //PREPARE L'ENVOI D'UN DOSSIER EN MUTUALISATION. SEUL CF MUTUALISE LES DOSSIERS
            $supports = $PARAM->hasKey('SUPPORT_TRAITEMENT_SPECIFIQUE_KIAMO') ? explode(',', $PARAM->SUPPORT_TRAITEMENT_SPECIFIQUE_KIAMO) : [];

            if (empty($PARAM->PARAM_PATRIMIAL) && $dossier->getSupport() && !in_array($dossier->getSupport()->getId(), $supports)) {
                $sql->transaction(SQL_Requete_Financial::getUpdateRequete(
                    330,
                    array($this->getMutualisationName($_POST['cnf']), $dossier->getId())
                ));

                $forceDomainMutualisation = [];
                if(!empty($_POST['tuniform']) && $_POST['tuniform'] == 1) { // dossier Tuniform refusé sur Ymanci
                    debug::Trace($trackingId . ' Force mutualisation du dossier ' . $dossier->getId(), false, 'debug_mutualisation.log');
                    $forceDomainMutualisation = [static::URI_STARTO, static::URI_PATRIMIAL];
                }

                debug::Trace($trackingId . ' Mutualisation du dossier ' . $dossier->getId(), false, 'debug_mutualisation.log');
                $this->mutualiseDossier($dossier, $trackingId, $forceDomainMutualisation);
            } else {
                debug::Trace($trackingId . ' Pas de mutualisation pour le dossier ' . $dossier->getId(), false, 'debug_mutualisation.log');
            }

            if (empty($PARAM->PARAM_PATRIMIAL)){                
                if(!empty($_POST['tuniform']) && $_POST['tuniform'] == 1) { // dossier Tuniform refusé sur Ymanci
                    $dossier->setStatus(Classeur_DossierRefinancement::REFUS_AZUR);
                    Classeur_DossierRefinancement_DAO::update_status_db($dossier);
                }
            }

            //cas d'un leads qui vient de la mutualisation - on garde une clé commune entre tous les mêmes leads qui ont été mutualisé pour pouvoir les croisées
            if (!empty($_POST['uniq_key'])) {
                $sql = SQL_SQL::getInstance();
                $sql->transaction(SQL_Requete_Financial::getUpdateRequete(
                    331,
                    array($_POST['uniq_key'], $dossier->getId())
                ));
            }

            $xml = "1";

        } catch (Exception $e) { //PB discofone ?
            Debug::trace("Err enter dossier : " . $e->getMessage(), false, "debug-affi.log");
            Debug::trace($e, false, "debug-affi.log");

            $leadTracker->handleException($e);
            $xml = "0";
        }

        //output
        $leadTracker->xmlReturned($xml);
        if (!$return) {
            echo $xml;
        } else {
            return $xml;
        }
    }

    /**
     * @param Classeur_DossierRefinancement $dossier
     * @param null                          $trackingId
     *
     * @throws Exceptions_Financial_Classeur_Dossier
     * @throws Exceptions_Financial_Classeur_DossierRefinancement
     * @throws Exceptions_Financial_Local
     * @throws Exceptions_Financial_Parametre
     * @throws Exceptions_Financial_Remarque
     * @throws Exceptions_Financial_Systeme
     * @throws Exceptions_Financial_Update
     * @throws Exceptions_Financial_User
     * @throws Exceptions_Priorite
     */
    protected function mutualiseDossier(Classeur_DossierRefinancement $dossier, $trackingId = null, $forceDomain = [])
    {
        global $PARAM, $DEV, $USER;
        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);

        $transfertDomain = [];

        $priorite_premium = Priorite_DAO::getObjectByDossierByPrioriteByEtat(
            $dossier->getId(),
            Priorite::TYPE_PRIORITE_PREMIUM,
            Priorite::ETAT_AZUR
        );

        $priorite = Priorite_DAO::getObjectByDossierByPrioriteByEtat(
            $dossier->getId(),
            Priorite::TYPE_PRIORITE,
            Priorite::ETAT_AZUR
        );

        $segmentation = Priorite_DAO::getObjectByDossierByPrioriteByEtat(
            $dossier->getId(),
            Priorite::TYPE_PRIORITE_SEGMENTATION,
            Priorite::ETAT_AZUR
        );

        $container = ContainerSingleton::getContainer();


        $logger = $container->get('logger')->withName('SERVICE_PASSERELLE.mutualiseDossier');
        $logger->debug('segmentation return for mutualisation', [$segmentation]);

        $leadTracker->priorityGenerated($priorite_premium, $priorite, $segmentation);

        $dossier_refinancement_traitement = null;

        // Transfert to Cetelem
        if (
            ContainerSingleton::getContainer()->getParameter('SEGMENTATION_CETELEM_ENABLED') == "true"
        ) {
            if ($this->segmentCetelem->data === strtoupper(static::DOMAIN_CETELEM)) {
                Debug::trace('transfert to cetelem ' . $dossier->getId(), false, 'segmentation_cetelem.log');
                $leadTracker->debug("Transfert to cetelem");
                $active = (ContainerSingleton::getContainer()->hasParameter('TRANSFERT_CETELEM_ENABLED') &&
                    ContainerSingleton::getContainer()->getParameter('TRANSFERT_CETELEM_ENABLED') &&
                    $this->canTransfer(static::DOMAIN_CETELEM, $dossier->getSupport()->getSrc(), $this->segmentCetelem->data)
                );
                parent::transfertToCetelem($dossier, $active);
            }
        }

        if (
            !$DEV && (in_array($priorite->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_LOC2,
                Classeur_DossierRefinancement::PRIORITE_RAC1,
                Classeur_DossierRefinancement::PRIORITE_RAC2,
                Classeur_DossierRefinancement::PRIORITE_RAC3
            )))
            && in_array($dossier->getSupport()->getSrc(), array(
                'pid-emul',
                'pid-emul-autre',
                'finanzen',
                'finanzen-autre',
                'finanzen-exclu',
                'caz',
                'ad-p19',
                'biig'
            ))
        ) {

            $dossier_refinancement_traitement = new Classeur_DossierRefinancement_Traitement($dossier);
            $crd = $dossier_refinancement_traitement->getTotalPretConsoCRD() + $dossier_refinancement_traitement->getTotalPretImmoCRD();

            if (in_array($priorite->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_LOC2
            )) && $crd >= 15000 && $crd <= 20000) {
                $_POST['vousfinancer'] = false;
                if (!empty($PARAM->TRANSFERT_BFI_CREDITSOFTLY)) {
                    $leadTracker->debug('Sharing selected', ['domain' => 'creditsoftly.fr', 'process_line' => __LINE__]);
                    $transfertDomain[] = 'creditsoftly.fr';
                }
            }

            // PRIORITE_RAC1, PRIORITE_RAC2, PRIORITE_RAC3
            if (in_array($priorite->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_RAC1,
                Classeur_DossierRefinancement::PRIORITE_RAC2,
                Classeur_DossierRefinancement::PRIORITE_RAC3
            ))) {

                if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_STARTO;
                }

                if (!empty($PARAM->TRANSFERT_BFI_PATRIMIAL)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_PATRIMIAL, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_PATRIMIAL;
                }
            }
        }

        //Seg1
        if (
            !$DEV && (in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_RAC_PREMIUM,
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_COEUR_DE_CIBLE,
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_LOC_PREMIUM,
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_RAC_200K
            )))
            && in_array($dossier->getSupport()->getId(), array(343, 344, 316, 317, 438, 459, 471))
        ) { //finanzen, finanzen-autre, pid emul, pid emul autre, finanzen-exclu, cazelis, advertiseMe-passerelle

            $donnee_emprunteur   = $dossier->getDonneeDossierEmprunteur();
            $donnee_coemprunteur = $dossier->getDonneeDossierCoEmprunteur();

            $revenu = $donnee_emprunteur->getSalaire();
            if (!empty($donnee_coemprunteur)) {
                $revenu += $donnee_coemprunteur->getSalaire();
            }

            if (empty($dossier_refinancement_traitement)) {
                $dossier_refinancement_traitement = new Classeur_DossierRefinancement_Traitement($dossier);
            }

            $crd_conso    = $dossier_refinancement_traitement->getTotalPretConsoCRD();

            //PRIORITE_PREMIUM_RAC_PREMIUM
            if (in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_RAC_PREMIUM
            ))) {

                if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_STARTO;
                }

                if (!empty($PARAM->TRANSFERT_BFI_PATRIMIAL)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_PATRIMIAL, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_PATRIMIAL;
                }
            }

            //PRIORITE_PREMIUM_COEUR_DE_CIBLE
            if (in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_COEUR_DE_CIBLE
            ))) {

                if (!empty($PARAM->TRANSFERT_BFI_PATRIMIAL)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_PATRIMIAL, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_PATRIMIAL;
                }

                if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_STARTO;
                }
            }

            //PRIORITE_PREMIUM_RAC_200K
            if (in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_RAC_200K
            )) && $crd_conso >= 25000 && $crd_conso <= 100000) {

                if (!empty($PARAM->TRANSFERT_BFI_PATRIMIAL)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_PATRIMIAL, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_PATRIMIAL;
                }

                if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_STARTO;
                }
            }

            //PRIORITE_PREMIUM_LOC_PREMIUM
            if (in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_LOC_PREMIUM
            ))) {

                if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_STARTO;
                }
                if (!empty($PARAM->TRANSFERT_BFI_PATRIMIAL)) {
                    $leadTracker->debug('Sharing selected', ['domain' => static::URI_PATRIMIAL, 'process_line' => __LINE__]);
                    $transfertDomain[] = static::URI_PATRIMIAL;
                }
            }
        }

        /** Ajout d'une possibilité de forcer la mutualisation vers un ou plusieurs domaines particuliers */
        foreach ($forceDomain as $domain) {
            switch ($domain) {
                case static::URI_STARTO:
                case static::URI_PATRIMIAL:
                    $transfertDomain[] = $domain;
            }
        }

        /*if (!$DEV && ( in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_COEUR_DE_CIBLE
            )))
            && in_array($dossier->getSupport()->getId(), array(292))
        ) { //DevisProx

            if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
                $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
                $transfertDomain[] = static::URI_STARTO;
            }

            if (!empty($PARAM->TRANSFERT_BFI_PATRIMIAL)) {
                $leadTracker->debug('Sharing selected', ['domain' => static::URI_PATRIMIAL, 'process_line' => __LINE__]);
                $transfertDomain[] = static::URI_PATRIMIAL;
            }
        }

        if (
            !$DEV && ((in_array($priorite_premium->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_RAC_PREMIUM,
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_COEUR_DE_CIBLE,
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_LOC_PREMIUM,
                Classeur_DossierRefinancement::PRIORITE_PREMIUM_RAC_200K
            ))) || (in_array($priorite->getValeur(), array(
                Classeur_DossierRefinancement::PRIORITE_LOC2
            ))))
            && (in_array(substr($dossier->getEmprunteur()->getAdresse()->getCP(), 0, 2), array(22, 29, 35, 56)))
            && (in_array($dossier->getSupport()->getId(), array(293, 292))) //Assuragency devisprox
        ) {
            if (!empty($PARAM->TRANSFERT_BFI_ACTIONISTA)) {

                $leadTracker->debug('Sharing selected', ['domain' => 'actionista.fr', 'process_line' => __LINE__]);
                $transfertDomain[] = 'actionista.fr';
                Debug::trace('Transfert Actionista : ' . $dossier->getEmprunteur()->getMailAdresse(), false, 'debug-mutualisation-actionista.log');
            }
        }*/

        /*
        * Partie devisprox
         * code commenté    : désactivation mutualisation devisprox pour Starto
         * code décommenté  : activation mutualisation devisprox pour Starto
        * On refuse (status : 'refus_azur') le dossier si il part chez starto en mettant comme remarque : "Lead traité par STARTO" (sur l'user admin)
        */
        // if ($dossier->getSupport()->getId() == 292) {
        //     if (
        //         $this->canTransfer(
        //             static::DOMAIN_STARTO,
        //             $dossier->getSupport()->getSrc(),
        //             $segmentation->getValeur()
        //         )
        //     ) {
        //         if (
        //             !in_array(
        //                 $segmentation->getValeur(),
        //                 [
        //                     Classeur_DossierRefinancement::PRIORITE_SEGMENTATION_1L3B,
        //                     Classeur_DossierRefinancement::PRIORITE_SEGMENTATION_1L2B
        //                 ]
        //             )
        //         ) {

        //             //Ajout refus azur en suivi
        //             $dossier->setStatus('refus_azur');
        //             $dossier->setRemarqueAzur(new Remarque('Lead traité par STARTO'));
        //             $dossier->setRemarqueCommerciale(new Remarque('Lead traité par STARTO'));
        //             Classeur_Dossier_DAO::update_db($dossier);
        //             $suivi = new Suivi(date('Y-m-d'), $USER, new Remarque('Lead traité par STARTO'));
        //             Suivi_DAO::insert_with_dossier_db($suivi, $dossier);

        //             //Création de l'objet financial_modification
        //             $modif = new Update(date('Y-m-d'), $USER, new Remarque('Lead traité par STARTO'));
        //             Update_DAO::insert_with_dossier_db($modif, $dossier);

        //             //Creation de l'objet detail refus (financial_refus_detail)
        //             $detailRefus = DetailRefus_DAO::getObject(114);

        //             $refus = new Classeur_Refus($modif, $detailRefus, new Remarque('Lead traité par STARTO'));
        //             Classeur_Refus_DAO::insert_with_dossier_refinancement_db($refus, $dossier);

        //             //Add row to financial_dossier_suivi
        //             Classeur_DossierRefinancement_Suivi_DAO::insert_user_dossier_db($dossier, $USER);
        //         }
        //         if (!empty($PARAM->TRANSFERT_BFI_STARTO)) {
        //             $leadTracker->debug('Sharing selected', ['domain' => static::URI_STARTO, 'process_line' => __LINE__]);
        //             $transfertDomain[] = static::URI_STARTO;
        //         }
        //     }
        // }

        if ($PARAM->hasKey('TRANSFERT_BFI_CREDITDOM') && $PARAM->TRANSFERT_BFI_CREDITDOM) {
            $dossier_refinancement_traitement = new Classeur_DossierRefinancement_Traitement($dossier);
            $crd = $dossier_refinancement_traitement->getTotalPretConsoCRD() + $dossier_refinancement_traitement->getTotalPretImmoCRD();
            $donnee_emprunteur   = $dossier->getDonneeDossierEmprunteur();
            $donnee_coemprunteur = $dossier->getDonneeDossierCoEmprunteur();

            $typeContrat = !empty($donnee_emprunteur->getContrat()) ? strtolower($donnee_emprunteur->getContrat()->getLibelle()) : '';
            $typeContratCj = !empty($donnee_coemprunteur) && !empty($donnee_coemprunteur->getContrat()) ? strtolower($donnee_coemprunteur->getContrat()->getLibelle()) : '';
            $profession = strtolower($donnee_emprunteur->getProfession());
            $professionCj = !empty($donnee_coemprunteur) ? strtolower($donnee_coemprunteur->getProfession()) : '';

            $nbPretImmo = count($donnee_emprunteur->getListPretImmo());
            $nbPretConso = count($donnee_emprunteur->getListPretConso());
            if (
                $crd >= 18000
                && (substr($dossier->getEmprunteur()->getAdresse()->getCP(), 0, 3) == 972
                    || (substr($dossier->getEmprunteur()->getAdresse()->getCP(), 0, 3) == 971
                        && $dossier->getTypeDossier() == 'proprietaire'
                    ))
                && ($donnee_emprunteur->getSalaire() >= 1800)
                && (empty($donnee_coemprunteur) || (!empty($donnee_coemprunteur) && $donnee_coemprunteur->getSalaire() >= 700))
                && $nbPretImmo + $nbPretConso >= 2
                && $nbPretConso >= 1
                && (in_array($typeContrat, ['cdi', 'retraite'])
                    || in_array($typeContratCj, ['cdi', 'retraite'])
                    || $profession == 'retraité'
                    || stripos($profession, 'fonction') !== false
                    || stripos($professionCj, 'fonction') !== false
                )
            ) {
                $leadTracker->debug('Sharing selected', ['domain' => 'creditdom.fr', 'process_line' => __LINE__]);
                $transfertDomain[] = 'creditdom.fr';
                Debug::trace(
                    'Transfert CréditDom : ' . $dossier->getEmprunteur()->getMailAdresse(),
                    false,
                    'debug-mutualisation-creditdom.log'
                );
            } elseif (
                substr($dossier->getEmprunteur()->getAdresse()->getCP(), 0, 3) == 972
                || (substr($dossier->getEmprunteur()->getAdresse()->getCP(), 0, 3) == 971
                    && $dossier->getTypeDossier() == 'proprietaire'
                )
            ) {
                Debug::trace(
                    'Erreur transfert creditdom : ' . $dossier->getEmprunteur()->getMailAdresse(),
                    false,
                    'debug-mutualisation-creditdom.log'
                );
                if ($crd < 18000) {
                    Debug::trace('ERROR CRD', false, 'debug-mutualisation-creditdom.log');
                }
                if ($donnee_emprunteur->getSalaire() < 1800) {
                    Debug::trace('ERROR SALAIRE', false, 'debug-mutualisation-creditdom.log');
                }
                if (!empty($donnee_coemprunteur) && $donnee_coemprunteur->getSalaire() < 700) {
                    Debug::trace('ERROR SALAIRE CONJOINT', false, 'debug-mutualisation-creditdom.log');
                }
                if ($nbPretImmo + $nbPretConso < 2) {
                    Debug::trace('ERROR NB PRETS', false, 'debug-mutualisation-creditdom.log');
                }
                if ($nbPretConso < 1) {
                    Debug::trace('ERROR NB PRETS CONSO', false, 'debug-mutualisation-creditdom.log');
                }
                if ($nbPretConso < 1) {
                    Debug::trace('ERROR NB PRETS CONSO', false, 'debug-mutualisation-creditdom.log');
                }
                if (!(in_array($typeContrat, ['cdi', 'retraite'])
                    || in_array($typeContratCj, ['cdi', 'retraite'])
                    || $profession == 'retraité'
                    || stripos($profession, 'fonction') !== false
                    || stripos($professionCj, 'fonction') !== false
                )) {
                    Debug::trace('ERROR CONTRAT', false, 'debug-mutualisation-creditdom.log');
                }
            }
        }

        $seg5 = Classeur_DossierRefinancement_DAO::getSegmentationDatascienceV5($dossier->getId());

        if (
            $this->mutualisationSeg3DataScience(static::DOMAIN_STARTO, $trackingId, $dossier, $segmentation)
            || $this->mutualisationSeg5DataScience(static::DOMAIN_STARTO, $trackingId, $dossier, $seg5)
        ) {
            $transfertDomain[] = static::URI_STARTO;
        }

        if ($this->mutualisationSeg3DataScience(static::DOMAIN_PATRIMIAL, $trackingId, $dossier, $segmentation)) {
            $transfertDomain[] = static::URI_PATRIMIAL;
        }
        
        if(!empty($_POST['tuniform']) && $_POST['tuniform'] == 1) {
            $transfertDomain[] = static::URI_PATRIMIAL;
            $transfertDomain[] = static::URI_STARTO;
        }

        if (!empty($transfertDomain)) {
            $transfertDomain = array_unique($transfertDomain);
            $_POST['uniq_key'] = $this->getMutualisationName($_POST['cnf']) . md5(mt_rand(0, 999999999999999) . mt_rand(0, 999999999999999));
            $_POST['origin']   = $this->getMutualisationName($_POST['cnf']);
            $_POST['trackingId'] = $trackingId;

            if (!empty($PARAM->PARAM_COVID19) && $PARAM->PARAM_COVID19 && $dossier->getTypeDossier() != Classeur_DossierRefinancement::LOCATAIRE) {
                try {
                    $leadTracker->warning('Refusing dossier COVID-19');

                    //On clot le dossier pour raison de Covid
                    ContainerSingleton::getContainer()->get('covid.closer')->closeDossier(
                        $dossier,
                        CovidCloser::REASON_MUTUALISATION,
                        true
                    );
                    ContainerSingleton::getContainer()->get('covid.closer')->transfertDossierAzurCovid(
                        $dossier->getId()
                    );
                } catch (\Exception $exception) {
                    $leadTracker->handleException($exception);
                }
            }
        }

        foreach ($transfertDomain as $domain) {
            $this->transfertToAnotherDomaine($domain, $dossier, $trackingId);
            $leadTracker->sharingWithDomain($domain);
            Debug::trace('transfert Mutualisation Passerelle ' . $domain . ': ' . $_POST['mail'], false, 'debug-bfi-mutualisation-affi.log');
        }


        $_POST['origin'] = "";
    }

    /**
     * Permet de determiner si le dossier correspond aux critères minimum
     *
     * @param
     *
     * @return Boolean
     */
    protected function isValide(Classeur_DossierRefinancement &$dossier)
    {
        return true;
    }

    private function loadCritere($src, $TRACKING_KEY)
    {
        if (empty($src)) {
            throw new RuntimeException('SRC manquant pour le chargement du critère');
        }
        $critere = LeadCritereDAO::getCritereDAO($src);
        if (!$critere) {
            throw new RuntimeException(sprintf('Aucun critère trouvé pour la source "%s"',$src));
        }
        Debug::trace("lead associated : $TRACKING_KEY", false, 'leadCritere.log');          // log lead ID
        Debug::trace($critere, false, 'leadCritere.log');                                   // log critere obj
        return $critere;
    }

private function formatXml($code)
{
    // ✅ si c'est déjà un XML complet → on le retourne directement
    if (is_string($code) && strpos($code, '<root>') !== false) {
        return $code;
    }

    // ✅ extraire un code numérique même si on reçoit du texte
    if (is_string($code) && preg_match('/-?\d+/', $code, $matches)) {
        $code = (int)$matches[0];
    }

    $code = (int)$code;

    // ✅ reconstruction propre
    return '<?xml version="1.0" standalone="yes"?>'
        . '<root>'
        . ($code === 1
            ? '<ok>1</ok>'
            : '<err>' . $code . '</err>')
        . '</root>';
}

    /**
     * @author Vincent Fringant
     */
    private function isDoublonWithCritere($critere)
    {
        $duree = (int) $critere->get("doublonDuree");
        $dateTime = new DateTime();

        if ($duree > 0) {
            $dateTime->modify('-' . $duree . ' months');
        }
        $date = $dateTime->format('Y-m-d');
        $result = $this->isDoublonMail($date);
        return $result;
    }

    private function sanitizeInput($post, $get)
    {
        $post['src'] = $get['src'];
        return [
            'post' => array_map('trim', $post),
            'get'  => array_map('trim', $get),
            'src'  => $get['src'],
        ];
    }

    /**
     * Handle lead result and return XML response
     *
     * @param mixed        $err
     * @param mixed        $result
     * @param object       $leadTracker
     * @param string|null  $mail
     * @param string       $logFile
     *
     * @return string XML
     */
    private function handleLeadResult($err,$result,$leadTracker,$mail = null,$logFile)
    {
        if (!empty($err)) {
            if ($err == -1) {
                $leadTracker->handleException(
                    new RuntimeException('Load affiliate error')
                );
                Debug::trace("Error : " . $err . " " . $mail,false,$logFile);
            }
            return "<err>" . $err . "</err>";
        }
        if (empty($result)) {
            $leadTracker->handleException(new RuntimeException('Empty enterDossier result'));
            Debug::trace("enterDossier return null : " . $mail,false,$logFile);
            return "<err>-1</err>";
        }
        $leadTracker->validate();
        return "<ok>1</ok>";
    }

    /**
     * Permet de Vérfier si un dossier passe ou pas dans le filtre
     *
     *
     * @throws Exceptions_Financial_Systeme Data error
     */
    public function checkFluxAffi()
    {
        global $LOCAL, $PARAM, $USER, $trackingId;

        $leadTracker = getTracker();
        $logFile = 'debug-affi-check.log';
        try {
            $LOCAL = 1;
            $PARAM = Parametre_DAO::getObject(0);
            $USER  = $this->admin;

            Debug::trace($_POST, false, $logFile);
            $data = $this->sanitizeInput($_POST, $_GET);
            $critere = $this->loadCritere($data['src'], $_POST['TRACKING_KEY']);    // log include critere > leadCritere.log
            $lead = $this->NormalizeLead($data['post'], $logFile);                  // log include
            $_POST = $lead;


            $this->initDB();
            $this->checkDossier();
            $err = $this->EvaluateLeadWithCritere($lead, $critere, $leadTracker, $logFile);  // log include

            $xml = $this->handleLeadResult($err, $result = TRUE, $leadTracker, @$lead['mail'], $logFile);
            
        } catch (Exception $e) {
            $leadTracker->handleException($e);
            Debug::trace("Exceptions : ", false, $logFile);
            Debug::trace($e, false, $logFile);
            $xml = $this->formatXml('-1 Exp');
        }

        $leadTracker->xmlReturned($xml);
        if (!empty($xml)) {
            Debug::trace('RESULT  (editeur ' . @$_GET['ed'] . '): ' . $xml, false, $logFile);
            echo $this->formatXml(str_replace(['<err>', '</err>'], '', $xml)); //output
        }
    }

    /**
     * Permet de récupérer le flux de données type ASSURE PROX pour Public Id
     *
     * @throws Exceptions_Financial_Systeme Data error
     */
    public function loadFluxAffi(){
        global $LOCAL, $PARAM, $USER, $SESSION, $MAIL_DEV, $MAIL_PROD, $trackingId;
        
        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $logFile = 'debug-affi.log';
        $log = [];
        $_POST["email"] = array_key_exists('email', $_POST) ? strtolower($_POST["email"]) : '';
        $log["lead"] = [];
        $log["monitoring"] = [];
        $log["result"] = [];
        $log["source"] = "";
        $log["lead"]["mail"] = $_POST["email"];
        $log["monitoring"]["reception du lead"] = date("y-m-d H:i:s");
        Debug::startProfiler("Réception du lead", "startLead");
        Debug::startProfiler("Traitement données", "stepTraitement");

        try {
            $LOCAL = 1;
            $PARAM = Parametre_DAO::getObject(0);
            $USER = $this->admin;

            Debug::trace('RAW LEAD', false, $logFile);  
            Debug::trace($_POST, false, $logFile);                                       // log raw lead
        
            $data = $this->sanitizeInput($_POST, $_GET);
            $log["source"] = $data['src'];
            $critere = $this->loadCritere($data['src'], $_POST["TRACKING_KEY"]);         // log critere > leadCritere.log incluce
            $lead = $this->NormalizeLead($data['post'], $logFile);                       // log normalized lead > logFile include
            $_POST = $lead;


            $this->initDB();
            $this->checkDossier();

            $err = $this->EvaluateLeadWithCritere($lead, $critere, $leadTracker, $logFile);  // log include
            $send_err = false;
            $dossier_type = "LOC";
            $nb_credits   = 2;
            if (@$lead['proprietaire']) {
                $dossier_type = "PRO";

                //nb credits pro
                $nb_credits = count($lead['type_credit']);
                if ($nb_credits >= 3) {
                    $nb_credits = 3;
                }
            }
            $dossier_type .= ";" . $nb_credits;

            $log["monitoring"]["verification criteres"] = round(Debug::stopProfiler("stepCriteres"), 2);
            Debug::startProfiler("Insertion du dossier", "stepEnterDossier");

            if ((is_object($critere) && $critere->get('recette') !== true)) { //permet de travailler en phase de recette
                if (empty($err) || (!empty($err) && $err != -6 && $send_err)) {
                    if ($PARAM->hasKey("PASSERELLE_QUEUE_ENABLED") && $PARAM->PASSERELLE_QUEUE_ENABLED) {
                        $this->checkDossier();
                        $data = ['POST' => $_POST, 'GET' => $_GET,
                            'ARGS' => [empty($err), empty($err), true],
                            'trackingId' => $trackingId ];
                        $result = $this->getPasserelleQueue()->insert(serialize($data), Service_Passerelle::class);
                    } else {
                        $result = $this->enterDossier(empty($err), empty($err), true, $trackingId);
                    }
                }
            } else {
                $result = true;
            }
            $log["monitoring"]["enterDossier"] = round(Debug::stopProfiler("stepEnterDossier"), 2);

            $xml = $this->handleLeadResult($err, $result, $leadTracker, @$lead['mail'], $logFile);
            
        } catch (Exception $e) {
            Debug::trace("Exceptions : ", false, $logFile);
            Debug::trace($e, false, $logFile);
            $xml = $this->formatXml('-1 Exp');
            $log["result"]["success"] = false;
            $log["result"]["exception"] = $e->getMessage();
            $leadTracker->handleException($e);
        }

        if (!empty($xml)) {
            Debug::trace('RESULT  (editeur ' . @$_GET['ed'] . '): ' . $xml, false, $logFile);
            if (!$critere->get('recette')) {
                Debug::trace('(Lead precedent en recettage, pas dans la DB)' . $xml, false, $logFile);
            }
            echo $xml= $this->formatXml(str_replace(['<err>', '</err>'], '', $xml)); //output
        }
        $leadTracker->xmlReturned($xml);
        if (!isset($log["success"])) {$log["result"]["success"] = true;}
        $log["result"]["xml"] = $xml;
        $log["monitoring"]["duration"] = round(Debug::stopProfiler("startLead"), 2);
        $log["monitoring"]["fin du traitement"] = date("Y-m-d H:i:s");
        Debug::trace(json_encode($log, JSON_PRETTY_PRINT), false, "monitoring_loadFluxAffi.log");
    }

    /**
     * Permet de récupérer le flux de données type LEADS FR en version anonymisée
     *
     * @throws Exceptions_Financial_Systeme Data error
     */
    public function loadFluxAffiAnon()
    {
        global $LOCAL, $PARAM, $USER, $SESSION, $MAIL_DEV, $MAIL_PROD, $trackingId;
        
        
        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $log = [];
        $_POST["email"] = array_key_exists('email', $_POST) ? strtolower($_POST["email"]) : '';
        Debug::trace($_POST, false, "debug-affi-anon.log"); // log donnée
        $_POST['src'] = @$_GET['src'];
        $log["lead"] = [];
        $log["monitoring"] = [];
        $log["result"] = [];
        $log["source"] = "";
        $log["lead"]["mail"] = $_POST["email"];
        $log["monitoring"]["reception du lead"] = date("y-m-d H:i:s");
        Debug::startProfiler("Réception du lead", "startLead");
        Debug::startProfiler("Traitement données", "stepTraitement");
        $logFile = 'debug-affi-anon.log';

        try {
            $LOCAL = 1;
            $PARAM = Parametre_DAO::getObject(0);
            $USER = $this->admin;
            Debug::trace($_POST, false, $logFile);                                              // log raw lead
        
            $data = $this->sanitizeInput($_POST, $_GET);
            $log["source"] = $data['src'];
            $critere = $this->loadCritere($data['src'], $_POST['TRACKING_KEY']);                // log include critere > leadCritere.log
            // Debug::trace(json_encode($critere->get('anon')), false, 'leadCritere.log');      // log autorize anonymosation
             if (!$critere->get('anon'))
            { 
                $this->rejectOnce($err,-1,null,$leadTracker);
                $this->debugTrace($logFile, $_POST['TRACKING_KEY'], LeadCritMapping::COLUMN['ANON']);  // log logFile
            } else {    
                $_POST = $this->NormalizeLead($data['post'], $logFile);                         // log include normalized lead > logFile
                $_POST['tel1'] = '0000000000';
                $_POST['mail'] = 'anonymisation' . date('U') . '@premista.fr';

                $this->initDB();
                $this->checkDossier();

                $log["monitoring"]["traitement donnees"] = round(Debug::stopProfiler("stepTraitement"), 2);
                Debug::startProfiler("Test criteres", "stepCriteres");
                
                $err = $this->EvaluateLeadWithCritere($_POST, $critere, $leadTracker, $logFile);  // log include
                $send_err = false;

                $dossier_type = "LOC";
                $nb_credits   = 2;
                if (@$_POST['proprietaire']) {
                    $dossier_type = "PRO";
                    //nb credits pro
                    $nb_credits = count($_POST['type_credit']);
                    if ($nb_credits >= 3) {
                        $nb_credits = 3;
                    }
                }
                $dossier_type .= ";" . $nb_credits;

                $log["monitoring"]["verification criteres"] = round(Debug::stopProfiler("stepCriteres"), 2);
                Debug::startProfiler("Insertion du dossier", "stepEnterDossier");
                $xml = $this->retrieveAppetenceRacProprietaire(empty($err), empty($err), true, $trackingId);
                $result = false;
            }

            if (empty($err) || (!empty($err) && $err != -6 && $send_err)) {
                $result = ($xml == "1");
            }
            $log["monitoring"]["enterDossier"] = round(Debug::stopProfiler("stepEnterDossier"), 2);
            
            $xml = $this->handleLeadResult($err, $result, $leadTracker, @$_POST['mail'], $logFile);
            
        } catch (Exception $e) {
            Debug::trace("Exceptions : ", false, 'debug-affi-anon.log');
            Debug::trace($e, false, 'debug-affi-anon.log');
            $xml = $this->formatXml('-1 Exp');
            $log["result"]["success"] = false;
            $log["result"]["exception"] = $e->getMessage();
            $leadTracker->handleException($e);
        }

        if (!empty($xml)) {
            Debug::trace('RESULT  (editeur ' . @$_GET['ed'] . '): ' . $xml, false, $logFile);
            echo $xml= $this->formatXml(str_replace(['<err>', '</err>'], '', $xml)); //output
        }
        $leadTracker->xmlReturned($xml);

        if (!isset($log["success"])) {
            $log["result"]["success"] = true;
        }
        $log["result"]["xml"] = $xml;
        $log["monitoring"]["duration"] = round(Debug::stopProfiler("startLead"), 2);
        $log["monitoring"]["fin du traitement"] = date("Y-m-d H:i:s");
        Debug::trace(json_encode($log, JSON_PRETTY_PRINT), false, "monitoring_loadFluxAffi_Anon.log");
    }

    /**
     * Permet de récupérer le flux de données Tuniform
     *
     * @throws Exceptions_Financial_Systeme Data error
     */
    public function loadFluxTuniform()
    {
        global $LOCAL, $PARAM, $USER, $SESSION, $MAIL_DEV, $MAIL_PROD, $trackingId;
        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $log = [];
        $log["lead"] = [];
        $log["monitoring"] = [];
        $log["result"] = [];
        $log["source"] = "";
        $log["lead"]["mail"] = $_POST["email"];
        $log["monitoring"]["reception du lead"] = date("y-m-d H:i:s");
        Debug::startProfiler("Réception du lead", "startLead");
        Debug::startProfiler("Traitement données", "stepTraitement");
        $logFile = 'debug-tuniform.log';
        try {
            $LOCAL = 1;
            $PARAM = Parametre_DAO::getObject(0);
            $USER  = User_Financial_DAO::getObject(100);
            Debug::trace($_POST, false, $logFile);
        
            $data = $this->sanitizeInput($_POST, $_GET);
            $log["source"] = $data['src'];
            $critere = $this->loadCritere($data['src'], $_POST['TRACKING_KEY']);  // log include critere > leadCritere.log

            if (empty($_POST['statutlgmt'])) {
                $_POST['statutlgmt'] = 'propriétaire';
            }
            $_POST = $this->NormalizeLead($data['post'], $logFile);                // log include normalized lead > logFile
            $_POST['tuniform'] = 1; // spe on tuni

            $this->initDB();
            $this->checkDossier();
            if ($this->isDoublonWithCritere($critere)) {
                $leadTracker->rejectLead(new DoubledLead($_POST['mail']));
                Debug::trace('ERROR > Doublon lead: '.$_POST['TRACKING_KEY'].' - Email: '.$_POST['mail'], false, $logFile);  // logFile
                $xml = $this->formatXml(-2);
            } else {
                $transfertDomain = [static::URI_PATRIMIAL, static::URI_STARTO];
                //On ajoute les informations de tracking communes
                $_POST['uniq_key'] = 'TUNIFORM' . uniqid();
                $_POST['trackingId'] = $trackingId;
                foreach ($transfertDomain as $domain) {
                    switch($domain){
                        case static::URI_PATRIMIAL:
                            $_POST['origin'] = implode(',', array_map([$this, 'getMutualisationName'], $transfertDomain));
                            break;
                        case static::URI_STARTO:
                            $_POST['origin'] = implode(',', array_map([$this, 'getMutualisationName'], array_diff($transfertDomain, [$domain])));
                            break;
                    }
                    $this->transfertToAnotherDomaine($domain, null, $trackingId);
                    $leadTracker->sharingWithDomain($domain);
                    Debug::trace('Transfert Mutualisation Passerelle ' . $domain . ': ' . $_POST['mail'], false, 'debug-bfi-mutualisation-affi.log');
                }
                $xml = $this->formatXml(1);
            }
        } catch (Exception $e) {
            Debug::trace("Exceptions : ", false, $logFile);
            Debug::trace($e, false, $logFile);
            $xml = $this->formatXml('-1 Exp');
            $log["result"]["success"] = false;
            $log["result"]["exception"] = $e->getMessage();
            $leadTracker->handleException($e);
        }

        if (!empty($xml)) {
            Debug::trace('RESULT  (editeur ' . @$_GET['ed'] . '): ' . $xml, false, "debug-tuniform.log");
            echo $xml; //output
        }
        $leadTracker->xmlReturned($xml);

        if (!isset($log["success"])) {
            $log["result"]["success"] = true;
        }
        $log["result"]["xml"] = $xml;
        $log["monitoring"]["duration"] = round(Debug::stopProfiler("startLead"), 2);
        $log["monitoring"]["fin du traitement"] = date("Y-m-d H:i:s");
        Debug::trace(json_encode($log, JSON_PRETTY_PRINT), false, "monitoring_loadFluxAffiTuniform.log");
    }

    /**
     * Permet de récupérer le flux de données Devis PROX
     *
     *
     * @throws Exceptions_Financial_Systeme Data error
     */
    public function loadFluxDevisProx()
    {
        global $LOCAL, $PARAM, $USER, $trackingId;
        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $logFile = 'debug-devisprox.log';

        try {
            $leadTracker->debug('starting loadFluxDevisProx');
        
            $data = $this->sanitizeInput($_POST, $_GET);
            $log["source"] = $data['src'];
            $critere = $this->loadCritere($data['src'], $_POST['TRACKING_KEY']);                 // log include critere > leadCritere.log
            $critere = LeadCritereHydrator::hydrateToArray($critere);

            $_POST = $this->NormalizeLead($data['post'], $logFile);                             // log include normalized lead > logFile          
            $_POST['idq'] = isset($post['idq']) ? (int) $post['idq'] : self::IDQ_DEVISPROX;     // spe on devisprox
            Debug::trace($_POST, false, $logFile);

            if ($_POST['idq'] == self::IDQ_DEVISPROX) {
                $xml = 0;
                $type = isset($_POST['proprietaire']) ? 'PRO' : 'LOC';

                switch ($_POST['src']) {
                    case 'devisprox_premium':
                        if (
                            (intval($_POST['age_vous']) >= $critere[LeadCritMapping::COLUMN['AGE_MIN']])
                            && (!empty($tmp['crd_conso']) && $tmp['crd_conso'] >= $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]])
                            && ($tmp['nbConso'] >= $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]])
                            && (!isset($tmp['hebergement_gratuit']) || $tmp['hebergement_gratuit'] === false)
                            && (
                                (!empty($tmp['locataire']) && $tmp['locataire'] == true
                                    && (
                                        (empty($tmp['nom_conjoint']) && $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_LOC']])
                                        || (!empty($tmp['nom_conjoint']) && $tmp['revenus_conjoint'] + $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_FOYER_LOC']])
                                    )
                                ) || (!empty($tmp['proprietaire']) && $tmp['proprietaire'] == true
                                    && (
                                        (empty($tmp['nom_conjoint']) && $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_PRO']])
                                        || (!empty($tmp['nom_conjoint']) && $tmp['revenus_conjoint'] + $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_FOYER_PRO']])
                                    )
                                )
                            ) && $this->isCDIFilterForDevisprox($tmp) && !$this->isDomtomForDevisprox($tmp)
                        ) {
                            $xml = $this->initDevisprox($trackingId, $critere);
                        }
                        break;
                    case 'devisprox_exclu':
                        if (
                            (intval($_POST['age_vous']) >= $critere[LeadCritMapping::COLUMN['AGE_MIN']])
                            && (!empty($tmp['crd_conso']) && $tmp['crd_conso'] >= $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]])
                            && ($tmp['nbConso'] >= $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO'.$type]])
                            && ($tmp['nbConso'] + $tmp['nbImmo'] >= 3)
                            && (!isset($tmp['hebergement_gratuit']) || $tmp['hebergement_gratuit'] === false)
                            && (
                                (!empty($tmp['locataire']) && $tmp['locataire'] == true
                                    && (
                                        (empty($tmp['nom_conjoint']) && $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_LOC']])
                                        || (!empty($tmp['nom_conjoint']) && $tmp['revenus_conjoint'] + $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_FOYER_LOC']])
                                    )
                                ) || (!empty($tmp['proprietaire']) && $tmp['proprietaire'] == true
                                    && (
                                        (empty($tmp['nom_conjoint']) && $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_PRO']])
                                        || (!empty($tmp['nom_conjoint']) && $tmp['revenus_conjoint'] + $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_FOYER_PRO']])
                                    )
                                )
                            )
                            && $this->isCDIFilterForDevisprox($tmp) && !$this->isDomtomForDevisprox($tmp)
                        ) {
                            $xml = $this->initDevisprox($trackingId, $critere);
                        }
                        break;
                    case 'devisprox':
                        if (
                            (intval($_POST['age_vous']) >= $critere[LeadCritMapping::COLUMN['AGE_MIN']])
                            && ($_POST['nbConso'] + $_POST['nbImmo'] > 1)
                            && (!isset($tmp['hebergement_gratuit']) || $tmp['hebergement_gratuit'] === false)
                            && (
                                (!empty($tmp['locataire']) && $tmp['locataire'] == true
                                    && ($tmp['crd_conso'] + (empty($tmp['crd_immo']) ? 0 : $tmp['crd_immo']) >= $critere[LeadCritMapping::COLUMN['CRD_MIN_LOC']])
                                    && (
                                        (empty($tmp['nom_conjoint']) && $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_LOC']])
                                        || (!empty($tmp['nom_conjoint']) && $tmp['revenus_conjoint'] + $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['CRD_MIN_FOYER_LOC']])
                                    )
                                ) || (!empty($tmp['proprietaire']) && $tmp['proprietaire'] == true
                                    && (!empty($tmp['crd_conso']) && $tmp['crd_conso'] >= $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_PRO']])
                                    && ($tmp['crd_conso'] + (empty($tmp['crd_immo']) ? 0 : $tmp['crd_immo']) >= $critere[LeadCritMapping::COLUMN['CRD_MIN_PRO']])
                                    && (
                                        (empty($tmp['nom_conjoint']) && $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_PRO']])
                                        || (!empty($tmp['nom_conjoint']) && $tmp['revenus_conjoint'] + $tmp['revenus_vous'] >= $critere[LeadCritMapping::COLUMN['REV_MIN_FOYER_PRO']])
                                    )
                                )
                            ) && $this->isCDIFilterForDevisprox($tmp) && !$this->isDomtomForDevisprox($tmp)
                        ) {
                            $xml = $this->initDevisprox($trackingId, $critere);
                        }
                        break;
                }
                
                if ($xml == 0) {$xml = 'KO';}
            } else {
                $leadTracker->rejectLead(new Idq());
                $xml = 'KO';
            }
        } catch (Exception $e) {
            $leadTracker->handleException($e);
            $xml = 'KO';
        }

        if (!empty($xml)) {
            if ($xml == 1) {
                $leadTracker->validate();
                $xml = 'OK';
            }
            echo $xml;
        }
        $leadTracker->xmlReturned($xml);
    }

    public function retrieveAppetenceRacProprietaire($valid = 1, $send_mail = true, $return = false, $trackingId = "", $src = '')
    {
        global $PARAM, $DEV, $LOCAL, $USER, $SESSION;

        $xml = "0";

        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $leadTracker->debug('enter retrieve appetence rac proprietaire');

        try {
            $container = ContainerSingleton::getContainer();

            $this->initDB();
            $this->checkDossier();

            $LOCAL = 1;

            $PARAM = Parametre_DAO::getObject(0);

            //check passerelle autorisation
            // $support = $this->retrieveSupport();
            $appetenceRac = $this->getAppetenceRacProprietaireFromTemporaryDossierFinancial();

            $filtreAppetenceRac = [];
            if (!empty($PARAM->APPETENCE_RAC_LEAD_ANONYMISE)) {
                $filtreAppetenceRac = explode(",", $PARAM->APPETENCE_RAC_LEAD_ANONYMISE);
            }

            //if (is_array($appetenceRac) && in_array($appetenceRac['appetence_rac']['appetence_rac'], $filtreAppetenceRac)) {
            if (is_array($appetenceRac) && in_array($appetenceRac['appetence_rac'], $filtreAppetenceRac)) {
                $xml = "1";
            }
        } catch (Exception $e) { //PB discofone ?
            Debug::trace("Err enter dossier : " . $e->getMessage(), false, "debug-affi-anon.log");
            Debug::trace($e, false, "debug-affi-anon.log");
        }

        //output
        if (!$return) {
            echo $xml;
        } else {
            return $xml;
        }
    }

    /**
     * Check for doublon for a specific email
     */
    public function checkMail()
    {
        global $trackingId;
        $leadTracker = getTracker();
        $leadTracker->setTrackingKey($trackingId);
        $xml = "";
        try {
            if (!empty($_POST['email'])) {
                $mail = trim(strtolower($_POST['email']));
                $_POST['cnf'] = static::URI_CENTRALFINANCE;
                $this->initDB();
                Debug::trace($_GET, false, "debug-doublon-passerelle.log");

                //vérfie si on a le dossier depuis - de 6 mois
                $date = new DateTime();
                $period = $this->getCheckPeriod($_GET['src']);
                $date->modify($period);
                $date = $date->format("Y-m-d");

                if ($this->isDoublonMail()) {
                    $leadTracker->rejectLead(new DoubledLead($mail, $date));
                    $xml = "<err>-2</err>";
                } else {
                    $leadTracker->validate();
                    $xml = "<ok>OK</ok>";
                }
            } else {
                $leadTracker->handleException(new RuntimeException('Empty email'));
                $xml = "<err>ERR</err>";
            }
        } catch (\Exception $e) {
            $leadTracker->handleException($e);
            $xml = sprintf('<err>%s</err>', $e->getMessage());
        }

        //output
        $xml = '<?xml version="1.0" standalone="yes"?><root>' . $xml . '</root>';
        $leadTracker->xmlReturned($xml);
        echo $xml;
    }

    /**
     * Permet de récupérer le flux de données Devis PROX
     *
     *
     * @throws Exceptions_Financial_Systeme Data error
     */
    public function checkDoublonDevisProx()
    {
        global $LOCAL, $USER, $PARAM;
        if (!empty($_GET['email'])) {
            try {
                $_POST['cnf'] = static::URI_CENTRALFINANCE;
                $this->initDB();
                Debug::trace($_GET, false, "debug-doublondevisprox.log");

                if ($this->isDoublonMail('-6 months')) { //doublon
                    $xml = "1";
                } else {
                    $xml = "0";
                }
                Debug::trace($xml, false, "debug-doublondevisprox.log");
                Debug::trace(sprintf("%s, %s, %s", $_GET['email'], $xml, date('Y-m-d H:i:s')), false, "debug-doublondevisprox.log");
            } catch (Exception $e) {
                $xml = "<ko>KO</ko>";
            }
        } elseif (!empty($_GET['emailMD5'])) {
            try {
                $_POST['cnf'] = static::URI_CENTRALFINANCE;
                $this->initDB();
                Debug::trace($_GET, false, "debug-doublondevisprox.log");

                //vérfie si on a le dossier depuis - de 6 mois
                $date = new DateTime();
                $date->modify("-6 months");

                $date = $date->format("Y-m-d");
                $sql  = SQL_SQL::getInstance();

                $mail = trim($_GET['emailMD5']);
                $_POST['mail'] = $mail;

                if ($this->isDoublonMail('-6 months')) { //doublon
                    $xml = "1";
                } else {
                    $xml = "0";
                }
                Debug::trace($xml, false, "debug-doublondevisprox.log");
                Debug::trace(sprintf("%s, %s, %s", $mail, $xml, date('Y-m-d H:i:s')), false, "debug-doublondevisprox.log");
            } catch (Exception $e) {
                $xml = "<ko>KO</ko>";
            }
        } else {
            $xml = "<ko>KO</ko>";
        }

        //output
        $xml = '<?xml version="1.0" standalone="yes"?><root>' . $xml . '</root>';
        echo $xml;
    }

    /**
     * @param Classeur_DossierRefinancement $dossier
     * @param int $teamId
     * @param string $typeRepartition
     *
     * @throws Exceptions_Financial_Classeur_Dossier
     * @throws Exceptions_Financial_Equipe
     * @throws Exceptions_Financial_Group
     * @throws Exceptions_Financial_Mail
     * @throws Exceptions_Financial_Remarque
     * @throws Exceptions_Financial_Repartition
     * @throws Exceptions_Financial_Systeme
     * @throws Exceptions_Financial_Update
     * @throws Exceptions_Financial_User
     * @throws Exceptions_SQL
     */
    private function repartitionAuto($dossier, $teamId, $typeRepartition)
    {
        global $USER, $PARAM;
        $user_destination = Classeur_DossierRefinancement_Repartition_Central::getUserByGroup(
            $teamId,
            $typeRepartition
        );
        if (!empty($user_destination)) {
            Classeur_DossierRefinancement_Repartition_Central::increaseQuota(
                $user_destination->getId(),
                $typeRepartition
            );
        } else {
            $user_destination = $USER;
        }
        Classeur_DossierRefinancement_Suivi_DAO::insert_user_dossier_db($dossier, $user_destination);

        $remarque = new Remarque("Affectation automatique du dossier à " . $user_destination->getFullName());
        $suivi = new Suivi(date("Y-m-d"), $user_destination, $remarque);
        Classeur_DossierRefinancement_DAO::update_suivi_rc_db($dossier, $suivi);

        $dossier->setStatus(Classeur_DossierRefinancement::EN_COURS_COMMERCIAL);
        Classeur_DossierRefinancement_DAO::update_status_db($dossier);

        Mail_Mail::sendBasicMail(
            User_Financial_DAO::getObject(100)->getMailAdresse(),
            $user_destination->getMailAdresse(),
            'Affectation automatique',
            sprintf(
                "Le dossier %s%u %s %s vous a &eacute;t&eacute; affect&eacute;.",
                $PARAM->PREFIX_NUM_DOSSIER,
                $dossier->getId(),
                $dossier->getEmprunteur()->getNom(),
                $dossier->getEmprunteur()->getPrenom()
            )
        );
    }

    /**
     * This function checkCdi is used to control if the borrower (or his spouse) is in CDI situation
     * 
     * This function will return "true" if the borrower (or his spouse) is in CDI situation and "false" if not
     * 
     * @param array $tmp
     * @param string $typeContrat
     * @param string $profession
     * @param string $typeContratCj
     * @param string $professionCj
     * 
     * @return bool
     */
    private function checkCdi($tmp, $typeContrat, $profession, $typeContratCj=null, $professionCj=null) {
        if (
            array_key_exists('nom_conjoint', $tmp) && !empty($tmp['nom_conjoint']) &&
            !(
                $typeContrat == "cdi"
                || $typeContrat == "tns"
                || $typeContrat == "travailleurnonsalarie"
                || $typeContrat == "retraite"
                || $typeContrat == "profession liberale"
                || stripos($profession, 'retrait') !== false
                || stripos($profession, 'profession lib') !== false
                || stripos($profession, 'tns') !== false
                || $typeContratCj == "cdi"
                || $typeContratCj == "tns"
                || $typeContratCj == "travailleurnonsalarie"
                || $typeContratCj == "retraite"
                || $typeContratCj == "profession liberale"
                || stripos($professionCj, 'retrait') !== false
                || stripos($professionCj, 'profession lib') !== false
                || stripos($professionCj, 'tns') !== false
            )
            || empty($tmp['nom_conjoint']) &&
            !(
                $typeContrat == "cdi"
                || $typeContrat == "tns"
                || $typeContrat == "travailleurnonsalarie"
                || $typeContrat == "retraite"
                || $typeContrat == "profession liberale"
                || stripos($profession, 'retrait') !== false
                || stripos($profession, 'profession lib') !== false
                || stripos($profession, 'tns') !== false
            )
        ) {
            return false;
        }
        return true;
    }

    /**
     * This function checkRevenus is used to control if the borrower (or his spouse) respect the minimum revenue conditions
     *
     * @param array $tmp
     * @param float|null $minRevenuFoyer
     * @param float|null $minRevenu
     * @param bool $new_revenu
     * @param int $err
     * @param object $leadTracker
     * @return void
     */
    private function checkRevenus($tmp, $minRevenuFoyer, $minRevenu, $new_revenu, &$err, $leadTracker)
    {
        $revenuVous = isset($tmp['revenus_vous']) ? $tmp['revenus_vous'] : 0;
        $revenuConjoint = isset($tmp['revenus_conjoint']) ? $tmp['revenus_conjoint'] : 0;
        $hasConjoint = isset($tmp['nom_conjoint']) && !empty($tmp['nom_conjoint']);
        $revenuFoyer = $revenuVous + $revenuConjoint;

        $isRejected = false;
        $minRequired = null;

        if ($hasConjoint) {
            // Foyer : on teste minRevenuFoyer
            if ($minRevenuFoyer && $revenuFoyer < $minRevenuFoyer) {
                $isRejected = true;
                $minRequired = $minRevenuFoyer;
            } elseif ($new_revenu) {
                // si $new_revenu est actif, test de chaque revenu avec minRevenu
                if (
                    ($minRevenu && $revenuVous < $minRevenu)
                    || ($minRevenu && $revenuConjoint < $minRevenu)
                ) {
                    $isRejected = true;
                    $minRequired = $minRevenu;
                }
            }
        } else {
            // Célibataire ou sans co-emprunteur : on teste minRevenu
            if ($minRevenu && $revenuVous < $minRevenu) {
                $isRejected = true;
                $minRequired = $minRevenu;
            }
        }

        if ($isRejected) {
            $leadTracker->rejectLead(
                new MinRevenue(
                    $revenuVous ? $revenuVous : false,
                    $hasConjoint ? $revenuConjoint : false,
                    $minRequired
                )
            );
            $err = -3;
        }
    }

    public function saveLeadData($message, $queue) {
        if (!isset($_POST['cnf'])) {
            $_POST['cnf'] = static::URI_CENTRALFINANCE;
        }
        $this->initDB();
        $PARAM = Parametre_DAO::getObject(0);
        $domaine = static::DOMAIN_CENTRALFINANCE;
        if ($PARAM->hasKey('LEAD_FREEZE_DATA') && $PARAM->LEAD_FREEZE_DATA) {
            parent::sendMessageToRabbitMQ($domaine, $message, $queue);
        }
    }

    public function NormalizeLead ($post, $logFile) {

        $tmp = [];
        // id
        $tmp['civ_vous'] =
            (empty($post['civilite']) && empty($post['civ']))
                ? self::CIV_DEFAULT
                : (!empty($post['civilite'])
                    ? (($post['civilite'] == 1) ? self::CIV_DEFAULT : self::CIV_W)
                    : (!empty($post['civ']) ? $post['civ'] : self::CIV_DEFAULT)
                );

        $tmp['nom_vous']    = isset($post['nom']) ? $post['nom'] : ".";
        $tmp['prenom_vous'] = isset($post['prenom']) ? $post['prenom'] : ".";
        $tmp['mail']        = isset($post['email']) ? strtolower(trim($post['email'])) : 'anonymisation'. date('U').'@premista.fr';

        if (isset($post['optin'])) {
            if ($post['optin'] == 'true' || $post['optin'] == '1') {
                $tmp['acc_noemailing'] = true;
            } else { $tmp['acc_noemailing'] = false; }
        } else { 
            $tmp['acc_noemailing'] = false;
        }

        if (!empty($post['typecontrat'])) {$tmp['typecontrat'] = $post['typecontrat'];}
        $tmp['acc_finalite'] = true;

        // Date de naissance – fallback propre
        $date = !empty($post['dnat'])
            ? explode("/", Fonction_Convertion::convertDateUSToUE($post['dnat']))
            : (!empty($post['dob'])
                ? explode("/", Fonction_Convertion::convertDateUSToUE($post['dob']))
                : Fonction_Convertion::DNAT_DEFAUT
            );
        //Age = -1 si non renseigné
        $tmp['jour_vous']  = $date[0];
        $tmp['mois_vous']  = $date[1];
        $tmp['annee_vous'] = $date[2];
        $tmp['age_vous']   = Fonction_Convertion::getAge($tmp['jour_vous'].'/'.$tmp['mois_vous'].'/'.$tmp['annee_vous']);

        // Adresse
        if (isset($post['adr1'])){ $tmp['adresse'] = trim($post['adr1']);
        } elseif (isset($post['adresse'])){
            $tmp['adresse'] = trim($post['adresse']);
        } else {
            $tmp['adresse'] = '';}
        $tmp['cp']      = isset($post['cp'])   ? trim($post['cp'])   : '';
        $tmp['ville']   = isset($post['ville'])? trim($post['ville']): '';
        
        //partenaire
        $tmp['acc_partenaires'] = array_key_exists('acc_partenaires', $post) && in_array($post['acc_partenaires'], ['true', '1'], true);
        $tmp['acc_finalite']    = true;

        // Téléphones
        $nameTel = ['telgsm', 'telfixe','tel_mobile', 'tel_domicile'];
        foreach ($nameTel as $key) {
            if (!empty($post[$key])) {
                if (empty($tmp['tel1'])) {
                    $tmp['tel1'] = $post[$key];
                } elseif (empty($tmp['tel2'])) {
                    $tmp['tel2'] = $post[$key];
                }
            }
        }

        $map = array(
            1=>"Employé",
            2=>"Cadre",
            3=>"Commerçant",
            4=>"Fonctionnaire",
            5=>"Enseignant",
            6=>"Agriculteur",
            7=>"Artisan",
            8=>"Chef d'entreprise",
            9=>"Profession libérale",
            10=>"VRP",
            11=>"Etudiant",
            12=>"Retraité",
            13=>"Sans profession",
            14=>"Recherche d'emploi",
            15=>"Autre"
        );

        // Profession
        if (!empty($post['profession'])) {$tmp['profession_vous'] = $post['profession'];
        } elseif (!empty($post['profession']) && isset($mapProfession[$post['profession']])) {
            $tmp['profession_vous'] = $map[$post['profession']];
        } else {
            $tmp['profession_vous'] = self::PROF_NC;
        }
    
        $tmp['anciennete_vous'] = (!empty($post['anneecontrat']) 
                                ? date('Y') - $post['anneecontrat'] 
                                : (!empty($post['anciennete'])
                                    ? $post['anciennete']
                                    : self::ANCIENNETE_DEFAUT)
                                );

        //----------------- Conjoint(e)
        if (!empty($post['nomcj']) || !empty($post['salairecj']) || !empty($post['cj_nom'])) {
            $tmp['civ_conjoint'] = (empty($post['civilitecj']) && empty($post['civ_conjoint']))
                    ? self::CIV_DEFAULT
                    : (!empty($post['civilitecj'])
                        ? (($post['civilitecj'] == 1) ? self::CIV_DEFAULT : self::CIV_W)
                        : (!empty($post['civ_conjoint']) ? $post['civ_conjoint'] : self::CIV_DEFAULT)
                    );

            $tmp['nom_conjoint'] = isset($post['nomcj'])
                ? trim($post['nomcj'])
                : (isset($post['cj_nom'])
                    ? trim($post['cj_nom'])
                    : '.');

            $tmp['prenom_conjoint'] = isset($post['prenomcj'])
                ? trim($post['prenomcj']) 
                : (isset($post['cj_prenom']) 
                    ? trim($post['cj_prenom']) 
                    : '.');

            $date = !empty($post['dnatcj'])
                    ? explode("/", Fonction_Convertion::convertDateUSToUE($post['dnatcj']))
                    : (!empty($post['cj_dob'])
                        ? explode("/", Fonction_Convertion::convertDateUSToUE($post['cj_dob']))
                        : Fonction_Convertion::DNAT_DEFAUT
                    );
            $tmp['jour_conjoint']  = $date[0];
            $tmp['mois_conjoint']  = $date[1];
            $tmp['annee_conjoint'] = $date[2];

            if (isset($post['professioncj']) && $post['professioncj'] !== '') {
                $tmp['profession_conjoint'] = $post['professioncj'];
            } elseif (isset($post['cj_profession']) && isset($map[$post['cj_profession']])) {
                $tmp['profession_conjoint'] = $map[$post['cj_profession']];
            }

            $tmp['anciennete_conjoint'] = (!empty($post['cj_anciennete'])) 
                                        ? $post['cj_anciennete']
                                        : ((!empty($post['anneecontratcj']))
                                            ? date('Y') - $post['anneecontratcj']
                                            : self::ANCIENNETE_DEFAUT
                                        );

            if (!empty($post['salairecj'])) {$tmp['revenus_conjoint'] = $post['salairecj'];
            } elseif (!empty($post['cj_salaire'])) {$tmp['revenus_conjoint'] = $post['cj_salaire'];}
        }

        if (!empty($post['typecontratcj'])) {$tmp['typecontratcj'] = $post['typecontratcj'];}

        $mapSituation = array(
            "célibataire"  => 1,
            "celibataire"  => 1,
            "1"            => 1,
            "marié"        => 2,
            "2"            => 2,    
            "marie"        => 2,
            "pacsé"        => 3,
            "pacse"        => 3,
            "6"            => 4,
            "veuf"         => 4,
            "3"            => 5,
            "union libre"  => 5,
            "4"            => 6,
            "divorcé"      => 7,
            "5"            => 7,
            "divorce"      => 7,


        );

        // $tmp['situation']
        if (isset($post['situfam'])) {
            $tmp['situation'] = $mapSituation[strtolower($post['situfam'])];
        } elseif (isset($post['situation_famille'])) {
            $tmp['situation'] = $mapSituation[strtolower($post['situation_famille'])];
        } else {$tmp['situation'] = 1;}

        // $tmp['enfants']
        if (isset($post['nbenf'])) {$tmp['enfants'] = (int)$post['nbenf'];
        } elseif (isset($post['nbre_enfant'])) {$tmp['enfants'] = (int)$post['nbre_enfant'];}
        else { $tmp['enfants'] = 0; };

        // $tmp['pensions_versees']
        if (!empty($post['paversee'])) {$tmp['pensions_versees'] = $post['paversee'];
        } elseif (!empty($post['montant_pension_versee'])) {$tmp['pensions_versees'] = $post['montant_pension_versee'];}


        if (empty($post['loyer'])) {
            if (!empty($post['autrescharges'])) {$tmp['loyer'] = $post['autrescharges'];}
        } elseif (empty($post['montant_votre_loyer'])) {$tmp['loyer'] = $post['loyer'];
            if (!empty($post['autrescharges'])) {$tmp['autres_charges'] = $post['autrescharges'];}
        } else {$tmp['Loyer'] = $post['montant_votre_loyer'];}

        $tmp['charges'] = 0;
        
        if (isset($post['statutlgmt']) && in_array($post['statutlgmt'], ['proprietaire', 'propri'], true)) {
            $tmp['proprietaire'] = true;
        } elseif (stripos($post['statutlgmt'], 'gratuit') !== false || 
                stripos($post['statutlgmt'],'employeur') !== false ||
                isset($post['type_locataire']) == 2 ){
            $tmp['locataire'] = true;
            $tmp['hebergement_gratuit'] = true;
        } else {
            $tmp['locataire'] = true;
        }

        // revenu
        $tmp['revenus_vous'] = isset($post['revenus_vous']) 
                            ? (int)$post['revenus_vous']
                            : (!empty($post['salaire'])
                                ? (int)$post['salaire'] 
                                : 1);

        $tmp['fonciers'] = array_key_exists('loyers_percu',$post) ? (int)$post['loyers_percu'] : 0;

        //$tmp['pensions']
        if (array_key_exists('parecue',$post)) {$tmp['pensions'] = (int)$post['parecue'];
        } elseif (array_key_exists('autres_revenus',$post)){$tmp['pensions']= (int)$post['autres_revenus'];} 

        if (array_key_exists('allocs',$post)) {$tmp['allocations'] = (int)$post['allocs'];
        } elseif (array_key_exists('allocation_famil',$post)){$tmp['allocations'] = (int)$post['allocation_famil'];} 

        $tmp['aide_logement'] = array_key_exists('apl',$post) ? (int)$post['apl'] : 0;
        $tmp['type_credit']   = [];
        $tmp['mensualite']    = [];
        $tmp['crd']           = [];
        $tmp['crd_conso']     = 0;
        $tmp['crd_immo']      = 0; 
        $crd_conso            = 0;
        $crd_immo             = 0;
        $nbConso              = 0;
        $nbImmo               = 0;

        if (isset($post['nbpretconso'])) { $nbConso = $post['nbpretconso'];
            } else {
            for ($i = 1;; $i++) {
                if (!empty($post['mensuconso'. $i]) || !empty($post['rdconso'. $i])) {
                    $typeConso = array_key_exists('typecrconso' . $i, $post) ? $post['typecrconso' . $i] : 'CONSO';
                    $rdConso = array_key_exists('rdconso' . $i, $post) ? $post['rdconso' . $i] : 0;
                    $tmp['type_credit'][] =  $typeConso . ": " . $post['rdconso' . $i];
                    $tmp['mensualite'][] = array_key_exists('mensuconso' . $i, $post) ? $post['mensuconso' . $i] : 10;
                    $tmp['crd'][] = (int)$rdConso;
                    $crd_conso += $rdConso;
                    $tmp['crd_conso'] = (int)$crd_conso;
                    $nbConso++;
                    // $tmp['nbConso'] = (int)$nbConso;
                } else {  break; }
            }
        }
        $tmp['nbConso'] = (int)$nbConso;

        if (isset($post['nbpretimmo'])) { $nbImmo = $post['nbpretimmo'];
            } else {
            for ($i = 1;; $i++) {
                $suffix = $i > 1 ? $i : '';
                $keyPret = 'mensuimmo' . $suffix;
                if (!empty($post[$keyPret])) {
                    $rdImmo = array_key_exists('rdimmo' . $suffix, $post) ? (int)$post['rdimmo' . $suffix] : 0;
                    $tmp['type_credit'][] = "IMMOBILIER: " . (int)$post['rdimmo' . $suffix];
                    $tmp['mensualite'][] = (int)$post['mensuimmo' . $suffix];
                    $tmp['crd'][] = (int)$rdImmo;
                    $crd_immo += $rdImmo;
                    $tmp['crd_immo'] = (int)$crd_immo;
                    $nbImmo++;
                    // $tmp['nbImmo'] = (int)$nbImmo;
                } else { break; }
            }
        }
        $tmp['nbImmo'] = (int)$nbImmo;

        $tmp['crd'] = $tmp['crd_conso'] + $tmp['crd_immo'];

        if (isset($post['tres'])) {$tmp['montant_souhaite'] = (int)$post['tres'];}
        elseif (isset($post['tresorie'])) {$tmp['montant_souhaite']= (int)$post['tresorie'];}
        else {$tmp['montant_souhaite']=0;}

        if (empty($post['FICP'])) {$post['FICP'] = "oui";}

        if (!isset($post['bdf'])) {$tmp['bdf'] = "0";
        } else {
        $tmp['bdf'] = (!empty($post['pbban']) && (strtolower($post['pbban']) == "interdiction bancaire" || 
            strtolower($post['pbban']) == "ficp" || $post['FICP'] == "oui")) ? 'oui' : 'non';
        }

        if (!empty($post['dejafaitrachat']) && $post['dejafaitrachat'] == 'Oui') {
            $tmp['texte_remarque'] .= "Client déjà restructuré\n";
        } elseif (!empty($post['deja_restructure']) && $post['deja_restructure'] == 1) {
            if (!empty($post['date_last_restructuration'])) {
                $tmp['texte_remarque'] .= "Client déjà restructuré le " . $post['date_last_restructuration'] . "\n";
            } else {$tmp['texte_remarque'] .= "Client déjà restructuré\n";}
        }

        $tmp['src'] = !empty($post['src']) ? $post['src'] : 'pid-emul';
        $tmp['ed']  = !empty($post['ed']) ? $post['ed'] : '';

        if (!empty($post['nature_de_projet'])) {$tmp['nature_de_projet'] = $post['nature_de_projet'];}

        $tmp['sumMensualite'] = array_sum($tmp['mensualite']);
        $tmp['revenuTotal'] = $tmp['revenus_vous'];
        $tmp['calculatedTaux'] = $tmp['revenuTotal'] > 0 ? ($tmp['sumMensualite'] / $tmp['revenuTotal']) * 100 : 0; 
        if (isset($tmp['revenus_conjoint'])) {$tmp['revenuTotal'] += $tmp['revenus_conjoint'];}

        $tmp['cnf'] = Service_Passerelle::URI_CENTRALFINANCE;

        // --------- only loadFluxDevisProx
        if (array_key_exists('montant_remboursements_mensuel_conso',$post)) {
            for ($i = 1; $i <= $post['nb_credit_conso']; $i++) {
                $tmp['type_credit'][] = "CONSO";
                $tmp['mensualite'][]  = round($post['montant_remboursements_mensuel_conso'] / $post['nb_credit_conso']);
                $tmp['crd_total'][]         = round($post['capitaux_restant_dus_conso'] / $post['nb_credit_conso']);
                $tmp['crd'] = $tmp['crd_total'];
                }
        }
        if (array_key_exists('montant_remboursements_mensuel_immo',$post)) {
            if (empty($post['capitaux_restant_dus_immo'])) {
                $post['capitaux_restant_dus_immo'] = 10;}
            for ($i = 1; $i <= $post['nb_credit_immo']; $i++) {
                $tmp['type_credit'][] = "IMMO";
                $tmp['mensualite'][]  = round($post['montant_remboursements_mensuel_immo'] / $post['nb_credit_immo']);
                $tmp['crd_total'][]         = round($post['capitaux_restant_dus_immo'] / $post['nb_credit_immo']);
                $tmp['crd'] = $tmp['crd_total'];
                }
        }
        if (array_key_exists('estimation_patrimoine', $post)) {
            $tmp['texte_remarque'] .= "Valeur du bien estimée ".$post['estimation_patrimoine']."€\n";
        }
        if (array_key_exists('periode_appel',$post) && $post['periode_appel'] != 1) {
            $tmp['texte_remarque'] .= " Le client souhaite être contacté de préférence : ";
            switch ($post['periode_appel']) {
                case "2":$tmp['texte_remarque'] .= "Matin"; break;
                case "3":$tmp['texte_remarque'] .= "Après Midi"; break;
                case "4":$tmp['texte_remarque'] .= "Soir"; break;
            }
        }
        if (isset($_GET['devisprox_exclu'])) {$tmp['src'] = 'devisprox_exclu';
        } elseif (isset($_GET['devisprox_premium'])) {
            $tmp['src'] = 'devisprox_premium';
        }

        if (array_key_exists('capitaux_restant_dus_conso',$post)) {$tmp['capitaux_restant_dus_conso'] = $post['capitaux_restant_dus_conso'];}
        if (array_key_exists('capitaux_restant_dus_immo',$post)) {$tmp['capitaux_restant_dus_immo']  = $post['capitaux_restant_dus_immo'];}
        if (array_key_exists('nb_credit_conso',$post)) {$tmp['nb_credit_conso']  = $post['nb_credit_conso'];}
        if (array_key_exists('nb_credit_immo',$post)) {$tmp['nb_credit_immo']  = $post['nb_credit_immo'];}

        // ---------------------------------

        // ---------- only on loadFluxAffi /amon / DevisProx
        if (array_key_exists('autres_revenus', $post) && $post['autres_revenus'] > 1) {
            $tmp['autres_revenus'] = $post['autres_revenus'];
        }
        if (array_key_exists('TRACKING_KEY',$post)){
            $tmp['TRACKING_KEY'] = $post['TRACKING_KEY'];
        }
        if (array_key_exists('acc_partenaires', $post) && ($post['acc_partenaires'] == 'true' || $post['acc_partenaires'] == '1')){
            $tmp['acc_partenaires'] = $post['acc_partenaires'];
        }
        $tmp['externalId'] = (!empty($post['gclid']) ? $post['gclid'] : (!empty($post['external_id']) ? $post['external_id'] : null));
        $tmp['acc_finalite'] = true;
        // -----------------------


        Debug::trace("Lead Normamized", false, $logFile);
        Debug::trace($tmp, false, $logFile);
        return $tmp;
    }

    private function rejectOnce(&$err, $code, $reason, $leadTracker){
        if ($err !== 0) {return;}
        $leadTracker->rejectLead($reason);
        $err = $code;
    }
    
    private function debugTrace ($logFile, $trackinKey, $critereName, $critere = null, $leadState = null)
    {
        Debug::trace('ERROR > TRACKING_KEY: '.$trackinKey.
                    ' > CRITERE: '.$critereName,
                    // ' Critere: '.json_encode($critere[$critereName]).
                    // ' > Etat lead: '.json_encode($leadState),
                    false, $logFile);
    }

    public function EvaluateLeadWithCritere($tmp, $critereObj, $leadTracker, $logFile){
        $err =0;
        $critere = LeadCritereHydrator::hydrateToArray($critereObj);
        // $critere = $critereObj->getArray();
        // var_dump($critere);

        if ($this->isDoublonWithCritere($critereObj)) {
                $this->rejectOnce($err,-2,new DoubledLead($tmp['mail']),$leadTracker);
                Debug::trace('ERROR > Doublon lead: '.$tmp['TRACKING_KEY'].' - Email: '.$tmp['mail'], false, $logFile);
        }
                
        if (!empty($critere[LeadCritMapping::COLUMN['TRESO_MIN']])) {
            $tmp['crd'] += (int)$tmp['montant_souhaite'];
        }


        // -------  INCLUSION DE LA REUNION, MARTINIQUE ET GUADELOUP && DOMTOM
        if (array_key_exists(LeadCritMapping::COLUMN['IS_DOMTOM'], $critere) && (bool) $critere[LeadCritMapping::COLUMN['IS_DOMTOM']] === FALSE 
        && in_array((int) substr($tmp['cp'], 0, 3),[974, 972, 971]) ||
            array_key_exists(LeadCritMapping::COLUMN['IS_DOMTOM'], $critere) && (bool) $critere[LeadCritMapping::COLUMN['IS_DOMTOM']] === FALSE 
            && in_array((int) substr($tmp['cp'], 0, 3), Classeur_DossierRefinancement_Traitement_Central_DAO::DEPARTEMENTS_DOMTOM)){
            $this->rejectOnce($err,-14,new NotDomTom(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['IS_DOMTOM'],$tmp['cp']);
        }

        if (isset($critere[LeadCritMapping::COLUMN['HEBERGEMENT_FREE']]) && 
            $critere[LeadCritMapping::COLUMN['HEBERGEMENT_FREE']] === FALSE && 
            !empty($tmp['hebergement_gratuit']) === TRUE) {
            // $leadTracker->rejectLead(new NoFreeRenter());
            // $err = -11;
            $this->rejectOnce($err,-11,new NoFreeRenter(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['HEBERGEMENT_FREE'],$tmp['hebergement_gratuit']);
        }

        $type = isset($tmp['proprietaire']) ? 'PRO' : 'LOC';

        // ----------- CALCUL DE L'AGE. ----------
        if ($tmp['age_vous'] != -1 || isset($critere[LeadCritMapping::COLUMN['AGE_MIN_'.$type]]) || isset($critere[LeadCritMapping::COLUMN['AGE_MAX'.$type]])){
            if ($tmp['age_vous'] < (int)$critere[LeadCritMapping::COLUMN['AGE_MIN_'.$type]]) {
                // $leadTracker->rejectLead( new MinAge($tmp['age_vous'],$critere[LeadCritMapping::COLUMN['AGE_MIN_'.$type]]));
                // $err = -13;
                $this->rejectOnce($err,-13,new MinAge($tmp['age_vous'],$critere[LeadCritMapping::COLUMN['AGE_MIN_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['AGE_MIN_'.$type]);
            } elseif ($tmp['age_vous'] > (int)$critere[LeadCritMapping::COLUMN['AGE_MAX_'.$type]]) {
                // $leadTracker->rejectLead( new MaxAge($tmp['age_vous'],$critere[LeadCritMapping::COLUMN['AGE_MAX_'.$type]]));
                // $err = -13;
                $this->rejectOnce($err,-13,new MaxAge($tmp['age_vous'],$critere[LeadCritMapping::COLUMN['AGE_MAX_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['AGE_MAX_'.$type]);
            }
        }

        $minRevenu = array_key_exists(LeadCritMapping::COLUMN['REV_MIN_'.$type],$critere ) ? $critere[LeadCritMapping::COLUMN['REV_MIN_'.$type]] : 0;
        $minRevenuFoyer = array_key_exists(LeadCritMapping::COLUMN['REV_MIN_FOYER_'.$type],$critere) ? $critere[LeadCritMapping::COLUMN['REV_MIN_FOYER_'.$type]] : 0;

        if (!empty($critere[LeadCritMapping::COLUMN['LOC']]) == FALSE && $critere[LeadCritMapping::COLUMN['LOC']] !== isset($tmp['locataire']) ? $tmp['locataire'] : 0) { //PID ne fourni pas de dossier LOC
            // $leadTracker->rejectLead(new NotOwner());
            // $err = -8;
            $this->rejectOnce($err,-8,new NotOwner(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['LOC']);
        }

        if (!empty($critere[LeadCritMapping::COLUMN['PRO']]) == FALSE && $critere[LeadCritMapping::COLUMN['PRO']] !== isset($tmp['proprietaire']) ?$tmp['proprietaire'] :0) {
            // $leadTracker->rejectLead(new NotTenant());
            // $err = -17;
            $this->rejectOnce($err,-8,new NotTenant(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['PRO']);
        }
        if (isset($newRevenuPro)) {$critere[LeadCritMapping::COLUMN['NEW_REV_PRO']] = $newRevenuPro;}
        if ($tmp['src'] == 'younitedrefuspp') {
            if ((isset($tmp['nbConso']) + isset($tmp['nbImmo'])) < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]) {
                // $leadTracker->rejectLead(new MinLoanCount(isset($tmp['nbConso']) + isset($tmp['nbImmo']), $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]));
                // $err = -5;
                $this->rejectOnce($err,-5,new MinLoanCount(isset($tmp['nbConso']) + isset($tmp['nbImmo']), $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]);          
            }
        } else {
            if (!empty($critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]) && $tmp['crd_conso'] < $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]) {
                // $leadTracker->rejectLead(new MinConsummationAmount($tmp['crd_conso'], $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]));
                // $err = -4;
                $this->rejectOnce($err,-4,new MinConsummationAmount($tmp['crd_conso'], $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]);
            }
            if (!empty($critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]]) && $tmp['crd_conso'] > $critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]] ) {
                // $leadTracker->rejectLead(new MaxConsummationAmount($tmp['crd_conso'], $critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]]));
                // $err = -4;
                $this->rejectOnce($err,-4,new MaxConsummationAmount($tmp['crd_conso'], $critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]);
            }
            if (!empty($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]) && $tmp['crd'] <= 0 ) {
                // $leadTracker->rejectLead(new NullOwnerAmount($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]], $tmp['crd_immo']));
                // $err = -4;
                $this->rejectOnce($err,-4,new NullOwnerAmount($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]], $tmp['crd_immo']),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]);
            }
            // if (empty($critere[LeadCritMapping::COLUMN['ONLY_PRET_CONSO']]) && ($tmp['nbConso'] + $tmp['nbImmo']) < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]] ||
            // !empty($critere[LeadCritMapping::COLUMN['ONLY_PRET_CONSO']]) && $tmp['nbConso'] < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]){
            if (($tmp['nbConso'] + $tmp['nbImmo']) < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]){
                // $leadTracker->rejectLead(new MinLoanCount(($tmp['nbConso']+$tmp['nbImmo']), $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]));
                // $err = -5;
                $this->rejectOnce($err,-5,new MinLoanCount(($tmp['nbConso']+$tmp['nbImmo']), $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['NB_MIN_PRET_'.$type]);
            } elseif (!empty($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]) && $tmp['nbConso'] < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]) {
                // $leadTracker->rejectLead(new MinLoanCount($tmp['nbConso'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]));
                // $err = -5;
                $this->rejectOnce($err,-5,new MinLoanCount($tmp['nbConso'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],'nbConso < '.LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]);
            } elseif (!empty($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]) && $tmp['nbConso'] < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]) {
                // $leadTracker->rejectLead(new MinLoanCount($tmp['nbImmo'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]));
                // $err = -5;
                $this->rejectOnce($err,-5,new MinLoanCount($tmp['nbImmo'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],'nbConso < '.LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]);
            }
            if (!empty($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]) && $tmp['nbConso'] < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]) {
                // $leadTracker->rejectLead(new MinLoanCount($tmp['nbConso'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]));
                // $err = -5;
                $this->rejectOnce($err,-5,new MinLoanCount($tmp['nbConso'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],'nbConso < '.LeadCritMapping::COLUMN['NB_MIN_PRET_CONSO_'.$type]);
            }
            if (!empty($critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]) && $tmp['nbImmo'] < $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]) {
                // $leadTracker->rejectLead(new MinLoanCount($tmp['nbImmo'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]));
                // $err = -5;
                $this->rejectOnce($err,-5,new MinLoanCount($tmp['nbImmo'], $critere[LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],'nbImmo < '.LeadCritMapping::COLUMN['NB_MIN_PRET_IMMO_'.$type]);
            }

        }

        // only CRD CONSO
            // if (isset($critere[LeadCritMapping::COLUMN['ONLY_CRD_CONSO']]) && $critere[LeadCritMapping::COLUMN['ONLY_CRD_CONSO']] === false) {
                if (isset($critere[LeadCritMapping::COLUMN['CRD_MIN_'.$type]]) && $tmp['crd'] < $critere[LeadCritMapping::COLUMN['CRD_MIN_'.$type]]) {
                    $this->rejectOnce($err,-4,new MinLoanAmount($tmp['crd'], $critere[LeadCritMapping::COLUMN['CRD_MIN_'.$type]]),$leadTracker);
                    $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CRD_MIN_'.$type]);
                }
                if (!empty($critere[LeadCritMapping::COLUMN['CRD_MAX_'.$type]]) && $tmp['crd'] > $critere[LeadCritMapping::COLUMN['CRD_MAX_'.$type]]) {
                    $this->rejectOnce($err,-4,new MaxLoanAmount($tmp['crd'], $critere[LeadCritMapping::COLUMN['CRD_MAX_'.$type]]),$leadTracker);
                    $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CRD_MAX_'.$type]);
                }
            // } else {
                if (isset($critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]) && $tmp['crd_conso'] < $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]) {
                    $this->rejectOnce($err,-4,new MinConsummationAmount($tmp['crd_conso'], $critere[LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]]),$leadTracker);
                    $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CRD_MIN_CONSO_'.$type]);
                }
                if (!empty($critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]]) && $tmp['crd_conso'] > $critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]]) {
                    $this->rejectOnce($err,-4,new MaxConsummationAmount($tmp['crd_conso'], $critere[LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]]),$leadTracker);
                    $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CRD_MAX_CONSO_'.$type]);
                }
            // }

        // maf
        $maf = $tmp['crd'] + $tmp['montant_souhaite'];
        if (!empty($critere[LeadCritMapping::COLUMN['MAF_MAX_'.$type]]) && $maf > $critere[LeadCritMapping::COLUMN['MAF_MAX_'.$type]]) {
            // $leadTracker->rejectLead(new MaxMafAmount($maf, $critere[LeadCritMapping::COLUMN['MAF_MAX_'.$type]]));
            // $err = -12;
            $this->rejectOnce($err,-12,new MaxMafAmount($maf, $critere[LeadCritMapping::COLUMN['MAF_MAX_'.$type]]),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['MAF_MAX_'.$type]);
        }
        if (!empty($critere[LeadCritMapping::COLUMN['MAF_MIN_'.$type]]) && $maf < $critere[LeadCritMapping::COLUMN['MAF_MIN_'.$type]]) {
            // $leadTracker->rejectLead(new MinMafAmount($maf, $critere[LeadCritMapping::COLUMN['MAF_MIN_'.$type]]));
            // $err = -15;
            $this->rejectOnce($err,-15,new MinMafAmount($maf, $critere[LeadCritMapping::COLUMN['MAF_MIN_'.$type]]),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['MAF_MIN_'.$type]);
        }

        if ($tmp['bdf'] == "oui" || $tmp['bdf'] == "1") {
            // $leadTracker->rejectLead(new Bdf());
            // $err = -6;
            $this->rejectOnce($err,-6,new Bdf(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],'BDF');
        }
        // ---------- CALCUL TAUX ENDETEMENT ----------------
        if (!empty($tmp['revenus_vous']) && $tmp['revenus_vous'] != 0) {
            if ((!empty($critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MIN_'.$type]]) && $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MIN_'.$type]] >= 0 && $tmp['calculatedTaux'] < $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MIN']])) {
                // $leadTracker->rejectLead(new MinDebtRatio($tmp['calculatedTaux'], $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MIN'.$type]]));
                // $err = -18;
                $this->rejectOnce($err,-18,new MinDebtRatio($tmp['calculatedTaux'], $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MIN'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['TX_ENDETTEMENT_MIN'.$type]);
            }
            if ((!empty($critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MAX_'.$type]]) && $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MAX_'.$type]] >= 0 && $tmp['calculatedTaux'] > $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MAX']])) {
                // $leadTracker->rejectLead(new MaxDebtRatio($tmp['calculatedTaux'], $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MAX_'.$type]]));
                // $err = -18;
                $this->rejectOnce($err,-18,new MaxDebtRatio($tmp['calculatedTaux'], $critere[LeadCritMapping::COLUMN['TX_ENDETTEMENT_MAX'.$type]]),$leadTracker);
                $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['TX_ENDETTEMENT_MAX'.$type]);
            }
        }

        // -------------  CALCUL FILTRE SUR REVENU  -----------
        $this->checkRevenus($tmp, $minRevenuFoyer, $minRevenu, !empty($critere[LeadCritMapping::COLUMN['NEW_REV_'.$type]]), $err, $leadTracker);
        // Only accept cdi contracts or retirements
        $typeContrat = array_key_exists('typecontrat', $tmp) ? strtolower($tmp['typecontrat']) : '';
        $typeContratCj = array_key_exists('typecontratcj', $tmp) ? strtolower($tmp['typecontratcj']) : '';
        $profession = strtolower($tmp['profession_vous']);
        $professionCj = array_key_exists('profession_conjoint', $tmp) ? strtolower($tmp['profession_conjoint']) : '';

        if (!empty($critere[LeadCritMapping::COLUMN['CDI']]) && $this->checkCdi($tmp, $typeContrat, $profession, $typeContratCj, $professionCj) === false) {
            // $leadTracker->rejectLead(new NotCdi());
            // $leadTracker->rejectLead(new NotRetired());
            // $err = -9;
            $this->rejectOnce($err,-9,new NotCdi(),$leadTracker);
            $this->rejectOnce($err,-9,new NotRetired(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['CDI']);
        }

        // ------------- YOUNITED -------------
        if (!empty($critere[LeadCritMapping::COLUMN['YOUNITED_REFUS_RAC']])) {
            if ($typeContrat != "cdi" && stripos($profession, 'retrait') === false) {
                if ( (stripos($profession, 'independant') !== false && $tmp['anciennete_vous'] < 2)
                    || (stripos($profession, 'profession lib') !== false && $tmp['anciennete_vous'] < 3)) {
                    // $err = -14;
                    $this->rejectOnce($err,-14,null,$leadTracker);
                    $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']. ' independant & liberale');
                } elseif ($typeContrat == 'cdd') {
                    if ($tmp['anciennete_vous'] >= 2){
                        if ($typeContratCj != 'cdi'
                        && stripos($professionCj, 'retrait') === false) { 
                            // $err = -9; 
                            $this->rejectOnce($err,-9,null,$leadTracker);
                            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']. ' cdd > 2');
                        }
                    } else {
                        // $err = -14;
                        $this->rejectOnce($err,-14,null,$leadTracker);
                        $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE'].' < 2'); 
                    }
                } elseif (stripos($profession, 'independant') === false && stripos($profession, 'profession lib') === false && $typeContrat != 'cdd') { 
                    // $err = -9;
                    $this->rejectOnce($err,-9,null,$leadTracker);
                    $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']. ' retired');
                }
            }
        }

        // Ancienneté si l'emprunteur est en profession libérale
        if (!empty($critere[LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']]) &&
            $critere[LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']] > 0 &&
            $tmp['anciennete_vous'] != self::ANCIENNETE_DEFAUT && $profession == self::PROF_LIB &&
            $tmp['anciennete_vous'] < $critere[LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']]) {
            // $leadTracker->rejectLead(new MinAnciennete($tmp['anciennete_vous'],$critere[LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']]));
            // $err = -14;
            $this->rejectOnce($err,-14,new MinAnciennete($tmp['anciennete_vous'],$critere[LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']]),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['PROF_LIB_MIN_DUREE']);
        }
        if (!empty($critere[LeadCritMapping::COLUMN['TRESO_MIN']]) > $tmp['montant_souhaite']) {
            // $leadTracker->rejectLead(new MinTresorerie($tmp['montant_souhaite'], $critere[LeadCritMapping::COLUMN['TRESO_MIN']]));
            // $err = -10;
            $this->rejectOnce($err,-10,new MinTresorerie($tmp['montant_souhaite'], $critere[LeadCritMapping::COLUMN['TRESO_MIN']]),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['TRESO_MIN']);
        }
        if (!empty($critere[LeadCritMapping::COLUMN['LEAD_HS']]) !== false) {
            // $leadTracker->rejectLead(new LeadHs());
            // $err = -16;
            $this->rejectOnce($err,-16,new LeadHs(),$leadTracker);
            $this->debugTrace($logFile, $tmp['TRACKING_KEY'],LeadCritMapping::COLUMN['LEAD_HS']);
        }
        
        return $err;
    }

}

ob_start();
global $TRACKING_KEY;
$TRACKING_KEY = $trackingId;
$_POST['TRACKING_KEY'] = $TRACKING_KEY;

$datas = ['GET' => $_GET,'POST' => $_POST,];

Debug::trace(
    sprintf(
        "%s : Lead injection with data (%s): \n\tPOST(%s), \n\tGET(%s)",
        $TRACKING_KEY,
        __FILE__,
        json_encode($_POST),
        json_encode($_GET)
    ),false,'ServicePasserelle.log');

/******************************** SERVER DECLARATION ********************************/
// Lance la méthode associée au paramètre en GET
$saveStatus = false;
try {
    // Sauvegarde des informations du lead à l'arrivée
    $soap = new Service_Passerelle();
    if (!empty($_GET)) {
        $saveStatus = true;
        $message = [
            'trackingId' => $TRACKING_KEY,
            'date' => date('d/m/Y H:i:s'),
            'datas' => $datas
        ];
        $soap->saveLeadData($message, 'RABBITMQ_QUEUE_LEAD_DATA');
    }

    if (!empty($_GET['tuniform'])) {
        $soap->loadFluxTuniform();
    } elseif (!empty($_GET['affi'])) {
        $soap->loadFluxAffi();
    } elseif (!empty($_GET['anon'])) {
        $soap->loadFluxAffiAnon();
    } elseif (!empty($_GET['quickcheck'])) {
        $soap->checkMail();
    } elseif (!empty($_GET['check'])) {
        $soap->checkFluxAffi();
    } elseif (!empty($_GET['devisprox']) || (!empty($_GET['src']) && in_array($_GET['src'], ['devisprox_premium', 'devisprox_exclu'])) || !empty($_GET['devisprox_premium']) || !empty($_GET['devisprox_exclu'])) {
        $soap->loadFluxDevisProx();
    } elseif (!empty($_GET['devisproxdoublon']) || !empty($_GET['doublon'])) {
        $soap->checkDoublonDevisProx();
    }
} catch (\Exception $e) {
    $leadTracker->handleException($e);

    $text = sprintf("Error while loading lead : %s", $e->getMessage());
    Debug::trace($text, false, 'ServicePasserelle.log');
}

$httpResponse = ob_get_clean();

Debug::trace(
    sprintf(
        "%s : Lead injection with response (%s): \n\tRESPONSE(%s)",
        $TRACKING_KEY,
        __FILE__,
        $httpResponse
    ),
    false,
    'ServicePasserelle.log'
);

// on enregistre le retour dans une rabbitMq
if (!empty($httpResponse) && $saveStatus) {
    $pattern = '/<(ok|err)>(\-?\d+)<\/\1>/';
    if (preg_match($pattern, $httpResponse, $matches)) {
        $status = $matches[2];
    } else {
        $status = "Retour non valide.";
    }

    $message = [
        'trackingId' => $TRACKING_KEY,
        'status' => $status,
    ];
    $soap->saveLeadData($message, 'RABBITMQ_QUEUE_LEAD_STATUS');
}

echo $httpResponse;
