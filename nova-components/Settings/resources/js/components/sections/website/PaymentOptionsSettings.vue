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
    <div id="payment_options_settings">
      <div class="title">{{__('Payment options settings')}}</div>
      <div class="content_page">
        <form @submit.prevent="updateContact">
          <div class="choose_options">
            <div class="name">{{__('Active Payment Options')}}</div>
            <div class="options_area">
<!--              <div class="item">-->
<!--                <p>{{__('Bank Transfer')}}</p>-->
<!--                <label class="switch">-->
<!--                  <input type="checkbox"  ref="checkbox1"  v-model="bank_transfer" @change="handleCheckedStatusForBankTransfer($event)">-->
<!--                  <span class="slider round"></span>-->
<!--                </label>-->
<!--              </div>-->
              <div class="item">
                <p>{{__('Visa / Credit Card')}}</p>
                <label class="switch">
                  <input type="checkbox"  ref="checkbox2"  v-model="credit_card" @change="handleCheckedStatusForCreditCard($event)">
                  <span class="slider round"></span>
                </label>
              </div><!-- item -->
            </div><!-- options_area -->
          </div><!-- choose_options -->
            <div class="block_area">
                <div class="name">{{__('Deposit Amount Required')}}</div>
                <div class="textarea_area">
                    <select v-model="deposit_percentage">
                        <option value="100" selected>100%</option>
                        <option value="75">75%</option>
                        <option value="50">50%</option>
                        <option value="25">25%</option>
                    </select>
                </div><!-- textarea_area -->
            </div><!-- block_area -->
<!--          <div class="block_area">-->
<!--            <div class="name">{{__('Bank Account Info Ar')}}</div>-->
<!--            <div class="textarea_area">-->
<!--              <vue-editor v-model="bank_account_info_ar" :editor-toolbar="customToolbar"></vue-editor>-->
<!--              &lt;!&ndash; <textarea name="bank_account_info_ar" v-model="bank_account_info_ar" id="bank_account_info_ar" cols="30" rows="10" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40"></textarea>&ndash;&gt;-->
<!--            </div>&lt;!&ndash; textarea_area &ndash;&gt;-->
<!--          </div>-->
<!--          <div class="block_area last">-->
<!--            <div class="name">{{__('Bank Account Info En')}}</div>-->
<!--            <div class="textarea_area">-->
<!--              <vue-editor v-model="bank_account_info_en" :editor-toolbar="customToolbar"></vue-editor>-->
<!--              &lt;!&ndash; <textarea name="bank_account_info_en" v-model="bank_account_info_en" id="bank_account_info_en" cols="30" rows="10" class="inline-block form-control form-input form-input-bordered text-lg text-90 w-full h-40"></textarea>&ndash;&gt;-->
<!--            </div>&lt;!&ndash; textarea_area &ndash;&gt;-->
<!--          </div>-->
          <div class="buttons_area">
            <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">{{ __('Save') }}</button>
            <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
          </div><!-- buttons_area -->
        </form>
      </div>
    </div>
  </div>
</template>

