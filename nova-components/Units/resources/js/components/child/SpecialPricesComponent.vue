<template>
  <div class="root">
    <div class="table_area">
        <div class="title">
          <span>{{__('Special Prices')}}<p>( <template v-if="paginator">{{paginator.totalResults}}</template><template v-else>0</template> )</p></span>
          <button type="button" @click="openNewSpecialPriceModal">{{__('Add special prices')}}</button>
        </div>
        <div class="table_content">
            <div class="table_responsive relative">
                <!-- Loader -->
                <loading :active.sync="isLoading"
                          :can-cancel="true"
                          :loader="'spinner'"
                          :color="'#7e7d7f'"
                          :is-full-page="false">
                </loading>
                <table>
                    <thead>
                    <tr>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Unit category')}}</th>
                        <th>{{__('Creation Date')}}</th>
                        <th>{{__('Special Price Start & End Date')}}</th>
                        <th>{{__('Actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                        <template v-if="specialPrices && specialPrices.length">
                            <tr v-for="(obj , i) in specialPrices" :key="i">
                                <td><span :class="{enabled : obj.enabled , not_enabled : !obj.enabled}">{{obj.name}}</span></td>
                                <td>
                                    <div class="units">
                                        <p>{{obj.unit_category.name[locale]}}</p>
                                    </div><!-- units -->
                                </td>
                                <td>{{obj.created_at | formatDateSpecial }}</td>
                                <td>
                                    {{ obj.start_date | formatDateSpecial }} - {{obj.end_date | formatDateSpecial}}
                                </td>
                                <td>
                                    <div class="action">
                                        <a class="cursor-pointer" @click="openSpecialPriceDetailsModal(obj)" :title="__('View')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current">
                                                <path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path>
                                            </svg>
                                        </a>
                                        <a class="cursor-pointer" @click="openSpecialPriceEditModal(obj)" :title="__('Edit')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current">
                                                <path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path>
                                            </svg>
                                        </a>
                                        <button type="button" :title="__('Delete')" @click="initDeleteConfirm(obj.id)"
                                        v-if="obj.enabled == 0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current">
                                                <path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path>
                                            </svg>
                                        </button>
                                    </div><!-- action -->
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="5" class="text-center">
                                    {{__('there are no special prices yet')}}
                                </td>
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
                        :click-handler="getSpecialPrices"
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

    <!-- Add Special Prices Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add special prices')" overlay-theme="dark" ref="newSpecialPriceModal" class="Add_new_offer_modal relative">
      <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
      <div class="row_group">
        <div class="col">
          <label>{{__('Name')}}<span class="required">*</span></label>
          <input type="text" :placeholder="__('Name')" v-model="specialPriceObject.name">
        </div><!-- col -->
        <div class="col">
          <label>{{__('Unit category')}}<span class="required">*</span></label>
          <select v-model="specialPriceObject.unit_category_id">
            <option :value="null" disabled>{{__('Select Unit Category')}}</option>
            <option v-for="(category , i) in categories" :key="i" :value="category.id">{{category.name[locale]}}</option>
          </select>
        </div><!-- col -->
      </div><!-- row_group -->
      <div class="form_group">
        <label>{{__('Days Prices')}}<span class="required">*</span></label>
        <div class="alert_special_price" role="alert">
            {{ __('If a special price is not set on some days, the basic unit price will be applied')}}
        </div>
        <div class="days_prices">
          <div class="item">
            <span>{{__('Sunday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Sunday">
          </div><!-- item -->
          <div class="item">
            <span>{{__('Monday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Monday">
          </div><!-- item -->
          <div class="item">
            <span>{{__('Tuesday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Tuesday">
          </div><!-- item -->
          <div class="item">
            <span>{{__('Wednesday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Wednesday">
          </div><!-- item -->
          <div class="item">
            <span>{{__('Thursday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Thursday">
          </div><!-- item -->
          <div class="item">
            <span>{{__('Friday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Friday">
          </div><!-- item -->
          <div class="item">
            <span>{{__('Saturday')}}</span>
            <input type="tel"  v-model="specialPriceObject.days_prices.Saturday">
          </div><!-- item -->
        </div><!-- days_prices -->
      </div><!-- form_group -->
      <div class="form_group">
        <label>{{__('Special Price Start & End Date')}}<span class="required">*</span></label>
        <vcc-date-picker
          class='v-date-picker'
          :locale="vcc_local"
          mode='range'
          v-model='specialPriceObject.selectedDate'
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
      <div class="switch_group">
        <label>{{__('Enable Special Price')}}</label>
        <div class="switch">
          <input type="checkbox" v-model="specialPriceObject.enable_special_price" @change="handleEnableSpecialPrice($event)">
          <span class="slider round" />
        </div><!-- switch -->
      </div><!-- switch_group -->
      <button class="save_add_offer" @click="addSpecialPrice">{{__('Save')}}</button>
    </sweet-modal>
    <!-- Add Special Prices Modal -->

    <!-- View Special Price Details Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Special Price Details')" overlay-theme="dark" ref="specialPriceDetailsModal" class="View_offer_modal relative">
      <div class="table_responsive">
        <table>
          <tbody>
            <tr>
              <td>{{__('Name')}}</td>
              <td>{{specialPriceDetailsObject.name}}</td>
            </tr>
            <tr>
              <td>{{__('Unit category')}}</td>
              <td>{{specialPriceDetailsObject.unit_category}}</td>
            </tr>
            <tr>
              <td>{{__('Days Prices')}}</td>
              <td>
                <template v-if="specialPriceDetailsObject.days_prices">
                  <ul>
                    <li>{{__('Sunday')}} : {{specialPriceDetailsObject.days_prices.Sunday != null ? specialPriceDetailsObject.days_prices.Sunday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                    <li>{{__('Monday')}} : {{specialPriceDetailsObject.days_prices.Monday != null ? specialPriceDetailsObject.days_prices.Monday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                    <li>{{__('Tuesday')}} : {{specialPriceDetailsObject.days_prices.Tuesday != null ? specialPriceDetailsObject.days_prices.Tuesday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                    <li>{{__('Wednesday')}} : {{specialPriceDetailsObject.days_prices.Wednesday != null ? specialPriceDetailsObject.days_prices.Wednesday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                    <li>{{__('Thursday')}} : {{specialPriceDetailsObject.days_prices.Thursday != null ? specialPriceDetailsObject.days_prices.Thursday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                    <li>{{__('Friday')}} : {{specialPriceDetailsObject.days_prices.Friday != null ? specialPriceDetailsObject.days_prices.Friday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                    <li>{{__('Saturday')}} : {{specialPriceDetailsObject.days_prices.Saturday != null ? specialPriceDetailsObject.days_prices.Saturday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                  </ul>
                </template>
              </td>
            </tr>
            <tr>
              <td>{{__('Price Start and End Date')}}</td>
              <td>{{ specialPriceDetailsObject.start_date | formatDateSpecial }} - {{specialPriceDetailsObject.end_date | formatDateSpecial}}</td>
            </tr>
            <tr>
              <td>{{__('Special Price Status')}}</td>
              <td>
                <label class="Enabled" v-if="specialPriceDetailsObject && specialPriceDetailsObject.enabled">{{__('Enabled')}}</label>
                <label class="Not_Enabled" v-else-if="specialPriceDetailsObject && !specialPriceDetailsObject.enabled">{{__('Not Enabled')}}</label>
              </td>
            </tr>
          </tbody>
        </table>
      </div><!-- table_responsive -->
    </sweet-modal>
    <!-- View Special Price  Details Modal -->

    <!-- Edit Special Prices Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Edit Special Price')" overlay-theme="dark" ref="editSpecialPriceModal" class="Add_new_offer_modal relative">

        <!-- Loader -->
        <loading :active.sync="isLoading"
                 :can-cancel="true"
                 :loader="'spinner'"
                 :color="'#7e7d7f'"
                 :is-full-page="false">
        </loading>

        <div class="row_group">
            <div class="col">
                <label>{{__('Name')}}<span class="required">*</span></label>
                <input type="text" :placeholder="__('Name')" v-model="specialPriceObject.name">
            </div><!-- col -->
            <div class="col">
                <label>{{__('Unit category')}}<span class="required">*</span></label>
                <select v-model="specialPriceObject.unit_category_id">
                    <option :value="null" disabled>{{__('Select Unit Category')}}</option>
                    <option v-for="(category , i) in categories" :key="i" :value="category.id">{{category.name[locale]}}</option>
                </select>
            </div><!-- col -->
        </div><!-- row_group -->
        <div class="form_group">
            <label>{{__('Days Prices')}}<span class="required">*</span></label>
            <div class="alert_special_price" role="alert">
                {{ __('If a special price is not set on some days, the basic unit price will be applied')}}
            </div>
            <div class="days_prices">

                <template v-if="specialPriceObject.days_prices">
                    <div class="item">
                        <span>{{__('Sunday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Sunday">
                    </div><!-- item -->
                    <div class="item">
                        <span>{{__('Monday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Monday">
                    </div><!-- item -->
                    <div class="item">
                        <span>{{__('Tuesday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Tuesday">
                    </div><!-- item -->
                    <div class="item">
                        <span>{{__('Wednesday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Wednesday">
                    </div><!-- item -->
                    <div class="item">
                        <span>{{__('Thursday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Thursday">
                    </div><!-- item -->
                    <div class="item">
                        <span>{{__('Friday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Friday">
                    </div><!-- item -->
                    <div class="item">
                        <span>{{__('Saturday')}}</span>
                        <input type="tel"  v-model="specialPriceObject.days_prices.Saturday">
                    </div><!-- item -->
                </template>
            </div><!-- days_prices -->
        </div><!-- form_group -->
        <div class="form_group">
            <label>{{__('Special Price Start & End Date')}}<span class="required">*</span></label>
            <vcc-date-picker
                    class='v-date-picker'
                    :locale="vcc_local"
                    mode='range'
                    v-model='specialPriceObject.selectedDate'
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
        <div class="switch_group">
            <label>{{__('Enable Special Price')}}</label>
            <div class="switch">
                <input type="checkbox" v-model="specialPriceObject.enable_special_price" @change="handleEnableSpecialPrice($event)">
                <span class="slider round" />
            </div><!-- switch -->
        </div><!-- switch_group -->
        <button class="save_add_offer" @click="updateSpecialPrice">{{__('Update')}}</button>
    </sweet-modal>
    <!-- Edit Special Prices Modal -->

    <!-- Delete Confirm Component -->
    <delete-confirm ref="deleteShared" :id="targetToDelete" :targetModel="model" />

    </div>

</template>

<script>

    import Pagination from '../Pagination';
    import Loading from 'vue-loading-overlay';
    import DeleteConfirm from '../DeleteConfirm';

    export default {
        name: "SpecialPricesComponent",
        props : ['categories' , 'locale'],
        components: {
            Pagination,
            Loading,
            DeleteConfirm
        },
        computed:{
          defaultRange(){
                moment.locale(this.vcc_local.id);
                let start = moment(this.specialPriceObject.selectedDate.start).format('dddd YYYY/MM/DD') ;
                let end = moment(this.specialPriceObject.selectedDate.end).format('dddd YYYY/MM/DD') ;
                return start + ' - ' + end;
          }
        },
        data(){
            return {
                inputValue : null,
                currency :Nova.app.currentTeam.currency,
                isLoading : false,
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
                specialPriceObject : {
                    name : null,
                    unit_category_id : null,
                    days_prices : {
                        Sunday : null,
                        Monday : null,
                        Tuesday : null,
                        Wednesday : null,
                        Thursday : null,
                        Friday : null,
                        Saturday : null
                    },
                    selectedDate: {
                        start: new Date(moment().toISOString()),
                        end: new Date(moment().add('1', 'days').toISOString())
                    },
                    enable_special_price : false
                },
                specialPriceDetailsObject : {},
                specialPrices : [],
                paginator : {},
                targetToDelete : null,
                model : 'SpecialPrice'

            }
        },
        methods: {
          openNewSpecialPriceModal(){
              Nova.$emit('empty-fields');
              this.$refs.newSpecialPriceModal.open()
          },
            addSpecialPrice(){
                if(!this.specialPriceObjectHasErrors()){

                    this.isLoading = true;
                    axios.post('/nova-vendor/units/store-special-price' , {special_price : this.specialPriceObject})
                    .then(response => {
                        if(response.data.status === 'special_price_created'){

                            this.$toasted.show(this.__('Special Price has been added successfully'), {
                                duration : 4000,
                                type: 'success',
                                position : 'top-center',
                            });

                            this.emptySpecialPriceFields();
                            console.log(this.specialPriceObject.selectedDate);
                            this.$refs.newSpecialPriceModal.close();
                            this.getSpecialPrices();

                        }

                        if(response.data.status === 'special_price_is_found_before'){
                            this.$toasted.show(this.__('Sorry , we can\'t add or update this special price cause it intersect with another special price'), {
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
            updateSpecialPrice(){
                if(!this.specialPriceObjectHasErrors()){

                    this.isLoading = true;
                    axios.put('/nova-vendor/units/update-special-price' , {special_price : this.specialPriceObject})
                        .then(response => {
                            if(response.data.status === 'special_price_updated'){
                                this.$toasted.show(this.__('Special price has been updated successfully'), {
                                    duration : 4000,
                                    type: 'success',
                                    position : 'top-center',
                                });
                                this.emptySpecialPriceFields();
                                this.$refs.editSpecialPriceModal.close();
                                this.getSpecialPrices();
                            }

                            if(response.data.status === 'special_price_is_found_before'){
                                this.$toasted.show(this.__('Sorry , we can\'t add or update this special price cause it intersect with another special price'), {
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
            specialPriceObjectHasErrors(){
                if(!this.specialPriceObject.name){
                    this.$toasted.show(this.__('Special price name is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(!Number(this.specialPriceObject.unit_category_id)){
                    this.$toasted.show(this.__('Unit category id is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(this.checkDaysObjectProperties(this.specialPriceObject.days_prices)){
                    this.$toasted.show(this.__('At least one day price is required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }

                if(!this.specialPriceObject.selectedDate.start || !this.specialPriceObject.selectedDate.start ){
                    this.$toasted.show(this.__('Special price start & end dates are required'), {
                        duration : 4000,
                        type: 'error',
                        position : 'top-center',
                    });
                    return true;
                }
            },
            checkDaysObjectProperties(obj) {
                return  !Object.values(obj).some(x => (x !== null && x !== ''));
            },
            emptySpecialPriceFields() {
                this.specialPriceObject.name = null;
                this.specialPriceObject.unit_category_id = null;
                this.specialPriceObject.days_prices = {
                    Sunday : null,
                    Monday : null,
                    Tuesday : null,
                    Wednesday : null,
                    Thursday : null,
                    Friday : null,
                    Saturday : null
                };

                this.specialPriceObject.selectedDate.start = new Date(moment().toISOString());
                this.specialPriceObject.selectedDate.end = new Date(moment().add('1', 'days').toISOString());

            },
            handleEnableSpecialPrice(event){
                this.specialPriceObject.enable_special_price = event.target.checked;
            },
            getSpecialPrices(page=1){
                this.isLoading = true;
                let url  = `/nova-vendor/units/get-resources/SpecialPrice?page=`+ page;
                axios.get(url)
                    .then(response => {
                        this.specialPrices = response.data.data;
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
            openSpecialPriceDetailsModal(obj){

                this.specialPriceDetailsObject = {
                    name : obj.name,
                    unit_category : obj.unit_category.name[this.locale],
                    days_prices  : {
                        Sunday : obj.days_prices.Sunday,
                        Monday : obj.days_prices.Monday,
                        Tuesday : obj.days_prices.Tuesday,
                        Wednesday : obj.days_prices.Wednesday,
                        Thursday : obj.days_prices.Thursday,
                        Friday : obj.days_prices.Friday,
                        Saturday : obj.days_prices.Saturday,
                    },
                    start_date : obj.start_date,
                    end_date: obj.end_date,
                    enabled : obj.enabled
                };

                this.$refs.specialPriceDetailsModal.open();
            },
            closeSpecialPriceDetailsModal(){
              this.$refs.specialPriceDetailsModal.close();
            },
            openSpecialPriceEditModal(obj){

                this.specialPriceObject = {
                    id : obj.id,
                    name : obj.name,
                    unit_category_id : obj.unit_category_id,
                    days_prices : obj.days_prices,
                    selectedDate: {
                        start: new Date(moment(obj.start_date).toISOString()),
                        end: new Date(moment(obj.end_date).toISOString())
                    },
                    enable_special_price : obj.enabled
                };

                this.$refs.editSpecialPriceModal.open();
            },
            initDeleteConfirm(id){
                this.targetToDelete = id;
                this.$refs.deleteShared.$refs.deleteConfirm.open();
            }
        },
        watch: {
            specialPriceObject: {

                handler: function(newVal) {
                    if(newVal.days_prices.Sunday !== null){
                        newVal.days_prices.Sunday = newVal.days_prices.Sunday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Sunday = newVal.days_prices.Sunday.replace(/[^\d.\d]/g,'')
                    }
                    if(newVal.days_prices.Monday !== null){
                        newVal.days_prices.Monday = newVal.days_prices.Monday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Monday = newVal.days_prices.Monday.replace(/[^\d.\d]/g,'')
                    }
                    if(newVal.days_prices.Tuesday !== null){
                        newVal.days_prices.Tuesday = newVal.days_prices.Tuesday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Tuesday = newVal.days_prices.Tuesday.replace(/[^\d.\d]/g,'')
                    }
                    if(newVal.days_prices.Wednesday !== null){
                        newVal.days_prices.Wednesday = newVal.days_prices.Wednesday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Wednesday = newVal.days_prices.Wednesday.replace(/[^\d.\d]/g,'')
                    }
                    if(newVal.days_prices.Thursday !== null){
                        newVal.days_prices.Thursday = newVal.days_prices.Thursday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Thursday = newVal.days_prices.Thursday.replace(/[^\d.\d]/g,'')
                    }
                    if(newVal.days_prices.Friday !== null){
                        newVal.days_prices.Friday = newVal.days_prices.Friday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Friday = newVal.days_prices.Friday.replace(/[^\d.\d]/g,'')
                    }
                    if(newVal.days_prices.Saturday !== null){
                        newVal.days_prices.Saturday = newVal.days_prices.Saturday.replace(/([٠١٢٣٤٥٦٧٨٩])|([۰۱۲۳۴۵۶۷۸۹])/g, (m, $1, $2) => m.charCodeAt(0) - ($1 ? 1632 : 1776));
                        newVal.days_prices.Saturday = newVal.days_prices.Saturday.replace(/[^\d.\d]/g,'')
                    }
                },
                deep: true
            },
        },
        mounted(){
            Nova.$on('SpecialPrice-destroyed' , () => {
                this.getSpecialPrices(1);
            });
            Nova.$on('empty-fields' , ()=> {
                this.emptySpecialPriceFields();
            })
        },
        created(){
            this.getSpecialPrices();
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
          ul {
            li {
              display: inline-block;
              margin: 2.5px;
              background: #ffffff;
              border-radius: 4px;
              font-size: 14px;
              padding: 2px 5px;
              border: 1px solid #eee;
            } /* li */
          } /* ul */
        } /* td */
      } /* tr */
    } /* table */
  } /* table_responsive */
} /* View_offer_modal */

.alert_special_price {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  padding: 10px;
  border-radius: 4px;
  font-size: 15px;
  color: #856404;
  margin: 0 auto 15px;
  a, button {
    color: #533f03;
    font-weight: bold;
    outline: none;
    &:hover {text-decoration: underline;}
  } /* a */
} /* upgrade_subscription_msg */
</style>
