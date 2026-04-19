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
    <div id="page_details">
      <div class="title">
        <span>{{__('Page Details')}}</span>
        <div class="actions">
          <button @click="openDeleteModal"  :title="__('Delete')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="delete" role="presentation" class="fill-current text-80"><path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z"></path></svg>
          </button>
          <router-link :to="{name: 'settings.website.pages.edit', params: {id: page.id}}" :title="__('Edit')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current text-white" style="margin-top: -2px; margin-left: 3px;"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
          </router-link>
        </div><!-- actions -->
      </div><!-- title -->
      <div class="content_page">
        <loading :active.sync="isLoading" :is-full-page="false"></loading>
        <div class="form-left">
          <div class="flex border-b border-40">
            <div class="flex border-b border-40 w-full">
              <div class="w-1/3 py-6 px-8">
                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Status')}}</label>
              </div>
              <div class="py-6 px-8 w-1/2">
                <div class="page_status">
                  <p v-if="page.status" class="enabled">{{__('Active')}}</p>
                  <p v-else class="not_enabled">{{__('Inactive')}}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="form-left">
          <div class="flex border-b border-40">
            <div class="flex border-b border-40 w-full">
              <div class="w-1/3 py-6 px-8">
                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title AR')}}</label>
              </div>
              <div class="py-6 px-8 w-1/2">{{page.title.ar}}</div>
            </div>
          </div>
        </div>
        <div class="form-left">
          <div class="flex border-b border-40">
            <div class="flex border-b border-40 w-full">
              <div class="w-1/3 py-6 px-8">
                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Title EN')}} </label>
              </div>
              <div class="py-6 px-8 w-1/2">
                <p>{{page.title.en}}</p>
                <small class="text-gray-500">{{__('Slug')}} : {{page.slug}}</small>
              </div>
            </div>
          </div>
        </div>
        <div class="form-left">
          <div class="flex border-b border-40 w-full">
            <div class="w-1/3 py-6 px-8">
              <label class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Content Ar')}} </label>
            </div>
            <div class="py-6 px-8 w-1/2" v-html="page.content.ar"></div>
          </div>
          <div class="flex border-b border-40 w-full">
            <div class="w-1/3 py-6 px-8">
              <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Content En')}} </label>
            </div>
            <div class="py-6 px-8 w-1/2" v-html="page.content.en"></div>
          </div>
        </div>
        <div class="form-left">
          <div class="flex border-b border-40">
            <div class="flex border-b border-40 w-full">
              <div class="w-1/3 py-6 px-8">
                <label  class="inline-block text-80 pt-2 leading-tight mb-2">{{__('Order of appearance')}} </label>
              </div>
              <div class="py-6 px-8 w-1/2">
                <p>{{page.order}}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-30 flex p-4 justify-between">
          <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
        </div>
        <delete-confirm ref="deleteShared" :id="page.id" from="details" />
      </div><!-- content_page -->
    </div><!-- page_details -->
  </div>
</template>

<script>
    import DeleteConfirm from './DeleteConfirm';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "Details",
        components : {
          DeleteConfirm,
          Loading
        },
        data(){
            return {
                crumbs : [],
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
                isLoading : false
            }
        },
        methods: {
            goBack() {
                this.$router.push({path: '/settings/website/pages'})
            },
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
            openDeleteModal(){
                this.$refs.deleteShared.$refs.deletePage.open();
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
                    text: 'Page Details',
                    to: '#',
                }
            ];

            this.getPageData(this.$route.params.id);

        }
    }
</script>

<style lang="scss" scoped>
#page_details {
  margin: 10px auto 0;
  border: 1px solid #ddd;
  border-radius: .5rem;
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
  overflow: hidden;
  .title{
    background: #f7fafc;
    border-bottom: 1px solid #ddd;
    padding: .75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    span {
      color: #000;
      font-size: 1.125rem;
    } /* span */
    .actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      svg {
        height: 20px;
        width: 20px;
      } /* svg */
      button {
        display: block;
        margin: 0 0 0 10px;
        padding: 7px;
      } /* button */
      a {
        display: block;
        padding: 7px;
        background: #4099de;
        border-radius: 4px;
        &:hover {
          background: #0071C9;
        } /* hover */
      } /* a */
    } /* actions */
  } /* title */
  .content_page {
    background: #fff;
     p {
        display: inline-block;
        position: relative;
        &::after {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 100%;
            float: right;
            margin: 5px 0 0 10px;
        } /* after */
        &.enabled {
            &::after {
                background: green;
            } /* after */
        } /*enabled  */
        &.not_enabled {
            &::after {
                background: #ff0000;
            } /* after */
        } /*not_enabled  */
    } /* p */
  } /* content_page */
} /* deposit_management_page */
</style>
