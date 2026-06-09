import { AutocompleteObjectModel } from './AutocompleteObjectModel.interface';

export interface Support {
  id_support?: string;
  libelle?: string;
  actif?: boolean;
  actif_crit?: boolean;
  type?: string;
  src?: string;
  typePub?: string;
  typeFlux?: string;
  reservationId?: string;
  origineFlux?: string;
  phraseAccroche?: string;
  visible?: boolean;
}

export interface FormattedSupport {
  id_support: string;
  libelle?: string;
  type?: string;
  format: string;
}

export interface SupportsDatagridRow {
  id: number;
  libelle: string;
  type: string;
  source: string;
  typePub: string;
  reservation: string;
  typeFlux: string;
  derniereUtilisation: string;
  origineFlux?: string;
  phraseAccroche?: string;
  actif?: boolean;
  actif_crit?: string | null;
}

export interface SupportForm {
  id_support: number | string;
  libelle: string;
  type?: AutocompleteObjectModel;
  source: string;
  typePub?: AutocompleteObjectModel;
  reservation?: AutocompleteObjectModel;
  typeFlux?: AutocompleteObjectModel;
  origineFlux?: string;
  phraseAccroche?: string;
  actif: boolean;
  visible: boolean,
  actif_crit: string | null | undefined,
}
