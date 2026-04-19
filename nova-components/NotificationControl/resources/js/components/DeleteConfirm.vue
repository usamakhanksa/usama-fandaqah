<template>

    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="true" :title="__('Delete')" overlay-theme="dark" ref="deleteConfirmModal">

        <div class="relative mx-auto justify-center z-20">
            <loading :active.sync="isLoading"
                     :is-full-page="false">
            </loading>

            <div class="p-8">
                <p class="text-lg text-black leading-normal">{{__('Are you sure to delete ?')}}</p>
            </div>
            <div class="bg-30 px-6 py-3 flex -mx-2 -mb-2">
                <div class="ml-auto">
                    <button type="button" @click="stepBack()"  class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"> {{__('Back')}}</button>
                    <button id="confirm-delete-button" @click="deleteRecord"   class="btn btn-default btn-danger">{{__('delete')}}</button>
                </div>
            </div>

        </div>

    </sweet-modal>

</template>

<script>
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "DeleteConfirm",
        components : {Loading},
        props : ['type'],
        data(){
            return {
                isLoading : false
            }
        },
        methods : {
            deleteRecord(){
                if(this.type === 'phone'){
                    Nova.$emit('phone-record-deleted');
                    this.$refs.deleteConfirmModal.close();
                }

                if(this.type === 'email'){
                    Nova.$emit('email-record-deleted');
                    this.$refs.deleteConfirmModal.close();
                }

            },
            stepBack(){
                this.$refs.deleteConfirmModal.close();
            }
        },
        mounted() {
            Nova.$on('phone-record-delete' , () => {
                this.$refs.deleteConfirmModal.open();
            })

            Nova.$on('email-record-delete' , () => {
                this.$refs.deleteConfirmModal.open();
            })


        }
    }
</script>

<style scoped>

</style>
