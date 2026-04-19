<template>
  <div class="item_reservation_button">
    <button 
    v-if="reservation.all_grouped_reservations_ids && 
    reservation.all_grouped_reservations_ids.length == 1 && 
    reservation.reservation_type == 'group' &&
    reservation.status == 'confirmed' &&
    reservation.company &&
    reservation.customer_id
    "
    class="main_button cancel"  
    @click="open">{{__('Remove Company')}}</button>
    <!-- Remove company  -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :hide-close-button="true" overlay-theme="dark" ref="removeCompany" class="delete_confirm_modal">
        <div class="delete_confirm_modal_content">
          <loading :active.sync="loading" :is-full-page="false"></loading>
          <h1>{{__('Are you sure to remove the company from the reservation ?')}}</h1>
          <div class="buttons_delete">
              <button id="confirm-delete-button" @click="doRemoveCompany" class="cancel">{{__('Remove Company')}}</button>
              <button type="button" @click="stepBack" class="back_delete_button"> {{__('Step Back')}}</button>
          </div>
          </div>
    </sweet-modal>

  </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "remove-company",
        components : {
            Loading
        },
        props : ['reservation'],
        data: () => {
            return {
                loading: false,
                invoices : []
            }
        },
        methods: {
            open(){
            if(this.reservation.pure_invoices_without_credit_notes.length){
              this.$toasted.show(this.__('Invoices found, you can not remove company'), {type: 'error'});
              return 
            }
            this.$refs.removeCompany.open();
          },
          doRemoveCompany(){
             this.loading = true;   
             axios.post(`/nova-vendor/calender/reservation/${this.reservation.id}/remove-company`)
             .then(response => {
                this.loading = false;
                if(response.data.success){
                    Nova.$emit('refresh-reservation');
                    this.$toasted.show(this.__('Company was removed from reservation successfully'), {type: 'success'})
                }else{
                    this.$toasted.show(this.__('Something went wrong. Please try again or contact customer support.'), {type: 'success'})
                }
                
                this.$refs.removeCompany.close();;
                
             })
          },
          stepBack(){
            this.$refs.removeCompany.close();
          }

        },
        mounted() {
          
        }

    }
</script>
<style lang="scss" scoped>
.cancel_reservation_modal {
  textarea {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    background: #fafafa;
    border: 1px solid #ddd;
    font-size: 15px;
    color: #000;
    margin: 0 auto 10px;
  } /* textarea */
  button.cancel_button {
    height: 35px;
    background: #e74444;
    width: 100%;
    border-radius: 5px;
    font-size: 15px;
    color: #fff;
    &:hover {background: #dd3a3a;}
  } /* cancel_button */
} /* add_comment_modal */
</style>
