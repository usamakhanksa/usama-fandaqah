<template>
  <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Edit Review')" overlay-theme="dark" ref="editReview" class="edit_review_modal">

      <loading
        :active.sync="isLoading"
        :is-full-page="false" />
      <div class="inputs_row">
          <div class="big_col">
              <textarea name="positive_comment"  cols="30" rows="3" v-model="review.positive_comment"></textarea>
          </div>

          <div class="big_col">
              <textarea name="negative_comment"  cols="30" rows="3" v-model="review.negative_comment"></textarea>
          </div>
      </div><!-- inputs_row -->
      <button class="save_button" @click="updateReview()">{{__('Save')}}</button>

  </sweet-modal>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "Edit Review",
        components : {Loading},
        data(){
            return {
                isLoading : false,
                id : null,
                review : {
                    positive_comment : '',
                    negative_comment : ''
                }
            }
        },
        methods : {
          updateReview(){
                this.isLoading = true;
                axios.put(`/nova-vendor/customer-reviews/update-review/${this.id}` ,  this.review)
                        .then((response)=> {
                            this.$toasted.success(Nova.app.__('Review has been approved successfully'), {
                              duration: 3000
                            });
                            Nova.$emit(`review-updated`);
                            this.$refs.editReview.close();
                            this.isLoading = false;

                        });
            },
            stepBack(){
                this.$refs.approveReview.close();
            },
            getReview(){
                axios.get(`/nova-vendor/customer-reviews/get-review/${this.id}`)
                    .then((response)=> {
                        this.review.positive_comment = response.data.q_seven;
                        this.review.negative_comment = response.data.q_eight;
                    });
            }
        },
        beforeDestroy() {
            Nova.$off('review-updated');
        },
        mounted() {
            Nova.$on('open-edit-modal' , (val) => {
                this.id = val;
                this.getReview();
                this.$refs.editReview.open();
            })
        }
    }
</script>

<style lang="scss">
    .edit_review_modal {
        .sweet-modal .sweet-content .sweet-content-content {
            max-height: none !important;
            overflow: visible;
        } /* sweet-content-content */
        .inputs_row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin: 0 -15px;
            @media (min-width: 320px) and (max-width: 767px) {
                margin: 0 auto;
            } /* Mobile */
            .col {
                width: 50%;
                padding: 0 15px;
                margin: 0 auto 10px;
                @media (min-width: 320px) and (max-width: 767px) {
                    width: 100%;
                    padding: 0;
                } /* Mobile */
                label {
                    display: block;
                    margin: 0 auto 5px;
                    font-size: 15px;
                    span {
                        display: inline-block;
                        margin: 0 5px 0 0;
                        color: #f00;
                    } /* span */
                } /* label */
                input {
                    height: 40px;
                    padding: 0 10px !important;
                    color: #000 !important;
                    font-size: 15px !important;
                    border: 1px solid #dddddd !important;
                    background: #fafafa;
                    width: 100%;
                    cursor: pointer;
                    &.readonly {
                        background: #ddd;
                        border-color: #c4c4c4 !important;
                        cursor: not-allowed;
                    } /* readonly */
                } /* input */
            } /* col */
            .big_col {
                margin: 0 auto 10px;
                width: 100%;
                padding: 0 15px;
                @media (min-width: 320px) and (max-width: 767px) {
                    padding: 0;
                } /* Mobile */
                textarea {
                    width: 100%;
                    padding: 10px;
                    border-radius: 5px;
                    background: #fafafa;
                    border: 1px solid #ddd;
                    font-size: 15px;
                    color: #000;
                } /* textarea */
            } /* big_col */
        } /* inputs_row */
        button.save_button {
            height: 35px;
            background: #4099de;
            width: 100%;
            border-radius: 5px;
            font-size: 15px;
            color: #fff;
            &:hover {background: #0071C9;}
        } /* button */
    } /* edit_review_modal */
</style>
