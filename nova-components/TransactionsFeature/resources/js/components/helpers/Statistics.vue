<template>
    <div class="flex flex-wrap -mx-3 mb-3">
         <card class="flex flex-col items-center justify-center custom-panel"  >
             <loading-view :loading="loading" class="w-full">
                 <div class="w-full flex flex-reverse flex-row-reverse">
                     <div class="w-1/5 flex flex-row-reverse items-center justify-center">
                         <svg xmlns="http://www.w3.org/2000/svg" width="38.851" height="38.396" viewBox="0 0 38.851 38.396"><g transform="translate(0 -3)"><g transform="translate(22.76 22.213)"><path d="M303.141,261.676a1.46,1.46,0,1,1,1.669-1.445.759.759,0,1,0,1.518,0,3.019,3.019,0,0,0-2.428-2.877v-.395a.759.759,0,0,0-1.518,0v.395a3.019,3.019,0,0,0-2.428,2.877,3.084,3.084,0,0,0,3.187,2.962,1.46,1.46,0,1,1-1.669,1.445.759.759,0,0,0-1.518,0,3.019,3.019,0,0,0,2.428,2.877v.328a.759.759,0,1,0,1.518,0v-.328a3.019,3.019,0,0,0,2.428-2.877A3.084,3.084,0,0,0,303.141,261.676Z" transform="translate(-299.954 -256.201)" fill="#333b45"/></g><g transform="translate(29.132 19.525)"><path d="M389.051,224.434a9.887,9.887,0,0,0-4.038-3.574.759.759,0,1,0-.658,1.368,8.354,8.354,0,0,1,3.763,11.442.759.759,0,1,0,1.34.713,9.875,9.875,0,0,0-.406-9.949Z" transform="translate(-383.924 -220.785)" fill="#333b45"/></g><g transform="translate(26.33 18.631)"><path d="M347.81,209h-.051a.759.759,0,1,0,0,1.518h.048a.759.759,0,0,0,0-1.518Z" transform="translate(-347 -209.001)" fill="#333b45"/></g><g transform="translate(16.087 23.466)"><path d="M218.255,285.269a8.354,8.354,0,0,1-3.762-11.442.759.759,0,0,0-1.34-.713,9.869,9.869,0,0,0,4.445,13.523.759.759,0,1,0,.657-1.368Z" transform="translate(-212.002 -272.711)" fill="#333b45"/></g><g transform="translate(24.007 36.842)"><path d="M317.184,449h-.044a.759.759,0,0,0-.007,1.518h.051a.759.759,0,0,0,0-1.518Z" transform="translate(-316.378 -448.999)" fill="#333b45"/></g><g transform="translate(0 3)"><g transform="translate(0 0)"><path d="M29.4,16.064a3.483,3.483,0,0,0-.337-.62h1.975a3.49,3.49,0,1,0,0-6.981H24.958A3.488,3.488,0,0,0,22.081,3H3.49a3.49,3.49,0,1,0,0,6.981H9.567a3.481,3.481,0,0,0,0,3.946H7.588a3.488,3.488,0,0,0-2.169,6.222,3.484,3.484,0,0,0,0,5.463,3.484,3.484,0,0,0,0,5.463A3.488,3.488,0,0,0,7.588,37.3H16.53A12.9,12.9,0,1,0,29.4,16.064Zm1.638-6.083a1.973,1.973,0,0,1,0,3.946H12.444a1.973,1.973,0,0,1,0-3.946ZM3.49,8.463a1.973,1.973,0,0,1,0-3.946H22.081a1.973,1.973,0,0,1,0,3.946Zm4.1,6.981H26.179a1.972,1.972,0,0,1,.861.2c-.359-.03-.722-.046-1.088-.046a12.86,12.86,0,0,0-9.128,3.794H7.588a1.973,1.973,0,0,1,0-3.946Zm0,5.463h7.938a12.863,12.863,0,0,0-1.95,3.946H7.588a1.973,1.973,0,0,1,0-3.946Zm0,5.463h5.639a12.91,12.91,0,0,0-.047,3.946H7.588a1.973,1.973,0,0,1,0-3.946Zm0,9.409a1.973,1.973,0,0,1,0-3.946h5.9a12.849,12.849,0,0,0,1.821,3.946Zm18.363,4.1A11.382,11.382,0,1,1,37.333,28.5,11.395,11.395,0,0,1,25.951,39.878Z" transform="translate(0 -3)" fill="#333b45"/></g></g></g></svg>
                     </div>

                     <div class="w-4/5 text-right">
                         <h2 class="block capitalize text-md mb-2 w-full">{{__('Total')}}</h2>

                         <h4 class="text-base font-normal w-full"> {{ total_number  }} {{__(currency)}}</h4>

                     </div>

                 </div>
             </loading-view>
         </card>


     </div>
</template>

<script>
    export default {
        name: "statistics",
        props: {
            statistics: {
                type: Object,
                default: {
                    'cash_total': 0,
                    'bank_transfer_total': 0,
                    'mada_total': 0,
                    'credit_card_total': 0,
                    'grand': 0
                }
            },
            data(){
        return{
            currency :Nova.app.currentTeam.currency,

        }
    },
            loading: {
                type: Boolean,
                default: true
            }
        },

    }
</script>

<style scoped>

</style>
