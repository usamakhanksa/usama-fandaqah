<template>
  <div>
    <button v-if="from == 'profile'" class="btn btn-default btn-icon bg-primary" style="color: #eaeaea" :title="__('Edit')" @click="open">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
    </button>
     <button
            v-else
            class="cursor-pointer text-70 hover:text-primary mr-3"
            :title="__('Edit')"
            @click="open"
    >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>

    </button>
    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Edit Company')" overlay-theme="dark" ref="editCompany" class="edit-company">

      <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :is-full-page="false"></loading>
      <!-- Validation -->
      <validation v-if="showErrorBag"  :error-bag="errorBag" />
      <div class="row_group">
        <div class="col">
          <label>{{__('Company Name')}}<span>*</span></label>
          <input type="text" v-model="name" :placeHolder="__('Company Name')">
        </div>
        <div class="col">
          <label>{{__('Company Phone')}}<span>*</span></label>
          <input type="tel" v-model="phone" :placeHolder="__('Company Phone')">
        </div>
      </div>
      <div class="row_group">
        <div class="col">
          <label>{{__('City')}}<span>*</span></label>
          <input type="text" v-model="city" :placeHolder="__('City')">
        </div>
        <div class="col">
          <label>{{__('Company Address')}}<span>*</span></label>
          <input type="text" v-model="address" :placeHolder="__('Company Address')">
        </div>
      </div>
      <div class="row_group">
        <div class="col">
          <label>{{__('Person In Charge')}}<span>*</span></label>
          <input type="text" v-model="person_incharge_name" :placeHolder="__('Person In Charge')">
        </div>
        <div class="col">
          <label>{{__('Person In Charge Phone')}}</label>
          <vue-tel-input
              :defaultCountry="'SA'"
              @onInput="checkThePhone($event)"
              :required="true"
              :enabledFlags="true"
              name="phone"
              :placeholder="__('Person In Charge Phone')"
              :inputOptions="{ showDialCode: false, tabindex: 0 }"
              v-model="person_incharge_phone"
              class="mb-2"
            >
            </vue-tel-input>
            <p v-if="!personInChargeValidPhone" style="color:#ce1025;text-align: justify;">{{__('Phone number is not valid')}}</p>
          <!-- <input type="text" v-model="person_incharge_phone" :placeHolder="__('Person In Charge Phone')"> -->
        </div>
      </div>
      <div class="row_group">
        <div class="col">
          <label>{{__('Company Email')}}</label>
          <input type="text" v-model="email" :placeHolder="__('Company Email')">
        </div>
        <div class="col">
          <label>{{__('Tax number')}}</label>
          <input type="text" v-model="tax_number" :placeHolder="__('Tax number')">
        </div>
      </div>
      <button :disabled="disabled" @click="updateCompany" style="margin: 0 auto 10px !important;">{{__('Edit Company')}}</button>
    </sweet-modal>
  </div>
</template>

<script>
    import Validation from './Validation'
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "edit-company",
        components: {
            Validation,
            Loading
        },
        props: {
            company: {
                type: Object,
                required: true,
            },
            from: {
                type: String,
                default: null,
                required: false
            }
        },
        data: () => {
            return {
                name:                   null,
                phone:                  null,
                city:                   null,
                address:                null,
                person_incharge_name:   null,
                person_incharge_phone:  null,
                email:                  null,
                tax_number:             null,
                locale:                 Nova.config.local,
                disabled:               false,
                errorBag:               {},
                showErrorBag : false,
                loading:                false,
                personInChargeValidPhone: true,
                customerPhoneCountry : null,
            }
        },
        methods: {
            fillInputs(){
                this.name                   = this.company.name;
                this.phone                  = this.company.phone;
                this.city                   = this.company.city;
                this.address                = this.company.address;
                this.person_incharge_name   = this.company.person_incharge_name;
                this.person_incharge_phone  = this.company.person_incharge_phone;
                this.email                  = this.company.email;
                this.tax_number             = this.company.tax_number;
                this.errorBag               = {};

            },
            open() {
                this.fillInputs();
                this.$refs.editCompany.open()
            },
            updateCompany() {
                if (!this.name)
                {
                    this.$toasted.show(this.__("Company name is required"), {type: 'error'})
                    return
                }
                if (!this.phone)
                {
                    this.$toasted.show(this.__("Company phone is required"), {type: 'error'})
                    return
                }
                if (!this.city)
                {
                    this.$toasted.show(this.__("City is required"), {type: 'error'})
                    return
                }
                if (!this.address)
                {
                    this.$toasted.show(this.__("Company address is required"), {type: 'error'})
                    return
                }
                if (!this.person_incharge_name)
                {
                    this.$toasted.show(this.__("Person in charge name is required"), {type: 'error'})
                    return
                }
                this.loading = true;
                this.disabled = true;
                axios.put(`/nova-vendor/new/customers/companies/update`, {
                        id                      : this.company.id,
                        name                    : this.name,
                        phone                   : this.phone,
                        city                    : this.city,
                        address                 : this.address,
                        person_incharge_name    : this.person_incharge_name,
                        person_incharge_phone   : this.person_incharge_phone,
                        email                   : this.email,
                        tax_number              : this.tax_number
                    })
                    .then(response => {
                        Nova.$emit('company-updated');
                        this.loading = false;
                        this.$refs.editCompany.close();
                        this.disabled = false;
                        this.$toasted.show(this.__('Company updated successfully'), {type: 'success'});
                    }).catch(error => {
                        this.loading = true;
                        this.disabled = true;
                        if (error.response) {
                            this.loading = false;
                            this.disabled = false;
                            this.showErrorBag =  true,
                            this.errorBag = error.response.data.errors
                        }
                    });

            },
            checkThePhone(phone){
                this.personInChargeValidPhone = phone.isValid;
                this.customerPhoneCountry = phone.country.name;
            },
        },
        beforeDestroy() {
            Nova.$off('company-updated');
        },

    }
</script>

<style lang="scss" scoped>

.edit-company {
    text-align: start !important;
   .sweet-content {
    overflow: auto;
    max-height: 500px;
    display: block;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */

  .input_group {
    display: block;
    margin: 0 auto 10px;
  } /* input_group */
  .row_group {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    margin: 0 -10px;
    @media (min-width: 320px) and (max-width: 767px) {
      margin: 0;
    } /* Mobile */
    .col {
      width: 50%;
      padding: 0 10px;
      margin: 0 auto 10px;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
        padding: 0;
      } /* Mobile */
    } /* col */
  } /* row_group */
  label {
    display: block;
    margin: 0 auto 5px;
    text-align: start !important;
    font-size: 15px;
    span {
      display: inline-block;
      margin: 0 5px 0 0;
      color: #f00;
      [dir="ltr"] & {
        margin: 0 0 0 5px;
      } /* ltr */
    } /* span */
  } /* label */
  input {
    height: 40px !important;
    padding: 0 10px !important;
    color: #000 !important;
    font-size: 15px !important;
    border: 1px solid #dddddd !important;
    background: #fafafa !important;
    width: 100% !important;
    &[readonly="readonly"] {
      cursor: pointer;
    } /* readonly */
  } /* input */
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
  } /* select */
  button {
    background: #4099de ;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 100px;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    padding: 0 15px;
    color: #ffffff !important;
    width: 100%;
    margin: 0 auto 10px !important;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
  } /* button */
} /* Deposit_Transaction_Modal */
</style>
