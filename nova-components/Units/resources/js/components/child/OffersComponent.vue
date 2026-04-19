<template>
  <div class="root">
    <div class="table_area">
      <div class="title">
        <span>{{__('Offers')}} <p>( <template v-if="paginator">{{paginator.totalResults}}</template> )</p></span>
        <!-- <button type="button" @click="openNewOfferModal">{{__('Add a new offer')}}</button> -->
      </div><!-- title -->
      <div class="table_content">
        <div class="table_responsive relative">
          <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
          <table>
            <thead>
              <tr>
                <th>{{__('Name')}}</th>
                <th>{{__('Discount type / value')}}</th>
                <th>{{__('Unit Categories')}}</th>
                <th>{{__('Creation Date')}}</th>
                <th>{{__('Start / End')}}</th>
                <th>{{__('Actions')}}</th>
              </tr>
            </thead>
            <tbody>
              <template v-if="offers && offers.length">
                <tr v-for="(offer , i) in offers" :key="i">
                  <td><span :class="{enabled : offer.enabled , not_enabled : !offer.enabled}">{{offer.name}}</span></td>
                  <td>{{__(offer.discount_type)}} / {{offer.discount_amount}} {{offer.discount_type === 'price' ? __(currency) : '%' }}</td>
                  <td>
                    <div class="units">
                      <p v-for="(category,i) in offer.categories" :key="i">{{category.name[locale]}}</p>
                    </div><!-- units -->
                  </td>
                  <td>{{offer.created_at | formatDateSpecial }}</td>
                  <td>
                    {{ offer.start_date | formatDateSpecial }} - {{offer.end_date | formatDateSpecial}}
                    <div v-if="offer.offer_dates_status === 'out-dated'" class="label text-red-500">{{__('Expired')}}</div>
                    <div v-if="offer.offer_dates_status === 'between'"   class="label text-green-500">{{__('Can be applied')}}</div>
                    <div v-if="offer.offer_dates_status === 'incoming'"  class="label text-blue-500">{{__('UpComing')}}</div>
                  </td>
                  <td>
                    <div class="action">
                      <a class="cursor-pointer" @click="openOfferDetailsModal(offer)" :title="__('View')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                      </a>
                      <a class="cursor-pointer" @click="openOfferEditModal(offer)" :title="__('Edit')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
                      </a>
                      <button type="button" :title="__('Delete')" @click="initDeleteConfirm(offer.id)" v-if="offer.enabled == 0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                      </button>
                    </div><!-- action -->
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="text-center">{{__('there are no offers yet')}}</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div><!-- table_responsive -->
        <div class="w-full flex flex-wrap mt-3 justify-center">
          <pagination
            v-if="paginator && paginator.lastPage > 1"
            :page-count="paginator.lastPage"
            :page-range="3"
            :margin-pages="2"
            :click-handler="getOffers"
            :value="paginator.currentPage"
            :prev-text="__('Previous')"
            :next-text="__('Next')"
            :container-class="'pagination  w-full flex justify-center'"
            :page-class="'page-item'"
            :page-link-class="'page-link'"
            :prev-link-class="'page-link'"
            :next-link-class="'page-link'"
            :prev-class="'page-item'"
            :next-class="'page-item'"
            :first-last-button="true"
            :first-button-text="__('First')"
            :last-button-text="__('Last')"
          />
        </div><!-- flex -->
      </div><!-- table_content -->
    </div><!-- table_area -->

    <!-- Add New Offer Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add a new offer')" overlay-theme="dark" ref="newOfferModal" class="Add_new_offer_modal relative">
      <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <!-- Discount name -->
      <div class="form_group">
        <label>{{__('Offer Name')}}<span class="required">*</span></label>
        <input type="text" :placeholder="__('Offer Name')" v-model="offerObject.offer_name">
      </div><!-- form_group -->
      <!-- Discount type & amount -->
      <div class="row_group">
        <div class="col">
          <label>{{__('Discount Type')}} <span class="required">*</span></label>
          <select v-model="offerObject.discount_type">
            <option :value="null" disabled>{{__('Select Discount Type')}}</option>
            <option value="percentage">{{__('Percentage(%)')}}</option>
            <option value="price">{{__('Price (SAR)')}}</option>
          </select>
        </div><!-- col -->
        <div class="col">
            <label>{{__('Discount Amount')}} <span class="required">*</span></label>
            <input type="tel"  v-model="offerObject.discount_amount" :placeholder="__('Discount Amount')">
        </div><!-- col -->
      </div><!-- row_group -->
      <!-- Categories -->
      <div class="form_group">
        <label>{{__('Unit Categories Included')}} <span class="required">*</span></label>
        <div class="all_units last">
          <label>
            <input type="checkbox" :checked="allCategoriesChecked" @change="checkAllCategories(categories)">
            <span class="checkmark" />
            <p>{{__('Check All')}}</p>
          </label>
          <template v-if="categories">
            <label v-for="(category , i) in categories" :key="i">
              <input type="checkbox" :checked="selectedCategoriesCollector.includes(category.id)" @change="handleCategoriesCollectorState(category)">
              <span class="checkmark" />
              <p>{{ category.name[locale] }}</p>
            </label>
          </template>
        </div><!-- all_units -->
      </div><!-- form_group -->
      <!-- Days -->
      <div class="form_group">
        <label>{{__('Discount will be applied on the following days')}} <span class="required"></span></label>
        <div class="all_units last">
          <label>
            <input type="checkbox" :checked="allDaysChecked" @change="checkAllDays(initDays)">
            <span class="checkmark" />
            <p>{{__('Check All')}}</p>
          </label>
          <!-- Days of week -->
          <label v-for="(day,i) in initDays" :key="i">
            <input type="checkbox"  :checked="selectedDaysCollector.includes(day)" @change="handleDaysCollectorState(day)">
            <span class="checkmark" />
            <p>{{__(day.charAt(0).toUpperCase() + day.slice(1))}}</p>
          </label>
        </div><!-- all_units -->
      </div><!-- form_group -->
      <!-- Calendar -->
      <div class="form_group">
        <label>{{__('Offer Price Start and End Date')}} <span class="required">*</span></label>
        <vcc-date-picker
          class='v-date-picker'
          :locale="vcc_local"
          mode='range'
          v-model='offerObject.selectedDate'
          show-caps
          is-expanded
          :columns="$screens({ default: 1, lg: 2 })"
          :popoverExpanded="true"
          :min-date='minDate'
          :popover="{ placement: 'top', visibility: 'click' }"
        >
            <input v-model="inputValue = defaultRange" :readonly="true" type="text">
        </vcc-date-picker>
      </div><!-- form_group -->
      <!-- Enable Offer -->
      <div class="switch_group">
        <label>{{__('Enable Offer')}}</label>
        <div class="switch">
          <input type="checkbox" v-model="offerObject.enable_offer" @change="handleEnableOffer($event)">
          <span class="slider round" />
        </div><!-- switch -->
      </div><!-- switch_group -->
      <button class="save_add_offer" @click="addOffer">{{__('Save')}}</button>
    </sweet-modal>
    <!-- Add New Offer Modal -->

    <!-- View Offer Details Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Offer Details')" overlay-theme="dark" ref="offerDetailsModal" class="View_offer_modal relative">
      <div class="w-full flex justify-end">
        <div v-if="offerDetailsObject.offer_dates_status === 'out-dated'" class="label bg-red-500">{{__('Expired')}}</div>
        <div v-if="offerDetailsObject.offer_dates_status === 'between'"   class="label bg-green-500">{{__('Can be applied')}}</div>
        <div v-if="offerDetailsObject.offer_dates_status === 'incoming'"  class="label bg-blue-500">{{__('UpComing')}}</div>
      </div><!-- flex -->
      <div class="table_responsive">
        <table>
          <tbody>
            <tr>
              <td>{{__('Offer Name')}}</td>
              <td>{{offerDetailsObject.name}}</td>
            </tr>
            <tr>
              <td>{{__('Discount Type')}}</td>
              <td>{{__(offerDetailsObject.discount_type)}}</td>
            </tr>
            <tr>
              <td>{{__('Discount Amount')}}</td>
              <td>{{offerDetailsObject.discount_amount}}</td>
            </tr>
            <tr>
              <td>{{__('Unit Categories Included')}}</td>
              <td><p v-for="(category,i) in offerDetailsObject.categories" :key="i">{{category.name[locale]}}</p></td>
            </tr>
            <tr>
              <td>{{__('Discount will be applied on the following days')}}</td>
              <td><span v-for="(day,i) in offerDetailsObject.days" :key="i">{{__(day.charAt(0).toUpperCase() + day.slice(1))}}</span></td>
            </tr>
            <tr>
              <td>{{__('Offer Price Start and End Date')}}</td>
              <td>{{ offerDetailsObject.start_date | formatDateSpecial }} - {{offerDetailsObject.end_date | formatDateSpecial}}</td>
            </tr>
            <tr>
              <td>{{__('Show status')}}</td>
              <td>
                <label class="Enabled" v-if="offerDetailsObject && offerDetailsObject.enabled">{{__('Enabled')}}</label>
                <label class="Not_Enabled" v-else-if="offerDetailsObject && !offerDetailsObject.enabled">{{__('Not Enabled')}}</label>
              </td>
            </tr>
          </tbody>
        </table>
      </div><!-- table_responsive -->
    </sweet-modal>
    <!-- View Offer Details Modal -->

    <!-- Edit Offer Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Edit offer')" overlay-theme="dark" ref="editOfferModal" class="Add_new_offer_modal relative">

        <!-- Loader -->
        <loading :active.sync="isLoading"
                  :can-cancel="true"
                  :loader="'spinner'"
                  :color="'#7e7d7f'"
                  :is-full-page="false">
        </loading>
        <!-- Discount name -->
        <div class="form_group">
            <label>{{__('Offer Name')}}<span class="required">*</span></label>
            <input type="text" :placeholder="__('Offer Name')" v-model="offerObject.offer_name">
        </div><!-- form_group -->

        <!-- Discount type & amount -->
        <div class="row_group">
            <div class="col">
                <label>{{__('Discount Type')}} <span class="required">*</span></label>
                <select v-model="offerObject.discount_type">
                    <option :value="null" disabled>{{__('Select Discount Type')}}</option>
                    <option value="percentage">{{__('Percentage(%)')}}</option>
                    <option value="price">{{__('Price (SAR)')}}</option>
                </select>
            </div><!-- col -->
            <div class="col">
                <label>{{__('Discount Amount')}} <span class="required">*</span></label>
                <input type="tel"  v-model="offerObject.discount_amount" :placeholder="__('Discount Amount')">
            </div><!-- col -->
        </div><!-- row_group -->

        <!-- Categories -->
        <div class="form_group">
            <label>{{__('Unit Categories Included')}} <span class="required">*</span></label>
            <div class="all_units last">
                <label>
                    <input type="checkbox" :checked="allCategoriesChecked" @change="checkAllCategories(categories)">
                    <span class="checkmark" />
                    <p>{{__('Check All')}}</p>
                </label>

                <template v-if="categories">
                    <label v-for="(category , i) in categories" :key="i">
                        <input type="checkbox" :checked="selectedCategoriesCollector.includes(category.id)" @change="handleCategoriesCollectorState(category)">
                        <span class="checkmark" />
                        <p>{{ category.name[locale] }}</p>
                    </label>
                </template>
            </div><!-- all_units -->
        </div><!-- form_group -->

        <!-- Days -->
        <div class="form_group">
            <label>{{__('Discount will be applied on the following days')}} <span class="required"></span></label>
            <div class="all_units last">
                <label>
                    <input type="checkbox" :checked="allDaysChecked" @change="checkAllDays(initDays)">
                    <span class="checkmark" />
                    <p>{{__('Check All')}}</p>
                </label>

                <!-- Days of week -->
                <label v-for="(day,i) in initDays" :key="i">
                    <input type="checkbox"  :checked="selectedDaysCollector.includes(day)" @change="handleDaysCollectorState(day)">
                    <span class="checkmark" />
                    <p>{{__(day.charAt(0).toUpperCase() + day.slice(1))}}</p>
                </label>


            </div><!-- all_units -->
        </div><!-- form_group -->

        <!-- Calendar -->
        <div class="form_group">
            <label>{{__('Offer Price Start and End Date')}} <span class="required">*</span></label>
            <vcc-date-picker
                    class='v-date-picker'
                    :locale="vcc_local"
                    mode='range'
                    v-model='offerObject.selectedDate'
                    show-caps
                    is-expanded
                    :columns="$screens({ default: 1, lg: 2 })"
                    :popoverExpanded="true"
                    :min-date='minDate'
                    :popover="{ placement: 'top', visibility: 'click' }"
                    @change="logger"
                    @input="logger"
            >
                <input v-model="inputValue = defaultRange" :readonly="true" type="text">
            </vcc-date-picker>

        </div><!-- form_group -->

        <!-- Enable Offer -->
        <div class="switch_group">
            <label>{{__('Enable Offer')}}</label>
            <div class="switch">
                <input type="checkbox" v-model="offerObject.enable_offer" @change="handleEnableOffer($event)">
                <span class="slider round" />
            </div><!-- switch -->
        </div><!-- switch_group -->
        <button class="save_add_offer" @click="updateOffer">{{__('Update')}}</button>
    </sweet-modal>
    <!-- Edit Offer Modal -->

    <!-- Delete Confirm Component -->
    <delete-confirm ref="deleteShared" :id="targetToDelete" :targetModel="model" />
  </div>
