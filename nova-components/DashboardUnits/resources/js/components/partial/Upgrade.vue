<template>
    <div>
        <sweet-modal :enable-mobile-fullscreen="false" blocking :pulse-on-block="false" :title="__('Renew your subscription now')" overlay-theme="dark" ref="modal" class="purchase_license_modal">
            <div id="purchase_license">
                <div class="title">{{ __('Purchase a license for the system') }}</div>
                <div class="desc">{{ __('The account for which a license is to be purchased is')}} <p>{{currentTeam()}}</p></div>

                <div class="custom-control custom-checkbox" v-for="(item, index) in licenses" :key="index">
                    <label class="custom-control-label" :for="item.id">
                        {{ __(item.name)}}
                        <p>{{ item.price }} {{ __(currency)}}</p></label>
                    <input type="radio" :id="item.id" name="license" class="custom-control-input" :value="item"  v-model="license">
                    <span class="checkmark"></span>
                </div>

                <!-- <div class="discount_code" v-if="promo_code_data == null">
                    <label for="discount">كود الخصم (من سعر الرخصة)</label>
                    <div class="discount_form">
                        <input type="text" id="discount" placeholder="أكتب كود الخصم هنا " v-model="promo_code">
                        <button type="button" @click="check_promo_code()">تطبيق</button>
                    </div>
                    <p style="color: green;" v-if="promo_code_added && promo_code_message != ''">{{ promo_code_message}}</p>
                    <p style="color: red;" v-if="!promo_code_added && promo_code_message != ''">{{ promo_code_message}}</p>
                </div>

                <div class="discount_code applied" v-else>
                    <p> {{ promo_code }}</p>
                    (<a @click="cancel">{{ __('cancel') }}</a>)
                </div> -->

                <ul>
                    <li class="purchase_a_license_active" v-if="promo_code_data != null">
                        <span>{{ __('System license price')}}</span>
                        <p class="coupon_inactive">{{ license.price }} {{ __(currency)}}</p>
                        <div class="active_coupon">
                          <span v-if="promo_code_data.discount_type == 'percent'">{{ __('Price after discount')}} ({{promo_code_data.discount_value}}%)</span>
                          <span v-if="promo_code_data.discount_type == 'fixed'">{{ __('Price after discount')}} ( {{promo_code_data.discount_value}})</span>
                          <p>{{ license.price - promo_code_value }} {{ __(currency)}}</p>
                        </div>
                    </li>
                    <li class="purchase_a_license_active" v-else>
                      <div class="active_coupon">
                        <span>{{ __('System license price')}}</span>
                        <p>{{ license.price }} {{ __(currency)}}</p>
                      </div>
                    </li>
                    <li>
                        <span>{{ __('VAT')}}</span>
                        <p >{{ vat }} {{ __(currency)}}</p>
                    </li>
                    <li class="total_price">
                        <span>{{ __('Total')}}</span>
                        <p >{{ total_price }}</p>
                    </li>
                </ul>
                <!-- <div class="bills_errors" v-if="bills_errors">
                  {{bills_errors.message}}
                </div>  -->

                <button  class="w-full btn btn-default btn-danger" type="button" style="font-weight: bold;font-size: 15px;" @click="gotoTechnicalSupport">
                    {{ __('Contact the technical support')}}
                </button>
                <!-- <button v-if="!loading" class="w-full btn btn-default btn-danger" type="button" @click="create_bill()">
                    {{ __('Pay now')}}
                </button>
                <button v-if="loading" class="w-full btn btn-default btn-danger" type="button" disabled="">
                    {{ __('loading ...')}}
                </button> -->
                <div class="flex justify-center p-5">
                    <img src="/logo_pay.png" alt="">
                </div>
            </div>
        </sweet-modal>
    </div>
</template>

