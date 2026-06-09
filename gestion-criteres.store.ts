import { AxiosError, AxiosResponse } from 'axios';
import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import { critere, critereDatagridRow, critereForm } from 'src/models/Critere.interface';
import { DatagridColumns } from 'src/models/DatagridColumns.interface';
import { useI18n } from 'vue-i18n';
import { CrudAction } from 'src/enums/CrudAction.enum';
import { useLoadingPopup } from 'src/composables/Popup.composable';
import { useToast } from 'src/composables/Toast.composable';
import { ToastType } from 'src/enums/ToastType.enum';
import { i18n } from 'src/boot/i18n';
import { castcritereValue, critere_TYPES } from 'src/models/Critere.interface';

const normalize = (val: any) => {
  if (val === '' || val === null || val === undefined) return null
  if (typeof val === 'boolean') return val
  if (Array.isArray(val)) return [...val].sort() // handle arrays
  if (!isNaN(val) && val !== '') return Number(val) // numeric normalization
  return val
}

export const useGestionCriteresStore = defineStore('gestionCriteresStore', {
  state: () => ({
    allCriteres: [] as critereDatagridRow[],
    CriteresFormatted: [] as critereDatagridRow[],
    CriteresDatagridColumns: [] as DatagridColumns[],
    action: CrudAction.CREATE as CrudAction,
    critereForm: {} as critereForm,
    isLoading: false,     
    isModalOpen: false,
    criteresBySupportId: {} as Record<number,boolean | null>,
  }),

  getters: {
    isInAddMode: (state) => state.action === CrudAction.CREATE,
    isInReadMode: (state) => state.action === CrudAction.READ,
    isInUpdateMode: (state) => state.action === CrudAction.UPDATE
  },

  actions: {
    initCriteresPage() {
      const { t } = useI18n();
      this.CriteresFormatted = [];
      this.CriteresDatagridColumns = [
        {
          name: 'id',
          align: 'left',
          label: t('criteres.datagrid.id'),
          field: 'id',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'idSupport',
          align: 'left',
          label: t('criteres.datagrid.idSupport'),
          field: 'idSupport',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'src',
          align: 'left',
          label: t('criteres.datagrid.src'),
          field: 'src',
          sortable: true,
          editable: false,
          visible: true
        },
      ];
    },

    /**
     * @desc Get Criteres from api
     */
    getCriteresAll(): Promise<critereForm []> {
      this.isLoading = true;
      return new Promise((resolve, reject) => {
        api
          .get('/criteres/All')
          .then((res: AxiosResponse) => {
            const data: critere [] = res.data?.data ? res.data?.data : [];
            this.CriteresFormatted = data.map(this.formatCriteresDatagridRow);
            this.allCriteres = data;
            console.log(res.data.data);
            // for return etat critere into support page (lookup)
            this.criteresBySupportId = {};
            data.forEach((c) => {
              if (c.idSupport) {
                this.criteresBySupportId[Number(c.idSupport)] = c.actifCritere;
              }
            });
            resolve(res.data.data);
            // console.log(res.data.data);
          })
          .catch((err: AxiosError) => {
            console.log(err.response);
            reject(err);
          })
          .finally(() => {
            this.isLoading = false;
          });
      });
    },
    
    getCritereByIdSrc(idSupport : number): Promise<critere> {
      this.isLoading = true;
      return new Promise((resolve, reject) => {
        api
          .get(`/critere/src=${idSupport}`)
          .then((res: AxiosResponse) => {
            const data: critere = res.data?.data ? res.data?.data : null;
            resolve(res.data.data);
            console.log(res.data.data);
          })
          .catch((err: AxiosError) => {
            console.log(err.response);    
            reject(err);
          })
          .finally(() => {
            this.isLoading = false;
          });
      });
    },

    formatCriteresDatagridRow(critereFromAPI: Record<string, any>): critere {
      return {
        id: critereFromAPI.id,
        idSupport: critereFromAPI.idSupport,
        src: critereFromAPI.src,
        // typePub: critereFromAPI.typePub,
        actifCritere: critereFromAPI.actifCritere,
        libelle: critereFromAPI.libelle,
        recette: critereFromAPI.recette,
        doublonDuree: critereFromAPI.doublonDuree,
        leadHs: critereFromAPI.leadHs,
        younitedRefusRac: critereFromAPI.younitedRefusRac,
        ageMinPro: critereFromAPI.ageMinPro,
        ageMaxPro: critereFromAPI.ageMaxPro,
        ageMinLoc: critereFromAPI.ageMinLoc,
        ageMaxLoc: critereFromAPI.ageMaxLoc,
        cdi: critereFromAPI.cdi,
        newRevPro: critereFromAPI.newRevPro,
        pro: critereFromAPI.proprietaire,
        nbMinPretPro: critereFromAPI.nbMinPretPro,
        nbMinPretConsoPro: critereFromAPI.nbMinPretConsoPro,
        nbMinPretImmoPro: critereFromAPI.nbMinPretImmoPro,
        crdMinPro: critereFromAPI.crdMinPro,
        crdMaxPro: critereFromAPI.crdMaxPro,
        crdMinImmoPro: critereFromAPI.crdMinImmoPro,
        crdMaxImmoPro: critereFromAPI.crdMaxImmoPro,
        crdMinImmoLoc: critereFromAPI.crdMinImmoLoc,
        crdMaxImmoLoc: critereFromAPI.crdMaxImmoLoc,
        crdMinConsoPro: critereFromAPI.crdMinConsoPro,
        crdMaxConsoPro: critereFromAPI.crdMaxConsoPro,
        revMinPro: critereFromAPI.revMinPro,
        revMinFoyerPro: critereFromAPI.revMinFoyerPro,
        mafMinPro: critereFromAPI.mafMinPro,
        mafMaxPro: critereFromAPI.mafMaxPro,
        loc: critereFromAPI.loc,
        nbMinPretLoc: critereFromAPI.nbMinPretLoc,
        nbMinPretConsoLoc: critereFromAPI.nbMinPretConsoLoc,
        nbMinPretImmoLoc: critereFromAPI.nbMinPretImmoLoc,
        crdMinLoc: critereFromAPI.crdMinLoc,
        crdMaxLoc: critereFromAPI.crdMaxLoc,
        crdMinConsoLoc: critereFromAPI.crdMinConsoLoc,
        crdMaxConsoLoc: critereFromAPI.crdMaxConsoLoc,
        revMinLoc: critereFromAPI.revMinLoc,
        revMinFoyerLoc: critereFromAPI.revMinFoyerLoc,
        mafMinLoc: critereFromAPI.mafMinLoc,
        mafMaxLoc: critereFromAPI.mafMaxLoc,
        newRevLoc: critereFromAPI.newRevLoc,
        onlyCrdConso: critereFromAPI.onlyCrdConso,
        onlyPretConso: critereFromAPI.onlyPretConso,
        inclusTreso: critereFromAPI.inclusTreso,
        tresoMin: critereFromAPI.tresoMin,
        hebergementFree: critereFromAPI.hebergementFree,
        mut: critereFromAPI.mut,
        refusCoord: critereFromAPI.refusCoord,
        profession: critereFromAPI.profession,
        histoMut: critereFromAPI.histoMut,
        isDomtom: critereFromAPI.isDomtom,
        comments: critereFromAPI.comments,
        nbMinPretConso: critereFromAPI.nbMinPretConso,
        nbMinPretImmo: critereFromAPI.nbMinPretImmo,
        txEndettementMinPro: critereFromAPI.txEndettementMinPro,
        txEndettementMaxPro: critereFromAPI.txEndettementMaxPro,
        txEndettementMaxLoc: critereFromAPI.txEndettementMaxLoc,
        txEndettementMinLoc: critereFromAPI.txEndettementMinLoc,
        profLibMinDuree: critereFromAPI.profLibMinDuree,
        anon: critereFromAPI.anon,
      };
    },

    formatCriteresDatagridRowSearch(critereFromAPI: critere): critereDatagridRow {
      return {
        id: Number(critereFromAPI.id),
        idSupport: Number(critereFromAPI.idSupport),
        src: critereFromAPI.src,
        actifCritere: Boolean(critereFromAPI.actifCritere),
      };
    },

    buildCriterePayload(): Partial<critere> {
      const result: any = {};

      Object.entries(this.critereForm).forEach(([key, value]) => {
        const typedKey = key as keyof critere;

        if (typedKey === 'profession') {
          result[typedKey] = value ? String(value) : null;
        } else {
          result[typedKey] = normalize(value);
        }
      });

      result.actifCritere = true;

      return result;
    },

    // initCritereForm(critere?: Partial<critere>) {
    //   this.critereForm = Object.fromEntries(
    //     Object.entries(critere || {}).map(([key, value]) => [
    //       key,
    //       normalize(value)
    //     ])
    //   ) as critereForm;
    //   return this.critereForm;
    // },

    initCritereForm(critere?: Partial<critere>) {
      const result: any = {};

      for (const key in critere_TYPES) {
        const typedKey = key as keyof critere;

        result[typedKey] = castcritereValue(
          typedKey,
          critere?.[typedKey]
        );
      }

      this.critereForm = result as critereForm;

      return this.critereForm;
    },

    createCritere() {
      const { loadingPopup } = useLoadingPopup();
      loadingPopup.show();

      const body = {
        critere: JSON.stringify(this.buildCriterePayload())
      };

      return new Promise((resolve, reject) => {
        api
          .post('/critere', body)
          .then((res: AxiosResponse) => {
            useToast(
              i18n.global.t('toast.title.success'),
              i18n.global.t('toast.message.savedSuccessfully'),
              ToastType.SUCCESS
            );
            resolve(res.data);
          })
          .catch((err: any) => {
            console.log(err.response);
            useToast(
              i18n.global.t('toast.title.error'),
              err.response?.data?.errors[0]?.detail || i18n.global.t('toast.message.anErrorOccured'),
              ToastType.ERROR
            );
            reject(err);
          })
          .finally(() => {
            loadingPopup.hide();
          });
      });
    },

    updateCritere(id: number) {
      const { loadingPopup } = useLoadingPopup();
      loadingPopup.show();

      const urlencoded = new URLSearchParams();
      urlencoded.append('critere', JSON.stringify(this.buildCriterePayload()));


      return new Promise((resolve, reject) => {
        api
          .put(`/critere/${id}`, urlencoded)
          .then((res: AxiosResponse) => {
            useToast(
              i18n.global.t('toast.title.success'),
              i18n.global.t('toast.message.updatedSuccessfully'),
              ToastType.SUCCESS
            );
            resolve(res.data);
          })
          .catch((err: any) => {
            console.log(err.response);
            useToast(
              i18n.global.t('toast.title.error'),
              err.response?.data?.errors[0]?.detail || i18n.global.t('toast.message.anErrorOccured'),
              ToastType.ERROR
            );
            reject(err);
          })
          .finally(() => {
            loadingPopup.hide();
          });
      });
    },

    // delete > Actif/inactif
    deleteCritere(id: number) {
      const { loadingPopup } = useLoadingPopup();
      loadingPopup.show();
      return new Promise((resolve, reject) => {
        api
          .delete(`/critere/${id}`)
          .then((res: AxiosResponse) => {
            useToast(
              i18n.global.t('toast.title.success'),
              i18n.global.t('toast.message.operationSucceed'),
              ToastType.SUCCESS
            );
            // this.CriteresFormatted = this.allCriteres.filter((critere) => Number(critere.id) !== id);
            resolve(res.data);
          })
          .catch((err: any) => {
            console.log(err.response);
            useToast(
              i18n.global.t('toast.title.error'),
              err.response?.data?.errors[0]?.detail || i18n.global.t('toast.message.anErrorOccured'),
              ToastType.ERROR
            );
            reject(err);
          })
          .finally(() => {
            loadingPopup.hide();
          });
      });
    },

    searchByCriteria(criteria: {id?: number; idSupport?: number; src?: string;
    }) {
      this.CriteresFormatted = this.allCriteres.filter((row) => {
        return (
          // (!criteria.id || row.id.toString().includes(criteria.id)) && // permissif
          (criteria.id === undefined || row.id === criteria.id) &&
          // (criteria.id_support === undefined || row.idSupport === criteria.id_support) && //strict
          (!criteria.idSupport || row.idSupport === criteria.idSupport) &&
          (!criteria.src || row.src?.toLowerCase().includes(criteria.src.toLowerCase()))
        );
      });
    },

    resetSearch() {
      // this.CriteresFormatted = [...this.allCriteres];
      this.getCriteresAll();
    },

    openModal() {
      this.isModalOpen = true
    },

    closeModal() {
      this.isModalOpen = false
    }
  }
});