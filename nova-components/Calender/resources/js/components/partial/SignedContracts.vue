<template>
  <div class="item_reservation_button">
    <sweet-modal
        :enable-mobile-fullscreen="false"
        :pulse-on-block="false"
        width="70%"
        :title="__('Signed Contracts') + ' #' + reservation.number"
        overlay-theme="dark"
        ref="signedContractsModal"
        class="contract_modal relative"
        @open="open"
        >
        <loading
            :active="isLoading"
            :loader="'spinner'"
            :color="'#7e7d7f'"
            :opacity="0.6"
            :is-full-page="false"
        />

        <div v-if="reservation.signed_contracts" class="p-4">
            <!-- Centered Heading -->
            <div class="text-center text-black text-xl font-bold mb-6">
            {{ __('Signed Contracts') }} ({{ reservation.signed_contracts.length }})
            </div>

        <div class="table_contracts">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>{{__('Signature Date')}}</th>
              <th>{{__('Actions')}}</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="reservation.signed_contracts.length">
              <tr v-for="(contract,i) in reservation.signed_contracts" :key="i">
                <td>{{ reservation.signed_contracts.length - i }}</td>
                <td>{{ contract.signed_at | formatDateWithAmPm }}</td>
            
                <td class="actions">
      
                  <a class="contract_btn_print" target="_blank" :href="aws_storage_url + contract.html_path"></a>

                </td>
              </tr>
            </template>
          </tbody>
        </table>
        </div><!-- table_contracts -->


        </div>
        </sweet-modal>

  </div>
</template>

<script>
  
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name : 'signed-contracts',
        props : ['reservation'],
        components: {
            Loading
        },
        data() {
            return {
                locale : null,
                isLoading: false,
                aws_storage_url : window.AWS_BUCKET_STORAGE_URL
            }
        },

        methods: {
          open(){
          
          }
        },
        mounted() {
          this.locale = Nova.config.local;
          Nova.$on("loading", (isLoading) => {
            this.isLoading = isLoading;
          });
        }

    }
</script>

