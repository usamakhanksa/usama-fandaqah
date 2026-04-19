<template>
    <div class="flex-row">
        <div class="filter_field">
            <select-control
                :dusk="`${filter.name}-filter-select`"
                :value="value"
                @change="handleChange"
                :options="filter.options"
                label="name"
            >
            <option value="" selected>{{ __(filter.name) }}</option>
            </select-control>
        </div>
    </div>
</template>

<script>
export default {

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
        filterType : {
            type : String
        },
        filterComponent:{
            type : String
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
