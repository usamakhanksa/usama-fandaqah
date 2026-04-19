<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <input
                :id="field.name"
                type="text"
                class="w-full form-control form-input form-input-bordered"
                :class="errorClasses"
                :placeholder="field.name"
                v-model="value"
                ref="customDate"
            />

        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import flatpickr from "flatpickr";

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    computed:{
        dateFormat() {
            return  'Y-m-d'
        },
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },
    },
    mounted(){
        const self = this;
        flatpickr(this.$refs.customDate, {
            enableTime: false,
            enableSeconds: false,
            dateFormat: this.dateFormat,
            allowInput: false,
            mode: 'single',
            time_24hr: false,
            onReady() {
                self.$refs.customDate.parentNode.classList.add('date-filter')
            },
            onChange(){
                self.value = self.$refs.customDate.value;
            },
            locale : Nova.config.local
        })
    }
}
</script>
