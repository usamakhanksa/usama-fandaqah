<template>
    <div class="main_reservations_table rounded overflow-hidden">
        <table class="table w-full"
               v-if="resources.length > 0"
               cellpadding="0"
               cellspacing="0"

        >
            <thead>
            <tr>
                <th colspan="6"></th>
                <th colspan="6">{{ __('Reservation') }}</th>
                <th colspan="3">{{ __('The Due') }}</th>
                <th colspan="3">{{ __('Finance') }}</th>
                <th></th>
            </tr>
            </thead>
            <thead>
            <tr>
              <th>{{__('Reservation Number')}}</th>
              <th>{{ __('Customer') }}</th>
              <th>{{ __('Unit Number') }}</th>
              <th>{{ __('Unit Name') }}</th>
              <th>{{ __('Status') }}</th>
              <th>{{ __('Reservation Status') }}</th>
              <th>{{ __('Rent Type') }}</th>
              <th>{{ __('Date In') }}</th>
              <th>{{ __('Date Out') }}</th>
              <th>{{ __('Nights Count') }}</th>
              <th>{{ __('Leasing') }}</th>
              <th>{{ __('Services') }}</th>
              <th>{{ __('Amount') }}</th>
              <th>{{ __('Taxes') }}</th>
              <th>{{ __('The Total') }}</th>
              <th>{{ __('Paid') }}</th>
              <th>{{ __('Creditor') }}</th>
              <th>{{ __('Debtor') }}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>


            <tr
                    v-for="(resource, index) in resources"
                    :testId="`${resourceName}-items-${index}`"
                    :key="resource.id.value"
                    :delete-resource="deleteResource"
                    :restore-resource="restoreResource"
                    is="reservations-table-row"
                    :resource="resource"
                    :resource-name="resourceName"
                    :relationship-type="relationshipType"
                    :via-relationship="viaRelationship"
                    :via-resource="viaResource"
                    :via-resource-id="viaResourceId"
                    :via-many-to-many="viaManyToMany"
                    :checked="selectedResources.indexOf(resource) > -1"
                    :actions-are-available="actionsAreAvailable"
                    :should-show-checkboxes="shouldShowCheckboxes"
                    :update-selection-status="updateSelectionStatus"
            />



            </tbody>
        </table>
    </div><!-- main_reservations_table -->

</template>

<script>
    import { InteractsWithResourceInformation } from 'laravel-nova'

    export default {
        mixins: [InteractsWithResourceInformation],
        name: "reservation-table",

        props: {
            authorizedToRelate: {
                type: Boolean,
                required: true,
            },
            resourceName: {
                default: null,
            },
            resources: {
                default: [],
            },
            singularName: {
                type: String,
                required: true,
            },
            selectedResources: {
                default: [],
            },
            selectedResourceIds: {},
            shouldShowCheckboxes: {
                type: Boolean,
                default: false,
            },
            actionsAreAvailable: {
                type: Boolean,
                default: false,
            },
            viaResource: {
                default: null,
            },
            viaResourceId: {
                default: null,
            },
            viaRelationship: {
                default: null,
            },
            relationshipType: {
                default: null,
            },
            updateSelectionStatus: {
                type: Function,
            },
        },

        data: () => ({
            selectAllResources: false,
            selectAllMatching: false,
            resourceCount: null,
        }),

        methods: {
            /**
             * Delete the given resource.
             */
            deleteResource(resource) {
                this.$emit('delete', [resource])
            },

            /**
             * Restore the given resource.
             */
            restoreResource(resource) {
                this.$emit('restore', [resource])
            },

            /**
             * Broadcast that the ordering should be updated.
             */
            requestOrderByChange(field) {
                this.$emit('order', field)
            },
        },

        computed: {
            /**
             * Get all of the available fields for the resources.
             */
            // fields() {
            //     if (this.resources) {
            //         return this.resources[0].fields
            //     }
            // },

            /**
             * Determine if the current resource listing is via a many-to-many relationship.
             */
            viaManyToMany() {
                return (
                    this.relationshipType == 'belongsToMany' || this.relationshipType == 'morphToMany'
                )
            },

            /**
             * Determine if the current resource listing is via a has-one relationship.
             */
            viaHasOne() {
                return this.relationshipType == 'hasOne' || this.relationshipType == 'morphOne'
            },
        },
    }
</script>
<style>
.cursor-pointer.inline-flex.items-center svg {
	display: none;
}
.main_reservations_table {
  overflow: auto;
  width: 100%;
  padding: 0 0 15px 0;
  background: #fff;
}
.main_reservations_table .table {border: 1px solid #e2e8f0;}
.main_reservations_table .table thead tr th {
  padding: 10px 5px;
  line-height: 20px;
  font-weight: normal;
  font-size: 15px;
  border: 1px solid #5E697C;
  vertical-align: middle;
  text-align: center;
  color: #ffffff;
  background: #4a5568;
}
.main_reservations_table .table tbody tr {background: #fff;}
.main_reservations_table .table tbody tr td {
  text-align: center;
  padding: 10px 5px;
  vertical-align: middle;
  line-height: 20px;
  font-size: 15px;
  border: 1px solid #ced4dc;
  color: #000000;
  font-weight: normal;
  height: 3.3rem;
  background: #ffffff;
}
.main_reservations_table .table tbody tr td .font-bold {font-weight: normal;}
/*.main_reservations_table .table tbody tr td span.active::before {*/
/*    content: "";*/
/*    height: 8px;*/
/*    width: 8px;*/
/*    display: inline-block;*/
/*    background: #38c172;*/
/*    border-radius: 100%;*/
/*    margin: 0 0 0 5px;*/
/*}*/
/*.main_reservations_table .table tbody tr td span.active::before {*/
/*    content: "";*/
/*    height: 8px;*/
/*    width: 8px;*/
/*    display: inline-block;*/
/*    background: #38c172;*/
/*    border-radius: 100%;*/
/*    margin: 0 0 0 5px;*/
/*}*/
/*.main_reservations_table .table tbody tr td span.canceled::before {*/
/*    content: "";*/
/*    height: 8px;*/
/*    width: 8px;*/
/*    display: inline-block;*/
/*    background: #ff0000;*/
/*    border-radius: 100%;*/
/*    margin: 0 0 0 5px;*/
/*}*/
/*.main_reservations_table .table tbody tr td a {color: #297ec0;}*/
.main_reservations_table .table tbody tr td a:hover {color: #3d92d4;}
/* Portrait phones and smaller */
@media (min-width: 320px) and (max-width: 480px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

/* Smart phones and Tablets */
@media (min-width: 481px) and (max-width: 767px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

/* Small Screens */
@media (min-width: 768px) and (max-width: 991px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}

/* Medium Screens */
@media (min-width: 992px) and (max-width: 1000px) {
  .main_reservations_table {
    overflow: scroll;
    padding: 0 0 15px 0;
  }
}
</style>
