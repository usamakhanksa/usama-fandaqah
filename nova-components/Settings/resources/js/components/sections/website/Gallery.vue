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
        <card  id="gallery_website">
            <div class="title-card title-card p-3 bg-gray-200 text-xl rounded-lg rounded-b-none border-b-2  border-gray-300">{{__('Photo album')}}</div>
            <div class="card-content">
                <div class="form-left">
                    <div class="flex border-b border-40">
                        <div class="flex border-b border-40 w-full">
                            <div class="w-1/5 py-6 px-8">
                                <label for="enable_gallery" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Activate Album')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                <label class="switch">
                                    <input type="checkbox" id="enable_gallery" v-model="enable_gallery">
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
                                <label for="gallery" class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Pictures')}}</label>
                            </div>
                            <div class="py-6 px-8 w-1/2">
                                    <span class="form-file">
                                        <file-upload
                                            extensions="gif,jpg,jpeg,png,webp"
                                            accept="image/png,image/gif,image/jpeg,image/webp"
                                            name="avatar"
                                            class="form-file-btn btn btn-default btn-primary select-none"
                                            v-model="files"
                                            post-action="/nova-vendor/settings/upload-file"
                                            id="gallery"
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
                    <div class="gallery_images">
                        <div v-for="(image, i) in images">
                            <a href="#" @click="deleteImage(image, i)" title="Remove" class="icon delete"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg></a>
                            <img class="image" :src="image" :key="i" @click="index = i">
                        </div>
                    </div>
                    <vue-gallery-slideshow :images="images" :index="index" @close="index = null"></vue-gallery-slideshow>
                </div>

                <div class="bg-30 flex p-4 justify-between">
                        <button type="submit" @click="updateGallery" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                            {{ __('Save') }}
                        </button>
                        <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
                    </div>
            </div>
        </card>
    </div>
</template>

<script>
    import VueGallerySlideshow from 'vue-gallery-slideshow';
    import VueUploadComponent from 'vue-upload-component';

    export default {
        name: "Gallery",
        components: {
            VueGallerySlideshow,
            'file-upload': VueUploadComponent
        },
         data() {
            return {
                team: Object,
                installed: false,
                reset: true,
                crumbs: [],
                locale : null,
                settings: Object,
                files: [],
                images: [],
                new_images: [],
                index: null,
                enable_gallery: false,
                edit: false,
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
                    text: 'Photo album',
                    to: '#',
                }
            ],
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            editSaveGallery() {
                this.loading = true;
                this.edit = false;
                let file = this.files[0];

                if (file == undefined)
                    return;

                const data = new FormData();
                data.append('file', file.file, file.name);

                Nova.request()
                    .post("/nova-vendor/settings/upload-file", data)
                    .then(response => {
                        this.images.push(response.data)
                        this.new_images.push(response.data);
                        this.loading = false;
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
                this.editSaveGallery();
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
            deleteImage(image, index) {
                let filteredItems = this.images.slice(0, index).concat(this.images.slice(index + 1, this.images.length));

                this.images = filteredItems;
                this.new_images = filteredItems;
            },
            updateGallery() {
                Nova.request()
                    .put("/nova-vendor/settings/update-website-gallery/"+this.team.id, {
                        images: this.new_images,
                        enable_gallery: this.enable_gallery
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
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;
                        this.enable_gallery = this.settings.enable_gallery;
                        this.settings.gallery.forEach( (element) => {
                            this.images.push(element.path)
                        })
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
        },
    }
</script>

<style scoped>
    .gallery_images {
        padding: 10px;
    }
    .gallery_images img {
        display: inline-block;
        margin: 5px;
    }
    .image {
        width: 100px;
        height: 100px;
        background-size: cover;
        cursor: pointer;
        margin: 5px;
        border-radius: 3px;
        border: 1px solid lightgray;
        object-fit: contain;
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
    #gallery_website .flex.border-b.border-40.w-full {display: block;}
    #gallery_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #gallery_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #gallery_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #gallery_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
@media (min-width: 481px) and (max-width: 767px) {
    #gallery_website .flex.border-b.border-40.w-full {display: block;}
    #gallery_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8, #gallery_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {
        padding: 10px;
        display: block;
        width: 100%;
    }
    #gallery_website .flex.border-b.border-40.w-full .py-6.px-8.w-1\/2 {padding: 0 10px 10px;}
    #gallery_website .flex.border-b.border-40.w-full .w-1\/5.py-6.px-8 label {margin: 0 auto;}
}
</style>
