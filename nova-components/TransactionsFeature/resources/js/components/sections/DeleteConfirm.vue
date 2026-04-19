<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Delete Process')" overlay-theme="dark" ref="deleteConfirm" class="delete_confirm">
    <div class="relative mx-auto justify-center z-20">
      <loading
        :active.sync="isLoading"
        :is-full-page="false" />
      <span>{{__('Are you sure to delete this transaction ?')}}</span>
      <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
        <div class="flex justify-end flex-wrap">
          <button id="confirm-delete-button" @click="deleteResource(id)"   class="btn btn-default btn-danger m-0">{{__('delete')}}</button>
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
        props : ['id'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
          deleteResource(id){
                this.isLoading = true;
                axios.delete(`/nova-vendor/transactions-feature/deleteTransaction` , { data : {id : id } })
                        .then((response)=> {
                          if(response.data.flag === 'success'){
                            this.$toasted.success(Nova.app.__('Resource has been deleted successfully'), {
                              duration: 3000
                            });
                            Nova.$emit(`transaction-destroyed`);
                            this.$refs.deleteConfirm.close();
                            this.isLoading = false;
                          }else{
                            // Nova.$emit('something-wrong');
                            this.$toasted.error(Nova.app.__('Something went wrong , we are working hard to fix it'), {
                              duration: 3000
                            });
                            this.$refs.deleteConfirm.close();
                            this.isLoading = false;
                          }
                        })
            },
            stepBack(){
                this.$refs.deleteConfirm.close();
            }
        }
    }
</script>

<style lang="scss">
.delete_confirm {
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