<style lang="scss">

 .contract_modal {
   .sweet-modal {
      @media (min-width: 768px) and (max-width: 991px) {
        width: 95% !important;
      } /* @media */
   } /* sweet-modal */
  .embed_area {
    max-height: 500px;
    height: 100%;
    overflow-y: auto;
    display: block !important;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    @media (min-width: 320px) and (max-width: 480px) {
      display: none !important;
    } /* @media */
    iframe {
      width: 100%;
      height: 100%;
      min-height: 500px;
    } /* iframe */
  } /* embed_area */
 } /* contract_modal */

 .table_contracts {
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
            &.actions {
                display: flex;
                justify-content: center;
                align-items: center;

                a {
                    display: block;
                    height: 20px;
                    width: 20px;
                    background-position: center center;
                    background-size: contain;
                    background-repeat: no-repeat;
                    margin: 5px;
                    cursor: pointer;

                    &.show_credit_note_button {
                        background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSIxLURvY3VtZW50IiBpZD0iXzEtRG9jdW1lbnQiIHZpZXdCb3g9IjAgMCA0OCA0OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGUvPjxwYXRoIGQ9Ik00Mi43MSw4LjI5bC04LThBMSwxLDAsMCwwLDM0LDBIOEEzLDMsMCwwLDAsNSwzVjQ1YTMsMywwLDAsMCwzLDNINDBhMywzLDAsMCwwLDMtM1Y5QTEsMSwwLDAsMCw0Mi43MSw4LjI5Wk0zNSwzLjQxLDM5LjU5LDhIMzZhMSwxLDAsMCwxLTEtMVpNNDEsNDVhMSwxLDAsMCwxLTEsMUg4YTEsMSwwLDAsMS0xLTFWM0ExLDEsMCwwLDEsOCwySDMzVjdhMywzLDAsMCwwLDMsM2g1WiIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjIwIiB4PSIxNiIgeT0iMTgiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyNCIgeD0iMTIiIHk9IjI0Ii8+PHJlY3QgaGVpZ2h0PSIyIiB3aWR0aD0iMjQiIHg9IjEyIiB5PSIzMCIvPjxyZWN0IGhlaWdodD0iMiIgd2lkdGg9IjE2IiB4PSIxMiIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzNCIgeT0iMzYiLz48cmVjdCBoZWlnaHQ9IjIiIHdpZHRoPSIyIiB4PSIzMCIgeT0iMzYiLz48L3N2Zz4=");

                    }
                    &.add_credit_note_button {
                        background-image: url("data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pjxzdmcgdmlld0JveD0iMCAwIDM2MCA0MTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNjNWM2ZWY7fS5jbHMtMntmaWxsOiMzODU4Njg7fS5jbHMtM3tmaWxsOiM0MjY1NzI7fS5jbHMtNHtmaWxsOiM2YWUxODQ7fS5jbHMtNXtmaWxsOiNhOGZmOGM7fS5jbHMtNntmaWxsOiMzODlkOWM7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZS8+PGcgZGF0YS1uYW1lPSJMYXllciAyIiBpZD0iTGF5ZXJfMiI+PGcgZGF0YS1uYW1lPSJMYXllciAxIiBpZD0iTGF5ZXJfMS0yIj48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0yODcsMzk5bDktNmExMywxMywwLDAsMS0xMywxM0gxOUExMywxMywwLDAsMSw2LDM5M1YyMUExMywxMywwLDAsMSwxOSw4SDEzTDM1LjUsMzYzLjVhMTMsMTMsMCwwLDAsMTMsMTNaIi8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iODQuNzIiIHg9IjY2LjI4IiB5PSI3Mi40MiIvPjxyZWN0IGNsYXNzPSJjbHMtMiIgaGVpZ2h0PSIxNC4wMyIgd2lkdGg9Ijg0LjczIiB4PSI2Ni4yOCIgeT0iMTQ2Ljg4Ii8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iMTM2LjA3IiB4PSI2Ni4yOCIgeT0iMTA4LjM3Ii8+PHJlY3QgY2xhc3M9ImNscy0yIiBoZWlnaHQ9IjE0LjAzIiB3aWR0aD0iODQuNzIiIHg9IjY2LjI4IiB5PSIyMzcuNDIiLz48cmVjdCBjbGFzcz0iY2xzLTIiIGhlaWdodD0iMTQuMDMiIHdpZHRoPSI4NC43MyIgeD0iNjYuMjgiIHk9IjMxMS44OCIvPjxyZWN0IGNsYXNzPSJjbHMtMiIgaGVpZ2h0PSIxNC4wMyIgd2lkdGg9IjEzNi4wNyIgeD0iNjYuMjgiIHk9IjI3My4zNyIvPjxwYXRoIGNsYXNzPSJjbHMtMyIgZD0iTTI4Myw0MTBIMTlBMTksMTksMCwwLDEsMCwzOTFWMTlBMTksMTksMCwwLDEsMTksMEgyODNhMTksMTksMCwwLDEsMTksMTlWMzkxQTE5LDE5LDAsMCwxLDI4Myw0MTBaTTE5LDEyYTcsNywwLDAsMC03LDdWMzkxYTcsNywwLDAsMCw3LDdIMjgzYTcsNywwLDAsMCw3LTdWMTlhNyw3LDAsMCwwLTctN1oiLz48Y2lyY2xlIGNsYXNzPSJjbHMtNCIgY3g9IjI5NS41IiBjeT0iMjI5LjUiIHI9IjU4LjUiLz48cGF0aCBjbGFzcz0iY2xzLTUiIGQ9Ik0zNDcsMjI2LjVhNTguNTIsNTguNTIsMCwwLDEtNjUsNTguMTQsNTguNSw1OC41LDAsMCwwLDAtMTE2LjI4LDU4LjUyLDU4LjUyLDAsMCwxLDY1LDU4LjE0WiIvPjxwYXRoIGNsYXNzPSJjbHMtNiIgZD0iTTMwNiwyODQuNTJhNTguNSw1OC41LDAsMSwxLDAtMTE2LDU4LjUsNTguNSwwLDAsMCwwLDExNloiLz48cGF0aCBjbGFzcz0iY2xzLTMiIGQ9Ik0yOTUuNSwyOTFBNjQuNSw2NC41LDAsMSwxLDM2MCwyMjYuNSw2NC41Nyw2NC41NywwLDAsMSwyOTUuNSwyOTFabTAtMTE3QTUyLjUsNTIuNSwwLDEsMCwzNDgsMjI2LjUsNTIuNTYsNTIuNTYsMCwwLDAsMjk1LjUsMTc0WiIvPjxwYXRoIGNsYXNzPSJjbHMtMyIgZD0iTTI4NC42MywyMTF2MzcuNzVjMCw5LjY1LDE1LDkuNjcsMTUsMFYyMTFjMC05LjY1LTE1LTkuNjctMTUsMFoiLz48cGF0aCBjbGFzcz0iY2xzLTMiIGQ9Ik0zMTEsMjIyLjM4SDI3My4yNWMtOS42NSwwLTkuNjcsMTUsMCwxNUgzMTFjOS42NSwwLDkuNjctMTUsMC0xNVoiLz48L2c+PC9nPjwvc3ZnPg==");

                    }
                } /* a */
            }

          } /* td */
        } /* tr */
      } /* tbody */
    } /* table */
  } /* table_contracts */


  .contract_btn_print {
    display: block;
    height: 25px;
    width: 25px;
    outline: none;
    background-position: 50%;
    background-size: 20px 20px;
    background-repeat: no-repeat;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M15.471 15.471v1.934a1.934 1.934 0 0 1-1.934 1.934H5.8a1.934 1.934 0 0 1-1.934-1.934v-1.934H1.934A1.934 1.934 0 0 1 0 13.537v-5.8A1.94 1.94 0 0 1 1.934 5.8h1.934V1.934A1.94 1.94 0 0 1 5.8 0h7.735a1.934 1.934 0 0 1 1.934 1.934V5.8H17.4a1.934 1.934 0 0 1 1.934 1.934v5.8a1.934 1.934 0 0 1-1.934 1.937zm0-1.934H17.4v-5.8H1.934v5.8h1.934V11.6A1.94 1.94 0 0 1 5.8 9.669h7.735a1.934 1.934 0 0 1 1.936 1.931zM13.537 5.8V1.934H5.8V5.8zM5.8 11.6v5.8h7.735v-5.8z' fill='%23333b45'/%3E%3C/svg%3E");
} /* print */
</style>
