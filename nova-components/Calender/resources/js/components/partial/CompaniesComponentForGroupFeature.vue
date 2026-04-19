<template>
  <div class="companies_component mb-5">
    
        <div class="search_company" v-if="reservation_type == 'group'">


            <companies-for-group-screen-component />

            <!-- <div class="label_with_button mb-2">
                <label class="custom_company_label" for="group_auto_complete">
                    {{__('Search For Company')}}
                </label>
                <button  v-permission="'create companies'" @click="openAddCompanyModal">{{__('Add Company')}}</button>
            </div>
            <div class="search_company">
                <autocomplete
                    id="group_auto_complete"
                    ref="autoComplete"
                    :source="companies_endpoint"
                    :resultsDisplay="formatForDropDown"
                    @results="companiesFound"
                    @noResults="noCompaniesFound"
                    @selected="companySelected"
                    @clear="companiesInputCleared"
                    :placeholder="__('Search by Name, Phone or Email')"
                >
                <div slot="noResults" style="display:flex; align-content:center;">
                    <p>{{__('No companies found')}} ... </p>
                </div>

                </autocomplete>
            </div> -->

                <div class="attachable_reservations" v-if="attachable_reservations.length">
                    <company-main-reservation-selector v-if="attachable_reservations" :attachable_reservations="attachable_reservations" />
                </div>

        </div>

        <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Add Company')" overlay-theme="dark" ref="addCompanyReservation" class="add-company relative">

            <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
            <!-- Validation -->
            <company-validation v-if="showErrorBag"  :error-bag="errorBag" />
            <div class="row_group">
                <div class="col">
                <label>{{ __('Company Name') }}<span>*</span></label>
                <input type="text" v-model="company.name" :placeHolder="__('Company Name')">
                </div>
                <div class="col">
                <label>{{__('Company Phone')}}<span>*</span></label>
                <input type="tel" v-model="company.phone" :placeHolder="__('Company Phone')">
                </div>
            </div>
            <div class="row_group">
                <div class="col">
                <label>{{__('City')}}<span>*</span></label>
                <input type="text" v-model="company.city" :placeHolder="__('City')">
                </div>
                <div class="col">
                <label>{{__('Company Address')}}<span>*</span></label>
                <input type="text" v-model="company.address" :placeHolder="__('Company Address')" >
                </div>
            </div>
            <div class="row_group">
                <div class="col">
                <label>{{__('Person In Charge')}}<span>*</span></label>
                <input type="text" v-model="company.person_incharge_name" :placeHolder="__('Person In Charge')" >
                </div>
                <div class="col">
                <label>{{__('Person In Charge Phone')}}</label>
                <input type="text" v-model="company.person_incharge_phone" :placeHolder="__('Person In Charge Phone')" >
                </div>
            </div>
            <div class="row_group">
                <div class="col">
                <label>{{__('Company Email')}}</label>
                <input type="text" v-model="company.email" :placeHolder="__('Company Email')" >
                </div>
                <div class="col">
                <label>{{__('Tax number')}}</label>
                <input type="text" v-model="company.tax_number" :placeHolder="__('Tax number')" >
                </div>
            </div>
            <button :disabled="disabled" @click="createNewCompany">{{__('Save')}}</button>
        </sweet-modal>


  </div>
</template>

