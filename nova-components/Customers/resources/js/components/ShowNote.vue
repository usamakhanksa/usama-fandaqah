<template>
    <div>
        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Note Content')" overlay-theme="dark" ref="show" class="cancel_reservation_modal customer_modal_crud">


        <textarea class="w-full text-area-note my-2" readonly wrap="soft" cols="30" :placeholder="__('Please write your note here ...')" rows="3" :value="target_note" />

        <div class="action_buttons">
            <button class="table w-full" @click="close">{{__('Close')}}</button>
        </div><!-- action_buttons -->
    </sweet-modal>
    </div>

</template>

<script>

export default {
    name: 'show-note',
    props: ['target_note'],
    methods:{
        close(){
            this.$refs.show.close();
        }
    },


};
</script>

<style lang="scss" scoped>
button {
        background: #61788a;
        border-radius: 5px;
        border: 1px solid #6e8698;
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
            background: #4c5e6b;
            border-color: #4d5d6a;
        } /* hover */
    } /* button */
.text-area-note {
    width: 100%;
    border-radius: 10px;
    padding: 10px;
    background-color: #f6f6f6;
    border: 1px solid #ddd;
}
</style>
