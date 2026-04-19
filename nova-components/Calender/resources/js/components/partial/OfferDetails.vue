<template>
    <!-- View Special Price Details Modal -->
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Offer Details')" overlay-theme="dark" ref="offerFromNewReservation" class="View_offer_modal relative">
         <div class="w-full flex justify-end">
        <div v-if="offerDetailsObject.offer_dates_status === 'out-dated'" class="label bg-red-500">{{__('Expired')}}</div>
        <div v-if="offerDetailsObject.offer_dates_status === 'between'"   class="label bg-green-500">{{__('Can be applied')}}</div>
        <div v-if="offerDetailsObject.offer_dates_status === 'incoming'"  class="label bg-blue-500">{{__('UpComing')}}</div>
      </div><!-- flex -->
      <div class="table_responsive">
        <table>
          <tbody>
            <tr>
              <td>{{__('Offer Name')}}</td>
              <td>{{offerDetailsObject.name}}</td>
            </tr>
            <tr>
              <td>{{__('Discount Type')}}</td>
              <td>{{__(offerDetailsObject.discount_type)}}</td>
            </tr>
            <tr>
              <td>{{__('Discount Amount')}}</td>
              <td>{{offerDetailsObject.discount_amount}}  {{ offerDetailsObject.discount_type == 'percentage' ? '%' : __(currency) }}</td>
            </tr>
            <tr>
              <td>{{__('Unit Categories Included')}}</td>
              <td><p v-for="(category,i) in offerDetailsObject.categories" :key="i">{{category.name[locale]}}</p></td>
            </tr>
            <tr>
              <td>{{__('Discount will be applied on the following days')}}</td>
              <td><span v-for="(day,i) in offerDetailsObject.days" :key="i">{{__(day.charAt(0).toUpperCase() + day.slice(1))}}</span></td>
            </tr>
            <tr>
              <td>{{__('Offer Price Start and End Date')}}</td>
              <td>{{ offerDetailsObject.start_date | formatDateSpecial }} - {{offerDetailsObject.end_date | formatDateSpecial}}</td>
            </tr>
            <tr>
              <td>{{__('Show status')}}</td>
              <td>
                <label class="Enabled" v-if="offerDetailsObject && offerDetailsObject.enabled">{{__('Enabled')}}</label>
                <label class="Not_Enabled" v-else-if="offerDetailsObject && !offerDetailsObject.enabled">{{__('Not Enabled')}}</label>
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
        name: "OfferDetails",
        props : ['offerDetailsObject','locale'],
        data(){
        return{
            currency :Nova.app.currentTeam.currency,

        }
    },
        mounted(){

        }
    }
</script>

<style lang="scss" scoped>
     .View_offer_modal {
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
          p {
            display: inline-block;
            margin: 2.5px;
            background: #ffffff;
            border-radius: 4px;
            font-size: 14px;
            padding: 2px 5px;
            border: 1px solid #eee;
          } /* p */
          span {
            display: inline-block;
            margin: 2.5px;
            background: #ffffff;
            border-radius: 4px;
            font-size: 14px;
            padding: 2px 5px;
            border: 1px solid #eee;
          } /* span */
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
        } /* td */
      } /* tr */
    } /* table */
  } /* table_responsive */
} /* View_offer_modal */
</style>
