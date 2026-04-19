<template>
    <sweet-modal :enable-mobile-fullscreen="false"  :pulse-on-block="false" :title="__('Open Closed Contract')" overlay-theme="dark" ref="openClosedContract" class="open_closed_contract">
        <div class="relative mx-auto justify-center z-20">
            <span>{{__('Are you sure to re-open this contract again ?')}}</span>
            <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
                <div class="flex justify-end flex-wrap">
                    <button id="confirm-delete-button" @click="confirm"   class="btn btn-default btn-danger m-0">{{__('Open Contract')}}</button>
                    <button type="button" @click="stepBack()"  class="btn btn-default bg-gray-400 ml-2"> {{__('Back')}}</button>
                </div>
            </div>
        </div>
    </sweet-modal>
</template>

<script>
    export default {
        name: "OpenClosedContractConfirm",
        props : ['reservation'],
        methods:{
            stepBack(){
                this.$refs.openClosedContract.close();
            },
            confirm(){
                this.$refs.openClosedContract.close();
                if(this.reservation.customer_id){
                    this.$router.push({path: `/reservation/${this.reservation.id}?occ=true`});
                }else{
                    this.$router.push({path: `/reservation-noc/${this.reservation.id}?occ=true`});
                }
            }
        },
        mounted(){
            Nova.$on('open-closed-contract' , () => {
                this.$refs.openClosedContract.open();
            })
        }
    }
</script>

<style lang="scss" scoped>

    .open_closed_contract {
    h2 {
        line-height: 63px;
    } /* h2 */
    span {
        padding: 30px 20px;
        line-height: normal;
        display: block;
        font-size: 20px;
        color: #000;
    } /* span */
    } /* delete_confirm_slider_image */
</style>
