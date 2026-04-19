<template>
       <div>

        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>
        <router-link to="/companies?page=1" v-permission="'view companies'">
            <div class="my-1 px-1 w-full overflow-hidden sm:w-1/2 md:w-1/2 lg:w-1/2 xl:w-1/3 cursor-pointer">
                <div class=" setting flex bg-white rounded-lg p-4 shadow m-2">
                    <div class="h-16 pt-2 w-16  text-center text-4xl  text-muted ">
                        <svg xmlns="http://www.w3.org/2000/svg" width="45.688" height="47.001" viewBox="0 0 45.688 47.001"><g transform="translate(-7.984 0.998)"><path d="M165.517,85.93h14.13a1.422,1.422,0,0,0,1.42-1.42V81.716a1.422,1.422,0,0,0-1.42-1.42h-14.13a1.422,1.422,0,0,0-1.42,1.42V84.51A1.422,1.422,0,0,0,165.517,85.93Zm-.046-4.214a.046.046,0,0,1,.046-.046h14.13a.046.046,0,0,1,.046.046V84.51a.046.046,0,0,1-.046.046h-14.13a.046.046,0,0,1-.046-.046Z" transform="translate(-141.754 -73.817)" fill="#333b45"/><path d="M165.517,165.119h1.705a.687.687,0,1,0,0-1.374h-1.705a.046.046,0,0,1-.046-.046v-2.794a.046.046,0,0,1,.046-.046h14.13a.046.046,0,0,1,.046.046V163.7a.046.046,0,0,1-.046.046h-9.241a.687.687,0,1,0,0,1.374h9.241a1.421,1.421,0,0,0,1.42-1.42v-2.794a1.421,1.421,0,0,0-1.42-1.42h-14.13a1.421,1.421,0,0,0-1.42,1.42V163.7A1.421,1.421,0,0,0,165.517,165.119Z" transform="translate(-141.754 -145.722)" fill="#333b45"/><path d="M52.251,41.867h-.818V9.83a1.421,1.421,0,0,0-1.42-1.42h-5.4V3.569h1.045A1.168,1.168,0,0,0,46.824,2.4V.168A1.168,1.168,0,0,0,45.657-1h-4.5a.687.687,0,0,0-.687.687.687.687,0,0,0,.687.687h4.292V2.195H16.206V.376H37.928a.687.687,0,0,0,.687-.687A.687.687,0,0,0,37.928-1H16A1.168,1.168,0,0,0,14.832.168V2.4A1.168,1.168,0,0,0,16,3.569h1.045v4.84h-5.4a1.422,1.422,0,0,0-1.42,1.42V41.867H9.4a1.421,1.421,0,0,0-1.42,1.42v1.3A1.422,1.422,0,0,0,9.4,46H52.251a1.422,1.422,0,0,0,1.42-1.42v-1.3A1.422,1.422,0,0,0,52.251,41.867ZM44.612,9.784h5.4a.046.046,0,0,1,.046.046V41.867H44.612ZM18.417,3.569H43.238v38.3H39.313v-18.3a1.146,1.146,0,0,0-1.145-1.145H30.877l-.049,0-.049,0H23.488a1.146,1.146,0,0,0-1.145,1.145v18.3H18.417ZM37.938,23.795V41.867H31.564V23.795Zm-7.847,0V41.867H23.717V23.795ZM11.6,9.83a.046.046,0,0,1,.046-.046h5.4V41.867H11.6ZM52.3,44.583a.046.046,0,0,1-.046.046H9.4a.046.046,0,0,1-.046-.046v-1.3a.046.046,0,0,1,.046-.046H52.252a.046.046,0,0,1,.046.046Z" fill="#333b45"/><path d="M420.757,150.518H418.97a.687.687,0,0,0,0,1.375h1.786a.687.687,0,0,0,0-1.375Z" transform="translate(-372.56 -137.58)" fill="#333b45"/><path d="M420.757,189.364H418.97a.687.687,0,0,0,0,1.375h1.786a.687.687,0,0,0,0-1.375Z" transform="translate(-372.56 -172.853)" fill="#333b45"/><path d="M420.757,228.21H418.97a.687.687,0,0,0,0,1.375h1.786a.687.687,0,0,0,0-1.375Z" transform="translate(-372.56 -208.126)" fill="#333b45"/><path d="M420.757,267.06H418.97a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-372.56 -243.402)" fill="#333b45"/><path d="M420.757,305.906H418.97a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-372.56 -278.675)" fill="#333b45"/><path d="M420.757,344.752H418.97a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-372.56 -313.948)" fill="#333b45"/><path d="M420.757,383.6H418.97a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-372.56 -349.221)" fill="#333b45"/><path d="M420.757,422.444H418.97a.687.687,0,0,0,0,1.374h1.786a.687.687,0,0,0,0-1.374Z" transform="translate(-372.56 -384.495)" fill="#333b45"/><path d="M62.516,150.518H60.729a.687.687,0,0,0,0,1.375h1.786a.687.687,0,0,0,0-1.375Z" transform="translate(-47.27 -137.58)" fill="#333b45"/><path d="M62.516,189.364H60.729a.687.687,0,0,0,0,1.375h1.786a.687.687,0,0,0,0-1.375Z" transform="translate(-47.27 -172.853)" fill="#333b45"/><path d="M62.516,228.21H60.729a.687.687,0,0,0,0,1.375h1.786a.687.687,0,0,0,0-1.375Z" transform="translate(-47.27 -208.126)" fill="#333b45"/><path d="M62.516,267.06H60.729a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-47.27 -243.402)" fill="#333b45"/><path d="M62.516,305.906H60.729a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-47.27 -278.675)" fill="#333b45"/><path d="M62.516,344.752H60.729a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-47.27 -313.948)" fill="#333b45"/><path d="M62.516,383.6H60.729a.687.687,0,1,0,0,1.374h1.786a.687.687,0,1,0,0-1.374Z" transform="translate(-47.27 -349.221)" fill="#333b45"/><path d="M62.516,422.444H60.729a.687.687,0,0,0,0,1.374h1.786a.687.687,0,0,0,0-1.374Z" transform="translate(-47.27 -384.495)" fill="#333b45"/></g></svg>
                    </div>
                    <div  class=" w-5/6">
                            <h2 class="text-lg mb-0 mt-2">{{__('Companies Management')}}</h2>
                        <div class="text-muted">{{__('Control & Manage Companies & Group Reservations')}}</div>
                    </div>
                </div>
            </div>
        </router-link>
        <div id="customer_management_page">
            <div class="title">{{ __('Customers') }}</div>
            <div class="content_page">

                <!-- Filter Area -->
                <div class="filter_area">



                     <div class="item">
                        <input
                            type="text"
                            v-model.lazy="customer_name"
                            :placeholder="__('Name')"
                            @change="getCustomers"
                        >
                     </div>
                      <div class="item">
                        <input
                            type="text"
                            v-model.lazy="idNumber"
                            :placeholder="__('ID Number')"
                            @change="getCustomers"
                        >
                      </div>
                       <div class="item">
                        <input
                            type="text"
                            v-model.lazy="phoneNumber"
                            :placeholder="__('Phone Number')"
                            @change="getCustomers"
                        >
                        </div>

                        <div class="item">
                                <select  v-model.lazy="highlight_id"  @change="getCustomers">
                                    <option value="" selected="selected">{{__('Highlight')}}</option>
                                    <option v-for="(highlight,i) in highlights" :key="i" :value="highlight.id">{{highlight.name}}</option>
                                </select>
                        </div>


                       <div class="item">
                        <select  v-model.lazy="gender"  @change="getCustomers">
                                <option value="" selected="selected">{{__('Gender')}}</option>
                                <option value="female">{{__('Female')}}</option>
                                <option value="male">{{__('Male')}}</option>
                            </select>
                        </div>


                    <div class="reset_filters" slot="reset-btn">
                        <button
                            @click="resetFilters()"
                            v-tooltip="{
                          targetClasses: ['it-has-a-tooltip'],
                          placement: 'top',
                          content: __('Reset Filters'),
                          classes: ['tooltip_reset']
                        }"
                        >
                        </button>
                    </div>
                    <!-- reset_filters -->


                </div>

                <hr>


                 <div class="table_title">
                <span class="table">{{ __('Total Customers Count') }} <p v-if="paginator">{{paginator.totalResults}}</p></span>

                <create-customer v-permission="'add customers'" :highlights="highlights" />
                </div><!-- table_title -->
                <!-- Table Listing Area -->
                <div class="table_area">
                    <div class="table-responsive relative">
                        <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
                        <table class="table w-full"
                               cellpadding="0"
                               cellspacing="0"
                        >
                            <thead>
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Phone Number')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('ID Number')}}</th>
                                <th>{{__('Highlight')}}</th>
                                <th>{{__('Gender')}}</th>
                                <th>{{__('The Reservations')}}</th>
                                <th>{{__('Nationality')}}</th>
                                <th></th>

                            </tr>
                            </thead>
                            <tbody>
                            <template v-if="customers.length">
                                <tr v-for="customer in customers" :key="customer.id">

                                    <td ><router-link class="text-primary" :to="{name : 'new-customers-show' , params: {id: customer.id}}">{{ customer.name ? customer.name : '-' }}</router-link></td>
                                    <td style="direction:ltr">{{customer.phone ? customer.phone : '-'}}</td>
                                    <td>{{customer.email ? customer.email : '-'}}</td>
                                    <td>{{ customer.id_number ? customer.id_number : '-'}}</td>
                                    <td v-if="customer.highlight" v-html="customer.label"></td>
                                    <td v-else>-</td>
                                    <td>{{customer.gender ? __(customer.gender) : '-'}}</td>
                                    <td>{{customer.reservations_count}}</td>
                                    <td>{{customer.nationality ? customer.nationality_string : '-'}}</td>

                                    <td class="td-fit">

                                        <button>
                                            <router-link style="margin: 0 5px !important;" :to="{name : 'new-customers-show' , params: {id: customer.id}}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                                        </router-link>
                                        </button>
                                        <button v-permission="'manage customers'" @click="setCustomer(customer)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
                                        </button>
                                        <button v-permission="'delete customers'" v-if="!customer.reservations_count"  @click="setCustomerId(customer.id)" >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="13" class="text-center p-5">{{__('No Customers Found!')}}</td>
                                </tr>
                            </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="w-full flex flex-wrap mt-3 justify-center" >
                    <pagination
                        v-if="paginator.lastPage > 1"
                        :page-count="paginator.lastPage"
                        :page-range="3"
                        :margin-pages="2"
                        :click-handler="getCustomers"
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
                </div><!-- Pagination -->
                <div class="Results_area" v-if="customers.length">
                    <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
                    <p>{{__('Count')}}  : {{paginator.totalResults}}</p>
                </div><!-- Results_area -->


                </div>
            </div>
            <delete-confirm-component :customer_id="customer_id_to_delete" ref="deleteConfirm" />
            <update-customer :highlights="highlights" :customer_obj="customer_to_edit" :component_from="'index'" ref="editCustomer" />
        </div>


    </div>
