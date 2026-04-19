<template>
  <loading-view :loading="initialLoading" :dusk="resourceName + '-index-component'">
    <!-- <custom-index-header v-if="!viaResource" class="mb-3" :resource-name="resourceName" /> -->
    <div v-if="shouldShowCards">
      <cards
        v-if="smallCards.length > 0"
        :cards="smallCards"
        class="mb-3"
        :resource-name="resourceName"
      />
      <cards
        v-if="largeCards.length > 0"
        :cards="largeCards"
        size="large"
        :resource-name="resourceName"
      />
    </div>
    <div id="reservations_management_page">
      <div class="title">{{__('Reservations Management')}}</div>
      <div class="content_page">
        <div v-if="hasFilters" class="filter_area">
          <div class="item" v-for="(filter,index) in filters" :key="index">
            <date-range-filter
              :filter-key="filter.class"
              :is="filter.component"
              :key="filter.name"
              :resource-name="resourceName"
              @change="$emit('filter-changed')"
              @input="$emit('filter-changed')"
              v-if="filter.component == 'date-range-filter'"
            />
            <reservation-number
              v-if="filter.component === 'reservation-number'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <reservation-status
              v-if="filter.component === 'reservation-status'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <customer-name
              v-if="filter.component === 'customer-name'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <unit-number
              v-if="filter.component === 'unit-number'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <unit-name
              v-if="filter.component === 'unit-name'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <creditor-debtor-filter
              v-if="filter.component === 'creditor-debtor-filter'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <rent-type-filter
              v-if="filter.component === 'rent-type-filter'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
            <customer-highlight-type-filter
              v-if="filter.component === 'customer-highlight-type-filter'"
              :resource-name="resourceName"
              :key="filter.name"
              :filter-key="filter.class"
              :is="filter.component"
              @input="$emit('filter-changed')"
              @change="$emit('filter-changed')"
            />
          </div><!-- item -->
          <div class="reset_filters">
            <button
              @click="resetFilters()"
              v-tooltip="{
                targetClasses: ['it-has-a-tooltip'],
                placement: 'top',
                content: __('Reset Filters'),
                classes: ['tooltip_reset']
              }"
            >
            </button>
          </div><!-- reset_filters -->
        </div><!-- filter_area -->

        <hr>
        
        <div class="statistics_area">
          <ul>
            <li>
              <total-amount :totalAmount="totalAmount" :loading="loading"></total-amount>
            </li>
            <li>
              <total-income :totalIncome="totalIncome" :loading="loading"></total-income>
            </li>
            <li>
              <total-rent :totalRent="totalRent" :loading="loading"></total-rent>
            </li>
            <li>
              <total-service :totalServices="totalServices" :loading="loading"></total-service>
            </li>
            <li>
              <total-tax :totalTaxes="totalTaxes" :loading="loading"></total-tax>
            </li>
            <li>
              <total-cost :totalCost="totalCost" :loading="loading"></total-cost>
            </li>
            <li>
              <total-receipts :totalReceipts="totalReceipts" :loading="loading"></total-receipts>
            </li>
            <li>
              <total-credit :theTotalCredit="theTotalCredit" :loading="loading"></total-credit>
            </li>
            <li>
              <total-debtor :totalDebtor="totalDebtor" :loading="loading"></total-debtor>
            </li>
            <li>
              <total-creditor :totalCreditor="totalCreditor" :loading="loading"></total-creditor>
            </li>
          </ul>
        </div><!-- statistics_area -->

        <hr>
        
        <div class="action_buttons" v-if="resources.length">
          <button type="button" class="excel_button" @click="excelExport"></button>
          <button type="button" class="print_button" @click="printReport()"></button>
        </div><!-- action_buttons -->

        <div class="table_area">
          <!-- <div class="py-3 flex items-center border-b border-50">
            <div class="flex items-center ml-auto px-3">
              <action-selector
                v-if="selectedResources.length > 0"
                :resource-name="resourceName"
                :actions="actions"
                :pivot-actions="pivotActions"
                :pivot-name="pivotName"
                :query-string="{
                    currentSearch,
                    encodedFilters,
                    currentTrashed,
                    viaResource,
                    viaResourceId,
                    viaRelationship,
                }"
                :selected-resources="selectedResourcesForActionSelector"
                @actionExecuted="getResources"
              />
            </div>
          </div> -->
          <loading-view :loading="loading">
            <div v-if="!resources.length" class="no_data_show">
              <svg xmlns="http://www.w3.org/2000/svg" width="65" height="51"><path d="M56 40h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0v-2h-2a1 1 0 1 1 0-2h2v-2a1 1 0 1 1 2 0v2zm-5.364-8H38v8h7.05c.35-3.53 2.535-6.517 5.587-8zM45.05 42H6a6 6 0 0 1-6-6V6a6 6 0 0 1 6-6h44a6 6 0 0 1 6 6v25.05c5.053.502 9 4.765 9 9.95 0 5.523-4.477 10-10 10-5.185 0-9.45-3.947-9.95-9zM20 30h16v-8H20v8zm0 2v8h16v-8H20zm34-2v-8H38v8h16zM2 30h16v-8H2v8zm0 2v4a4 4 0 0 0 4 4h12v-8H2zm18-12h16v-8H20v8zm34 0v-8H38v8h16zM2 20h16v-8H2v8zm52-10V6a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v4h52zm1 39a8 8 0 1 0 0-16 8 8 0 1 0 0 16z" fill="#a8b9c5" fill-rule="nonzero"/></svg>
              <span>{{('No Reservations Matched Your Criteria')}}</span>
            </div><!-- no_data_show -->
            <div class="overflow-hidden overflow-x-auto relative"> 
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
            <form id="reservations" target="_blank" method="post"  style="display: none" action="/home/print/reservationsReport">
              <input type="hidden" :value="reservationsData" name="reservationsData">
            </form>
          </loading-view>
        </div><!-- table_area -->
      </div><!-- content_page -->
    </div><!-- reservations_management_page --> 
        
        
  </loading-view>
