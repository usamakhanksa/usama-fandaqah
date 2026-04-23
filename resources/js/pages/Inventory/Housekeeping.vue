<template>
    <InventoryLayout>
        <div class="bg-white rounded-3xl shadow-xl border border-[#fbcdab]/20 overflow-hidden">
            <!-- Header Actions -->
            <div class="p-8 border-b border-[#f2f0eb] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-2xl font-black text-[#2a273c]">Task Schedule</h2>
                <div class="flex space-x-2">
                    <button @click="showAddModal = true" class="bg-[#e95a54] text-white px-6 py-2.5 rounded-xl font-bold hover:bg-[#2a273c] transition-all">
                        + New Task
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#2a273c] text-[#fbcdab]">
                        <tr>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-widest">Room</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-widest">Task Type</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-widest">Assigned To</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-widest">Status</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-widest">Scheduled</th>
                            <th class="px-8 py-5 text-xs font-black uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f2f0eb]">
                        <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-[#f2f0eb]/30 transition-colors">
                            <td class="px-8 py-6">
                                <span class="text-lg font-black text-[#2a273c]">#{{ task.room.room_number }}</span>
                            </td>
                            <td class="px-8 py-6 capitalize font-bold text-[#2a273c]">
                                {{ task.task_type.replace('_', ' ') }}
                            </td>
                            <td class="px-8 py-6 text-[#8f9793]">
                                {{ task.assigned_to || 'Unassigned' }}
                            </td>
                            <td class="px-8 py-6">
                                <select 
                                    v-model="task.status" 
                                    @change="updateStatus(task)"
                                    :class="[
                                        'rounded-lg text-xs font-black border-none ring-1 py-1 px-3 appearance-none cursor-pointer',
                                        task.status === 'completed' ? 'bg-green-100 text-green-700 ring-green-200' :
                                        task.status === 'in_progress' ? 'bg-blue-100 text-blue-700 ring-blue-200' :
                                        'bg-yellow-100 text-yellow-700 ring-yellow-200'
                                    ]"
                                >
                                    <option value="pending">PENDING</option>
                                    <option value="in_progress">IN PROGRESS</option>
                                    <option value="completed">COMPLETED</option>
                                </select>
                            </td>
                            <td class="px-8 py-6 text-sm text-[#8f9793]">
                                {{ new Date(task.scheduled_at).toLocaleDateString() }}
                            </td>
                            <td class="px-8 py-6">
                                <button @click="deleteTask(task.id)" class="text-red-400 hover:text-red-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="tasks.data.length === 0" class="p-20 text-center text-[#8f9793]">
                No housekeeping tasks scheduled.
            </div>
        </div>

        <!-- Add Task Modal -->
        <TransitionRoot appear :show="showAddModal" as="template">
            <Dialog as="div" @close="showAddModal = false" class="relative z-50">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-[#2a273c]/80 backdrop-blur-sm" />
                </TransitionChild>
                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-3xl bg-white p-8 border border-[#fbcdab]/50">
                                <DialogTitle as="h3" class="text-2xl font-black text-[#2a273c] mb-6">Assign Housekeeping Task</DialogTitle>
                                <form @submit.prevent="submit" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Room</label>
                                        <select v-model="form.room_id" class="w-full rounded-xl border-[#fbcdab]/30 py-2.5">
                                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                                Room {{ room.room_number }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Task Type</label>
                                        <select v-model="form.task_type" class="w-full rounded-xl border-[#fbcdab]/30 py-2.5">
                                            <option value="daily_refresh">Daily Refresh</option>
                                            <option value="deep_clean">Deep Clean</option>
                                            <option value="inspection">Inspection</option>
                                            <option value="maintenance">Maintenance</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#8f9793] uppercase mb-1">Staff Member</label>
                                        <input v-model="form.assigned_to" type="text" placeholder="Worker Name" class="w-full rounded-xl border-[#fbcdab]/30 py-2.5">
                                    </div>
                                    <button type="submit" class="w-full bg-[#2a273c] text-[#fbcdab] py-4 rounded-xl font-black hover:bg-[#e95a54] hover:text-white transition-all">
                                        SCHEDULE TASK
                                    </button>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </InventoryLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import InventoryLayout from '@/layouts/InventoryLayout.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
    tasks: Object,
    rooms: Array
});

const showAddModal = ref(false);
const form = useForm({
    room_id: '',
    task_type: 'daily_refresh',
    assigned_to: '',
    status: 'pending'
});

const submit = () => {
    form.post('/housekeeping', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const updateStatus = (task) => {
    router.put(`/housekeeping/${task.id}`, {
        status: task.status
    }, { preserveScroll: true });
};

const deleteTask = (id) => {
    if (confirm('Cancel this task?')) {
        router.delete(`/housekeeping/${id}`);
    }
};
</script>
