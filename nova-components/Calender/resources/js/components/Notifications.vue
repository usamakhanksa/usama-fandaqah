<template>
    <div>
        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                <li
                    class="breadcrumbs__item"
                    v-for="(crumb,i) in crumbs"
                    :key="i"
                >
                    <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                </li>
                </ul>
            </nav>
        </div>
        <div id="deposit_management_page">
            <div class="title">{{ __("PMS Notifications") }}</div>
            <div class="content_page">
                

                <!-- Filters -->
                <div class="filter_area">
                
                    <div class="item">
                        <select
                        v-model="notification_is_read_filter"
                        >
                        <option value="unread" :selected="true">
                            {{ __("Unread") }}
                        </option>
                        <option value="read">
                            {{ __("Read") }}
                        </option>

                        </select>
                    </div>

                    <!-- item -->
                    <div class="reset_filters">
                        <button
                        @click="resetFilters"
                        v-tooltip="{
                            targetClasses: ['it-has-a-tooltip'],
                            placement: 'top',
                            content: __('Reset Filters'),
                            classes: ['tooltip_reset'],
                        }"
                        ></button>
                    </div>
       
                </div>
                <!-- Filters Area -->

                <!-- Table Listing Area -->
                <div class="table_area">
                    <div class="table-responsive relative">
                        <loading
                            :active="isLoading"
                            :loader="'spinner'"
                            :color="'#7e7d7f'"
                            :opacity="0.7"
                           
                            :is-full-page="false"
                        ></loading>
                        <table
                            class="table w-full"
                            cellpadding="0"
                            cellspacing="0"
                        >
                            <thead>
                                <tr>
                                    <!-- <th>#</th> -->
                                    <th>{{ __("The Source") }}</th>
                                    <th>{{ __("Subtype") }}</th>
                                    <th>{{ __("Room Number") }}</th>
                                    <th>{{ __("Guest Name") }}</th>
                                    <th>{{ __("Request Date") }}</th>
                                    <th>{{ __("Scheduled At") }}</th>
                                    <th>{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="data.length">
                                    <tr v-for="(notification,i) in data" :key="i">
                                       
                                        <!-- <td>{{ notification.id }}</td> -->
                                        <td>{{ __(notification.type) }}</td>
                                        <td> 
                                            <p v-if="notification.subtype" class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-900 dark:text-gray-300">{{ __(notification.subtype) }}</p>
                                        </td>
                                        <td>{{ notification.room_number }}</td>
                                        <td>{{ notification.guest_name }}</td>
                                        <td>{{ notification.created_at | formatDateWithAmPm }}</td>
                                        <td v-if="notification.subtype == 'wakeup_call'">
                                            <p v-if="notification.scheduled_at" class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">{{ notification.scheduled_at | formatDateWithAmPm }}</p>
                                            <p v-else>-</p>
                                        </td>
                                        <td v-else>
                                           -
                                        </td>
                                       
                                        <td>
                                            
                                            <button v-if="notification && parseInt(notification.requires_user_action) && notification_is_read_filter == 'unread'" type="button" @click="openTreatConfirm(notification)" :title="__('Treat Request')" class="appearance-none cursor-pointer text-70 hover:text-primary mx-2">
                                                <svg height="20px" width="20px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 344.963 344.963" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path style="fill:#010002;" d="M321.847,86.242l-40.026-23.11l-23.104-40.02h-46.213l-40.026-23.11l-40.026,23.11H86.239 l-23.11,40.026L23.11,86.242v46.213L0,172.481l23.11,40.026v46.213l40.026,23.11l23.11,40.026h46.213l40.02,23.104l40.026-23.11 h46.213l23.11-40.026l40.026-23.11v-46.213l23.11-40.026l-23.11-40.026V86.242H321.847z M156.911,243.075 c-3.216,3.216-7.453,4.779-11.671,4.72c-4.219,0.06-8.455-1.504-11.671-4.72l-50.444-50.444c-6.319-6.319-6.319-16.57,0-22.889 l13.354-13.354c6.319-6.319,16.57-6.319,22.889,0l25.872,25.872l80.344-80.35c6.319-6.319,16.57-6.319,22.889,0l13.354,13.354 c6.319,6.319,6.319,16.57,0,22.889L156.911,243.075z"></path> </g> </g></svg>
                                            </button>
                                            <p v-else>-</p>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            {{ __("No New Notifications Found") }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div
                        class="w-full flex flex-wrap mt-3 justify-center"
                        v-if="data.length"
                    >
                        <pagination
                            v-if="paginator.total > per_page"
                            :page-count="paginator.lastPage"
                            :page-range="3"
                            :margin-pages="2"
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
                            @input="getCurrentPage($event)"
                        />
                    </div>
                    <!-- Pagination -->
                    <div class="Results_area" v-if="data.length">
                        <p>
                            {{ __("Results") }} : {{ __("From") }} (
                            {{ paginator.from }} ) - {{ __("To") }} (
                            {{ paginator.to }} )
                        </p>
                        <p>{{ __("Count") }} : {{ paginator.total }}</p>
                    </div>
                    <!-- Results_area -->
                </div>
            </div>
        </div>

    <!-- Treat IPTV Request Modal -->
    <sweet-modal
    :enable-mobile-fullscreen="false"
    :pulse-on-block="false"
    :hide-close-button="true"
    overlay-theme="dark"
    ref="treatNotificationModal"
    class="treat-modal"
    >
    <div class="treat-modal-content">
        <loading :active.sync="isLoadingTreat" :is-full-page="false" />

        <h2 class="treat-modal-title">
        {{ __("Are you sure you want to approve this request ?") }}
        </h2>

        <div class="treat-modal-buttons">
        <button
            @click="fulfillRequest"
            class="treat-button confirm"
        >
            {{ __("Yes") }}
        </button>
        <button
            @click="stepBack"
            class="treat-button cancel"
        >
            {{ __("No") }}
        </button>
        </div>
    </div>
    </sweet-modal>


    </div>
</template>

<script>
import "../airbnb-modified.css";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import Pagination from "./Pagination";
import BreadCrumb from "./BreadCrumb";
export default {
    name: "notifications",
    components: {
        BreadCrumb,
        Loading,
        Pagination,
    },
    data() {
        return {
            data: [],
            paginator: {},
            crumbs: [],
            selectedPage: 1,
            team_id: Nova.config.user.current_team_id,
            team: Nova.app.currentTeam,
            locale : Nova.app.local,
            per_page : 20,
            isLoadingTreat : false,
            notification_is_read_filter : 'unread'
        };
    },
   
    methods: {
        resetFilters() {
            this.notification_is_read_filter = 'unread';
            this.getData();
        },
        getData() {
            this.isLoading = true;
   
            let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale,
                    'Authorization' : `Bearer ${window.FANDAQAH_API_V2_AUTHORIZATION_BEARER}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                params : this.$route.query
            };
           
            axios.get(window.FANDAQAH_API_NOTIFICATIONS_URL + `/list` , config)
                .then((response) => {
                    
                 
                    this.data = response.data.data.data;
                    this.paginator = {
                        currentPage: response.data.data.current_page || null,
                        lastPage: response.data.data.last_page || null,
                        from: response.data.data.from || null,
                        to: response.data.data.to || null,
                        total: response.data.data.total || null,
                        pathPage: response.data.data.path + "?page=" || null,
                        firstPageUrl: response.data.data.first_page_url || null,
                        lastPageUrl: response.data.data.last_page_url || null,
                        nextPageUrl: response.data.data.next_page_url || null,
                        prevPageUrl: response.data.data.prev_page_url || null,
                    };
                    this.isLoading = false;
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        getCurrentPage(page) {
            this.selectedPage = page;
        },
        openTreatConfirm(notification) {
            this.target_notification_id = notification.id;
            this.$refs.treatNotificationModal.open();
        },
        stepBack() {
            this.$refs.treatNotificationModal.close();
        },
        fulfillRequest(){
            let config = {
                headers : {
                    'x-team' : this.team_id,
                    'x-localization' : this.locale,
                    'Authorization' : `Bearer ${window.FANDAQAH_API_V2_AUTHORIZATION_BEARER}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                params : this.$route.query
            };
            this.isLoadingTreat = true;
            axios 
            .post(window.FANDAQAH_API_NOTIFICATIONS_URL + `/treat-notification?id=${this.target_notification_id}`,null,config)
            .then(response => {
                this.isLoadingTreat = false;

                if(response.data.success){
                    this.$refs.treatNotificationModal.close();
                    this.$toasted.show(this.__("Request fulfilled successfully"), {
                        type: "success",
                    });

                    this.getData();
                }else{
                    this.$toasted.show(response.data.error, {
                        type: "error"
                    });
                    return;
                }

              
            })
        },
        fetchNewNotifications(){
            this.getData();
        }
    },
    watch: {
        notification_is_read_filter: function (val) {
            if(val != null){
                let opt = {}
                opt["read_status"] = val;
                opt["page"] = 1;
                this.$router.push({
                    name : 'notifications',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }

            if(val == null){
                let opt = {}
                opt["page"] = 1;
                this.$router.push({
                    name : 'notifications',
                    query: Object.assign({}, this.$route.query, opt)
                } , () => {
                    this.getData();
                })
            }
        },
        selectedPage: function (val) {
            if (val) {
                let opt = {};
                opt["page"] = val;
                this.$router.push(
                    {
                        name: "notifications",
                        query: Object.assign({}, this.$route.query, opt),
                    },
                    () => {
                        this.getData();
                    }
                );
            }
        },
    },
   
    mounted() {

        window.addEventListener('pms-notification-received', this.fetchNewNotifications);

        this.crumbs = [
            {
                text: "Home",
                to: "/dashboards/main",
            },

            {
                text: "PMS Notifications",
                to: "#",
            },
        ];

        this.getData();
        

       
    },
};
</script>

<style lang="scss">

#deposit_management_page {
    margin: 10px auto 0;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    .title {
        background: #f7fafc;
        border-bottom: 1px solid #ddd;
        padding: 0.75rem;
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
                        background-color: #5e6d83;
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
            .mx-w-30px {
                max-width: 30px;
            }
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
                    background: #0071c9;
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
                                border: 1px solid #5e697c;
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
                                padding: 15px 5px;
                                vertical-align: middle;
                                line-height: 20px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                height: auto;
                                background: #ffffff;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                    a,
                                    button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
                                        outline: none;
                                        svg {
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {
                                                    fill: #3d92d4;
                                                }
                                            }
                                        }
                                        &:hover {
                                            color: #3d92d4;
                                        }
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
} /* deposit_management_page */
.line-break-anywhere {
    line-break: anywhere;
}
.text-xss {
        font-size: 11px;
    }

    .treat-modal .treat-modal-content {
  padding: 2rem;
  background-color: white;
  border-radius: 12px;
  text-align: center;
  max-width: 400px;
  margin: 0 auto;
}

.treat-modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 1.5rem;
}

.treat-modal-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
}

.treat-button {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  min-width: 100px;
  font-size: 1rem;
  transition: background-color 0.2s ease;
  border: none;
  cursor: pointer;
}

.treat-button.confirm {
  background-color: #15803d;
  color: white;
}

.treat-button.confirm:hover {
  background-color: #166534;
}

.treat-button.cancel {
  background-color: #e5e7eb;
  color: #374151;
}

.treat-button.cancel:hover {
  background-color: #d1d5db;
}

</style>
