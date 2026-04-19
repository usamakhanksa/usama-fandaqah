<template>
  <div class="item_reservation_button">
    <button 
    v-if="reservation.all_grouped_reservations_ids &&
    reservation.all_grouped_reservations_ids.length >= 1 && 
    reservation.reservation_type == 'group' && 
    reservation.status == 'confirmed' && 
    reservation.company"
    class="main_button"  
    @click="open">{{__('Change Company')}}</button>
    <!-- Remove company  -->
    <sweet-modal id="change_company_modal" class="update_customer_modal relative" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Change Company')" overlay-theme="dark" ref="changeCompany">
       <loading
                :active.sync="loading"
                :can-cancel="false"
                :is-full-page="fullPage"
        />
      <div class="holder mt-3 mb-5">
        <div class="headers d-flex justify-content-end  mb-2">

            <button class="main_button"v-permission="'create companies'" @click="openAddCompanyModal">{{__('Add Company')}}</button>
        </div>
        <div class="formgroup">
                <autocomplete
                    id="group_auto_complete"
                    ref="autoComplete"
                    :initialValue="reservation.company.id"
                    :initialDisplay="reservation.company.name  + ' ' + reservation.company.phone"
                    :source="companies_endpoint"
                    :resultsDisplay="formatForDropDown"
                  
                    @selected="companySelected"
                    @clear="companiesInputCleared"
                    :placeholder="__('Search By Company Name')"
                >
                <div slot="noResults" style="display:flex; align-content:center;">
                    <p>{{__('No companies found')}} ... </p>
                </div>

                </autocomplete>
        </div>
      </div>

      <button class="update_customer_button" :disabled="change_company_disabled" @click="doChangeCompany">{{__('Change Company')}}</button>
    </sweet-modal>

    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Add Company')" overlay-theme="dark" ref="addCompanyReservation" class="add-company relative">

        <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
        <!-- Validation -->
        <company-validation v-if="showErrorBag"  :error-bag="errorBag" />
        <div class="row_group">
            <div class="col">
            <label>{{ __('Company Name') }}<span>*</span></label>
            <input type="text" v-model="new_company.name" :placeHolder="__('Company Name')">
            </div>
            <div class="col">
            <label>{{__('Company Phone')}}<span>*</span></label>
            <input type="tel" v-model="new_company.phone" :placeHolder="__('Company Phone')">
            </div>
        </div>
        <div class="row_group">
            <div class="col">
            <label>{{__('City')}}<span>*</span></label>
            <input type="text" v-model="new_company.city" :placeHolder="__('City')">
            </div>
            <div class="col">
            <label>{{__('Company Address')}}<span>*</span></label>
            <input type="text" v-model="new_company.address" :placeHolder="__('Company Address')" >
            </div>
        </div>
        <div class="row_group">
            <div class="col">
            <label>{{__('Person In Charge')}}<span>*</span></label>
            <input type="text" v-model="new_company.person_incharge_name" :placeHolder="__('Person In Charge')" >
            </div>
            <div class="col">
            <label>{{__('Person In Charge Phone')}}</label>
            <input type="text" v-model="new_company.person_incharge_phone" :placeHolder="__('Person In Charge Phone')" >
            </div>
        </div>
        <div class="row_group">
            <div class="col">
            <label>{{__('Company Email')}}</label>
            <input type="text" v-model="new_company.email" :placeHolder="__('Company Email')" >
            </div>
            <div class="col">
            <label>{{__('Tax number')}}</label>
            <input type="text" v-model="new_company.tax_number" :placeHolder="__('Tax number')" >
            </div>
        </div>
        <button :disabled="disabled" @click="createNewCompany">{{__('Save')}}</button>
    </sweet-modal>

  </div>
</template>