<script>
    export default {
        name: "Upgrade",
        data: () => {
            return {
                currency :Nova.app.currentTeam.currency,
              license: {
                  id: 1,
                  name: 'License for the system for a period of 12 months',
                  price: Nova.app.currentTeam.current_billing_plan == 'trial' ? 1500 : 1200,
                  checked: true,
              },
              licenses: [
                {
                  id: 1,
                  name: 'License for the system for a period of 12 months',
                  price: Nova.app.currentTeam.current_billing_plan == 'trial' ? 1500 :  1200,
                  checked: true,
                },
                {
                  id: 2,
                  name: 'License for the system for a period of 12 months plus Shomos',
                  price: Nova.app.currentTeam.current_billing_plan == 'trial' ? 2200 : 1900,
                  checked: false,
                },
              ],
              promo_code: null,
              promo_code_added: true,
              promo_code_message: '',
              promo_code_data: null,
              bills_errors: null,
              loading: false
            }
        },
        watch: {
            promo_code(newVal, oldVal) {
                if (!newVal) {
                    this.promo_code_added = true;
                }
            },
        },
        computed: {
          total_price: function () {
            return this.license.price + this.vat - this.promo_code_value
          },
          vat: function () {
            return (this.license.price - this.promo_code_value) *.15
          },
          promo_code_value: function () {
            if(this.promo_code_data != null){
              switch(this.promo_code_data.discount_type){
                case 'percent':
                  return this.promo_code_data.discount_value * this.license.price/100;
                  break;
                case 'fixed':
                  return this.promo_code_data.discount_value;
                  break;
              }
                this.promo_code_data
            }
            return 0;
          },
        },
        methods: {
            gotoTechnicalSupport(){
                this.$refs.modal.close();
                this.$router.push('/techincal-support')
            },
            currentTeam() {
                return Spark.state.currentTeam.name;
            },
            cancel() {
              this.promo_code = null;
              this.promo_code_data = null;
              this.promo_code_added = true;
              this.promo_code_message = '';
            },
            check_promo_code() {
                if (!this.promo_code) {
                    this.promo_code_message = this.__('Promo Code Required');
                    this.promo_code_added = false;
                    return;
                }
                axios.post('/nova-vendor/DashboardUnits/upgrade/check_promo_code', {
                    'promo_code': this.promo_code
                }).then(res => {
                    this.promo_code_data = res.data.data;
                    this.promo_code_added = res.data.valid;
                    this.promo_code_message = this.__(res.data.message);
                    if (!res.data.valid) {
                        return;
                    }
                    this.$toasted.success(this.__('Coupon added successfully'), {
                        duration: 3000
                    })
                })
            },
            create_bill() {
              this.loading = true;
                axios.get('/nova-vendor/DashboardUnits/generate_bill', {
                  params: {
                    'promo_code': this.promo_code,
                    'license': this.license,
                    'items': [
                        {
                          'name': this.license.name,
                          'price': this.license.price,
                          'quantity': 1,
                        },
                    ],
                  }
                }).then(res => {
                  if(res.data.errors){
                    this.bills_errors = res.data.errors
                    this.loading = false;
                  }else{
                    this.bill = res.data.bill;
                    window.open(res.data.bill.pay_url,"_self");
                  }
                })
            }
        }
    }
</script>

