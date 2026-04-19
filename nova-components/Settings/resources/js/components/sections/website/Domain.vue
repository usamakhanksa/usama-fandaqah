<template>
    <div>
        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>
        <card id="domain_website" class="relative">
            <loading :active.sync="generalLoading" :is-full-page="false"></loading>
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Domain settings')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateDomain">
                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="website_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Activate Site')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <label class="switch">
                                        <input v-model="enable_website" type="checkbox">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="website_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Website Domain')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <label for="website_domain" class="inline-block text-80 text-lg leading-tight" style="direction: ltr;">.{{getDomain()}}</label>
                                    <input id="website_domain" @input="checkDomain" v-lazy-input v-model="website_domain" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90">
                                    <div v-if="website_domain && website_domain != team.slug">
                                        <div class="mt-4 text-lg text-green-600" v-if="available">النطاق {{website_domain}}.{{getDomain()}} متوفر</div>
                                        <div class="mt-4 text-lg text-red-600" v-if="not_available">النطاق {{website_domain}}.{{getDomain()}} غير متوفر جرب نطاق أخر</div>
                                    </div>
                                    <div class="mt-4 text-lg text-black-600" v-if="team.slug"><a target="_blank" :href="'http://'+team.slug+'.'+getDomain()">رابط الموقع الإلكتروني الخاص بك هو {{team.slug}}.{{getDomain()}}</a></div>
                                </div>
                            </div>
                        </div>
                    </div>

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="private_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Activate Private Domain')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <label class="switch">-->
<!--                                        <input v-model="enable_private_domain" type="checkbox" :checked="enable_private_domain">-->
<!--                                        <span class="slider round"></span>-->
<!--                                    </label>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="private_domain" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('private domain Domain')}}</label>
                                </div>
                                <div class="py-6 px-8 ">
                                    <template v-if="cached_private_domain == '' || cached_private_domain == null || private_domain_status == null">
                                        <input id="private_domain" @input="checkDns" v-lazy-input v-model="private_domain" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90">
                                    </template>
                                    <template v-else>
                                        <p>{{cached_private_domain}}</p>
                                    </template>
                                    <div v-if="(cached_private_domain_status == 'new' || cached_private_domain_status == 'installing' || cached_private_domain_status == 'installed' ) && (cached_private_domain != null || cached_private_domain != '') ">
                                        <p v-if="private_domain_status != 'installed'">{{__('The domain is being activated, this process may take 5 minutes')}}</p>
                                        <button @click="openDeleteConfirm" type="button" class="btn bg-red-500 hover:bg-red-400 text-white py-1 px-4">{{__('Delete')}}</button>
                                    </div>

                                    <div v-if="private_domain_status == 'deleted'">
                                        <p>{{__('The domain is being deleted ...')}}</p>
                                    </div>
                                    <div v-if="domain_restricted">
                                        <div class="mt-4 text-lg text-red-600">{{__('Domain is restricted')}}</div>
                                    </div>
                                    <div v-if="!is_domain_unique">
                                        <div class="mt-4 text-lg text-red-600">{{__('Domain is taken')}}</div>
                                    </div>
                                    <div v-if="private_domain && private_domain != team.slug && !domain_restricted">
                                        <div class="mt-4 text-lg text-green-600" v-if="dns_checked === true">تم التحقق من النطاق الخاص</div>
                                        <div class="mt-4 text-lg text-red-600" v-if="dns_checked === false">{{dns_checked_ip}} لايطابق <span class="text-success">{{ip}}</span> برجاء التأكد من اعدادت ال DNS الخاصة بهذا الدومين</div>
                                    </div>
                                    <div class="mt-4 text-lg text-black-600" v-if="team.slug && ( cached_private_domain == '' || cached_private_domain == null ) "><a target="_blank" :href="'http://'+team.slug+'.'+getDomain()"> {{ __('The domain DNS must first be directed to :') }} <span class="text-success">{{ip}}</span></a></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-30 flex p-4 justify-between">
                        <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                            {{ __('Save') }}
                        </button>
                        <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                    </div>
                </form>
            </div>


            <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Delete Private Domain')" overlay-theme="dark" ref="deleteConfirm" class="delete_confirm">
                <div class="relative mx-auto justify-center z-20 relative">
                    <loading :active.sync="isLoading" :is-full-page="false"></loading>
                    <span>{{__('Are you sure to delete this private domain ?')}}</span>
                    <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
                        <div class="flex justify-end flex-wrap">
                            <button id="confirm-delete-button" @click="clearPrivateDomain"   class="btn btn-default btn-danger m-0">{{__('delete')}}</button>
                            <button type="button" @click="hideDeleteModal"  class="btn btn-default bg-gray-400 ml-2"> {{__('Back')}}</button>
                        </div>
                    </div>
                </div>
            </sweet-modal>
        </card>
    </div>
