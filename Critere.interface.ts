import { AutocompleteObjectModel } from './AutocompleteObjectModel.interface';

export interface critere {
  id: number | null;
  idSupport: number;
  src: string;
  libelle: string | null;
  actifCritere: boolean | null;
  recette: boolean | null;
  doublonDuree: number | null;
  leadHs: boolean | null;
  younitedRefusRac: boolean | null;
  ageMinPro: number | null;
  ageMaxPro: number | null;
  ageMinLoc: number | null;
  ageMaxLoc: number | null;
  cdi: boolean | null;
  pro: boolean | null;
  nbMinPretPro: number | null;
  nbMinPretConsoPro: number | null;
  nbMinPretImmoPro: number | null;
  crdMinPro: number | null;
  crdMaxPro: number | null;
  crdMinImmoPro: number | null;
  crdMinImmoLoc: number | null;
  crdMaxImmoPro: number | null;
  crdMaxImmoLoc: number | null;
  crdMinConsoPro: number | null;
  crdMaxConsoPro: number | null;
  revMinPro: number | null;
  revMinFoyerPro: number | null;
  mafMinPro: number | null;
  mafMaxPro: number | null;
  loc: boolean | null;
  nbMinPretLoc: number | null;
  nbMinPretConsoLoc: number | null;
  nbMinPretImmoLoc: number | null;
  crdMinLoc: number | null;
  crdMaxLoc: number | null;
  crdMinConsoLoc: number | null;
  crdMaxConsoLoc: number | null;
  revMinLoc: number | null;
  revMinFoyerLoc: number | null;
  mafMinLoc: number | null;
  mafMaxLoc: number | null;
  newRevLoc: boolean | null;
  newRevPro: boolean| null;
  onlyCrdConso: boolean | null;
  onlyPretConso: boolean | null;
  inclusTreso: boolean | null;
  tresoMin: number | null;
  hebergementFree: boolean | null;
  mut: boolean | null;
  refusCoord: boolean | null;
  profession: string | null;
  histoMut: string | null;
  isDomtom: boolean | null;
  comments: string | null;
  nbMinPretConso: number | null;
  nbMinPretImmo: number | null;
  txEndettementMinPro: number | null;
  txEndettementMaxPro: number | null;
  txEndettementMinLoc: number | null;
  txEndettementMaxLoc: number | null;
  profLibMinDuree: number | null;
  anon: boolean | null;
}

/**
 * critere for Datagrid display
 * Flattened and formatted for table presentation
 */
export interface critereDatagridRow {
  id: number | null;
  idSupport: number;
  src: string;
  actifCritere: boolean | null;
}

/**
 * critere validation error response
 */
export interface critereValidationError {
  field: keyof critere;
  message: string;
  value?: unknown;
}

/**
 * critere API response
 */
export interface critereResponse {
  success: boolean;
  data?: critere;
  errors?: critereValidationError[];
  message?: string;
}

/**
 * Type mapping for property type checking (mirrors PHP $types array)
 */
