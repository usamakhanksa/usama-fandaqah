<template>
    <div>

        <bread-crumb />
        <heading class="mb-6">{{__('Notifications Settings Control')}}</heading>

        <div id="notification_page" class="relative">
            <!-- Loader -->
            <loading :active="isActive"
                     :loader="'spinner'"
                     :color="'#7e7d7f'"
                     :opacity="1.0"
                     :is-full-page="false">
            </loading>
            <div class="block">
                <div class="title">{{__('Management alerts')}}</div>
                <div class="content">

                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Send an alert when a New Reservation')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_reservation_added.value.email" v-model="settings.alert_reservation_added.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_reservation_added.value.sms" v-model="settings.alert_reservation_added.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div><!-- end top_row -->

                    </div><!-- end col_item -->
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Send an alert when a Reservation Delete')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_reservation_deleted.value.email"  v-model="settings.alert_reservation_deleted.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_reservation_deleted.value.sms" v-model="settings.alert_reservation_deleted.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div><!-- end top_row -->
                    </div><!-- end col_item -->
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Send an alert when a Reservation Cancel')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_reservation_canceled.value.email"  v-model="settings.alert_reservation_canceled.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_reservation_canceled.value.sms"  v-model="settings.alert_reservation_canceled.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div><!-- end top_row -->
                    </div><!-- end col_item -->
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Daily brief report')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_daily_report.value.email"  v-model="settings.alert_daily_report.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_daily_report.value.sms"  v-model="settings.alert_daily_report.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div>
                    </div> 



                    <!-- Email -->
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Email him to notify him of alerts')}}</span>
                            <div class="add_area">
                                <div class="col_add">
                                    <input type="email" :placeholder="__('Email')" v-model="emailInputValue">
                                    <button type="button" class="add_mail" @click="addEmailInput" :disabled="isEmailValid()">{{__('Add')}}</button>

                                </div><!-- end col_add -->

                                <div v-if="emailInputs.length">
                                    <div v-for="(emailInput , i) in emailInputs">
                                        <div class="col_add">
                                            <div class="mail_active">{{emailInput.email}}</div>
                                            <button type="button" class="clear_mail" @click="deleteEmailInput(i)">{{__('delete')}}</button>
                                        </div><!-- end col_add -->
                                    </div>
                                </div>
                            </div><!-- end add_area -->
                        </div><!-- end top_row -->
                    </div><!-- end col_item -->


                    <!-- Phone -->
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('mobile number to reach him alerts')}}</span>
                            <div class="add_area">
                                <div class="col_add">
                                    <vue-tel-input
                                        v-model.trim="phoneInputValue"
                                        defaultCountry="SA"
                                        :required="true"
                                        :enabledFlags="true"
                                        name="phone"
                                        :placeholder="__('Enter Phone Number')"
                                        :inputOptions="{ showDialCode: false, tabindex: 0 }"
                                    ></vue-tel-input>
                                    <button type="button" class="add_mail" @click="addPhoneInput" :disabled="isEmptyPhone()">{{__('Add')}}</button>
                                </div><!-- end col_add -->

                                <div v-if="phoneInputs.length">
                                    <div v-for="(phoneInput , i) in phoneInputs">
                                        <div class="col_add">
                                            <div class="mail_active">{{phoneInput.phone}}</div>
                                            <button type="button" class="clear_mail" @click="deletePhoneInput(i)">{{__('delete')}}</button>
                                        </div><!-- end col_add -->
                                    </div>
                                </div>

<!--                                <div class="col_add">-->
<!--                                    <div class="mail_active">966554555555+</div>-->
<!--                                    <button type="button" class="clear_mail">{{__('delete')}}</button>-->
<!--                                </div>&lt;!&ndash; end col_add &ndash;&gt;-->
                            </div><!-- end add_area -->
                        </div><!-- end top_row -->
                    </div><!-- end col_item -->



                </div><!-- end content -->
            </div><!-- end block -->


            <div class="block">
                <div class="title">{{__('Member alerts')}}</div>
                <div class="content">
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Automatic message when logging in')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_reservation_checked_in.value.email"  v-model="settings.alert_reservation_checked_in.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_reservation_checked_in.value.sms"  v-model="settings.alert_reservation_checked_in.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div><!-- end top_row -->
                        <div class="bottom_row">
                          <textarea name="" id="" cols="30" rows="10" v-model="settings.alert_reservation_checked_in.value.content">
                                {{settings.alert_reservation_checked_in.value.content}}
                          </textarea>

