<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :hide-close-button="true" overlay-theme="dark" ref="deleteConfirm" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="isLoading" :is-full-page="false" />
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteResource(id)" class="yes_delete_button">{{__('Yes, delete !')}}</button>
        <button type="button" @click="stepBack()" class="back_delete_button"> {{__('Do not retreat !')}}</button>
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
        props : ['id' , 'targetModel'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
          deleteResource(id){
                this.isLoading = true;
                axios.delete(`/nova-vendor/units/delete-resource` , { data : {id : id , model : this.targetModel} })
                        .then((response)=> {
                          if(response.data.status === 'model_destroyed'){
                            this.$toasted.success(Nova.app.__('Resource has been deleted successfully'), {
                              duration: 3000
                            });
                            Nova.$emit(`${this.targetModel}-destroyed`);
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