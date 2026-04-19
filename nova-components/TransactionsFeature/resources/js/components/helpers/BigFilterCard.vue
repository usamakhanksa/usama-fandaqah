<template>
  <div v-if="filters.length > 0" class="filter_area">
    <div class="item"  v-for="(filter,index) in filters" :key="index"  >
      <transaction-number v-if="filter.component == 'transaction-number'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
        :is="filter.component"
        @input="$emit('filter-changed')"
        @change="$emit('filter-changed')"
      />
      <id-number-filter v-if="filter.component == 'id-number-filter'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
        :is="filter.component"
        @input="$emit('filter-changed')"
        @change="$emit('filter-changed')"
      />
      <reservation-number-filter v-if="filter.component == 'reservation-number-filter'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
      />
      <date-range-filter v-if="filter.component == 'date-range-filter'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
        :is="filter.component"
        @input="$emit('filter-changed')"
        @change="$emit('filter-changed')"
      />
      <date-filter v-if="filter.component == 'date-filter'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
        :is="filter.component"
        @input="$emit('filter-changed')"
        @change="$emit('filter-changed')"
      />
      <phone-number-filter v-if="filter.component == 'phone-number-filter'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
        :is="filter.component"
        @input="$emit('filter-changed')"
        @change="$emit('filter-changed')"
      />
      <select-filter-component v-if="filter.component == 'select-filter'"
        :resource-name="resourceName"
        :key="filter.name"
        :filter-key="filter.class"
        :is="filter.component"
        :filterComponent="filter.component"
        :filterType="filter.type"
        @input="$emit('filter-changed')"
        @change="$emit('filter-changed')"
      />
    </div><!-- item -->
    <div class="item">
      <slot name = 'deposit-slot'></slot>
      <slot name = 'withdraw-slot'></slot>
    </div><!-- item -->
    <slot name="reset-btn"></slot>
  </div>
</template>

<script>
import SelectFilterComponent from "./SelectFilterComponent"
import DateRangeFilter from "./DateRangeFilter"
import IdNumberFilter from "./IdNumberFilter"
import ReservationNumberFilter from "./ReservationNumberFilter"
import DateFilter from "./DateFilter"
import PhoneNumberFilter from "./PhoneNumberFilter"
import TransactionNumber from "./TransactionNumber";



export default {
    components: {
        TransactionNumber,
       'select-filter' :  SelectFilterComponent ,
        DateRangeFilter ,
        IdNumberFilter ,
        ReservationNumberFilter ,
        DateFilter ,
        PhoneNumberFilter,
    },

    props: [
        'card',
        'cardPanel',
        'resourceName'
    ],
    data(){

    },
    computed:{
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

    methods: {

    },
}
</script>