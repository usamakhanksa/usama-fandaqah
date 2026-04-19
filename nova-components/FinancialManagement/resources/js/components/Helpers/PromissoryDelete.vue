<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="deletePromissoryModal" class="delete_confirm_modal">
    <div class="delete_confirm_modal_content">
      <loading :active.sync="loading" :is-full-page="false"></loading>
      <h1>{{__('are you sure ?')}}</h1>
      <h2>{{__('Deletion cannot be undone, would you like to continue ?')}}</h2>
      <div class="buttons_delete">
        <button id="confirm-delete-button" @click="deleteTransaction" class="yes_delete_button">{{__('Yes, delete !')}}</button>
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
    name: "promissory-delete",
    props : ['promissory_id'],
    components : {
      Loading
    },
    data(){
        return {
            loading : false
        }
    },
    methods:{
        stepBack(){
            this.$refs.deletePromissoryModal.close();
        },
        deleteTransaction(){
            this.loading = true;
            axios.post(`/nova-vendor/financial-management/promissories/delete?id=${this.promissory_id}`)
                .then((res) => {
                  this.loading = false;
                  if(res.data.success){
                    this.$toasted.show(this.__('Promissory Deleted Successfully'), {type: 'success'});
                    Nova.$emit('delete-promissory');
                    this.$refs.deletePromissoryModal.close();
                  }else{
                    this.$toasted.show(res.data.message, {type: 'error'});
                    this.$refs.deletePromissoryModal.close();
                  }
                  
                })

        }
    }
}
</script>

<style scoped>

</style>
