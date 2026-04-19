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
        <card id="website_phrases">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Search Engine Optimization')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateContact">




                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="google_console_code" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Google Search Console Code')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="google_console_code" v-model="google_console_code" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                    <a href="https://search.google.com/search-console/not-verified?original_url=/search-console/ownership&original_resource_id" target="_blank"><small class="text-gray-500">{{__('Know more about google search console code')}}</small></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="google_analytics_code" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Google Search Analytics Code')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input id="google_analytics_code" v-model="google_analytics_code" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                    <a href="https://analytics.google.com/analytics/web/provision/#/provision" target="_blank"><small class="text-gray-500">{{__('Know more about google search analytics code')}}</small></a>
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
    import { VueEditor } from "vue2-editor";
    export default {
        name: "seo",
        components : {
            VueEditor
        },
        data() {
            return {
                team: Object,
                settings: Object,
                crumbs: [],
                locale : null,
                google_console_code : null,
                google_analytics_code : null,

                customToolbar: [
                    ["bold", "italic", "underline"],
                    [{ list: "ordered" }, { list: "bullet" }] ,
                    [
                        {
                            align: ""
                        }, {
                        align: "center"
                    }, {
                        align: "right"
                    }, {
                        align: "justify"
                    }
                    ]
                ]
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
                    text: 'Search Engine Optimization',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;
                        this.google_console_code = this.settings.google_console_code;
                        this.google_analytics_code = this.settings.google_analytics_code;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            updateContact() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {
                        google_console_code : this.google_console_code,
                        google_analytics_code: this.google_analytics_code,
                    })
                    .then(response => {
                        this.settings = response.data;
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

            openAddressmap(){
              this.$refs.addressmap.open();
            }
        },
        created() {
            this.locale = Nova.config.local;
        },
    }
</script>

<style lang="scss">
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
  button.addressmapbutton {
    display: inline-block;
    height: 13px;
    width: 13px;
    background-position: center center;
    background-size: 13px;
    background-repeat: no-repeat;
    outline: none;
    box-shadow: none;
    margin: 0 5px 0 0;
    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 524.235 524.235' height='512px' viewBox='0 0 524.235 524.235' width='512px'%3E%3Cg%3E%3Cpath d='m262.118 0c-144.53 0-262.118 117.588-262.118 262.118s117.588 262.118 262.118 262.118 262.118-117.588 262.118-262.118-117.589-262.118-262.118-262.118zm17.05 417.639c-12.453 2.076-37.232 7.261-49.815 8.303-10.651.882-20.702-5.215-26.829-13.967-6.143-8.751-7.615-19.95-3.968-29.997l49.547-136.242h-51.515c-.044-28.389 21.25-49.263 48.485-57.274 12.997-3.824 37.212-9.057 49.809-8.255 7.547.48 20.702 5.215 26.829 13.967 6.143 8.751 7.615 19.95 3.968 29.997l-49.547 136.242h51.499c.01 28.356-20.49 52.564-48.463 57.226zm15.714-253.815c-18.096 0-32.765-14.671-32.765-32.765 0-18.096 14.669-32.765 32.765-32.765s32.765 14.669 32.765 32.765c0 18.095-14.668 32.765-32.765 32.765z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%231273EB'/%3E%3C/g%3E%3C/svg%3E%0A");
    [dir="ltr"] & {
      margin: 0 0 0 5px;
    } /* ltr */
  }
  .address_on_map_modal {
    padding: 15px;
    text-align: center;
    p {
      display: block;
      margin: 0 auto 15px;
      color: #000;
      font-size: 15px;
      [dir="ltr"] & {
        font-size: 14px;
      } /* ltr */
    } /* p */
    img {
      display: block;
      margin: 0 auto;
      max-width: 100%;
      width: auto;
      height: auto;
      max-height: 100%;
    } /* img */
  } /* address_on_map_modal */
</style>
