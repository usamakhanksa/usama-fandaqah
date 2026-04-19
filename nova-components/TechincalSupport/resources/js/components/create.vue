<template>
    <div>
        <button class="table" @click="openCreateTicketModal">{{__('Add New Ticket')}}</button>
        <sweet-modal @close="clearModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Add New Ticket')" overlay-theme="dark" ref="createTicketModal" class="cancel_reservation_modal ticket_modal_crud">
            <div class="ticket_create relative">
                <loading :active="isLoading" :loader="'spinner'" :color="'#7e7d7f'" :opacity="0.7"  :is-full-page="false"></loading>

                <div class="row_group">
                    <div class="col">
                        <label>{{__('Subject')}}<i>*</i></label>
                        <input type="text" v-model="ticket.title" :placeholder="__('Subject')">
                    </div><!-- title col -->
                </div>


                <div class="row_group" v-if="ticket_categories.length">
                    <div class="col">
                        <label>{{__('Main Category')}}<i>*</i></label>
                        <select v-model="ticket.main_category">
                            <option value="" selected disabled>{{__('Main Category')}}</option>
                            <option v-for="(cat,i) in ticket_categories" :key="i" :value="cat">{{__(cat.name)}}</option>
                        </select>
                    </div>
                </div>
                <div class="row_group" v-if="ticket.main_category && ticket.main_category.name != 'other'">
                    <div class="col">
                        <label>{{__('Sub Category')}}<i>*</i></label>
                        <select v-model="ticket.sub_category">
                            <option value="" selected disabled>{{__('Sub Category')}}</option>
                            <option v-for="(sub_cat,i) in ticket.main_category.data" :key="i" :value="sub_cat">{{__(sub_cat)}}</option>
                        </select>
                    </div>
                </div>

                <div class="row_group">
                    <div class="col">
                        <label>{{__('Description')}}</label>
                        <textarea wrap="soft" :placeholder="__('Write your description here ..')" rows="10" v-model="ticket.body"></textarea>
                    </div><!-- ticket type col -->
                </div><!-- row_group -->
                <div class="row_group">
                    <div class="col">
                        <label>{{__('Attachment')}}</label>
                        <file-upload
                                            extensions="gif,jpg,jpeg,png,webp"
                                            accept="image/png,image/gif,image/jpeg,image/webp"
                                            name="attachment_path"
                                            class="form-file-btn btn btn-default btn-primary select-none"
                                            v-model="ticket.attachment"
                                            @input-filter="inputFilter"
                                        >
                                            {{ __('Select Image') }}
                                        </file-upload>
                    </div><!-- title col -->
                    <div class="col">
                        <div id="preview">
                            <img v-if="image_url" :src="image_url" />
                        </div>
                    </div>
                        
                </div>
            </div>
        <div class="action_buttons">
            <button class="table w-full" @click="store()">{{__('Create Ticket')}}</button>
        </div><!-- action_buttons -->
    </sweet-modal>
    </div>

</template>

<script>

import Loading from 'vue-loading-overlay';
import VueUploadComponent from 'vue-upload-component';

