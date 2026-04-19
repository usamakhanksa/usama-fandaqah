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
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Intro Video')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateSettings">


                    <div class="flex border-b border-40 w-full">
                        <div class="w-1/6 py-6  px-8">
                            <label for="enable_about_us" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Enable Intro Video')}}</label>
                        </div>
                        <div class="py-6  w-1/2">
                            <label class="switch">
                                <input type="checkbox"  ref="checkbox"  v-model="enable_intro_video" @change="enableIntroVideo($event)">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Intro Text Ar')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input v-model="intro_text_ar" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Intro Text En')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input v-model="intro_text_en" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/3 py-6 px-8">
                                    <label for="contact_phone" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Youtube Video Url')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <input v-model="intro_video_url" type="text" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full">
                                    <small class="text-gray-500">{{__('Example')}} : https://www.youtube.com/watch?v=xxxxxxxxxxx </small>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex border-b border-40 w-full">
                        <div class="w-1/3 py-6 px-8">
                            <label class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Background')}}</label>
                        </div>
                        <div class="py-6 px-8 w-1/2">

                            <div class="avatar-edit mb-5" v-show="tempImageUrl">
                                <div class="avatar-edit-image">
                                    <img ref="editImage" :src="tempImageUrl" />
                                </div>
                            </div>

                            <div class="avatar-edit mb-5" v-show="intro_background !== null && tempImageUrl === null">
                                <div class="avatar-edit-image" v-if="intro_background">
                                    <template v-if="checkValidUrl(intro_background)">
                                        <img ref="editImage" :src="intro_background" />
                                    </template>
                                    <template v-else>
                                        <img ref="editImage" :src="app_url  +  intro_background" />
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

                    <div class="bg-30 flex p-4 justify-between">
                        <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                            {{ __('Save') }}
                        </button>
                        <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                    </div>
                </form>
            </div>
        </card>

        <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Address On The Map')" overlay-theme="dark" ref="example" class="delete_confirm">
            <div class="address_on_map_modal">
                <p>{{ __('Copy the video url from youtube') }} :</p>
                <img src="/images/map_address.png" alt="map">
            </div><!-- address_on_map_modal -->
        </sweet-modal>



    </div>
</template>

<script>

    import FileUpload from 'vue-upload-component'
    import ImageCompressor from "@xkeshi/image-compressor";
    export default {
        name: "Intro Video",
        components : {
            FileUpload
        },

        data() {
            return {
                team: Object,
                settings: Object,
                crumbs: [],
                locale : null,
                into_text_ar : null,
                into_text_en : null,
                intro_video_url : null,
                intro_background : null,
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
                enable_intro_video : false
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
                    text: 'Intro Video',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            enableIntroVideo(event){
                if(event.target.checked){
                    this.enable_intro_video = 1;
                }else{
                    this.enable_intro_video = 0;
                }
            },
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
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {

                        this.enable_intro_video = response.data.enable_intro_video;
                        this.intro_text_ar = response.data.intro_text.ar;
                        this.intro_text_en = response.data.intro_text.en;
                        this.intro_video_url = response.data.intro_video_url;
                        this.intro_background = response.data.intro_background;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            validateYouTubeUrl() {
                if (this.intro_video_url != undefined || this.intro_video_url != '') {
                    var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                    var match = this.intro_video_url.match(regExp);
                    if (match && match[2].length == 11) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
            updateSettings() {


                if(!this.intro_text_ar || !this.intro_text_en){
                    this.$toasted.error(Nova.app.__('Intro Text Is Required'), {
                        duration: 3000
                    });
                    return false;
                }

                if(!this.intro_video_url){
                    this.$toasted.error(Nova.app.__('Intro Video Url Is Required'), {
                        duration: 3000
                    });
                    return false;
                }

                if(!this.validateYouTubeUrl()){
                    this.$toasted.error(Nova.app.__('Invalid Youtube Video Url'), {
                        duration: 3000
                    });
                    return false;
                }

                let file = this.files[0];
                const data = new FormData();
                if(file){
                    data.append('file', file.file, file.name);
                }

                data.append('intro_text_ar', this.intro_text_ar);
                data.append('intro_text_en', this.intro_text_en);
                data.append('intro_video_url', this.intro_video_url);
                data.append('intro_background', this.intro_background);
                data.append('enable_intro_video', this.enable_intro_video);


                Nova.request()
                    .post("/nova-vendor/settings/intro-video-handler/"+this.team.id, data)
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

            openExampleUrl(){
                this.$refs.example.open();
            }
        },
        created() {
            this.locale = Nova.config.local;
            this.app_url = document.querySelector('meta[name="app_url"]').content;
        },
    }
</script>

<style lang="scss">
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
