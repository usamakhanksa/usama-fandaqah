<template>
    <div class="px-1 py-1" v-if="display">
        <div class="bg-blue-100 border-t-4 border-blue-500 rounded-b text-teal-900 px-4 py-3 shadow-md" role="alert">
            <div class="flex">
                <div class="py-1"><svg class="fill-current h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                &nbsp;
                <div>
                    <p class="font-bold">{{__('New Here ?')}}</p>
                    <p v-if="units == 0  && categories == 0  " class="text-sm">{{__("Its looks you didn't add any categories or units , to add a category") }}<a
                        href="#" @click="goToCategories" class="text-yellow-900 font-bold">{{__("click here")}} &nbsp;</a>{{__("Then add")}}
                        <a href="#" @click="goToUnits" class="text-yellow-900 font-bold" >{{ __('units') }}</a> {{__('to it.')}}</p>

                    <p v-if="units == 0  && categories != 0  " class="text-sm">{{__("Its looks you didn't add any units , to add units")}}
                        <a href="#" @click="goToUnits" class="text-yellow-900 font-bold" >{{__('click here')}}</a></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CompleteInfo",
        data: () => {
            return {
                display: false,
                loading: true,
                units: 0,
                categories: 0,
            }
        },
        methods: {
            goToUnits() {
                this.$router.push({path: '/resources/units/new'})

            },
            goToCategories() {
                this.$router.push({path: '/resources/unit-categories/new'})

            },
            checkCounts() {
                this.loading = true;
                axios
                    .get('/nova-vendor/calender/dashboard')
                    .then(response => {
                        //   console.log(response.data)
                        this.units = response.data.units
                        this.categories = response.data.unitCategories

                        if(!this.units){
                            this.display = true;
                        }
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$toasted.show(this.__(err), {type: 'error'})
                })


            }
        },
        mounted() {
            this.checkCounts();
        },
    }
</script>

<style scoped>

</style>
