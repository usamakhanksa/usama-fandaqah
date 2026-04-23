<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { 
    Squares2X2Icon, 
    PlusIcon, 
    ClockIcon, 
    ExclamationTriangleIcon, 
    CheckCircleIcon,
    MagnifyingGlassIcon,
    ArrowLeftIcon,
    ChatBubbleLeftEllipsisIcon,
    EllipsisHorizontalIcon,
    TrashIcon,
    PencilSquareIcon
} from '@heroicons/vue/24/outline';
import SidebarNavigation from '@/components/SidebarNavigation.vue';
import HeaderBar from '@/components/HeaderBar.vue';
import BaseModal from '@/components/BaseModal.vue';
import { debounce } from 'lodash';

const props = defineProps({
    tasks: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const isShowingTaskModal = ref(false);
const editingTask = ref(null);

const taskForm = useForm({
    title: '',
    description: '',
    room_number: '',
    priority: 'medium',
    status: 'pending',
    due_at: ''
});

const openTaskModal = (task = null) => {
    editingTask.value = task;
    if (task) {
        taskForm.title = task.title;
        taskForm.description = task.description;
        taskForm.room_number = task.room_number;
        taskForm.priority = task.priority;
        taskForm.status = task.status;
        taskForm.due_at = task.due_at ? new Date(task.due_at).toISOString().slice(0, 16) : '';
    } else {
        taskForm.reset();
    }
    isShowingTaskModal.value = true;
};

const submitTask = () => {
    if (editingTask.value) {
        taskForm.put(route('workspace.update', editingTask.value.id), {
            onSuccess: () => isShowingTaskModal.value = false
        });
    } else {
        taskForm.post(route('workspace.store'), {
            onSuccess: () => isShowingTaskModal.value = false
        });
    }
};

const deleteTask = (id) => {
    if (confirm('Are you sure you want to dismiss this task?')) {
        router.delete(route('workspace.destroy', id));
    }
};

watch(search, debounce((val) => {
    router.get(route('workspace.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

const getPriorityClass = (priority) => {
    switch (priority) {
        case 'urgent': return 'bg-[#e95a54] text-white';
        case 'high': return 'bg-[#e95a54]/20 text-[#e95a54]';
        case 'medium': return 'bg-[#fbcdab]/50 text-[#2a273c]';
        case 'low': return 'bg-[#8f9793]/10 text-[#8f9793]';
        default: return 'bg-gray-100 text-gray-500';
    }
};
</script>

<template>
    <Head title="Front Desk Workspace" />
    <div class="min-h-screen bg-[#f2f0eb] flex font-sans text-[#2a273c]">
        <SidebarNavigation />
        <div class="flex-1 flex flex-col">
            <HeaderBar title="Operational Workspace" />
            
            <main class="p-8 max-w-7xl mx-auto w-full">
                <!-- Workspace Header -->
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-8">
                    <div class="flex-1">
                        <Link href="/front-desk" class="inline-flex items-center gap-2 text-[#8f9793] font-black text-xs uppercase tracking-[0.2rem] hover:text-[#e95a54] transition-colors mb-4">
                            <ArrowLeftIcon class="w-4 h-4" /> Back to Manifest
                        </Link>
                        <h1 class="text-6xl font-black tracking-tighter text-[#2a273c]">
                            Action <span class="text-[#e95a54]">Center</span>
                        </h1>
                        <p class="text-xl font-medium text-[#8f9793] mt-2 italic">Priority tasks and operational workflow management.</p>
                    </div>

                    <div class="flex gap-4 w-full md:w-auto">
                        <div class="relative flex-1 md:w-64">
                            <input v-model="search" type="text" placeholder="Search tasks..." 
                                class="w-full bg-white border-none rounded-2xl py-4 pl-12 text-[#2a273c] shadow-lg focus:ring-4 focus:ring-[#e95a54]/10 transition-all font-medium">
                            <MagnifyingGlassIcon class="w-6 h-6 absolute left-4 top-4 text-[#8f9793]" />
                        </div>
                        <button @click="openTaskModal()" 
                            class="bg-[#2a273c] text-[#fbcdab] px-8 py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-[#e95a54] hover:text-white transition-all shadow-xl flex items-center gap-2">
                            <PlusIcon class="w-5 h-5 border-2 border-current rounded-lg p-0.5" /> Log Task
                        </button>
                    </div>
                </div>

                <!-- Focus Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                    <div class="bg-[#2a273c] p-8 rounded-[2.5rem] shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-[#e95a54]/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <span class="text-[0.65rem] font-black text-[#fbcdab] uppercase tracking-[0.3em]">Total Pending</span>
                        <div class="text-5xl font-black text-white mt-2 leading-none">08</div>
                    </div>
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-[#fbcdab]/30">
                        <span class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-[0.3em]">High Priority</span>
                        <div class="text-5xl font-black text-[#e95a54] mt-2 leading-none">03</div>
                    </div>
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-[#8f9793]/10">
                        <span class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-[0.3em]">In Progress</span>
                        <div class="text-5xl font-black text-[#2a273c] mt-2 leading-none">12</div>
                    </div>
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-[#8f9793]/10">
                        <span class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-[0.3em]">Completed Today</span>
                        <div class="text-5xl font-black text-[#8f9793] mt-2 leading-none">24</div>
                    </div>
                </div>

                <!-- task Grid/List -->
                <div class="grid grid-cols-1 gap-4">
                    <div v-for="task in tasks.data" :key="task.id" 
                        class="bg-white p-6 rounded-[2rem] shadow-lg border-l-8 transition-all hover:translate-x-2"
                        :class="{
                            'border-[#e95a54]': task.priority === 'urgent' || task.priority === 'high',
                            'border-[#fbcdab]': task.priority === 'medium',
                            'border-[#8f9793]/30': task.priority === 'low'
                        }">
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-3 py-1 rounded-lg font-black text-[0.6rem] uppercase tracking-wider" 
                                        :class="getPriorityClass(task.priority)">
                                        {{ task.priority }}
                                    </span>
                                    <span v-if="task.room_number" class="text-xs font-black text-[#2a273c] flex items-center gap-1 bg-[#f2f0eb] px-3 py-1 rounded-lg">
                                        <HomeIcon class="w-3 h-3" /> Room {{ task.room_number }}
                                    </span>
                                    <span v-if="task.due_at" class="text-xs font-bold text-[#e95a54] flex items-center gap-1">
                                        <ClockIcon class="w-4 h-4" /> {{ new Date(task.due_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                    </span>
                                </div>
                                <h3 class="text-2xl font-black text-[#2a273c] tracking-tight">{{ task.title }}</h3>
                                <p class="text-[#8f9793] font-medium mt-1 leading-relaxed">{{ task.description }}</p>
                            </div>

                            <div class="flex items-center gap-3 w-full md:w-auto">
                                <div class="flex-1 md:flex-none">
                                    <select @change="router.put(route('workspace.update', task.id), { ...task, status: $event.target.value })"
                                        class="w-full bg-[#f2f0eb] border-none rounded-xl py-3 px-6 text-xs font-black uppercase tracking-widest text-[#2a273c] focus:ring-4 focus:ring-[#e95a54]/10 transition-all cursor-pointer">
                                        <option value="pending" :selected="task.status === 'pending'">Pending</option>
                                        <option value="in_progress" :selected="task.status === 'in_progress'">In Progress</option>
                                        <option value="completed" :selected="task.status === 'completed'">Completed</option>
                                    </select>
                                </div>
                                <button @click="openTaskModal(task)" class="p-4 bg-[#f2f0eb] rounded-xl text-[#2a273c] hover:bg-[#2a273c] hover:text-[#fbcdab] transition-all group">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <button @click="deleteTask(task.id)" class="p-4 bg-[#f2f0eb] rounded-xl text-[#8f9793] hover:bg-[#e95a54] hover:text-white transition-all">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="tasks.data.length === 0" class="py-24 text-center bg-white rounded-[3rem] border-4 border-dashed border-[#8f9793]/10">
                        <div class="inline-flex p-8 rounded-[2rem] bg-[#f2f0eb] mb-6 text-[#8f9793]">
                            <Squares2X2Icon class="w-16 h-16" />
                        </div>
                        <h3 class="text-3xl font-black text-[#2a273c] tracking-tighter">Workspace Clear</h3>
                        <p class="text-[#8f9793] font-medium max-w-sm mx-auto mt-2 italic">All operational tasks are handled. Take a moment for Qahwa.</p>
                        <button @click="openTaskModal()" class="mt-8 text-[#e95a54] font-black text-xs uppercase tracking-widest flex items-center gap-2 mx-auto hover:gap-4 transition-all">
                            Create First Task <span class="text-xl">→</span>
                        </button>
                    </div>
                </div>
            </main>
        </div>

        <!-- Task Modal -->
        <BaseModal :show="isShowingTaskModal" @close="isShowingTaskModal = false" maxWidth="xl">
            <div class="p-10 bg-white relative overflow-hidden">
                <div class="absolute top-[-10%] left-[-10%] w-72 h-72 bg-[#e95a54]/5 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-center mb-10">
                        <div>
                            <span class="text-xs font-black text-[#e95a54] uppercase tracking-[0.4em]">Log New Request</span>
                            <h2 class="text-5xl font-black tracking-tighter text-[#2a273c] mt-2">Task <span class="text-[#8f9793]">Composer</span></h2>
                        </div>
                        <div class="bg-[#2a273c] p-5 rounded-[2rem]">
                            <ChatBubbleLeftEllipsisIcon class="w-8 h-8 text-[#fbcdab]" />
                        </div>
                    </div>

                    <form @submit.prevent="submitTask" class="space-y-8">
                        <div class="space-y-2">
                            <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest ml-1">Task Title</label>
                            <input v-model="taskForm.title" type="text" placeholder="e.g. Extra pillows for Room 204"
                                class="w-full bg-[#f2f0eb] border-none rounded-[1.5rem] py-5 px-8 text-[#2a273c] font-black text-xl placeholder-[#8f9793]/40 focus:ring-8 focus:ring-[#e95a54]/5 transition-all">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest ml-1">Assigned Room</label>
                                <input v-model="taskForm.room_number" type="text" placeholder="Room #"
                                    class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 px-6 text-[#2a273c] font-black focus:ring-4 focus:ring-[#e95a54]/10">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest ml-1">Priority Level</label>
                                <select v-model="taskForm.priority"
                                    class="w-full bg-[#f2f0eb] border-none rounded-2xl py-4 px-6 text-[#2a273c] font-black focus:ring-4 focus:ring-[#e95a54]/10">
                                    <option value="low">Low - Routine</option>
                                    <option value="medium">Medium - Standard</option>
                                    <option value="high">High - Urgent</option>
                                    <option value="urgent">Critical - Immediate</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[0.65rem] font-black text-[#8f9793] uppercase tracking-widest ml-1">Operational Details</label>
                            <textarea v-model="taskForm.description" rows="4" placeholder="Describe the guest request or task details..."
                                class="w-full bg-[#f2f0eb] border-none rounded-2xl py-6 px-8 text-[#2a273c] font-medium placeholder-[#8f9793]/40 focus:ring-4 focus:ring-[#e95a54]/10"></textarea>
                        </div>

                        <div class="pt-6 flex gap-4">
                            <button type="button" @click="isShowingTaskModal = false"
                                class="flex-1 py-5 bg-[#f2f0eb] text-[#8f9793] font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-200 transition-all">
                                Cancel
                            </button>
                            <button type="submit" :disabled="taskForm.processing"
                                class="flex-[2] py-5 bg-[#2a273c] text-[#fbcdab] font-black text-xs uppercase tracking-[0.2em] rounded-2xl hover:bg-[#e95a54] hover:text-white transition-all shadow-2xl shadow-[#2a273c]/30">
                                {{ editingTask ? 'Synchronize Updates' : 'Commit Task' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </BaseModal>
    </div>
</template>

<style scoped>
/* Luxury list animations */
.task-enter-active, .task-leave-active { transition: all 0.5s ease; }
.task-enter-from, .task-leave-to { opacity: 0; transform: translateX(-30px); }
</style>
