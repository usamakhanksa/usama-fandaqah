<template>
  <div>
    <date-time-picker
      dusk="date-filter"
      name="date-filter"
      :value="value"
      dateFormat="Y-m-d"
      :placeholder="__(filter.name)"
      :enable-time="false"
      :enable-seconds="false"
      :first-day-of-week="firstDayOfWeek"
      @input.prevent=""
      @change="handleChange"
    />
  </div>
</template>

<script>
    export default {
        name : 'date-filter',
        props: {
            resourceName: {
                type: String,
                required: true,
            },
            filterKey: {
                type: String,
                required: true,
            },
            lens: String,
        },

        methods: {
            handleChange(value) {
                this.$store.commit(`${this.resourceName}/updateFilterState`, {
                    filterClass: this.filterKey,
                    value,
                })
                this.$emit('change')
            },
        },

        computed: {
            placeholder() {
                return ''
            },

            value() {
                return this.filter.currentValue
            },

            filter() {
                return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey)
            },

            options() {
                return this.$store.getters[`${this.resourceName}/getOptionsForFilter`](this.filterKey)
            },

            firstDayOfWeek() {
                return this.filter.firstDayOfWeek || 0
            },
        },
    }
</script>
