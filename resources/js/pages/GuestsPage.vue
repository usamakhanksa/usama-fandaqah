<template>
  <div class="p-4 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold">Guest</h1>
        <p class="text-xs text-slate-400">Home &gt; Guest</p>
      </div>
      <div class="flex gap-2 items-center">
        <SearchInput v-model="query.search" class="w-72" placeholder="Search..." @submit="fetchRows"/>
        <div class="bg-slate-100 rounded-full p-1 flex text-xs">
          <button class="px-4 py-1 rounded-full" :class="tab==='guests'?'bg-slate-900 text-white':''" @click="switchTab('guests')">Guests</button>
          <button class="px-4 py-1 rounded-full" :class="tab==='companies'?'bg-slate-900 text-white':''" @click="switchTab('companies')">Company's</button>
        </div>
        <button class="btn-outline" @click="fetchRows">⟳</button>
        <button class="btn-outline" @click="exportList">Export</button>
        <button class="btn-primary" @click="openAdd">Add New</button>
      </div>
    </div>

    <div class="card p-3 space-y-3">
      <GuestsFilterBar :query="query" @change="updateFilters"/>
      <GuestsTable :rows="rows.data" :meta="rows.meta" @page="setPage" @edit="editRow" @delete="deleteRow"/>
    </div>

    <BaseModal v-model="openForm" :title="modalTitle">
      <div class="grid md:grid-cols-[150px,1fr] gap-4">
        <PersonCompanyTabs v-model:tab="formTab"/>
        <div class="card p-4">
          <PersonProfileForm v-if="formTab==='person'" :model="personForm" :countries="countries" @submit="savePerson"/>
          <CompanyProfileForm v-else :model="companyForm" :countries="countries" :cities="cities" @uploaded="uploadImage" @submit="saveCompany" @draft="saveDraft" @cancel="openForm=false"/>
        </div>
      </div>
    </BaseModal>

    <FooterBar/>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../services/api';
import SearchInput from '../components/SearchInput.vue';
import GuestsFilterBar from '../components/GuestsFilterBar.vue';
import GuestsTable from '../components/GuestsTable.vue';
import BaseModal from '../components/BaseModal.vue';
import PersonCompanyTabs from '../components/PersonCompanyTabs.vue';
import PersonProfileForm from '../components/PersonProfileForm.vue';
import CompanyProfileForm from '../components/CompanyProfileForm.vue';
import FooterBar from '../components/FooterBar.vue';

const tab = ref('guests');
const formTab = ref('person');
const openForm = ref(false);
const uploadedIds = ref([]);
const rows = ref({ data: [], meta: {} });
const countries = ref([]);
const cities = ref([]);
const query = reactive({ search: '', sort: 'name_asc', gender: 'all', type: 'all', page: 1 });
const personForm = reactive({ id: null, name: '', display_name: '', phone: '+966 ', email: '', card_id: '', gender: 'male', type: 'normal', date_of_birth: '', drop_down_civn: 'Drop Down', address: '', read_only_field: 'Read Only' });
const companyForm = reactive({ id: null, company_name: '', mobile_number: '+966 ', responsible_person_name: '', responsible_mobile_number: '+966 ', id_type: 'National ID', id_number: '', email: '', tax_number: '', city_id: null, country_id: 1, address: '' });

const modalTitle = computed(() => formTab.value === 'person' ? 'Person Profile' : 'Add New Company Profile');

const fetchRows = async () => {
  const endpoint = tab.value === 'guests' ? '/guests' : '/companies';
  const { data } = await api.get(endpoint, { params: query });
  rows.value = data;
};

const loadLookups = async () => {
  const [countriesRes, citiesRes] = await Promise.all([api.get('/lookups/countries'), api.get('/lookups/cities')]);
  countries.value = countriesRes.data;
  cities.value = citiesRes.data;
  if (!companyForm.city_id && cities.value.length) companyForm.city_id = cities.value[0].id;
};

const switchTab = (next) => {
  tab.value = next;
  query.page = 1;
  fetchRows();
};
const updateFilters = (payload) => { Object.assign(query, payload, { page: 1 }); fetchRows(); };
const setPage = (page) => { query.page = page; fetchRows(); };
const openAdd = () => { openForm.value = true; formTab.value = tab.value === 'companies' ? 'company' : 'person'; };
const exportList = () => window.open('/api/rooms/export', '_blank');

const editRow = (row) => {
  openForm.value = true;
  if (tab.value === 'guests') {
    formTab.value = 'person';
    Object.assign(personForm, row, { display_name: row.name });
  } else {
    formTab.value = 'company';
    Object.assign(companyForm, row);
  }
};

const deleteRow = async (row) => {
  await api.delete(tab.value === 'guests' ? `/guests/${row.id}` : `/companies/${row.id}`);
  await fetchRows();
};

const savePerson = async () => {
  const payload = { ...personForm, avatar: '/assets/avatars/guest1.svg' };
  if (personForm.id) await api.put(`/guests/${personForm.id}`, payload);
  else await api.post('/guests', payload);
  openForm.value = false;
  await fetchRows();
};

const saveCompany = async () => {
  const payload = { ...companyForm, media_ids: uploadedIds.value };
  if (companyForm.id) await api.put(`/companies/${companyForm.id}`, payload);
  else await api.post('/companies', payload);
  openForm.value = false;
  await fetchRows();
};

const saveDraft = async () => {
  await api.post('/companies/drafts', { payload: companyForm });
};

const uploadImage = async (file) => {
  const formData = new FormData();
  formData.append('file', file);
  const { data } = await api.post('/uploads', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
  uploadedIds.value.push(data.id);
};

onMounted(async () => {
  await loadLookups();
  await fetchRows();
});
</script>
