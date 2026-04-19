<template>
    <div>
        <input
            class="date-from"
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
    import '../airbnb-modified.css'
    import { Arabic } from "flatpickr/dist/l10n/ar.js"

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
                    maxDate: new Date(),

                    // static: true,
                    time_24hr: !this.twelveHourTime,
                    "locale": this.locale === 'ar' ? Arabic : 'english',
                })
            })
        },

        methods: {
            onChange(event) {
                if(this.$refs.datePicker.value){
                    Nova.$emit('selected_date', this.$refs.datePicker.value)
                }

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

    .date-from{
        background: #fafafa;
        height: 40px;
        padding: 0 10px;
        font-size: 15px;
        border: 1px solid #ddd !important;
        color: #000;
        width: 100%;
        border-radius: 4px !important;
        outline: none;
    }
</style>
