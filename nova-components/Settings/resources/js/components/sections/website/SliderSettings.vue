<template>
  <div id="slider_settings_page">
    <div class="mb-3">
      <nav v-if="crumbs.length">
        <ul class="breadcrumbs">
          <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
            <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
          </li>
        </ul>
      </nav>
    </div>
    <div class="block_area">
      <div class="title_area">{{__('Uploader Engine')}}</div>
      <div class="content_area">
        <div class="example-full">
            <div v-show="$refs.upload && $refs.upload.dropActive" class="drop-active">
                <h3>{{__('Drop files to upload')}}</h3>
            </div>
            <div class="upload" v-show="!isOption">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('Thumb')}}</th>
                            <th>{{__('Image Name')}}</th>
                            <th>{{__('Size')}}</th>
                            <th>{{__('Speed')}}</th>
                            <th>{{__('Image Status')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!files.length">
                          <td colspan="7" class="upload_intro">
                            <div class="title">{{__('Drop files anywhere to upload')}} <b>{{__('or')}}</b></div>
                            <label :for="name">{{__('Select Files')}}</label>
                          </td>
                        </tr>
                        <tr v-for="(file, index) in files" :key="file.id">
                            <td>{{index}}</td>
                            <td>
                                <img v-if="file.thumb" :src="file.thumb" :alt="file.name" />
                                <span v-else>{{__('No Image')}}</span>
                            </td>
                            <td>
                                <div class="filename">
                                    {{file.name}}
                                </div>
                                <div class="progress" v-if="file.active || file.progress !== '0.00'">
                                    <div :class="{'progress-bar': true, 'progress-bar-striped': true, 'bg-danger': file.error, 'progress-bar-animated': file.active}" role="progressbar" :style="{width: file.progress + '%'}">{{file.progress}}%</div>
                                </div>
                            </td>
                            <td>{{file.size | formatSize}}</td>
                            <td>{{file.speed | formatSize}}</td>

                            <td v-if="file.error"><div class="error_msg">{{__(file.error)}}</div></td>
                            <td v-else-if="file.success"><div class="success_msg">{{__('success')}}</div></td>
                            <td v-else-if="file.active"><div class="active_msg">{{__('active')}}</div></td>
                            <td v-else></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn dropdown-toggle" type="button">
                                        <!-- {{__('Action')}} -->
                                    </button>
                                    <div class="dropdown-menu">
                                        <a :class="{'dropdown-item': true, disabled: file.active || file.success || file.error === 'compressing'}" href="#" @click.prevent="file.active || file.success || file.error === 'compressing' ? false :  onEditFileShow(file)">{{__('Edit Image')}}</a>
                                        <a :class="{'dropdown-item': true, disabled: !file.active}" href="#" @click.prevent="file.active ? $refs.upload.update(file, {error: 'cancel'}) : false">{{__('Cancel Process')}}</a>

                                        <a class="dropdown-item" href="#" v-if="file.active" @click.prevent="$refs.upload.update(file, {active: false})">{{__('Abort')}}</a>
                                        <a class="dropdown-item" href="#" v-else-if="file.error && file.error !== 'compressing' && $refs.upload.features.html5" @click.prevent="$refs.upload.update(file, {active: true, error: '', progress: '0.00'})">{{__('Retry upload')}}</a>
                                        <a :class="{'dropdown-item': true, disabled: file.success || file.error === 'compressing'}" href="#" v-else @click.prevent="file.success || file.error === 'compressing' ? false : $refs.upload.update(file, {active: true})">{{__('Upload')}}</a>

                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#" @click.prevent="$refs.upload.remove(file)">{{__('Remove')}}</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="more_buttons">
                  <div class="btn-group">
                    <file-upload
                      class="btn btn-primary dropdown-toggle"
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
                      ref="upload"
                    >
                      <i class="fa fa-plus"></i> {{__('Select')}}
                    </file-upload>
                    <div class="dropdown-menu">
                      <label class="dropdown-item" :for="name">{{__('Add files')}}</label>
                      <a class="dropdown-item" href="#" @click="onAddFolader">{{__('Add folder')}}</a>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success" v-if="!$refs.upload || !$refs.upload.active" @click.prevent="$refs.upload.active = true">
                    <i class="fa fa-arrow-up" aria-hidden="true"></i> {{__('Start Upload')}}
                  </button>
                  <button type="button" class="btn btn-danger"  v-else @click.prevent="$refs.upload.active = false">
                    <i class="fa fa-stop" aria-hidden="true"></i> {{__('Stop Upload')}}
                  </button>
                </div><!-- more_buttons -->
              </div>
      </div><!-- content_area -->
    </div><!-- block_area -->



        


            <!-- Edit File Modal -->
            <div :class="{'modal-backdrop': true, 'fade': true, show: editFile.show}"></div>
            <div :class="{modal: true, fade: true, show: editFile.show}" id="modal-edit-file" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">{{__('Edit file')}}</h5>
                    <button type="button" class="close"  @click.prevent="editFile.show = false"><span>&times;</span></button>
                  </div>
                  <form @submit.prevent="onEditorFile">
                    <div class="modal-body">
                                <div class="form-group">
                                    <label for="name">{{__('Image Name')}}:</label>
                                    <input type="text" class="form-control" required id="name"  placeholder="Please enter a file name" v-model="editFile.name">
                                </div>
                                <div class="form-group" v-if="editFile.show && editFile.blob && editFile.type && editFile.type.substr(0, 6) === 'image/'">
                                    <label>{{__('Image File')}}: </label>
                                    <div class="edit-image">
                                        <img :src="editFile.blob" ref="editImage" />
                                    </div>

                                    <div class="edit-image-tool">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary" @click="editFile.cropper.rotate(-90)" title="cropper.rotate(-90)"><i class="fa fa-undo" aria-hidden="true"></i></button>
                                            <button type="button" class="btn btn-primary" @click="editFile.cropper.rotate(90)"  title="cropper.rotate(90)"><i class="fa fa-redo" aria-hidden="true"></i></button>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary" @click="editFile.cropper.crop()" title="cropper.crop()"><i class="fa fa-check" aria-hidden="true"></i></button>
                                            <button type="button" class="btn btn-primary" @click="editFile.cropper.clear()" title="cropper.clear()"><i class="fa fa-times" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click.prevent="editFile.show = false">{{__('Close')}}</button>
                                <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        
    <div class="block_area">
      <div class="title_area">{{__('Slider Images')}}</div>
      <div class="content_area">
        <div class="all_slider_items" v-if="images.length">
          <slider :images="imagesUrls" :index="index" @close="index = null"></slider>
          <loading :active="isImagesLoading" :is-full-page="false" :background-color="'#ffffff00'"></loading>
          <div class="slider-item"  v-for="(image, imageIndex) in images" :key="imageIndex">
            <div class="slider-image" @click="index = imageIndex" :style="{ backgroundImage: 'url(' + image.path + ')', width: '300px', height: '200px' }"></div>
            <button type="button" class="delete_img" @click="initDeleteConfirm(image.id)"><i aria-hidden="true" class="fa fa-times"></i></button>
          </div>
          <!--                <div-->
          <!--                    class="image"-->
          <!--                    v-for="(image, imageIndex) in images"-->
          <!--                    :key="imageIndex"-->
          <!--                    @click="index = imageIndex"-->
          <!--                    :style="{ backgroundImage: 'url(' + image.path + ')', width: '300px', height: '200px' }"-->
          <!--                >-->
          <!--                    <button type="button">close</button>-->
          <!--                </div>-->
        </div>
        <div v-else>
          <div class="no_slides_yet">{{__('No slides has been attached yet')}}</div>
        </div>
      </div><!-- content_area -->
    </div><!-- block_area -->
    <delete-confirm ref="deleteShared" :id="targetImageToDelete" />
  </div>
</template>

<script>
    import Cropper from 'cropperjs'
    import ImageCompressor from '@xkeshi/image-compressor'
    import FileUpload from 'vue-upload-component'
    import VueGallery from 'vue-gallery';
    import DeleteConfirm from "./DeleteConfirm";
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        components: {
            FileUpload,
            'slider' : VueGallery,
            DeleteConfirm,
            Loading
        },

        data() {
            return {
                imagesUrls : [],
                images: [],
                index: null,
                files: [],
                accept: 'image/png,image/gif,image/jpeg,image/webp',
                extensions: 'gif,jpg,jpeg,png,webp',
                minSize: 1024,
                size: 1024 * 1024 * 10,
                multiple: true,
                directory: false,
                drop: true,
                dropDirectory: true,
                addIndex: false,
                thread: 3,
                name: 'file',
                postAction: '/nova-vendor/settings/slider-upload-handler',
                headers: {
                    'X-Csrf-Token': $('meta[name="csrf-token"]').attr('content'),
                },
                data: {
                    '_csrf_token': $('meta[name="csrf-token"]').attr('content'),
                },

                autoCompress: 1024 * 1024,
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
                crumbs : [],
                handler : 0,
                targetImageToDelete : null,
                isImagesLoading : false
            }
        },

        watch: {
            'editFile.show'(newValue, oldValue) {
                // 关闭了 自动删除 error
                if (!newValue && oldValue) {
                    this.$refs.upload.update(this.editFile.id, { error: this.editFile.error || '' })
                }

                if (newValue) {
                    this.$nextTick(function () {
                        if (!this.$refs.editImage) {
                            return
                        }
                        let cropper = new Cropper(this.$refs.editImage, {
                            autoCrop: false,
                        })
                        this.editFile = {
                            ...this.editFile,
                            cropper
                        }
                    })
                }
            },

            'addData.show'(show) {
                if (show) {
                    this.addData.name = ''
                    this.addData.type = ''
                    this.addData.content = ''
                }
            },
        },

        methods: {
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

                        if(this.handler === this.files.length){

                            this.files = [];
                            this.$toasted.success(Nova.app.__('Slider Images have been attached successfully'), {
                                duration: 3000
                            })
                            this.$router.push({path: '/settings/website'});
                        }

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

            onEditFileShow(file) {
                this.editFile = { ...file, show: true }
                this.$refs.upload.update(file, { error: 'edit' })
            },

            onEditorFile() {
                if (!this.$refs.upload.features.html5) {
                    this.alert('Your browser does not support')
                    this.editFile.show = false
                    return
                }

                let data = {
                    name: this.editFile.name,
                }
                if (this.editFile.cropper) {
                    let binStr = atob(this.editFile.cropper.getCroppedCanvas().toDataURL(this.editFile.type).split(',')[1])
                    let arr = new Uint8Array(binStr.length)
                    for (let i = 0; i < binStr.length; i++) {
                        arr[i] = binStr.charCodeAt(i)
                    }
                    data.file = new File([arr], data.name, { type: this.editFile.type })
                    data.size = data.file.size
                }
                this.$refs.upload.update(this.editFile.id, data)
                this.editFile.error = ''
                this.editFile.show = false
            },

            // add folader
            onAddFolader() {
                if (!this.$refs.upload.features.directory) {
                    this.alert('Your browser does not support')
                    return
                }

                let input = this.$refs.upload.$el.querySelector('input')
                input.directory = true
                input.webkitdirectory = true
                this.directory = true

                input.onclick = null
                input.click()
                input.onclick = (e) => {
                    this.directory = false
                    input.directory = false
                    input.webkitdirectory = false
                }
            },

            // get slides
            getSlides(){


                let self = this;
                self.isImagesLoading = true;
                axios.get(`/nova-vendor/settings/slides/${Spark.state.currentTeam.id}`)
                    .then(response => {
                       $.each(response.data , function(index , val){
                           self.imagesUrls.push(val.path);
                       })

                        self.images = response.data;

                       self.isImagesLoading = false;

                    })
            },

            initDeleteConfirm(id){

                this.targetImageToDelete = id;
                this.$refs.deleteShared.$refs.deleteConfirm.open();
                // axios.delete(`/nova-vendor/settings/slides/${id}/delete`)
                //         .then((response)=> {
                //             console.log(response);
                //         })
            }

        },

        created() {
            this.getSlides();
        },
        mounted(){

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
                    text: 'Slider Settings',
                    to: '#',
                }
            ];

            Nova.$on('slider-image-deleted' , () => {
                this.getSlides();
            });


            /**
             * The Fucken Bootstrap
             */
            let style = document.createElement('link');
            style.setAttribute('id' , 'bootstrap-css');
            style.type = "text/css";
            style.rel = "stylesheet";
            style.href = 'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0-beta/dist/css/bootstrap.min.css';
            document.body.appendChild(style);

        },
        beforeDestroy() {
            $('#bootstrap-css').remove();
        }
    }
