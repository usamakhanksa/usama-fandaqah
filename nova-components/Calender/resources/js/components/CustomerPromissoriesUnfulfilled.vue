<template>

    <div>
        <sweet-modal  :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Unfulfilled Promissories Log On Customer :customer' , {customer : customer.name })" overlay-theme="dark" class="customer_notes_modal" ref="promissoriesModal">
            <div class="content_page">

                <div class="table_area">
                    <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>
                    <div class="table_responsive">
                        <table>
                            <thead>
                            <tr>
                                <th>{{__('Reservation Number')}}</th>
                                <th>{{__('Amount Total')}} </th>
                                <th>{{__('Amount Fulfilled')}} </th>
                                <th>{{__('Remaining Amount')}} </th>
                                <th>{{__('Due Date')}}</th>

                            </tr>
                            </thead>
                            <tbody>
                               <tr v-for="(promissory,i) in collection" :key="i">
                                   <td>{{promissory.reservation.number}}</td>
                                   <td>{{promissory.amount_total}} {{__(currency)}}</td>
                                   <td>{{promissory.amount_fulfilled}} {{__(currency)}}</td>
                                   <td>{{promissory.amount_remaining}} {{__(currency)}}</td>
                                   <td>{{promissory.due_date | formatDateSpecial}}</td>
                               </tr>
                            </tbody>
                        </table>
                        <br>
                        <div >

                            <pagination
                                v-if="paginator && paginator.totalResults > 10"
                                :page-count="paginator.lastPage"
                                :page-range="3"
                                :margin-pages="2"
                                :click-handler="getUnfulfilledPromissories"
                                :prev-text="__('Previous')"
                                :next-text="__('Next')"
                                :container-class="'pagination  w-full flex justify-center'"
                                :page-class="'page-item'"
                                :page-link-class="'page-link'"
                                :prev-link-class="'page-link'"
                                :next-link-class="'page-link'"
                                :prev-class="'page-item'"
                                :next-class="'page-item'"
                                :first-last-button="true"
                                :first-button-text="__('First')"
                                :last-button-text="__('Last')"
                                @input="getCurrentPage($event)"
                            >
                            </pagination>
                            <div class="w-full flex justify-between mt-4 mb-2">
                                <p>{{__('Results')}}  : {{__('From')}} ( {{paginator.from}} ) - {{__('To')}}  ( {{paginator.to}} )</p>
                                <p>{{__('Count')}}  : {{paginator.totalResults}}</p>
                            </div>
                        </div><!--  -->


                    </div><!-- table_responsive -->
                </div><!-- table_area -->
            </div>
        </sweet-modal>
    </div>

</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "customer-promissories-unfulfilled",
        components : {
            Loading
        },
        props:['customer' , 'collection' , 'paginator' , 'isLoading'],
        data(){
            isLoading : true
            return {
                isLoading : true,
                currency :Nova.app.currentTeam.currency,

            }

        },
        methods:{
            getCurrentPage(page){
                Nova.$emit('call-get-unfilfilled-promissories' , page);
            },
        },
    }
</script>

<style lang="scss" scoped>
    .notifier_table {
        margin-bottom: 10px;
        text-align: center;
    }
    .content_page {
        background: #fff;
        .filter_area {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin: 0 -10px;
            .item {
                width: 20%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 50%;
                } /* media */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 33.33333%;
                } /* media */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 25%;
                } /* media */
                input {
                    background: #fafafa;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                } /* input */
                select {
                    background-color: #fafafa;
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                    background-repeat: no-repeat;
                    background-size: 14px;
                    background-position: 10px center;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                } /* select */
            } /* item */
            .reset_filters {
                width: 100%;
                display: flex;
                padding: 0 10px;
                margin: 0 0 10px;
                justify-content: flex-end;
                button {
                    height: 40px;
                    width: 40px;
                    background-color: #718096;
                    border-radius: 4px;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: 20px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background-color: #5E6D83;
                    } /* hover */
                } /* button */
            } /* reset_filters */
        } /* filter_area */
        hr {
            margin: 20px auto;
            border-color: #ddd;
            &:last-of-type {
                margin: 0 0 20px;
            } /* last-of-type */
        } /* hr */
        .action_buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin: 0 auto 10px;
            button {
                display: block;
                height: 30px;
                width: 30px;
                margin: 0 10px 0 0;
                outline: none;
                background-position: center center;
                background-size: 25px;
                background-repeat: no-repeat;
                &.excel_button {
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
                } /* excel_button */
                &.print_button {
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                } /* print_button */
            } /* button */
        } /* action_buttons */
        .table_area {
            position: relative;
            .table_responsive {
                @media (min-width: 320px) and (max-width: 767px) {
                    overflow: auto;
                } /* media */
                table {
                    width: 100%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        margin: 0 auto 15px;
                    } /* media */
                    thead {
                        th {
                            background: #4a5568;
                            border: 1px solid #5E697C;
                            font-size: 15px;
                            padding: 10px;
                            vertical-align: middle;
                            color: #fff;
                            font-weight: normal;
                            text-align: center !important;
                        } /* th */
                    } /* thead */
                    tbody {
                        td {
                            background: #fafafa;
                            border: 1px solid #d3d3d3;
                            color: #000;
                            vertical-align: middle;
                            padding: 10px;
                            font-size: 15px;
                            line-height: 20px;
                            text-align: center !important;
                            height: auto;
                            .text-left {
                                text-align: inherit !important;
                            } /* text-left */
                            label#customer-label {
                                display: inline-block;
                                font-size: 14px;
                                border-radius: 4px;
                                padding: 3px 10px;
                                min-width: 60px;
                            } /* customer-label */
                            a {
                                color: #4099de;
                                font-weight: bold;
                                cursor: pointer;
                                &:hover {
                                    color: #0071C9;
                                } /* hover */
                            } /* a */
                            .action {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                flex-wrap: wrap;
                                button {
                                    margin: 5px !important;
                                    color: #b3b9bf;
                                    svg {
                                        display: block;
                                        path {
                                            fill: #b3b9bf;
                                            &:hover {
                                                fill: #4099de;
                                            } /* hover */
                                        } /*  path*/
                                    } /* svg */
                                    &:hover {
                                        color: #4099de;
                                    } /* hover */
                                } /* button */
                            } /* action */
                        } /* td */
                    } /* tbody */
                } /* table */
            } /* table_responsive */
        } /* table_area */
    } /* content_page */
</style>
