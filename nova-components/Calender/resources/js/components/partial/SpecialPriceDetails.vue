<template>
    <!-- View Special Price Details Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Special Price Details')" overlay-theme="dark" ref="specialPriceFromNewReservation" class="View_Special_Price_modal relative">
        <div class="table_responsive">
            <table>
                <tbody>
                <tr>
                    <td>{{__('Name')}}</td>
                    <td>{{specialPriceObject.name}}</td>
                </tr>
                <tr>
                    <td>{{__('Unit category')}}</td>
                    <td v-if="specialPriceObject.unit_category">{{specialPriceObject.unit_category.name[locale]}}</td>
                </tr>
                <tr>
                    <td>{{__('Special Prices')}}</td>
                    <td>
                        <template v-if="specialPriceObject.days_prices">
                            <ul>
                                <li>{{__('Sunday')}} : {{specialPriceObject.days_prices.Sunday != null ? specialPriceObject.days_prices.Sunday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Monday')}} : {{specialPriceObject.days_prices.Monday != null ? specialPriceObject.days_prices.Monday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Tuesday')}} : {{specialPriceObject.days_prices.Tuesday != null ? specialPriceObject.days_prices.Tuesday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Wednesday')}} : {{specialPriceObject.days_prices.Wednesday != null ? specialPriceObject.days_prices.Wednesday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Thursday')}} : {{specialPriceObject.days_prices.Thursday != null ? specialPriceObject.days_prices.Thursday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Friday')}} : {{specialPriceObject.days_prices.Friday != null ? specialPriceObject.days_prices.Friday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Saturday')}} : {{specialPriceObject.days_prices.Saturday != null ? specialPriceObject.days_prices.Saturday : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                            </ul>
                        </template>
                    </td>
                </tr>
                 <tr>
                    <td>{{__('Original Prices')}}</td>
                    <td>
                        <template v-if="specialPriceObject.unit_category">

                            <ul>

                                <li>{{__('Sunday')}} : {{specialPriceObject.unit_category.sunday_day_price != null ? specialPriceObject.unit_category.sunday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Monday')}} : {{specialPriceObject.unit_category.monday_day_price != null ? specialPriceObject.unit_category.monday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Tuesday')}} : {{specialPriceObject.unit_category.tuesday_day_price != null ? specialPriceObject.unit_category.tuesday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Wednesday')}} : {{specialPriceObject.unit_category.wednesday_day_price != null ? specialPriceObject.unit_category.wednesday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Thursday')}} : {{specialPriceObject.unit_category.thursday_day_price != null ? specialPriceObject.unit_category.thursday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Friday')}} : {{specialPriceObject.unit_category.friday_day_price != null ? specialPriceObject.unit_category.friday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                                <li>{{__('Saturday')}} : {{specialPriceObject.unit_category.saturday_day_price != null ? specialPriceObject.unit_category.saturday_day_price  : __('No Price Added') }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span></li>
                            </ul>
                        </template>
                    </td>
                </tr>
                <tr>
                    <td>{{__('Special Price Start & End Date')}}</td>
                    <td>{{ specialPriceObject.start_date | formatDateSpecial }} - {{specialPriceObject.end_date | formatDateSpecial}}</td>
                </tr>
                <tr>
                    <td>{{__('Special Price Status')}}</td>
                    <td>
                        <label class="Enabled" v-if="specialPriceObject && specialPriceObject.enabled">{{__('Enabled')}}</label>
                        <label class="Not_Enabled" v-else-if="specialPriceObject && !specialPriceObject.enabled">{{__('Not Enabled')}}</label>
                    </td>
                </tr>
                </tbody>
            </table>
        </div><!-- table_responsive -->
    </sweet-modal>
    <!-- View Special Price  Details Modal -->

</template>

<script>

    export default {
        name: "SpecialPriceDetails",
        data(){
        return{
            currency :Nova.app.currentTeam.currency,

        }
    },
        props : ['specialPriceObject','locale'],
        mounted(){

        }
    }
</script>

<style lang="scss" scoped>
    .View_Special_Price_modal {
        .sweet-content {
            max-height: 500px;
            overflow-y: auto;
            display: block !important;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f5f5f5;
            &::-webkit-scrollbar {width: 6px;}
            &::-webkit-scrollbar-track {background: #f5f5f5;}
            &::-webkit-scrollbar-thumb {background: #ccc;}
            &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
        } /* sweet-content */
        div.label {
            display: block;
            margin: 0 0 10px;
            color: #fff;
            padding: 0 25px;
            border-radius: 100px;
            height: 25px;
            line-height: 25px;
            font-size: 15px;
        } /* div.label */
        .table_responsive {
            @media (min-width: 320px) and (max-width: 767px) {
                overflow: auto;
            } /* media */
            table {
                width: 100%;
                @media (min-width: 320px) and (max-width: 767px) {
                    margin: 0 auto 15px;
                } /* media */
                tr {
                    td {
                        &:first-child {
                            background: #4a5568;
                            border: 1px solid #5E697C;
                            font-size: 15px;
                            padding: 10px;
                            vertical-align: middle;
                            color: #fff;
                            font-weight: normal;
                            text-align: inherit;
                            width: 30%;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 50%;
                            } /* media */
                        } /* first-child */
                        &:last-child {
                            background: #fafafa;
                            border: 1px solid #d3d3d3;
                            color: #000;
                            vertical-align: middle;
                            padding: 10px;
                            font-size: 15px;
                            line-height: 20px;
                            text-align: inherit;
                        } /* first-child */
                        label {
                            border-radius: 100px;
                            padding: 0 20px;
                            height: 25px;
                            line-height: 25px;
                            color: #fff;
                            font-size: 15px;
                            display: inline-block;
                            &.Enabled {
                                background: green;
                            } /* Enabled */
                            &.Not_Enabled {
                                background: red;
                            } /* Not_Enabled */
                        } /* label */
                        ul {
                            li {
                                display: inline-block;
                                margin: 2.5px;
                                background: #ffffff;
                                border-radius: 4px;
                                font-size: 14px;
                                padding: 2px 5px;
                                border: 1px solid #eee;
                            } /* li */
                        } /* ul */
                    } /* td */
                } /* tr */
            } /* table */
        } /* table_responsive */
    } /* View_offer_modal */
</style>
