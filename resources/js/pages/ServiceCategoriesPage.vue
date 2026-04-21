<template>
  <main class="bg-[#f2f0eb] min-h-screen p-8 transition-all">
    <!-- Top Integration Rack -->
    <!-- <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-12">
      <div v-for="card in integrationCards" :key="card.title" :class="card.bg" class="rounded-3xl p-6 text-white shadow-xl flex flex-col justify-between h-32 hover:scale-[1.02] transition-transform cursor-pointer group relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform"></div>
        <span class="text-[10px] uppercase font-black tracking-widest opacity-70">{{ card.type }}</span>
        <h3 class="font-bold text-sm tracking-tight leading-tight">{{ card.title }}</h3>
      </div>
    </div> -->

    <!-- Page Body -->
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="space-y-1">
          <h1 class="text-4xl font-black text-[#2a273c]">Services Category</h1>
          <p class="text-[#2a273c]/50 font-medium text-sm tracking-wide">Manage and organize your hotel service departments</p>
        </div>
        
        <button 
          @click="openModal()" 
          class="bg-[#e95a54] hover:bg-orange-600 text-white px-8 py-4 rounded-2xl font-black text-sm transition-all shadow-lg shadow-orange-100 flex items-center justify-center gap-3 transform hover:-translate-y-1 active:scale-95"
        >
          <i class="pi pi-plus-circle"></i>
          <span>Add New Service Category</span>
        </button>
      </div>

      <!-- Main Dynamic Table -->
      <ServiceCategoriesTable 
        :refreshKey="refreshKey" 
        @edit="openModal($event, 'edit')" 
        @view="openModal($event, 'view')"
      />
    </div>

    <!-- Dynamic Form Modal -->
    <ServiceCategoryFormModal 
      :show="showModal" 
      :category="selectedCategory" 
      :isEdit="isEdit"
      :isView="isView"
      @close="showModal = false" 
      @saved="refreshKey++"
    />
  </main>
</template>

<script setup>
import { ref } from 'vue';
import ServiceCategoriesTable from '../components/ServiceCategoriesTable.vue';
import ServiceCategoryFormModal from '../components/ServiceCategoryFormModal.vue';

const showModal = ref(false);
const selectedCategory = ref(null);
const isEdit = ref(false);
const isView = ref(false);
const refreshKey = ref(0);

const integrationCards = [
  { title: 'SMS Feature Raseel', type: 'Communication', bg: 'bg-[#e95a54]' },
  { title: 'Ammar Integration', type: 'ERP Sync', bg: 'bg-[#2a273c]' },
  { title: 'Shomoos Integration', type: 'Gov Report', bg: 'bg-[#8f9793]' },
  { title: 'Passport Scanner', type: 'Hardware', bg: 'bg-[#fbcdab] text-[#2a273c]' },
  { title: 'Payment Gateway', type: 'Finance', bg: 'bg-[#e95a54]' },
];

function openModal(category = null, mode = 'create') {
  selectedCategory.value = category;
  isEdit.value = mode === 'edit';
  isView.value = mode === 'view';
  showModal.value = true;
}
</script>
