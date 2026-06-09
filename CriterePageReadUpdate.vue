<template>
  <q-page class="row q-pt-md">
    <q-form
    class="col-8 q-pa-md"
    ref="formRef"
    @submit="onSubmitCritere">
      <base-neo-panel :title="title+': '+gestionCriteresStore.critereForm.libelle+' / src: '+gestionCriteresStore.critereForm.src" bordered>
      <template #actions>
        <div class="row items-center q-gutter-sm">
          <q-badge
            color="orange"
            :class="{ 'invisible': !isDirty}"
            style="margin-right: 40px;"
          >
            {{ t('criteres.unsavedChanges') }}
          </q-badge>
        </div>
      </template>
      <div class="row q-col-gutter-md q-pa-xs idClass">
            <!-- <div class="col-2">
              <base-neo-textfield
                :label="t('criteres.datagrid.id')"
                required
                v-model="gestionCriteresStore.critereForm.id"
                dense
                :readonly="true"
              />
            </div> -->
            <div class="col-4 ">
              <label class="text-caption">
                {{ t('criteres.datagrid.idSupport') }}
              </label>
              <div class="text-body1">
                {{ gestionCriteresStore.critereForm.idSupport }}
              </div>
            </div>
            <div class="col-4 ">
              <label class="text-caption">
                {{ t('criteres.libelle') }}
              </label>
              <div class="text-body1">
                {{ gestionCriteresStore.critereForm.libelle }}
              </div>
            </div>
            <!-- <div class="col-4">
              <base-neo-textfield
              :label="t('criteres.libelle')"
              required
              v-model="gestionCriteresStore.critereForm.libelle"
              dense
              :readonly="true"
              />
            </div> -->
            <div class="col-4 ">
              <label class="text-caption">
                {{ t('criteres.critere') }}
              </label>
              <div class="text-body1">
                {{ gestionCriteresStore.critereForm.actifCritere ? t('criteres.actif') : t('criteres.inactif')}}
              </div>
            </div>
      </div>
      <q-card-actions class="row justify-center q-gutter-md q-pa-md">
        <q-btn-group >
        <!-- reset -->
          <base-neo-button
            v-if="isBtnResetVisible"
            :label="nameResetBtn"
            size="sm"
            @click="onReset"
            icon-right="update"
          />
        <!-- update -->
          <base-neo-button
            v-if="isBtnActionVisible"
            :label="nameUpdateBtn"
            size="sm"
            @click="onUpdate"
            icon-right="edit"
          />
          <!-- Actif/unactif -->
          <base-neo-button
            v-if="isBtnActifVisible"
            :label="nameActifBtn"
            color="primary-darken"
            size="sm"
            @click="onDelete"
           :icon-right="actifIcon"
          />
          <!-- Duplicat -->
          <!-- <base-neo-button
            v-if="isBtnDuplicatVisible"
            :label="nameDuplicatBtn"
            color="secondary"
            size="sm"
            @click="onDuplicat"
            icon-right="content_copy"
            disable
          /> -->
          </q-btn-group>
      </q-card-actions>
        <!-- checkbox -->
        <div class="row q-col-gutter-md q-pa-xs">
          <base-neo-checkbox
              :label="t('criteres.onlyCdi')"
              v-model="gestionCriteresStore.critereForm.cdi"
              dense
              :disabled="gestionCriteresStore.isInReadMode"
              class="q-mb-md"
              :class="{ 'dirty-checkbox': isFieldDirty('cdi')}"
            />
          <!-- <base-neo-checkbox
              :label="t('criteres.onlyCrdConso')"
              v-model="gestionCriteresStore.critereForm.onlyCrdConso"
              dense
              :disabled="gestionCriteresStore.isInReadMode"
              class="q-mb-md"
            />
          <base-neo-checkbox
            :label="t('criteres.onlyPretConso')"
            v-model="gestionCriteresStore.critereForm.onlyPretConso"
            dense
            :disabled="gestionCriteresStore.isInReadMode"
            class="q-mb-md"
          /> -->
          <base-neo-checkbox
              :label="t('criteres.isDomtom')"
              v-model="gestionCriteresStore.critereForm.isDomtom"
              dense
              :disabled="gestionCriteresStore.isInReadMode"
              class="q-mb-md"
              :class="{ 'dirty-checkbox': isFieldDirty('isDomtom')}"
          />
          <base-neo-checkbox
            :label="t('criteres.refusCoord')"
            v-model="gestionCriteresStore.critereForm.refusCoord"
            dense
            :disabled="gestionCriteresStore.isInReadMode"
            class="q-mb-md"
            :class="{ 'dirty-checkbox': isFieldDirty('refusCoord')}"
          />
          <base-neo-checkbox
            :label="t('criteres.anon')"
            v-model="gestionCriteresStore.critereForm.anon"
            dense
            :disabled="gestionCriteresStore.isInReadMode"
            class="q-mb-md"
            :class="{ 'dirty-checkbox': isFieldDirty('anon')}"
          />
        </div>
        <!-- profession / liberal -->
        <div class="row q-col-gutter-md q-pa-xs">
          <div class="col-9"> 
            <base-neo-dropdown-multi-select
              v-model="currentProf"
              :label="t('criteres.profession')"
              :max-values="10"
              dense
              :options="stringOptions"
              :disabled="gestionCriteresStore.isInReadMode"
              :class="{'orange-outline': isFieldDirty('profession')}"
            />
        </div>
          <div class="col-3">
            <base-neo-number
                :label="t('criteres.profLibMinDuree')"
                positiveOnly
                v-model="gestionCriteresStore.critereForm.profLibMinDuree"
                :readonly="gestionCriteresStore.isInReadMode"
                dense
                :class="{'orange-outline': isFieldDirty('profLibMinDuree')}"
            />
          </div>
        </div>
        <!-- pro / loc -->
        <div class="row q-col-gutter-md q-pa-xs">
          <!-- pro -->
          <div class="col-12 col-md-6" >
            <q-card flat bordered class="q-pa-md bg-amber-1 rounded-borders">
              <base-neo-checkbox
                :label="t('criteres.proprietaire')"
                required
                v-model="gestionCriteresStore.critereForm.pro"
                dense
                :disabled="gestionCriteresStore.isInReadMode"
                class="q-mb-md"
                :class="{ 'dirty-checkbox': isFieldDirty('pro')}"
              />
              <!-- Age Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                <base-neo-number
                  :label="t('criteres.ageMin')"
                  v-model="gestionCriteresStore.critereForm.ageMinPro"
                  dense
                  :rules="[
                    NeoRules.isLessThanOrEqualTo(() => gestionCriteresStore.critereForm.ageMaxPro, t('criteres.shouldBeL')+' '+t('criteres.ageMax'))
                  ]"
                  positiveOnly
                  :readonly="gestionCriteresStore.isInReadMode"
                  ref="ageMinProRef"
                  :class="{'orange-outline': isFieldDirty('ageMinPro')}"
                />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.ageMax')"
                    v-model="gestionCriteresStore.critereForm.ageMaxPro"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.ageMinPro, t('criteres.shouldBeG')+' '+t('criteres.ageMin'))
                    ]"
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="ageMaxProRef"
                    :class="{'orange-outline': isFieldDirty('ageMaxPro')}"
                  />
                </div>
              </div>
              <div class="col-6">
                <base-neo-number
                  :label="t('criteres.nbMinPret')"
                  v-model="gestionCriteresStore.critereForm.nbMinPretPro"
                  dense
                  positiveOnly
                  :readonly="gestionCriteresStore.isInReadMode"
                  :class="{'orange-outline': isFieldDirty('nbMinPretPro')}"
                />
              </div>
              <!-- nb Pret CONSO / IMMO Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.nbMinPretConso')"
                    v-model="gestionCriteresStore.critereForm.nbMinPretConsoPro"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('nbMinPretConsoPro')}"
                  />
              </div>
              <div class="col-6">
                <base-neo-number
                  :label="t('criteres.nbMinPretImmo')"
                  v-model="gestionCriteresStore.critereForm.nbMinPretImmoPro"
                  dense
                  positiveOnly
                  :readonly="gestionCriteresStore.isInReadMode"
                  :class="{'orange-outline': isFieldDirty('nbMinPretImmoPro')}"
                />
              </div>
              </div>
              <!-- CDR Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMin')"
                    v-model="gestionCriteresStore.critereForm.crdMinPro"
                    dense
                    positiveOnly
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.crdMaxPro,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.crdMaxPro,
                          t('criteres.shouldBeL') + ' ' + t('criteres.crdMax')
                      )),
                    ]"
                    ref="crdMinPro"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMinPro')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMax')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMaxPro"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.crdMinPro, 
                      t('criteres.shouldBeG')+' '+t('criteres.crdMin'))
                    ]"
                    ref="crdMaxPro"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMaxPro')}"
                  />
                </div>
              </div>
              <!-- CRD Immo Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMinImmo')"
                    v-model="gestionCriteresStore.critereForm.crdMinImmoPro"
                    dense
                    positiveOnly
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.crdMaxImmoPro,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.crdMaxImmoPro,
                          t('criteres.shouldBeL') + ' ' + t('criteres.crdMaxImmo')
                      )),
                    ]"
                    ref="crdMinImmoProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMinImmoPro')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMaxImmo')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMaxImmoPro"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.crdMinImmoPro, 
                      t('criteres.shouldBeG')+' '+t('criteres.crdMinImmo'))
                    ]"
                    ref="crdMaximmoProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMaxImmoPro')}"
                  />
                </div>
              </div>
              <!-- CRD CONSO Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMinConso')"
                    v-model="gestionCriteresStore.critereForm.crdMinConsoPro"
                    dense
                    positiveOnly
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.crdMaxConsoPro,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.crdMaxConsoPro,
                          t('criteres.shouldBeL') + ' ' + t('criteres.crdMaxConso')
                      )),
                    ]"
                    ref="crdMinConsoProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMinConsoPro')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMaxConso')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMaxConsoPro"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.crdMinConsoPro, 
                      t('criteres.shouldBeG')+' '+t('criteres.crdMinConso'))
                    ]"
                    ref="crdMaxConsoProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMaxConsoPro')}"
                  />
                </div>
              </div>
                <!-- rev Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.revMin')"
                    v-model="gestionCriteresStore.critereForm.revMinPro"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('revMinPro')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.revMinFoyer')"
                    v-model="gestionCriteresStore.critereForm.revMinFoyerPro"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('revMinFoyerPro')}"
                  />
                </div>
              </div>
              <base-neo-checkbox
                :label="t('criteres.newRev')"
                v-model="gestionCriteresStore.critereForm.newRevPro"
                :disabled="gestionCriteresStore.isInReadMode"
                dense
                class="q-mb-md"
                :class="{ 'dirty-checkbox': isFieldDirty('newRevPro')}"
              />
              <!-- MAF Pro -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.mafMin')"
                    v-model="gestionCriteresStore.critereForm.mafMinPro"
                    dense
                    positiveOnly
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.mafMaxPro,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.mafMaxPro,
                          t('criteres.shouldBeL') + ' ' + t('criteres.mafMax')
                      )),
                    ]"
                    ref="mafMinProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('mafMinPro')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.mafMax')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.mafMaxPro"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.mafMinPro, 
                      t('criteres.shouldBeG')+' '+t('criteres.mafMin'))
                    ]"
                    ref="mafMaxProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('mafMaxPro')}"
                  />
                </div>
              </div>
              <!-- Tx endettement Pro  -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">  
                  <base-neo-number
                    :label="t('criteres.txEndettementMin')"
                    v-model="gestionCriteresStore.critereForm.txEndettementMinPro"
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    dense
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.txEndettementMaxPro,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.txEndettementMaxPro,
                          t('criteres.shouldBeL') + ' ' + t('criteres.txEndettementMax')
                      )),
                    ]"
                    ref="txEndettementMinProRef"
                    :class="{'orange-outline': isFieldDirty('txEndettementMinPro')}"
                  />
                </div>
                <div class="col-6"> 
                  <base-neo-number
                    :label="t('criteres.txEndettementMax')"
                    v-model="gestionCriteresStore.critereForm.txEndettementMaxPro"
                    dense
                    positiveOnly
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.txEndettementMinPro, 
                      t('criteres.shouldBeG')+' '+t('criteres.txEndettementMin'))
                    ]"
                    ref="txEndettementMaxProRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('txEndettementMaxPro')}"
                  />
                </div>
              </div>

            </q-card>
          </div>
          
          <!-- loc -->
          <div class="col-12 col-md-6">
            <q-card flat bordered class="q-pa-md bg-blue-grey-1 rounded-borders">
              <base-neo-checkbox
                :label="t('criteres.locataire')"
                v-model="gestionCriteresStore.critereForm.loc"
                dense
                :readonly="gestionCriteresStore.isInReadMode"
                class="q-mb-md"
                :class="{ 'dirty-checkbox': isFieldDirty('loc')}"
              />
              <!-- Age Loc -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.ageMin')"
                    v-model="gestionCriteresStore.critereForm.ageMinLoc"
                    dense
                    :rules="[
                      NeoRules.isLessThanOrEqualTo(() => gestionCriteresStore.critereForm.ageMaxLoc, t('criteres.shouldBeL')+' '+t('criteres.ageMax'))
                    ]"
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="ageMinLocRef"
                    :class="{'orange-outline': isFieldDirty('ageMinLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.ageMax')"
                    v-model="gestionCriteresStore.critereForm.ageMaxLoc"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.ageMinLoc, t('criteres.shouldBeG')+' '+t('criteres.ageMin'))
                    ]"
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="ageMaxLocRef"
                    :class="{'orange-outline': isFieldDirty('ageMaxLoc')}"
                  />
                </div>
              </div>
              <base-neo-number
                :label="t('criteres.nbMinPret')"
                v-model="gestionCriteresStore.critereForm.nbMinPretLoc"
                dense
                positiveOnly
                :readonly="gestionCriteresStore.isInReadMode"
                :class="{'orange-outline': isFieldDirty('nbMinPretLoc')}"
              />
              <!-- nb Pret CONSO / IMMO Loc-->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.nbMinPretConso')"
                    v-model="gestionCriteresStore.critereForm.nbMinPretConsoLoc"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('nbMinPretConsoLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.nbMinPretImmo')"
                    v-model="gestionCriteresStore.critereForm.nbMinPretImmoLoc"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('nbMinPretImmoLoc')}"
                  />
                </div>
              </div>
              <!-- CRD Loc -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMin')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMinLoc"
                    dense
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.crdMaxLoc,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.crdMaxLoc,
                          t('criteres.shouldBeL') + ' ' + t('criteres.crdMax')
                      )),
                    ]"
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="crdMinLocRef"
                    :class="{'orange-outline': isFieldDirty('crdMinLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMax')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMaxLoc"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.crdMinLoc, 
                      t('criteres.shouldBeG')+' '+t('criteres.crdMin'))
                    ]"
                    ref="crdMaxLocRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMaxLoc')}"
                  />
                </div>
              </div>
              <!-- CRD IMMO Loc-->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMinImmo')"
                    v-model="gestionCriteresStore.critereForm.crdMinImmoLoc"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.crdMaxImmoLoc,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.crdMaxImmoLoc,
                          t('criteres.shouldBeL') + ' ' + t('criteres.crdMaxImmo')
                      )),
                    ]"
                    ref="crdMinImmoLocRef"
                    :class="{'orange-outline': isFieldDirty('crdMinImmoLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMaxImmo')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMaxImmoLoc"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.crdMinImmoLoc, 
                      t('criteres.shouldBeG')+' '+t('criteres.crdMinImmo'))
                    ]"
                    ref="crdMaxImmoLocRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('crdMaxImmoLoc')}"
                  />
                </div>
              </div>
              <!-- CRD CONSO Loc -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMinConso')"
                    v-model="gestionCriteresStore.critereForm.crdMinConsoLoc"
                    dense
                    positiveOnly
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.crdMaxConsoLoc,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.crdMaxConsoLoc,
                          t('criteres.shouldBeL') + ' ' + t('criteres.crdMaxConso')
                      )),
                    ]"
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="crdMinConsoLocRef"
                    :class="{'orange-outline': isFieldDirty('crdMinConsoLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.crdMaxConso')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.crdMaxConsoLoc"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.crdMinConsoLoc, 
                      t('criteres.shouldBeG')+' '+t('criteres.crdMinConso'))
                    ]"
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="crdMaxConsoLocRef"
                    :class="{'orange-outline': isFieldDirty('crdMaxConsoLoc')}"
                  />
                </div>
              </div>
              <!-- rev Loc-->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.revMin')"
                    v-model="gestionCriteresStore.critereForm.revMinLoc"
                    dense
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('revMinLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.revMinFoyer')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.revMinFoyerLoc"
                    dense
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('revMinFoyerLoc')}"
                  />
                </div>
              </div>
              <base-neo-checkbox
                :label="t('criteres.newRev')"
                v-model="gestionCriteresStore.critereForm.newRevLoc"
                :disabled="gestionCriteresStore.isInReadMode"
                dense
                class="q-mb-md"
                :class="{ 'dirty-checkbox': isFieldDirty('newRevLoc')}"
              />
              <!-- MAF Loc -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.mafMin')"
                    v-model="gestionCriteresStore.critereForm.mafMinLoc"
                    dense
                    positiveOnly
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.mafMaxLoc,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.mafMaxLoc,
                          t('criteres.shouldBeL') + ' ' + t('criteres.mafMax')
                      )),
                    ]"
                    ref="mafMinLocRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('mafMinLoc')}"
                  />
                </div>
                <div class="col-6">
                  <base-neo-number
                    :label="t('criteres.mafMax')"
                    positiveOnly
                    v-model="gestionCriteresStore.critereForm.mafMaxLoc"
                    dense
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.mafMinLoc, 
                      t('criteres.shouldBeG')+' '+t('criteres.mafMax'))
                    ]"
                    ref="mafMaxLocRef"
                    :readonly="gestionCriteresStore.isInReadMode"
                    :class="{'orange-outline': isFieldDirty('mafMaxLoc')}"
                  />
                </div>
              </div>
              <!-- tx endettement -->
              <div class="row q-col-gutter-md q-pa-xs">
                <div class="col-6"> 
                  <base-neo-number
                    :label="t('criteres.txEndettementMin')"
                    v-model="gestionCriteresStore.critereForm.txEndettementMinLoc"
                    positiveOnly
                    :readonly="gestionCriteresStore.isInReadMode"
                    dense
                    :rules="[
                      ignoreIfTargetEmpty(
                        () => gestionCriteresStore.critereForm.txEndettementMaxLoc,
                        NeoRules.isLessThanOrEqualTo(
                        () => gestionCriteresStore.critereForm.txEndettementMaxLoc,
                          t('criteres.shouldBeL') + ' ' + t('criteres.txEndettementMin')
                      )),
                    ]"
                    ref="txEndettementMinLocRef"
                    :class="{'orange-outline': isFieldDirty('txEndettementMinLoc')}"
                  />
                </div>
                <div class="col-6">  
                  <base-neo-number
                    :label="t('criteres.txEndettementMax')"
                    v-model="gestionCriteresStore.critereForm.txEndettementMaxLoc"
                    dense
                    positiveOnly
                    :rules="[
                      NeoRules.isGreaterThanOrEqualTo(() => gestionCriteresStore.critereForm.txEndettementMinLoc, 
                      t('criteres.shouldBeG')+' '+t('criteres.txEndettementMax'))
                    ]"
                    :readonly="gestionCriteresStore.isInReadMode"
                    ref="txEndettementMaxLocRef"
                    :class="{'orange-outline': isFieldDirty('txEndettementMaxLoc')}"
                  />
                </div>
                <base-neo-checkbox
                  :label="t('criteres.hebergementFree')"
                  v-model="gestionCriteresStore.critereForm.hebergementFree"
                  dense
                  :disabled="gestionCriteresStore.isInReadMode"
                  class="q-mb-md"
                  :class="{ 'dirty-checkbox': isFieldDirty('hebergementFree')}"
                />
              </div>
            </q-card>
          </div>
        </div>
        <!-- treso -->
        <div class="row q-col-gutter-md q-pa-xs ">
          <div class="col-6"> 
            <base-neo-number
              :label="t('criteres.tresoMin')"
              v-model="gestionCriteresStore.critereForm.tresoMin"
              positiveOnly
              :readonly="gestionCriteresStore.isInReadMode"
              dense
              :class="{'orange-outline': isFieldDirty('tresoMin')}"
            />
          </div>
          <div class="col-6"> 
            <base-neo-checkbox
              :label="t('criteres.inclusTreso')"
              v-model="gestionCriteresStore.critereForm.inclusTreso"
              dense
              :disabled="gestionCriteresStore.isInReadMode"
              class="q-mb-md"
              :class="{ 'dirty-checkbox': isFieldDirty('inclusTreso')}"
            />
          </div>
        </div>
        <!-- mutualisation -->
        <div class="row q-col-gutter-md q-pa-xs">
            <base-neo-checkbox
            :label="t('criteres.mut')"
            v-model="gestionCriteresStore.critereForm.mut"
            dense
            :disabled="gestionCriteresStore.isInReadMode"
            class="q-mb-md"
            :class="{ 'dirty-checkbox': isFieldDirty('mut')}"
          />
          <div class="col-9">  
          <base-neo-textfield
            :label="t('criteres.histoMut')"
            v-model="gestionCriteresStore.critereForm.histoMut"
            :readonly="gestionCriteresStore.isInReadMode"
            dense
            :class="{ 'dirty-checkbox': isFieldDirty('histoMut')}"
          />
          </div>
        </div>
        <!-- comments -->
        <div class="col-6 q-pa-xs"> 
          <base-neo-textfield
            :label="t('criteres.comments')"
            v-model="gestionCriteresStore.critereForm.comments"
            :readonly="gestionCriteresStore.isInReadMode"
            dense
            :class="{ 'dirty-checkbox': isFieldDirty('comments')}"
          />
        </div>
      </base-neo-panel>
    </q-form>
    <!-- footer bar -->
    <the-neo-footer is-btn-validate-visible @fn-validate="formRef?.submit()" @fn-cancel="onCancel" />
  </q-page>