<script>
    import CompanyValidation from './CompanyValidation';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "change-company",
        components : {
            Loading,
            CompanyValidation
        },
        props : ['reservation'],
        data: () => {
            return {
                loading: false,
                companies_endpoint: '/nova-vendor/new/customers/company/target/search?entity=company&q=',
                current_company: null,
                errorBag: null,
                showErrorBag : false,
                new_company: {
                    name:                   null,
                    phone:                  null,
                    city:                   null,
                    address:                null,
                    person_incharge_name:   null,
                    person_incharge_phone:  null,
                    email:                  null,
                    tax_number:             null
                },
                change_company_disabled : true
            }
        },
        methods: {
            open(){
                this.change_company_disabled = true;
                if(this.reservation.pure_invoices_without_credit_notes.length){
                this.$toasted.show(this.__('Invoices found, you can not change company'), {type: 'error'});
                return 
                }
                this.current_company = this.reservation.company;
                this.$refs.changeCompany.open();
            },
            formatForDropDown(company) {
                return company.name + ' ' + company.phone
            },
            companySelected(obj){
                this.current_company = obj.value;
                this.change_company_disabled = false;
            },
            companiesInputCleared(){
                this.change_company_disabled = false;
                this.current_company = null;
            },

            openAddCompanyModal() {
                this.clearInputs();
                this.$refs.addCompanyReservation.open();
            },
            clearInputs(){
                this.new_company.name                   = null;
                this.new_company.phone                  = null;
                this.new_company.city                   = null;
                this.new_company.address                = null;
                this.new_company.person_incharge_name   = null;
                this.new_company.person_incharge_phone  = null;
                this.new_company.email                  = null;
                this.new_company.tax_number             = null;
                this.errorBag               = {};
                this.showErrorBag = false;
            },

            createNewCompany(){

                if (!this.new_company.name)
                {
                    this.$toasted.show(this.__("Company name is required"), {type: 'error'})
                    return
                }
                if (!this.new_company.phone)
                {
                    this.$toasted.show(this.__("Company phone is required"), {type: 'error'})
                    return
                }


                    if (!this.new_company.city)
                    {
                        this.$toasted.show(this.__("City is required"), {type: 'error'})
                        return
                    }
                    if (!this.new_company.address)
                    {
                        this.$toasted.show(this.__("Company address is required"), {type: 'error'})
                        return
                    }
                    if (!this.new_company.person_incharge_name)
                    {
                        this.$toasted.show(this.__("Person in charge name is required"), {type: 'error'})
                        return
                    }

                this.loading = true;
                this.disabled = true;
                this.showErrorBag = false;
                axios.post(`/nova-vendor/new/customers/companies/create`, {
                        team_id                 : Nova.config.user.current_team_id,
                        user_id                 : Nova.config.user.id,
                        name                    : this.new_company.name,
                        phone                   : this.new_company.phone,
                        city                    : this.new_company.city,
                        address                 : this.new_company.address,
                        person_incharge_name    : this.new_company.person_incharge_name,
                        person_incharge_phone   : this.new_company.person_incharge_phone,
                        email                   : this.new_company.email,
                        tax_number              : this.new_company.tax_number
                    })
                    .then(response => {

                        this.clearInputs();
                        this.loading = false;
                        this.disabled = false;
                        this.showErrorBag = false;
                        this.$refs.addCompanyReservation.close();
                        this.$toasted.show(this.__('Company added successfully'), {type: 'success'});
                    }).catch(error => {
                        this.loading = true;
                        this.disabled = true;
                        if (error.response) {

                            this.loading = false;
                            this.disabled = false;
                            this.showErrorBag = true;
                            this.errorBag = error.response.data.errors
                        }
                    });

            },
            doChangeCompany(){
                if(!this.current_company){
                    this.$toasted.show(this.__('Company selection is required'), {type: 'error'});
                    return 
                }
                this.loading = true;
                axios.post(`/nova-vendor/calender/reservation/${this.reservation.attachable_id ? this.reservation.attachable_id : this.reservation.id}/company/${this.current_company}/change`)
                .then(response => {
                    if(response.data.success){
                        this.loading = false;
                        this.$toasted.show(this.__('Company has been changed successfully'), {type: 'success'});
                        this.$refs.changeCompany.close();
                        Nova.$emit('update-reservation');
                    }
                   
                })
                console.log(this.current_company);
            }
        },
        mounted() {
            this.current_company = this.reservation.company;
        }

    }
</script>