<!--                            <div class="smalltxt">-->
<!--                                <small>{{__('Characters')}}  : 50</small>-->
<!--                                <small>{{__('Messages')}}  : 1</small>-->
<!--                            </div>&lt;!&ndash; end smalltxt &ndash;&gt;-->
                            <div class="another_choose">
                                <label>
                                    <input type="checkbox" name="contractNumber" :checked="settings.alert_reservation_checked_in.value.contentOptions.contractNumber" v-model="settings.alert_reservation_checked_in.value.contentOptions.contractNumber">
                                    <span class="checkmark"></span>
                                    <p>{{__('Contract Number')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="date" :checked="settings.alert_reservation_checked_in.value.contentOptions.date" v-model="settings.alert_reservation_checked_in.value.contentOptions.date">
                                    <span class="checkmark"></span>
                                    <p>{{__('Date')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="unitName" :checked="settings.alert_reservation_checked_in.value.contentOptions.unitName" v-model="settings.alert_reservation_checked_in.value.contentOptions.unitName">
                                    <span class="checkmark"></span>
                                    <p>{{__('Unit name')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="invoiceAmount" :checked="settings.alert_reservation_checked_in.value.contentOptions.invoiceAmount" v-model="settings.alert_reservation_checked_in.value.contentOptions.invoiceAmount">
                                    <span class="checkmark"></span>
                                    <p>{{__('Invoice Amount')}}</p>
                                </label>
                            </div><!-- end another_choose -->
                        </div><!-- end bottom_row -->
                    </div><!-- end col_item -->
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Automatic message upon check-out')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_reservation_checked_out.value.email"  v-model="settings.alert_reservation_checked_out.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_reservation_checked_out.value.sms"  v-model="settings.alert_reservation_checked_out.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div><!-- end top_row -->
                        <div class="bottom_row">
                              <textarea name="" id="" cols="30" rows="10" v-model="settings.alert_reservation_checked_out.value.content">
                                {{settings.alert_reservation_checked_out.value.content}}
                              </textarea>
<!--                            <div class="smalltxt">-->
<!--                                <small>{{__('Characters')}}  : 50</small>-->
<!--                                <small>{{__('Messages')}}  : 1</small>-->
<!--                            </div>&lt;!&ndash; end smalltxt &ndash;&gt;-->
                            <div class="another_choose">
                                <label>
                                    <input type="checkbox" name="contractNumber" :checked="settings.alert_reservation_checked_out.value.contentOptions.contractNumber" v-model="settings.alert_reservation_checked_out.value.contentOptions.contractNumber">
                                    <span class="checkmark"></span>
                                    <p>{{__('Contract Number')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="date" :checked="settings.alert_reservation_checked_out.value.contentOptions.date" v-model="settings.alert_reservation_checked_out.value.contentOptions.date">
                                    <span class="checkmark"></span>
                                    <p>{{__('Date')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="unitName" :checked="settings.alert_reservation_checked_out.value.contentOptions.unitName" v-model="settings.alert_reservation_checked_out.value.contentOptions.unitName">
                                    <span class="checkmark"></span>
                                    <p>{{__('Unit name')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="invoiceAmount" :checked="settings.alert_reservation_checked_out.value.contentOptions.invoiceAmount" v-model="settings.alert_reservation_checked_out.value.contentOptions.invoiceAmount">
                                    <span class="checkmark"></span>
                                    <p>{{__('Invoice Amount')}}</p>
                                </label>
                            </div><!-- end another_choose -->
                        </div><!-- end bottom_row -->
                    </div><!-- end col_item -->
<!--                    <div class="col_item">-->
<!--                        <div class="top_row">-->
<!--                            <span>{{__('Message when confirming the reservation')}}</span>-->
<!--                            <label>-->
<!--                                <input type="checkbox" name="email" :checked="settings.alert_reservation_comfirmed.value.email"  v-model="settings.alert_reservation_comfirmed.value.email">-->
<!--                                <span class="checkmark"></span>-->
<!--                                <p>{{__('Email')}}</p>-->
<!--                            </label>-->
<!--                            <label>-->
<!--                                <input type="checkbox" name="sms" :checked="settings.alert_reservation_comfirmed.value.sms"  v-model="settings.alert_reservation_comfirmed.value.sms">-->
<!--                                <span class="checkmark"></span>-->
<!--                                <p>{{__('sms')}}</p>-->
<!--                            </label>-->
<!--                        </div>&lt;!&ndash; end top_row &ndash;&gt;-->
<!--                        <div class="bottom_row">-->
<!--                          <textarea name="" id="" cols="30" rows="10" v-model="settings.alert_reservation_comfirmed.value.content">-->
<!--                                {{settings.alert_reservation_comfirmed.value.content}}-->
<!--                          </textarea>-->
<!--&lt;!&ndash;                            <div class="smalltxt">&ndash;&gt;-->
<!--&lt;!&ndash;                                <small>{{__('Characters')}}  : 50</small>&ndash;&gt;-->
<!--&lt;!&ndash;                                <small>{{__('Messages')}}  : 1</small>&ndash;&gt;-->
<!--&lt;!&ndash;                            </div>&lt;!&ndash; end smalltxt &ndash;&gt;&ndash;&gt;-->
<!--                            <div class="another_choose">-->
<!--                                <label>-->
<!--                                    <input type="checkbox" name="contractNumber" :checked="settings.alert_reservation_comfirmed.value.contentOptions.contractNumber" v-model="settings.alert_reservation_comfirmed.value.contentOptions.contractNumber">-->
<!--                                    <span class="checkmark"></span>-->
<!--                                    <p>{{__('Contract Number')}}</p>-->
<!--                                </label>-->
<!--                                <label>-->
<!--                                    <input type="checkbox" name="date" :checked="settings.alert_reservation_comfirmed.value.contentOptions.date" v-model="settings.alert_reservation_comfirmed.value.contentOptions.date">-->
<!--                                    <span class="checkmark"></span>-->
<!--                                    <p>{{__('Date')}}</p>-->
<!--                                </label>-->
<!--                                <label>-->
<!--                                    <input type="checkbox" name="unitName" :checked="settings.alert_reservation_comfirmed.value.contentOptions.unitName" v-model="settings.alert_reservation_comfirmed.value.contentOptions.unitName">-->
<!--                                    <span class="checkmark"></span>-->
<!--                                    <p>{{__('Unit name')}}</p>-->
<!--                                </label>-->
<!--                                <label>-->
<!--                                    <input type="checkbox" name="invoiceAmount" :checked="settings.alert_reservation_comfirmed.value.contentOptions.invoiceAmount" v-model="settings.alert_reservation_comfirmed.value.contentOptions.invoiceAmount">-->
<!--                                    <span class="checkmark"></span>-->
<!--                                    <p>{{__('Invoice Amount')}}</p>-->
<!--                                </label>-->
<!--                            </div>&lt;!&ndash; end another_choose &ndash;&gt;-->
<!--                        </div>&lt;!&ndash; end bottom_row &ndash;&gt;-->
<!--                    </div> -->
                </div><!-- end content -->
            </div><!-- end block -->

            <div class="block">
                <div class="title">{{__('Payment alerts')}}</div>
                <div class="content">
                    <div class="col_item">
                        <div class="top_row">
                            <span>{{__('Automatic message when payment is successful')}}</span>
                            <label>
                                <input type="checkbox" name="email" :checked="settings.alert_payment_successful.value.email"  v-model="settings.alert_payment_successful.value.email">
                                <span class="checkmark"></span>
                                <p>{{__('Email')}}</p>
                            </label>
                            <label>
                                <input type="checkbox" name="sms" :checked="settings.alert_payment_successful.value.sms"  v-model="settings.alert_payment_successful.value.sms">
                                <span class="checkmark"></span>
                                <p>{{__('sms')}}</p>
                            </label>
                        </div><!-- end top_row -->
                        <div class="bottom_row">
                          <textarea name="" id="" cols="30" rows="10" v-model="settings.alert_payment_successful.value.content">
                                {{settings.alert_payment_successful.value.content}}
                          </textarea>

                            <div class="another_choose">
                                <label>
                                    <input type="checkbox" name="contractNumber" :checked="settings.alert_payment_successful.value.contentOptions.contractNumber" v-model="settings.alert_payment_successful.value.contentOptions.contractNumber">
                                    <span class="checkmark"></span>
                                    <p>{{__('Contract Number')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="date" :checked="settings.alert_payment_successful.value.contentOptions.date" v-model="settings.alert_payment_successful.value.contentOptions.date">
                                    <span class="checkmark"></span>
                                    <p>{{__('Date')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="unitName" :checked="settings.alert_payment_successful.value.contentOptions.unitName" v-model="settings.alert_payment_successful.value.contentOptions.unitName">
                                    <span class="checkmark"></span>
                                    <p>{{__('Unit name')}}</p>
                                </label>
                                <label>
                                    <input type="checkbox" name="invoiceAmount" :checked="settings.alert_payment_successful.value.contentOptions.invoiceAmount" v-model="settings.alert_payment_successful.value.contentOptions.invoiceAmount">
                                    <span class="checkmark"></span>
                                    <p>{{__('Invoice Amount')}}</p>
                                </label>
                            </div><!-- end another_choose -->
                        </div><!-- end bottom_row -->
                    </div><!-- end col_item -->

                </div><!-- end content -->
            </div><!-- end block -->
            <div class="footer_buttons bg-30 flex p-4 justify-between">
                <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8" @click="saveOrUpdateNotificationControlSettings">{{ __('Save') }}</button>
                <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
            </div><!-- end footer_buttons -->
        </div><!-- end notification_page -->

        <delete-confirm :type="type" />



    </div>
</template>

<script>

    import DeleteConfirm from "./DeleteConfirm";
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import BreadCrumb from "./BreadCrumb";
    export default {
        name : 'notification-control',
        components : {
            Loading,
            BreadCrumb,
            DeleteConfirm
        },
        data() {
            return {
                settings : {
                    alert_reservation_added : {
                        value : {
                            sms : false,
                            email : false
                        }
                    },
                    alert_reservation_deleted : {
                        value : {
                            sms : false,
                            email : false
                        }
                    },
                    alert_reservation_canceled : {
                        value : {
                            sms : false,
                            email : false
                        }
                    },
                    alert_daily_report : {
                        value : {
                            sms : false,
                            email : false
                        }
                    },
                    alert_email : {
                        value : []
                    },
                    alert_phone : {
                        value : []
                    },
                    alert_reservation_checked_in : {
                        value : {
                            sms : false,
                            email : false,
                            content : null,
                            contentOptions : {
                                date : null,
                                unitName : null,
                                invoiceAmount : null,
                                contractNumber : null,
                            }
                        }
                    },
                    alert_reservation_checked_out : {
                        value : {
                            sms : false,
                            email : false,
                            content : null,
                            contentOptions : {
                                date : null,
                                unitName : null,
                                invoiceAmount : null,
                                contractNumber : null,
                            }
                        }
                    },
                    alert_payment_successful : {
                        value : {
                            sms : false,
                            email : false,
                            content : null,
                            contentOptions : {
                                date : null,
                                unitName : null,
                                invoiceAmount : null,
                                contractNumber : null,
                            }
                        }
                    },
                },
                emailInputValue : null,
                phoneInputValue : '',
                emailInputs : [],
                phoneInputs : [],
                emailRegex : /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,24}))$/,
                isActive: true,
                phoneRecordIndex : null,
                emailRecordIndex : null,
                type : null

            }
        },
        methods: {
            goBack() {
                this.$router.push({path: '/settings'})
            },
            getSettings() {
                let self = this;
                self.isActive = true;
                axios.get('/nova-vendor/notification-control/get-notification-settings').then(response => {
                    if(response.data.length){

                        response.data.forEach(function(obj){

                            switch (obj.key) {
                                case 'alert_reservation_added' :
                                    self.settings.alert_reservation_added.value.sms = obj.value.sms;
                                    self.settings.alert_reservation_added.value.email = obj.value.email;
                                    break;
                                case 'alert_reservation_deleted' :
                                    self.settings.alert_reservation_deleted.value.sms = obj.value.sms;
                                    self.settings.alert_reservation_deleted.value.email = obj.value.email;
                                    break;
                                case 'alert_reservation_canceled' :
                                    self.settings.alert_reservation_canceled.value.sms = obj.value.sms;
                                    self.settings.alert_reservation_canceled.value.email = obj.value.email;
                                    break;
                                case 'alert_daily_report' :
                                    self.settings.alert_daily_report.value.sms = obj.value.sms;
                                    self.settings.alert_daily_report.value.email = obj.value.email;
                                    break;
                                case 'alert_email' :
                                    self.emailInputs = obj.value;
                                    self.settings.alert_email.value = obj.value;
                                    break;
                                case 'alert_phone' :
                                    self.phoneInputs = obj.value;
                                    self.settings.alert_phone.value = obj.value;
                                    break;
                                case 'alert_reservation_checked_in' :
                                    self.settings.alert_reservation_checked_in.value.sms = obj.value.sms;
                                    self.settings.alert_reservation_checked_in.value.email = obj.value.email;
                                    self.settings.alert_reservation_checked_in.value.content = obj.value.content;

                                    self.settings.alert_reservation_checked_in.value.contentOptions.contractNumber = obj.value.contentOptions.contractNumber;
                                    self.settings.alert_reservation_checked_in.value.contentOptions.date = obj.value.contentOptions.date;
                                    self.settings.alert_reservation_checked_in.value.contentOptions.unitName = obj.value.contentOptions.unitName;
                                    self.settings.alert_reservation_checked_in.value.contentOptions.invoiceAmount = obj.value.contentOptions.invoiceAmount;
                                    break;
                                case 'alert_reservation_checked_out' :
                                    self.settings.alert_reservation_checked_out.value.sms = obj.value.sms;
                                    self.settings.alert_reservation_checked_out.value.email = obj.value.email;
                                    self.settings.alert_reservation_checked_out.value.content = obj.value.content;

                                    self.settings.alert_reservation_checked_out.value.contentOptions.contractNumber = obj.value.contentOptions.contractNumber;
                                    self.settings.alert_reservation_checked_out.value.contentOptions.date = obj.value.contentOptions.date;
                                    self.settings.alert_reservation_checked_out.value.contentOptions.unitName = obj.value.contentOptions.unitName;
                                    self.settings.alert_reservation_checked_out.value.contentOptions.invoiceAmount = obj.value.contentOptions.invoiceAmount;
                                    break;
                                    
                                case 'alert_payment_successful' :
                                    self.settings.alert_payment_successful.value.sms = obj.value.sms;
                                    self.settings.alert_payment_successful.value.email = obj.value.email;
                                    self.settings.alert_payment_successful.value.content = obj.value.content;
                                
                                    self.settings.alert_payment_successful.value.contentOptions.contractNumber = obj.value.contentOptions.contractNumber;
                                    self.settings.alert_payment_successful.value.contentOptions.date = obj.value.contentOptions.date;
                                    self.settings.alert_payment_successful.value.contentOptions.unitName = obj.value.contentOptions.unitName;
                                    self.settings.alert_payment_successful.value.contentOptions.invoiceAmount = obj.value.contentOptions.invoiceAmount;
                                    break;

                                default :
                                    break;
                            }
                        });
                    }

                    self.isActive = false;

                })
                .catch(error => {
                    console.log(error);
                });

            },
            addEmailInput(){
                this.emailInputs.push({email : this.emailInputValue});
                this.settings.alert_email.value = this.emailInputs;
                this.emailInputValue = null;
            },
            deleteEmailInput(index){
                this.emailRecordIndex = index;
                this.type = 'email'
                Nova.$emit('email-record-delete');
            },
            confirmDeleteEmail(){
                this.emailInputs.splice(this.emailRecordIndex,1)
            },
            isEmailValid: function() {
                return (this.emailInputValue == "")? "" : (this.emailRegex.test(this.emailInputValue)) ?  false :  true;
            },
            isEmptyPhone: function() {
                return (this.phoneInputValue == "" || this.phoneInputValue === null)? true : false
            },
            addPhoneInput(){

                this.phoneInputs.push({phone : this.phoneInputValue.replace(/\s/g, "")});
                this.settings.alert_phone.value = this.phoneInputs;
                this.phoneInputValue = null;
            },
            deletePhoneInput(index){
                this.phoneRecordIndex = index;
                this.type = 'phone';
                Nova.$emit('phone-record-delete');
            },
            confirmDeletePhone(){
                this.phoneInputs.splice(this.phoneRecordIndex,1)
            },
            saveOrUpdateNotificationControlSettings(){
                if(!this.emailInputs.length){
                    this.$toasted.show(this.__('Please make sure that at least one email address added'), {
                        duration : 2000,
                        type: 'error',
                        position: "top-center",
                    });

                    return false;
                }

                if(!this.phoneInputs.length){
                    this.$toasted.show(this.__('Please make sure that at least one phone number added'), {
                        duration : 2000,
                        type: 'error',
                        position: "top-center",
                    });

                    return false;
                }

                this.isActive = true;
                axios.post('/nova-vendor/notification-control/update-notification-control' , {data : JSON.stringify(this.settings)})
                        .then((res) => {

                            this.isActive = false;
                            this.$router.push('/settings');
                            this.$toasted.show(this.__('Notifications control settings has been stored successfully'), {
                                duration : 2000,
                                type: 'success',
                                position: "top-center",
                            });
                        })
                        .catch((err) => {
                            console.log(err);
                        })
            }
        },
        mounted() {
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Settings',
                    to: '/settings',
                },
                {
                    text: 'Notifications',
                    to: '#',
                }
            ]

            this.getSettings();

            Nova.$on('phone-record-deleted' , () => {
                this.confirmDeletePhone();
            })

            Nova.$on('email-record-deleted' , () => {
                this.confirmDeleteEmail();
            })

        },
    }
</script>
