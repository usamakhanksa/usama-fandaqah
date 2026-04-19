<template>
    <div>
        <div class="filter_field">


            <input
                    class="block w-full form-control-sm form-input form-input-bordered h-9 px-2 py-0"
                    type="text"
                    autocomplete="off"
                    :value="value"
                    @change="handleChange"
                    :placeholder="__(filter.name)"
            >

        </div>
    </div>
</template>

<script>
export default {
    name : 'customer-name' ,
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
            let value  = btoa(unescape(encodeURIComponent(event.target.value)));
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: this.filterKey,
                value: value,
            })
            this.$emit('change')
        },
    },

    computed: {
        filter() {
            return this.$store.getters[`${this.resourceName}/getFilter`](this.filterKey)
        },

        value() {
            return decodeURIComponent(escape(window.atob(this.filter.currentValue)))
        },
    },
}
</script>