<style lang="scss" scoped>
.update_customer_modal {
  .sweet-content {
    max-height: 500px;
    overflow-y: auto;
    display: block;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */

  .search_criteria{
                        display: flex;
                        align-items: center;
                        justify-content: flex-start;
                        padding: 0 0 10px;
                        margin-bottom: 10px;
                        border-bottom: 1px solid #ddd;

                        .title {
                            margin: 0 0 0 20px;
                             @media (min-width: 320px) and (max-width: 767px) {
                                margin-bottom: 5px;
                            }
                        }
                        .radios_area {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;

                        label.custom_radio {
                            display: block;
                            position: relative;
                            padding: 0 30px 0 0;
                            cursor: pointer;
                            color: #7E8790;
                            line-height: 30px;
                            margin: 0 0 0 50px;
                            -webkit-user-select: none;
                            -moz-user-select: none;
                            -ms-user-select: none;
                            user-select: none;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                            [dir="ltr"] & {
                                padding: 0 0 0 30px;
                                margin: 0 50px 0  0;
                            } /* rtl */
                            &:hover {
                                .checkmark {background: #fafafa;}
                                p {color: #444444;}
                            } /* hover */
                            input {
                                position: absolute;
                                opacity: 0;
                                cursor: pointer;
                                height: 0;
                                width: 0;
                                &:checked ~ {
                                    .checkmark {
                                        background: #fafafa;
                                        &::after {
                                            opacity: 1;
                                            visibility: visible;
                                            -webkit-transform: scale(1);
                                            -moz-transform: scale(1);
                                            -o-transform: scale(1);
                                            transform: scale(1);
                                        } /* after */
                                    } /* checkmark */
                                    p {color: #0A80D8;}
                                } /* checked */
                            } /* input */
                            .checkmark {
                                position: absolute;
                                top: 0;
                                right: 0;
                                height: 20px;
                                width: 20px;
                                background-color: #fcfcfc;
                                border: 1px solid #e8e8e8;
                                border-radius: 100%;
                                -webkit-transition: all 0.2s ease-in-out;
                                -moz-transition: all 0.2s ease-in-out;
                                -o-transition: all 0.2s ease-in-out;
                                transition: all 0.2s ease-in-out;
                                [dir="ltr"] & {
                                    right: auto;
                                    left: 0;
                                } /* rtl */
                                &::after {
                                    content: "";
                                    background: #0A80D8;
                                    position: absolute;
                                    top: 4px;
                                    right: 4px;
                                    width: 10px;
                                    height: 10px;
                                    opacity: 0;
                                    visibility: hidden;
                                    border-radius: 100%;
                                    -webkit-transform: scale(0);
                                    -moz-transform: scale(0);
                                    -o-transform: scale(0);
                                    transform: scale(0);
                                    -webkit-transition: all 0.2s ease-in-out;
                                    -moz-transition: all 0.2s ease-in-out;
                                    -o-transition: all 0.2s ease-in-out;
                                    transition: all 0.2s ease-in-out;
                                } /* after */
                            } /* checkmark */
                            p {
                                display: block;
                                line-height: 20px;
                                font-size: 16px;
                                color: #000;
                                -webkit-transition: all 0.2s ease-in-out;
                                -moz-transition: all 0.2s ease-in-out;
                                -o-transition: all 0.2s ease-in-out;
                                transition: all 0.2s ease-in-out;
                            } /* p */
                            &:last-of-type{
                                margin: 0;
                            }
                             @media (min-width: 320px) and (max-width: 767px) {
                                margin: 0;
                            }
                        } /* label */
                        @media (min-width: 320px) and (max-width: 767px) {
                            display: grid;
                            gap: 5px;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                        }
                    } /* radios_area */
                        @media (min-width: 320px) and (max-width: 767px) {
                            flex-direction: column;
                        }
                    } /* search_criteria */
  .formgroup {
    display: block;
    margin: 0 auto 10px;
    .autocomplete__box {
      border: 1px solid #ddd !important;
      background: #fafafa;
      color: #000;
      height: 40px;
      padding: 0 10px;
      box-shadow: none !important;
      border-radius: 5px;
    } /* autocomplete__box */
    ul.autocomplete__results {
      border: 1px solid #ddd;
      border-radius: 0 0 5px 5px;
      margin: -3px 0 0 0;
      background: #f5f5f5;
     li.autocomplete__results__item {
                                 color: #000 !important;
                                    font-size: 15px !important;
                                    border-bottom: 1px solid #ddd !important;
                                    padding: 10px !important;
                                    display: flex !important;
                                    align-items: center !important;
                                    justify-content: space-between !important;
                                span {
                                  display: flex !important;
                                    font-size: 15px !important;
                                    &.user{
                                            display: flex;
                                            align-items: center;
                                            justify-content: flex-start;
                                        &::before{
                                                content: "";
                                                width: 20px;
                                                height: 20px;
                                                background-position: center center;
                                                background-size: 20px 20px;
                                                background-repeat: no-repeat;
                                                margin: 0 0 0 5px;
                                                // background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDQ4IDQ4IiBoZWlnaHQ9IjQ4cHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA0OCA0OCIgd2lkdGg9IjQ4cHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxwYXRoIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTI0LDQ1QzEyLjQwMiw0NSwzLDM1LjU5OCwzLDI0UzEyLjQwMiwzLDI0LDNzMjEsOS40MDIsMjEsMjFTMzUuNTk4LDQ1LDI0LDQ1eiAgIE0zNS42MzMsMzljLTAuMTU3LTAuMjMxLTAuMzU1LTAuNTE4LTAuNTE0LTAuNzQyYy0wLjI3Ny0wLjM5NC0wLjU1NC0wLjc4OC0wLjgwMi0xLjE3OEMzNC4zMDUsMzcuMDYyLDMyLjkzNSwzNS4yMjQsMjgsMzUgIGMtMS43MTcsMC0yLjk2NS0xLjI4OC0yLjk2OC0zLjA2NkwyNSwzMWMwLTAuMTM1LTAuMDE2LDAuMTQ4LDAsMHYtMWwxLTFjMC43MzEtMC4zMzksMS42Ni0wLjkwOSwyLjM5NS0xLjQ2NGwwLjEzNS0wLjA5MyAgQzI5LjExMSwyNy4wNzQsMjkuOTIzLDI2LjI5NywzMCwyNmwwLjAzNi0wLjM4MUMzMC40MDksMjMuNjk2LDMxLDIwLjE5OCwzMSwxOWMwLTQuNzEtMi4yOS03LTctN2MtNC43NzUsMC03LDIuMjI0LTcsNyAgYzAsMS4yMywwLjU5MSw0LjcxMSwwLjk2Myw2LjYxNmwwLjAzNSwwLjM1MmMwLjA2MywwLjMxMywwLjc5OSwxLjA1NCwxLjQ0OSwxLjQ2MmwwLjA5OCwwLjA2MkMyMC4zMzMsMjguMDQzLDIxLjI3NSwyOC42NTcsMjIsMjkgIGwxLDF2MWMwLjAxNCwwLjEzOCwwLTAuMTQ2LDAsMGwtMC4wMzMsMC45MzRjMCwxLjc3NS0xLjI0NiwzLjA2NC0yLjg4MywzLjA2NGMtMC4wMDEsMC0wLjAwMiwwLTAuMDAzLDAgIGMtNC45NTYsMC4yMDEtNi4zOTMsMi4wNzctNi4zOTUsMi4wNzdjLTAuMjUyLDAuMzk2LTAuNTI4LDAuNzg5LTAuODA3LDEuMTg0Yy0wLjE1NywwLjIyNC0wLjM1NSwwLjUxLTAuNTEzLDAuNzQxICBjMy4yMTcsMi40OTgsNy4yNDUsNCwxMS42MzMsNFMzMi40MTYsNDEuNDk4LDM1LjYzMywzOXogTTI0LDVDMTMuNTA3LDUsNSwxMy41MDcsNSwyNGMwLDUuMzg2LDIuMjUsMTAuMjM3LDUuODUsMTMuNjk0ICBDMTEuMjMyLDM3LjEyOSwxMS42NCwzNi41NjUsMTIsMzZjMCwwLDEuNjctMi43NDMsOC0zYzAuNjQ1LDAsMC45NjctMC40MjIsMC45NjctMS4wNjZoMC4wMDFDMjAuOTY3LDMxLjQxMywyMC45NjcsMzEsMjAuOTY3LDMxICBjMC0wLjEzLTAuMDIxLTAuMjQ3LTAuMDI3LTAuMzczYy0wLjcyNC0wLjM0Mi0xLjU2NC0wLjgxNC0yLjUzOS0xLjQ5NGMwLDAtMi40LTEuNDc2LTIuNC0zLjEzM2MwLDAtMS01LjExNi0xLTcgIGMwLTQuNjQ0LDEuOTg2LTksOS05YzYuOTIsMCw5LDQuMzU2LDksOWMwLDEuODM4LTEsNy0xLDdjMCwxLjYxMS0yLjQsMy4xMzMtMi40LDMuMTMzYy0wLjk1NSwwLjcyMS0xLjgwMSwxLjIwMi0yLjU0MywxLjU0NiAgYy0wLjAwNSwwLjEwOS0wLjAyMywwLjIwOS0wLjAyMywwLjMyMWMwLDAtMC4wMDEsMC40MTMtMC4wMDEsMC45MzRoMC4wMDFDMjcuMDMzLDMyLjU3OCwyNy4zNTUsMzMsMjgsMzNjNi40MjQsMC4yODgsOCwzLDgsMyAgYzAuMzYsMC41NjUsMC43NjcsMS4xMjksMS4xNDksMS42OTRDNDAuNzQ5LDM0LjIzNyw0MywyOS4zODYsNDMsMjRDNDMsMTMuNTA3LDM0LjQ5Myw1LDI0LDV6IiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=");
                                                background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaWQ9Ik91dGxpbmVkIiB2aWV3Qm94PSIwIDAgMzIgMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHRpdGxlLz48ZyBpZD0iRmlsbCI+PHBhdGggZD0iTTI0LDE3SDhhNSw1LDAsMCwwLTUsNXY3SDVWMjJhMywzLDAsMCwxLDMtM0gyNGEzLDMsMCwwLDEsMywzdjdoMlYyMkE1LDUsMCwwLDAsMjQsMTdaIi8+PHBhdGggZD0iTTE2LDE1YTYsNiwwLDEsMC02LTZBNiw2LDAsMCwwLDE2LDE1Wk0xNiw1YTQsNCwwLDEsMS00LDRBNCw0LDAsMCwxLDE2LDVaIi8+PC9nPjwvc3ZnPg==");
                                        }
                                    }
                                    &.phone{
                                        direction: ltr;
                                        display: flex;
                                        align-items: center;
                                        justify-content: flex-start;

                                        &::before{
                                                content: "";
                                                width: 20px;
                                                height: 20px;
                                                background-position: center center;
                                                background-size: 20px 20px;
                                                background-repeat: no-repeat;
                                                margin: 0 5px 0 0;
                                                background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA1MTIgNTEyOyIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgd2lkdGg9IjUxMnB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj48cGF0aCBkPSJNNDE1LjksMzM1LjVjLTE0LjYtMTUtNTYuMS00My4xLTgzLjMtNDMuMWMtNi4zLDAtMTEuOCwxLjQtMTYuMyw0LjNjLTEzLjMsOC41LTIzLjksMTUuMS0yOSwxNS4xYy0yLjgsMC01LjgtMi41LTEyLjQtOC4yICBsLTEuMS0xYy0xOC4zLTE1LjktMjIuMi0yMC0yOS4zLTI3LjRsLTEuOC0xLjljLTEuMy0xLjMtMi40LTIuNS0zLjUtMy42Yy02LjItNi40LTEwLjctMTEtMjYuNi0yOWwtMC43LTAuOCAgYy03LjYtOC42LTEyLjYtMTQuMi0xMi45LTE4LjNjLTAuMy00LDMuMi0xMC41LDEyLjEtMjIuNmMxMC44LTE0LjYsMTEuMi0zMi42LDEuMy01My41Yy03LjktMTYuNS0yMC44LTMyLjMtMzIuMi00Ni4ybC0xLTEuMiAgYy05LjgtMTItMjEuMi0xOC0zMy45LTE4Yy0xNC4xLDAtMjUuOCw3LjYtMzIsMTEuNmMtMC41LDAuMy0xLDAuNy0xLjUsMWMtMTMuOSw4LjgtMjQsMjAuOS0yNy44LDMzLjJjLTUuNywxOC41LTkuNSw0Mi41LDE3LjgsOTIuNCAgYzIzLjYsNDMuMiw0NSw3Mi4yLDc5LDEwNy4xYzMyLDMyLjgsNDYuMiw0My40LDc4LDY2LjRjMzUuNCwyNS42LDY5LjQsNDAuMyw5My4yLDQwLjNjMjIuMSwwLDM5LjUsMCw2NC4zLTI5LjkgIEM0NDIuMywzNzAuOCw0MzEuNSwzNTEuNiw0MTUuOSwzMzUuNXogTTQwNC40LDM5MS40Yy0yMCwyNC4yLTMxLjUsMjQuMi01Mi4zLDI0LjJjLTIwLjMsMC01MS44LTE0LTg0LjItMzcuMyAgYy0zMS0yMi40LTQ0LjgtMzIuNy03NS45LTY0LjZjLTMyLjktMzMuNy01My42LTYxLjgtNzYuNC0xMDMuNWMtMjQuMS00NC4xLTIxLjQtNjMuNC0xNi41LTc5LjNjMi42LTguNSwxMC40LTE3LjYsMjEtMjQuMiAgYzAuNS0wLjMsMS0wLjcsMS42LTFjNS4zLTMuNCwxNC4xLTkuMSwyMy43LTkuMWM4LDAsMTUuMSw0LDIxLjksMTIuM2wxLDEuMmMyNS41LDMxLjIsNDUuNCw1OC44LDMwLjQsNzkuMiAgYy0xMC42LDE0LjMtMTYuMiwyNC0xNS4zLDM0YzAuOCw5LjcsNy4zLDE3LDE3LjEsMjhsMC43LDAuOGMxNi4xLDE4LjIsMjAuNywyMywyNy4xLDI5LjVjMS4xLDEuMSwyLjIsMi4zLDMuNSwzLjZsMS44LDEuOSAgYzcuNCw3LjcsMTEuNSwxMS45LDMwLjMsMjguNGwxLjEsMWM4LDcsMTMuOSwxMi4xLDIyLjUsMTIuMWM4LjksMCwxOC43LTUuNiwzNy4zLTE3LjVjMS45LTEuMiw0LjYtMS45LDgtMS45ICBjMjEuNywwLDU5LjEsMjQuOCw3Mi4yLDM4LjNDNDE3LDM1OS43LDQyMywzNjguOSw0MDQuNCwzOTEuNHoiLz48L3N2Zz4=");
                                        }
                                    }
                                }
                                &:hover {
                                    background: #f0f0f0;
                                }


                            } /* autocomplete__results__item */
    } /* autocomplete__results */
  } /* formgroup */
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
      margin: 0 0 10px;
      @media (min-width: 320px) and (max-width: 767px) {
        width: 100%;
        padding: 0;
      } /* Mobile */
      .vue-tel-input {
        display: flex;
        width: 100%;
        height: 40px;
        background: #fafafa;
        border: 1px solid #dddddd !important;
        line-height: 40px;
        font-size: 15px;
        color: #000;
        border-radius: 4px;
        padding: 0;
        text-align: right;
        align-items: center;
        box-shadow: none;
        [dir="ltr"] & {
          text-align: left;
        } /* rtl */
        .dropdown {
          padding: 0;
          width: 70px;
          background: #f5f5f5;
          height: 38px;
          border-left: 1px solid #dddddd;
          border-radius: 0 4px 4px 0;
          [dir="ltr"] & {
            border-right: 1px solid #dddddd;
            border-left: none;
            border-radius: 4px 0 0 4px;
          } /* rtl */
          span.selection {
            display: flex !important;
            height: 40px;
            justify-content: center;
            align-items: center;
            width: auto;
            margin: 0 auto;
            font-size: 12px !important;
            .iti-flag {
              margin: 0;
            } /* iti-flag */
            span.dropdown-arrow {
              width: auto;
              margin: 0 5px 0 0;
              display: inline-block !important;
              font-size: inherit !important;
              [dir="ltr"] & {
                margin: 0 0 0 5px;
              } /* ltr */
            } /* dropdown-arrow */
          } /* selection */
          ul {
            margin: 0 auto;
            left: auto;
            right: 0;
            width: auto;
            min-width: 210px;
            top: 43px;
            max-width: 386px;
            border-radius: 4px;
            text-align: inherit;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f5f5f5;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
            [dir="ltr"] & {
              left: 0;
              right: auto;
            } /* rtl */
            li {
              direction: rtl;
              display: flex;
              align-items: center;
              justify-content: flex-start;
              padding: 3px 10px;
              line-height: normal;
              font-weight: normal;
              color: #000;
              [dir="ltr"] & {
                direction: ltr;
              } /* ltr */
              .iti-flag {
                margin: 0;
              } /* iti-flag */
              strong {
                display: block;
                font-weight: normal;
                font-size: 15px;
                margin: 0 5px;
              } /* strong */
              span {
                direction: ltr;
                color: #666 !important;
                font-size: inherit !important;
              } /* span */
            } /* li */
          } /* ul */
        } /* dropdown */
        input {
          width: 76%;
          border-radius: 0 !important;
          height: 38px !important;
          border: none !important;
          padding: 0 10px 0 0 !important;
          [dir="ltr"] & {
            padding: 0 0 0 10px !important;
          } /* ltr */
        } /* input */
      } /* vue-tel-input */
    } /* col */
  } /* row_group */
  label {
    display: block;
    margin: 0 auto 5px;
    font-size: 15px;
    i {
      display: inline-block !important;
      margin: 0 5px 0 0;
      color: #f00 !important;
      font-style: normal;
    } /* i */
  } /* label */
  input {
    height: 40px !important;
    padding: 0 10px !important;
    color: #000 !important;
    font-size: 15px !important;
    border: 1px solid #dddddd !important;
    background: #fafafa !important;
    width: 100%;
    &[readonly="readonly"] {
      cursor: pointer;
    } /* readonly */
    &.customer_search {
      background: transparent !important;
      border: none !important;
      height: 40px !important;
      border-radius: 0 !important;
      padding: 0 10px !important;
      display: block;
    } /* customer_search */
  } /* input */
  label.customer_highlight {
    height: 40px;
    border-radius: 4px;
    text-align: center;
    font-size: 15px;
    line-height: 40px;
    color: #000;
    margin: 0 auto;
  } /* customer_highlight */
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
  button.update_customer_button {
    background: #4099de;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 100px;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    padding: 0 15px;
    color: #ffffff;
    width: 100%;
    margin: 0 auto 10px;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
  } /* update_customer_button */

  button.clear_customer {
    background: #4099de;
    border-radius: 5px;
    border: 1px solid #4099de;
    min-width: 100px;
    height: 35px;
    line-height: 35px;
    font-size: 15px;
    padding: 0 15px;
    color: #ffffff;
    // width: 100%;
    margin: 5px auto 0;
    -webkit-transition: all 0.2s ease-in-out;
    -moz-transition: all 0.2s ease-in-out;
    -o-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
    &:hover {
      background: #0071C9;
      border-color: #0071C9;
    } /* hover */
  } /* clear_customer */
} /* update_customer_modal */
.headers{
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.add-company {

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
  background: #4099de;
  border-radius: 5px;
  border: 1px solid #4099de;
  min-width: 100px;
  height: 35px;
  line-height: 35px;
  font-size: 15px;
  padding: 0 15px;
  color: #ffffff;
  width: 100%;
  margin: 0 auto 10px;
  -webkit-transition: all 0.2s ease-in-out;
  -moz-transition: all 0.2s ease-in-out;
  -o-transition: all 0.2s ease-in-out;
  transition: all 0.2s ease-in-out;
  &:hover {
    background: #0071C9;
    border-color: #0071C9;
  } /* hover */
} /* button */
} /* add_company */


</style>

<style lang="scss">
#change_company_modal {
  .sweet-modal {
    overflow: visible !important;
    .sweet-content {
        overflow: visible !important;
    }
  }
}
</style>