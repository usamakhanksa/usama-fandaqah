<template>
  <div>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Promissory Fulfillment History')" overlay-theme="dark" ref="promissoryFulfillmentHistory" class="invoices_list_modal">
      <div class="table_invoices relative">
        <loading :active="loading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7" :height="20" :width="20" :is-full-page="false"></loading>
        <table>
          <thead>
            <tr>
              <th>{{__('Transaction Number')}}</th>
              <th>{{__('Amount')}}</th>
              <th>{{__('Date')}}</th>
              <th width="15%"></th>
            </tr>
          </thead>
          <tbody>
            <template v-if="transactions.length">
              <tr v-for="(transaction , i) in transactions" :key="i">
                <td>{{transaction.number}}</td>
                <td>{{(transaction.amount).toFixed(2)}}</td>
                <td>{{transaction.date }}</td>
                <td>
                  <a  class="cursor-pointer text-70 hover:text-primary cursor-pointer" @click="openTransactionModal(transaction)" ><svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg></a>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </sweet-modal>

        <promissory-single-fulfill-modal ref="transaction" :transaction="targetTransaction" />

  </div>
</template>

<script>
    import PromissorySingleFulfillModal from './PromissorySingleFulfillModal';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "promissory-fulfillment-history",
        components:{
            Loading,
            PromissorySingleFulfillModal
        },
        data(){
            return {
                loading : false,
                transactions : [],
                targetTransaction : null,
                currency :Nova.app.currentTeam.currency,

            }
        },
        methods : {
          openTransactionModal(transaction)
          {
            this.targetTransaction = transaction;
            this.$nextTick(() => {
              this.$refs.transaction.$refs.openPrint.open();
            })
          }
        },
        mounted() {
            Nova.$on('open-promissory-fulfilllment-history' , (id) => {
                this.loading = true;
                const self = this;
                axios.get(window.FANDAQAH_API_URL + `/promissories/show?id=${id}`)
                .then(response => {
                    this.transactions = response.data.data.transactions;
                    this.$nextTick(() => {
                      self.$refs.promissoryFulfillmentHistory.open();
                    });
                    this.loading = false;
                })
            })

        },
    }
</script>

<style lang="scss">
.invoices_list_modal {
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
  .table_invoices {
    width: 100%;
    overflow: auto;
    margin: 0 auto 10px;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    table {
      border: 1px solid #e2e8f0;
      width: 100%;
      th {
        padding:  5px;
        line-height: normal;
        font-weight: normal;
        font-size: 15px;
        border: 1px solid #5E697C;
        vertical-align: middle;
        text-align: center;
        color: #ffffff;
        background: #4a5568;
      } /* th */
      tbody {
        tr {
          background: #fff;
          td {
            text-align: center;
            padding: 5px;
            vertical-align: middle;
            line-height: normal;
            font-size: 15px;
            border: 1px solid #ced4dc;
            color: #000000;
            font-weight: normal;
            background: #ffffff;
            a {
              display: inline-block;
              margin: 5px;
            } /* a */
          } /* td */
        } /* tr */
      } /* tbody */
    } /* table */
  } /* table_invoices */
} /* invoices_list_modal */
</style>