<script>
    import CompaniesForGroupScreenComponent from './CompaniesForGroupScreenComponent.vue';

    import CompanyMainReservationSelector from './CompanyMainReservationSelector';
    import CompanyValidation from './CompanyValidation';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "companies-component-for-group-feature",
        components : {
            Loading,
            CompanyValidation,
            CompanyMainReservationSelector,
            CompaniesForGroupScreenComponent
        },
        props : [],
        data(){
            return {
                companies_endpoint: '/nova-vendor/new/customers/company/target/search?entity=company&q=',
                company_id: null,
                reservation_type : 'group',
                disabled : false,
                company: {
                    name:                   null,
                    phone:                  null,
                    city:                   null,
                    address:                null,
                    person_incharge_name:   null,
                    person_incharge_phone:  null,
                    email:                  null,
                    tax_number:             null
                },
                errorBag:               null,
                showErrorBag : false,
                loading: false,
                attachable_reservations : [],
                attachable_reservation : null
            }
        },
        methods : {
            formatForDropDown(company) {
                return company.name + ' ' + company.phone
            },
            companiesFound(){
            },
            noCompaniesFound(){
                this.company_id = null;
                Nova.$emit('companyIdSelected' , this.company_id);
                Nova.$emit('attachable_reservation' , null);
            },
            companiesInputCleared(){
                this.company_id = null;
                this.attachable_reservations = [];
                Nova.$emit('companyIdSelected' , this.company_id );
                Nova.$emit('attachable_reservation' , null);
            },
            companySelected(obj){
                this.company_id = obj.value;
                Nova.$emit('companyIdSelected' , this.company_id);
                this.getCompanAttachableReservations();
            },
            openAddCompanyModal() {
                this.clearInputs();
                this.$refs.addCompanyReservation.open();
            },
            createNewCompany(){

                if (!this.company.name)
                {
                    this.$toasted.show(this.__("Company name is required"), {type: 'error'})
                    return
                }
                if (!this.company.phone)
                {
                    this.$toasted.show(this.__("Company phone is required"), {type: 'error'})
                    return
                }

               
                    if (!this.company.city)
                    {
                        this.$toasted.show(this.__("City is required"), {type: 'error'})
                        return
                    }
                    if (!this.company.address)
                    {
                        this.$toasted.show(this.__("Company address is required"), {type: 'error'})
                        return
                    }
                    if (!this.company.person_incharge_name)
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
                        name                    : this.company.name,
                        phone                   : this.company.phone,
                        city                    : this.company.city,
                        address                 : this.company.address,
                        person_incharge_name    : this.company.person_incharge_name,
                        person_incharge_phone   : this.company.person_incharge_phone,
                        email                   : this.company.email,
                        tax_number              : this.company.tax_number
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
            clearInputs(){
                this.company.name                   = null;
                this.company.phone                  = null;
                this.company.city                   = null;
                this.company.address                = null;
                this.company.person_incharge_name   = null;
                this.company.person_incharge_phone  = null;
                this.company.email                  = null;
                this.company.tax_number             = null;
                this.errorBag               = {};
                this.showErrorBag = false;
            },
            getCompanAttachableReservations(){
                axios.get(`/nova-vendor/calender/companies/${this.company_id}/attachable-reservations`)
                .then(response => {
                    if(response.data.length){
                        this.attachable_reservations = response.data;
                    }
                })
            }
        },
        mounted() {
            Nova.$on('attachable_reservation' , (reservation) => {
                this.attachable_reservation = reservation;
            })
        }
    }
</script>

<style lang="scss">

.search_company {
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

                            input {
                                border: none !important;
                                height: 38px !important;
                                border-radius: 0 !important;
                                background: transparent !important;
                            }

                            /* input */
                        }

                        /* autocomplete__box */
                        ul.autocomplete__results {
                            border: 1px solid #ddd;
                            border-radius: 0 0 5px 5px;
                            margin: -3px 0 0 0;
                            background: #f5f5f5;

                            li.autocomplete__results__item {
                                margin: 0px;
                                 color: #000;
                                    font-size: 15px;
                                    border-bottom: 1px solid #ddd;
                                    padding: 10px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                span {
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


                            }

                            /* autocomplete__results__item */
                        }

                        /* autocomplete__results */
                    }
