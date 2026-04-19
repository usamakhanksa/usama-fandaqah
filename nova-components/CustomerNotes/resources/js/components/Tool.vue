<template>

    <div>
        <div class="flex flex-wrap overflow-hidden mt-2">
            <div class=" overflow-hidden w-full sm:w-full md:w-3/3 lg:w-3/3 xl:w-3/3 p-1" v-permission="'view notes'">
                <h1 class="mb-1 text-90 font-normal text-2xl">{{__('Notes to the customer')}}</h1>
                <div class="flex-none   card py-2 px-2">
                    <div class="flex justify-center items-center px-6 py-3" v-if="!notes.length">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="mx-auto" width="64" height="64">
                                <path class="heroicon-ui"
                                      d="M6 14H4a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h12a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v13a1 1 0 0 1-1.7.7L16.58 18H8a2 2 0 0 1-2-2v-2zm0-2V8c0-1.1.9-2 2-2h8V4H4v8h2zm14-4H8v8h9a1 1 0 0 1 .7.3l2.3 2.29V8z"/>
                            </svg>
                            <div class="text-base text-80 font-normal mb-6">
                                <label> {{__('No Notes Found !')}}</label>
                            </div>
                        </div>
                    </div>
                    <ul class="w-full ">
                        <li class="bg-20 p-1 rounded-lg border border-blue-15  mb-3" v-for="note in notes.slice(0,2)">
                            <div class="p-2 whitespace-pre-line">{{note.comment}}</div>
                            <div class="flex w-full justify-between items-center">
                                <div class="flex justify-between items-center text-80">

                                    <img v-if="note.commenter.photo_url" alt="" class="rounded-full text-90 h-10 w-10 p-1 flex items-center justify-center"
                                         v-bind:src="'https://fandaqah.s3.eu-central-1.amazonaws.com/'+note.commenter.photo_url"/>
                                    {{ note.commenter.name }}
                                </div>
                                <div class="p1 text-80 text-sm px-1  ">
                                    {{note.created_at | formatDate }}
                                </div>
                            </div>
                        </li>
                    </ul>
                    <!-- Sandwich Icon  Open Display Modal if more than 2  -->
                    <div v-if="notes.length > 2"  @click="openDisplayNotesModal"  class="flex w-full bg-30 items-center text-center rounded p-1 hover:bg-50 text-center justify-center text-80 text-sm mb-3 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="" height="24" style="fill: #b9b9b9;"><path d="M4 15a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0-2a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm8 2a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0-2a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm8 2a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0-2a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" class="heroicon-ui"></path></svg>
                    </div>

                    <!-- Add Note Button && Sandwich Icon for more notes  -->
                    <div class="flex w-full mt-2 justify-between">
                        <button class="btn btn-default btn-primary" @click="openAddNoteModal" v-permission="'add notes'">{{__('Add Note')}}</button>
                        <button v-if="notes.length > 2" @click="openDisplayNotesModal" class="btn-default  btn-outline-primary">
                            {{__('more')}} ({{notes.length}}) ..
                        </button>
                    </div>

                    <!-- Modals -->
                    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add Note')" overlay-theme="dark" ref="addNoteModal">
                        <div>
                            <textarea class="w-full text-area my-2" wrap="soft" cols="30" :placeholder="__('Add Note')+'..'" rows="3" v-model="note"/>
                            <button  class="btn btn-default  btn-primary" @click="saveNote"> {{__('Add Note')}}</button>
                        </div>
                    </sweet-modal>

                    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Notes')" overlay-theme="dark" ref="notesModal">
                        <ul class="w-full commentsModal">
                            <li class="bg-20 p-1 rounded-lg border border-blue-15  mb-3" v-for="note in notes">
                                <div class="p-2 whitespace-pre-line">{{ltrim(note.comment)}}</div>
                                <div class="flex w-full justify-between items-center">
                                    <div class="flex justify-between items-center text-80">
                                        <img v-if="note.commenter.photo_url" alt="" class="rounded-full text-90 h-10 w-10 p-1 flex items-center justify-center"
                                             v-bind:src="'https://fandaqah.s3.eu-central-1.amazonaws.com/'+note.commenter.photo_url"/>
                                        {{ note.commenter.name }}
                                    </div>
                                    <div class="p1 text-80 text-sm px-1">
                                        {{note.created_at | formatDate }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </sweet-modal>

                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: ['resourceName', 'resourceId', 'field'],
    components:{
    },
    data(){
        return {
            notes :  [],
            note : ''
        }
    },
    methods:{
        fetchCustomerNotes(){
            Nova.request().get('/nova-vendor/customer-notes/getCustomerNotes?id=' + this.resourceId)
                .then((res) => {
                    this.notes = res.data ;
                })
                .catch((err) => {
                    this.$toasted.show(err , {
                        type : 'error',
                        theme: "bubble",
                        duration : 5000
                    });
                })
        },
        openAddNoteModal(){
            this.note = '';
            this.$refs.addNoteModal.open()
        },
        closeAddNoteModal(){
            this.note = '';
            this.$refs.addNoteModal.close()
        },
        openDisplayNotesModal(){
            this.$refs.notesModal.open()
        },
        ltrim(str) {
            if (str == null) return str;
            return str.trim();
        },
        saveNote(){
            if(!this.note){
                this.$toasted.show(this.__('Please add you note first') , {
                    type : 'error',
                    theme: "bubble",
                    duration : 5000
                });
                return false;
            }

            Nova.request().post('/nova-vendor/customer-notes/storeNote' , {
                commentableType: "App\\Customer",
                commentableId: this.resourceId,
                note: this.note,
            }).then((res) => {
                this.closeAddNoteModal();
                this.$toasted.show(this.__('Note has been added successfully') , {
                    type : 'success',
                    theme: "bubble",
                    duration : 5000
                });
                this.fetchCustomerNotes();
            }).catch((err) => {
                this.$toasted.show(err , {
                    type : 'error',
                    theme: "bubble",
                    duration : 5000
                });

            });
        }
    },
    mounted() {
        this.fetchCustomerNotes();
    },
}
</script>

<style scoped>
    .commentsModal {
        max-height: 550px;
        overflow: auto;
    }

    @media (min-width: 320px) and (max-width: 480px) {
        .commentsModal {
            max-height: 420px;
            overflow: auto;
        }
    }

    @media (min-width: 481px) and (max-width: 767px) {
        .commentsModal {
            max-height: 420px;
            overflow: auto;
        }
    }

    @media (min-width: 768px) and (max-width: 991px) {
        .commentsModal {
            max-height: 420px;
            overflow: auto;
        }
    }

    .text-area {
        width: 100%;
        border-radius: 10px;
        padding: 10px;
        background-color: #f6f6f6;
        border: 1px solid #ddd;
    }
</style>
