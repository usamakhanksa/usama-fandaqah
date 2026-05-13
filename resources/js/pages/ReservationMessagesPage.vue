<template>
  <div class="p-6 space-y-6 bg-[#f8f9fa] min-h-full flex flex-col">
    <!-- Header & Filters -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h1 class="text-2xl font-bold text-[#2a273c]">Reservation Messages</h1>
        <p class="text-xs font-black text-[#e95a54] uppercase tracking-widest mt-1">Guest Communication History</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <select v-model="filters.type" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Types</option>
          <option value="sms">SMS</option>
          <option value="email">Email</option>
          <option value="whatsapp">WhatsApp</option>
        </select>
        <select v-model="filters.status" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
          <option value="">All Status</option>
          <option value="sent">Sent</option>
          <option value="pending">Pending</option>
          <option value="failed">Failed</option>
        </select>
        <input type="date" v-model="filters.date" class="bg-slate-50 border-none rounded-xl py-3 px-4 text-xs font-bold text-slate-600 focus:ring-2 focus:ring-[#e95a54]">
        <button @click="showSendModal = true" class="bg-[#e95a54] text-white px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-all flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          Send Message
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-50 overflow-hidden flex-1 flex flex-col">
      <div class="overflow-x-auto flex-1">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-50">
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation #</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Guest</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Subject</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sent At</span></th>
              <th class="p-4 text-start"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span></th>
              <th class="p-4 text-end"><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="msg in messages" :key="msg.id" class="hover:bg-slate-50/30 transition-colors group">
              <td class="p-4">
                <span class="text-sm font-bold text-[#e95a54]">#{{ msg.reservation_id }}</span>
              </td>
              <td class="p-4">
                <span class="text-sm font-bold text-[#2a273c]">{{ msg.sent_by?.name || '—' }}</span>
              </td>
              <td class="p-4">
                <span :class="typeClass(msg.type)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ msg.type }}
                </span>
              </td>
              <td class="p-4">
                <span class="text-xs text-slate-600 font-medium">{{ msg.subject || '—' }}</span>
              </td>
              <td class="p-4">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#2a273c]">{{ formatDate(msg.sent_at || msg.created_at) }}</span>
                  <span class="text-[10px] text-slate-400 font-medium">{{ formatTime(msg.sent_at || msg.created_at) }}</span>
                </div>
              </td>
              <td class="p-4">
                <span :class="statusClass(msg.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                  {{ msg.status }}
                </span>
              </td>
              <td class="p-4 text-end">
                <div class="flex items-center justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="viewMessage(msg)" class="bg-[#2a273c] text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-opacity-90 transition-all">
                    View
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="messages.length === 0">
              <td colspan="7" class="p-20 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                  </div>
                  <h3 class="text-lg font-bold text-[#2a273c]">No Messages Found</h3>
                  <p class="text-xs text-slate-400 font-medium">No messages match your filters.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="p-6 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Showing {{ messages.length }} messages</span>
        <div class="flex gap-2">
          <button v-if="pagination.prev" @click="changePage(pagination.current - 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
          <button v-if="pagination.next" @click="changePage(pagination.current + 1)" class="p-2 hover:bg-white rounded-xl transition-colors border border-transparent hover:border-slate-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
        </div>
      </div>
    </div>

    <!-- View Message Modal -->
    <div v-if="selected" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="selected = null">
      <div class="bg-white rounded-3xl p-8 w-full max-w-lg">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-[#2a273c]">Message Details</h3>
          <button @click="selected = null" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
          </button>
        </div>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reservation</span>
            <span class="font-bold text-[#e95a54]">#{{ selected.reservation_id }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</span>
            <span :class="typeClass(selected.type)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ selected.type }}</span>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Subject</span>
            <span class="font-medium text-slate-600">{{ selected.subject || '—' }}</span>
          </div>
          <div class="py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Message</span>
            <p class="text-sm text-slate-600 bg-slate-50 rounded-xl p-3">{{ selected.message }}</p>
          </div>
          <div class="flex justify-between py-2 border-b border-slate-50">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span>
            <span :class="statusClass(selected.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ selected.status }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sent At</span>
            <span class="font-medium text-slate-600">{{ formatDate(selected.sent_at || selected.created_at) }} {{ formatTime(selected.sent_at || selected.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Send Message Modal -->
    <div v-if="showSendModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 w-full max-w-md">
        <h3 class="text-xl font-bold text-[#2a273c] mb-6">Send Message</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Reservation ID</label>
            <input v-model="sendForm.reservation_id" type="number" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]" placeholder="Enter reservation ID">
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Type</label>
            <select v-model="sendForm.type" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]">
              <option value="sms">SMS</option>
              <option value="email">Email</option>
              <option value="whatsapp">WhatsApp</option>
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Subject</label>
            <input v-model="sendForm.subject" type="text" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54]" placeholder="Optional subject">
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Message</label>
            <textarea v-model="sendForm.message" rows="4" class="w-full bg-slate-50 border-none rounded-xl py-3 px-4 text-sm font-bold text-[#2a273c] focus:ring-2 focus:ring-[#e95a54] resize-none" placeholder="Enter message..."></textarea>
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="showSendModal = false" class="flex-1 bg-slate-100 text-[#2a273c] py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">Cancel</button>
          <button @click="sendMessage" :disabled="sending" class="flex-1 bg-[#e95a54] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-opacity-90 transition-all disabled:opacity-50">
            {{ sending ? 'Sending...' : 'Send' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import api from '../services/api';
import dayjs from 'dayjs';

const messages = ref([]);
const selected = ref(null);
const showSendModal = ref(false);
const sending = ref(false);
const filters = reactive({ date: '', type: '', status: '', per_page: 15, page: 1 });
const pagination = reactive({ current: 1, next: false, prev: false });
const sendForm = reactive({ reservation_id: '', type: 'sms', subject: '', message: '' });

const typeClass = (type) => ({ sms: 'bg-blue-100 text-blue-600', email: 'bg-purple-100 text-purple-600', whatsapp: 'bg-emerald-100 text-emerald-600' }[type] || 'bg-slate-100 text-slate-600');
const statusClass = (s) => ({ sent: 'bg-emerald-100 text-emerald-600', pending: 'bg-amber-100 text-amber-600', failed: 'bg-red-100 text-red-600' }[s] || 'bg-slate-100 text-slate-600');
const formatDate = (d) => d ? dayjs(d).format('DD MMM YYYY') : '—';
const formatTime = (d) => d ? dayjs(d).format('hh:mm A') : '';

const load = async () => {
  try {
    const { data } = await api.get('/reservations/messages', { params: filters });
    messages.value = data.data || [];
    pagination.next = !!data.next_page_url;
    pagination.prev = !!data.prev_page_url;
    pagination.current = data.current_page || 1;
  } catch (err) {
    console.error('Failed to load messages', err);
  }
};

const viewMessage = (msg) => { selected.value = msg; };
const changePage = (page) => { filters.page = page; load(); };

const sendMessage = async () => {
  if (!sendForm.reservation_id || !sendForm.message) return;
  sending.value = true;
  try {
    await api.post('/reservations/messages', sendForm);
    showSendModal.value = false;
    Object.assign(sendForm, { reservation_id: '', type: 'sms', subject: '', message: '' });
    load();
  } catch (err) {
    console.error('Failed to send message', err);
  } finally {
    sending.value = false;
  }
};

watch(() => [filters.date, filters.type, filters.status], () => { filters.page = 1; load(); });
onMounted(() => load());
</script>
