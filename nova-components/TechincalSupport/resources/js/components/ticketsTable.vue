<template>
        <div class="table_area">
            <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="fullPage"></loading>
            <div class="table_responsive">
                <table>
                    <thead>
                    <tr>
                        <th>{{__('Subject')}}</th>
                        <th>{{__('Description')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Date')}}</th>
                        <th>{{__('Actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                        <template v-if="tickets.length">
                            <tr v-for="ticket in tickets" :key="ticket.id">
                                <td>{{ticket.title}}</td>
                                <td>{{ ticket.body.length <= 100 ?  ticket.body : ticket.body.substring(0,100)+".." }}</td>
                                <td><span :class="[ticket.status == 1 ? 'status-new' : '',ticket.status == 2 ? 'status-open' : '',ticket.status == 3 ? 'status-pending' : '',ticket.status == 4 ? 'status-solved' : '',ticket.status == 5 ? 'status-closed' : '',ticket.status == 6 ? 'status-merged' : '',ticket.status == 7 ? 'status-spam' : '',]">
                                        {{__(getStatusTranslate(ticket.status))}}
                                    </span></td>
                                <td>{{ticket.created_at}}</td>
                                <td>
                                    <router-link v-if="ticket.id" :to="{path: `/techincal-support/ticket/${ticket.id}`}" :title="__('View')" class="cursor-pointer text-70 hover:text-primary mx-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="18" viewBox="0 0 22 16" aria-labelledby="view" role="presentation" class="fill-current"><path d="M16.56 13.66a8 8 0 0 1-11.32 0L.3 8.7a1 1 0 0 1 0-1.42l4.95-4.95a8 8 0 0 1 11.32 0l4.95 4.95a1 1 0 0 1 0 1.42l-4.95 4.95-.01.01zm-9.9-1.42a6 6 0 0 0 8.48 0L19.38 8l-4.24-4.24a6 6 0 0 0-8.48 0L2.4 8l4.25 4.24h.01zM10.9 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"></path></svg>
                                    </router-link>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="5" style="padding: 10px;">{{__('No Tickets Found')}}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>

            </div><!-- table_responsive -->
            <div class="w-full flex flex-wrap mt-3 justify-center">
        <pagination v-if="paginator.last_page > 1"
          :page-count="paginator.lastPage"
          :page-range="3"
          :margin-pages="2"
          :click-handler="getTickets"
          :value="paginator.currentPage"
          :prev-text="__('Previous')"
          :next-text="__('Next')"
          :container-class="'pagination  w-full flex justify-center'"
          :page-class="'page-item'"
          :page-link-class="'page-link'"
          :prev-link-class="'page-link'"
          :next-link-class="'page-link'"
          :prev-class="'page-item'"
          :next-class="'page-item'"
          :first-last-button="true"
          :first-button-text="__('First')"
          :last-button-text="__('Last')"
        />
      </div><!-- flex -->
        </div><!-- table_area -->
</template>

<script>
import Pagination from "./Pagination.vue";
export default {
    components : {
        Pagination
    },
    mounted() {
        this.getTickets();
        Nova.$on('call-tickets-query' , () => {
            this.getTickets();
        })

    },
    data() {
        return {
            isLoading : false,
            tickets:[],
            paginator:[],
        }
    },
    methods:{
        getStatusTranslate(status){
            switch(status){
                case 1:
                    return 'New Status';
                case 2:
                    return 'Open Status';
                case 3:
                    return 'Pending Status';
                case 4:
                    return 'Solved Status';
                case 5:
                    return 'Closed Status';
                case 6:
                    return 'Merged Status';
                case 7:
                    return 'Spam Status';

            }
        },
        getTickets(page =1){
            this.isLoading = true;
            axios
            .get('/nova-vendor/techincal-support/tickets?page='+page)
            .then(response => {
                if(response.data.success){
                    this.isLoading = false;
                    this.tickets = response.data.data.data
                    this.paginator = response.data.data
                }else{
                    this.isLoading = false;
                    return;
                }

            });
        }
    }
}
</script>

<style lang="scss">


            .table_area {
                position: relative;
                svg{
                    margin:auto;
                }
                .status-new{
                    background-color: #b3c0c7;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .status-open{
                    background-color: #126ee8;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .status-pending{
                    background-color: #ff9019;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .status-solved{
                    background-color: #50c669;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .status-closed{
                    background-color: #f6574b;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .status-merged{
                    background-color: #50c669;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .status-spam{
                    background-color: #50c669;
                    padding: 2px 10px;
                    border-radius: 5px;
                }
                .table_responsive {
                    @media (min-width: 320px) and (max-width: 767px) {
                        overflow: auto;
                    } /* media */
                    table {
                        width: 100%;
                        @media (min-width: 320px) and (max-width: 767px) {
                            margin: 0 auto 15px;
                        } /* media */
                        thead {
                            th {
                                background: #4a5568;
                                border: 1px solid #5E697C;
                                font-size: 15px;
                                padding: 5px;
                                vertical-align: middle;
                                color: #fff;
                                font-weight: normal;
                                text-align: center !important;
                            } /* th */
                        } /* thead */
                        tbody {
                            td {
                                background: #fafafa;
                                border: 1px solid #d3d3d3;
                                color: #000;
                                vertical-align: middle;
                                font-size: 15px;
                                line-height: 10px;
                                text-align: center !important;
                                height: auto;
                                .text-left {
                                    text-align: inherit !important;
                                } /* text-left */
                                label#customer-label {
                                    display: inline-block;
                                    font-size: 14px;
                                    border-radius: 4px;
                                    padding: 3px 10px;
                                    min-width: 60px;
                                } /* customer-label */
                                a {
                                    color: #4099de;
                                    font-weight: bold;
                                    cursor: pointer;
                                    &:hover {
                                        color: #0071C9;
                                    } /* hover */
                                } /* a */
                                .action {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    flex-wrap: nowrap;
                                    button {
                                        margin: 5px !important;
                                        color: #b3b9bf;
                                        svg {
                                            display: block;
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {
                                                    fill: #4099de;
                                                } /* hover */
                                            } /*  path*/
                                        } /* svg */
                                        &:hover {
                                            color: #4099de;
                                        } /* hover */
                                    } /* button */
                                } /* action */
                            } /* td */
                        } /* tbody */
                    } /* table */
                } /* table_responsive */
            } /* table_area */
</style>