</script>


<style scoped lang="css">

    /* -- Gallery -- */
    #blueimp-gallery{
        color: #fff !important;
    }
    /* -- End Gallery -- */



    .example-full >>> {
        /*@import "https://cdn.jsdelivr.net/npm/bootstrap@4.0.0-beta/dist/css/bootstrap.min.css";*/
        /*@import "https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css";*/
        @import "https://cdn.jsdelivr.net/gh/highlightjs/cdn-release/build/styles/atom-one-light.min.css";
        @import "https://cdn.jsdelivr.net/npm/cropperjs/dist/cropper.css";
        @import "https://unpkg.com/blueimp-gallery@2.27.0/css/blueimp-gallery.min.css";
    }


    .example-full .btn-group .dropdown-menu {
        display: block;
        visibility: hidden;
        transition: all .2s
    }

    .example-full .btn-group:hover > .dropdown-menu {
        visibility: visible;
    }

    .example-full label.dropdown-item {
        margin-bottom: 0;
    }

    .example-full .btn-group .dropdown-toggle {
        margin-right: .6rem
    }

    .example-full .filename {
        margin-bottom: .3rem
    }

    .example-full .btn-is-option {
        margin-top: 0.25rem;
    }

    .example-full .edit-image img {
        max-width: 100%;
    }

    .example-full .edit-image-tool {
        margin-top: .6rem;
    }

    .example-full .edit-image-tool .btn-group{
        margin-right: .6rem;
    }

    .example-full .footer-status {
        padding-top: .4rem;
    }

    .example-full .drop-active {
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        position: fixed;
        z-index: 9999;
        opacity: .6;
        text-align: center;
        background: #000;
    }

    .example-full .drop-active h3 {
        margin: -.5em 0 0;
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        -webkit-transform: translateY(-50%);
        -ms-transform: translateY(-50%);
        transform: translateY(-50%);
        font-size: 40px;
        color: #fff;
        padding: 0;
    }

    .modal-backdrop.fade {
        visibility: hidden;
    }
    .modal-backdrop.fade.show {
        visibility: visible;
    }
    .fade.show {
        display: block;
        z-index: 1072;
    }

    .modal-dialog{
        overflow-y: initial !important
    }
    .modal-body{
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }

    .table thead th {
        text-align: justify;
    }
