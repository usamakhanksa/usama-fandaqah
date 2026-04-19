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
        <card  id="banner_website">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Main banner of the facade')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateBanner">

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="banner_file" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Banner')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <div class="avatar-edit" v-show="!edit">
                                        <div class="avatar-edit-image">
                                            <img ref="editImage" :src="files.length ? files[0].url : banner_file_url" />
                                        </div>
                                    </div>

                                    <div class="avatar-edit" v-show="files.length && edit">
                                        <div class="avatar-edit-image" v-if="files.length">
                                            <img ref="editImage" :src="files[0].url" />
                                        </div>
                                    </div>
                                    <span class="form-file">
                                        <file-upload
                                            extensions="gif,jpg,jpeg,png,webp"
                                            accept="image/png,image/gif,image/jpeg,image/webp"
                                            name="avatar"
                                            class="form-file-btn btn btn-default btn-primary select-none"
                                            v-model="files"
                                            post-action="/nova-vendor/settings/upload-file"
                                            id="banner_file"
                                            ref="upload"
                                            :drop="!edit"
                                            @input-file="inputFile"
                                            @input-filter="inputFilter"
                                        >
                                            {{ __('Image') }}
                                        </file-upload>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="banner_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Text Color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="banner_text_color" v-model="banner_text_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="banner_search_box_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search box background color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="banner_search_box_background_color" v-model="banner_search_box_background_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="banner_search_button_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search button text color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="banner_search_button_text_color" v-model="banner_search_button_text_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="banner_search_button_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Search button background color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="banner_search_button_background_color" v-model="banner_search_button_background_color" />
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
    import { Sketch } from 'vue-color';
    import VueUploadComponent from 'vue-upload-component';

    export default {
        name: "Banner",
        components: {
            'sketch-picker': Sketch,
            'file-upload': VueUploadComponent
        },
         data() {
            return {
                files: [],
                team: Object,
                installed: false,
                reset: true,
                crumbs: [],
                locale : null,
                edit: false,
                banner_file: null,
                banner_text_color: '',
                banner_search_box_background_color: '',
                banner_search_button_text_color: '',
                banner_search_button_background_color: '',
                banner_file_url: '',
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
                    text: 'Main banner of the facade',
                    to: '#',
                }
            ],
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            editSave() {
                this.edit = false;
                let file = this.files[0];

                const data = new FormData();
                data.append('file', file.file, file.name);

                Nova.request()
                    .post("/nova-vendor/settings/upload-file", data)
                    .then(response => {
                        this.banner_file = response.data;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            inputFile(newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    this.$nextTick(function () {
                        this.edit = true
                    })
                }
                if (!newFile && oldFile) {
                    this.edit = false
                }
                this.editSave();
            },
            inputFilter(newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    if (!/\.(gif|jpg|jpeg|png|webp)$/i.test(newFile.name)) {
                        this.$toasted.success(Nova.app.__('Your choice is not a picture'), {
                            duration: 3000
                        });
                        return prevent()
                    }
                }
                if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
                    newFile.url = ''
                    let URL = window.URL || window.webkitURL
                    if (URL && URL.createObjectURL) {
                        newFile.url = URL.createObjectURL(newFile.file)
                    }
                }
            },
            updateBanner() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {
                        banner_file: this.banner_file,
                        banner_text_color: this.banner_text_color.hex,
                        banner_search_box_background_color: this.banner_search_box_background_color.hex,
                        banner_search_button_text_color: this.banner_search_button_text_color.hex,
                        banner_search_button_background_color: this.banner_search_button_background_color.hex,
                    })
                    .then(response => {
                        this.settings = response.data;

                        this.banner_file = this.settings.banner_file;
                        this.banner_text_color = this.settings.banner_text_color;
                        this.banner_search_box_background_color = this.settings.banner_search_box_background_color;
                        this.banner_search_button_text_color = this.settings.banner_search_button_text_color;
                        this.banner_search_button_background_color = this.settings.banner_search_button_background_color;
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

                        this.banner_file = this.settings.banner_file;
                        this.banner_text_color = this.settings.banner_text_color;
                        this.banner_search_box_background_color = this.settings.banner_search_box_background_color;
                        this.banner_search_button_text_color = this.settings.banner_search_button_text_color;
                        this.banner_search_button_background_color = this.settings.banner_search_button_background_color;

                        if (this.banner_file && !this.is_url(this.banner_file)) {
                            this.banner_file_url = location.origin + this.banner_file;
                        }

                        if (this.is_url(this.banner_file)) {
                            this.banner_file_url = this.banner_file;
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            is_url(str) {
                let regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
                return regexp.test(str);
            }
        },
    }
</script>

<style scoped>
@media (min-width: 320px) and (max-width: 480px) {
    #banner_website .flex.border-b.border-40.w-full {display: block;}
    #banner_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #socialmedia_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #banner_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #banner_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
@media (min-width: 481px) and (max-width: 767px) {
    #banner_website .flex.border-b.border-40.w-full {display: block;}
    #banner_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #socialmedia_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #banner_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #banner_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
</style>
