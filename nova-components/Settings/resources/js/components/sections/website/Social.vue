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
        <card  id="socialmedia_website">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Social media')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateSocial">
                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="enable_social" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Activate Social Sites')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <label class="switch">
                                        <input type="checkbox" id="enable_social" v-model="enable_social">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="social_icons_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Color Icons')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="social_icons_color" v-model="social_icons_color" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="social_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Background Color')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="social_background_color" v-model="social_background_color" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                     <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="social_facebook_url" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Facebook')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="social_facebook_url"  v-model="social_facebook_url" type="text" class="w-full block form-control form-input form-input-bordered text-lg text-90" :placeholder="__('the link')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="social_instagram_url" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Instagram')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="social_instagram_url"  v-model="social_instagram_url" type="text" class="w-full block form-control form-input form-input-bordered text-lg text-90" :placeholder="__('the link')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="social_twitter_url" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Twitter')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="social_twitter_url"  v-model="social_twitter_url" type="text" class="w-full block form-control form-input form-input-bordered text-lg text-90" :placeholder="__('the link')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="social_snapchat_url" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Snapchat')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="social_snapchat_url"  v-model="social_snapchat_url" type="text" class="w-full block form-control form-input form-input-bordered text-lg text-90" :placeholder="__('the link')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="social_youtube_url" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Youtube')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="social_youtube_url"  v-model="social_youtube_url" type="text" class="w-full block form-control form-input form-input-bordered text-lg text-90" :placeholder="__('the link')">
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
        </card>
    </div>
</template>

<script>
    import { Sketch } from 'vue-color'

    export default {
        name: "Social",
        components: {
            'sketch-picker': Sketch
        },
         data() {
            return {
                team: Object,
                settings: Object,
                installed: false,
                reset: true,
                crumbs: [],
                locale : null,
                social_facebook_url : null,
                social_youtube_url: null,
                social_snapchat_url: null,
                social_twitter_url: null,
                social_instagram_url: null,
                social_icons_color: '',
                social_background_color: '',
                enable_social: false
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
                    text: 'Social media',
                    to: '#',
                }
            ],
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            updateSocial() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {
                        social_youtube_url: this.social_youtube_url,
                        social_snapchat_url: this.social_snapchat_url,
                        social_twitter_url: this.social_twitter_url,
                        social_instagram_url: this.social_instagram_url,
                        social_facebook_url : this.social_facebook_url,
                        // social_icons_color: this.social_icons_color.hex,
                        // social_background_color: this.social_background_color.hex,
                        enable_social: this.enable_social,
                    })
                    .then(response => {
                        this.settings = response.data;
                        this.social_youtube_url = response.data.social_youtube_url;
                        this.social_snapchat_url = response.data.social_snapchat_url;
                        this.social_twitter_url = response.data.social_twitter_url;
                        this.social_instagram_url = response.data.social_instagram_url;
                        this.social_facebook_url = response.data.social_facebook_url;
                        // this.social_icons_color = response.data.social_icons_color;
                        // this.social_background_color = response.data.social_background_color;
                        this.enable_social = response.data.enable_social;
                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('Success'), {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            },
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;
                        this.social_youtube_url = response.data.social_youtube_url;
                        this.social_snapchat_url = response.data.social_snapchat_url;
                        this.social_twitter_url = response.data.social_twitter_url;
                        this.social_instagram_url = response.data.social_instagram_url;
                        this.social_facebook_url = response.data.social_facebook_url;
                        // this.social_icons_color = response.data.social_icons_color;
                        // this.social_background_color = response.data.social_background_color;
                        this.enable_social = response.data.enable_social;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        }
    }
</script>

<style scoped>
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
    #socialmedia_website .flex.border-b.border-40.w-full {display: block;}
    #socialmedia_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #socialmedia_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #socialmedia_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #socialmedia_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
@media (min-width: 481px) and (max-width: 767px) {
    #socialmedia_website .flex.border-b.border-40.w-full {display: block;}
    #socialmedia_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #socialmedia_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #socialmedia_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #socialmedia_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
</style>
