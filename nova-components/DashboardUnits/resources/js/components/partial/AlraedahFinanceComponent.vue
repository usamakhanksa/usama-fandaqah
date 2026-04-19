<template>
  <div class="base">

    <div class="flex w-full mb-4">
        <nav>
            <ul class="breadcrumbs">
                <li class="breadcrumbs__item">
                    <router-link to="/">{{ __('Home') }}</router-link>
                </li>
                <li class="breadcrumbs__item">
                    <router-link to="/settings">{{ __('Settings') }}</router-link>
                </li>
                <li class="breadcrumbs__item">
                    <router-link to="/settings/integrations">{{ __('Integration Settings') }}</router-link>
                </li>
                <li class="breadcrumbs__item">
                    <router-link to="#">{{ __('Alraedah Finance') }}</router-link>
                </li>
            </ul>
        </nav>
    </div>
      <div class="prospect_form relative card p-3">
                <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :is-full-page="false"></loading>

                <div class="previous_request_message" role="alert" v-if="isThereAnyProspect">
                        <p>{{ __('You have an old request submitted at')}} <span class="make_it_bolder">{{oldProspectDate}}</span> </p>
                        <p v-if="oldProspectStatus == 'pending'">{{ __('Request status is')}} <span class="make_it_bolder" :class="oldProspectStatus == 'pending' ? 'pending' : ''">{{__('Request is pending')}}</span></p>
                        <p v-if="oldProspectStatus == 'pending'">{{__('You still can update this request while the status is pending')}}</p>
                        <p v-if="oldProspectStatus == 'approved'">{{ __('Request status is')}} <span class="make_it_bolder" :class="oldProspectStatus == 'approved' ? 'approved' : ''" >{{__('Request approved from fandaqah - and we sent it to alraedah')}}</span></p>
                        <p v-if="oldProspectStatus == 'rejected'">{{ __('Request status is')}} <span class="make_it_bolder" :class="oldProspectStatus == 'rejected' ? 'rejected' : ''" >{{__("Request rejected from fandaqah - and we didn'\t send it to alraedah")}}</span></p>
                        <p v-if="oldProspectStatus == 'rejected' && oldProspectRejectReason">{{__('Rejected Reason')}} : <span> {{ oldProspectRejectReason }} </span></p>
                        <p v-if="oldProspectStatus == 'approved' && oldProspectResponse">{{__('ALRaedah Prospect ID')}} : <span> {{ oldProspectResponse.data.key }} </span></p>
                </div>


                <div class="row_group">
                    <div class="col">
                        <label>{{__('Tenant Name')}}<i>*</i></label>
                        <input type="text" v-model="prospectInfo.tenant_name" :placeholder="__('Tenant Name')">
                    </div><!-- name col -->
                    <div class="col">
                        <label>{{__('Full Name')}}<i>*</i></label>
                        <input type="text" v-model="prospectInfo.full_name" :placeholder="__('Full Name')">
                    </div><!-- name col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Phone Number')}}<i>*</i></label>
                        <!-- <vue-tel-input
                                defaultCountry="SA"
                                :required="true"
                                :enabledFlags="true"
                                name="phone"
                                :placeholder="__('Phone Number')"
                                :inputOptions="{ showDialCode: false, tabindex: 0 }"
                                v-model="prospectInfo.phone"
                        >
                        </vue-tel-input> -->
                        <input type="text" v-model="prospectInfo.phone" :placeholder="__('Phone Number')">
                    </div><!-- phone col -->
                    <div class="col">
                        <label>{{__('Email address')}}<i>*</i></label>
                        <input type="email" v-model="prospectInfo.email" :placeholder="__('Email address')">
                    </div><!-- email address col -->
                </div>


                <div class="row_group">
                    <div class="col">
                        <div class="radios_area">
                            <p>{{__('Do you have a Valid Commercial Registration for your Company?')}}</p>
                            <div class="radios_group">
                                <label for="value1">
                                    <input type="radio" value="1" v-model="prospectInfo.tenant_has_valid_commercial_registration" id="value1" name="group1">
                                    <div class="checkmark"></div>
                                    <span>نعم</span>
                                </label>
                                <label for="value2">
                                    <input type="radio" value="0" v-model="prospectInfo.tenant_has_valid_commercial_registration" id="value2" name="group1">
                                    <div class="checkmark"></div>
                                    <span>لا</span>
                                </label>
                            </div>
                        </div>
                    </div><!-- phone col -->
                    <div class="col">
                        <div class="radios_area">
                            <p>{{__('Does your company receive payments (mada, visa, master card)?')}}</p>
                            <div class="radios_group">
                                <label for="value3">
                                    <input type="radio" value="1" v-model="prospectInfo.tenant_has_receive_payments" id="value3" name="group2">
                                    <div class="checkmark"></div>
                                    <span>نعم</span>
                                </label>
                                <label for="value4">
                                    <input type="radio" value="0" v-model="prospectInfo.tenant_has_receive_payments" id="value4" name="group2">
                                    <div class="checkmark"></div>
                                    <span>لا</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Good time to contact')}}</label>
                        <select v-model="prospectInfo.appropriate_contact_time">
                            <option value="8am - 10am">8am - 10am</option>
                            <option value="10am - 12pm">10am - 12pm</option>
                            <option value="12pm - 02pm">12pm - 02pm</option>
                            <option value="02pm - 04pm">02pm - 04pm</option>
                            <option value="04pm - 06pm">04pm - 06pm</option>
                            <option value="06pm - 08pm">06pm - 08pm</option>
                        </select>
                    </div><!-- phone col -->

                </div>
            <div class="action_buttons" v-if="!isThereAnyProspect || oldProspectStatus == 'pending'">
                <button class="send" @click="sendData">{{__('Send')}}</button>
            </div><!-- action_buttons -->
            </div><!-- prospect_form -->

  </div>

