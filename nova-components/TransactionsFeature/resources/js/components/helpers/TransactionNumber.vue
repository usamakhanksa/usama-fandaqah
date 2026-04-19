<template>
  <div>
    <input
      type="text"
      :value="value"
      @change="handleChange"
      :placeholder="__('Transaction Number')"
    >
  </div>
</template>

<script>
export default {
    name : 'transaction-number' ,
    props: {
        resourceName: {
            type: String,
            required: true,
        },
        filterKey: {
            type: String,
            required: true,
        },
    },
    data(){
        return {
            placeholder : __('Hit Enter Key To Filter')
        }
    },

    methods: {
        handleChange(event) {
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: this.filterKey,
                value: event.target.value,
            })

            this.$emit('change')
        },
    },

    computed: {
        filter() {
            return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey)
        },

        value() {
            return this.filter.currentValue
        }
    },
}
</script>
