<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    client: Object
})

const form = useForm({
    first_name: props.client.first_name,
    last_name: props.client.last_name,
    email: props.client.email,
    phone: props.client.phone,
    national_id: props.client.national_id,
    type: props.client.type,
    city: props.client.city,
    address: props.client.address,
})

const submit = () => {
    form.put(route('client-relations.update', props.client.id))
}
</script>

<template>
    <Head :title="`Edit ${client.first_name} | Fandaqah`" />

    <div class="min-h-screen bg-[#f2f0eb] p-6">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <Link :href="route('client-relations.show', client.id)" class="text-[#8f9793] hover:text-[#2a273c] flex items-center gap-2 mb-2 transition">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Profile
                    </Link>
                    <h1 class="text-3xl font-bold text-[#2a273c]">Edit Client Profile</h1>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-[#fbcdab]/20">
                <div class="p-8 space-y-8">
                    <!-- Personal Info -->
                    <section>
                        <h2 class="text-lg font-bold text-[#e95a54] mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-[#e95a54]/10 flex items-center justify-center text-sm">1</span>
                            Personal Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">First Name *</label>
                                <input v-model="form.first_name" type="text" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]" />
                                <div v-if="form.errors.first_name" class="text-red-500 text-xs mt-1">{{ form.errors.first_name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">Last Name *</label>
                                <input v-model="form.last_name" type="text" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]" />
                                <div v-if="form.errors.last_name" class="text-red-500 text-xs mt-1">{{ form.errors.last_name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">National ID / Passport</label>
                                <input v-model="form.national_id" type="text" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]" />
                                <div v-if="form.errors.national_id" class="text-red-500 text-xs mt-1">{{ form.errors.national_id }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">Profile Type *</label>
                                <select v-model="form.type" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]">
                                    <option value="tenant">Tenant</option>
                                    <option value="buyer">Buyer</option>
                                    <option value="investor">Investor</option>
                                </select>
                                <div v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</div>
                            </div>
                        </div>
                    </section>

                    <!-- Contact Info -->
                    <section>
                        <h2 class="text-lg font-bold text-[#e95a54] mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-[#e95a54]/10 flex items-center justify-center text-sm">2</span>
                            Contact Details
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">Email Address *</label>
                                <input v-model="form.email" type="email" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]" />
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">Phone Number *</label>
                                <input v-model="form.phone" type="text" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]" />
                                <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">City</label>
                                <input v-model="form.city" type="text" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c]" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-[#2a273c] mb-2">Address</label>
                                <textarea v-model="form.address" rows="3" class="w-full bg-[#f2f0eb]/50 border-none rounded-xl focus:ring-2 focus:ring-[#e95a54] p-3 text-[#2a273c] font-sans"></textarea>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="bg-[#f2f0eb]/50 p-8 border-t border-[#fbcdab]/20 flex justify-end gap-4">
                    <Link :href="route('client-relations.show', client.id)" class="px-6 py-3 text-[#2a273c] font-semibold hover:bg-white rounded-xl transition">
                        Cancel Changes
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="bg-[#2a273c] text-white px-8 py-3 rounded-xl font-bold hover:bg-[#e95a54] transition shadow-lg flex items-center gap-2 group"
                    >
                        <CheckIcon class="w-5 h-5 group-hover:scale-110 transition" />
                        {{ form.processing ? 'Updating...' : 'Update Profile' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