</template>

<script>

import momenttimezone from 'moment-timezone';
import Loading from 'vue-loading-overlay';
    export default {
        name: "alraedah-finance-component",
        components: {
            Loading
        },
        data: () => {
            return {
                isLoading: false,
                prospectInfo : {
                    tenant_name: Nova.app.currentTeam.name,
                    full_name: Nova.app.currentTeam.owner.name,
                    phone: Nova.app.currentTeam.owner.phone,
                    email: Nova.app.currentTeam.owner.email,
                    tenant_has_valid_commercial_registration: 0,
                    tenant_has_receive_payments: 0,
                    appropriate_contact_time: null,
                    share_data : 0
                },
                current_team_id : Nova.app.user.current_team_id,
                isThereAnyProspect : false,
                oldProspectDate: null,
                oldProspectStatus : null

            }
        },
        methods: {

            sendData(){

                this.isLoading = true;
                if(!this.prospectInfo.tenant_name
                    || !this.prospectInfo.full_name
                    || !this.prospectInfo.phone
                    || !this.prospectInfo.email
                ){
                    this.$toasted.show(this.__('Please fill in required information'), {type: 'error'});
                    this.isLoading = false;
                    return;
                }

               axios.post('/nova-vendor/DashboardUnits/createProspect', {
                   prospectInfo : this.prospectInfo,
                   current_team_id : this.current_team_id
               })
               .then(response => {
                   if(response.data.success)
                   {
                      this.isThereAnyProspect = true;
                      this.oldProspectDate = momenttimezone(response.data.prospectObj.created_at).format('YYYY/MM/DD hh:mm A');
                      this.oldProspectStatus = response.data.prospectObj.status;
                      this.prospectInfo = response.data.prospectObj.data;
                      this.$toasted.show(this.__('Prospect has been sent successfully'), {type: 'success'});

                   }
                   this.isLoading = false;
               })
               .catch(error => {
                    if(error && error.response && error.response.status == 422){
                        if(error.response.data.errors['prospectInfo.email']){
                            this.$toasted.show(error.response.data.errors['prospectInfo.email'][0], {type: 'error'});
                        }

                        if(error.response.data.errors['prospectInfo.phone']){
                            this.$toasted.show(this.__('Phone is not valid it should match this formula : 0512345678'), {type: 'error'});
                        }
                    }
                    this.isLoading = false;
               })
            },
            getOldRequest(){
                this.isLoading = true;
                axios.get(`/nova-vendor/DashboardUnits/getOldProspect?current_team_id=${this.current_team_id}`)
                .then(response => {

                    if(Object.keys(response.data) != 0){
                        this.isThereAnyProspect = true;
                        this.prospectInfo =  {
                            tenant_name: response.data.data.tenant_name,
                            full_name: response.data.data.full_name,
                            phone: response.data.data.phone,
                            email: response.data.data.email,
                            tenant_has_valid_commercial_registration: response.data.data.tenant_has_valid_commercial_registration,
                            tenant_has_receive_payments: response.data.data.tenant_has_receive_payments,
                            appropriate_contact_time: response.data.data.appropriate_contact_time,
                            share_data : response.data.data.share_data,
                        };

                        this.oldProspectDate = momenttimezone(response.data.created_at).format('YYYY/MM/DD hh:mm A');
                        this.oldProspectStatus = response.data.status;
                        this.oldProspectRejectReason = response.data.reject_reason;
                        this.oldProspectResponse = response.data.response;
                    }else{
                        this.isThereAnyProspect = false;
                    }

                    this.isLoading = false;
                })
            }

        },
        mounted(){
            this.getOldRequest();
        }
    }
