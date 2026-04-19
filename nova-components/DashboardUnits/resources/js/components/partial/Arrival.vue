<template>
    <loading-view :loading="initialLoading" :dusk="resourceName + '-index-component'" class="w-full">
        <card>
            <div class="p-3 bg-gray-100 flex items-center border-b border-50 rounded-lg rounded-b-sm">
                <h3 class="text-black text-lg text_font_edit">{{__('Arrivals today')}} - {{ readableDate() +' '}} <p class="m-0 mr-1 inline-block">{{ date }}</p></h3>
            </div>

            <loading-view :loading="loading">
                <div v-if="!resources.length" class="flex justify-center items-center px-6 py-8">
                    <div class="text-center my-6">
                        <svg
                            class="mb-3 mx-auto"
                            xmlns="http://www.w3.org/2000/svg"
                            width="65"
                            height="51"
                            viewBox="0 0 65 51"
                        >
                            <g id="Page-1" fill="none" fill-rule="evenodd">
                                <g
                                    id="05-blank-state"
                                    fill="#A8B9C5"
                                    fill-rule="nonzero"
                                    transform="translate(-779 -695)"
                                >
                                    <path
                                        id="Combined-Shape"
                                        d="M835 735h2c.552285 0 1 .447715 1 1s-.447715 1-1 1h-2v2c0 .552285-.447715 1-1 1s-1-.447715-1-1v-2h-2c-.552285 0-1-.447715-1-1s.447715-1 1-1h2v-2c0-.552285.447715-1 1-1s1 .447715 1 1v2zm-5.364125-8H817v8h7.049375c.350333-3.528515 2.534789-6.517471 5.5865-8zm-5.5865 10H785c-3.313708 0-6-2.686292-6-6v-30c0-3.313708 2.686292-6 6-6h44c3.313708 0 6 2.686292 6 6v25.049375c5.053323.501725 9 4.765277 9 9.950625 0 5.522847-4.477153 10-10 10-5.185348 0-9.4489-3.946677-9.950625-9zM799 725h16v-8h-16v8zm0 2v8h16v-8h-16zm34-2v-8h-16v8h16zm-52 0h16v-8h-16v8zm0 2v4c0 2.209139 1.790861 4 4 4h12v-8h-16zm18-12h16v-8h-16v8zm34 0v-8h-16v8h16zm-52 0h16v-8h-16v8zm52-10v-4c0-2.209139-1.790861-4-4-4h-44c-2.209139 0-4 1.790861-4 4v4h52zm1 39c4.418278 0 8-3.581722 8-8s-3.581722-8-8-8-8 3.581722-8 8 3.581722 8 8 8z"
                                    />
                                </g>
                            </g>
                        </svg>

                        <h3 class="text-base text-100 font-normal mt-2">{{ __('No Arrivals Found') }}</h3>

                        <create-resource-button
                            classes="btn btn-sm btn-outline inline-flex items-center"
                            :singular-name="singularName"
                            :resource-name="resourceName"
                            :via-resource="viaResource"
                            :via-resource-id="viaResourceId"
                            :via-relationship="viaRelationship"
                            :relationship-type="relationshipType"
                            :authorized-to-create="authorizedToCreate && !resourceIsFull"
                            :authorized-to-relate="authorizedToRelate"
                        >
                        </create-resource-button>
                    </div>
                </div>

                <div class="overflow-hidden overflow-x-auto relative">
                    <!-- Resource Table -->
                    <reservation-table
                        :authorized-to-relate="authorizedToRelate"
                        :resource-name="resourceName"
                        :resources="resources"
                        :singular-name="singularName"
                        :selected-resources="selectedResources"
                        :selected-resource-ids="selectedResourceIds"
                        :actions-are-available="allActions.length > 0"
                        :should-show-checkboxes="shouldShowCheckBoxes"
                        :via-resource="viaResource"
                        :via-resource-id="viaResourceId"
                        :via-relationship="viaRelationship"
                        :relationship-type="relationshipType"
                        :update-selection-status="updateSelectionStatus"
                        @order="orderByField"
                        @delete="deleteResources"
                        @restore="restoreResources"
                        ref="resourceTable"
                    />
                </div>

                <!-- Pagination -->
                <component
                    :is="paginationComponent"
                    v-if="resourceResponse && resources.length > 0"
                    :next="hasNextPage"
                    :previous="hasPreviousPage"
                    @page="selectPage"
                    :pages="totalPages"
                    :page="currentPage"
                >
                    <span
                        v-if="resourceCountLabel"
                        class="text-sm text-80 px-4"
                        :class="{ 'ml-auto': paginationComponent == 'pagination-links' }"
                    >
                        {{ resourceCountLabel }}
                    </span>
                </component>
            </loading-view>
        </card>
    </loading-view>
</template>
<script>
    import ReservationTable from "../ReservationTable";
    // import Index from '../../../../../../nova/resources/js/views/Index'
    import Index from './Index'
    export default {
        name: "Arrival",
        components:{
            ReservationTable
        },
        mixins: [Index],
        props: {
            date: {
                default: '',
            },
            field: {
                type: Object,
            },
            resourceName: {
                default: 'arrivals',
            },
            viaResource: {
                default: '',
            },
            viaResourceId: {
                default: '',
            },
            viaRelationship: {
                default: '',
            },
            relationshipType: {
                type: String,
                default: '',
            },
        },
        methods: {
            readableDate() {
                let date = new Date(this.date);
                switch (date.getDay()) {
                    case 0:
                        return Nova.app.__('Sunday');
                    case 1:
                        return Nova.app.__('Monday');
                    case 2:
                        return Nova.app.__('Tuesday');
                    case 3:
                        return Nova.app.__('Wednesday');
                    case 4:
                        return Nova.app.__('Thursday');
                    case 5:
                        return Nova.app.__('Friday');
                    case 6:
                        return Nova.app.__('Saturday');
                }
            }
        },
        computed: {
            resourceRequestQueryString() {
                return {
                    date: this.date,
                    search: this.currentSearch,
                    filters: this.encodedFilters,
                    orderBy: this.currentOrderBy,
                    orderByDirection: this.currentOrderByDirection,
                    perPage: this.currentPerPage,
                    trashed: this.currentTrashed,
                    page: this.currentPage,
                    viaResource: this.viaResource,
                    viaResourceId: this.viaResourceId,
                    viaRelationship: this.viaRelationship,
                    viaResourceRelationship: this.viaResourceRelationship,
                    relationshipType: this.relationshipType,
                }
            },
        },
        watch: {
            date: function (val) {
                
                this.getResources();
            },
        }
    }
</script>
<style scoped>
h3.text_font_edit, .text-base.text-100.font-normal.mt-2 {font-size: 17px;}
</style>