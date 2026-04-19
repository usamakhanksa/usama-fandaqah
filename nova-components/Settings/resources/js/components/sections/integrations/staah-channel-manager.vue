<template>
    <div class="integration_col">
        <div class="integration_item">
            <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="false"></loading>
            <div class="status_label">
                <label v-if="channel_manager_status == 'connected'" class="connected">{{ __('Connected') }}</label>
                <label v-else class="notconnected">{{ __('Not Connected') }}</label>
            </div>
            <div>
                <div class="imgthumb cursor-pointer">
                    <img src="/images/channel_manager.png" alt="unifonic">
                </div><!-- imgthumb -->
                <div class="desc">{{ __('Channel Manager') }}</div>
                <div class="date_integration">
                    <button v-if="channel_manager_status == 'disconnected'" @click="makeConnection('connect')"
                        class="connect"> {{
                            __('Connect')
                        }}</button>
                    <button @click="makeConnection('disconnect')" v-else class="disconnect"> {{
                        __('Disconnect !')
                    }}</button>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
export default {
    name: "staah-channel-manager",
    components: {
        Loading
    },
    data() {
        return {
            jawaly: null,
            key: 'staah-channel-manager',
            isLoading: false,
            locale: Nova.config.local,
            team: null,
            setting: null,
            channel_manager_status: 'disconnected'
        }
    },
    methods: {
        getGeneralSettings() {
            axios.get(`/nova-vendor/settings/get-general-settings`)
                .then(response => {
                    this.setting = response.data;
                })
        },
        checkChannelManagerConenction() {
            axios.get(`/nova-vendor/settings/get-team-info`)
                .then(response => {
                    this.team = response.data;
                    this.channel_manager_status = response.data.channel_manager_status
                    this.getGeneralSettings();
                })
        },
        makeConnection(type) {
            const self = this;
            this.isLoading = true;
            if (type == 'connect') {
                var phone_without_hyphen = this.team.owner.phone.replace(/-/g, "");
                var pure_owner_phone = phone_without_hyphen.replace(/\+/g, "");
                axios.post(window.STAAH_MEDIATOR_API_URL + '/api/v1/createOrUpdateProperty', {
                    team: {
                        id: this.team.id,
                        name: this.team.name,
                        owner_id: this.team.owner.id,
                        owner_name: this.team.owner.name,
                        owner_email: this.team.owner.email,
                        owner_phone: pure_owner_phone,
                        city_title: this.team.city.title['ar'].replace(/\s+/g, '-').toLowerCase()
                    },
                    setting: this.setting,
                    locale: this.locale,
                    HotelDescriptiveContentNotifType: 'New'
                })
                    .then(response => {
                        if (response.data.Status == "Success") {
                            this.channel_manager_status = 'connected';
                            this.$toasted.success(this.__('Fandaqah Integrated Successfully'), {
                                duration: 3000
                            });
                            this.updateTeamChannelStatus()
                            this.createDefaultRatePlan();
                        } else {
                            if(response.data.Errors){
                                if (response.data.Errors.length){
                                    response.data.Errors.forEach(function (error) {
                                        self.$toasted.error(error.ShortText, {
                                            duration: 5000
                                        });
                                    })
                                }else{
                                    this.channel_manager_status = 'connected';
                                    this.$toasted.success(this.__('Fandaqah Integrated Successfully'), {
                                        duration: 3000
                                    });
                                    this.updateTeamChannelStatus()
                                }
                               
                            }
                        }

                        this.isLoading = false;
                    })
            } else {
                // make a disconnect request - will be using team id 
                this.channel_manager_status = 'disconnected';
                this.updateTeamChannelStatus();
            }

        },
        updateTeamChannelStatus() {
            axios.post('/nova-vendor/settings/update-channel-status', {
                channel_manager_status: this.channel_manager_status
            })
            .then(response => {
                if (this.channel_manager_status == 'disconnected'){
                    this.$toasted.success(this.__('Fandaqah Disconnected Successfully'), {
                        duration: 3000
                    });
                     this.isLoading = false;
                }
            })
        },
        createDefaultRatePlan() {
            axios.post(window.STAAH_MEDIATOR_API_URL + '/api/v1/createOrUpdateRatePlan', {
                team_id: this.team.id,
                rate_meal_plan_id: 15, // means room only from staah loopkups codes 
                rate_name: 'default rate'
            })
                .then(response => {
                    console.log(response.data)
                })
        }

    },
    mounted() {
        this.checkChannelManagerConenction();
    }
}
</script>

