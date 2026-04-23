<template>
    <div class="space-y-6">
        <div v-for="(value, key) in form" :key="key" class="bg-white rounded-2xl p-6 shadow-sm border border-[#8f9793]/10">
            <label class="block text-sm font-semibold text-[#2a273c] mb-2 uppercase tracking-wide">
                {{ formatLabel(key) }}
            </label>
            <div class="relative">
                <input 
                    v-if="typeof value === 'string' || typeof value === 'number'"
                    v-model="form[key]"
                    :type="getInputType(key)"
                    class="w-full bg-[#f2f0eb]/50 border-none rounded-xl px-4 py-3 text-[#2a273c] focus:ring-2 focus:ring-[#e95a54] transition-all"
                >
                <div v-else-if="typeof value === 'boolean'" class="flex items-center">
                    <button 
                        @click="form[key] = !form[key]"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[#e95a54] focus:ring-offset-2"
                        :class="form[key] ? 'bg-[#e95a54]' : 'bg-[#8f9793]/30'"
                    >
                        <span 
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                            :class="form[key] ? 'translate-x-6' : 'translate-x-1'"
                        />
                    </button>
                    <span class="ml-3 text-sm text-[#8f9793]">{{ form[key] ? 'Enabled' : 'Disabled' }}</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button 
                @click="submit"
                :disabled="processing"
                class="bg-[#e95a54] text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-[#e95a54]/20 hover:bg-[#e95a54]/90 transition-all flex items-center"
            >
                <SaveIcon v-if="!processing" class="w-5 h-5 mr-2" />
                <LoaderIcon v-else class="w-5 h-5 mr-2 animate-spin" />
                {{ processing ? 'Saving...' : 'Save Settings' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { SaveIcon, LoaderIcon } from 'lucide-vue-next';

const props = defineProps({
    data: Object,
    group: String
});

const form = reactive({ ...props.data.settings });
const processing = ref(false);

watch(() => props.data.settings, (newVal) => {
    Object.assign(form, newVal);
}, { deep: true });

const formatLabel = (key) => {
    return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getInputType = (key) => {
    if (key.includes('email')) return 'email';
    if (key.includes('phone')) return 'tel';
    if (key.includes('color')) return 'color';
    return 'text';
};

const submit = () => {
    processing.value = true;
    router.post(route('settings.update', props.group), form, {
        onFinish: () => processing.value = false,
        preserveScroll: true
    });
};
</script>