</script>

<style lang="scss">

    @import '~vue-tel-input/dist/vue-tel-input.css';
     .prospect_form {
        .formgroup {
            display: block;
            margin: 0 auto 10px;

            .autocomplete__box {
                border: 1px solid #ddd !important;
                background: #fafafa;
                color: #000;
                height: 40px;
                padding: 0 10px;
                box-shadow: none !important;
                border-radius: 5px;

                input {
                    border: none !important;
                    height: 38px !important;
                    border-radius: 0 !important;
                    background: transparent !important;
                }



                /* input */
            }

            /* autocomplete__box */
            ul.autocomplete__results {
                border: 1px solid #ddd;
                border-radius: 0 0 5px 5px;
                margin: -3px 0 0 0;
                background: #f5f5f5;

                li.autocomplete__results__item {
                    display: block;
                    color: #000;
                    font-size: 15px;

                    &:hover {
                        background: #f0f0f0;
                    }
                }

                /* autocomplete__results__item */
            }

            /* autocomplete__results */
        }

        /* formgroup */
        .loader_item {
            padding: 50px;
        }

        /* loader_item */
        .customerNotes {
            span {
                margin: 0 auto 15px;
                border-radius: 5px;
                padding: 10px;
                text-align: center;
                color: #b7791f;
                border: 1px solid #fbd38d;
                background: #fffaf0;
                font-size: 15px;
                cursor: pointer;
                display: block;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;

                &:hover {
                    background: #faf5eb;
                    border-color: #f6ce88;
                    color: #b2741a;
                }

                /* hover */
            }

            /* span */
        }

        /* customerNotes */
        .customer_notes_modal {
            .sweet-content {
                max-height: 500px;
                overflow-y: auto;
                display: block !important;
                scrollbar-width: thin;
                scrollbar-color: #ccc #f5f5f5;

                &::-webkit-scrollbar {
                    width: 6px;
                }

                &::-webkit-scrollbar-track {
                    background: #f5f5f5;
                }

                &::-webkit-scrollbar-thumb {
                    background: #ccc;
                }

                &::-webkit-scrollbar-thumb:window-inactive {
                    background: #f5f5f5;
                }
            }

            /* sweet-content */
            .note_item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                flex-wrap: wrap;
                border: 1px solid #ddd;
                margin: 0 auto 10px;
                border-radius: 5px;
                padding: 5px;
                background: #fdfdfd;

                .desc {
                    display: block;
                    white-space: pre-line;
                    font-size: 15px;
                    color: #000;
                    margin: 0 0 10px;
                }

                /* desc */
                .user_info {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    width: 100%;

                    .user_account {
                        display: flex;
                        align-items: center;
                        justify-content: flex-start;
                        font-size: 15px;
                        color: #666666;

                        img {
                            display: block;
                            width: 35px;
                            height: 35px;
                            border-radius: 100%;
                            margin: 0 0 0 5px;
                            border: 1px solid #ddd;

                            [dir="ltr"] & {
                                margin: 0 5px 0 0;
                            }

                            /* ltr */
                        }

                        /* img */
                    }

                    /* user_account */
                    time {
                        display: block;
                        font-size: 13px;
                        color: #777777;
                    }

                    /* time */
                }

                /* user_info */
            }

            /* note_item */
        }

        /* customer_notes_modal */
        .row_group {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            margin: 0 -10px;
            @media (min-width: 320px) and (max-width: 767px) {
                margin: 0;
            }

            .id_expire{
                width: 100%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    padding: 0;
                }
            }

            .address{
                width: 100%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    padding: 0;
                }
            }
            /* Mobile */
            .col {
                width: 50%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    padding: 0;
                }
                /* Mobile */
                .vue-tel-input {
                    display: flex;
                    width: 100%;
                    height: 40px;
                    background: #fafafa;
                    border: 1px solid #dddddd !important;
                    line-height: 40px;
                    font-size: 15px;
                    color: #000;
                    border-radius: 4px;
                    padding: 0;
                    text-align: right;
                    align-items: center;
                    box-shadow: none;

                    [dir="ltr"] & {
                        text-align: left;
                    }

                    /* rtl */
                    .dropdown {
                        padding: 0;
                        width: 70px;
                        background: #f5f5f5;
                        height: 38px;
                        border-left: 1px solid #dddddd;
                        border-radius: 0 4px 4px 0;

                        [dir="ltr"] & {
                            border-right: 1px solid #dddddd;
                            border-left: none;
                            border-radius: 4px 0 0 4px;
                        }

                        /* rtl */
                        span.selection {
                            display: flex !important;
                            height: 40px;
                            justify-content: center;
                            align-items: center;
                            width: auto;
                            margin: 0 auto;
                            font-size: 12px !important;

                            .iti-flag {
                                margin: 0;
                            }

                            /* iti-flag */
                            span.dropdown-arrow {
                                width: auto;
                                margin: 0 5px 0 0;
                                display: inline-block !important;
                                font-size: inherit !important;

                                [dir="ltr"] & {
                                    margin: 0 0 0 5px;
                                }

                                /* ltr */
                            }

                            /* dropdown-arrow */
                        }

                        /* selection */
                        ul {
                            margin: 0 auto;
                            left: auto;
                            right: 0;
                            width: auto;
                            min-width: 210px;
                            top: 43px;
                            max-width: 386px;
                            border-radius: 4px;
                            text-align: inherit;
                            scrollbar-width: thin;
                            scrollbar-color: #ccc #f5f5f5;

                            &::-webkit-scrollbar {
                                width: 6px;
                            }

                            &::-webkit-scrollbar-track {
                                background: #f5f5f5;
                            }

                            &::-webkit-scrollbar-thumb {
                                background: #ccc;
                            }

                            &::-webkit-scrollbar-thumb:window-inactive {
                                background: #f5f5f5;
                            }

                            [dir="ltr"] & {
                                left: 0;
                                right: auto;
                            }

                            /* rtl */
                            li {
                                direction: rtl;
                                display: flex;
                                align-items: center;
                                justify-content: flex-start;
                                padding: 3px 10px;
                                line-height: normal;
                                font-weight: normal;
                                color: #000;

                                [dir="ltr"] & {
                                    direction: ltr;
                                }

                                /* ltr */
                                .iti-flag {
                                    margin: 0;
                                }

                                /* iti-flag */
                                strong {
                                    display: block;
                                    font-weight: normal;
                                    font-size: 15px;
                                    margin: 0 5px;
                                }

                                /* strong */
                                span {
                                    direction: ltr;
                                    color: #666 !important;
                                    font-size: inherit !important;
                                    margin: 0;
                                }

                                /* span */
                            }

                            /* li */
                        }

                        /* ul */
                    }

                    /* dropdown */
                    input {
                        width: 76%;
                        border-radius: 0 !important;
                        height: 38px !important;
                        border: none !important;
                        padding: 0 10px 0 0 !important;

                        [dir="ltr"] & {
                            padding: 0 0 0 10px !important;
                        }

                        /* ltr */
                    }

                    /* input */
                }

                /* vue-tel-input */
            }


            /* col */
        }

        /* row_group */
        label {
            display: block;
            margin: 0 auto 5px;
            font-size: 15px;

            i {
                display: inline-block !important;
                margin: 0 5px 0 0;
                color: #f00 !important;
                font-style: normal;
            }

            /* i */
        }

        /* label */
        input {
            height: 40px !important;
            padding: 0 10px !important;
            color: #000 !important;
            font-size: 15px !important;
            border: 1px solid #dddddd !important;
            background: #fafafa !important;
            width: 100%;

            &[readonly="readonly"] {
                cursor: pointer;
            }

            /* readonly */
            &.customer_search {
                background: transparent !important;
                border: none !important;
                height: 40px !important;
                border-radius: 0 !important;
                padding: 0 10px !important;
                display: block;
            }

            /* customer_search */
        }

        /* input */
        label.customer_highlight {
            height: 40px;
            border-radius: 4px;
            text-align: center;
            font-size: 15px;
            line-height: 40px;
            color: #000;
            margin: 0 auto;
        }

        /* customer_highlight */
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

            [dir="ltr"] & {
                background-position: 97% center;
            }

            /* ltr */
        }

        /* select */
    }

    .action_buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin: 10px 0 10px;
        } /* action_buttons */