<!-- <neoModal
  v-model="isConsultModalOpen"
  :supports="gestionSupportsStore.supportsFormatted"
  @select-support="onSupportSelected"
/> -->
</template>

<script lang="ts" setup>
import { useMainLayoutStore } from 'src/stores/main-layout.store';
import { useI18n } from 'vue-i18n';
import { computed, onBeforeMount, onMounted, onUnmounted, watch, reactive, nextTick } from 'vue';
import TheNeoFooter from 'src/components/TheNeoFooter.vue';
import { useFooterStore } from 'src/stores/footer.store';
import { useConfirmationPopup } from 'src/composables/Popup.composable';
import { useGestionSupportsStore } from 'src/stores/gestion-supports.store';
import { useRouter } from 'vue-router';
import BaseNeoPanel from 'src/components/BaseNeoPanel.vue';
import BaseNeoCheckbox from 'src/components/BaseNeoCheckbox.vue';
import BaseNeoTextfield from 'src/components/BaseNeoTextField.vue';
import { CrudAction } from 'src/enums/CrudAction.enum';
import { useToast } from 'src/composables/Toast.composable';
import { ToastType } from 'src/enums/ToastType.enum';
import { QForm } from 'quasar';
import { ref } from 'vue';
import BaseNeoDropdownMultiSelect from 'src/components/BaseNeoDropdownMultiSelect.vue'
import BaseNeoNumber from 'src/components/BaseNeoNumber.vue';
import { useGestionCriteresStore } from 'src/stores/gestion-criteres.store';
import BaseNeoButton from 'src/components/BaseNeoButton.vue';
import * as NeoRules from 'src/constants/NeoRules';

