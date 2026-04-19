<template>
    <div>
        <nav v-if="crumbs.length">
            <ul class="breadcrumbs">
                <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
                    <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                </li>
            </ul>
        </nav>
        <heading class="mb-6">{{counters.name}}</heading>
        <div class="card records_numbers">
            <form class="w-full p-6">
                <!-- reservation_number -->
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-reservation_number">
                        {{ __('reservation number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.reservation_number"
                        id="grid-reservation_number"
                        min="0" type="number"
                        :placeholder="__('reservation number')">
                        <p class="text-red-500 text-xs italic"
                            v-if="'reservation_number' in errors">
                            {{ errors.reservation_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_reservation_number">
                        {{ __('last reservation number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_reservation_number"
                        id="grid-last_reservation_number"
                        type="number"
                        :placeholder="__('last reservation number')"
                        min="0"  disabled>
                    </div>
                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('reservation_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>

                <!-- invoice_number -->
                 <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-invoice_number">
                        {{ __('invoice number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.invoice_number"
                        id="grid-invoice_number"
                        min="0" type="number"
                        :placeholder="__('invoice number')">

                        <p class="text-red-500 text-xs italic"
                            v-if="'invoice_number' in errors">
                            {{ errors.invoice_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_invoice_number">
                        {{ __('last invoice number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_invoice_number"
                        id="grid-last_invoice_number"
                        type="number"
                        :placeholder=" __('last invoice number')"
                        min="0"  disabled>
                    </div>

                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('invoice_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>
                <!-- credit_note_number -->
                 <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-credit_note_number">
                        {{ __('Credit Note Number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.credit_note_number"
                        id="grid-credit_note_number"
                        min="0" type="number"
                        :placeholder="__('Credit Note Number')">

                        <p class="text-red-500 text-xs italic"
                            v-if="'credit_note_number' in errors">
                            {{ errors.credit_note_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_credit_note_number">
                        {{ __('Last Credit Note Number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_credit_note_number"
                        id="grid-last_credit_note_number"
                        type="number"
                        :placeholder=" __('Last Credit Note Number')"
                        min="0"  disabled>
                    </div>

                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('invoice_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>

                <!-- contract_number -->
<!--                 <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-contract_number">
                        {{ __('contract number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.contract_number"
                        id="grid-contract_number"
                        min="0" type="number"
                        :placeholder="__('contract number') ">

                        <p class="text-red-500 text-xs italic"
                            v-if="'contract_number' in errors">
                            {{ errors.contract_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_contract_number">
                        {{ __('last contract number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_contract_number"
                        id="grid-last_contract_number"
                        type="number"
                        :placeholder="__('last contract number')"
                        min="0"  disabled>
                    </div>
                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('contract_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div> -->

                <!-- payment_number -->
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-payment_number">
                        {{ __('payment number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.payment_number"
                        id="grid-payment_number"
                        min="0" type="number"
                        :placeholder="__('payment number')">

                        <p class="text-red-500 text-xs italic"
                            v-if="'payment_number' in errors">
                            {{ errors.payment_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_payment_number">
                        {{ __('last payment number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_payment_number"
                        id="grid-last_payment_number"
                        type="number"
                        :placeholder="__('last payment number')"
                        min="0"  disabled>
                    </div>
                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('payment_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>

                <!-- receipt_number -->
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-receipt_number">
                        {{ __('receipt number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.receipt_number"
                        id="grid-receipt_number"
                        min="0" type="number"
                        :placeholder=" __('receipt number')">
                        <p class="text-red-500 text-xs italic"
                            v-if="'receipt_number' in errors">
                            {{ errors.receipt_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_receipt_number">
                        {{ __('last receipt number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_receipt_number"
                        id="grid-last_receipt_number"
                        type="number"
                        :placeholder="__('last receipt number')"
                        min="0"  disabled>
                    </div>
                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('receipt_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>

                <!-- service_number -->
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-service_number">
                        {{ __('service number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.service_number"
                        id="grid-service_number"
                        min="0" type="number"
                        :placeholder="__('service number')">

                        <p class="text-red-500 text-xs italic"
                            v-if="'service_number' in errors">
                            {{ errors.service_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_service_number">
                        {{ __('last service number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_service_number"
                        id="grid-last_service_number"
                        type="number"
                        :placeholder="__('last service number')"
                        min="0"  disabled>
                    </div>
                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('service_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>


                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-2/5 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-service_number">
                        {{ __('Promissory Number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                        v-model="counters.promissory_number"
                        id="grid-promissory_number"
                        min="0" type="number"
                        :placeholder="__('Promissory Number')">
                        <p class="text-red-500 text-xs italic"
                            v-if="'promissory_number' in errors">
                            {{ errors.promissory_number[0]}}
                        </p>
                    </div>
                    <div class="w-full md:w-2/5 px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last_service_number">
                        {{ __('Last Promissory Number')}}
                        </label>
                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                        v-model="counters.last_promissory_number"
                        id="grid-last_promissory_number"
                        type="number"
                        min="0"
                        :placeholder="__('Last Promissory Number')"
                         disabled>
                    </div>
                    <div class="w-full md:w-1/5  pt-6">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="button"
                        @click="updateCounters('promissory_number')" >
                        {{ __('Save')}}
                        </button>
                    </div>
                </div>
<div class="flex flex-wrap justify-start">
            <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
        </div>

            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                counters: {
                    reservation_number: 0,
                    last_reservation_number: 0,

                    receipt_number: 0,
                    last_receipt_number: 0,

                    contract_number: 0,
                    last_contract_number: 0,

                    invoice_number: 0,
                    last_invoice_number: 0,

                    credit_note_number: 0,
                    last_credit_note_number: 0,

                    payment_number: 0,
                    last_payment_number: 0,

                    service_number: 0,
                    last_service_number: 0,

                    promissory_number: 0,
                    last_promissory_number: 0,
                },
                installed: false,
                reset: true,
                groupName: 'counter',
                crumbs: [],
                errors: [],
            }
        },
        mounted() {
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Settings',
                    to: '/settings',
                },
                {
                    text: 'counter',
                    to: '#',
                }
            ]
            this.getCounters();
        },
        computed: {
            config: function () {
                return Nova.config.settings_tool.config;
            },
            translations: function () {
                return Nova.config.settings_tool.translations;
            }
        },
        methods: {
            getCounters() {
                Nova.request()
                    .get('/nova-vendor/settings/get_counters').then(response => {
                    this.counters = response.data
                })
                    .catch(error => {
                        this.$toasted.error(this.translations['load_error'], {
                            duration: 3000
                        });
                    });
            },
            goBack() {
                this.$router.push({path: '/settings'})
            },
            updateCounters(column) {


                Nova.request()
                    .put("/nova-vendor/settings/update_counters", {
                        reservation_number: this.counters.reservation_number,
                        receipt_number: this.counters.receipt_number,
                        contract_number: this.counters.contract_number,
                        invoice_number: this.counters.invoice_number,
                        credit_note_number: this.counters.credit_note_number,
                        payment_number: this.counters.payment_number,
                        service_number: this.counters.service_number,
                        promissory_number: this.counters.promissory_number,
                        column: column
                    })
                    .then(response => {

                        this.settings = response.data.settings
                        let message = response.data.message && response.data.message !== ''
                            ? response.data.message
                            : this.translations['save_success'];
                        this.$toasted.success(message, {
                            duration: 3000
                        })
                        this.errors = [];
                    })
                    .catch(error => {
                        this.errors = error.response.data.errors;

                        this.$toasted.error(this.translations['save_error'], {
                            duration: 3000
                        });
                    });
            },
            obtainValues() {
                let components = this.$refs.components ? this.$refs.components : [];
                let values = {};
                for (let index in components) {
                    values[components[index].fieldAttribute] = components[index].value;
                }
                return values;
            }
        },
    }
</script>

<style scoped>
    .tab-icon {
        margin-right: 0.4rem;
        height: 0.8rem;
    }
</style>
