<template>
  <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="deleteModal" class="delete_confirm_modal">
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
    name: "delete-modal",
    props : ['transaction_id'],
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
            this.$refs.deleteModal.close();
        },
        deleteTransaction(){
            this.loading = true;
            axios.post(`/nova-vendor/financial-management/deleteTransaction?transaction_id=${this.transaction_id}`)
                .then((res) => {
                    this.$toasted.show(this.__('Transaction Deleted Successfully'), {type: 'success'});
                    Nova.$emit('delete-transaction');
                    this.loading = false;
                    this.$refs.deleteModal.close();
                }).catch(err => {
                        this.loading = false;
                        if (err.response && err.response.data && err.response.data.message) {
                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                    })

        }
    }
}
</script>

<style scoped>

</style>