const { t } = useI18n();
const router = useRouter();
const mainLayoutStore = useMainLayoutStore();
const gestionSupportsStore = useGestionSupportsStore();
const gestionCriteresStore = useGestionCriteresStore();
const footerStore = useFooterStore();
const formRef = ref<QForm>();
import NeoModal from 'src/pages/components/NeoModal.vue';
const isConsultModalOpen = ref<boolean>(false);

import { cloneDeep, isEqual } from 'lodash'
const initialForm = ref<any>(null)

onBeforeMount(() => {
  gestionCriteresStore.action = router.currentRoute.value.params.action.toString().toUpperCase() as CrudAction;
    if (!gestionCriteresStore.critereForm.idSupport) {
      router.push({ name: 'supports' });
    }
});

onMounted(async () => {
  mainLayoutStore.setTitle(t('sideBar.criteres').toLocaleUpperCase());
  mainLayoutStore.setIcon('icon-files-on.svg');
  mainLayoutStore.hideNavbar();
  mainLayoutStore.hideTabBar();
  footerStore.isBtnValidateVisible = gestionCriteresStore.action !== CrudAction.READ;
  footerStore.nameValidateBtn = t('buttons.validate');
  footerStore.isBtnCancelVisible = true;
  footerStore.nameCancelBtn = t('buttons.cancel');
  initialForm.value = cloneDeep(normalizeForm(gestionCriteresStore.critereForm))
  isConsultModalOpen.value = true;
});