<style lang="scss">
.staahModal {
    .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block !important;
        position: relative;
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
    .loader_item {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;

        svg {
            display: block;
            margin: 0 auto;
            width: 60px !important;
            height: auto;
            align-self: flex-end;
        }

        /* svg */
        span {
            display: table;
            margin: 20px auto 0;
            font-size: 20px;
            width: 100%;
            align-self: baseline;
            text-align: center;
            color: #000;
        }

        /* span */
    }

    /* loader_item */
    .alert_unifonic {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        text-align: center;
        padding: 15px;
        border-radius: 4px;
        font-size: 15px;
        margin: 0 auto 15px;
    }

    /* alert_unifonic */
    .input_group {
        margin: 0 auto 10px;

        label {
            display: block;
            font-size: 15px;
            margin: 0 auto 5px;
            color: #000;
        }

        /* label */
        input {
            height: 40px;
            padding: 0 10px;
            color: #000;
            font-size: 15px;
            border: 1px solid #dddddd !important;
            background: #fafafa;
            width: 100%;
        }

        /* input */
    }

    /* input_group */
    button {
        height: 35px;
        width: 100%;
        color: #ffffff;
        background: #4099de;
        border: 1px solid #4099de;
        border-radius: 5px;
        padding: 0;
        margin: 15px auto;
        cursor: pointer;
        -webkit-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;

        &:hover {
            background: #4099de;
        }
    }

    /* button */
}

/* staahModal */
.recharge_mobile_modal {
    .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block;
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
    span {
        display: block;
        font-size: 16px;
        color: #000;
        margin: 0 auto 5px;
        text-align: center;
    }

    /* span */
    p {
        display: block;
        font-size: 16px;
        color: #000;
        margin: 0 auto 5px;
        text-align: center;
    }

    /* p */
    .input_group {
        margin: 30px auto;
        display: table;
        text-align: center;

        input {
            height: 40px;
            padding: 0 10px;
            color: #000;
            font-size: 20px;
            border: 1px solid #dddddd;
            background: #fafafa;
            min-width: 150px;
            max-width: 100%;
            display: block;
            margin: 0 auto;
            text-align: center !important;
            padding: 0 !important;
        }

        /* input */
        label {
            display: block;
            font-size: 15px;
            margin: 5px auto 0;

            p {
                display: inline-block;
                margin: 0 auto;
                font-weight: bold;
                font-size: inherit;
            }

            /* p */
            i {
                font-style: normal;
                display: inline-block;
                font-weight: bold;
                font-size: inherit;
            }

            /* i */
        }

        /* label */
        .wrong {
            display: block;
            margin: 5px auto 0;
            color: red;
            font-size: 15px;
        }

        /* wrong */
    }

    /* input_group */
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
        -webkit-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;

        &:hover {
            background: #0071C9;
            border-color: #0071C9;
        }

        /* hover */
    }

    /* button */
}

/* recharge_mobile_modal */

.Fonic_Info_Modal {
    .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block !important;
        position: relative;
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
    span {
        display: block;
        margin: 20px auto;
        font-weight: bold;
        font-size: 15px;

        p {
            display: block;
            font-size: 15px;
            font-weight: normal;
            margin: 5px auto 0;
        }

        /* p */
        ul {
            list-style: unset;
            padding: 10px 30px;

            li {
                margin: 0 auto 10px;
                font-size: 15px;
                color: #000;
                font-weight: normal;
            }

            /* li */
        }

        /* ul */
    }

    /* span */
    embed {
        width: 100% !important;
        height: 600px !important;
    }

    /* embed */
}

/* Fonic_Info_Modal */
</style>
