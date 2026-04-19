<template>
    <div>
        <bread-crumb />

        <div class="flex items-center mb-3">
            <h4 class="text-90 font-normal text-2xl flex-no-shrink">{{__('My Account')}}</h4>
            <div class="ml-3 w-full flex items-center custom-flex">
                <div class="flex w-full justify-end items-center"></div>
                <div class="ml-3">
                    <!---->
                    <!---->
                </div>
                <!---->
                <!---->
                <!---->
                <div class="v-portal" style="display: none;"></div>
                <div class="v-portal" style="display: none;"></div>
                <div class="v-portal" style="display: none;"></div>


                <router-link v-if="user.current_team_id != 44" to="/profile/edit" class="btn btn-default btn-icon bg-primary custom-mx-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current text-white" style="margin-top: -2px; margin-left: 3px;">
                        <path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path>
                    </svg>
                </router-link>


            </div>
        </div>

        <div class="card mb-6 py-3 px-6">
            <div class="flex border-b border-40">
                <div class="w-1/4 py-4">
                    <h4 class="font-normal text-80">#</h4></div>
                <div class="w-3/4 py-4">
                    <p class="text-90">{{user.id}}</p>
                </div>
            </div>


            <div class="flex border-b border-40">
                <div class="w-1/4 py-4">
                    <h4 class="font-normal text-80">{{__('Name')}}</h4></div>
                <div class="w-3/4 py-4">
                    <p class="text-90">{{user.name}}</p>
                </div>
            </div>
            <div class="flex border-b border-40">
                <div class="w-1/4 py-4">
                    <h4 class="font-normal text-80">{{__('Title')}}</h4></div>
                <div class="w-3/4 py-4">
                    <p class="text-90">{{user.title}}</p>
                </div>
            </div>
            <div class="flex border-b border-40">
                <div class="w-1/4 py-4">
                    <h4 class="font-normal text-80">{{ __('Email') }}</h4>
                </div>
                <div class="w-1/4 py-4">
                    <p class="text-90">{{ user.email }}</p>
                </div>
                <div class="w-2/4 py-4">
                    <button
                        :class="{
                            'bg-green-500 text-white': user.email_verified_at !== null,
                            'bg-blue-500 text-white hover:bg-blue-600': user.email_verified_at === null,
                            'bg-gray-400 cursor-not-allowed': isButtonDisabledEmail,
                        }"
                        :disabled="user.email_verified_at !== null || isButtonDisabledEmail"
                        @click="sendEmailVerification"
                        class="px-4 py-2 rounded shadow"
                    >
                        {{ user.email_verified_at !== null ? __('Verified') : __('Verify Email') }}
                    </button>
                    <div v-if="emailSentMessage" class="text-green-500 mt-2">
                        {{ emailSentMessage }}
                    </div>
                </div>
            </div>




            <div class="flex border-b border-40">
                <div class="w-1/4 py-4">
                    <h4 class="font-normal text-80">{{__('Phone')}}</h4>
                </div>
                <div class="w-1/4 py-4">
                    <p class="text-90">{{ user.phone }}</p>
                </div>
                <div class="w-2/4 py-4">
                    <button
                        v-if="!showCodeInput"
                        :class="{
                            'bg-green-500 text-white': user.phone_verified_at !== null,
                            'bg-blue-500 text-white hover:bg-blue-600': user.phone_verified_at === null,
                            'bg-gray-400 cursor-not-allowed': isButtonDisabled
                        }"
                        :disabled="isButtonDisabled || user.phone_verified_at !== null"
                        @click="sendSmsVerification"
                        class="px-4 py-2 rounded shadow"
                    >
                    {{ user.phone_verified_at !== null ? __('Verified') : __('Verify Phone Number') }}
                    </button>

                    <div v-else class="flex items-center space-x-2">
                        <input
                            v-model="verificationCode"
                            type="text"
                            maxlength="4"
                            class="border px-3 py-2 rounded w-24"
                            placeholder="Enter Code"
                        />
                        <button
                            @click="checkVerificationCode"
                            class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600"
                        >
                            {{ __('Check') }}
                        </button>
                    </div>
                    <div v-if="smsSentMessage" class="text-green-500 mt-2">
                        {{ smsSentMessage }}
                    </div>
                </div>
            </div>
            <div class="flex border-b border-40">
                <div class="w-1/4 py-4">
                    <h4 class="font-normal text-80">{{__('Roles')}}</h4></div>
                <div class="w-3/4 py-4">
                    <p class="text-90">{{user.roles_text}}</p>
                </div>
            </div>
            <div class="relative" v-if="team.enable_digital_signature && enable_digital_signature !== 0">
                <div class="flex border-b border-40 remove-bottom-border">
                    <div class="w-1/4 py-4">
                        <h4 class="font-normal text-80">{{__('Signature')}}</h4></div>
                    <div class="w-2/4 py-4 relative">
                        <VueSignaturePad class="border border-blue-300 border-solid mt-2 rounded-md" width="100%" height="200px" ref="signaturePad" :options="{ onBegin, onEnd }"/>
                        <div class="mt-2 text-center">
                            <button @click="undoSignature()" class="bg-red-300 text-white px-4 py-2 rounded shadow hover:bg-red-400"
                            >{{ __('Clear') }}</button>
                            <button @click="saveSignature()" class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600"
                            >{{ __('Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    
    export default {
        name: "Profile",
        data() {
            return {
                user : {},
                emailVerificationInProgress : false,
                verificationInProgress: false,
                isButtonDisabled: false, // to disable the button temporarily
                isButtonDisabledEmail: false, // to disable the button temporarily
                verificationTimeout: 60,
                EmailverificationTimeout: 5,
                verificationCode: '',
                emailSentMessage: '',
                smsSentMessage: '',
                showCodeInput: false,
                team: null,
            }
        },
        methods : {
            getUserInfo(){
                axios.get('/nova-vendor/settings/getUserObject')
                    .then((res) => {
                        this.user = res.data;
                        if(this.team.enable_digital_signature && this.team.enable_digital_signature !== 0 && this.user.digital_signature) {
                            this.unCompressSignature(this.user.digital_signature.signature_base64);
                        }
                    })
            },

            sendEmailVerification() {
                this.isButtonDisabledEmail = true;
                // Prevent sending if email is verified or if verification is in progress
                if (this.user.email_verified_at !== null) return;

                axios.post('/nova-vendor/settings/sendEmailVerification', {
                    user_id: this.user.id, // Pass the user ID in the request body
                })
                .then((response) => {
                    this.startEmailCountdown();
                    if (response.data.status && !response.data.autoVerified) {
                        this.emailSentMessage = this.__('Verification email sent successfully!');
                    }
                    if (response.data.status && response.data.autoVerified) {
                        this.emailSentMessage = response.data.message;
                        this.user.email_verified_at = new Date().toISOString(); // Mark as verified
                    } else {
                        this.$toast.error(__('Failed to send verification email.'));

                        this.emailSentMessage = this.__('Failed to send verification email. Please try again.');
                    }
                })
                .catch((error) => {
                    // Handle error
                    this.$toast.error(__('Failed to send verification email.'));
                    console.error('Error sending email verification:', error);

                });
            },
            sendSmsVerification() {

                // Make the API call to send the SMS verification
                axios.post('/nova-vendor/settings/sendSmsVerification', {
                    user_id: this.user.id, // Pass the user ID in the request body
                })
                .then((response) => {
                    // Disable the button and start the countdown
                    this.isButtonDisabled = true;
                    this.startCountdown(); // Start the countdown after sending the SMS
                    if (response.data.status && !response.data.autoVerified) {
                        console.log('SMS sent successfully.');
                        this.smsSentMessage = this.__('Verification SMS sent successfully!');
                        this.showCodeInput = true;

                    }else if(response.data.status && response.data.autoVerified){
                        console.log('SMS sent successfully.');
                        this.smsSentMessage = this.__('Phone number is auto-verified successfully!');
                        this.user.phone_verified_at = new Date().toISOString(); // Mark as verified
                        this.showCodeInput = false; // Hide the input field
                    }
                    else {
                        console.log('SMS sent not successfully.');

                        // alert(response.data.message);
                        this.smsSentMessage = this.__('Failed to send verification SMS. Please try again.');
                    }
                })
                .catch((error) => {
                    console.log(error);
                    // Handle validation or other server-side errors

                    if (error.response && error.response.data && error.response.data.message) {
                        alert(error.response.data.message); // Use the server error message
                    }
                    else {
                        alert('Error sending phone verification: invalid number format');
                    }
                    console.error('Error sending phone verification:', error);
                    this.isButtonDisabled = false; // Re-enable the button if there's an error
                });
            },
            checkVerificationCode() {
                // Call the backend to verify the code
                axios.post('/nova-vendor/settings/checkSmsVerification', {
                    user_id: this.user.id,
                    code: this.verificationCode,
                })
                .then((response) => {
                    console.log(response.data);
                    if (response.data.success) {
                        this.user.phone_verified_at = new Date().toISOString(); // Mark as verified
                        this.showCodeInput = false; // Hide the input field
                        console.log('Phone verified successfully.');
                    } else {
                        alert('Invalid code. Please try again.');
                    }
                })
                .catch((error) => {
                    alert('Invalid or Expired code. Please try again.');
                    console.error('Error verifying code:', error);
                });
            },
            startEmailCountdown() {
                let secondsRemaining = this.EmailverificationTimeout;

                const countdownInterval = setInterval(() => {
                    secondsRemaining--;
                    if (secondsRemaining <= 0) {
                        clearInterval(countdownInterval); // Stop the countdown when time is up
                        this.isButtonDisabledEmail = false; // Re-enable the button
                    }
                }, 1000); // Update every second
            },
            startCountdown() {
                let secondsRemaining = this.verificationTimeout;

                const countdownInterval = setInterval(() => {
                    secondsRemaining--;
                    if (secondsRemaining <= 0) {
                        clearInterval(countdownInterval); // Stop the countdown when time is up
                        this.isButtonDisabled = false; // Re-enable the button
                    }
                }, 1000); // Update every second
            },
            async saveSignature() {
                try {
                    const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
                    console.log('is empty signature',isEmpty);
                    const response = await axios.post('/signature/store', {
                        user_id: this.user.id,
                        signature: !isEmpty ? data : null,
                        type: 'user'
                    });
                    this.$toasted.show(response.data.message, {type: 'success'});
                }
                catch (error) {
                    console.error('Error saving signature:', error);
                    this.$toasted.show(error.message, {type: 'error'});

                }
            },
            undoSignature() {
                this.$refs.signaturePad.undoSignature();
            },
            async unCompressSignature() {
                try {
                    const response = await axios.post('/signature/uncompress', {
                        signature: this.user.digital_signature.signature_base64,
                    });
                    if(response) {
                        this.$refs.signaturePad.fromDataURL(response.data.signature);
                    }
                } catch (error) {
                    console.error('Error decompressing signature:', error);
                    this.$toasted.show(error.message, {type: 'error'});
                }
            }
        },
        mounted() {
            this.team = Nova.app.currentTeam;
            this.getUserInfo();
            // this.checkEmailVerificationCooldown();
        }
    }
</script>

<style scoped>
 .left-5per {
    left: 5%;
 }
</style>