const normalizeForm = (form: any) => {
  const normalized: any = {}
  Object.keys(form).forEach(key => {
    normalized[key] = normalize(form[key])
  })
  return normalized
}

const isDirty = computed(() => {
  if (!initialForm.value) return false

  const current = normalizeForm(gestionCriteresStore.critereForm)

  return !isEqual(initialForm.value, current)
})

watch(isConsultModalOpen, (val) => {
  console.log("MODAL STATE:", val)
})

watch(isDirty, (val) => {
  footerStore.isBtnValidateVisible = val
})

const isFieldDirty = (field: keyof typeof gestionCriteresStore.critereForm) => {
  if (!initialForm.value) return false
  return normalize(gestionCriteresStore.critereForm[field]) !== initialForm.value[field]
}


const title = computed(() => {
  if (gestionCriteresStore.action === CrudAction.CREATE) {
    return t('criteres.title.creationCritere');
  } else if (gestionCriteresStore.action === CrudAction.UPDATE) {
    return t('criteres.title.miseAJourCritere');
  } else {
    return t('criteres.title.informationsCritere');
  }
});

function onUpdate() {
  gestionCriteresStore.action =CrudAction.UPDATE;
  footerStore.isBtnValidateVisible = true;
  router.push({ name: 'criteres-action', params: {action: CrudAction.UPDATE.toLocaleLowerCase()}});
}

