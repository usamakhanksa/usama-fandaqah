<template>
    <div>
        <sweet-modal :enable-mobile-fullscreen="false" @open="setNote" :pulse-on-block="false" :title="__('Update Note')" overlay-theme="dark" ref="edit" class="cancel_reservation_modal customer_modal_crud relative">
        <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
        <textarea class="w-full text-area my-2" wrap="soft" cols="30" :placeholder="__('Please write your note here ...')" rows="3" v-model="note"/>
        <div class="action_buttons">
            <button v-if="type == 'company'" class="table w-full" @click="updateCompanyNote">{{__('Update Note')}}</button>
            <button v-else class="table w-full" @click="update">{{__('Update Note')}}</button>
        </div><!-- action_buttons -->
    </sweet-modal>
    </div>

</template>

<script>

import Loading from 'vue-loading-overlay';

export default {
    name: 'update-note',
    props: ['target_note' , 'id' , 'type' , 'typeId'],
    components: {
        Loading
    },
    data() {
        return {
            note : null,
            isLoading : false
        }
    },

    methods: {
        update() {
            if(!this.note)  return this.$toasted.show(this.__('Note content is required'), {type: 'error'});
            this.isLoading = true;
            axios
            .put(`/nova-vendor/new/customers/notes/${this.id}`,{
                note:this.note,
                commentableType: "App\\Customer",
                commentableId: this.$route.params.id
            })
            .then(response => {
                if(response.data.success){
                   this.$toasted.show(this.__('Note updated successfully'), {type: 'success'});
                   Nova.$emit('call-customer-notes-query');
                }else{
                   this.$toasted.show(this.__('Something went wrong'), {type: 'error'});
                }

                this.isLoading = false;
                this.$refs.edit.close();
            });
        },
        updateCompanyNote() {
            if(!this.note)  return this.$toasted.show(this.__('Note content is required'), {type: 'error'});
            this.isLoading = true;
            axios
            .put(`/nova-vendor/new/customers/companies/notes/${this.id}`,{
                note:this.note
            })
            .then(response => {
                if(response.data.success){
                   this.$toasted.show(this.__('Note updated successfully'), {type: 'success'});
                   Nova.$emit('call-companies-notes-query');
                }else{
                   this.$toasted.show(this.__('Something went wrong'), {type: 'error'});
                }

                this.isLoading = false;
                this.$refs.edit.close();
            });
        },
        setNote(){
            this.note = this.target_note;
        }

    },

};
</script>

<style lang="scss" scoped>
.text-area {
    width: 100%;
    border-radius: 10px;
    padding: 10px;
    background-color: #f6f6f6;
    border: 1px solid #ddd;
}

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
</style>