export default {
    title: 'create-ticket',
    components: {
        Loading,
        'file-upload': VueUploadComponent
    },
    data() {
        return {
            ticket:{
                title:null,
                body:null,
                attachment:[],
                main_category : null,
                sub_category : null
            },
            isLoading : false,
            ticket_categories : [],
            image_url : null
        }
    },
    computed:{

    },
    methods: {
        clearModal(){
            this.image_url = null;
        },
        /**
         * Pretreatment
         * @param  Object|undefined   newFile   Read and write
         * @param  Object|undefined   oldFile   Read only
         * @param  Function           prevent   Prevent changing
         * @return undefined
         */
        inputFilter: function (newFile, oldFile, prevent) {
            if (newFile && !oldFile) {
                // Filter non-image file
                if (!/\.(jpeg|jpe|jpg|gif|png|webp)$/i.test(newFile.name)) {
                return prevent()
                }
            }

         
            let URL = window.URL || window.webkitURL
            if (URL && URL.createObjectURL) {
               this.image_url = URL.createObjectURL(newFile.file)
            }

            
        },
        openCreateTicketModal(){
            this.ticket.title = null;
            this.ticket.body = null;
            this.$refs.createTicketModal.open();
        },
        store() {
            if(!this.validateForm()){return;}
            this.isLoading = true;

            const data = new FormData();
            let config = {
                header : {
                    'Content-Type' : 'multipart/form-data'
                }
            }
            if(this.ticket.attachment[0])
                data.append('attachment', this.ticket.attachment[0].file);

                data.append('title', this.ticket.title);
                data.append('body', this.ticket.body);
                data.append('main_category', this.__(this.ticket.main_category.name));
                data.append('sub_category', this.ticket.sub_category ?  this.__(this.ticket.sub_category) : null);

            Nova.request()
            .post('/nova-vendor/techincal-support/tickets', data,config)
            .then(response => {
                if(response.data.success){
                    this.isLoading = false;
                    this.$toasted.show(this.__(response.data.message), {type: 'success'});
                    this.$refs.createTicketModal.close();
                    this.ticket.title = null;
                    this.ticket.body = null;
                    Nova.$emit('call-tickets-query');
                }else{
                    this.isLoading = false;
                    this.$toasted.show(response.data.message, {type: 'error'});
                    return;
                }

            });
        },
        validateForm(){
            if (!this.ticket.title || !this.ticket.body) {
                this.$toasted.show(this.__('Please fill ticket info'), {type: 'error'})
                this.isLoading = false;
                return false;
            }

            if (!this.ticket.main_category) {
                this.$toasted.show(this.__('Please select main category'), {type: 'error'})
                this.isLoading = false;
                return false;
            }

            if (this.ticket.main_category.name != 'other' && !this.ticket.sub_category) {
                this.$toasted.show(this.__('Please select sub category'), {type: 'error'})
                this.isLoading = false;
                return false;
            }

            return true;
        }

    },
    mounted(){
    },
    created(){
        this.ticket_categories = [
             {
                name : "dashboard",
                data : [
                    "widget",
                    "statistics"
                ]
            },
            {
                name : "unit housing",
                data : [
                    "unit category",
                    "unit status",
                    "statistics",
                    "receipt report",
                    "arrival report",
                    "departure report"
                ]
            },
            {
                name : "reservation table",
                data : [
                    "reservation status",
                    "reservation period"
                ]
            },
            {
                name : "reservations",
                data : [
                    "filter",
                    "statistics",
                    "reservation status",
                    "reservation summary",
                    "customer information",
                    "invoices",
                    "receipts",
                    "services",
                    "shomoos status"
                ]
            },
            {
                name : "pos",
                data : [
                    "user permission",
                    "service price",
                    "invoice",
                    "print",
                    "in-house charge",
                    "show operations"
                ]
            },
            {
                name: "financial management",
                data: [
                    "deposit",
                    "withdraw",
                    "promissory",
                    "safe movement",
                    "credit notes"
                ]
            },
            {
                name: "customers",
                data: [
                    "customer details",
                    "company details"
                ]
            },
            {
                name : "units",
                data: [
                    "unit categories",
                    "unit details",
                    "features",
                    "special offers",
                    "category rate"
                ]
            },
            {
                name: "services",
                data: [
                    "service category",
                    "user permission",
                    "service price",
                    "service details"
                ]
            },
            {
                name: "reports",
                data: [
                    "deposit",
                    "withdraw",
                    "safe movement",
                    "customer movement",
                    "service report",
                    "monthly report",
                    "units movement",
                    "occupancy",
                    "cleaning",
                    "maintenance",
                    "reservation transfer",
                    "revenue, tax",
                    "reservation resources",
                    "employee contract",
                    "invoices report",
                    "balady report"
                ]
            },
            {
                name : "settings",
                data : [
                    "general",
                    "integration",
                    "users & roles",
                    "documents",
                    "notifications",
                    "finance",
                    "activity log",
                    "ledger numbers",
                    "reservation resources",
                    "customer group",
                    "website",
                    "rating"
                ]
            },
            {
                name : "other",
                data : []
            }
        ];
    }
};
</script>

<style lang="scss">
#preview {
  display: flex;
  justify-content: center;
  align-items: center;
}

#preview img {
  max-width: 100%;
  max-height: 500px;
}
            .action_buttons {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                margin: 0 auto 10px;
                button {
                    display: block;
                    height: 30px;
                    width: 140px;
                    margin: 0 10px 0 0;
                    outline: none;
                    background-position: center center;
                    background-size: 25px;
                    background-repeat: no-repeat;
                    &.excel_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* excel_button */
                    &.print_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* print_button */
                } /* button */
            } /* action_buttons */