function onDelete() {
  const { confirmationPopup } = useConfirmationPopup(t('loginPage.confirmation'), t('criteres.areYouSureToActif'));
  confirmationPopup.onOk(({ clicked }) => {
    if (clicked === 'YES') {
      gestionCriteresStore.deleteCritere(Number(gestionCriteresStore.critereForm.id));
      router.push({ name: 'supports' });
    }
  });
}

function onCancel() {
  const { confirmationPopup } = useConfirmationPopup(
    t('locals.confirmation'),
    t('popup.messages.areYouSureToCancel')
  )
  confirmationPopup.onOk(({ clicked }) => {
    if (clicked === 'YES') {
      router.push({ name: 'supports' });
    }
  })
}

//async function onDuplicat(){
//   await gestionSupportsStore.getSupports()
//   isConsultModalOpen.value = true;
// }

function onReset(){
    const { confirmationPopup } = useConfirmationPopup(
    t('locals.confirmation'),
    t('popup.messages.areYouSureToReset')
  )
  confirmationPopup.onOk(({ clicked }) => {
    if (clicked === 'YES') {
      gestionCriteresStore.initCritereForm({
      id: gestionCriteresStore.critereForm.id,
      idSupport: gestionCriteresStore.critereForm.idSupport,
      src: gestionCriteresStore.critereForm.src 
      });

      gestionCriteresStore.action = CrudAction.UPDATE;
      router.push({ name: 'criteres-action', params: {action: CrudAction.UPDATE.toLocaleLowerCase()}});
    }
  })

}

