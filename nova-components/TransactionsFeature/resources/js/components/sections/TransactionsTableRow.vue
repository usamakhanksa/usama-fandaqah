<template>
    <tr :dusk="resource['id'].value + '-row'">
        <!-- Resource Selection Checkbox -->
        <td
            :class="{
                'w-16': shouldShowCheckboxes,
                'w-8': !shouldShowCheckboxes,
            }"
        >
            <checkbox
                :data-testid="`${testId}-checkbox`"
                :dusk="`${resource['id'].value}-checkbox`"
                v-if="shouldShowCheckboxes"
                :checked="checked"
                @input="toggleSelection"
            />
        </td>

        <!-- Fields -->
        <td v-for="(field,index) in resource.fields" :key="index">
            <component
                :is="'index-' + field.component"
                :class="`text-${field.textAlign}`"
                :resource-name="resourceName"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :field="field"
            />
        </td>

        <td class="td-fit text-right pr-6 ">
            <!-- View Resource Link -->
<!--            <span v-if="resource.authorizedToView">-->
<!--                <router-link-->
<!--                    :data-testid="`${testId}-view-button`"-->
<!--                    :dusk="`${resource['id'].value}-view-button`"-->
<!--                    class="cursor-pointer text-70 hover:text-primary mr-3"-->
<!--                    :to="{-->
<!--                        name: 'reservation',-->
<!--                        params: {-->
<!--                            id: resource['id'].value,-->
<!--                        },-->
<!--                    }"-->
<!--                    :title="__('View')"-->
<!--                >-->
<!--                    <icon type="view" width="22" height="18" view-box="0 0 22 16" />-->
<!--                </router-link>-->
<!--            </span>-->


                <!-- Edit Resource Link -->
<!--            <span v-if="resource.authorizedToUpdate">-->
<!--                &lt;!&ndash; Edit Pivot Button &ndash;&gt;-->
<!--                <router-link-->
<!--                    v-if="relationshipType == 'belongsToMany' || relationshipType == 'morphToMany'"-->
<!--                    class="cursor-pointer text-70 hover:text-primary mr-3"-->
<!--                    :dusk="`${resource['id'].value}-edit-attached-button`"-->
<!--                    :to="{-->
<!--                        name: 'edit-attached',-->
<!--                        params: {-->
<!--                            resourceName: viaResource,-->
<!--                            resourceId: viaResourceId,-->
<!--                            relatedResourceName: resourceName,-->
<!--                            relatedResourceId: resource['id'].value,-->
<!--                        },-->
<!--                        query: {-->
<!--                            viaRelationship: viaRelationship,-->
<!--                        },-->
<!--                    }"-->
<!--                    :title="__('Edit Attached')"-->
<!--                >-->
<!--                    <icon type="edit" />-->
<!--                </router-link>-->

<!--                &lt;!&ndash; Edit Resource Link &ndash;&gt;-->
<!--                <router-link-->
<!--                    v-else-->
<!--                    class="cursor-pointer text-70 hover:text-primary mr-3"-->
<!--                    :dusk="`${resource['id'].value}-edit-button`"-->
<!--                    :to="{-->
<!--                        name: 'edit',-->
<!--                        params: {-->
<!--                            resourceName: resourceName,-->
<!--                            resourceId: resource['id'].value,-->
<!--                        },-->
<!--                        query: {-->
<!--                            viaResource: viaResource,-->
<!--                            viaResourceId: viaResourceId,-->
<!--                            viaRelationship: viaRelationship,-->
<!--                        },-->
<!--                    }"-->
<!--                    :title="__('Edit')"-->
<!--                >-->
<!--                    <icon type="edit" />-->
<!--                </router-link>-->
<!--            </span>-->

            <!-- Delete Resource Link -->
            <button
                :data-testid="`${testId}-delete-button`"
                :dusk="`${resource['id'].value}-delete-button`"
                class="appearance-none cursor-pointer text-70 hover:text-primary btn_display"
                v-if="resource.authorizedToDelete && (!resource.softDeleted || viaManyToMany)"
                @click.prevent="openDeleteModal"
                :title="__(viaManyToMany ? 'Detach' : 'Delete')"
            >
                <icon />
            </button>

            <a class="display:none;"  :href="'/home/reservation/sub-invoice/' + transaction_hash_id  " ref="reservation_href" target="_blank"></a>
            <a class="display:none;"  :href="'/home/team/sub-invoice/' + transaction_hash_id  " ref="team_href" target="_blank"></a>
            <button class="cursor-pointer text-70 hover:text-primary mr-3" @click="hashTransactionId(resource['id'].value)"  target="_blank"  :title="__('Print Transaction')" >

                <svg xmlns="http://www.w3.org/2000/svg" width="19.339" height="19.339" viewBox="0 0 19.339 19.339"><path d="M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z" transform="translate(-2 -2)" fill="#333b45"/></svg>

            </button>

            <!-- Restore Resource Link -->