</template>

<script>

import DeleteConfirmComponent from './DeleteConfirmComponent';
import CreateCustomer from './create';
import UpdateCustomer from './update';
import Loading from 'vue-loading-overlay';
import Pagination from './Pagination';
export default {
    name : 'customers',
    components : {
        Loading,
        Pagination,
        CreateCustomer,
        DeleteConfirmComponent,
        UpdateCustomer
    },
  data() {
    return {
      data:null,
      customers: [],
      crumbs : [],
      isLoading : false,
      customer: null,
      isLoading : false,
      customer_name : '',
      idNumber : '',
      highlight_id : '',
      gender : '' ,
      phoneNumber : '',
      paginator : {},
      highlights: [],
      locale: Nova.config.local,
      customer_id_to_delete : null,
      customer_to_edit : null
    };
  },
  methods: {
    getCustomers(page=1) {
      this.isLoading = true;
      axios
        .get(`/nova-vendor/new/customers?page=${page}&name=${this.customer_name}&id_number=${this.idNumber}&phone=${this.phoneNumber}&highlight_id=${this.highlight_id}&gender=${this.gender}`)
        .then(response => {
          this.customers = response.data.data;
          this.paginator = {
                currentPage : response.data.current_page ,
                lastPage : response.data.last_page ,
                from : response.data.from,
                to : response.data.to,
                totalResults : response.data.total,
                pathPage : response.data.path + '?page=',
                firstPageUrl : response.data.first ,
                lastPageUrl : response.data.last ,
                nextPageUrl : response.data.next ,
                prevPageUrl : response.data.prev ,
          };
          this.isLoading = false;
        });
    },
    show(id) {
      axios
        .get(this.url + '/' + id)
        .then((response) => (this.customer = response.data.data));
    },
    update(id) {
      var req = { title: "Vue POST Request Example" };
      axios
        .put(this.url + '/' + id, req)
        .then((response) => (this.customer = response.data.data));
    },
    setCustomer(customer) {
      this.customer_to_edit = customer;
      setTimeout(() => {
          this.$refs.editCustomer.$refs.edit.open();
      }, 0);
    },
    setCustomerId(id) {
      this.customer_id_to_delete = id;
      this.$refs.deleteConfirm.$refs.delete.open();
    },
    resetFilters(){
        this.customer_name = '';
        this.idNumber = '';
        this.phoneNumber = '';
        this.highlight_id = '';
        this.gender = '';
        this.getCustomers();
    },

    getHighlights() {
            axios.get('/apidata/highlights')
                .then(response => {
                    this.highlights = response.data.data;
                })
    },
    getCountryFlag(code){
        axios.get(`https://www.countryflags.io/${code}/flat/64.png`)
    }
  },
  mounted() {
    this.getCustomers();
    this.getHighlights();
    this.crumbs = [
        {
            to : '/',
            text : 'Home'
        },
        {
            to : '/new/customers',
            text : 'Customers'
        }
    ];

    Nova.$on('call-customers-query' , () => {
        this.getCustomers();
    })
  },
};
</script>


