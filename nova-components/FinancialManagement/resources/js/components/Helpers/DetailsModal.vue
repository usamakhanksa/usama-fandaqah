<template>
    <sweet-modal v-if="transaction" :enable-mobile-fullscreen="false" class="Details_modal" :pulse-on-block="false" :title="__('Transaction Details')" overlay-theme="dark" ref="transactionModal" >

        <div class="flex flex-wrap overflow-hidden card py-2 px-2">
            <div v-if="transaction.number" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Transaction Number')}} :</div>
                <div class="w-3/4 px-5"> #{{transaction.number}}</div>
            </div>
            <template v-if="transaction.enable_tax_on_withdraw">
                <div class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                    <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Amount')}} :</div>
                    <div class="w-3/4 px-5"> {{transaction.amount_without_tax}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> </div>
                </div>

                <div class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                    <div class="w-1/4 text-80 border-l border-gray-300"> {{__('VAT')}} :</div>
                    <div class="w-3/4 px-5"> {{transaction.tax_amount}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> ({{transaction.tax_percentage}}%) </div>
                </div>

                <div class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                    <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Amount Include Tax')}} :</div>
                    <div class="w-3/4 px-5"> {{transaction.amount}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> </div>
                </div>

                <div v-if="transaction.supplier_tax_number" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                    <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Supplier tax number')}} :</div>
                    <div class="w-3/4 px-5"> {{transaction.supplier_tax_number}} </div>
                </div>

                <div v-if="transaction.invoice_number" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                    <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Invoice number')}} :</div>
                    <div class="w-3/4 px-5"> {{transaction.invoice_number}} </div>
                </div>
            </template>
            <template v-else>
                <div v-if="transaction.amount" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                    <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Amount')}} :</div>
                    <div class="w-3/4 px-5"> {{transaction.amount}} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> </div>
                </div>
            </template>
            <div v-if="transaction.statement" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Statement')}} :</div>
                <div v-if="transaction.statement == 'various_services'" class="w-3/4 px-5"> {{__('Various services')}}</div>
                <div v-else class="w-3/4 px-5"> {{transaction.statement}}</div>
            </div>
            <div v-if="transaction.date_receipt" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Date Receipt')}} :</div>
                <div class="w-3/4 px-5"> {{transaction.date_receipt}}</div>
            </div>
            <div v-if="transaction.received_from" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300" v-if="transaction.type == 'deposit'"> {{__('From')}} :</div>
                <div class="w-1/4 text-80 border-l border-gray-300" v-else> {{__('To')}} :</div>
                <div class="w-3/4 px-5"> {{transaction.received_from}}</div>
            </div>
            <div v-if="transaction.payment_method" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Payment Type')}} :</div>
                <div class="w-3/4 px-5">
                    {{transaction.payment_method == 'credit' ? __('Credit Card') : __(this.capitalize(transaction.payment_method))}}
                </div>
            </div>
            <div v-if="transaction.payment_method == 'credit-payment'" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Person In Charge')}} :</div>
                <div class="w-3/4 px-5"> {{transaction.person_in_charge}}</div>
            </div>

            <div v-else-if="transaction.payment_method != 'cash'" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Payment Reference')}} :</div>
                <div class="w-3/4 px-5"> {{__(transaction.reference)}}</div>
            </div>


            <div v-if="transaction.employee" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Employee')}} :</div>
                <div class="w-3/4 px-5"> {{__(transaction.employee)}}</div>
            </div>
            <div v-if="transaction.received_by" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Received By')}} :</div>
                <div class="w-3/4 px-5"> {{__(transaction.received_by)}}</div>
            </div>
            <div v-if="transaction.note" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-gray-300 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Notes')}} :</div>
                <div class="w-3/4 px-5"> {{__(transaction.note)}}</div>
            </div>

            <div v-if="transaction.created_by != transaction.updated_by" class="w-full overflow-hidden md:w-w-full lg:w-full xl:w-w-full flex my-1 p-2 border border-red-500 text-base bg-white">
                <div class="w-1/4 text-80 border-l border-gray-300"> {{__('Last Update By')}} :</div>
                <div class="w-3/4 px-5"> {{__(transaction.lastUpdateEmployee)}}</div>
            </div>
        </div>
    </sweet-modal>
</template>

<script>
export default {
    name: "DetailsModal",
    props : ['transaction'],
    data(){
        return{
            currency :Nova.app.currentTeam.currency,

        }
    },
    methods:{
        capitalize(label){
            if (typeof label !== 'string') return ''
            return label.charAt(0).toUpperCase() + label.slice(1)
        },
    }
}
</script>

<style lang="scss">
    .Details_modal {

        .sweet-content {
            overflow: auto !important;
            max-height: calc(100vh - 6rem) !important;
            display: block !important;
            scrollbar-width: thin !important;
            scrollbar-color: #ccc #f5f5f5 !important;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
        } /* sweet-content */
    }

</style>
