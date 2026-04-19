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
        <card  id="logo_website">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Logo and basic colors')}}</div>
            <div class="card-content">
                <form @submit.prevent="updateLogo">

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="logo" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Basic logo')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <div class="avatar-edit" v-show="!edit_logo">
                                        <div class="avatar-edit-image">
                                            <img ref="editLogo" :src="logo_files.length ? logo_files[0].url : logo_file_url" />
                                        </div>
                                    </div>

                                    <div class="avatar-edit" v-show="logo_files.length && edit_logo">
                                        <div class="avatar-edit-image" v-if="logo_files.length">
                                            <img ref="editLogo" :src="logo_files[0].url" />
                                        </div>
                                    </div>
                                    <span class="form-file">
                                        <file-upload
                                            extensions="gif,jpg,jpeg,png,webp"
                                            accept="image/png,image/gif,image/jpeg,image/webp"
                                            name="logo_path"
                                            class="form-file-btn btn btn-default btn-primary select-none"
                                            v-model="logo_files"
                                            post-action="/nova-vendor/settings/upload-file"
                                            id="logo"
                                            ref="uploadLogo"
                                            :drop="!edit_logo"
                                            @input-file="inputFileLogo"
                                            @input-filter="inputFilter"
                                        >
                                            {{ __('Select Logo') }}
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
                                    <label for="favicon" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Favicon')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <div class="avatar-edit" v-show="!edit_favicon">
                                        <div class="avatar-edit-image">
                                            <img ref="editFavicon" :src="favicon_files.length ? favicon_files[0].url : favicon_file_url" />
                                        </div>
                                    </div>

                                    <div class="avatar-edit" v-show="favicon_files.length && edit_favicon">
                                        <div class="avatar-edit-image" v-if="favicon_files.length">
                                            <img ref="editFavicon" :src="favicon_files[0].url" />
                                        </div>
                                    </div>
                                    <span class="form-file">
                                        <file-upload
                                            extensions="gif,jpg,jpeg,png,webp"
                                            accept="image/png,image/gif,image/jpeg,image/webp"
                                            name="favicon_path"
                                            class="form-file-btn btn btn-default btn-primary select-none"
                                            v-model="favicon_files"
                                            post-action="/nova-vendor/settings/upload-favicon"
                                            id="favicon"
                                            ref="uploadFavicon"
                                            :drop="!edit_favicon"
                                            @input-file="inputFileFavicon"
                                            @input-filter="inputFilter"
                                        >
                                            {{ __('Select Favicon') }}
                                        </file-upload>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Website Background Color')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="background_color" v-model="background_color" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="header_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Header Background Color')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="header_background_color" v-model="header_background_color" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->


<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="basic_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Basic Website Background Color')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="basic_text_color" v-model="website_background" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="basic_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Basic text color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="basic_text_color" v-model="basic_text_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="sub_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Subtext color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="sub_text_color" v-model="sub_text_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="hover_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Hover Text color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="hover_text_color" v-model="hover_text_color" />
                                </div>
                            </div>
                        </div>
                    </div>

<!--                     <div class="title-card title-card p-3 bg-gray-200 text-xl border-b-2 border-t-2  border-gray-300">{{__('Footer')}}</div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="footer_text_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('footer text color')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="footer_text_color" v-model="footer_text_color" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="footer_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('footer background color')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <sketch-picker id="footer_background_color" v-model="footer_background_color" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div class="form-left">-->
<!--                        <div class="flex border-b border-40">-->
<!--                            <div class="flex border-b border-40 w-full">-->
<!--                                <div class="w-1/5 py-6 px-8">-->
<!--                                    <label for="footer_logo" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Logo Footer')}}</label>-->
<!--                                </div>-->
<!--                                <div class="py-6 px-8 w-1/2">-->
<!--                                    <div class="avatar-edit" v-show="!edit_footer_logo">-->
<!--                                        <div class="avatar-edit-image">-->
<!--                                            <img ref="editFooterLogo" :src="footer_logo_files.length ? footer_logo_files[0].url : footer_logo_file_url" />-->
<!--                                        </div>-->
<!--                                    </div>-->

