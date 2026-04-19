<template>

    <div>
        <button 
            :disabled="smsDisabled"
            @click="send"
            :title="btn_title"
            v-if="can_send_sms"
            class="sms_component_button">
            {{ __(smsButtonText) }}
        </button>
    </div>
</template>

<script>
    export default {
        name: "sms-component",
        props : ['entity_id','document_url','document_type','sms_base_title','phone','team_id'],
        data(){
            return {
                can_send_sms : false,
                locale : Nova.config.local,
                smsDisabled: false,
                smsButtonText: 'SMS',
                countdown: 60,
                countdownTimer: null,
            }
        },
        methods: {
            checkSmsIntegration(){
                Nova.request().get('/api/check_integrate_with_sms_gateway', {
                        params : {
                            team_id: Nova.app.user.current_team_id
                        }
                })
                .then(response => {
                    if(response.data.data.type != 'no_balance'){
                        this.can_send_sms = response.data.data.check;
                    }
                })
            },
            startCountdown() {
              this.smsDisabled = true;
              this.smsButtonText = `${this.countdown}s`;

              this.countdownTimer = setInterval(() => {
                this.countdown--;

                if (this.countdown > 0) {
                  this.smsButtonText = `${this.countdown}s`;
                } else {
                  clearInterval(this.countdownTimer);
                  this.smsDisabled = false;
                  this.smsButtonText = 'SMS';
                  this.countdown = 60; // reset for next use
                }
              }, 1000);
            },
            send(){

                if(!this.phone && this.document_type == 'pos'){
                    this.$toasted.show(this.__('Phone number is missing , we can not send sms right now'), {type: 'error'});
                    return;
                }

                this.startCountdown();
                this.is_sending = true;
                axios
                .post('/nova-vendor/calender/sms/printless/send' , {
                    sms_base_title : this.sms_base_title,
                    document_url : this.document_url,
                    entity_id : this.entity_id,
                    lang : this.locale,
                    document_type : this.document_type,
                    phone : this.phone ? this.phone : null,
                    team_id : this.team_id ? this.team_id : null
                })
                .then(response => {

                    this.is_sending = false
                    if(response && response.data.abort && response.data.message == 'no person in charge phone found'){
                        this.$toasted.show(this.__('No Person in charge phone was added in company information'), {type: 'error'});
                        return;
                    }

                    if(response && response.data.abort && response.data.message == 'no customer phone found'){
                        this.$toasted.show(this.__('Phone number is missing , we can not send sms right now'), {type: 'error'});
                        return;
                    }
                    this.$toasted.show(this.__('The sending is underway - the message will be received within 1 minute'), {type: 'success'})
                })
            }
        },
        mounted() {
            this.checkSmsIntegration();
        }
    }
</script>


<style lang="scss" scoped>
   .sms_component_button{
        display: block !important;
        height: 35px !important;
        width: 50px !important;
        border-radius: 5px !important;
        background-position: center center !important;
        background-size: 30px !important;
        background-repeat: no-repeat !important;
        background-color: #dedede !important;
        margin: 5px !important;
        cursor: pointer !important;  
        font-weight: 600 !important;
        &:hover {
            background-color: #bebcbc !important;
        }
    }
</style>