export const critere_TYPES: Record<keyof critere, string> = {
  id: 'int',
  idSupport: 'int',
  src: 'string',
  libelle: 'string',
  actifCritere: 'bool',
  recette: 'bool',
  doublonDuree: 'int',
  leadHs: 'bool',
  younitedRefusRac: 'bool',
  ageMinPro: 'int',   
  ageMaxPro: 'int',
  ageMinLoc: 'int',   
  ageMaxLoc: 'int',
  cdi: 'bool',
  pro: 'bool',
  newRevPro: 'bool',
  nbMinPretPro: 'int',
  nbMinPretConsoPro: 'int',
  nbMinPretImmoPro: 'int',
  crdMinPro: 'int',
  crdMaxPro: 'int',
  crdMaxImmoPro: 'int',
  crdMinImmoPro: 'int',
  crdMinConsoPro: 'int',
  crdMaxConsoPro: 'int',
  revMinPro: 'int',
  revMinFoyerPro: 'int',
  mafMinPro: 'int',
  mafMaxPro: 'int',
  loc: 'bool',
  nbMinPretLoc: 'int',
  nbMinPretConsoLoc: 'int',
  nbMinPretImmoLoc: 'int',
  crdMinLoc: 'int',
  crdMaxLoc: 'int',
  crdMaxImmoLoc: 'int',
  crdMinImmoLoc: 'int',
  crdMinConsoLoc: 'int',
  crdMaxConsoLoc: 'int',
  revMinLoc: 'int',
  revMinFoyerLoc: 'int',
  mafMinLoc: 'int',
  mafMaxLoc: 'int',
  newRevLoc: 'bool',
  onlyCrdConso: 'bool',
  onlyPretConso: 'bool',
  inclusTreso: 'bool',
  tresoMin: 'int',
  hebergementFree: 'bool',
  mut: 'bool',
  refusCoord: 'bool',
  profession: 'string',
  histoMut: 'string',
  isDomtom: 'bool',
  comments: 'string',
  nbMinPretConso: 'int',
  nbMinPretImmo: 'int',
  txEndettementMinPro: 'float',
  txEndettementMaxPro: 'float',
  txEndettementMinLoc: 'float',
  txEndettementMaxLoc: 'float',
  profLibMinDuree: 'int',
  anon: 'bool',
};

/**
 * Helper function to cast value to proper type (mirrors PHP set() method)
 */
export function castcritereValue(
  property: keyof critere,
  value: any
): any {
  if (value === null || value === undefined) {
    return null;
  }
  const type = critere_TYPES[property];
  switch (type) {
    case 'int':
      return parseInt(value, 10);
    case 'bool':
      return Boolean(value);
    case 'float':
      return parseFloat(value);
    case 'string':
      return String(value);
    default:
      return value;
  }
}

export interface critereForm  {
  id?: number | null;
  idSupport: number;
  src: string;
  libelle: string;
  actifCritere: boolean;
  recette?: boolean;
  doublonDuree?: number;
  leadHs?: boolean;
  younitedRefusRac?: boolean;
  cdi?: boolean;

  pro?: boolean;
  ageMinPro?: number;
  ageMaxPro?: number;
  nbMinPretPro?: number;
  nbMinPretConsoPro?: number;
  nbMinPretImmoPro?: number;
  crdMinPro?: number;
  crdMaxPro?: number;
  crdMinImmoPro?: number;
  crdMinImmoLoc?: number;
  crdMaxImmoPro?: number;
  crdMaxImmoLoc?: number;
  crdMinConsoPro?: number;
  crdMaxConsoPro?: number;
  revMinPro?: number;
  revMinFoyerPro?: number;
  newRevPro?: number;
  mafMinPro?: number;
  mafMaxPro?: number;
  txEndettementMinPro?: number;
  txEndettementMaxPro?: number;

  loc?: boolean;
  ageMinLoc?: number;
  ageMaxLoc?: number;
  nbMinPretLoc?: number;
  nbMinPretConsoLoc?: number;
  nbMinPretImmoLoc?: number;
  crdMinLoc?: number;
  crdMaxLoc?: number;
  crdMinConsoLoc?: number;
  crdMaxConsoLoc?: number;
  revMinLoc?: number;
  revMinFoyerLoc?: number;
  mafMinLoc?: number;
  mafMaxLoc?: number;
  newRevLoc?: boolean;
  txEndettementMinLoc?: number;
  txEndettementMaxLoc?: number;

  onlyCrdConso?: boolean;
  onlyPretConso?: boolean;
  inclusTreso?: boolean;
  tresoMin?: number;
  hebergementFree?: boolean;
  mut?: boolean;
  refusCoord?: boolean;
  profession?: string;
  histoMut?: string;
  isDomtom?: boolean;
  comments?: string;
  profLibMinDuree?: number;
  anon?: boolean;
}
