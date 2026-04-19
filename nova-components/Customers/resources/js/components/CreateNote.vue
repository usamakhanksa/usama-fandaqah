<template>
    <div>
        <button class="table" @click="openAddNoteModal">{{__('Add Note')}}</button>
        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add Note')" overlay-theme="dark" ref="createNote" class="cancel_reservation_modal customer_modal_crud relative">
        <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>
        <textarea class="w-full text-area my-2" wrap="soft" cols="30" :placeholder="__('Please write your note here ...')" rows="3" v-model="note"/>

        <div class="action_buttons">
            <button v-if="type == 'company' " class="table w-full" @click="storeCompanyNote">{{__('Add Note')}}</button>
            <button v-else class="table w-full" @click="store">{{__('Add Note')}}</button>
        </div><!-- action_buttons -->
    </sweet-modal>
    </div>

</template>

<script>

import Loading from 'vue-loading-overlay';

export default {
    name: 'create-note',
    props: {
        type: {
            type: String,
        },
        typeId: {
            type: Number
        }
    },
    components: {
        Loading
    },
    data() {
        return {
            note : '',
            isLoading : false
        }
    },

    methods: {
        store() {
            if(!this.note)  return this.$toasted.show(this.__('Note content is required'), {type: 'error'});
            this.isLoading = true;
            axios
            .post('/nova-vendor/new/customers/notes/store',{
                note:this.note,
                commentableType: "App\\Customer",
                commentableId: this.$route.params.id
            })
            .then(response => {
                if(response.data.success){
                   this.$toasted.show(this.__('Note created successfully'), {type: 'success'});
                   Nova.$emit('call-customer-notes-query');
                }else{
                   this.$toasted.show(this.__('Something went wrong'), {type: 'error'});
                }
                this.note = '';
                this.isLoading = false;
                this.$refs.createNote.close();
            });
        },

        storeCompanyNote() {
            if(!this.note)  return this.$toasted.show(this.__('Note content is required'), {type: 'error'});
            this.isLoading = true;
            axios
            .post(`/nova-vendor/new/customers/companies/${this.typeId}/notes/store`,{
                note:this.note,
            })
            .then(response => {

                if(response.data.success){
                   this.$toasted.show(this.__('Note created successfully'), {type: 'success'});
                   Nova.$emit('call-companies-notes-query');
                }else{
                   this.$toasted.show(this.__('Something went wrong'), {type: 'error'});
                }
                this.note = '';
                this.isLoading = false;
                this.$refs.createNote.close();
            });
        },

        openAddNoteModal(){
            this.$refs.createNote.open();
        }

    },

};
</script>

<style lang="scss">
.text-area {
    width: 100%;
    border-radius: 10px;
    padding: 10px;
    background-color: #f6f6f6;
    border: 1px solid #ddd;
}
</style>
