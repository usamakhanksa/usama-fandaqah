<template>
    <div>
        <bread-crumb />

        <h1 class="mb-3 text-90 font-normal text-2xl">{{__('Update My Information')}}</h1>
        <div class="card overflow-hidden">
            <form autocomplete="off">

                <div>
                    <div class="flex border-b border-40" >
                        <div class="w-1/5 py-6 px-8">
                            <label for="name" class="inline-block text-80 pt-2 leading-tight">
                                {{__('Name')}}
                            </label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <input id="name" type="text" :placeholder="__('Name')" v-model="user.name" class="w-full form-control form-input form-input-bordered" disabled>
                            <!---->
                            <div class="help-text help-text mt-2"> </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex border-b border-40" >
                        <div class="w-1/5 py-6 px-8">
                            <label for="email" class="inline-block text-80 pt-2 leading-tight">
                                {{__('Email')}}
                            </label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <input id="email"  type="text" placeholder="بريد الإلكتروني" v-model="user.email" class="w-full form-control form-input form-input-bordered">
                            <!---->
                            <div class="help-text help-text mt-2"> </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex border-b border-40">
                        <div class="w-1/5 py-6 px-8">
                            <label for="phone" class="inline-block text-80 pt-2 leading-tight">
                                {{__('Phone')}}
                            </label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <vue-tel-input
                                :defaultCountry="'sa'"
                                @onInput="checkThePhone($event)"
                                :required="true"
                                :enabledFlags="true"
                                name="phone"
                                :placeholder="__('Enter Phone Number')"
                                :inputOptions="{ showDialCode: false, tabindex: 0 }"
                                v-model="user.phone"
                            >
                            </vue-tel-input>
                            <p v-if="!customerValidPhone" style="color:#ce1025;">{{__('Phone number is not valid')}}</p>

                            <div v-if="phoneError" class="text-red-500 mt-2">
                                {{ phoneErrorMessage }}
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex border-b border-40">
                        <div class="w-1/5 py-6 px-8">
                            <label for="title" class="inline-block text-80 pt-2 leading-tight">
                                {{ __('Title') }}
                            </label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <select id="title"
                                v-model="user.title"
                                class="w-full form-control form-input form-input-bordered">
                                <option value="Owner">{{ __('Owner') }}</option>
                                <option value="Investor">{{ __('Investor') }}</option>
                                <option value="Receptionist">{{ __('Receptionist') }}</option>
                                <option value="Hotel Manager">{{ __('Hotel Manager') }}</option>
                                <option value="Maintenance Manager">{{ __('Maintenance Manager') }}</option>
                                <option value="General Manager">{{ __('General Manager') }}</option>
                                <option value="Operation Manager">{{ __('Operation Manager') }}</option>
                                <option value="Sales Manager">{{ __('Sales Manager') }}</option>
                                <option value="Reservations Manager">{{ __('Reservations Manager') }}</option>
                                <option value="Finance Manager">{{ __('Finance Manager') }}</option>
                                <option value="IT Manager">{{ __('IT Manager') }}</option>
                                <option value="Acountant">{{ __('Acountant') }}</option>
                                <option value="Housekeeping Manager">{{ __('Housekeeping Manager') }}</option>
                                <option value="Front Office Manager">{{ __('Front Office Manager') }}</option>
                                <option value="Other">{{ __('Other') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- <div>
                    <div class="flex border-b border-40">
                        <div class="w-1/5 py-6 px-8">
                            <label for="password" class="inline-block text-80 pt-2 leading-tight">
                                {{__('Password')}}
                            </label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <input id="password"  type="password" placeholder="كلمة المرور" v-model="user.password" autocomplete="new-password" class="w-full form-control form-input form-input-bordered">

                            <div class="help-text help-text mt-2"> </div>
                        </div>
                    </div>
                </div> -->

                <div class="bg-30 flex p-4 justify-between">
                    <button @click="stepBack" type="button" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{__('Back')}}</button>
                    <button @click="updateUserData"  type="button" class="btn btn-default btn-primary inline-flex items-center relative" dusk="update-button">{{__('Update')}}</button>
                </div>
            </form>
        </div>

        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Please Fix Below Errors')" overlay-theme="dark" ref="errorModal" class="Cancel_Reservation_Modal">

            <ul>
                <template v-if="validationMessages.length">
                    <li style="color: red;" v-for="(err , i) in validationMessages" :key="i">{{err}}</li>
                </template>
            </ul>
            <div class="flex w-full justify-end pt-2 border-t-2 mt-4">
                <button class="shadow btn btn-primary px-7 py-2 mr-2 text-base" @click="navigateToWhatsapp">{{__('Contact Support')}}</button>
                <button class="shadow btn  btn-danger px-8 py-2 text-base" @click="close">{{__('Back')}}</button>
            </div>

        </sweet-modal>


    </div>
</template>

<script>
    export default {
        name: "ProfileEdit",
        data(){
            return {
                user : {},
                validationMessages : [],
                phoneError: false,
                phoneErrorMessage: '',
                customerValidPhone: true,

            }
        },
        methods : {
            getUserInfo(){
                axios.get('/nova-vendor/settings/getUserObject')
                    .then((res) => {
                        this.user = res.data;
                    })
            },
            checkThePhone(phone){
                console.log(phone);
                this.customerValidPhone = phone.isValid;
                this.customerPhoneCountry = phone.country.name;
            },
            stepBack(){
                this.$router.push('/profile')
            },
            open(){
                this.$refs.errorModal.open();
            },
            close(){
                this.validation = null ;
                this.$refs.errorModal.close();
            },
            updateUserData() {
                axios.post('/nova-vendor/settings/updateUserProfileData', this.user)
                    .then((res) => {
                        if (!res.data.status) {
                            // Assign validation messages to the array
                            this.validationMessages = res.data.messages;

                            // Open the error modal
                            this.$refs.errorModal.open();
                        } else {
                            Nova.$emit('user-updated', res.data.user);

                            this.$toasted.show(this.__('Profile Data Updated Successfully'), {
                                type: 'success',
                            });

                            this.stepBack();
                        }
                    })
                    .catch((error) => {
                        if (error.response && error.response.data.messages) {
                            this.validationMessages = error.response.data.messages;
                            this.$refs.errorModal.open();
                        } else {
                            console.error('Unexpected error:', error);
                        }
                    });
            },
            navigateToWhatsapp() {
                window.open('https://wa.me/966555947522', '_blank'); // Opens the link in a new tab or page
            },
            validatePhone() {
                if (this.user.phone.length < 10) {
                    this.phoneError = true;
                    this.phoneErrorMessage = 'Phone number must be at least 10 digits';
                } else {
                    this.phoneError = false;
                    this.phoneErrorMessage = '';
                }
            }
        },
        mounted() {
            this.getUserInfo();
        }
    }
</script>

<style scoped>

</style>
