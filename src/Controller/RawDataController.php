<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RawDataController extends AbstractController
{
    
    /**
     * getConstantByAction
     *
     * @param  string $action
     * @return JsonResponse
     */
    public function getConstantByAction(string $action): JsonResponse
    {
        return call_user_func([self::class, $action]);
    }

    /**
     * listScoring
     *
     * Return raw data for dossier commercial list scoring
     * Use for replace raw data in front code
     *
     * @return JsonResponse : list of scoring
     */
    private function listScoring(): JsonResponse
    {
        $listScoring = [
            'VERT',
            'ORANGE',
            'ROUGE',
            'NOIR',
            'FRIGO',
            'NF'
        ];
        return new JsonResponse(static::formatData($listScoring), 200);
    }

    /**
     * dossierState
     * Return raw data for dossier list of state for a dossier
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function dossierState(): JsonResponse
    {
        $dossierState = [
            'fin_azur',
            'accord_banque',
            'attente_signature_client',
            'encours_commercial',
            'encours_depot_banque',
            'encaisse',
            'envoi_bfc',
            'frigo',
            'potentiel',
            're_edition_offre_de_pret',
            'signature_offre_de_pret',
            'encours_azur',
            'en_attente_azur'
        ];
        return new JsonResponse(static::formatData($dossierState), 200);
    }

    /**
     * dossierType
     * Return raw data for dossier : list of type for a dossier
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function dossierType(): JsonResponse
    {
        $dossierType = [
            'locataire',
            'proprietaire'
        ];
        return new JsonResponse(static::formatData($dossierType), 200);
    }

    /**
     * dossierOrientation
     * Return raw data for dossier : list of orientation for a dossier
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function dossierOrientation(): JsonResponse
    {
        $dossierOrientation = [
            'conso',
            'hypo'
        ];
        return new JsonResponse(static::formatData($dossierOrientation), 200);
    }

    /**
     * depotBanqueFollow
     * Return raw data type of follow for depot banque
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function depotBanqueFollow(): JsonResponse
    {
        $depotBanqueFollow = [
            'envoi',
            'demande_doc',
            'doc_receptionne',
            'dos_receptionne',
            'doc_envoye',
            'refus banque',
            'refus cf',
            'refus_score',
            'refus client',
            'accord',
            'accord sous reserve',
            'depot_valider',
            'en_contestation'
        ];

        return new JsonResponse(static::formatData($depotBanqueFollow), 200);
    }

    /**
     * peopleType
     * Return raw data type of people for contact dossier
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function peopleType(): JsonResponse
    {
        $peopleType = [
            'client',
            'partenaire',
            'notaire',
            'assurance',
            'banque',
            'at',
            'da',
            'cc',
            'rc'
        ];

        return new JsonResponse(static::formatData($peopleType), 200);
    }

    /**
     * currentState
     * Return raw data current state for suivi assurance
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function currentState(): JsonResponse
    {
        $currentState = [
            'reception_adp',
            'demande_doc',
            'envoi_doc',
            'accord_compagnie',
            'majoration',
            'tarif_normal',
            'reception_bpa',
            'renvoi_adp',
            'envoi_compagnie',
            'envoi_adp',
            'envoi_avis_compagnie',
            'edition_bpa',
            'envoi_bpa',
            'demande_ca',
            'envoi_ca',
            'tarification',
            'tarification_valide',
            'demande_adp',
            'attente_retour_rc'
        ];

        return new JsonResponse(static::formatData($currentState), 200);
    }

    /**
     * garantyType
     * Return raw data garanty type for bank deposit
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function depotBanqueGarantyType(): JsonResponse
    {
        $garantyType = [
            'CAUT',
            'HYPO',
            'PSHC',
            'SGAR'
        ];

        return new JsonResponse(static::formatData($garantyType), 200);
    }

    /**
     * notaryAppointmentState
     * Return raw data state for notary appointment
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */
    private function notaryAppointmentState(): JsonResponse
    {
        $notaryAppointmentState = [
            'fait',
            'annuler client',
            'annuler notaire',
            'en cours'
        ];

        return new JsonResponse(static::formatData($notaryAppointmentState), 200);
    }

    /**
     * typeBien
     * Return raw data type of property
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */

    private function typeBien(): JsonResponse
    {
        $propertyType = [
            'STANDARD',
            'ATYPIQUE',
            'DUPLEX',
            'TRIPLEX',
            'LOFT/ATELIER'
        ];

        return new JsonResponse(static::formatData($propertyType), 200);
    }

    /**
     * usageBien
     * Return raw data uses of the property
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */

    private function usageBien(): JsonResponse
    {
        $usageBien = [
            'PRINCIPAL',
            'SECONDAIRE',
            'LOCATIF',
            'MIXTE',
            'COMMERCIAL'
        ];

        return new JsonResponse(static::formatData($usageBien), 200);
    }

    /**
     * natureBien
     * Return raw data nature of the property
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */

    private function natureBien(): JsonResponse
    {
        $natureBien = [
            'MAISON',
            'APPARTEMENT',
            'HOTEL_PARTICULIER',
            'TERRAIN',
            'IMMEUBLE',
            'LOCAL'
        ];

        return new JsonResponse(static::formatData($natureBien), 200);
    }

    /**
     * LodgingType
     * Return raw data of lodging types
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */

    private function lodgingType(): JsonResponse
    {
        $lodgingType = [
            ['data' => 'Parent', 'label' => 'Parent (Avec loyer fictif)'],
            ['data' => 'Employeur Sans Loyer Fictif', 'label' => 'Employeur Sans Loyer Fictif'],
            ['data' => 'Employeur Avec Loyer Fictif', 'label' => 'Employeur Avec Loyer Fictif'],
            ['data' => 'Autre', 'label' => 'Autre (Avec loyer fictif)']

        ];

        return new JsonResponse(static::formatData($lodgingType), 200);
    }


    /**
     * typeTaux
     * Return raw data for dossier rate types
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */

    private function typeTaux(): JsonResponse
    {
        $typeTaux = [
            ['data' => 'fixe', 'label' => 'Taux fixe'],
            ['data' => 'variable', 'label' => 'Taux variable'],
        ];

        return new JsonResponse(static::formatData($typeTaux), 200);
    }

    /**
     * typePret
     * Return raw data
     * Use for replace raw data in front code
     *
     * @return JsonResponse
     */

    private function typePret(): JsonResponse
    {
        $typePret = [
            'inconnu',
            'amortissable',
            'rachat',
            'renouvelable'
        ];

        return new JsonResponse(static::formatData($typePret), 200);
    }

    /**
     * bankSavingProduct
     * Return raw data type of saving product
     *
     * @return JsonResponse
     */
    private function bankSavingProduct(): JsonResponse
    {
        $savingProduct = [
            ['data' => 'SAN', 'label' => 'Aucune Epargne'],
            ['data' => 'LDD', 'label' => 'Livret Développement Durable'],
            ['data' => 'CAT', 'label' => 'Compte à Terme'],
            ['data' => 'CEL', 'label' => 'Compte Epargne Logement'],
            ['data' => 'PEA', 'label' => 'Plan Epargne Action'],
            ['data' => 'CL', 'label' => 'Compte sur Livret'],
            ['data' => 'PEE', 'label' => 'Plan Epargne Entreprise'],
            ['data' => 'VIE', 'label' => 'Assurance Vie'],
            ['data' => 'PEL', 'label' => 'Plan Epargne Logement'],
            ['data' => 'LEP', 'label' => 'Livret Epargne Populaire'],
            ['data' => 'PER', 'label' => 'Plan Epargne Retraite']
        ];

        return new JsonResponse(static::formatData($savingProduct), 200);
    }

    /**
     * dossierGaranty
     * Return raw data type of garanty from files
     *
     * @return JsonResponse
     */
    private function dossierGaranty(): JsonResponse
    {
        $garanty = [
            '',
            'Hypothèque Conventionnelle',
            'PPD',
            'Hypothèque judiciaire',
            'Hypothèque Légale',
            'Caution Mutuelle',
            'Caution personne Physique'
        ];

        return new JsonResponse(static::formatData($garanty), 200);
    }

    /**
     * bankCard
     * Return raw data type of card
     *
     * @return JsonResponse
     */
    private function bankCard(): JsonResponse
    {
        $card = [
            ['data' => 'S', 'label' => 'Aucune'],
            ['data' => 'I', 'label' => 'CB à débit immédiat'],
            ['data' => 'D', 'label' => 'CB à débit différé']
        ];

        return new JsonResponse(static::formatData($card), 200);
    }

    /**
     * dateType
     * Return raw data of date type
     *
     * @return JsonResponse
     */
    private function dateType(): JsonResponse
    {
        $dateType = [
            'Date de création',
            'Date de modification',
            'Date de 1er Rendez-Vous',
            "Date d'encaissement"
        ];
        return new JsonResponse(static::formatData($dateType), 200);
    }

    /**
     * tallySheetLabels
     *
     * @return JsonResponse
     */
    private function tallySheetLabels() : JsonResponse
    {
        $labels = [
            'Mademoiselle',
            'Madame',
            'Monsieur',
            'Monsieur et Madame',
            'Monsieur ou Madame',
            'Monsieur et Monsieur',
            'Madame et Madame'
        ];

        return new JsonResponse(static::formatData($labels), 200);
    }

    /**
     * financingType
     *
     * @return JsonResponse
     */
    private function financingType() : JsonResponse
    {
        $financingType = [
            'Regroupement de crédit avec garantie hypothécaire' => 'regroupement de credit avec garantie hypothecaire' ,
            'Regroupement de crédit sans garantie hypothécaire' => 'regroupement de credit sans garantie hypothecaire' ,
            'Regroupement de crédits avec caution mutuelle' => 'Regroupement de crédits avec caution mutuelle'
        ];
        return new JsonResponse(static::formatData($financingType), 200);
    }

    /**
     * partImmo
     *
     * @return JsonResponse
     */
    private function partImmo() : JsonResponse
    {
        $partImmo = [
            'LS2' => 'part_immo_ls2',
            'LCC' => 'part_immo_lcc'
        ];
        return new JsonResponse(static::formatData($partImmo), 200);
    }

    /**
     * Type rappel for preconisation
     *
     * @return JsonResponse
     */
    private function preconisationRappelType() : JsonResponse
    {
        $typeRappel = [
            'rendezvous' => 'Rendez Vous',
            'relance tel' => 'Relance Téléphonique',
            'recherche ve' => 'Recherche VE',
            'banque' => 'Banque',
            'notaire' => 'notaire',
            'assurance' => 'Assurance',
            'partenaire' => 'Partenaire',
            'autre' => 'Autre'
        ];
        return new JsonResponse(static::formatData($typeRappel), 200);
    }

    /**
     * formatData
     * Return a formated array for result like every normal route
     *
     * @param  String[] $data
     * @return array $dataFormated
     */
    private static function formatData($data)
    {
        $dataFormated = ['data' => $data];

        return $dataFormated;
    }
}
