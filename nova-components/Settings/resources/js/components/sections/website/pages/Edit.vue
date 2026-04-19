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

        <card id="website_phrases">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Edit Page')}}</div>
            <div class="card-content relative">

                <loading :active.sync="isLoading" :is-full-page="false"></loading>

                <div class="form-left">
                    <div class="flex border-b border-40">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Status')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <label class="switch">
                                    <input type="checkbox" id="enable_gallery" v-model="page.status" :checked="page.status">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-left">
                    <div class="flex border-b border-40">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title AR')}} <span class="text-red-500">*</span></label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <input  v-model="page.title.ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-left">
                    <div class="flex border-b border-40">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title EN')}} <span class="text-red-500">*</span></label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <input  v-model="page.title.en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                <small class="text-gray-500" v-show="page.title.en">{{__('Slug')}} : {{page.slug}}</small>
                                <p class="text-red-500" v-show="slug_error_msg">{{slug_error_msg}}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-left">
                    <div class="flex border-b border-40 w-full">
                        <div class="w-1/3 py-6 px-8">
                            <label class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Content Ar')}} <span class="text-red-500">*</span></label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <vue-editor v-model="page.content.ar"></vue-editor>
                        </div>
                    </div>

                    <div class="flex border-b border-40 w-full">
                        <div class="w-1/3 py-6 px-8">
                            <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Content En')}} <span class="text-red-500">*</span></label>
                        </div>
                        <div class="py-6 px-8 w-1/2">
                            <vue-editor v-model="page.content.en"></vue-editor>
                        </div>
                    </div>
                </div>


                <div class="form-left">
                    <div class="flex border-b border-40">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Order of appearance')}} <span class="text-red-500">*</span></label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <input  v-model="page.order" type="number"  class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-30 flex p-4 justify-between">
                    <button @click="updatePage" type="button" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                        {{ __('Save') }}
                    </button>
                    <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                </div>
            </div>
        </card>
    </div>
</template>

<script>
    import { VueEditor } from "vue2-editor";
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "Edit",
        components : {
            VueEditor,
            Loading
        },
        data(){
            return {
                page : {
                    id : null,
                    title : {
                        ar: null,
                        en: null
                    },
                    slug : null,
                    content : {
                        ar : null,
                        en :null
                    },
                    status : 0,
                    order : 1,
                },
                crumbs : [],
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
                ],
                isLoading : false,
                slug_error_msg : null

            }
        },
        methods : {
            getPageData(id){
                this.isLoading = true;
                Nova.request().get(`/nova-vendor/settings/get-page-details/${id}`)
                    .then(response => {
                        this.page = {
                         id : this.$route.params.id,
                         title : {
                             ar :response.data.title['ar'] ,
                             en :response.data.title['en'] ,

                         },
                         content : {
                            ar : response.data.content['ar'],
                            en : response.data.content['en'],

                         },
                         slug : response.data.slug,
                         status : response.data.status,
                         order: response.data.order

                        };
                        this.isLoading = false;
                    });
            },
            scrollToTop() {
                window.scrollTo(0,0);
            },
            updatePage(){
                this.isLoading = true;

                if(!this.page.title.ar ||
                    !this.page.title.en ||
                    !this.page.content.ar ||
                    !this.page.content.en ||
                    !this.page.order
                ){
                    this.$toasted.error(Nova.app.__('Please fill are the required fields'), {
                        duration: 3000
                    });
                    this.isLoading = false;
                    return false;
                }



                Nova.request()
                    .put("/nova-vendor/settings/edit-page" , this.page)
                        .then(response => {
                            this.isLoading = false;
                            if(response.data.status === 'error'){
                                this.slug_error_msg = response.data.errors.slug[0];
                                this.$toasted.error(this.slug_error_msg , {
                                    duration: 3000
                                });
                                this.scrollToTop();
                                return false;
                            }

                            this.$router.push(`/settings/website/pages/${this.$route.params.id}/details`);
                            this.$toasted.success(Nova.app.__('Page updated successfully'), {
                                duration: 3000
                            })
                        })
            },
            goBack() {
                this.$router.push({path: '/settings/website/pages'})
            },
        },
        watch : {
            page: {
                handler(val){
                    let slug = "";
                    // Change to lower case
                    let titleLower = val.title.en.toLowerCase();
                    // Letter "e"
                    slug = titleLower.replace(/e|é|è|ẽ|ẻ|ẹ|ê|ế|ề|ễ|ể|ệ/gi, 'e');
                    // Letter "a"
                    slug = slug.replace(/a|á|à|ã|ả|ạ|ă|ắ|ằ|ẵ|ẳ|ặ|â|ấ|ầ|ẫ|ẩ|ậ/gi, 'a');
                    // Letter "o"
                    slug = slug.replace(/o|ó|ò|õ|ỏ|ọ|ô|ố|ồ|ỗ|ổ|ộ|ơ|ớ|ờ|ỡ|ở|ợ/gi, 'o');
                    // Letter "u"
                    slug = slug.replace(/u|ú|ù|ũ|ủ|ụ|ư|ứ|ừ|ữ|ử|ự/gi, 'u');
                    // Letter "d"
                    slug = slug.replace(/đ/gi, 'd');
                    // Trim the last whitespace
                    slug = slug.replace(/\s*$/g, '');
                    // Change whitespace to "-"
                    slug = slug.replace(/\s+/g, '-');
                    val.slug = slug;
                },
                deep: true
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
                    text: 'Introductory pages',
                    to: '/settings/website/pages',
                },
                {
                    text: this.$route.params.id,
                    to: `/settings/website/pages/${this.$route.params.id}/details`,
                },
                {
                    text: 'Edit Page',
                    to: '#',
                }
            ];
            this.getPageData(this.$route.params.id);
        }
    }
</script>

<style lang="scss" scoped>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 26px;
        input {
            opacity: 0;
            width: 100% !important;
            height: 100% !important;
            z-index: 99;
            position: relative;
            cursor: pointer;
            &:checked ~ {
                .slider {
                    background-color: #21b978;
                    &:before {
                        -webkit-transform: translateX(33px);
                        -ms-transform: translateX(33px);
                        transform: translateX(33px);
                    } /* before */
                } /* slider */
            } /* checked */
            &:focus {
                .slider {
                    box-shadow: 0 0 1px #21b978;
                } /* slider */
            } /* focus */
        } /* input */
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
            &:before {
                position: absolute;
                content: "";
                height: 20px;
                width: 20px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                -webkit-transition: .4s;
                transition: .4s;
            } /* before */
            &.round {
                border-radius: 34px;
                &:before {
                    border-radius: 50%;
                } /* before */
            } /* round */
        } /* slider */
    } /* switch */
</style>