<style lang="scss">
.purchase_license_modal {
  .sweet-modal {
    @media (min-width: 320px) and (max-width: 480px) {
      width: 95%;
    } /* @media */
  }
}
  #purchase_license {
    direction: rtl;
    font-family: Dubai-Regular ,sans-serif;
    max-width: 60%;
    margin: 15px auto;
    width: auto;
    @media (min-width: 320px) and (max-width: 480px) {
      max-width: 100%;
      margin: 0 !important;
      padding: 0 !important;
    } /* @media */
    .title {
      color: #000;
      font-family: Dubai-Bold;
      text-align: center;
      font-size: 18px;
      margin: 0 auto 10px;
      padding: 0 0 10px;
      position: relative;
      @media (min-width: 320px) and (max-width: 480px) {
        font-size: 15px;
        margin: 15px auto;
      } /* @media */
    } /* title */
    .desc {
      text-align: center;
      margin: 0 auto 10px;
      padding: 0 0 10px;
      font-size: 15px;
      font-family: 'Dubai-Bold';
      color: #444;
      p {
        display: block;
        margin: 5px auto 0;
        font-family: 'Dubai-Bold';
        font-size: 17px;
        color: #000;
      } /* p */
    } /* desc */
    .custom-checkbox {
      background: #F5F5F5;
      margin: 0 auto 10px;
      height: 50px;
      line-height: 48px;
      border-radius: 5px;
      color: #000;
      position: relative;
      border: 1px solid #D1D1D1;
      cursor: pointer;
      input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
      } /* input */
      .checkmark {
        position: absolute;
        top: 12px;
        right: 10px;
        height: 25px;
        width: 25px;
        background-color: #ffffff;
        border-radius: 100%;
        border: 1px solid #707070;
        &::after {
          content: "";
          position: absolute;
          display: none;
          left: 9px;
          top: 5px;
          width: 6px;
          height: 11px;
          border: solid white;
          border-width: 0 3px 3px 0;
          -webkit-transform: rotate(45deg);
          -ms-transform: rotate(45deg);
          transform: rotate(45deg);
        } /* after */
      } /* checkmark */
      label {
        padding: 0 42px 0 10px;
        display: block;
        color: #000;
        font-size: 16px;
        cursor: pointer;
        position: relative;
        z-index: 9;
        @media (min-width: 320px) and (max-width: 480px) {
          font-size: 15px;
        } /* @media */
        p {
          display: block;
          float: left;
          margin: 0 auto;
        } /* p */
      } /* label */
    } /* custom-checkbox */
    .discount_code {
      margin: 20px auto 0;
      &.applied {
        text-align: center;
        background-color: #cee2cf;
        border-radius: 5px;
        padding: 10px;
        p {
          display: inline-block;
        }
        a {
          display: inline-block;
          color: #F44336;
          cursor: pointer;
        }
      }
      label {
        font-size: 15px;
        color: #777777;
        margin: 0 auto 5px;
        display: block;
      } /* label */
      .discount_form {
        display: flex;
        justify-content: space-between;
        align-items: center;
        input {
          height: 40px;
          border: 1px solid #D1D1D1;
          padding: 0 10px;
          width: 72%;
          border-radius: 4px;
          font-size: 16px;
          text-align: center;
          color: #000000;
          outline: none;
          @media (min-width: 320px) and (max-width: 480px) {
            font-size: 15px;
          } /* @media */
        } /* input */
        button {
          height: 40px;
          border: 1px solid #F95959;
          border-radius: 4px;
          color: #F95959;
          font-size: 17px;
          width: 25%;
          background: #fff;
          box-shadow: none;
          outline: none;
          cursor: pointer;
          @media (min-width: 320px) and (max-width: 480px) {
            font-size: 15px;
          } /* @media */
          &:hover {
            background: #fff;
            border-color: #EA4A4A;
            color: #EA4A4A;
          } /* hover */
        } /* button */
      } /* discount_form */
    } /* discount_code */
    ul {
      margin: 15px auto;
      display: flex;
      flex-wrap: wrap;
      flex-direction: row-reverse;
      li {
        border-top: 1px solid #ddd;
        padding: 10px 0;
        color: #707070;
        font-size: 16px;
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-content: center;
        @media (min-width: 320px) and (max-width: 480px) {
          font-size: 15px;
        } /* @media */
        &:first-child {border-top: none;}
        span {width: 60%;}
        p {
          width: 40%;
          text-align: left;
          &.coupon_inactive {
            text-decoration: line-through;
            color: #999999;
          } /* coupon_inactive */
        } /* p */
        .active_coupon {
          display: flex;
          width: 100%;
          margin: 5px auto 0;
        } /* active_coupon */
        &.total_price {
          width: 70%;
          font-family: 'Dubai-Bold';
          font-size: 19px;
          @media (min-width: 320px) and (max-width: 480px) {
            font-size: 17px;
          } /* @media */
        } /* total_price */
      } /* li */
    } /* ul */
    .bills_errors {
      margin: 0 auto 10px;
      text-align: center;
      color: #ff0000;
      font-size: 15px;
    } /* bills_errors */
    button.payNow {
      font-family: 'Dubai-Medium';
      font-weight: normal;
      font-size: 18px;
      height: 45px;
      border-radius: 4px;
      background: #F95959;
      box-shadow: 0 3px 5px 2px rgba(0, 0, 0, 0.1);
      color: #ffffff;
      width: 100%;
      outline: none;
      &:hover {background: #EA4A4A;}
    } /* button */
    .logo_pay {
      margin: 15px auto;
      text-align: center;
      img {
        max-width: 100%;
        max-height: 35px;
        width: auto;
        height: auto;
        display: block;
        margin: 0 auto;
      } /* img */
    } /* logo_pay */
    .last_step {
      background: #ffffff;
      box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
      padding: 1.25rem 3.25rem;
      border-radius: .5rem;
      display: flex;
      min-height: 550px;
      flex-wrap: wrap;
      align-content: center;
      justify-content: center;
    } /* last_step */
  } /* purchase_license */

    #purchase_license  .custom-checkbox:hover input ~ .checkmark {background-color: #f4f4f4;}
    #purchase_license  .custom-checkbox input:checked ~ .checkmark {
        background-color: #F75858;
        border-color: #F75858;
    }
    #purchase_license  .custom-checkbox input:checked ~ .checkmark:after {display: block;}


    #purchase_license  .discount_code .discount_form input::placeholder {
        color: #AAAAAA;
        opacity: 1;
    }
    #purchase_license  .discount_code .discount_form input:-ms-input-placeholder {color: #AAAAAA;}
    #purchase_license  .discount_code .discount_form input::-ms-input-placeholder {color: #AAAAAA;}


    #purchase_license .col_left .last_step svg {
        display: block;
        margin: 0 auto;
    }
    #purchase_license .col_left .last_step span {
        display: block;
        width: 100%;
        font-size: 20px;
        text-align: center;
        margin: 20px auto;
        color: #707070;
        font-family: Dubai-Medium;
    }
    #purchase_license .col_left .last_step a {
        font-family: 'Dubai-Medium';
        font-weight: normal;
        font-size: 16px;
        height: 45px;
        border-radius: 4px;
        background: #F95959;
        box-shadow: 0 3px 5px 2px rgba(0, 0, 0, 0.1);
        color: #ffffff;
        line-height: 45px;
        width: 100%;
        display: block;
        text-align: center;
    }
    #purchase_license .col_left .last_step a:hover {background: #EA4A4A;}
    /* Portrait phones and smaller */
    @media (min-width: 320px) and (max-width: 767px) {
        #purchase_license {
            margin: 30px 15px;
            width: auto;
            display: block;
        }
        #purchase_license .col_right {
            margin: 0 auto 20px;
            width: auto;
        }
        #purchase_license .col_right .logo {margin: 0 auto;}
        #purchase_license .col_right .logo svg {
            display: block;
            margin: 0 auto;
        }
        #purchase_license .col_right h1,
        #purchase_license .col_right h2,
        #purchase_license .col_right h3,
        #purchase_license .col_right a.call_whats,
        #purchase_license .col_right .pay_icon {display: none;}
        #purchase_license .col_left {width: auto;}
        #purchase_license  {padding: 1.25rem;}
        #purchase_license .col_left .last_step {
            min-height: auto;
            padding: 3.25rem;
        }
    }
    /* Small Screens By ahmed rabee3 */
    @media (min-width: 768px) and (max-width: 991px) {
        #purchase_license {
            width: auto;
            margin: 50px 20px;
        }
        #purchase_license .col_right, #purchase_license .col_left {width: 50%;}
        #purchase_license .col_right h2 {font-size: 20px;}
    }
    @media (min-width: 992px) and (max-width: 1170px) {
        #purchase_license {
            width: auto;
            margin: 50px 20px;
        }
    }

</style>
