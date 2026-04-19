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
        <card id="website_phrases" class="relative">
            <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>

            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('About Us')}}</div>
            <div class="card-content">
                <form @submit.prevent="storeOrUpdateAboutUs">

                    <div class="form-left">

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/6 py-6  px-8">
                                <label for="enable_about_us" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Activate About Us')}}</label>
                            </div>
                            <div class="py-6  w-1/2">
                                <label class="switch">
                                    <input type="checkbox" id="enable_about_us" ref="checkbox"  v-model="enable_about_us" @change="enableAboutUsChanged($event)">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label class="inline-block text-80 pt-2 leading-tight mb-2">{{__('About Us Image')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">

                                <div class="avatar-edit mb-5" v-show="tempImageUrl">
                                    <div class="avatar-edit-image">
                                        <img ref="editImage" :src="tempImageUrl" />
                                    </div>
                                </div>

                                <div class="avatar-edit mb-5" v-show="about_us_image !== null && tempImageUrl === null">
                                    <div class="avatar-edit-image" v-if="about_us_image">
                                        <template v-if="checkValidUrl(about_us_image)">
                                            <img ref="editImage" :src="about_us_image" />
                                        </template>
                                        <template v-else>
                                            <img ref="editImage" :src="app_url  +  about_us_image" />
                                        </template>
                                    </div>
                                </div>
                                <span class="form-file">
                                    <file-upload
                                            class="form-file-btn btn btn-default btn-primary select-none"
                                            :post-action="postAction"
                                            :extensions="extensions"
                                            :accept="accept"
                                            :multiple="multiple"
                                            :directory="directory"
                                            :size="size || 0"
                                            :thread="thread < 1 ? 1 : (thread > 5 ? 5 : thread)"
                                            :headers="headers"
                                            :data="data"
                                            :drop="drop"
                                            :drop-directory="dropDirectory"
                                            :add-index="addIndex"
                                            v-model="files"
                                            @input-filter="inputFilter"
                                            @input-file="inputFile"
                                            ref="upload">
                                            <i class="fa fa-plus"></i>
                                            {{__('Select Image')}}
                                    </file-upload>
                                </span>
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label for="about_us_title_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title Ar')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <input id="about_us_title_ar" v-model="about_us_title_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label for="about_us_title_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title En')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <input id="about_us_title_en" v-model="about_us_title_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label for="about_us_content_ar" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Content Ar')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <vue-editor id="about_us_content_ar" v-model="about_us_content_ar" :editor-toolbar="customToolbar" />
                            </div>
                        </div>

                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/3 py-6 px-8">
                                <label for="about_us_content_en" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Content En')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <vue-editor id="about_us_content_en" v-model="about_us_content_en" :editor-toolbar="customToolbar" />
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

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    import { VueEditor } from "vue2-editor";
    import FileUpload from 'vue-upload-component'
    import ImageCompressor from "@xkeshi/image-compressor";
    export default {
        name: "About Us Section",
        components : {
            VueEditor,
            FileUpload,
            Loading
        },
        data() {
            return {
                team: Object,
                settings: Object,
                crumbs: [],
                locale : null,
                about_us_image : null,
                about_us_title_ar : null,
                about_us_title_en : null,
                about_us_content_ar : null,
                about_us_content_en : null,
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
                files: [],
                accept: 'image/png,image/gif,image/jpeg,image/webp',
                extensions: 'gif,jpg,jpeg,png,webp',
                minSize: 1024,
                size: 1024 * 1024 * 10,
                multiple: false,
                directory: false,
                drop: false,
                dropDirectory: false,
                addIndex: false,
                thread: 1,
                name: 'file',
                postAction: '/nova-vendor/settings/about-us-upload-handler',
                headers: {
                    'X-Csrf-Token': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'multipart/form-data'
                },
                data: {
                    '_csrf_token': $('meta[name="csrf-token"]').attr('content'),
                },

                uploadAuto: false,
                isOption: false,

                addData: {
                    show: false,
                    name: '',
                    type: '',
                    content: '',
                },
                editFile: {
                    show: false,
                    name: '',
                },

                tempImageUrl : null,
                app_url : null,
                enable_about_us : 0,
                checked : false,
                isLoading : false

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
                    text: 'About Us',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            checkValidUrl(url){
                let pattern = /^((http|https|ftp):\/\/)/;

                if(pattern.test(url)) {
                    return true;
                }else{
                    return false;
                }
            },
            inputFilter(newFile, oldFile, prevent) {

                if (newFile && !oldFile) {
                    // Before adding a file
                    // 添加文件前

                    // Filter system files or hide files
                    // 过滤系统文件 和隐藏文件
                    if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
                        return prevent()
                    }

                    // Filter php html js file
                    // 过滤 php html js 文件
                    if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
                        return prevent()
                    }

                    // Automatic compression
                    // 自动压缩
                    if (newFile.file && newFile.type.substr(0, 6) === 'image/' && this.autoCompress > 0 && this.autoCompress < newFile.size) {
                        newFile.error = 'compressing'
                        const imageCompressor = new ImageCompressor(null, {
                            convertSize: Infinity,
                            maxWidth: 512,
                            maxHeight: 512,
                        })
                        imageCompressor.compress(newFile.file)
                            .then((file) => {
                                this.$refs.upload.update(newFile, { error: '', file, size: file.size, type: file.type })
                            })
                            .catch((err) => {
                                this.$refs.upload.update(newFile, { error: err.message || 'compress' })
                            })
                    }
                }


                if (newFile && (!oldFile || newFile.file !== oldFile.file)) {

                    // Create a blob field
                    // 创建 blob 字段
                    newFile.blob = ''
                    let URL = window.URL || window.webkitURL
                    if (URL && URL.createObjectURL) {
                        newFile.blob = URL.createObjectURL(newFile.file)
                    }

                    // Thumbnails
                    // 缩略图
                    newFile.thumb = ''
                    if (newFile.blob && newFile.type.substr(0, 6) === 'image/') {
                        newFile.thumb = newFile.blob
                    }

                    this.tempImageUrl = newFile.blob;
                    this.about_us_image = newFile.blob;
                }
            },
            // add, update, remove File Event
            inputFile(newFile, oldFile) {

                if (newFile && oldFile) {
                    // update

                    if (newFile.active && !oldFile.active) {
                        // beforeSend


                        // min size
                        if (newFile.size >= 0 && this.minSize > 0 && newFile.size < this.minSize) {
                            this.$refs.upload.update(newFile, { error: 'size' })
                        }
                    }

                    if (newFile.progress !== oldFile.progress) {
                        // progress
                    }

                    if (newFile.error && !oldFile.error) {
                        // error
                    }

                    if (newFile.success && !oldFile.success) {
                        // success
                        // this step is required to handle redirection
                        this.handler = this.handler + 1;

                        // if(this.handler === this.files.length){
                        //
                        //     this.files = [];
                        //     this.$toasted.success(Nova.app.__('Slider Images have been attached successfully'), {
                        //         duration: 3000
                        //     })
                        //     this.$router.push({path: '/settings/website'});
                        // }

                    }


                }


                if (!newFile && oldFile) {
                    // remove
                    if (oldFile.success && oldFile.response.id) {
                        // $.ajax({
                        //   type: 'DELETE',
                        //   url: '/upload/delete?id=' + oldFile.response.id,
                        // })
                    }
                }


                // Automatically activate upload
                if (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if (this.uploadAuto && !this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }


            },
            getSettings() {
                this.isLoading = true
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {

                        this.settings = response.data;
                        this.about_us_title_ar = this.settings.about_us_title ? this.settings.about_us_title.ar : '';
                        this.about_us_title_en = this.settings.about_us_title ? this.settings.about_us_title.en : '';
                        this.about_us_content_ar = this.settings.about_us_content ? this.settings.about_us_content.ar : '';
                        this.about_us_content_en = this.settings.about_us_content ? this.settings.about_us_content.en : '';
                        this.about_us_image = this.settings.about_us_image;
                        this.enable_about_us = this.settings.enable_about_us;

                        this.isLoading = false
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            storeOrUpdateAboutUs() {


                // validation step

                // if(this.enable_about_us){
                //
                //
                // }



                if(!this.about_us_image){
                    this.$toasted.error(Nova.app.__('About Us Image Is Required'), {
                        duration: 3000
                    });
                    return false;
                }


                if(!this.about_us_title_ar || !this.about_us_title_en){
                    this.$toasted.error(Nova.app.__('About Us Title Is Required'), {
                        duration: 3000
                    });
                    return false;
                }

                if(!this.about_us_content_ar || !this.about_us_content_en){
                    this.$toasted.error(Nova.app.__('About Us Content Is Required'), {
                        duration: 3000
                    });
                    return false;
                }


                this.isLoading = true;

                let file = this.files[0];
                const data = new FormData();
                if(file){
                    data.append('file', file.file, file.name);
                }
                data.append('enable_about_us', this.enable_about_us);
                data.append('about_us_title_ar', this.about_us_title_ar);
                data.append('about_us_title_en', this.about_us_title_en);
                data.append('about_us_content_ar', this.about_us_content_ar);
                data.append('about_us_content_en', this.about_us_content_en);

                Nova.request()
                    .post("/nova-vendor/settings/about-us-upload-handler/"+this.team.id, data)
                    .then(response => {
                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('About Us Data has been saved successfully'), {
                            duration: 3000
                        });
                        this.isLoading = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            },
            enableAboutUsChanged(event){
                if(event.target.checked){
                    this.enable_about_us = 1;
                }else{
                    this.enable_about_us = 0;
                }
            }
        },
        created() {
            this.locale = Nova.config.local;
            this.app_url = document.querySelector('meta[name="app_url"]').content + '/';

        },
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
</style>
