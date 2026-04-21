<template>
  <section class="empty-state-panel">
    <div class="empty-content">
      <h2 class="empty-title">{{ title }}</h2>
      <p class="empty-description">{{ description }}</p>
      <button type="button" class="btn-primary mt-5" @click="openModal = true">No data yet – Add new</button>
    </div>

    <div v-if="openModal" class="modal-backdrop" @click.self="openModal = false">
      <div class="modal-card">
        <header class="modal-header">
          <h3 class="text-lg font-bold">{{ modalTitle }}</h3>
          <button type="button" class="modal-close" @click="openModal = false">✕</button>
        </header>

        <form class="space-y-4" @submit.prevent="submit">
          <label class="block text-sm font-semibold text-[var(--dark-navy)]">Name</label>
          <input v-model="form.name" class="input" placeholder="Enter name" required />

          <label class="block text-sm font-semibold text-[var(--dark-navy)]">Description</label>
          <textarea v-model="form.description" class="input min-h-24" placeholder="Optional description" />

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" class="btn-outline" @click="openModal = false">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';

const emit = defineEmits(['create']);
const props = defineProps({
  title: { type: String, default: 'No records found' },
  description: { type: String, default: 'This page starts empty by design and is ready for CRUD operations.' },
  modalTitle: { type: String, default: 'Add new record' },
});

const openModal = ref(false);
const form = reactive({ name: '', description: '' });

const submit = () => {
  emit('create', { ...form });
  form.name = '';
  form.description = '';
  openModal.value = false;
};
</script>

<style scoped>
.empty-state-panel { border: 2px dashed var(--muted-green); background: var(--soft-beige); border-radius: 1rem; padding: 2.5rem; text-align: center; }
.empty-title { color: var(--dark-navy); font-weight: 700; font-size: 1.25rem; }
.empty-description { margin-top: .5rem; color: var(--muted-green); }
.modal-backdrop { position: fixed; inset: 0; background: rgba(42, 39, 60, .55); display: grid; place-items: center; padding: 1rem; z-index: 40; }
.modal-card { width: min(32rem, 100%); background: var(--soft-beige); border: 1px solid var(--muted-green); border-radius: 1rem; padding: 1.25rem; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-close { border: 0; background: transparent; color: var(--dark-navy); font-size: 1.1rem; cursor: pointer; }
</style>
