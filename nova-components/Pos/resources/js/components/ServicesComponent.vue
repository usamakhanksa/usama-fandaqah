<template>
    <div>
        <div class="services_section">
            <div class="search_area">
                <router-link :to="{ name : 'pos.services-management' , query : {per_page : 20}}" >{{__('Show Operations')}}</router-link>
                <div>
                    <input type="text" @input="filterItems($event)" v-model="items_query"  autofocus="">
                </div>
            </div><!-- end search_area -->
            <div class="show_services relative">
                <!-- Loader -->
                <loading :active.sync="isLoading"
                         :can-cancel="true"
                         :loader="'spinner'"
                         :color="'#7e7d7f'"
                         :is-full-page="false">
                </loading>
                <services-grid-component :services="servicesPerCategory" :locale="locale" />
            </div><!-- end show_services -->
            <div class="category_area" v-if="categories.length">
                <carousel :autoplay="false" :nav="false" :autoWidth="true" :dots="false" :rtl="true">
                    <div v-for="(cat , index) in categories" @getServicesPerCategory="getServicesPerCategory(cat.id)" @click="handleClick(index, cat.id)" :key="index" class="cat" :class="{'current' : selected === index }">{{cat.name[locale]}}</div>
                </carousel>
            </div><!-- end category_area -->
            <div class="operation_buttons" v-if="cart_items.length">
                <button type="button" @click="abortProcess()" class="cancel">{{__('Abort Process')}}</button>
            </div><!-- end operation_buttons -->

            <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Abort Process')" overlay-theme="dark" ref="abortProcessModal" class="delete_confirm_slider_image">
                <div class="relative mx-auto justify-center z-20">
                    <loading :active.sync="isLoading" :is-full-page="false" />
                    <span>{{__('Confirm Abort Process ?')}}</span>
                    <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
                        <div class="flex justify-end flex-wrap">
                            <button id="confirm-delete-button" @click="abort()"   class="btn btn-default btn-danger m-0">{{__('Cancel Process')}}</button>
                            <button type="button" @click="stepBack()"  class="btn btn-default bg-gray-400 ml-2"> {{__('Back')}}</button>
                        </div>
                    </div>
                </div>
            </sweet-modal>
        </div><!-- end services_section -->
    </div>
</template>

<script>

    import carousel from 'vue-owl-carousel2'
    import ServicesGridComponent from './services/ServicesGridComponent'
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "ServicesComponent",
        components: {carousel , ServicesGridComponent , Loading},
        data(){
            return {
                locale : null,
                categories : [],
                selected : 0 ,
                servicesPerCategory : [],
                items_query : null,
                isLoading : false ,
                cart_items : [],
                cat_id : null,
                selectedIsNull : false,
                userId : Nova.config.userId
            }
        }, 
        methods: {
            getServicesCategories(){
                const self = this;
                axios.get('/nova-vendor/pos/services-categories')
                    .then(response => {
                        this.categories = response.data;
                        // var filtered_categories = this.categories;
                        if(this.categories.length){
                            this.categories.forEach(function(cat){
                                if (cat.users){
                                    
                                    if (!JSON.parse(cat.users).includes(self.userId)){
                                        self.categories = self.categories.filter(category => category.id != cat.id);
                                    }
                                }
                               
                            });
                        }

                        // this.categories = filtered_categories;
                        if(this.categories.length){
                            if (!this.selectedIsNull) {
                                this.cat_id = this.categories[this.selected].id;
                                this.getServicesPerCategory(this.categories[this.selected].id);
                            } else {
                                this.getServicesPerCategory(this.cat_id);
                            }
                        }

                    })
            },
            getServicesPerCategory(cat_id){


                this.isLoading = true;
                this.items_query = null;
                this.servicesPerCategory = [];
                axios.get(`/nova-vendor/pos/category/${cat_id}/services`)
                    .then(response => {
                        this.servicesPerCategory = response.data;
                        this.isLoading = false;
                    })
            },
            abortProcess(){
                this.$refs.abortProcessModal.open();
            },
            abort(){
                Nova.$emit('process-aborted');
                this.$refs.abortProcessModal.close();
            },
            stepBack(){
                this.$refs.abortProcessModal.close();
            },
            filterItems(event){
                if(event.inputType === 'deleteContentBackward'){
                    this.getServicesCategories(this.cat_id);
                    return false;
                }

                this.isLoading = true;
                let self = this;
                _.debounce(() => {

                    self.servicesPerCategory = self.servicesPerCategory.filter(function(obj){

                        return  obj.name.toLowerCase().indexOf(self.items_query.trim().toLowerCase()) > -1

                    });
                    self.isLoading = false;
                }, 2000)();

            },
            handleClick(index , cat_id){

                // if(this.cart_items.length){
                //     this.$toasted.show(this.__('You must only add items of the same category'), {
                //         duration : 4000,
                //         type: 'error',
                //         position : 'top-center',
                //     });
                //     return false;
                // }

                this.cat_id = cat_id;

                // if(!this.cart_items.length){
                    this.selected = index;
                    this.selectedIsNull = false;
                    this.getServicesPerCategory(cat_id);
                // }
                // else{
                //     this.selectedIsNull = true;
                //     this.selected = null;
                //     this.$toasted.show(this.__('You must only add items of the same category'), {
                //         duration : 4000,
                //         type: 'error',
                //         position : 'top-center',
                //     });
                //     return false;
                // }

            }

        },
        created(){
            this.getServicesCategories();
        },
        mounted(){
            this.locale = Nova.config.local;

            Nova.$on('cart-items' , (items) => {
                this.cart_items = items ;
            })


            Nova.$on('remove-item-from-cart-confirmed' , (uuid) => {
                this.cart_items = _.reject(this.cart_items, function(obj) { return obj.uuid === uuid; });

                if(!this.cart_items.length){
                    // this.selected = 0 ;
                    this.getServicesCategories();
                }
            });

            Nova.$on('process-aborted' , () => {
                this.cart_items = [] ;
                this.getServicesCategories();
                this.selected = 0;
            })

            Nova.$on('empty-cart' , () => {
                this.cart_items = [] ;
                this.getServicesCategories();
                this.selected = 0;
            })
        }
    }
</script>

<style scoped>

</style>