<script>

    import { VueEditor } from "vue2-editor";
    export default {
        name: "Payment Options Settings",
        components : {
            VueEditor
        },
        data() {
            return {
                team: Object,
                settings: Object,
                crumbs: [],
                locale : null,
                credit_card : 0 ,
                // bank_transfer : 0,
                // bank_account_info_ar : null,
                // bank_account_info_en : null,
                deposit_percentage : 100,
                // customToolbar: [
                //     ["bold", "italic", "underline"],
                //     [{ list: "ordered" }, { list: "bullet" }] ,
                //     [
                //         {
                //             align: ""
                //             }, {
                //                 align: "center"
                //             }, {
                //                 align: "right"
                //             }, {
                //                 align: "justify"
                //             }
                //     ]
                // ]

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
                    text: 'Payment options settings',
                    to: '#',
                }
            ];
            this.team = Spark.state.currentTeam;
            this.getSettings();
        },
        methods: {
            getSettings() {
                Nova.request()
                    .get("/nova-vendor/settings/website-settings/"+this.team.id, {})
                    .then(response => {
                        this.settings = response.data;
                        // this.bank_transfer = this.settings.bank_transfer;
                        this.credit_card = this.settings.credit_card;
                        // this.bank_account_info_ar = this.settings.bank_account_info.ar;
                        // this.bank_account_info_en = this.settings.bank_account_info.en;
                        this.deposit_percentage = this.settings.deposit_percentage;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            handleCheckedStatusForBankTransfer(event){

                if(event.target.checked){
                    this.bank_transfer = 1;
                }else{
                    this.bank_transfer = 0;
                }
            },
            handleCheckedStatusForCreditCard(event){

                if(event.target.checked){
                    this.credit_card = 1;
                }else{
                    this.credit_card = 0;
                }
            },
            updateContact() {

                // if(!this.bank_transfer && !this.credit_card){
                //     this.$toasted.error(Nova.app.__('Please you have to enable at least one payment option'), {
                //         duration: 3000
                //     });
                //     return false;
                // }
                if(!this.credit_card){
                    this.$toasted.error(Nova.app.__('Please you have to enable at least one payment option'), {
                        duration: 3000
                    });
                    return false;
                }

                // if(this.bank_transfer){
                //     if(!this.bank_account_info_ar || !this.bank_account_info_en){
                //         this.$toasted.error(Nova.app.__('Please fill in bank account information cause bank transfer option is on'), {
                //             duration: 3000
                //         });
                //         return false;
                //     }
                // }



                Nova.request()
                    .put("/nova-vendor/settings/update-website-settings/"+this.team.id, {

                        // bank_transfer : this.bank_transfer ,
                        credit_card : this.credit_card,
                        deposit_percentage : this.deposit_percentage,
                        // bank_account_info: {
                        //     ar: this.bank_account_info_ar,
                        //     en: this.bank_account_info_en
                        // },

                    })
                    .then(response => {
                        this.settings = response.data;
                        this.$router.push('/settings/website');
                        this.$toasted.success(Nova.app.__('Success'), {
                            duration: 3000
                        })
                    })
                    .catch(error => {
                        console.log(error)
                    });
            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            }
        },
        created() {
            this.locale = Nova.config.local;
        },
    }
</script>

<style lang="scss">
    select {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
        width: 100%;
        height: 40px !important;
        padding: 0 10px !important;
        background-color: #fafafa !important;
        border: 1px solid #ddd !important;
        color: #000;
        font-size: 15px;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        -webkit-appearance: none;
        -moz-appearance: none;
        -o-appearance: none;
        appearance: none;
        border-radius: 5px !important;
        background-position: 15px center;
        background-repeat: no-repeat;
        background-size: 14px;

        [dir="ltr"] & {
            background-position: 97% center;
        }

        /* ltr */
    }
  #payment_options_settings {
    margin: 10px auto 0;
    border: 1px solid #ddd;
    border-radius: .5rem;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    .title{
      background: #f7fafc;
      border-bottom: 1px solid #ddd;
      padding: .75rem;
      color: #000;
      font-size: 1.125rem;
      display: block;
    } /* title */
    .content_page {
      background: #fff;
      padding: 10px;
      .choose_options {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;
        border-bottom: 1px solid #ddd;
        padding: 10px 0 20px;
        .name {
          min-width: 25%;
          color: #666666;
          font-size: 16px;
          @media (min-width: 320px) and (max-width: 480px) {
            min-width: 100%;
            text-align: center;
            margin: 0 0 10px;
          } /* media */
          @media (min-width: 768px) and (max-width: 991px) {
            min-width: 33.33333%;
          } /* media */
        } /* name */
        .options_area {
          min-width: 50%;
          display: flex;
          align-items: center;
          justify-content: flex-start;
          flex-wrap: wrap;
          @media (min-width: 320px) and (max-width: 480px) {
            min-width: 100%;
            justify-content: center;
          } /* media */
          .item {
            min-width: 50%;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            p {
              display: block;
              margin: 0 0 0 10px;
              font-size: 15px;
              color: #222;
            } /* p */
            .switch {
              position: relative;
              display: inline-block;
              width: 60px;
              height: 26px;
              margin: 0;
              .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                -webkit-transition: .4s;
                transition: .4s;
                &::before {
                  position: absolute;
                  content: "";
                  height: 20px;
                  width: 20px;
                  left: 3px;
                  bottom: 3px;
                  background-color: white;
                  -webkit-transition: .4s;
                  transition: .4s;
                } /* before */
                &.round {
                  border-radius: 34px;
                  &::before {
                    border-radius: 50%;
                  } /* before */
                } /* round */
              } /* slider */
              input {
                opacity: 0;
                width: 0;
                height: 0;
                &:checked + {
                  .slider {
                    background-color: #21b978;
                    &::before {
                      -webkit-transform: translateX(33px);
                      -ms-transform: translateX(33px);
                      transform: translateX(33px);
                    } /* before */
                  } /* slider */
                } /* checked */
                &:focus + {
                  .slider {
                    box-shadow: 0 0 1px #21b978;
                  } /* slider */
                } /* focus */
              } /* input */
            } /* switch */
          } /* item */
        } /* options_area */
      } /* choose_options */
      .block_area {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: wrap;
        border-bottom: 1px solid #ddd;
        padding: 20px 0;
        .name {
          min-width: 25%;
          color: #666666;
          font-size: 16px;
          @media (min-width: 320px) and (max-width: 480px) {
            min-width: 100%;
            text-align: center;
            margin: 0 0 10px;
          } /* media */
          @media (min-width: 768px) and (max-width: 991px) {
            min-width: 33.33333%;
          } /* media */
        } /* name */
        .textarea_area {
          min-width: 50%;
        } /* textarea_area */
        &.last {
          border-bottom: none;
        } /* last */
      } /* block_area */
      .buttons_area {
        background: #f4f7fa;
        margin: 0 -10px -10px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-direction: row-reverse;
        border-top: 1px solid #ddd;
      } /* buttons_area */
    } /* content_page */
  } /* payment_options_settings */
</style>
