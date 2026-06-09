import { AxiosError, AxiosResponse } from 'axios';
import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import { Support, SupportForm, SupportsDatagridRow } from 'src/models/Support.interface';
import { DatagridColumns } from 'src/models/DatagridColumns.interface';
import { useI18n } from 'vue-i18n';
import { CrudAction } from 'src/enums/CrudAction.enum';
import { AutocompleteObjectModel } from 'src/models/AutocompleteObjectModel.interface';
import { RESERVATION_OPTIONS, TYPE_FLUX_OPTIONS, TYPE_PUB_OPTIONS, TYPE_SUPPORT_OPTIONS, CRITERE_ETAT_OPTIONS } from 'src/constants/Support';
import { useLoadingPopup } from 'src/composables/Popup.composable';
import { useToast } from 'src/composables/Toast.composable';
import { ToastType } from 'src/enums/ToastType.enum';
import { i18n } from 'src/boot/i18n';
import { formatYYYYMMDDtoDDMMYYYY } from 'src/constants/NeoRules';
import { useGestionCriteresStore } from 'src/stores/gestion-criteres.store';
import { uniqueId } from 'lodash';

export const useGestionSupportsStore = defineStore('gestionSupportsStore', {
  state: () => ({
    supportsFormatted: <SupportsDatagridRow[]>[],
    supportsDatagridColumns: [] as DatagridColumns[],
    action: CrudAction.CREATE as CrudAction,
    supportForm: {} as SupportForm,
    typeSupportOptions: [] as AutocompleteObjectModel[],
    typePubOptions: [] as AutocompleteObjectModel[],
    typeFluxOptions: [] as AutocompleteObjectModel[],
    reservationOptions: [] as AutocompleteObjectModel[],
    isLoading: false,

  }),

  getters: {
    isInAddMode: (state) => state.action === CrudAction.CREATE,
    isInReadMode: (state) => state.action === CrudAction.READ,
    isInUpdateMode: (state) => state.action === CrudAction.UPDATE,
  },

  actions: {
    initSupportsPage() {
      const { t } = useI18n();

      this.supportsFormatted = [];

      this.supportsDatagridColumns = [
        {
          name: 'id',
          align: 'left',
          label: t('supports.datagrid.id'),
          field: 'id',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'libelle',
          align: 'left',
          label: t('supports.datagrid.libelle'),
          field: 'libelle',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'type',
          align: 'left',
          label: t('supports.datagrid.type'),
          field: 'type',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'source',
          align: 'left',
          label: t('supports.datagrid.source'),
          field: 'source',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'typePub',
          align: 'left',
          label: t('supports.datagrid.typePub'),
          field: 'typePub',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'reservation',
          align: 'left',
          label: t('supports.datagrid.reservation'),
          field: 'reservation',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'typeFlux',
          align: 'left',
          label: t('supports.datagrid.typeFlux'),
          field: 'typeFlux',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'actif',
          align: 'left',
          label: t('supports.datagrid.actif'),
          field: 'actif',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'actif_crit',
          align: 'left',
          label: t('supports.datagrid.critereEtat'),
          // field: 'actif_crit',
          field: 'actif_crit',
          sortable: true,
          editable: false,
          visible: true
        },
        {
          name: 'derniereUtilisation',
          align: 'left',
          label: t('supports.datagrid.derniereUtilisation'),
          field: 'derniereUtilisation',
          sortable: true,
          editable: false,
          visible: true
        }
      ];
    },

    /**
     * @desc Get supports from api
     */
    getSupports(): Promise<Support[]> {
      this.isLoading = true;
      return new Promise((resolve, reject) => {
        api
          // .get('/support/findByAffiliations?active=true&checkAffiliation=true')
          .get('/support/findByAffiliations?checkAffiliation=true')
          .then((res: AxiosResponse) => {
            this.supportsFormatted = res.data.data?.map(this.formatSupportsDatagridRow);
            resolve(res.data.data);
            console.log(res.data.data); // support
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

    // formatSupportsDatagridRow(supportFromAPI: [Record<string, any>, Record<string, string> | null]): SupportsDatagridRow {
    //   return {
    //     id: Number(supportFromAPI[0].id_support),
    //     libelle: supportFromAPI[0].libelle,
    //     type: supportFromAPI[0].type,
    //     source: supportFromAPI[0].src,
    //     typePub: supportFromAPI[0].typePub,
    //     reservation: supportFromAPI[0].reservationId ? RESERVATION_OPTIONS.find((option) => option.value === supportFromAPI[0].reservationId)?.label || '' : '',
    //     typeFlux: supportFromAPI[0].typeFlux,
    //     derniereUtilisation: supportFromAPI[1] ? formatYYYYMMDDtoDDMMYYYY(supportFromAPI[1].date)?.replaceAll('-', '/') : '',
    //     actif: supportFromAPI[0].actif,
    //     critereEtat: supportFromAPI[0].critereEtat // retourne etat du critere
    //   };
    // },
    formatSupportsDatagridRow(supportFromAPI: [Record<string, any>, Record<string, string> | null]
    ): SupportsDatagridRow {
      return {
        id: Number(supportFromAPI[0].id_support),
        libelle: supportFromAPI[0].libelle,
        type: supportFromAPI[0].type,
        source: supportFromAPI[0].src,
        typePub: supportFromAPI[0].typePub,
        reservation: supportFromAPI[0].reservationId ? RESERVATION_OPTIONS.find(option => option.value === supportFromAPI[0].reservationId)?.label || '' : '',
        typeFlux: supportFromAPI[0].typeFlux,
        derniereUtilisation: supportFromAPI[1] ? formatYYYYMMDDtoDDMMYYYY(supportFromAPI[1].date)?.replaceAll('-', '/') : '',
        actif: supportFromAPI[0].actif,
        actif_crit: supportFromAPI[0].actif_crit === true ? 'Actif' : supportFromAPI[0].actif_crit === false? 'Inactif' : ''
      };
    },

    formatSupportsDatagridRowSearch(supportFromAPI: Support): SupportsDatagridRow {
      return {
        id: Number(supportFromAPI.id_support),
        libelle: String(supportFromAPI.libelle),
        type: String(supportFromAPI.type),
        source: String(supportFromAPI.src),
        typePub: String(supportFromAPI.typePub),
        reservation: supportFromAPI.reservationId ? RESERVATION_OPTIONS.find((option) => option.value === supportFromAPI.reservationId)?.label || '' : '',
        typeFlux: String(supportFromAPI.typeFlux),
        derniereUtilisation: '',
        actif: supportFromAPI.actif,
        phraseAccroche: supportFromAPI.phraseAccroche,
        origineFlux: supportFromAPI.origineFlux,
        actif_crit: supportFromAPI.actif_crit === true? 'Actif': supportFromAPI.actif_crit === false? 'Inactif': ''
      };
    },

    async applyCritereEtat() {
      const gestionCriteresStore = useGestionCriteresStore();
      const critAll = await gestionCriteresStore.getCriteresAll();
      this.supportsFormatted = this.supportsFormatted.map(support => {
      const val = gestionCriteresStore.criteresBySupportId[support.id];
        return {
          ...support,
         actif_crit: val === true ? 'Actif': val === false ? 'Inactif': ''
        };
      });
    },
      
    clearActifCrit() {
      this.supportsFormatted = this.supportsFormatted.map(support => ({
        ...support,
        actif_crit: null
      }));
    },

    initSupportForm(support?: SupportsDatagridRow) {
      this.typeSupportOptions = TYPE_SUPPORT_OPTIONS;
      this.typePubOptions = TYPE_PUB_OPTIONS;
      this.typeFluxOptions = TYPE_FLUX_OPTIONS;
      this.reservationOptions = RESERVATION_OPTIONS;

      this.supportForm = {
        id_support: support?.id || '',
        libelle: support?.libelle || '',
        actif: support?.actif === false ? false : true,
        type: support?.type
          ? TYPE_SUPPORT_OPTIONS.find((option) => option.value === support.type)
          : TYPE_SUPPORT_OPTIONS.find((option) => option.value === 'presse'),
        source: support?.source || '',
        typePub: support?.typePub ? TYPE_PUB_OPTIONS.find((option) => option.value === support.typePub) : undefined,
        typeFlux: support?.typeFlux ? TYPE_FLUX_OPTIONS.find((option) => option.value === support.typeFlux) : undefined,
        reservation: support?.reservation
          ? RESERVATION_OPTIONS.find((option) => option.label === support.reservation)
          : RESERVATION_OPTIONS.find((option) => option.value === 'libre'),
        origineFlux: support?.origineFlux,
        phraseAccroche: support?.phraseAccroche,
        visible: true,
        actif_crit: support?.actif_crit,
      };
    },



    createSupport() {
      const { loadingPopup } = useLoadingPopup();

      loadingPopup.show();

      const body = {
        support: JSON.stringify({
          libelle: this.supportForm.libelle,
          actif: this.supportForm.actif,
          type: this.supportForm.type?.value || '',
          src: this.supportForm.source,
          typePub: this.supportForm.typePub?.value || '',
          typeFlux: this.supportForm.typeFlux?.value || '',
          reservationId: this.supportForm.reservation,
          origineFlux: this.supportForm.origineFlux || '',
          phraseAccroche: this.supportForm.phraseAccroche || '',
          visible: true
        })
      };

      return new Promise((resolve, reject) => {
        api
          .post('/support', body)
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

    updateSupport() {
      const { loadingPopup } = useLoadingPopup();

      loadingPopup.show();

      const body: Support = {
        id_support: this.supportForm.id_support.toString(),
        libelle: this.supportForm.libelle,
        actif: this.supportForm.actif,
        type: this.supportForm.type?.value || '',
        src: this.supportForm.source,
        typePub: this.supportForm.typePub?.value || '',
        typeFlux: this.supportForm.typeFlux?.value || '',
        reservationId: this.supportForm.reservation,
        origineFlux: this.supportForm.origineFlux || '',
        phraseAccroche: this.supportForm.phraseAccroche || '',
        visible: true
      };

      const urlencoded = new URLSearchParams();
      urlencoded.append('support', JSON.stringify(body));

      console.log(JSON.stringify(body));
      return new Promise((resolve, reject) => {
        api
          .put('/support', urlencoded)
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

    async getByCriteria(criteria: any) {
      this.isLoading = true;

      const data = {
        criteria: JSON.stringify(criteria)
      };

      try {
        const res: AxiosResponse = await api.post('/support/search', data);

        this.supportsFormatted = res.data.data?.map(this.formatSupportsDatagridRowSearch);
        this.clearActifCrit();
        await this.applyCritereEtat();

      } catch (err: any) {
        console.log(err.response);
      } finally {
        this.isLoading = false;
      }
    },


    deleteSupport(id: number) {
      const { loadingPopup } = useLoadingPopup();

      loadingPopup.show();
      return new Promise((resolve, reject) => {
        api
          .delete(`/support/${id}`)
          .then((res: AxiosResponse) => {
            useToast(
              i18n.global.t('toast.title.success'),
              i18n.global.t('toast.message.operationSucceed'),
              ToastType.SUCCESS
            );
            this.supportsFormatted = this.supportsFormatted.filter((support) => Number(support.id) !== id);
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
    }
  }
});
