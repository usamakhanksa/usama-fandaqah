<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Delete Page')" overlay-theme="dark" ref="deletePage" class="delete_confirm_slider_image">
    <div class="relative mx-auto justify-center z-20">
      <loading :active.sync="isLoading" :is-full-page="false"></loading>
      <span>{{__('Are you sure to delete this page ?')}}</span>
      <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
        <div class="flex justify-end flex-wrap">
          <button id="confirm-delete-button" @click="deletePage(id)"   class="btn btn-default btn-danger m-0">{{__('delete')}}</button>
          <button type="button" @click="stepBack()"  class="btn btn-default bg-gray-400 ml-2"> {{__('Back')}}</button>
        </div>
      </div>
    </div>
  </sweet-modal>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "DeleteConfirm",
        components : {Loading},
        props : ['id','from'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
            deletePage(id){
                this.isLoading = true;
                axios.delete(`/nova-vendor/settings/delete-page/${id}`)
                        .then((response)=> {

                            this.$refs.deletePage.close();
                            this.isLoading = false;
                            this.$toasted.success(Nova.app.__('Page Deleted Successfully'), {
                                duration: 3000
                            });
                            if(this.from === 'index'){
                                Nova.$emit('page-deleted');
                            }else{
                                this.$router.push(`/settings/website/pages`);
                            }

                        })
            },
            stepBack(){
                this.$refs.deletePage.close();
            }
        }
    }
</script>

<style lang="scss">
.delete_confirm_slider_image {
  h2 {
	  line-height: 63px;
  } /* h2 */
  span {
    padding: 30px 20px;
    line-height: normal;
    display: block;
    font-size: 20px;
    color: #000;
  } /* span */
} /* delete_confirm_slider_image */
</style>
