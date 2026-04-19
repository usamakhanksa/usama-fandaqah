<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Approve Review')" overlay-theme="dark" ref="approveReview" class="approve_review">
    <div class="relative mx-auto justify-center z-20">
      <loading
        :active.sync="isLoading"
        :is-full-page="false" />
      <span>{{__('Are you sure to approve this review ?')}}</span>
      <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
        <div class="flex justify-end flex-wrap">
          <button id="confirm-delete-button" @click="approveReview(id)"   class="btn btn-default btn-danger m-0">{{__('Approve')}}</button>
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
        name: "Approve Review",
        components : {Loading},
        data(){
            return {
                isLoading : false,
                id : null
            }
        },
        methods : {
          approveReview(id){
                this.isLoading = true;
                axios.post(`/nova-vendor/customer-reviews/approve-review/${id}`)
                        .then((response)=> {

                            this.$toasted.success(Nova.app.__('Review has been approved successfully'), {
                              duration: 3000
                            });
                            Nova.$emit(`review-approved`);
                            this.$refs.approveReview.close();
                            this.isLoading = false;

                        })
            },
            stepBack(){
                this.$refs.approveReview.close();
            }
        },
        beforeDestroy() {
            Nova.$off('review-approved');
        },
        mounted() {
            Nova.$on('open-approve-modal' , (val) => {
                this.id = val;
                this.$refs.approveReview.open();
            })
        }
    }
</script>

<style lang="scss">
.approve_review {
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
