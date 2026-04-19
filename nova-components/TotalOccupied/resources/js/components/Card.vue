<template>
  <div class="w-full">
    <loading-view :loading="loading" >
      <div class="bg-white border border-gray-400 rounded w-full flex flex-wrap mb-5">
        <div class="sm:w-1/1 md:w-1/2 lg:w-1/2 xl:w-1/2 p-4 reservations_cost">
          <h2 class="inline-block capitalize text-lg w-full">{{__('Total occupancy rate')}} : <h4 class="inline-block font-normal mr-1"><b class="text-black-500">{{ total }} %</b></h4></h2>
        </div>
      </div>
    </loading-view>
  </div>
</template>

<script>
export default {
    props: [
        'card',

        // The following props are only available on resource detail cards...
        // 'resource',
        // 'resourceId',
        // 'resourceName',
    ],
    data () {
      return {
          loading: false,
          total: 0
      }
    },
    mounted() {
        this.getTotal()

        Nova.$on('occupied-reset-filter' , () => {
            this.getTotal();
        })
    },
    methods: {
        getTotal(encodedFilters) {
            this.loading = true;
            Nova.request().post('/nova-vendor/total-occupied/total' , {filters : encodedFilters})
                .then((response) => {
                    this.loading = false;
                    this.total = response.data.total;
                }).catch((error) => {
                    this.loading = false;
                this.$toasted.show(error, {type: 'error'});
            })
        }
    }
}
</script>
<style scoped>
.statistics_items {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(222px, 1fr));
}
.statistics_items .statistic_item {padding: .5rem;}
</style>