async function onSubmitCritere () {
  if (!(await formRef.value?.validate())) {
    return
  }
  if (gestionCriteresStore.action === CrudAction.CREATE) {
    await gestionCriteresStore.createCritere() 
  } else {
    await gestionCriteresStore.updateCritere(Number(gestionCriteresStore.critereForm.id))
  }
  router.push({ name: 'supports' });
}

/** -----------------------------------
 * manage existing defined profession from db
 */
const currentProf = computed({
  get() {
    if (Array.isArray(gestionCriteresStore.critereForm.profession)) return gestionCriteresStore.critereForm.profession

    if (typeof gestionCriteresStore.critereForm.profession === 'string') {
      return gestionCriteresStore.critereForm.profession
        .split(',')
        .filter(Boolean)
    }
    return []
  },
  set(val) {
    gestionCriteresStore.critereForm.profession = val.join(',')
  }
})

const stringOptions = ref([
  { label: t('criteres.cdi'), value: 'cdi' },
  { label: t('criteres.retired'), value: 'retired' },
  { label: t('criteres.executive'), value: 'executive' },
  { label: t('criteres.official'), value: 'official' },
  { label: t('criteres.employee'), value: 'employee' },
  { label: t('criteres.liberal'), value: 'liberal' },
])
/* ------------------------------ */

