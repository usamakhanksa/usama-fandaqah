<template>
    <div v-if="!loading">

        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>

        <heading class="mb-3">{{ __('Edit :resource', { resource: singularName }) }}</heading>

        <card class="overflow-hidden">
            <form v-if="fields" @submit.prevent="updateResource" autocomplete="off">
                <!-- Validation Errors -->
                <validation-errors :errors="validationErrors" />

                <!-- Fields -->
                <div v-for="field in fields">
                    <component
                        @file-deleted="updateLastRetrievedAtTimestamp"
                        :is="'form-' + field.component"
                        :errors="validationErrors"
                        :resource-id="resourceId"
                        :resource-name="resourceName"
                        :field="field"
                    />
                </div>

                <!-- Update Button -->
                <div class="bg-30 flex items-center px-8 py-4">
<!--                    <a-->
<!--                        @click="$router.back()"-->
<!--                        class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6"-->
<!--                    >-->
<!--                        {{ __('Cancel') }}-->
<!--                    </a>-->
                    <progress-button
                        dusk="update-button"
                        @click.native="openModel"
                        :disabled="isWorking"
                        :processing="submittedViaUpdateResource"
                    >
                        {{ __('Update :resource', { resource: singularName }) }}
                    </progress-button>

                    <button v-if="MyTravel" type="button" @click="sync" v-permission = "'sync data to mytravel'"
                        class="btn bg-green-600 hover:bg-green-500 text-white ml-2 py-2 px-2">
                        {{ __('Sync Room Images to MyTravel') }}</button>
                </div>
            </form>
        </card>

        <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" ref="confirmModal" :title="__('Select Units You Wish To Apply This Update')" class="updateCategoryModal">
            <ul class="list-reset confirmModalSave">

                <div class="errors"
                style="color: red;
                    line-height: 2;
                    margin-bottom: 10px;
                "
            >

                    <p v-if="validationErrors.errors.translations_name_en">{{validationErrors.errors.translations_name_en[0]}}</p>
                    <p v-if="validationErrors.errors.translations_name_ar">{{validationErrors.errors.translations_name_ar[0]}}</p>
                    <p v-if="validationErrors.errors.sunday_day_price">{{validationErrors.errors.sunday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.monday_day_price">{{validationErrors.errors.monday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.tuesday_day_price">{{validationErrors.errors.tuesday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.wednesday_day_price">{{validationErrors.errors.wednesday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.thursday_day_price">{{validationErrors.errors.thursday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.friday_day_price">{{validationErrors.errors.friday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.saturday_day_price">{{validationErrors.errors.saturday_day_price[0]}}</p>
                    <p v-if="validationErrors.errors.month_price">{{validationErrors.errors.month_price[0]}}</p>
                </div>
                <li class="checkalllabel">

                 <label for="checkall">{{ __('Check All') }}<input type="checkbox" name="checkall" id="checkall" v-model="allSelected" @click="selectAll"><span class="checkmark"></span></label>
                </li>

                <li class="flex items-center" v-for="unit in units">
                  <label :for="unit.id">{{ unit.name + ' ' + unit.unit_number }}<input type="checkbox" :name="unit.id" :value="unit.id" v-model="selected_units"><span class="checkmark"></span></label>
                </li>
            </ul>

            <div class="flex w-full justify-end border-t-2 p-y1 mt-2">
              <progress-button
                class="mr-3"
                dusk="update-and-continue-editing-button"
                @click.native="updateAndContinueEditing"
                :disabled="isWorking"
                :processing="submittedViaUpdateAndContinueEditing"
            >
                {{ __('Update & Continue Editing') }}
            </progress-button>

            <progress-button
                class="mr-3"
                dusk="update-and-continue-editing-button"
                @click.native="updateAndContinueEditing"
                :disabled="isWorking"
                :processing="submittedViaUpdateResource"
            >
                {{ __('Update :resource', { resource: singularName }) }}
            </progress-button>
            </div>
        </sweet-modal>
    </div>
</template>

<script>
    // import Update from '@nova/views/Update'
    import Update from './core/Update'
    import { Errors, InteractsWithResourceInformation } from 'laravel-nova'
    export default {
        name: "update-unit-category-button",
        mixins: [
            Update,
            InteractsWithResourceInformation
        ],
        data: () => ({
            crumbs : [],
            relationResponse: null,
            loading: true,
            submittedViaUpdateAndContinueEditing: false,
            submittedViaUpdateResource: false,
            fields: [],
            units: [],
            selected_units: [],
            validationErrors: new Errors(),
            lastRetrievedAt: null,
            allSelected : false,
            category_name : null,
            MyTravel : false
        }),
        methods: {
            /**
             * Get the available fields for the resource.
             */
            async getFields() {
                this.loading = true

                this.fields = []

                const { data: fields } = await Nova.request()
                    .get(`/nova-api/${this.resourceName}/${this.resourceId}/update-fields`, {
                        params: {
                            editing: true,
                            editMode: 'update',
                            viaResource: this.viaResource,
                            viaResourceId: this.viaResourceId,
                            viaRelationship: this.viaRelationship,
                        },
                    })
                    .catch(error => {
                        if (error.response.status == 404) {
                            this.$router.push({ name: '404' })
                            return
                        }
                    })

                this.fields = fields

                this.loading = false
            },

            sync() {
                let id = this.resourceId;
                console.log(id);
                axios.post(window.APP_URL + '/api/syncRoomImages/' + id)
                    .then(response => {

                        this.$toasted.show(
                            this.__('Room Images Synced Successfully'),
                            { type: 'success' }
                        )
                    })
                    .catch(error => {

                        this.$toasted.show(
                            this.__('Room Images Sync Failed'),
                            { type: 'error' }
                        )

                    });
            },

            /**
             * Update the resource using the provided data.
             */
            async updateResource() {
                this.submittedViaUpdateResource = true

                try {
                    const {
                        data: { redirect },
                    } = await this.updateRequest()
                    const response_update = await this.updateUnitsRequest();
                    this.$refs.confirmModal.close();

                    this.submittedViaUpdateResource = false

                    this.$toasted.show(
                        this.__('The :resource was updated!', {
                            resource: this.resourceInformation.singularLabel.toLowerCase(),
                        }),
                        { type: 'success' }
                    )

                    this.$router.push({ path: redirect })
                } catch (error) {
                    this.submittedViaUpdateResource = false

                    if (error.response.status == 422) {
                        this.validationErrors = new Errors(error.response.data.errors)
                    }

                    if (error.response.status == 409) {
                        this.$toasted.show(
                            this.__(
                                'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
                            ),
                            { type: 'error' }
                        )
                    }
                }
            },

            /**
             * Update the resource and reset the form
             */
            async updateAndContinueEditing() {
                this.submittedViaUpdateAndContinueEditing = true
                try {
                    const response = await this.updateRequest()
                    const response_update = await this.updateUnitsRequest();
                    this.$refs.confirmModal.close();


                    this.submittedViaUpdateAndContinueEditing = false

                    this.$toasted.show(
                        this.__('The :resource was updated!', {
                            resource: this.resourceInformation.singularLabel.toLowerCase(),
                        }),
                        { type: 'success' }
                    )

                    // if(response.data.resource){
                    //      this.updateFeaturedUnitCategoriesAccordingToCategoryStatusIfCategoryWasFound(response.data.resource.id , response.data.resource.team_id ,  response.data.resource.status);
                    // }

                    // Reset the form by refetching the fields
                    this.getFields()

                    this.validationErrors = new Errors()

                    this.updateLastRetrievedAtTimestamp()
                } catch (error) {
                    this.submittedViaUpdateAndContinueEditing = false

                    if (error.response.status == 422) {
                        this.validationErrors = new Errors(error.response.data.errors)
                    }

                    if (error.response.status == 409) {
                        this.$toasted.show(
                            this.__(
                                'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
                            ),
                            { type: 'error' }
                        )
                    }
                }
            },
            async updateWithoutContinueEditing() {
                try {
                    const response = await this.updateRequest()
                    const response_update = await this.updateUnitsRequest();
                    this.$refs.confirmModal.close();

                    this.$toasted.show(
                        this.__('The :resource was updated!', {
                            resource: this.resourceInformation.singularLabel.toLowerCase(),
                        }),
                        { type: 'success' }
                    )

                    // Reset the form by refetching the fields
                    this.getFields()

                    this.validationErrors = new Errors()

                    this.updateLastRetrievedAtTimestamp()
                } catch (error) {
                    this.submittedViaUpdateAndContinueEditing = false

                    if (error.response.status == 422) {
                        this.validationErrors = new Errors(error.response.data.errors)
                        // this.$toasted.show(
                        //     this.__(
                        //         'Prices are not valid , please make sure that prices are greater than or equal 1'
                        //     ),
                        //     { type: 'error' }
                        // )
                    }

                    if (error.response.status == 409) {
                        this.$toasted.show(
                            this.__(
                                'Another user has updated this resource since this page was loaded. Please refresh the page and try again.'
                            ),
                            { type: 'error' }
                        )
                    }
                }
            },

            /**
             * Send an update request for this resource
             */
             async updateRequest() {
                try {
                    const response = await Nova.request().post(
                        `/nova-api/${this.resourceName}/${this.resourceId}`,
                        this.updateResourceFormData,
                        {
                            params: {
                                viaResource: this.viaResource,
                                viaResourceId: this.viaResourceId,
                                viaRelationship: this.viaRelationship,
                            },
                        }
                    );
                    // Handle the response if needed
                    return response.data; // or perform additional actions based on the response
                } catch (error) {
                    if(error.response.data.errors.images){
                        this.$toasted.error(error.response.data.errors.images);
                    }
                    if(error.response.data.errors.main){
                        this.$toasted.error(error.response.data.errors.main);
                    }

                }
            },

            updateUnitsRequest() {
                let units = this.selected_units;
                this.selected_units = [];
                return Nova.request().post(
                    '/nova-vendor/units/update_selected_units', {
                        'selected_units': units
                    }
                )
            },
            /**
             * Update the last retrieved at timestamp to the current UNIX timestamp.
             */
            updateLastRetrievedAtTimestamp() {
                this.lastRetrievedAt = Math.floor(new Date().getTime() / 1000)
            },
            async getUnits() {
                await axios.get('/nova-vendor/units/get_units/' + this.resourceId)
                    .then((res) => {
                        this.units = res.data.data;
                    }).catch(error => {
                        state.error()
                    })
            },
            openModel() {
                this.allSelected = false;
                this.validationErrors = new Errors();
                this.$refs.confirmModal.open();
            },
            selectAll() {
                this.selected_units = [];

                if (!this.allSelected) {
                    for (let unit in this.units) {
                        this.selected_units.push(this.units[unit].id.toString());
                    }
                }
            },
            updateFeaturedUnitCategoriesAccordingToCategoryStatusIfCategoryWasFound(id,team_id,status){
                axios.post('/apidata/updateFeaturedCategories' , {
                    id : id,
                    team_id : team_id,
                    status : status
                })
                .then(res => {
                    console.log(res.data);
                })
            }
        },
        mounted() {
            this.getUnits();
            this.team = Spark.state.currentTeam;
            this.MyTravel = this.team.enable_mytravel;
        },
        created(){
            axios.get('/nova-vendor/units/get-unit-category-name/' + this.resourceId)
                .then(response => {
                    this.category_name = response.data.name;

                    this.crumbs = [
                        {
                            text: 'Home',
                            to: '/dashboards/main',
                        },
                        {
                            text: 'Unit Categories',
                            to: '/resources/unit-categories',
                        },
                        {
                            text: this.category_name,
                            to: `/resources/unit-categories/${this.resourceId}`,
                        },
                        {
                            text: 'Edit',
                            to: '#',
                        }
                    ];
                }) ;
        }
    }
</script>

<style lang="scss">
    .updateCategoryModal {
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
        }
    }

</style>
<style scoped>


    .button-custom-theme {
        align-items: baseline;
        padding-left: 2rem;
        padding-right: 2rem;
        padding-top: 1rem;
        padding-bottom: 1rem;
        display: flex;
        direction: ltr!important;
    }
.sweet-modal-overlay.theme-light.sweet-modal-clickable.is-visible {
  background: rgba(0, 0, 0, 0.8);
}
ul.confirmModalSave {
  padding: 10px;
  display: flex;
  flex-wrap: wrap;
}
ul.confirmModalSave li.checkalllabel {
  display: block;
  width: 100%;
  font-size: 16px;
  line-height: 18px;
  margin: 0 auto 10px;
}
ul.confirmModalSave li {
  width: 33.3333%;
  font-size: 14px;
  margin: 0 0 5px;
  line-height: 18px;
}
ul.confirmModalSave li label {
  display: block;
  position: relative;
  padding-right: 23px;
  margin-bottom: 10px;
  cursor: pointer;
  font-size: 14px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
ul.confirmModalSave li label input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  right: 0;
  z-index: 9;
  width: 100%;
  height: 100%;
}
ul.confirmModalSave li label .checkmark {
  position: absolute;
  top: 0;
  right: 0;
  height: 18px;
  width: 18px;
  background-color: #dddddd;
  border: 1px solid #bbb;
  border-radius: 3px;
}
ul.confirmModalSave li label:hover input ~ .checkmark {background-color: #ccc;}
ul.confirmModalSave li label input:checked ~ .checkmark {
  background-color: #2196F3;
  border-color: #1287E4;
}
ul.confirmModalSave li label .checkmark:after {
  content: "";
  position: absolute;
  display: none;
}
ul.confirmModalSave li label input:checked ~ .checkmark:after {display: block;}
ul.confirmModalSave li label .checkmark:after {
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}
</style>
