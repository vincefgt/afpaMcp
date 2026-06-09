<template>
  <q-page class="row justify-center q-pt-md">
    <div class="col-12">
      <div class="row q-col-gutter-sm">
        <!-- BLOC FORMULAIRE RECHERCHE-->
        <div class="col-3" v-if="isLightSearchVisible">
          <light-search
            @cancel="resetSearchForm"
            @submit="findSupports"
          >
            <template v-slot:forms>
              <form class="light_search_form q-mb-sm q-gutter-xs">
                <!-- lastName field  -->
                <base-neo-text-field
                  v-model="searchForm.supportName"
                  dense
                  :label="t('supports.datagrid.libelle')"
                />
                <base-neo-dropdown-list
                  v-model="searchForm.supportType"
                  :label="t('supports.datagrid.type')"
                  :options="TYPE_SUPPORT_OPTIONS"
                  dense
                  clearable
                />
                <base-neo-dropdown-list
                  v-model="searchForm.supportTypeFlux"
                  :label="t('supports.datagrid.typeFlux')"
                  :options="TYPE_FLUX_OPTIONS"
                  dense
                  clearable
                />
                <!-- <base-neo-dropdown-list
                  v-model="searchForm.supportCritereEtat"
                  :label="t('supports.datagrid.critereEtat')"
                  :options="CRITERE_ETAT_OPTIONS"
                  dense
                  clearable
                /> -->
              </form>
            </template>
          </light-search>
        </div>

        <!-- BLOC DATAGRID -->
        <div :class="isLightSearchVisible ? 'col-9' : 'col-12'">
          <base-neo-datagrid-fiche-client
            v-if="!gestionSupportsStore.isLoading"
            row-key="id"
            :rows="gestionSupportsStore.supportsFormatted"
            :columns="gestionSupportsStore.supportsDatagridColumns"
            is-full-height
            use-context-menu
            separator="cell"
            compact-mode
            :actionBtn="false"
            :context-menu-items="contextMenuItems"
            @click:context-item="({ itemMenu, data }) => handleMenuClick(itemMenu, data)"
            :rows-per-page="20"
          >
          </base-neo-datagrid-fiche-client>
          <q-inner-loading
            :showing="gestionSupportsStore.isLoading"
            color="primary"
            :label="t('supports.loading')"
            label-class="text-subtitle1 text-bold"
            class="inner-loading"
          />
        </div>
        <!-- footer bar -->
        <the-neo-footer is-btn-validate-visible @fn-validate="ajouterSupport" />
      </div>
    </div>
  </q-page>
</template>

<script lang="ts" setup>
import { useMainLayoutStore } from 'src/stores/main-layout.store';
import { useI18n } from 'vue-i18n';
import { useFooterStore } from 'src/stores/footer.store';
import { useConfirmationPopup } from 'src/composables/Popup.composable';
import { useGestionSupportsStore } from 'src/stores/gestion-supports.store';
import { useRouter } from 'vue-router';
import { onMounted, ref} from 'vue';
import { ItemContextMenu } from 'src/models/ItemContextMenu.interface';
import { CrudAction } from 'src/enums/CrudAction.enum';
import TheNeoFooter from 'src/components/TheNeoFooter.vue';
import LightSearch from 'src/components/LightSearch.vue';
import BaseNeoDatagridFicheClient from 'src/components/BaseNeoDatagridFicheClient.vue';
import BaseNeoTextField from 'src/components/BaseNeoTextField.vue';
import BaseNeoDropdownList from 'src/components/BaseNeoDropdownList.vue';
import { CRITERE_ETAT_OPTIONS, TYPE_FLUX_OPTIONS, TYPE_SUPPORT_OPTIONS } from 'src/constants/Support';
import { useAbility } from '@casl/vue';
import { useGestionCriteresStore } from 'src/stores/gestion-criteres.store';
import { nextTick } from 'process';

const { t } = useI18n();
const router = useRouter();
const mainLayoutStore = useMainLayoutStore();
const gestionSupportsStore = useGestionSupportsStore();
const footerStore = useFooterStore();
const isLightSearchVisible = ref<boolean>(true);
const { can } = useAbility();
const gestionCriteresStore = useGestionCriteresStore();

const searchForm = ref<{
  supportName: string;
  supportType: string;
  supportTypeFlux: string;
  supportCritereEtat: string;
}>({
  supportName: '',
  supportType: '',
  supportTypeFlux: '',
  supportCritereEtat: '',
});

