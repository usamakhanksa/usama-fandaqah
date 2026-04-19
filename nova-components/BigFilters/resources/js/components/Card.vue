<template>
    <div>
        <div v-if="filters.length > 0" class="border border-gray-500 rounded flex flex-wrap mb-5">
            <!-- Custom Filters  -->
            <div class="w-full">
                <div class="search_transactions">
                    <div class="filter_item"  v-for="(filter,index) in this.filters" :key="index"  >
                        <date-range-filter :filter-key="filter.class"
                                           :is="filter.component"
                                           :key="filter.name"
                                           :resource-name="resourceName"
                                           @change="$emit('filter-changed')"
                                           @input="$emit('filter-changed')"
                                           v-if="filter.component == 'date-range-filter'"
                        />
                    </div>
                    <div class="filter_item">
                       <button
                    @click="resetFilters()"
                    v-tooltip="{
                      targetClasses: ['it-has-a-tooltip'],
                      placement: 'top',
                      content: __('Reset Filters'),
                      classes: ['tooltip_reset']
                    }"
                    class="btn bg-gray-600 text-white h-9 px-3 hover:bg-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16.866" height="18.447" viewBox="0 0 16.866 18.447"><g transform="translate(0 0)"><path d="M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z" transform="translate(-20.982 0)" fill="#ffffff"/><path d="M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z" transform="translate(-75.175 -178.237)" fill="#ffffff"/></g></svg>
                  </button>
                    </div>
                </div>
            </div>
        </div>
        <total-occupied ref="totalOccupied"></total-occupied>
    </div>
</template>

<script>
    import DateRangeFilter from "./custom/DateRangeFilter";
    export default {
        components: {
            DateRangeFilter
        },
        props: {
            card: {
                filterMenuTitle: String,
                filterMaxHeight: Number,
                filterHideTitle: {
                    type: Boolean,
                    default: false
                }
            },
            resourceName: String,
            softDeletes: Boolean,
            viaResource: String,
            viaHasOne: Boolean,
            trashed: {
                type: String,
                validator: value => ['', 'with', 'only'].indexOf(value) != -1,
            },
            perPage: [String, Number],
            showTrashedOption: {
                type: Boolean,
                default: true,
            },
        },
        methods: {
            trashedChanged(event) {
                this.$emit('trashed-changed', event.target.value)
            },

            perPageChanged(event) {
                this.$emit('per-page-changed', event.target.value)
            },
            resetFilters(){
                Nova.$emit('occupied-reset-filter');
                // Emit Bus Event to handle custom filters
                _.map(this.filters, filter => filter.currentValue = '');
            },
        },

        computed: {
            /**
             * Return the filters from state
             */
            filters() {
                return this.$store.getters[`${this.resourceName}/filters`]
            },

            /**
             * Determine via state whether filters are applied
             */
            filtersAreApplied() {
                return this.$store.getters[`${this.resourceName}/filtersAreApplied`]
            },

            /**
             * Return the number of active filters
             */
            activeFilterCount() {
                return this.$store.getters[`${this.resourceName}/activeFilterCount`]
            },
            encodedFilters() {
                return this.$store.getters[`${this.resourceName}/currentEncodedFilters`]
            },
            filterRows(){
                if( this.filters.length > 3)
                {
                    return _.chunk(this.filters, 3)
                }
                else
                {
                    return [ this.filters ]
                }
            }
        },
        mounted() {
            this.$on('filter-changed', () => {
                this.$refs.totalOccupied.getTotal(this.encodedFilters)
            })
        }
    }
</script>
