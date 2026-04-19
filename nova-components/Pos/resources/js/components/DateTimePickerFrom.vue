<template>
    <div class="datePickerFrom">
    <!-- <div  class="py-1 pl-2 text-1xl inline-block"><h3 data-v-b9bc2c0a=""><i data-v-b9bc2c0a="" class="far fa-calendar-alt" aria-hidden="true"></i></h3></div> -->
    <input
        class="block w-full form-control-sm form-input form-input-bordered"
        :disabled="disabled"
        :class="{ '!cursor-not-allowed': disabled }"
        :value="value"
        ref="datePicker"
        type="text"
        readonly="true"
        :placeholder="placeholder"
    />
    </div>
</template>

<script>
    import flatpickr from 'flatpickr'
    import 'flatpickr/dist/themes/airbnb.css'

    export default {
        props: {
            value: {
                required: false,
            },
            placeholder: {
                type: String
            },
            disabled: {
                type: Boolean,
                default: false,
            },
            dateFormat: {
                type: String,
                default: 'Y-m-d H:i',
            },
            twelveHourTime: {
                type: Boolean,
                default: false,
            },
            enableTime: {
                type: Boolean,
                default: true,
            },
            enableSeconds: {
                type: Boolean,
                default: true,
            },
            firstDayOfWeek: {
                type: Number,
                default: 0,
            },
            locale: {
                type: String,
                default: null,
            },
        },

        data: () => ({ flatpickr: null }),

        mounted() {


            this.$nextTick(() => {
                this.flatpickr = flatpickr(this.$refs.datePicker, {
                    enableTime: this.enableTime,
                    enableSeconds: false,
                    onClose: this.onChange,
                    onChange: this.onChange,
                    dateFormat: this.dateFormat,
                    allowInput: true,

                    // static: true,
                    time_24hr: !this.twelveHourTime,
                    // locale: this.locale,
                })
            })
        },

        methods: {
            onChange(event) {
                Nova.$emit('from-change', this.$refs.datePicker.value)
            },
        },
        created() {
            Nova.$on('reset-dates' , ()=>{
                this.$refs.datePicker.value = null
            })
        }
    }
</script>

<style scoped>
    .\!cursor-not-allowed {
        cursor: not-allowed !important;
    }
</style>