<!--                                    <div class="avatar-edit" v-show="footer_logo_files.length && edit_footer_logo">-->
<!--                                        <div class="avatar-edit-image" v-if="footer_logo_files.length">-->
<!--                                            <img ref="editFooterLogo" :src="footer_logo_files[0].url" />-->
<!--                                        </div>-->
<!--                                    </div>-->
<!--                                    <span class="form-file">-->
<!--                                        <file-upload-->
<!--                                            extensions="gif,jpg,jpeg,png,webp"-->
<!--                                            accept="image/png,image/gif,image/jpeg,image/webp"-->
<!--                                            name="footer_logo_path"-->
<!--                                            class="form-file-btn btn btn-default btn-primary select-none"-->
<!--                                            v-model="footer_logo_files"-->
<!--                                            post-action="/nova-vendor/settings/upload-file"-->
<!--                                            id="footer_logo"-->
<!--                                            ref="uploadFooterLogo"-->
<!--                                            :drop="!edit_footer_logo"-->
<!--                                            @input-file="inputFileFooterLogo"-->
<!--                                            @input-filter="inputFilterFooter"-->
<!--                                        >-->
<!--                                            {{ __('Image') }}-->
<!--                                        </file-upload>-->
<!--                                    </span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->

                     <div class="title-card title-card p-3 bg-gray-200 text-xl border-b-2 border-t-2  border-gray-300">{{__('Buttons')}}</div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="button_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Button text color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="button_color" v-model="button_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="button_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Button Background color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="button_background_color" v-model="button_background_color" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-left">
                        <div class="flex border-b border-40">
                            <div class="flex border-b border-40 w-full">
                                <div class="w-1/5 py-6 px-8">
                                    <label for="button_background_color" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Button Hover & Click Color')}}</label>
                                </div>
                                <div class="py-6 px-8 w-1/2">
                                    <sketch-picker id="button_hover_click_color" v-model="button_hover_click_color" />
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
    import {Sketch} from 'vue-color'
    import VueUploadComponent from 'vue-upload-component';

    export default {
        name: "Logo",
        components: {
            'sketch-picker': Sketch,
            'file-upload': VueUploadComponent
        },
        data() {
            return {
                loading: false,
                team: Object,
                installed: false,
                reset: true,
                crumbs: [],
                locale: null,
                logo: null,
                favicon: null,
                background_color: '',
                header_background_color: '',
                basic_text_color: '',
                sub_text_color: '',
                hover_text_color: '',
                footer_text_color: '',
                footer_background_color: '',
                footer_logo: null,
                button_color: '',
                button_background_color: '',
                button_hover_click_color : '',
                logo_files: [],
                favicon_files: [],
                footer_logo_files: [],
                edit_logo: false,
                edit_favicon: false,
                edit_footer_logo: false,
                logo_file_url: null,
                favicon_file_url: null,
                footer_logo_file_url: null,
                website_background : '#ffffff'
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
                    text: 'Logo and basic colors',
                    to: '#',
                }
            ],
                this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            editSaveFooterLogo() {
                this.loading = true;
                this.edit_footer_logo = false;
                let file = this.footer_logo_files[0];

                const data = new FormData();
                data.append('file', file.file, file.name);

                Nova.request()
                    .post("/nova-vendor/settings/upload-file", data)
                    .then(response => {
                        this.footer_logo = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            editSaveLogo() {
                this.loading = true;
                this.edit_logo = false;
                let file = this.logo_files[0];

                const data = new FormData();
                data.append('file', file.file, file.name);

                Nova.request()
                    .post("/nova-vendor/settings/upload-file", data)
                    .then(response => {
                        this.logo = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            editSaveFavicon() {
                this.loading = true;
                this.edit_favicon = false;
                let file = this.favicon_files[0];

                const data = new FormData();
                data.append('file', file.file, file.name);

                Nova.request()
                    .post("/nova-vendor/settings/upload-favicon", data)
                    .then(response => {
                        this.favicon = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            inputFileFooterLogo(newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    this.$nextTick(function () {
                        this.edit_footer_logo = true
                    })
                }
                if (!newFile && oldFile) {
                    this.edit_footer_logo = false
                }
                this.editSaveFooterLogo();
            },
            inputFileLogo(newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    this.$nextTick(function () {
                        this.edit_logo = true
                    })
                }
                if (!newFile && oldFile) {
                    this.edit_logo = false
                }
                this.editSaveLogo();
            },
            inputFileFavicon(newFile, oldFile, prevent) {
                if (newFile && !oldFile) {
                    this.$nextTick(function () {
                        this.edit_favicon = true
                    })
                }
                if (!newFile && oldFile) {
                    this.edit_favicon = false
                }
                this.editSaveFavicon();
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
            inputFilterFooter(newFile, oldFile, prevent) {
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
            goBack() {
                this.$router.push({path: '/settings/website'})
            },
            updateLogo() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/" + this.team.id, {
                        logo: this.logo,
                        favicon: this.favicon,
                        // background_color: this.background_color.hex,
                        website_background: this.website_background.hex,
                        basic_text_color: this.basic_text_color.hex,
                        sub_text_color: this.sub_text_color.hex,
                        hover_text_color: this.hover_text_color.hex,
                        // footer_text_color: this.footer_text_color.hex,
                        // footer_background_color: this.footer_background_color.hex,
                        // footer_logo: this.footer_logo,
                        button_color: this.button_color.hex,
                        button_background_color: this.button_background_color.hex,
                        button_hover_click_color: this.button_hover_click_color ? this.button_hover_click_color.hex : null,
                        // header_background_color: this.header_background_color.hex,
                    })
                    .then(response => {
                        this.settings = response.data;

                        this.logo = this.settings.logo;
                        this.favicon = this.settings.favicon;
                        // this.background_color = this.settings.background_color;
                        this.website_background = this.settings.website_background;
                        this.basic_text_color = this.settings.basic_text_color;
                        this.sub_text_color = this.settings.sub_text_color;
                        this.hover_text_color = this.settings.hover_text_color;
                        // this.footer_text_color = this.settings.footer_text_color;
                        // this.footer_background_color = this.settings.footer_background_color;
                        // this.footer_logo = this.settings.footer_logo;
                        this.button_color = this.settings.button_color;
                        this.button_background_color = this.settings.button_background_color;
                        this.button_hover_click_color = this.settings.button_hover_click_color;
                        // this.header_background_color = this.settings.header_background_color;
                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('Success'), {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/" + this.team.id, {})
                    .then(response => {
                        this.settings = response.data;

                        this.logo = this.settings.logo;
                        this.favicon = this.settings.favicon;
                        // this.background_color = this.settings.background_color;
                        this.website_background = this.settings.website_background;
                        this.basic_text_color = this.settings.basic_text_color;
                        this.sub_text_color = this.settings.sub_text_color;
                        this.hover_text_color = this.settings.hover_text_color;
                        // this.footer_text_color = this.settings.footer_text_color;
                        // this.footer_background_color = this.settings.footer_background_color;
                        // this.footer_logo = this.settings.footer_logo;
                        this.button_color = this.settings.button_color;
                        this.button_background_color = this.settings.button_background_color;
                        this.button_hover_click_color = this.settings.button_hover_click_color ;
                        // this.header_background_color = this.settings.header_background_color;

                        if (this.logo && !this.is_url(this.logo)) {
                            this.logo_file_url = location.origin + this.logo;
                        }


                        if (this.is_url(this.logo)) {
                            this.logo_file_url = this.logo;
                        }

                        if (this.favicon && !this.is_url(this.favicon)) {
                            this.favicon_file_url = location.origin + this.favicon;
                        }


                        if (this.is_url(this.favicon)) {
                            this.favicon_file_url = this.favicon;
                        }


                        // if (this.footer_logo && !this.is_url(this.footer_logo)) {
                        //     this.footer_logo_file_url = location.origin + this.footer_logo;
                        // }
                        // if (this.is_url(this.footer_logo)) {
                        //     this.footer_logo_file_url = this.footer_logo;
                        // }
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            is_url(str) {
                let regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
                return regexp.test(str);
            }
        }
    }
</script>

<style scoped>

    .avatar-edit{
        margin-bottom: 10px;
    }
@media (min-width: 320px) and (max-width: 480px) {
    #logo_website .flex.border-b.border-40.w-full {display: block;}
    #logo_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #logo_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #logo_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #logo_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
@media (min-width: 481px) and (max-width: 767px) {
    #logo_website .flex.border-b.border-40.w-full {display: block;}
    #logo_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #logo_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #logo_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #logo_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
</style>
