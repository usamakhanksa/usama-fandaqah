<template>
  <loading-view :loading="initialLoading" :dusk="lens + '-lens-component'">
      <bread-crumb />
    <div id="employee_contracts_page">
      <div class="title">{{__('Employee Contracts')}}</div>
      <div class="content_page">
        <div v-if="shouldShowCards">
          <cards
            v-if="smallCards.length > 0"
            :cards="smallCards"
            class="mb-3"
            :resource-name="resourceName"
            :lens="lens"
          />
          <cards
            v-if="largeCards.length > 0"
            :cards="largeCards"
            size="large"
            :resource-name="resourceName"
            :lens="lens"
          />
        </div><!-- shouldShowCards -->
        <big-filter-card :resourceName="this.resourceName">
          <div slot="reset-btn" class="reset_filters">
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
        </big-filter-card>

        <hr>

        <div class="statistics_area">
          <ul>
            <li>
              <span>{{__('Total Reservations')}}</span>
              <p>{{reservations_count}}</p>
            </li>
            <li>
              <span>{{__('Total Reservations Amount')}}</span>
              <p>{{reservations_total}} {{__(currency)}}</p>
            </li>
          </ul>
        </div><!-- statistics_area -->

        <hr>

        <div class="action_buttons">
          <button type="button" class="excel_button" @click="excelExport"></button>
          <button type="button" class="print_button" @click="printReport"></button>
        </div><!-- action_buttons -->

        <div class="table_area">
          <loading-view :loading="loading">
            <div v-if="!resources.length" class="no_data_show">
              <svg xmlns="http://www.w3.org/2000/svg" width="65" height="51"><path d="M56 40h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0v-2h-2a1 1 0 1 1 0-2h2v-2a1 1 0 1 1 2 0v2zm-5.364-8H38v8h7.05c.35-3.53 2.535-6.517 5.587-8zM45.05 42H6a6 6 0 0 1-6-6V6a6 6 0 0 1 6-6h44a6 6 0 0 1 6 6v25.05c5.053.502 9 4.765 9 9.95 0 5.523-4.477 10-10 10-5.185 0-9.45-3.947-9.95-9zM20 30h16v-8H20v8zm0 2v8h16v-8H20zm34-2v-8H38v8h16zM2 30h16v-8H2v8zm0 2v4a4 4 0 0 0 4 4h12v-8H2zm18-12h16v-8H20v8zm34 0v-8H38v8h16zM2 20h16v-8H2v8zm52-10V6a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v4h52zm1 39a8 8 0 1 0 0-16 8 8 0 1 0 0 16z" fill="#a8b9c5" fill-rule="nonzero"/></svg>
              <span>{{__('No Contracts Matched the selected criteria')}}</span>
            </div><!-- no_data_show -->
            <div class="table_responsive">
              <resource-table
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
              <form id="guests_movement_report_form" target="_blank" method="post"  style="display: none" action="/home/print/contractsReport">
                <input type="hidden" name="type" value="contracts_report">
                <input type="hidden" :value="contractsInformationParsed" name="contractsInformation">
              </form>
            </div><!-- table_responsive -->
          </loading-view>
        </div><!-- table_area -->
      </div><!-- content_page -->
    </div><!-- employee_contracts_page -->
  </loading-view>
</template>

