<template>
    <div>
        <div class="filter_field">


            <input
                    class="block w-full form-control-sm form-input form-input-bordered h-9 px-2 py-0"
                    type="text"
                    :value="value"
                    @change="handleChange"
                    :placeholder="__(filter.name)"

            >

        </div>
    </div>
</template>

<script>
export default {
    name : 'phone-number-filter' ,
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
        },
    },
}
</script>