</template>

<script>
    import Pagination from '../Pagination';
    import Loading from 'vue-loading-overlay';
    import DeleteConfirm from '../DeleteConfirm';
    export default {
        name: "OffersComponent",
        props : ['categories' , 'locale'],
        components : {
            Pagination,
            Loading,
            DeleteConfirm
        },
        data(){
            return {
                inputValue : null,
                currency :Nova.app.currentTeam.currency,
                vcc_local: {
                    id: Nova.config.local,
                    firstDayOfWeek: 1,
                    masks: {
                        weekdays: 'WWW',
                        input: ['WWWW YYYY/MM/DD', 'L'],
                        data: ['WWWW YYYY/MM/DD', 'L'],
                    }
                },
                minDate :  new Date(),
                isLoading : false,

                selectedCategoriesCollector: [],
                selectedCategories : [],
                allCategoriesChecked : false,

                initDays : [
                    'sunday' ,
                    'monday' ,
                    'tuesday' ,
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday'
                ],
                selectedDaysCollector : [],
                allDaysChecked : false,

                offerObject : {
                    offer_name : null,
                    discount_type : null,
                    discount_amount : null ,
                    categories : [],
                    days : [],
                    selectedDate: {
                        start: new Date(moment().toISOString()),
                        end: new Date(moment().add('1', 'days').toISOString())
                    },
                    enable_offer : false
                },
                offers : [],
                paginator : {},
                offerDetailsObject : {},
                model : 'Offer',
                targetToDelete : null

            }
        },
        computed: {
            defaultRange(){
                moment.locale(this.vcc_local.id);
                let start = moment(this.offerObject.selectedDate.start).format('dddd YYYY/MM/DD') ;
                let end = moment(this.offerObject.selectedDate.end).format('dddd YYYY/MM/DD') ;
                return start + ' - ' + end;
            }
        },
        methods : {
          openNewOfferModal(){
            Nova.$emit('empty-fields');
            this.$refs.newOfferModal.open();
          },
            logger(){
                console.log(this.offerObject.selectedDate);
            },
            /**
             * Handle the state of  categories  checkboxes
             * @param category
             */
            handleCategoriesCollectorState(category){
                if(this.selectedCategoriesCollector.includes(category.id)){
                    this.selectedCategoriesCollector = this.selectedCategoriesCollector.filter(item => item != category.id);
                    this.selectedCategories = this.selectedCategories.filter(item => item.id != category.id);
                }else{
                    this.selectedCategoriesCollector.push(category.id);
                    this.selectedCategories.push(category);
                }
                this.offerObject.categories = this.selectedCategories;

                if(this.offerObject.categories.length < this.categories.length){
                    this.allCategoriesChecked = false;
                }else{
                    this.allCategoriesChecked = true;
                }

            },
            /**
             * @note The Fucking check-all option for categories
             * @param categories
             */
            checkAllCategories(categories){

                if(!this.allCategoriesChecked){
                    this.allCategoriesChecked = true;
                    this.selectedCategories = [];
                    this.selectedCategoriesCollector = [];
                    let self = this;
                    $.each(categories , function(index , category){
                         self.handleCategoriesCollectorState(category);
                    });
                }else{
                    this.allCategoriesChecked = false;
                    this.offerObject.categories = [];
                    this.selectedCategories = [];
                    this.selectedCategoriesCollector = [];
                }

            },
            /**
             * @note : this is an override to the base function cause am not aware of this data property allCategoriesChecked
             * every time i open my modal
             */
            checkAllCategoriesForEdit(categories){
                    this.selectedCategories = [];
                    this.selectedCategoriesCollector = [];
                    let self = this;
                    $.each(categories , function(index , category){
                        self.handleCategoriesCollectorState(category);
                    });
            },
            /**
             * Handle the state of days checkboxes
             * @param initDays
             */
            handleDaysCollectorState(day){

                if(this.selectedDaysCollector.includes(day)){
                    this.selectedDaysCollector = this.selectedDaysCollector.filter(item => item !== day);
                }else{
                    this.selectedDaysCollector.push(day);
                }

                this.offerObject.days = this.selectedDaysCollector;

                if(this.offerObject.days.length < this.initDays.length){
                    this.allDaysChecked = false;
                }else{
                    this.allDaysChecked = true;
                }
            },
            /**
             * @note The Fucking check-all option for week days
             * @param initDays
             */
            checkAllDays(initDays){
                let self = this;
                if(!self.allDaysChecked){
                    self.allDaysChecked = true;
                    self.selectedDaysCollector = [];
                    $.each(initDays , function(index , day){
                        self.handleDaysCollectorState(day);
                    });
                }else{
                    this.allDaysChecked = false;
                    this.offerObject.days = [];
                    this.selectedDaysCollector = [];
                }
            },

            /**
             * @note : this is an override to the base function cause am not aware of this data property allDaysChecked
             * every time i open my modal
             */
            checkAllDaysForEdit(initDays){
                let self = this;
                self.selectedDaysCollector = [];
                $.each(initDays , function(index , day){
                    self.handleDaysCollectorState(day);
                });
            },

            /**
             * Check action for enable offer
            **/
            handleEnableOffer(event){
                this.offerObject.enable_offer = event.target.checked;
            },
            /**
             * Store offer
             */
            addOffer(){

                if(!this.offerObjectHasErrors()){

                    // after we made sure that everything is okay
                    // we need to submit the offerObject data and store it

                    this.isLoading = true;

                    axios.post('/nova-vendor/units/store-offer' , {offer : this.offerObject , categories_ids : this.selectedCategoriesCollector})
                    .then(response => {

                        if(response.data.status === 'offer_created'){

                            this.$toasted.show(this.__('Offer has been added successfully'), {
                                duration : 4000,
                                type: 'success',
                                position : 'top-center',
                            });

                            this.emptyOfferFields();

                            this.$refs.newOfferModal.close();

                            this.getOffers();

                        }

                        if(response.data.status === 'offer_is_found_before'){
                            this.$toasted.show(this.__('Sorry , we can\'t add or update this offer cause it intersect with another offer'), {
                                duration : 4000,
                                type: 'error',
                                position : 'top-center',
                            });
                        }

                        if(response.data.status === 'something_wrong'){
                            this.$toasted.show(this.__('Something went wrong , we are working hard to fix it'), {
                                duration : 4000,
                                type: 'error',
                                position : 'top-center',
                            });
                        }

                        this.isLoading = false;
                    })
                }

            },

            /**
             * Validate Offer Object data
             * @returns {boolean}
             */
            offerObjectHasErrors(){
                if(!this.offerObject.offer_name){
                    this.$toasted.show(this.__('Offer name is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(this.offerObject.discount_type === null){
                    this.$toasted.show(this.__('Discount type is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(!this.offerObject.discount_amount){
                    this.$toasted.show(this.__('Discount amount is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }
                if(!Number(this.offerObject.discount_amount)){
                    this.$toasted.show(this.__('Discount amount must be numbers only and greater than zero'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(!this.offerObject.categories.length){
                    this.$toasted.show(this.__('Categories is required , please make sure to select one category at least'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(!this.offerObject.days.length){
                    this.$toasted.show(this.__('Days is required , please make sure to select one day at least'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(!this.offerObject.selectedDate.start || !this.offerObject.selectedDate.start ){
                    this.$toasted.show(this.__('Offer start & end dates are required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }
            },

            /**
             * Empty offer field
             * @todo : the date field is not reset
             */
            emptyOfferFields() {
                this.offerObject.offer_name = null;
                this.offerObject.discount_type = null;
                this.offerObject.discount_amount = null;
                this.offerObject.categories = [];
                this.offerObject.days = [];
                this.offerObject.enable_offer = false;
                this.offerObject.selectedDate.start = new Date(moment().toISOString());
                this.offerObject.selectedDate.end = new Date(moment().add('1', 'days').toISOString());
                this.selectedCategoriesCollector =  [];
                this.selectedCategories = [];
                this.allCategoriesChecked = false;
                this.selectedDaysCollector = [];
                this.allDaysChecked = false;
            },

            emitGetOffersEvent(){
                Nova.$emit('get-offers-by-paginator')
            },
            /**
             * Get Offers Paginated
             * @param page
             */
            getOffers(page=1){
                this.isLoading = true;
                let url  = `/nova-vendor/units/get-resources/Offer?page=`+ page;
                axios.get(url)
                    .then(response => {
                        this.offers = response.data.data;
                        this.paginator = {
                            currentPage : response.data.current_page ,
                            lastPage : response.data.last_page ,
                            from : response.data.from,
                            to : response.data.to,
                            totalResults : response.data.total,
                            pathPage : response.data.path + '?page=',
                            firstPageUrl : response.data.first_page_url ,
                            lastPageUrl : response.data.last_page_url ,
                            nextPageUrl : response.data.next_page_url ,
                            prevPageUrl : response.data.prev_page_url ,
                        };

                        this.isLoading = false;
                    });
            },

            /**
             * Open Offer details modal
             * @param offer
             */
            openOfferDetailsModal(offer){
                this.offerDetailsObject = offer;
                this.$refs.offerDetailsModal.open();
            },
            /**
             * Close Offer Details modal
             */
            closeOfferDetailsModal(){
                this.$refs.offerDetailsModal.close();
            },

            /**
             * Edit Offer Modal
             * @param offer
             */
            openOfferEditModal(offer){
                let self = this;
                self.offerObject = {
                        id : offer.id,
                        offer_name : offer.name,
                        discount_type : offer.discount_type,
                        discount_amount : offer.discount_amount ,
                        categories : offer.categories,
                        days : offer.days,
                        selectedDate: {
                            start: new Date(moment(offer.start_date).toISOString()),
                            end: new Date(moment(offer.end_date).toISOString())
                        },
                    enable_offer : offer.enabled
                };

                self.checkAllCategoriesForEdit(self.offerObject.categories);
                self.checkAllDaysForEdit(self.offerObject.days);
                self.$refs.editOfferModal.open();
            },
            /**
             * Update Offer
             */
            updateOffer(){
                if(!this.offerObjectHasErrors()){
                    this.isLoading = true;
                    axios.put('/nova-vendor/units/update-offer' , {data : this.offerObject , categories_ids : this.selectedCategoriesCollector})
                    .then(response => {

                         if(response.data.status === 'offer_updated'){

                            this.$toasted.show(this.__('Offer has been updated successfully'), {
                                duration : 4000,
                                type: 'success',
                                position : 'top-center',
                            });

                            this.emptyOfferFields();

                            this.$refs.editOfferModal.close();

                            this.getOffers();

                        }

                        if(response.data.status === 'offer_is_found_before'){
                            this.$toasted.show(this.__('Sorry , we can\'t add or update this offer cause it intersect with another offer'), {
                                duration : 4000,
                                type: 'error',
                                position : 'top-center',
                            });
                        }

                        if(response.data.status === 'something_wrong'){
                            this.$toasted.show(this.__('Something went wrong , we are working hard to fix it'), {
                                duration : 4000,
                                type: 'error',
                                position : 'top-center',
                            });
                        }

                        this.isLoading = false;
                    });
                }

            },
            /**
             * Delete Offer
             * @param id
             */
            initDeleteConfirm(id){
                this.targetToDelete = id;
                this.$refs.deleteShared.$refs.deleteConfirm.open();
            },

        },
        watch : {

            offerObject: {
                handler: function(newVal) {
                    if(newVal.discount_amount !== null){
                        newVal.discount_amount = newVal.discount_amount.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.discount_amount = newVal.discount_amount.replace(/[^\d.\d]/g,'')
                    }
                },
                deep: true
            },

        },
        mounted(){
            Nova.$on('Offer-destroyed' , () => {
                this.getOffers(1);
            });

            Nova.$on('empty-fields' , ()=> {
                this.emptyOfferFields();
            })
        },
        created(){
            this.getOffers();
        },
        beforeDestroy(){
            moment.locale('en');
        }
    }
</script>

<style lang="scss">
.View_offer_modal {
  .sweet-content {
    max-height: 500px;
    overflow-y: auto;
    display: block !important;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
  div.label {
    display: block;
    margin: 0 0 10px;
    color: #fff;
    padding: 0 25px;
    border-radius: 100px;
    height: 25px;
    line-height: 25px;
    font-size: 15px;
  } /* div.label */
  .table_responsive {
    @media (min-width: 320px) and (max-width: 767px) {
      overflow: auto;
    } /* media */
    table {
      width: 100%;
      @media (min-width: 320px) and (max-width: 767px) {
        margin: 0 auto 15px;
      } /* media */
      tr {
        td {
          &:first-child {
            background: #4a5568;
            border: 1px solid #5E697C;
            font-size: 15px;
            padding: 10px;
            vertical-align: middle;
            color: #fff;
            font-weight: normal;
            text-align: inherit;
            width: 30%;
            @media (min-width: 320px) and (max-width: 767px) {
              width: 50%;
            } /* media */
          } /* first-child */
          &:last-child {
            background: #fafafa;
            border: 1px solid #d3d3d3;
            color: #000;
            vertical-align: middle;
            padding: 10px;
            font-size: 15px;
            line-height: 20px;
            text-align: inherit;
          } /* first-child */
          p {
            display: inline-block;
            margin: 2.5px;
            background: #ffffff;
            border-radius: 4px;
            font-size: 14px;
            padding: 2px 5px;
            border: 1px solid #eee;
          } /* p */
          span {
            display: inline-block;
            margin: 2.5px;
            background: #ffffff;
            border-radius: 4px;
            font-size: 14px;
            padding: 2px 5px;
            border: 1px solid #eee;
          } /* span */
          label {
            border-radius: 100px;
            padding: 0 20px;
            height: 25px;
            line-height: 25px;
            color: #fff;
            font-size: 15px;
            display: inline-block;
            &.Enabled {
              background: green;
            } /* Enabled */
            &.Not_Enabled {
              background: red;
            } /* Not_Enabled */
          } /* label */
        } /* td */
      } /* tr */
    } /* table */
  } /* table_responsive */
} /* View_offer_modal */
</style>