onMounted(async () => {
  mainLayoutStore.setTitle(t('sideBar.supports').toLocaleUpperCase());
  mainLayoutStore.setIcon('icon-files-on.svg');
  mainLayoutStore.hideNavbar();
  mainLayoutStore.hideTabBar();
  footerStore.isBtnCancelVisible = false;
  footerStore.isBtnValidateVisible = true;
  footerStore.nameValidateBtn = t('supports.add');
  gestionSupportsStore.initSupportsPage();
  // // API CALL
  await gestionSupportsStore.getSupports(); 
  await gestionSupportsStore.applyCritereEtat(); //called one time
});

let contextMenuItems: Array<ItemContextMenu> = [
  { label: t('contextMenu.view'), event: 'consulter', isDisabled: false, appendIcon: 'visibility' },
  { label: t('contextMenu.edit'), event: 'modifier', isDisabled: false, appendIcon: 'edit' },
  { label: t('contextMenu.delete'), event: 'supprimer', isDisabled: !can('admin', 'utilisateur'), appendIcon: 'delete' },
  { label: t('criteres.critere'), event: 'critere', isDisabled: !can('admin', 'utilisateur'), appendIcon: 'visibility' }
];

async function handleMenuClick(itemMenu: ItemContextMenu, item: any) {
  switch (itemMenu.event) {
    case 'consulter':
      gestionSupportsStore.initSupportForm(item);
      gestionSupportsStore.action = CrudAction.READ;
      router.push({ name: 'supports-action', params: { action: CrudAction.READ.toLocaleLowerCase() } });
      break;

    case 'modifier':
      gestionSupportsStore.initSupportForm(item);
      gestionSupportsStore.action = CrudAction.UPDATE;
      router.push({ name: 'supports-action', params: { action: CrudAction.UPDATE.toLocaleLowerCase() } });
      break;

    case 'supprimer':
      // show confirmation popup
      // eslint-disable-next-line no-case-declarations
      const { confirmationPopup } = useConfirmationPopup(
        t('loginPage.confirmation'),
        t('popup.messages.areYouSureToDelete')
      );   
      confirmationPopup.onOk(({ clicked }) => {
        if (clicked === 'YES') {
          gestionSupportsStore.deleteSupport(Number(item.id));
        }
      });
      break;

      case 'critere':
        console.log(Number(item.id));
        const critere = await gestionCriteresStore.getCritereByIdSrc(Number(item.id));
        console.log(critere); // log if appel critere

        if (critere){
          gestionCriteresStore.initCritereForm({
            ...critere,
            idSupport: item.id,
            src: item.source,
            libelle: item.libelle});
          gestionCriteresStore.action = CrudAction.READ;
          router.push({ name: 'criteres-action', params: {action: CrudAction.READ.toLocaleLowerCase()}});
          break;
        } else {
            const { confirmationPopup } = useConfirmationPopup(
              t('loginPage.confirmation'),
              t('criteres.noFound')+t('popup.messages.areYouSureToCreate')
            );
          confirmationPopup.onOk(({ clicked }) => {
          if (clicked === 'YES') {
            gestionSupportsStore.action = CrudAction.CREATE;
            gestionCriteresStore.initCritereForm({
              idSupport: item.id,
              src: item.source,
              libelle: item.libelle,
              pro: true,
              loc: true,
              newRevLoc: true,
              newRevPro: true,
              hebergementFree: true,
              mut: true,
              inclusTreso: true,
              cdi: false,
              isDomtom: true,
              refusCoord: false,
            });
            router.push({ name: 'criteres-action', params: {action: CrudAction.CREATE.toLocaleLowerCase()}});
          }
          });
          break;
        }

    default:
      break;
  }
}

function ajouterSupport() {
  router.push({ name: 'supports-action', params: { action: CrudAction.CREATE.toLocaleLowerCase() } });
}

/**
 * Reset Light Search form
 */
async function resetSearchForm() {
  searchForm.value = {
    supportName: '',
    supportType: '',
    supportTypeFlux: '',
    supportCritereEtat: '',
  };
}

async function findSupports() {

  const criteria = {
    libelle: searchForm.value.supportName ? searchForm.value.supportName : undefined,
    type_support: searchForm.value.supportType ? searchForm.value.supportType.value : undefined,
    type_flux: searchForm.value.supportTypeFlux ? searchForm.value.supportTypeFlux.value : undefined,
    actif_crit: searchForm.value.supportCritereEtat ? searchForm.value.supportCritereEtat.value : undefined
  }
  await gestionSupportsStore.getByCriteria(criteria);
}

</script>