/* ---------------- NORMALIZATION ---------------- */
const normalize = (val: any) => {
  if (val === '' || val === null || val === undefined) return null
  if (Array.isArray(val)) return [...val].sort()
  if (typeof val === 'number' ||
    (typeof val === 'string' && val.trim() !== '' && !isNaN(Number(val)))
  ) {
    return Number(val)
  }
  return val
}  

/* --------- control button visibility ------------ */
const isBtnResetVisible = computed(() =>
  gestionCriteresStore.action === CrudAction.UPDATE
)
const nameResetBtn = computed(() => t('buttons.clear'))

const isBtnActifVisible = computed(() =>
  gestionCriteresStore.action === CrudAction.READ
)
const nameActifBtn = computed(() =>
  gestionCriteresStore.critereForm.actifCritere
    ? t('criteres.buttons.deactivate')
    : t('criteres.buttons.activate')
)

const isBtnActionVisible = computed(() =>
  gestionCriteresStore.action === CrudAction.READ 
)
const nameUpdateBtn = computed(() => t('buttons.update'))

const isBtnDuplicatVisible = computed(() =>
  gestionCriteresStore.action === CrudAction.READ 
)
const nameDuplicatBtn = computed(() => t('criteres.buttons.duplicat'))

// icon Actif/unactif
const actifIcon = computed(() =>
  gestionCriteresStore.critereForm.actifCritere
    ? 'visibility_off'
    : 'visibility'
)

