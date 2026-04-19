<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <vue-tel-input
                :id="field.name"
                type="text"
                :preferredCountries="field.preferredCountries"
                :defaultCountry="field.defaultCountry"
                :class="errorClasses"
                :placeholder="field.name"
                v-model="value"

            ></vue-tel-input>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import VueTelInput from 'vue-tel-input';

export default {
    mixins: [FormField, HandlesValidationErrors],
    components: {
        VueTelInput,
    },
    props: ['resourceName', 'resourceId', 'field'],

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
        },

    },
    watch: {
      value: function(val) {
          //do something when the data changes.
          if (val) {
              this.value = val.replace(/\s+/g, '');
          }
      }
  }
}
</script>