.make_it_bolder{
    font-weight: bold;
}

.approved {
    color: #016d32;
}
.rejected {
    color: #ff0000bf;
}
.pending {
    color: #4599dd
}
button.send {
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
    @media (min-width: 320px) and (max-width: 767px) {
        width: auto;
        &::before {display: none;}
    } /* media */
    &:hover {
        background-color: #0071C9;
    } /* hover */
} /* a */

.radios_area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    p {
        display: block;
        font-size: 16px;
        color: #000;
    }
    .radios_group {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        label {
            display: flex !important;
            align-items: center;
            justify-content: flex-start;
            margin: 0 1rem 0 0 !important;
            position: relative;
            input {
                cursor: pointer;
                position: absolute;
                right: 0;
                top: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                z-index: 99;
                &:checked ~ {
                    .checkmark {
                        border-color: #297ec0;
                        &::after {
                            opacity: 1;
                        }
                    }
                }
            }
            .checkmark {
                width: 20px;
                height: 20px;
                border: 1px solid #dee2e6;
                border-radius: 100%;
                margin: 0 0 0 .25rem;
                box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
                &::after {
                    content: "";
                    position: absolute;
                    right: 4px;
                    top: 5px;
                    width: 12px;
                    height: 12px;
                    background-color: #297ec0;
                    border-radius: 100%;
                    opacity: 0;
                }
            }
        }
    }
}
/* Mobile */
                            .vue-tel-input {
                                display: flex;
                                width: 100%;
                                height: 40px;
                                background: #fafafa;
                                border: 1px solid #dddddd !important;
                                line-height: 40px;
                                font-size: 15px;
                                color: #000;
                                border-radius: 4px;
                                padding: 0;
                                text-align: right;
                                align-items: center;
                                box-shadow: none;

                                [dir="ltr"] & {
                                    text-align: left;
                                }

                                /* rtl */
                                .dropdown {
                                    padding: 0;
                                    width: 70px;
                                    background: #f5f5f5;
                                    height: 38px;
                                    border-left: 1px solid #dddddd;
                                    border-radius: 0 4px 4px 0;

                                    [dir="ltr"] & {
                                        border-right: 1px solid #dddddd;
                                        border-left: none;
                                        border-radius: 4px 0 0 4px;
                                    }

                                    /* rtl */
                                    span.selection {
                                        display: flex !important;
                                        height: 40px;
                                        justify-content: center;
                                        align-items: center;
                                        width: auto;
                                        margin: 0 auto;
                                        font-size: 12px !important;

                                        .iti-flag {
                                            margin: 0;
                                        }

                                        /* iti-flag */
                                        span.dropdown-arrow {
                                            width: auto;
                                            margin: 0 5px 0 0;
                                            display: inline-block !important;
                                            font-size: inherit !important;

                                            [dir="ltr"] & {
                                                margin: 0 0 0 5px;
                                            }

                                            /* ltr */
                                        }

                                        /* dropdown-arrow */
                                    }

                                    /* selection */
                                    ul {
                                        margin: 0 auto;
                                        left: auto;
                                        right: 0;
                                        width: auto;
                                        min-width: 210px;
                                        top: 43px;
                                        max-width: 386px;
                                        border-radius: 4px;
                                        text-align: inherit;
                                        scrollbar-width: thin;
                                        scrollbar-color: #ccc #f5f5f5;

                                        &::-webkit-scrollbar {
                                            width: 6px;
                                        }

                                        &::-webkit-scrollbar-track {
                                            background: #f5f5f5;
                                        }

                                        &::-webkit-scrollbar-thumb {
                                            background: #ccc;
                                        }

                                        &::-webkit-scrollbar-thumb:window-inactive {
                                            background: #f5f5f5;
                                        }

                                        [dir="ltr"] & {
                                            left: 0;
                                            right: auto;
                                        }

                                        /* rtl */
                                        li {
                                            direction: rtl;
                                            display: flex;
                                            align-items: center;
                                            justify-content: flex-start;
                                            padding: 3px 10px;
                                            line-height: normal;
                                            font-weight: normal;
                                            color: #000;

                                            [dir="ltr"] & {
                                                direction: ltr;
                                            }

                                            /* ltr */
                                            .iti-flag {
                                                margin: 0;
                                            }

                                            /* iti-flag */
                                            strong {
                                                display: block;
                                                font-weight: normal;
                                                font-size: 15px;
                                                margin: 0 5px;
                                            }

                                            /* strong */
                                            span {
                                                direction: ltr;
                                                color: #666 !important;
                                                font-size: inherit !important;
                                            }

                                            /* span */
                                        }

                                        /* li */
                                    }

                                    /* ul */
                                }

                                /* dropdown */
                                input {
                                    width: 76%;
                                    border-radius: 0 !important;
                                    height: 38px !important;
                                    border: none !important;
                                    padding: 0 10px 0 0 !important;

                                    [dir="ltr"] & {
                                        padding: 0 0 0 10px !important;
                                    }

                                    /* ltr */
                                }

                                /* input */
                            }

.previous_request_message {
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
} /* previous_request_message */
</style>