/*--------------------------------------*/

onUnmounted(() => {
  footerStore.isBtnValidateVisible = false;
  footerStore.isBtnCancelVisible = false;
});


/* ---------------- VALIDATION ---------------- */
type CritereForm = typeof gestionCriteresStore.critereForm;
const ageMinLocRef = ref<any>(null);
const ageMaxLocRef = ref<any>(null);
const crdMinLocRef = ref<any>(null);
const crdMaxLocRef = ref<any>(null);
const crdMaxImmoLocRef = ref<any>(null);
const crdMinImmoLocRef = ref<any>(null);
const crdMaxConsoLocRef = ref<any>(null);
const crdMinConsoLocRef = ref<any>(null);
const mafMinLocRef = ref<any>(null);
const mafMaxLocRef = ref<any>(null);
const txEndettementMinLocRef = ref<any>(null);
const txEndettementMaxLocRef = ref<any>(null);

const ageMinProRef = ref<any>(null);
const ageMaxProRef = ref<any>(null);
const crdMinProRef = ref<any>(null);
const crdMaxProRef = ref<any>(null);
const crdMaxImmoProRef = ref<any>(null);
const crdMinImmoProRef = ref<any>(null);
const crdMaxConsoProRef = ref<any>(null);
const crdMinConsoProRef = ref<any>(null);
const mafMinProRef = ref<any>(null);
const mafMaxProRef = ref<any>(null);
const txEndettementMinProRef = ref<any>(null);
const txEndettementMaxProRef = ref<any>(null);

let timeout: any

watch(
  () => gestionCriteresStore.critereForm,
  () => validateRefs(ageMinLocRef, ageMaxLocRef, 
    crdMaxLocRef, crdMinLocRef,
    crdMinImmoLocRef, crdMaxImmoLocRef,
    crdMinConsoLocRef, crdMaxConsoLocRef,
    mafMinLocRef, mafMaxLocRef,
    txEndettementMinLocRef, txEndettementMaxLocRef,
    ageMinProRef, ageMaxProRef, 
    crdMaxProRef, crdMinProRef,
    crdMinImmoProRef, crdMaxImmoProRef,
    crdMinConsoProRef, crdMaxConsoProRef,
    mafMinProRef, mafMaxProRef,
    txEndettementMinProRef, txEndettementMaxProRef,
  ),
  // () => {
    // clearTimeout(timeout)
    // timeout = setTimeout(() => {
    //   formRef.value?.validate()
    // }, 300)
    // formRef.value?.validate() //all form
    // TO DO
    // dirty.value = true
  // },
  { deep: true } 
)
// helper validate fields
function validateRefs(...refs: any[]) {
  refs.forEach(r => r?.value?.validate())
}

// helper ignore 
const ignoreIfTargetEmpty = (getTarget, ruleFn) => (val) => {
  const target = getTarget();
  if (target === null || target === undefined || target === '') return true;
  return ruleFn(val);
};

// choose selection
function onSupportSelected(support: any) {
  gestionCriteresStore.critereForm.idSupport = support.id
  gestionCriteresStore.critereForm.src = support.source
}

onUnmounted(() => {
  footerStore.isBtnValidateVisible = false;
  footerStore.isBtnCancelVisible = false;
});
</script>

<style>
.dirty-field {
  border: 1px solid orange;
  background-color: #fff8e1;
}
.invisible {
  visibility: hidden;
}
.idClass {
  /* border: 1px solid grey; */
  background-color: rgb(229, 239, 249);
  margin: 5px;
}

</style>