<script>

    import XLSX from 'xlsx'
    import BreadCrumb from "./BreadCrumb";
    import BigFilterCard from "../helpers/BigFilterCard";
    import {
        Deletable,
        Errors,
        Filterable,
        HasCards,
        InteractsWithQueryString,
        InteractsWithResourceInformation,
        Minimum,
        Paginatable,
        PerPageable
    } from 'laravel-nova'
    export default {
    mixins: [
        HasCards,
        Deletable,
        Filterable,
        Paginatable,
        PerPageable,
        InteractsWithResourceInformation,
        InteractsWithQueryString,
    ],
    components:{
      'bread-crumb' : BreadCrumb ,
      'big-filter-card' : BigFilterCard
    },
    props: {
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
        lens: {
            type: String,
            required: true,
        },
    },

    data: () => ({
        initialLoading: true,
        loading: true,
        currency :Nova.app.currentTeam.currency,
        resourceResponse: null,
        resources: [],
        softDeletes: false,
        selectedResources: [],
        selectAllMatchingResources: false,
        allMatchingResourceCount: 0,
        hasId: false,

        deleteModalOpen: false,

        actions: [],
        pivotActions: null,
        actionValidationErrors: new Errors(),

        authorizedToRelate: false,

        orderBy: '',
        orderByDirection: '',
        trashed: '',
        headingTitle : 'Employee Contracts Report',
        contractsInformationParsed : null,
        contractsInformation : null,
        reservations_count : 0 ,
        reservations_total : 0

    }),

    /**
     * Mount the component and retrieve its initial data.
     */
    async created() {
        if (Nova.missingResource(this.resourceName)) return this.$router.push({ name: '404' })

        this.initializeSearchFromQueryString()
        this.initializePerPageFromQueryString()
        this.initializeTrashedFromQueryString()
        this.initializeOrderingFromQueryString()

        await this.initializeFilters(this.lens)
        this.getResources()
        // this.getAuthorizationToRelate()
        this.getActions()

        this.initialLoading = false

        this.$watch(
            () => {
                return (
                    this.lens +
                    this.resourceName +
                    this.encodedFilters +
                    this.currentSearch +
                    this.currentPage +
                    this.currentPerPage +
                    this.currentOrderBy +
                    this.currentOrderByDirection +
                    this.currentTrashed
                )
            },
            () => {
                this.getResources()
            }
        )
    },

    beforeRouteUpdate(to, from, next) {
        next()
        this.initializeState(this.lens)
    },

    methods: {

        /**
         * @author : Emad Rashad
         * @description :  Reset all filter all over the page
         */
        resetFilters(){
            // Emit Bus Event to handle custom filters
            // Nova.$emit('reset-clicked-withdraw') ;
            _.map(this.filters, filter => filter.currentValue = '');
        },

        excelExport() {

            Nova.request().post('/nova-vendor/transactions-feature/employee-contracts-report-excel', {
                contractsInformation: this.contractsInformationParsed ,

            }).then(response => {

                        let  defaultCellStyle = { font: { name: "Verdana", sz: 11, color: "FF00FF88"}, fill: {fgColor: {rgb: "FFFFAA00"}}};
                         // Data coming from my tool controller
                        let dataToBeExported = response.data.data;
                        // Export Json Data as worksheet
                        var transactionsWs = XLSX.utils.json_to_sheet(dataToBeExported)
                        // New workbook instance
                        var wb = XLSX.utils.book_new() // make Workbook of Excel
                        // Adding worksheet to workbook
                        XLSX.utils.book_append_sheet(wb, transactionsWs, response.data.filename );
                        // Export file
                        XLSX.writeFile(wb, response.data.filename + '.xlsx' , {defaultCellStyle : defaultCellStyle});
                        // fire success toast
                        this.$toasted.show(this.__('Report was exported successfully'), {type: 'success'});


                })
        },

        // pdfExport(){
        //      let self = this ;
        //     Nova.request().get('/nova-vendor/transactions-feature/transactions/exportPdf', {
        //         params: this.resourceRequestQueryString
        //     }).then(function(response) {
        //
        //             if(self.selectedResourceIds.length <= 0 ){
        //                  self.$toasted.show(self.__('Sorry , we can not generate your pdf unless you select at least one record '), {type: 'error'});
        //                  return false;
        //             }else{
        //                 self.$toasted.show(self.__('We are Generating PDF for you , It will be downloaded at once '), {type: 'success'});
        //
        //                 let blob = new Blob([response.data], { type: 'application/pdf' })
        //                 let  filename = response.headers['content-disposition'].split('=')[1].replace(/^\"+|\"+$/g, '')
        //                 let link = document.createElement('a')
        //                 link.href = window.URL.createObjectURL(blob)
        //                 link.download = filename
        //                 link.click()
        //
        //             }
        //
        //         })
        // },

        // printReport(){
        //     Nova.request().get('/nova-vendor/transactions-feature/transactions/printReport', {
        //         params: this.resourceRequestQueryString
        //     }).then(response => {
        //
        //             if(response.data.status == 'no_selection_to_print'){
        //                 this.$toasted.show(this.__('Please make sure to select at least one record to print '), {type: 'error'});
        //                 return false ;
        //
        //             }else{
        //
        //                 console.log(response.data) ;
        //             //    this.$toasted.show(this.__('You Shall See Print Screen Now'), {type: 'success'});
        //                printJS({
        //                             documentTitle :  response.data.title ,
        //                             header :  response.data.title ,
        //                             headerStyle : 'font-weight: 300;' ,
        //                             css: ['https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
        //                                         'https://fonts.googleapis.com/css?family=Open+Sans:400,700'
        //                             ],
        //                             scanStyles: false,
        //                             gridStyle : 'border: 1px solid lightgray; margin-bottom: -1px;padding:5px;',
        //                             showModal : true ,
        //                             modalMessage : 'Preparing the document' ,
        //                             printable: response.data.printable,
        //                             type: 'json',
        //                             properties: response.data.properties
        //                 });
        //             }
        //
        //         });
        // },

        printReport(){
            $('#guests_movement_report_form').submit();
        },

        selectAllResources() {
            this.selectedResources = this.resources.slice(0)
        },

        toggleSelectAll() {
            if (this.selectAllChecked) return this.clearResourceSelections()
            this.selectAllResources()
        },

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
                    Nova.request().get('/nova-api/' + this.resourceName + '/lens/' + this.lens, {
                        params: this.resourceRequestQueryString,
                    }),
                    300
                ).then(({ data }) => {
                    this.resources = []

                    this.resourceResponse = data
                    this.resources = data.resources
                    this.softDeletes = data.softDeletes
                    this.perPage = data.per_page
                    this.hasId = data.hasId

                    this.loading = false

                    this.getAllMatchingResourceCount()

                    // console.log(this.getAllMatchingResourceCount) ;
                    // console.log(data.resources) ;

                    if (!this.hasId) {

                        this.selectAllMatchingResources = true
                        this.selectAllResources()
                    }

                    Nova.$emit('resources-loaded')
                })
            })
        },

        /**
         * Get the actions available for the current resource.
         */
        getActions() {
            this.actions = []
            this.pivotActions = null
            Nova.request()
                .get(`/nova-api/${this.resourceName}/lens/${this.lens}/actions`, {
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
                .get('/nova-api/' + this.resourceName + '/lens/' + this.lens + '/count', {
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
         * Get the endpoint for this resource's metrics.
         */
        lensActionEndpoint() {
            return `/nova-api/${this.resourceName}/lens/${this.lens}/action`
        },


        filters() {

            return this.$store.getters[`${this.resourceName}/filters`]
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
                page: this.currentPage,
                viaResource: this.viaResource,
                viaResourceId: this.viaResourceId,
                // viaRelationship: this.viaRelationship,
                viaResourceRelationship: this.viaResourceRelationship,
                relationshipType: this.relationshipType,
                type: 'guests-report'
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
            return this.resourceInformation.singularLabel
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
         * Determine if the resource should show any cards
         */
        shouldShowCards() {
            return this.cards.length > 0
        },

        /**
         * Get the endpoint for this resource's metrics.
         */
        cardsEndpoint() {
            return `/nova-api/${this.resourceName}/lens/${this.lens}/cards`
        },

        /**
         * Determine whether to show the selection checkboxes for resources
         */
        shouldShowCheckBoxes() {
            return false;
            return (
                Boolean(this.hasId && this.hasResources && !this.viaHasOne) &&
                Boolean(
                    this.actionsAreAvailable ||
                        this.authorizedToDeleteAnyResources ||
                        this.canShowDeleteMenu
                )
            )
        },

        /**
         * Determinw whether the delete menu should be shown to the user
         */
        shouldShowDeleteMenu() {
            return Boolean(this.selectedResources.length > 0) && this.canShowDeleteMenu
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
         * Determinw whether the user is authorized to perform actions on the delete menu
         */
        canShowDeleteMenu() {
            return (
                this.hasId &&
                Boolean(
                    this.authorizedToDeleteSelectedResources ||
                        this.authorizedToForceDeleteSelectedResources ||
                        this.authorizedToDeleteAnyResources ||
                        this.authorizedToForceDeleteAnyResources ||
                        this.authorizedToRestoreSelectedResources ||
                        this.authorizedToRestoreAnyResources
                )
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
         * Get the current per page value from the query string.
         */
        currentPerPage() {
            return this.perPage
        },

    },

        mounted() {
            Nova.$on('resources-loaded' , () => {

                Nova.request().post('/nova-vendor/transactions-feature/contractsInformations' , {filters : this.encodedFilters})
                    .then((response) => {

                        this.contractsInformationParsed = JSON.stringify(response.data) ;
                        this.contractsInformation = response.data ;
                        this.reservations_count = response.data.reservations_count ;
                        this.reservations_total = response.data.reservations_total_amount ;

                    }).catch((error) => {
                    this.$toasted.show(error, {type: 'error'});
                })
            });
        }
    }
</script>

<style lang="scss">
  #employee_contracts_page {
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
          width: auto;
          display: flex;
          padding: 0 10px;
          margin: 0 0 10px;
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
                padding: 10px;
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
                padding: 10px;
                font-size: 15px;
                line-height: 20px;
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
              } /* td */
            } /* tbody */
          } /* table */
        } /* table_responsive */
      } /* table_area */
    } /* content_page */
  } /* employee_contracts_page */
</style>