.ticket_modal_crud {
    .sweet-content {
    overflow: auto;
    max-height: 85vh;
    display: block !important;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    @media (min-width: 320px) and (max-width: 767px) {
        max-height: 500px;
    }
  } /* sweet-content */
      textarea {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      background: #fafafa;
      border: 1px solid #ddd;
      font-size: 15px;
      color: #000;
      margin: 0 auto 10px;
    } /* textarea */
}

      .ticket_create {
                    .formgroup {
                        display: block;
                        margin: 0 auto 10px;

                        .autocomplete__box {
                            border: 1px solid #ddd !important;
                            background: #fafafa;
                            color: #000;
                            height: 40px;
                            padding: 0 10px;
                            box-shadow: none !important;
                            border-radius: 5px;

                            input {
                                border: none !important;
                                height: 38px !important;
                                border-radius: 0 !important;
                                background: transparent !important;
                            }

                            /* input */
                        }

                        /* autocomplete__box */
                        ul.autocomplete__results {
                            border: 1px solid #ddd;
                            border-radius: 0 0 5px 5px;
                            margin: -3px 0 0 0;
                            background: #f5f5f5;

                            li.autocomplete__results__item {
                                display: block;
                                color: #000;
                                font-size: 15px;

                                &:hover {
                                    background: #f0f0f0;
                                }
                            }

                            /* autocomplete__results__item */
                        }

                        /* autocomplete__results */
                    }

                    /* formgroup */
                    .loader_item {
                        padding: 50px;
                    }

                    /* loader_item */
                    .ticketNotes {
                        span {
                            margin: 0 auto 15px;
                            border-radius: 5px;
                            padding: 10px;
                            text-align: center;
                            color: #b7791f;
                            border: 1px solid #fbd38d;
                            background: #fffaf0;
                            font-size: 15px;
                            cursor: pointer;
                            display: block;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;

                            &:hover {
                                background: #faf5eb;
                                border-color: #f6ce88;
                                color: #b2741a;
                            }

                            /* hover */
                        }

                        /* span */
                    }

                    /* ticketNotes */
                    .ticket_notes_modal {
                        .sweet-content {
                            max-height: 500px;
                            overflow-y: auto;
                            display: block !important;
                            scrollbar-width: thin;
                            scrollbar-color: #ccc #f5f5f5;

                            &::-webkit-scrollbar {
                                width: 6px;
                            }

                            &::-webkit-scrollbar-track {
                                background: #f5f5f5;
                            }

                            &::-webkit-scrollbar-thumb {
                                background: #ccc;
                            }

                            &::-webkit-scrollbar-thumb:window-inactive {
                                background: #f5f5f5;
                            }
                        }

                        /* sweet-content */
                        .note_item {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            width: 100%;
                            flex-wrap: wrap;
                            border: 1px solid #ddd;
                            margin: 0 auto 10px;
                            border-radius: 5px;
                            padding: 5px;
                            background: #fdfdfd;

                            .desc {
                                display: block;
                                white-space: pre-line;
                                font-size: 15px;
                                color: #000;
                                margin: 0 0 10px;
                            }

                            /* desc */
                            .user_info {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                width: 100%;

                                .user_account {
                                    display: flex;
                                    align-items: center;
                                    justify-content: flex-start;
                                    font-size: 15px;
                                    color: #666666;

                                    img {
                                        display: block;
                                        width: 35px;
                                        height: 35px;
                                        border-radius: 100%;
                                        margin: 0 0 0 5px;
                                        border: 1px solid #ddd;

                                        [dir="ltr"] & {
                                            margin: 0 5px 0 0;
                                        }

                                        /* ltr */
                                    }

                                    /* img */
                                }

                                /* user_account */
                                time {
                                    display: block;
                                    font-size: 13px;
                                    color: #777777;
                                }

                                /* time */
                            }

                            /* user_info */
                        }

                        /* note_item */
                    }

                    /* ticket_notes_modal */
                    .row_group {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        flex-wrap: wrap;
                        margin: 0 -10px;
                        @media (min-width: 320px) and (max-width: 767px) {
                            margin: 0;
                        }

                        .id_expire{
                            width: 100%;
                            padding: 0 10px;
                            margin: 0 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                padding: 0;
                            }
                        }

                        .address{
                            width: 100%;
                            padding: 0 10px;
                            margin: 0 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                padding: 0;
                            }
                        }
                        /* Mobile */
                        .col {
                            width: 100%;
                            padding: 0 10px;
                            margin: 0 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                padding: 0;
                            }
                            /* Mobile */
                            .vue-tel-input {
                                display: flex;
                                width: 100%;
                                height: 40px;
                                background: #fafafa;
                                border: 1px solid #dddddd !important;
                                line-height: 40px;
                                font-size: 15px;
                                color: #000;
                                border-radius: 4px;
                                padding: 0;
                                text-align: right;
                                align-items: center;
                                box-shadow: none;

                                [dir="ltr"] & {
                                    text-align: left;
                                }

                                /* rtl */
                                .dropdown {
                                    padding: 0;
                                    width: 70px;
                                    background: #f5f5f5;
                                    height: 38px;
                                    border-left: 1px solid #dddddd;
                                    border-radius: 0 4px 4px 0;

                                    [dir="ltr"] & {
                                        border-right: 1px solid #dddddd;
                                        border-left: none;
                                        border-radius: 4px 0 0 4px;
                                    }

                                    /* rtl */
                                    span.selection {
                                        display: flex !important;
                                        height: 40px;
                                        justify-content: center;
                                        align-items: center;
                                        width: auto;
                                        margin: 0 auto;
                                        font-size: 12px !important;

                                        .iti-flag {
                                            margin: 0;
                                        }

                                        /* iti-flag */
                                        span.dropdown-arrow {
                                            width: auto;
                                            margin: 0 5px 0 0;
                                            display: inline-block !important;
                                            font-size: inherit !important;

                                            [dir="ltr"] & {
                                                margin: 0 0 0 5px;
                                            }

                                            /* ltr */
                                        }

                                        /* dropdown-arrow */
                                    }

                                    /* selection */
                                    ul {
                                        margin: 0 auto;
                                        left: auto;
                                        right: 0;
                                        width: auto;
                                        min-width: 210px;
                                        top: 43px;
                                        max-width: 386px;
                                        border-radius: 4px;
                                        text-align: inherit;
                                        scrollbar-width: thin;
                                        scrollbar-color: #ccc #f5f5f5;

                                        &::-webkit-scrollbar {
                                            width: 6px;
                                        }

                                        &::-webkit-scrollbar-track {
                                            background: #f5f5f5;
                                        }

                                        &::-webkit-scrollbar-thumb {
                                            background: #ccc;
                                        }

                                        &::-webkit-scrollbar-thumb:window-inactive {
                                            background: #f5f5f5;
                                        }

                                        [dir="ltr"] & {
                                            left: 0;
                                            right: auto;
                                        }

                                        /* rtl */
                                        li {
                                            direction: rtl;
                                            display: flex;
                                            align-items: center;
                                            justify-content: flex-start;
                                            padding: 3px 10px;
                                            line-height: normal;
                                            font-weight: normal;
                                            color: #000;

                                            [dir="ltr"] & {
                                                direction: ltr;
                                            }

                                            /* ltr */
                                            .iti-flag {
                                                margin: 0;
                                            }

                                            /* iti-flag */
                                            strong {
                                                display: block;
                                                font-weight: normal;
                                                font-size: 15px;
                                                margin: 0 5px;
                                            }

                                            /* strong */
                                            span {
                                                direction: ltr;
                                                color: #666 !important;
                                                font-size: inherit !important;
                                                margin: 0;
                                            }

                                            /* span */
                                        }

                                        /* li */
                                    }

                                    /* ul */
                                }

                                /* dropdown */
                                input {
                                    width: 76%;
                                    border-radius: 0 !important;
                                    height: 38px !important;
                                    border: none !important;
                                    padding: 0 10px 0 0 !important;

                                    [dir="ltr"] & {
                                        padding: 0 0 0 10px !important;
                                    }

                                    /* ltr */
                                }

                                /* input */
                            }

                            /* vue-tel-input */
                        }


                        /* col */
                    }

                    /* row_group */
                    label {
                        display: block;
                        margin: 0 auto 5px;
                        font-size: 15px;

                        i {
                            display: inline-block !important;
                            margin: 0 5px 0 0;
                            color: #f00 !important;
                            font-style: normal;
                        }

                        /* i */
                    }

                    /* label */
                    input {
                        height: 40px !important;
                        padding: 0 10px !important;
                        color: #000 !important;
                        font-size: 15px !important;
                        border: 1px solid #dddddd !important;
                        background: #fafafa !important;
                        width: 100%;

                        &[readonly="readonly"] {
                            cursor: pointer;
                        }

                        /* readonly */
                        &.ticket_search {
                            background: transparent !important;
                            border: none !important;
                            height: 40px !important;
                            border-radius: 0 !important;
                            padding: 0 10px !important;
                            display: block;
                        }

                        /* ticket_search */
                    }

                    /* input */
                    label.ticket_highlight {
                        height: 40px;
                        border-radius: 4px;
                        text-align: center;
                        font-size: 15px;
                        line-height: 40px;
                        color: #000;
                        margin: 0 auto;
                    }

                    /* ticket_highlight */
                    select {
                        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
                        width: 100%;
                        height: 40px !important;
                        padding: 0 10px !important;
                        background-color: #fafafa !important;
                        border: 1px solid #ddd !important;
                        color: #000;
                        font-size: 15px;
                        -webkit-box-sizing: border-box;
                        box-sizing: border-box;
                        -webkit-appearance: none;
                        -moz-appearance: none;
                        -o-appearance: none;
                        appearance: none;
                        border-radius: 5px !important;
                        background-position: 15px center;
                        background-repeat: no-repeat;
                        background-size: 14px;

                        [dir="ltr"] & {
                            background-position: 97% center;
                        }

                        /* ltr */
                    }

                    /* select */
                }

                /* ticket_details */
</style>