<style lang="scss">

   .table_title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      span.table {
        display: block;
        font-size: 15px;
        margin: 0 0 15px;
        color: #000;
        p {
          display: inline-block;
          margin: 0 5px 0 0;
          font-weight: bold;
          font-size: 16px;
        } /* p */
      } /* span */
      button.table {
        display: block;
        background-color: #4099de;
        font-size: 15px;
        padding: 0 20px;
        height: 35px;
        border-radius: 4px !important;
        line-height: 35px;
        color: #fff;
        cursor: pointer;
        outline: none;
        font-weight: normal !important;
        margin: 0 0 15px;
        @media (min-width: 320px) and (max-width: 767px) {
          width: auto;
          &::before {display: none;}
        } /* media */
        &:hover {
          background-color: #0071C9;
        } /* hover */
      } /* a */
    } /* table_title */
    .customer-label {
        font-size: 14px;
        padding: 0 .5rem;
        border-radius: .25rem;
        min-width: 60px;
    }
.Edit_Transaction_Modal {
    .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {width: 6px;}
        &::-webkit-scrollbar-track {background: #f5f5f5;}
        &::-webkit-scrollbar-thumb {background: #ccc;}
        &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    } /* sweet-content */
    .formgroup {
        display: block;
        margin: 0 auto 10px;
    } /* formgroup */
    .row_group {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin: 0 -10px;
        @media (min-width: 320px) and (max-width: 767px) {
            margin: 0;
        } /* Mobile */
        .col {
            width: 50%;
            padding: 0 10px;
            margin: 0 0 10px;
            @media (min-width: 320px) and (max-width: 767px) {
                width: 100%;
                padding: 0;
            } /* Mobile */
        } /* col */
    } /* row_group */
    label {
        display: block;
        margin: 0 auto 5px;
        font-size: 15px;
        span {
            display: inline-block;
            margin: 0 5px 0 0;
            color: #f00;
            [dir="ltr"] & {
                margin: 0 0 0 5px;
            } /* ltr */
        } /* span */
    } /* label */
    input {
        height: 40px !important;
        padding: 0 10px !important;
        color: #000 !important;
        font-size: 15px !important;
        border: 1px solid #dddddd !important;
        background: #fafafa !important;
        width: 100% !important;
        &[readonly="readonly"] {
            cursor: pointer;
        } /* readonly */
    } /* input */
    select {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
        width: 100%;
        height: 40px !important;
        padding: 0 10px !important;
        background-color: #fafafa !important;
        border: 1px solid #ddd !important;
        color: #000;
        font-size: 15px;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        -webkit-appearance: none;
        -moz-appearance: none;
        -o-appearance: none;
        appearance: none;
        border-radius: 5px !important;
        background-position: 15px center;
        background-repeat: no-repeat;
        background-size: 14px;
    } /* select */
    button {
        background: #4099de;
        border-radius: 5px;
        border: 1px solid #4099de;
        min-width: 100px;
        height: 35px;
        line-height: 35px;
        font-size: 15px;
        padding: 0 15px;
        color: #ffffff;
        width: 100%;
        margin: 0 auto 10px;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        &:hover {
            background: #0071C9;
            border-color: #0071C9;
        } /* hover */
    } /* button */
} /* Edit_Transaction_Modal */
.Results_area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    p {
        display: block;
        margin: 10px 0 0;
        font-size: 15px;
        color: #000;
    } /* p */
} /* Results_area */
#customer_management_page {
    margin: 10px auto 0;
    border: 1px solid #ddd;
    border-radius: .5rem;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    .title{
        background: #f7fafc;
        border-bottom: 1px solid #ddd;
        padding: .75rem;
        color: #000;
        font-size: 1.125rem;
        display: block;
    } /* title */
    .content_page {
        background: #fff;
        padding: 10px;
        .filter_area {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin: 0 -10px;
            .item {
                width: 16.66666%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 50%;
                } /* media */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 33.33333%;
                } /* media */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 25%;
                } /* media */
                input {
                    background: #fafafa;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                } /* input */
                select {
                    background-color: #fafafa;
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                    background-repeat: no-repeat;
                    background-size: 14px;
                    background-position: 10px center;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                } /* select */
            } /* item */
            .reset_filters {
                width: 100%;
                display: flex;
                padding: 0 10px;
                justify-content: flex-end;
                button {
                    height: 40px;
                    width: 40px;
                    background-color: #718096;
                    border-radius: 4px;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: 20px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background-color: #5E6D83;
                    } /* hover */
                } /* button */
            } /* reset_filters */
        } /* filter_area */
        hr {
            margin: 15px auto;
            border-color: #ddd;
        } /* hr */
        .statistics_area {
            ul {
                display: flex;
                align-items: flex-start;
                justify-content: flex-start;
                flex-wrap: wrap;
                margin: 0 -10px;
                li {
                    width: 20%;
                    padding: 0 10px;
                    @media (min-width: 320px) and (max-width: 480px) {
                        width: 50%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 481px) and (max-width: 767px) {
                        width: 33.33333%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 768px) and (max-width: 991px) {
                        width: 25%;
                        margin: 5px 0;
                    } /* media */
                    span {
                        display: block;
                        font-size: 15px;
                        color: #000;
                        margin: 0 0 5px;
                    } /* span */
                    p {
                        display: block;
                        font-size: 16px;
                        font-weight: bold;
                        line-height: 1.2;
                        &.totalDebtor {
                            color: #f56565;
                        } /* totalDebtor */
                        &.totalCreditor {
                            color: #48bb78;
                        } /* totalCreditor */
                    } /* p */
                } /* li */
            } /* ul */
        } /* statistics_area */
        .action_buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin: 0 0 10px;
            button.add_receipts {
                display: block;
                background: #4099de;
                border: none;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                padding: 5px 15px;
                &:hover {
                    background: #0071C9;
                } /* hover */
            } /* add_receipts */
            .buttons_area {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                button {
                    display: block;
                    height: 30px;
                    width: 30px;
                    margin: 0 10px 0 0;
                    outline: none;
                    background-position: center center;
                    background-size: 25px;
                    background-repeat: no-repeat;
                    &.excel_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* excel_button */
                    &.print_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* print_button */
                } /* button */
            } /* buttons_area */
        } /* action_buttons */
        .table_area {
            .no_data_show {
                text-align: center;
                padding: 50px 15px 40px;
                svg {
                    display: block;
                    margin: 0 auto 15px;
                } /* svg */
                span {
                    display: block;
                    font-size: 15px;
                    text-align: center;
                    color: #000;
                } /* span */
            } /* no_data_show */
            .table-responsive {
                width: 100%;
                margin: 0 auto 20px;
                position: relative;
                @media (min-width: 320px) and (max-width: 991px) {
                    overflow: auto;
                } /* media */
                table {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    display: table;
                    thead {
                        tr {
                            th {
                                padding: 10px 5px;
                                line-height: 20px;
                                font-weight: normal;
                                font-size: 15px;
                                border: 1px solid #5E697C;
                                vertical-align: middle;
                                text-align: center !important;
                                color: #ffffff;
                                background: #4a5568;
                            } /* th */
                        } /* tr */
                    } /* thead */
                    tbody {
                        tr {
                            td {
                                text-align: center !important;
                                // padding: 15px 5px;
                                vertical-align: middle;
                                line-height: 10px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                // height: auto;
                                background: #ffffff;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                    a, button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
                                        outline: none;
                                        svg {
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {fill: #3d92d4;}
                                            }
                                        }
                                        &:hover {color: #3d92d4;}
                                    } /* a */
                                } /* td-fit */
                                .text-left {
                                    text-align: center !important;
                                } /* text-left */
                            } /* td */
                        } /* tr */
                    } /* tbody */
                } /* table */
            } /* table-responsive */
        } /* table_area */
    } /* content_page */
} /* customer_management_page */
</style>