<!--            <button-->
<!--                :dusk="`${resource['id'].value}-restore-button`"-->
<!--                class="appearance-none cursor-pointer text-70 hover:text-primary mr-3"-->
<!--                v-if="resource.authorizedToRestore && resource.softDeleted && !viaManyToMany"-->
<!--                @click.prevent="openRestoreModal"-->
<!--                :title="__('Restore')"-->
<!--            >-->
<!--                <icon type="restore" with="20" height="21" />-->
<!--            </button>-->

            <portal to="modals">
                <transition name="fade">
                    <delete-resource-modal
                        v-if="deleteModalOpen"
                        @confirm="confirmDelete"
                        @close="closeDeleteModal"
                        :mode="viaManyToMany ? 'detach' : 'delete'"
                    >
                        <div slot-scope="{ uppercaseMode, mode }" class="p-8">
<!--                            <heading :level="2" class="mb-6">{{-->
<!--                                __(uppercaseMode + ' Resource')-->
<!--                            }}</heading>-->
                            <p class="text-80 leading-normal">
                                {{ __('Are you sure you want to delete this transaction?') }}
                            </p>
                        </div>
                    </delete-resource-modal>
                </transition>

                <transition name="fade">
                    <restore-resource-modal
                        v-if="restoreModalOpen"
                        @confirm="confirmRestore"
                        @close="closeRestoreModal"
                    >
                        <div class="p-8">
                            <heading :level="2" class="mb-6">{{ __('Restore Resource') }}</heading>
                            <p class="text-80 leading-normal">
                                {{ __('Are you sure you want to restore this resource?') }}
                            </p>
                        </div>
                    </restore-resource-modal>
                </transition>
            </portal>

        </td>
    </tr>
</template>

<script>
export default {
    props: [
        'testId',
        'deleteResource',
        'restoreResource',
        'resource',
        'resourcesSelected',
        'resourceName',
        'relationshipType',
        'viaRelationship',
        'viaResource',
        'viaResourceId',
        'viaManyToMany',
        'checked',
        'actionsAreAvailable',
        'shouldShowCheckboxes',
        'updateSelectionStatus',
    ],

    data: () => ({
        deleteModalOpen: false,
        restoreModalOpen: false,
        transaction_hash_id : null

    }),

    methods: {
        /**
         * Select the resource in the parent component
         */
        toggleSelection() {
            this.updateSelectionStatus(this.resource)
        },

        openDeleteModal() {
            this.deleteModalOpen = true
        },

        confirmDelete() {
            this.deleteResource(this.resource)
            this.closeDeleteModal()
        },

        closeDeleteModal() {
            this.deleteModalOpen = false
        },

        openRestoreModal() {
            this.restoreModalOpen = true
        },

        confirmRestore() {
            this.restoreResource(this.resource)
            this.closeRestoreModal()
        },

        closeRestoreModal() {
            this.restoreModalOpen = false
        },

        hashTransactionId(transaction_id) {
            Nova.request().post('/nova-vendor/calender/hashTransactionId', {
                transaction_id: transaction_id
            })
                .then(response => {
                    this.transaction_hash_id = response.data;

                    Nova.request().get('/nova-vendor/financial-management/transaction?id=' + transaction_id)
                        .then((res)=>{


                            let payable_type = res.data.transaction.payable_type;
                            if(payable_type == 'App\\Reservation'){
                                // then the print of reservation transaction sub invoice will be here
                                // Trigger a click event through ref
                                this.triggerPrintHref('reservation');

                            }else{
                                // then the print of team transaction sub invoice will be here
                                this.triggerPrintHref('team');
                            }
                        });

                });
        },
        triggerPrintHref(type){
            if(type == 'reservation'){
                this.$refs.reservation_href.click();
            }else{
                this.$refs.team_href.click();
            }
        }

    }


    ,
    created(){
        let params = {
            transaction_id: this.resource['id'].value
        }
        // Nova.request().post('/nova-vendor/transactions-feature/hashTransactionId', params)
        // .then(response => {
        //     this.transaction_hash_id = response.data ;
        // });
    }
}
</script>

<style scoped>
    .btn_display{
            display: inline-block;
    }
</style>