</style>



<style lang="scss">
  #slider_settings_page {
    .block_area {
      margin: 0 auto 30px;
      border: 1px solid #ddd;
      border-radius: .5rem;
      box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
      .title_area {
        background: #f7fafc;
        border-bottom: 1px solid #ddd;
        padding: .75rem;
        color: #000;
        font-size: 1.125rem;
        border-radius: .5rem .5rem 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
      } /* title */
      .content_area {
        background: #fff;
        padding: 10px;
        border-radius: 0 0 .5rem .5rem;
        .upload {
          .table-responsive {
            margin: 0 auto 10px;
            table {
              thead {
                tr {
                  th {
                    background: #4a5568;
                    padding: 10px 5px;
                    font-weight: normal;
                    font-size: 15px;
                    text-align: center;
                    line-height: normal;
                    color: #fff;
                    border: 1px solid #5E697C;
                    letter-spacing: 0;
                    vertical-align: middle;
                  } /* th */
                } /* tr */
              } /* thead */
              tbody {
                td {
                  background: #ffffff;
                  border: 1px solid #ced4dc;
                  text-align: center;
                  vertical-align: middle;
                  font-size: 15px;
                  height: auto;
                  letter-spacing: 0;
                  min-width: auto;
                  padding: 10px 5px;
                  font-weight: normal;
                  &.upload_intro {
                    text-align: center;
                    padding: 50px 10px;
                    background: #ffffff;
                    border: 1px solid #ced4dc;
                    height: auto;
                    min-width: auto;
                    font-weight: normal;
                    vertical-align: middle;
                    .title {
                      font-size: 25px;
                      color: #000000;
                      b {
                        display: block;
                        margin: 15px auto;
                        font-weight: normal;
                        color: #777777;
                      } /* b */
                    } /* title */
                    label {
                      display: inline-block;
                      margin: 0 auto;
                      background: #4099de;
                      color: #fff;
                      border-radius: 4px;
                      font-size: 16px;
                      padding: 10px 40px;
                      cursor: pointer;
                      -webkit-transition: all 0.2s ease-in-out;
                      -moz-transition: all 0.2s ease-in-out;
                      -o-transition: all 0.2s ease-in-out;
                      transition: all 0.2s ease-in-out;
                      &:hover {
                        background: #0071C9;
                      } /* hover */
                    } /* label */
                  } /* upload_intro */
                  img {
                    display: block;
                    margin: 0 auto;
                    max-width: 100%;
                    max-height: 40px;
                  } /* img */
                  .filename {
                    display: block;
                    text-align: right;
                    direction: ltr;
                    padding: 0 5px;
                    [dir="ltr"] & {
                      text-align: left;
                    } /* ltr */
                  } /* filename */
                  .btn-group {
                    button {
                      display: block;
                      margin: 0 auto;
                      background-color: #555555;
                      border-radius: 4px !important;
                      box-shadow: none;
                      border: none;
                      height: 30px;
                      padding: 0;
                      font-weight: normal;
                      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M28.456,11.667H25.649a1.078,1.078,0,0,1-.762-1.839l1.984-1.984a1.546,1.546,0,0,0,0-2.185L24.342,3.129a1.582,1.582,0,0,0-2.185,0L20.173,5.113a1.077,1.077,0,0,1-1.839-.762V1.544A1.546,1.546,0,0,0,16.789,0H13.211a1.546,1.546,0,0,0-1.544,1.544V4.351a1.077,1.077,0,0,1-1.839.762L7.843,3.129a1.582,1.582,0,0,0-2.185,0L3.129,5.658a1.546,1.546,0,0,0,0,2.185L5.113,9.827a1.078,1.078,0,0,1-.762,1.839H1.544A1.546,1.546,0,0,0,0,13.211v3.577a1.546,1.546,0,0,0,1.544,1.545H4.351a1.078,1.078,0,0,1,.762,1.839L3.129,22.157a1.546,1.546,0,0,0,0,2.185l2.529,2.529a1.581,1.581,0,0,0,2.185,0l1.984-1.984a1.078,1.078,0,0,1,1.839.762v2.807A1.546,1.546,0,0,0,13.211,30h3.577a1.546,1.546,0,0,0,1.544-1.544V25.649a1.078,1.078,0,0,1,1.839-.762l1.984,1.984a1.582,1.582,0,0,0,2.185,0l2.529-2.529a1.546,1.546,0,0,0,0-2.185l-1.984-1.984a1.078,1.078,0,0,1,.763-1.839h2.807A1.546,1.546,0,0,0,30,16.789V13.211A1.546,1.546,0,0,0,28.456,11.667Zm.433,5.122a.434.434,0,0,1-.433.433H25.649A2.189,2.189,0,0,0,24.1,20.959l1.984,1.984a.434.434,0,0,1,0,.613l-2.529,2.529a.433.433,0,0,1-.613,0L20.959,24.1a2.189,2.189,0,0,0-3.737,1.548v2.807a.434.434,0,0,1-.433.433H13.211a.434.434,0,0,1-.433-.433V25.649a2.155,2.155,0,0,0-1.351-2.022,2.214,2.214,0,0,0-.852-.173,2.158,2.158,0,0,0-1.534.647L7.057,26.085a.434.434,0,0,1-.613,0L3.914,23.556a.434.434,0,0,1,0-.613L5.9,20.958a2.188,2.188,0,0,0-1.548-3.736H1.544a.434.434,0,0,1-.433-.433V13.211a.434.434,0,0,1,.433-.433H4.351A2.189,2.189,0,0,0,5.9,9.041L3.914,7.057a.433.433,0,0,1,0-.613L6.444,3.914a.433.433,0,0,1,.613,0L9.041,5.9a2.189,2.189,0,0,0,3.737-1.547V1.544a.434.434,0,0,1,.433-.433h3.577a.434.434,0,0,1,.434.433V4.351A2.189,2.189,0,0,0,20.959,5.9l1.984-1.984a.433.433,0,0,1,.613,0l2.529,2.529a.434.434,0,0,1,0,.613L24.1,9.042a2.189,2.189,0,0,0,1.548,3.737h2.807a.433.433,0,0,1,.433.433Z' fill='%23ffffff'%3E%3C/path%3E%3Cpath d='M176.5,170.667a5.83,5.83,0,1,0,5.83,5.83A5.836,5.836,0,0,0,176.5,170.667Zm0,10.364a4.534,4.534,0,1,1,4.534-4.534A4.54,4.54,0,0,1,176.5,181.031Z' transform='translate(-161.497 -161.497)' fill='%23ffffff'%3E%3C/path%3E%3C/g%3E%3C/svg%3E");
                      background-repeat: no-repeat;
                      background-position: center center;
                      background-size: 18px;
                      width: 30px;
                      &::after {
                        display: none;
                      } /* after */
                    } /* button */
                    .dropdown-menu {
                      margin-top: 5px;
                      text-align: initial;
                      border-radius: 4px;
                      [dir="ltr"] & {
                        left: auto;
                        right: 0;
                      } /* ltr */
                      a {
                        display: block;
                        padding: 5px 10px;
                        font-size: 15px;
                        line-height: normal;
                      } /* a */
                    } /* dropdown-menu */
                  } /* btn-group */
                  .error_msg {color: red;}
                  .success_msg {color: #28a745;}
                  .active_msg {color: #555555;}
                } /* td */
              } /* tbody */
            } /* table */
          } /* table-responsive */
        } /* upload */
        .more_buttons {
          margin: 20px auto 0;
          display: flex;
          justify-content: flex-end;
          flex-wrap: wrap;
          align-items: center;
          button.btn-success {
            margin: 0 10px 0 0;
            height: 40px;
            padding: 0 20px;
            line-height: 40px;
            font-size: 15px;
            box-shadow: none;
            [dir="ltr"] & {
              margin: 0 0 0 10px;
            } /* ltr */
            i {
              float: left;
              line-height: 40px;
              margin: 0 10px 0 0;
              [dir="ltr"] & {
                float: right;
                margin: 0 0 0 10px;
              } /* ltr */
            } /* i */
          } /* btn-success */
          .btn-group {
            margin: 0;
            span.btn-primary {
              display: block;
              height: 40px;
              padding: 0 20px;
              line-height: 40px;
              font-size: 15px;
              margin: 0;
              cursor: pointer;
              i {
                float: right;
                line-height: 40px;
                height: 40px;
                display: block;
                margin: 0 0 0 10px;
                [dir="ltr"] & {
                  float: left;
                  margin: 0 10px 0 0;
                } /* ltr */
              } /* i */
              label {
                margin: 0;
                cursor: pointer;
              } /* label */
              &::after {
                margin: 0px 5px 0 0;
                position: relative;
                top: 2px;
                [dir="ltr"] & {
                  margin: 0px 0 0 5px;
                } /* ltr */
              } /* after */
            } /* btn-primary */
            .dropdown-menu {
              margin-top: 5px;
              text-align: initial;
              border-radius: 4px;
              [dir="ltr"] & {
                left: auto;
                right: 0;
              } /* ltr */
              label {
                display: block;
                padding: 5px 10px;
                font-size: 15px;
                line-height: normal;
                cursor: pointer;
              } /* label */
              a {
                display: block;
                padding: 5px 10px;
                font-size: 15px;
                line-height: normal;
              } /* a */
            } /* dropdown-menu */
          } /* btn-group */
        } /* more_buttons */
        .no_slides_yet {
          border: 1px solid #ddd;
          background: #fff;
          text-align: center;
          font-size: 15px;
          padding: 50px 10px;
          border-radius: 4px;
        } /* no_slides_yet */
        .all_slider_items {
          width: 100%;
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(214px, 1fr));
          background: #fff;
          position: relative;
          .vld-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
          } /* vld-overlay */
          .slider-item {
            margin: 10px auto;
            padding: 0 10px;
            width: 100%;
            position: relative;
            .slider-image {
              width: 100% !important;
              height: 150px !important;
              border-radius: 4px;
              margin: 0;
              border: 1px solid #ddd;
              background-size: cover;
              cursor: pointer;
              &::after {
                content: "";
                background: rgba(0, 0, 0, 0.4);
                position: absolute;
                top: 0;
                right: 10px;
                left: 10px;
                bottom: 0;
                border-radius: 4px;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
              } /* after */
            } /* slider-image */
            button.delete_img {
              height: 25px;
              width: 25px;
              background: red;
              border-radius: 2px;
              position: absolute;
              top: 5px;
              right: 15px;
              line-height: 25px;
              color: #fff;
              font-size: 15px;
              padding: 0;
              opacity: 0;
              -webkit-transition: all 0.2s ease-in-out;
              -moz-transition: all 0.2s ease-in-out;
              -o-transition: all 0.2s ease-in-out;
              transition: all 0.2s ease-in-out;
              [dir="ltr"] & {
                left: 15px;
                right: auto;
              } /* ltr */
            } /* delete_img */
            &:hover {
              button.delete_img {opacity: 1;}
              .slider-image {
                &::after {
                  background: rgba(0, 0, 0, 0.0);
                } /* after */
              } /* slider-image */
            } /* hover */
          } /* slider-item */
        } /* all_slider_items */
      } /* content_area */
    } /* block_area */
  } /* slider_settings_page */
</style>