.label_with_button {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.companies_component {

    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 10px;
    background: #fff;
    box-shadow: 0 2px 4px 0 rgba(0,0,0,0.05);
        .search_company {


            .attachable_reservations{
                margin: 10px 0;
                  label {
                    display: block;
                    margin: 0px;
                    font-size: 14px;
                    font-weight: 500;
                    color: #000;
                  }
                  select {
                    // margin-top: 10px;
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
                    min-width: 33.3333%;
                    height: 35px;
                    line-height: 35px;
                    font-size: 15px;
                    padding: 0 15px;
                    color: #ffffff;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    @media (min-width: 320px) and (max-width: 480px) {
                        min-width: 100%;
                        width: 100%;
                    }
                    /* Mobile */
                    @media (min-width: 481px) and (max-width: 767px) {
                        min-width: 100%;
                        width: 100%;
                    }
                    /* Mobile */
                    @media (min-width: 768px) and (max-width: 991px) {
                        min-width: 50%;
                        width: 50%;
                    }
                    /* Mobile */
                    &:hover {
                        background: #0071C9;
                        border-color: #0071C9;
                    }

                    /* hover */
                } /* button */
            }

               .label_with_button {
                display: flex;
                align-items: center;
                justify-content: space-between;
                    label.custom_company_label {
                        font-size: 14px;
                        font-weight: 500;
                        color: #000;
                    }

                    button {
                        min-width: 130px;
                        padding: 0 10px;
                        width: auto;
                        max-width: none;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
            }
        }
        .reservation_type {
            display: flex;

            margin: 0 0 10px;
            padding: 0 0 10px;
            justify-content: flex-start;
            font-size: 15px;
            color: #000;
            flex-wrap: wrap;
            align-items: center;
            .title {
                width: 25%;
                [dir="ltr"] & {
                    width: 30%;
                } /* rtl */
            } /* title */
            .radios_area {
                width: 75%;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                [dir="ltr"] & {
                    width: 70%;
                } /* rtl */
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
                } /* label */
            } /* radios_area */
        } /* reservation_type */
        .input_group {
            border-bottom: 1px solid #ddd;
            padding: 0 0 10px;
            margin: 0 0 10px;
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            select {
                width: 100%;
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
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
                } /* ltr */
            } /* select */
        } /* input_group */
        .date_range {
            border-bottom: 1px solid #ddd;
            margin: 0 0 10px;
            padding: 0 0 10px;
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            .date_picker_area {
                position: relative;
                input {
                    width: 100%;
                    height: 40px !important;
                    padding: 0 10px !important;
                    background-color: #fafafa !important;
                    border: 1px solid #ddd !important;
                    color: #000;
                    font-size: 15px;
                    cursor: pointer;
                    -webkit-box-sizing: border-box;
                    box-sizing: border-box;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                    border-radius: 5px !important;
                } /* input */
                .vc-popover-content-wrapper {
                    .vc-popover-content {
                        border: 1px solid #dddddd !important;
                        border-radius: 5px !important;
                        .vc-container {
                            background: #fafafa;
                            .vc-title-wrapper {
                                text-align: center;
                                width: 100%;
                                .vc-title {
                                    font-family: 'Dubai-Medium';
                                    font-weight: normal;
                                    font-size: 20px;
                                    line-height: 30px;
                                    height: 30px;
                                    padding: 0 30px;
                                } /* vc-title */
                            } /* vc-title-wrapper */
                            .vc-arrows-container {
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                width: 100%;
                                display: flex;
                                justify-content: space-between !important;
                                align-items: center;
                            } /* vc-arrows-container */
                            .vc-weekday {
                                color: #444444;
                                font-weight: normal;
                                font-size: 13px;
                                margin: 0 5px;
                                @media (min-width: 320px) and (max-width: 480px) {
                                    margin: 0 1px;
                                } /* Mobile */
                                @media (min-width: 481px) and (max-width: 767px) {
                                    margin: 0 3px;
                                } /* Mobile */
                            } /* vc-weekday */
                        } /* vc-container */
                    } /* vc-popover-content */
                } /* vc-popover-content-wrapper */
            } /* date_picker_area */
            .date_picker_alert {
                display: block;
                margin: 10px 0 0;
                background: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                border-radius: 4px;
                padding: 10px;
                font-size: 15px;
                span {
                    display: block;
                    font-family: 'Dubai-Bold';
                } /* span */
            } /* date_picker_alert */
        } /* date_range */
        .sourceId {
            label {
                display: block;
                font-size: 15px;
                color: #000;
                margin: 0 auto 5px;
            } /* label */
            select {
                width: 100%;
                background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
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
                } /* ltr */
            } /* select */
        } /* sourceId */
        .loader_item {
            padding: 50px;
        } /* loader_item */
        ul {
            li {
                border-top: 1px solid #dddddd;
                margin: 10px 0 0;
                padding: 10px 0 0;
                display: flex;
                justify-content: flex-start;
                flex-wrap: wrap;
                align-items: center;
                font-size: 15px;
                color: #000000;
                .name {
                    width: 25%;
                    [dir="ltr"] & {
                        width: 30%;
                    } /* rtl */
                } /* name */
                .desc {
                    width: 75%;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* desc */

                .total_price {
                    width: 75%;
                    direction: ltr;
                    font-size: 20px;
                    color: #000;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* total_price */
                .night_count {
                    width: 75%;
                    font-size: 20px;
                    color: #000;
                    [dir="ltr"] & {
                        width: 70%;
                    } /* rtl */
                } /* night_count */
            } /* li */
        } /* ul */

        button {
                background: #4099de;
                border-radius: 5px;
                border: 1px solid #4099de;
                min-width: 33.3333%;
                height: 35px;
                line-height: 35px;
                font-size: 15px;
                padding: 0 15px;
                color: #ffffff;
                -webkit-transition: all 0.2s ease-in-out;
                -moz-transition: all 0.2s ease-in-out;
                -o-transition: all 0.2s ease-in-out;
                transition: all 0.2s ease-in-out;
                @media (min-width: 320px) and (max-width: 480px) {
                    min-width: 100%;
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 481px) and (max-width: 767px) {
                    min-width: 100%;
                    width: 100%;
                }
                /* Mobile */
                @media (min-width: 768px) and (max-width: 991px) {
                    min-width: 50%;
                    width: 50%;
                }
                /* Mobile */
                &:hover {
                    background: #0071C9;
                    border-color: #0071C9;
                }

                /* hover */
        }
    } /* companies_component */

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