</template>

<script>
    import TotalCredit from "./block_helpers/TotalCredit";
    import TotalCost from "./block_helpers/TotalCost";
    import TotalAmount from "./block_helpers/TotalAmount";
    import TotalReceipts from "./block_helpers/TotalReceipts";
    import TotalCreditor from "./block_helpers/TotalCreditor";
    import TotalIncome from "./block_helpers/TotalIncome";
    import TotalRent from "./block_helpers/TotalRent";
    import TotalService from "./block_helpers/TotalService";
    import TotalTax from "./block_helpers/TotalTax";
    import TotalDebtor from "./block_helpers/TotalDebtor";
    import DateRangeFilter from "./reservation_filters/DateRangeFilter";
    import ReservationNumber from "./reservation_filters/ReservationNumber";
    import ReservationStatus from "./reservation_filters/ReservationStatus";
    import CustomerHighlight from "./reservation_filters/CustomerHighlight";
    import CustomerName from "./reservation_filters/CustomerName";
    import UnitNumber from "./reservation_filters/UnitNumber";
    import CreditorDebtorFilter from "./reservation_filters/CreditorDebtorFilter";
    import RentTypeFilter from "./reservation_filters/RentTypeFilter";
    import UnitName from './reservation_filters/UnitName';

    import XLSX from 'xlsx'

    import {
        Capitalize,
        Deletable,
        Filterable,
        HasCards,
        InteractsWithQueryString,
        InteractsWithResourceInformation,
        Minimum,
        Paginatable,
        PerPageable
    } from 'laravel-nova'

    export default {
        name: "reservations",
        mixins: [
            Deletable,
            Filterable,
            HasCards,
            Paginatable,
            PerPageable,
            InteractsWithResourceInformation,
            InteractsWithQueryString,
        ],
        components:{
          TotalDebtor,
          TotalCreditor,
          TotalCost,
        TotalAmount,
          TotalCredit,
          TotalReceipts,
            DateRangeFilter,
          ReservationNumber,
          ReservationStatus,
          CustomerName,
          UnitNumber,
          CreditorDebtorFilter,
          RentTypeFilter,
          UnitName,
          TotalIncome,
          TotalRent,
          TotalService,
          TotalTax,
            'customer-highlight-type-filter': CustomerHighlight
        },

        props: {
            field: {
                type: Object,
            },
            resourceName: {
                type: String,
                required: true,
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

        data: () => ({
            actionEventsRefresher: null,
            initialLoading: true,
            loading: true,

            resourceResponse: null,
            resources: [],
            softDeletes: false,
            selectedResources: [],
            selectAllMatchingResources: false,
            allMatchingResourceCount: 0,

            deleteModalOpen: false,

            actions: [],
            pivotActions: null,

            search: '',
            lenses: [],

            authorizedToRelate: false,

            orderBy: '',
            orderByDirection: '',
            trashed: '',
            totalReceipts:0 ,
            totalCost:0 ,
            totalAmount:0 ,
            theTotalCredit:0 ,
            totalCreditor:0 ,
            totalDebtor:0 ,
            reservationsData : null,
            totalIncome : 0 ,
            totalRent : 0 ,
            totalServices : 0,
            totalTaxes : 0
        }),

        /**
         * Mount the component and retrieve its initial data.
         */
        async created() {
            if (Nova.missingResource(this.resourceName)) return this.$router.push({ name: '404' })

            // Bind the keydown even listener when the router is visited if this
            // component is not a relation on a Detail page
            if (!this.viaResource && !this.viaResourceId) {
                document.addEventListener('keydown', this.handleKeydown)
            }

            this.initializeSearchFromQueryString()
            this.initializePerPageFromQueryString()
            this.initializeTrashedFromQueryString()
            this.initializeOrderingFromQueryString()

            await this.initializeFilters()
            await this.getResources()
            await this.getAuthorizationToRelate()

            this.getLenses()
            this.getActions()

            this.initialLoading = false

            this.$watch(
                () => {
                    return (
                        this.resourceName +
                        this.encodedFilters +
                        this.currentSearch +
                        this.currentPage +
                        this.perPage +
                        this.currentOrderBy +
                        this.currentOrderByDirection +
                        this.currentTrashed
                    )
                },
                () => {
                    this.getResources()
                }
            )

            // Refresh the action events
            if (this.resourceName === 'action-events') {
                Nova.$on('refresh-action-events', () => {
                    this.getResources()
                })

                this.actionEventsRefresher = setInterval(() => {
                    if (document.hasFocus()) {
                        this.getResources()
                    }
                }, 15 * 1000)
            }
        },

        beforeRouteUpdate(to, from, next) {
            next()
            this.initializeState(false)
        },

        /**
         * Unbind the keydown even listener when the component is destroyed
         */
        destroyed() {
            if (this.actionEventsRefresher) {
                clearInterval(this.actionEventsRefresher)
            }

            document.removeEventListener('keydown', this.handleKeydown)
        },

        methods: {

            printReport(){
                $('#reservations').submit();
            },

            excelExport(){
                Nova.request().post('/nova-vendor/calender/reservations-management-excel', {
                    params: this.reservationsData
                }).then(response => {


                    let  defaultCellStyle = { font: { name: "Verdana", sz: 11, color: "FF00FF88"}, fill: {fgColor: {rgb: "FFFFAA00"}}};
                    // Data coming from my tool controller
                    let dataToBeExported = response.data.data
                    // Export Json Data as worksheet
                    let transactionsWs = XLSX.utils.json_to_sheet(dataToBeExported);
                    // New workbook instance
                    let wb = XLSX.utils.book_new(); // make Workbook of Excel
                    // Adding worksheet to workbook
                    XLSX.utils.book_append_sheet(wb, transactionsWs, response.data.filename ) // sheetAName is name of Worksheet

                    // Export file
                    XLSX.writeFile(wb, response.data.filename + '.xlsx' , {defaultCellStyle : defaultCellStyle})
                    // fire success toast
                    this.$toasted.show(this.__('Report was exported successfully'), {type: 'success'});

                })
            },
            /**
             * @author : Emad Rashad
             * @description :  Reset all filter all over the page
             */
            resetFilters(){
                _.map(this.filters, filter => filter.currentValue = '');
            },
            /**
             * @author : Emad Rashad
             * @description :  Handle Statistics Blocks Data
             */
            // getReservationBlocksStatistics(resources){
            //
            //     // Get the resources ids
            //     // let resources_ids = _.map(resources, resource => resource.id.value);
            //
            //     Nova.
            //         request().
            //             post('/nova-vendor/calender/reservation/reservation-statistics-blocks')
            //                 .then((response) => {
            //                     if(response.data.status === "success"){
            //                         // begin fill our data attributes
            //
            //
            //                             this.totalReceipts = response.data.total_receipts;
            //                             this.totalCost = response.data.total_cost;
            //                             this.theTotalCredit = response.data.the_total_credit;
            //                             this.totalCreditor = response.data.total_creditor;
            //                             this.totalDebtor = response.data.total_debtor;
            //
            //
            //                     }else{
            //                             // Else is needed to reset values if no resources matched
            //                             this.totalReceipts = response.data.total_receipts;
            //                             this.totalCost = response.data.total_cost;
            //                             this.theTotalCredit = response.data.the_total_credit;
            //                             this.totalCreditor = response.data.total_creditor;
            //                             this.totalDebtor = response.data.total_debtor;
            //                     }
            //                 }).catch((error) => {
            //                         this.$toasted.show(error, {type: 'error'});
            //                 })
            // },

            /**
             * Handle the keydown event
             */
            handleKeydown(e) {
                // `c`
                if (
                    this.authorizedToCreate &&
                    !e.ctrlKey &&
                    !e.altKey &&
                    !e.metaKey &&
                    !e.shiftKey &&
                    e.keyCode == 67 &&
                    e.target.tagName != 'INPUT' &&
                    e.target.tagName != 'TEXTAREA'
                ) {
                    this.$router.push({ name: 'create', params: { resourceName: this.resourceName } })
                }
            },

            /**
             * Select all of the available resources
             */
            selectAllResources() {
                this.selectedResources = this.resources.slice(0)
            },

            /**
             * Toggle the selection of all resources
             */
            toggleSelectAll(event) {
                if (this.selectAllChecked) return this.clearResourceSelections()
                this.selectAllResources()
            },

            /**
             * Toggle the selection of all matching resources in the database
             */
            toggleSelectAllMatching() {
                if (!this.selectAllMatchingResources) {
                    this.selectAllResources()
                    this.selectAllMatchingResources = true

                    return
                }

                this.selectAllMatchingResources = false
            },

            /*
             * Update the resource selection status
             */
            updateSelectionStatus(resource) {
                if (!_(this.selectedResources).includes(resource))
                    return this.selectedResources.push(resource)
                const index = this.selectedResources.indexOf(resource)
                if (index > -1) return this.selectedResources.splice(index, 1)
            },

            /**
             * Get the resources based on the current page, search, filters, etc.
             */
            getResources() {
                this.loading = true

                this.$nextTick(() => {
                    this.clearResourceSelections()

                    return Minimum(
                        Nova.request().get('/nova-api/' + this.resourceName, {
                            params: this.resourceRequestQueryString,
                        }),
                        300
                    ).then(({ data }) => {


                        this.resources = []

                        this.resourceResponse = data

                        this.resources = data.resources
                        this.softDeletes = data.softDeletes
                        // this.perPage = data.per_page



                        this.loading = false

                        this.getAllMatchingResourceCount();
                        // this.getReservationBlocksStatistics(this.resources) ;

                        this.$emit('resources-loaded')
                    }).catch((err)=>{
                         console.log(err)
                    })
                })
            },

            /**
             * Get the relatable authorization status for the resource.
             */
            getAuthorizationToRelate() {
                if (
                    !this.authorizedToCreate &&
                    (this.relationshipType != 'belongsToMany' && this.relationshipType != 'morphToMany')
                ) {
                    return
                }

                if (!this.viaResource) {
                    return (this.authorizedToRelate = true)
                }

                return Nova.request()
                    .get(
                        '/nova-api/' +
                        this.resourceName +
                        '/relate-authorization' +
                        '?viaResource=' +
                        this.viaResource +
                        '&viaResourceId=' +
                        this.viaResourceId +
                        '&viaRelationship=' +
                        this.viaRelationship +
                        '&relationshipType=' +
                        this.relationshipType
                    )
                    .then(response => {
                        this.authorizedToRelate = response.data.authorized
                    })
            },

            /**
             * Get the lenses available for the current resource.
             */
            getLenses() {
                this.lenses = []

                if (this.viaResource) {
                    return
                }

                return Nova.request()
                    .get('/nova-api/' + this.resourceName + '/lenses')
                    .then(response => {
                        this.lenses = response.data
                    })
            },

            /**
             * Get the actions available for the current resource.
             */
            getActions() {
                this.actions = []
                this.pivotActions = null
                return Nova.request()
                    .get(`/nova-api/${this.resourceName}/actions`, {
                        params: {
                            viaResource: this.viaResource,
                            viaResourceId: this.viaResourceId,
                            viaRelationship: this.viaRelationship,
                            relationshipType: this.relationshipType,
                        },
                    })
                    .then(response => {
                        this.actions = _.filter(response.data.actions, action => {
                            return !action.onlyOnDetail
                        })
                        this.pivotActions = response.data.pivotActions
                    })
            },

            /**
             * Execute a search against the resource.
             */
            performSearch(event) {
                this.debouncer(() => {
                    // Only search if we're not tabbing into the field
                    if (event.which != 9) {
                        this.updateQueryString({
                            [this.pageParameter]: 1,
                            [this.searchParameter]: this.search,
                        })
                    }
                })
            },

            debouncer: _.debounce(callback => callback(), 500),

            /**
             * Clear the selected resouces and the "select all" states.
             */
            clearResourceSelections() {
                this.selectAllMatchingResources = false
                this.selectedResources = []
            },

            /**
             * Get the count of all of the matching resources.
             */
            getAllMatchingResourceCount() {
                Nova.request()
                    .get('/nova-api/' + this.resourceName + '/count', {
                        params: this.resourceRequestQueryString,
                    })
                    .then(response => {
                        this.allMatchingResourceCount = response.data.count
                    })
            },

            /**
             * Sort the resources by the given field.
             */
            orderByField(field) {
                var direction = this.currentOrderByDirection == 'asc' ? 'desc' : 'asc'
                if (this.currentOrderBy != field.attribute) {
                    direction = 'asc'
                }
                this.updateQueryString({
                    [this.orderByParameter]: field.attribute,
                    [this.orderByDirectionParameter]: direction,
                })
            },

            /**
             * Sync the current search value from the query string.
             */
            initializeSearchFromQueryString() {
                this.search = this.currentSearch
            },

            /**
             * Sync the current order by values from the query string.
             */
            initializeOrderingFromQueryString() {
                this.orderBy = this.currentOrderBy
                this.orderByDirection = this.currentOrderByDirection
            },

            /**
             * Sync the trashed state values from the query string.
             */
            initializeTrashedFromQueryString() {
                this.trashed = this.currentTrashed
            },

            /**
             * Update the trashed constraint for the resource listing.
             */
            trashedChanged(trashedStatus) {
                this.trashed = trashedStatus
                this.updateQueryString({ [this.trashedParameter]: this.trashed })
            },

            /**
             * Update the per page parameter in the query string
             */
            updatePerPageChanged(perPage) {
                this.perPage = perPage
                this.perPageChanged()
            },

            /**
             * Select the next page.
             */
            selectPage(page) {
                this.updateQueryString({ [this.pageParameter]: page })
            },

            /**
             * Sync the per page values from the query string.
             */
            initializePerPageFromQueryString() {
                this.perPage = this.$route.query[this.perPageParameter] || 25
            },
        },

        computed: {
            /**
             * Determine if the resource has any filters
             */
            hasFilters() {
                return this.$store.getters[`${this.resourceName}/hasFilters`]
            },
            filters() {

                return this.$store.getters[`${this.resourceName}/filters`]
            },

            /**
             * Determine if the resource should show any cards
             */
            shouldShowCards() {
                // Don't show cards if this resource is beings shown via a relations
                return this.cards.length > 0 && this.resourceName == this.$route.params.resourceName
            },

            /**
             * Get the endpoint for this resource's metrics.
             */
            cardsEndpoint() {
                return `/nova-api/${this.resourceName}/cards`
            },

            /**
             * Get the name of the search query string variable.
             */
            searchParameter() {
                return this.resourceName + '_search'
            },

            /**
             * Get the name of the order by query string variable.
             */
            orderByParameter() {
                return this.resourceName + '_order'
            },

            /**
             * Get the name of the order by direction query string variable.
             */
            orderByDirectionParameter() {
                return this.resourceName + '_direction'
            },

            /**
             * Get the name of the trashed constraint query string variable.
             */
            trashedParameter() {
                return this.resourceName + '_trashed'
            },

            /**
             * Get the name of the per page query string variable.
             */
            perPageParameter() {
                return this.resourceName + '_per_page'
            },

            /**
             * Get the name of the page query string variable.
             */
            pageParameter() {
                return this.resourceName + '_page'
            },

            /**
             * Build the resource request query string.
             */
            resourceRequestQueryString() {
                return {
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

            /**
             * Determine if all resources are selected.
             */
            selectAllChecked() {
                return this.selectedResources.length == this.resources.length
            },

            /**
             * Determine if all matching resources are selected.
             */
            selectAllMatchingChecked() {
                return (
                    this.selectedResources.length == this.resources.length &&
                    this.selectAllMatchingResources
                )
            },

            /**
             * Get the IDs for the selected resources.
             */
            selectedResourceIds() {
                return _.map(this.selectedResources, resource => resource.id.value)
            },

            /**
             * Get all of the actions available to the resource.
             */
            allActions() {
                return this.hasPivotActions
                    ? this.actions.concat(this.pivotActions.actions)
                    : this.actions
            },

            /**
             * Determine if the resource has any pivot actions available.
             */
            hasPivotActions() {
                return this.pivotActions && this.pivotActions.actions.length > 0
            },

            /**
             * Determine if the resource has any actions available.
             */
            actionsAreAvailable() {
                return this.allActions.length > 0
            },

            /**
             * Get the name of the pivot model for the resource.
             */
            pivotName() {
                return this.pivotActions ? this.pivotActions.name : ''
            },

            /**
             * Get the current search value from the query string.
             */
            currentSearch() {
                return this.$route.query[this.searchParameter] || ''
            },

            /**
             * Get the current order by value from the query string.
             */
            currentOrderBy() {
                return this.$route.query[this.orderByParameter] || ''
            },

            /**
             * Get the current order by direction from the query string.
             */
            currentOrderByDirection() {
                return this.$route.query[this.orderByDirectionParameter] || 'desc'
            },

            /**
             * Get the current trashed constraint value from the query string.
             */
            currentTrashed() {
                return this.$route.query[this.trashedParameter] || ''
            },

            /**
             * Determine if the current resource listing is via a many-to-many relationship.
             */
            viaManyToMany() {
                return (
                    this.relationshipType == 'belongsToMany' || this.relationshipType == 'morphToMany'
                )
            },

            /**
             * Determine if the resource / relationship is "full".
             */
            resourceIsFull() {
                return this.viaHasOne && this.resources.length > 0
            },

            /**
             * Determine if the current resource listing is via a has-one relationship.
             */
            viaHasOne() {
                return this.relationshipType == 'hasOne' || this.relationshipType == 'morphOne'
            },

            /**
             * Get the singular name for the resource
             */
            singularName() {
                if (this.isRelation && this.field) {
                    return Capitalize(this.field.singularLabel)
                }

                return Capitalize(this.resourceInformation.singularLabel)
            },

            /**
             * Get the selected resources for the action selector.
             */
            selectedResourcesForActionSelector() {
                return this.selectAllMatchingChecked ? 'all' : this.selectedResourceIds
            },

            /**
             * Determine if there are any resources for the view
             */
            hasResources() {
                return Boolean(this.resources.length > 0)
            },

            /**
             * Determine if there any lenses for this resource
             */
            hasLenses() {
                return Boolean(this.lenses.length > 0)
            },

            /**
             * Determine whether to show the selection checkboxes for resources
             */
            shouldShowCheckBoxes() {
                return (
                    Boolean(this.hasResources && !this.viaHasOne) &&
                    Boolean(
                        this.actionsAreAvailable ||
                        this.authorizedToDeleteAnyResources ||
                        this.canShowDeleteMenu
                    )
                )
            },

            /**
             * Determine if any selected resources may be deleted.
             */
            authorizedToDeleteSelectedResources() {
                return Boolean(_.find(this.selectedResources, resource => resource.authorizedToDelete))
            },

            /**
             * Determine if any selected resources may be force deleted.
             */
            authorizedToForceDeleteSelectedResources() {
                return Boolean(
                    _.find(this.selectedResources, resource => resource.authorizedToForceDelete)
                )
            },

            /**
             * Determine if the user is authorized to delete any listed resource.
             */
            authorizedToDeleteAnyResources() {
                return (
                    this.resources.length > 0 &&
                    Boolean(_.find(this.resources, resource => resource.authorizedToDelete))
                )
            },

            /**
             * Determine if the user is authorized to force delete any listed resource.
             */
            authorizedToForceDeleteAnyResources() {
                return (
                    this.resources.length > 0 &&
                    Boolean(_.find(this.resources, resource => resource.authorizedToForceDelete))
                )
            },

            /**
             * Determine if any selected resources may be restored.
             */
            authorizedToRestoreSelectedResources() {
                return Boolean(_.find(this.selectedResources, resource => resource.authorizedToRestore))
            },

            /**
             * Determine if the user is authorized to restore any listed resource.
             */
            authorizedToRestoreAnyResources() {
                return (
                    this.resources.length > 0 &&
                    Boolean(_.find(this.resources, resource => resource.authorizedToRestore))
                )
            },

            /**
             * Determinw whether the delete menu should be shown to the user
             */
            shouldShowDeleteMenu() {
                return Boolean(this.selectedResources.length > 0) && this.canShowDeleteMenu
            },

            /**
             * Determine whether the user is authorized to perform actions on the delete menu
             */
            canShowDeleteMenu() {
                return Boolean(
                    this.authorizedToDeleteSelectedResources ||
                    this.authorizedToForceDeleteSelectedResources ||
                    this.authorizedToRestoreSelectedResources ||
                    this.selectAllMatchingChecked
                )
            },

            /**
             * Determine if the index is a relation field
             */
            isRelation() {
                return Boolean(this.viaResourceId && this.viaRelationship)
            },

            /**
             * Return the heading for the view
             */
            headingTitle() {
                return this.loading
                    ? '&nbsp;'
                    : this.isRelation && this.field
                        ? this.field.name
                        : this.resourceResponse.label
            },

            /**
             * Return the resource count label
             */
            resourceCountLabel() {
                const first = this.perPage * (this.currentPage - 1)

                return (
                    this.resources.length &&
                    `${first + 1}-${first + this.resources.length} ${this.__('of')} ${
                        this.allMatchingResourceCount
                        }`
                )
            },

            /**
             * Return the currently encoded filter string from the store
             */
            encodedFilters() {
                return this.$store.getters[`${this.resourceName}/currentEncodedFilters`]
            },

            /**
             * Return the initial encoded filters from the query string
             */
            initialEncodedFilters() {
                return this.$route.query[this.filterParameter] || ''
            },

            paginationComponent() {
                return `pagination-${Nova.config['pagination'] || 'links'}`
            },

            hasNextPage() {
                return Boolean(this.resourceResponse && this.resourceResponse.next_page_url)
            },

            hasPreviousPage() {
                return Boolean(this.resourceResponse && this.resourceResponse.prev_page_url)
            },

            totalPages() {
                return Math.ceil(this.allMatchingResourceCount / this.currentPerPage)
            },

            /**
             * Get the current per page value from the query string.
             */
            currentPerPage() {
                return this.perPage
            },
        },
        mounted(){


            this.$on('resources-loaded' , () => {

                if (this.resourceName == 'reservations') {
                    Nova.request().post('/nova-vendor/calender/reservation/reservation-statistics-blocks' , {filters : this.encodedFilters})
                        .then((response) => {



                            if(response.data.status === "success"){
                                // begin fill our data attributes

                                this.reservationsData = JSON.stringify(response.data);
                                this.totalReceipts = response.data.total_receipts;
                                this.totalCost = response.data.total_cost;
                                this.totalAmount = response.data.total_amount;
                                this.theTotalCredit = response.data.the_total_credit;
                                this.totalCreditor = response.data.total_creditor;
                                this.totalDebtor = response.data.total_debtor;
                                this.totalIncome = response.data.total_income;
                                this.totalRent =  response.data.total_rent;
                                this.totalServices =  response.data.total_services;
                                this.totalTaxes =  response.data.total_taxes;



                            }
                            else{
                                // Else is needed to reset values if no resources matched
                                this.totalReceipts = response.data.total_receipts;
                                this.totalCost = response.data.total_cost;
                                this.totalAmount = response.data.total_amount;
                                this.theTotalCredit = response.data.the_total_credit;
                                this.totalCreditor = response.data.total_creditor;
                                this.totalDebtor = response.data.total_debtor;
                                this.totalIncome = response.data.total_income;
                                this.totalRent =  response.data.total_rent;
                                this.totalServices =  response.data.total_services;
                                this.totalTaxes =  response.data.total_taxes;
                            }


                        }).catch((error) => {
                        this.$toasted.show(error, {type: 'error'});
                    })
                }
            });
        }
    }
</script>

<style lang="scss">
#reservations_management_page {
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: .5rem;
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
  overflow: hidden;
  .title{
    background: #f7fafc;
    border-bottom: 1px solid #ddd;
    padding: .75rem;
    color: #000;
    font-size: 1.125rem;
    display: block;
  } /* title */
  .content_page {
    background: #fff;
    padding: 10px;
    .filter_area {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      justify-content: flex-start;
      margin: 0 -10px;
      .item {
        width: 20%;
        padding: 0 10px;
        margin: 0 0 10px;
        @media (min-width: 320px) and (max-width: 480px) {
          width: 50%;
        } /* media */
        @media (min-width: 481px) and (max-width: 767px) {
          width: 33.33333%;
        } /* media */
        @media (min-width: 768px) and (max-width: 991px) {
          width: 25%;
        } /* media */
        input {
          background: #fafafa;
          height: 40px;
          padding: 0 10px;
          font-size: 15px;
          border: 1px solid #ddd !important;
          color: #000;
          width: 100%;
          border-radius: 4px !important;
          outline: none;
        } /* input */
        select {
          background-color: #fafafa;
          background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
          background-repeat: no-repeat;
          background-size: 14px;
          background-position: 10px center;
          height: 40px;
          padding: 0 10px;
          font-size: 15px;
          border: 1px solid #ddd !important;
          color: #000;
          width: 100%;
          border-radius: 4px !important;
          outline: none;
          -webkit-appearance: none;
          -moz-appearance: none;
          -o-appearance: none;
          appearance: none;
        } /* select */
      } /* item */
      .reset_filters {
        width: 100%;
        display: flex;
        padding: 0 10px;
        justify-content: flex-end;
        button {
          height: 40px;
          width: 40px;
          background-color: #718096;
          border-radius: 4px;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: center center;
          background-size: 20px;
          -webkit-transition: all 0.2s ease-in-out;
          -moz-transition: all 0.2s ease-in-out;
          -o-transition: all 0.2s ease-in-out;
          transition: all 0.2s ease-in-out;
          &:hover {
            background-color: #5E6D83;
          } /* hover */
        } /* button */
      } /* reset_filters */
    } /* filter_area */
    hr {
      margin: 20px auto;
      border-color: #ddd;
      &:last-of-type {
        margin: 0 0 20px;
      } /* last-of-type */
    } /* hr */
    .statistics_area {
      ul {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin: 0 -10px;
        li {
          width: 20%;
          padding: 0 10px;
          margin: 0 0 20px;
          @media (min-width: 320px) and (max-width: 480px) {
            width: 50%;
          } /* media */
          @media (min-width: 481px) and (max-width: 767px) {
            width: 33.33333%;
          } /* media */
          @media (min-width: 768px) and (max-width: 991px) {
            width: 25%;
          } /* media */
          span {
            display: block;
            font-size: 15px;
            color: #000;
            margin: 0 0 5px;
          } /* span */
          p {
            display: block;
            font-size: 16px;
            font-weight: bold;
            &.totalDebtor {
              color: #f56565;
            } /* totalDebtor */
            &.totalCreditor {
              color: #48bb78;
            } /* totalCreditor */
          } /* p */
        } /* li */
      } /* ul */
    } /* statistics_area */
    .action_buttons {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      margin: 0 auto 10px;
      button {
        display: block;
        height: 30px;
        width: 30px;
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
    .table_area {
      .no_data_show {
        text-align: center;
        padding: 50px 15px 40px;
        svg {
          display: block;
          margin: 0 auto 15px;
        } /* svg */
        span {
          display: block;
          font-size: 15px;
          text-align: center;
          color: #000;
        } /* span */
      } /* no_data_show */
    } /* table_area */
  } /* content_page */
} /* reservations_management_page */
</style>