</template>

<script>

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "Domain",
        components : {
            Loading
        },
        data() {
            return {
                website_domain: null,
                private_domain: null,
                enable_private_domain: false,
                dns_checked: null,
                dns_checked_ip: null,
                ip: null,
                team: Object,
                installed: false,
                reset: true,
                available: false,
                enable_website: false,
                not_available: false,
                crumbs: [],
                locale : null,
                private_domain_status : null,
                cached_private_domain_status : null,
                cached_private_domain : null,
                isLoading : false,
                domain_restricted : false,
                is_domain_unique : true,
                timer : '',
                generalLoading : false
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
                    text: 'Website Settings',
                    to: '/settings/website',
                },
                {
                    text: 'Domain settings',
                    to: '#',
                }
            ];

        },
        created() {
            this.locale = Nova.config.local;
            this.ip = Nova.config.ip;
            this.getTeamInfo();
            // this.startInterval();
        },
        beforeDestroy() {
            clearInterval(this.timer);
        },
        methods: {
            getTeamInfo(){
                this.generalLoading = true;
                axios.get('/nova-vendor/settings/get-team-info')
                    .then(response => {
                        this.team = response.data;
                        this.website_domain = this.team.slug;
                        this.enable_website = this.team.enable_website;
                        this.private_domain = this.team.private_domain;
                        this.cached_private_domain = this.team.private_domain;
                        this.private_domain_status = this.team.private_domain_status;
                        this.cached_private_domain_status = this.team.private_domain_status;
                        this.enable_private_domain = this.team.enable_private_domain;
                        this.generalLoading = false;
                        this.startInterval();
                    });
            },
            startInterval(){

                    if(this.private_domain_status != 'installed'){
                       this.timer = setInterval( () => {
                            axios.get('/nova-vendor/settings/get-team-info')
                                .then((response) => {
                                    this.team = response.data;
                                    this.website_domain = this.team.slug;
                                    this.enable_website = this.team.enable_website;
                                    this.private_domain = this.team.private_domain;
                                    this.cached_private_domain = this.team.private_domain;
                                    this.private_domain_status = this.team.private_domain_status;
                                    this.cached_private_domain_status = this.team.private_domain_status;
                                    this.enable_private_domain = this.team.enable_private_domain;
                                    if(this.private_domain_status == 'installed'){
                                        this.$toasted.success(Nova.app.__('Domain installed successfully'), {
                                            duration: 3000
                                        });
                                        clearInterval(this.timer);
                                    }
                                });
                        }, 60000);
                    }else{
                       clearInterval(this.timer);
                    }
            },
            clearPrivateDomain(){
                this.isLoading = true;
              axios.post('/nova-vendor/settings/clear-private-domain' , {
                  team_id : this.team.id
              }).then(response => {
                  this.isLoading = false;
                  this.hideDeleteModal();
                  this.$toasted.success(Nova.app.__('Success'), {
                      duration: 3000
                  });
                  setTimeout(() => location.reload() , 2000);
              });
            },

            openDeleteConfirm(){
              this.$refs.deleteConfirm.open();
            },

            hideDeleteModal(){
                this.$refs.deleteConfirm.close();
            },

            checkIsValidDomain(domain) {
                let re = new RegExp(/^^(((([a-zA-Z0-9])|([a-zA-Z0-9][a-zA-Z0-9\-]{0,86}[a-zA-Z0-9]))\.(([a-zA-Z0-9])|([a-zA-Z0-9][a-zA-Z0-9\-]{0,73}[a-zA-Z0-9]))\.(([a-zA-Z0-9]{2,12}\.[a-zA-Z0-9]{2,12})|([a-zA-Z0-9]{2,25})))|((([a-zA-Z0-9])|([a-zA-Z0-9][a-zA-Z0-9\-]{0,162}[a-zA-Z0-9]))))$/);
                return domain.match(re);
            },
            getDomain() {
                if (location.host == 'app.fandaqah.com') {
                    return 'fndqh.com';
                }
                return location.host;
            },
            updateDomain() {
                if(!this.dns_checked && !this.private_domain_status){
                  this.private_domain = null;
                  this.enable_private_domain = false;
                }
                Nova.request()
                    .put("/nova-vendor/settings/update-domain", {
                        id: this.team.id,
                        slug: this.website_domain,
                        enable_website: this.enable_website,
                        private_domain: this.domain_restricted && !this.dns_checked   ? null :  this.private_domain,
                        enable_private_domain: this.enable_private_domain,
                        private_domain_status : this.private_domain_status && this.private_domain  ? this.private_domain_status : null
                    })
                    .then(response => {
                        this.team = response.data.team;
                        Spark.state.currentTeam = this.team;
                        this.website_domain = this.team.slug;
                        this.enable_website = this.team.enable_website;
                        this.private_domain = this.team.private_domain;
                        this.enable_private_domain = this.team.enable_private_domain;

                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('Success'), {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        this.$toasted.error(Nova.app.__('Error'), {
                            duration: 3000
                        });
                    })
                ;
                this.available = false;
                this.not_available = false;
            },
            checkDomain() {
                this.available = false;
                this.not_available = false;

                if (this.website_domain === null || !this.website_domain) {
                    this.$toasted.error(Nova.app.__('Insert Domain'), {
                        duration: 3000
                    });
                    return;
                }
                if (!this.checkIsValidDomain(this.website_domain)) {
                    this.$toasted.error(Nova.app.__('Insert Valid Domain'), {
                        duration: 3000
                    });
                    return;
                }

                Nova.request()
                    .put("/nova-vendor/settings/check-domain", {
                        id: this.team.id,
                        slug: this.website_domain
                    })
                    .then(response => {
                        this.available = true;
                    })
                    .catch(error => {
                        this.available = false;
                        this.not_available = true;
                    });
            },
            checkDns() {
                Nova.request()
                    .put("/nova-vendor/settings/check-dns", {
                        private_domain: this.private_domain
                    })
                    .then(response => {

                        if(response.data.restricted){
                            this.domain_restricted = true;
                            this.is_domain_unique = true;
                        }else if(response.data.not_unique){
                            this.is_domain_unique = false;
                        }else{
                            this.domain_restricted = false;
                            this.is_domain_unique = true;
                            this.dns_checked = response.data.success;
                            this.dns_checked_ip = response.data.ip;
                            this.private_domain_status = this.private_domain && this.dns_checked ?  'new' : null;
                        }

                    })
                    .catch(error => {
                        this.dns_checked = false;
                    });
            },
            getSettings() {

            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            }
        }
    }
</script>

<style scoped>

    .delete_confirm h2 {
        line-height: 63px;
    }
    .delete_confirm span {
        padding: 30px 20px;
        line-height: normal;
        display: block;
        font-size: 20px;
        color: #000;
    }


.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 26px;
  margin: 5px auto;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}
.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
input:checked + .slider {
  background-color: #21b978;
}
input:focus + .slider {
  box-shadow: 0 0 1px #21b978;
}
input:checked + .slider:before {
  -webkit-transform: translateX(33px);
  -ms-transform: translateX(33px);
  transform: translateX(33px);
}
.slider.round {
  border-radius: 34px;
}
.slider.round:before {
  border-radius: 50%;
}
@media (min-width: 320px) and (max-width: 480px) {
    #domain_website .flex.border-b.border-40.w-full {display: block;}
    #domain_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #domain_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #domain_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2[data-v-063dbc44] input {
        margin: 10px;
        width: 55%;
    }
    #domain_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #domain_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
@media (min-width: 481px) and (max-width: 767px) {
    #domain_website .flex.border-b.border-40.w-full {display: block;}
    #domain_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #domain_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #domain_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2[data-v-063dbc44] input {
        margin: 10px;
        width: 55%;
    }
    #domain_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #domain_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
</style>
