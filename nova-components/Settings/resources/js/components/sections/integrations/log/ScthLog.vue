<template>
    <div>
    <sweet-modal ref="scthLogModal" :enable-mobile-fullscreen="false"  blocking overlay-theme="dark" :title="__('SCTH Log')" @close="closeLog()">
        <div>
            <div class="scthlog_table" v-if="!logLoading && data.total > 0">
                <table class="table-auto">
                    <thead>
                    <tr>
                        <th class="px-4 py-2">{{__('Status')}}</th>
                        <th class="px-4 py-2">{{__('Type')}}</th>
                        <th class="px-4 py-2">{{__('Date')}}</th>
                        <th class="px-4 py-2">{{__('Reservation')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in data.data" :key="item.id">
                        <td class="border px-4 py-2" :class="'badge-'+jobStatusClass(item.content.status)">{{
                            __(item.content.status) }}
                        </td>
                        <td class="border px-4 py-2">{{ __(getName(item.content.name)) }}</td>
                        <td class="border px-4 py-2">{{ item.created_at }}</td>
                        <td class="border px-4 py-2" @click="viewReservation(item.content.data.reservation)">{{__('View Reservation')}}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="w-full flex flex-wrap mt-8 justify-center" v-if="data.total > data.per_page">
                <pagination
                    :page-count="data.last_page"
                    :page-range="3"
                    :margin-pages="2"
                    :click-handler="getLog"
                    :prev-text="__('Previous')"
                    :next-text="__('Next')"
                    :container-class="'pagination w-full flex justify-center'"
                    :page-class="'page-item'"
                    :page-link-class="'page-link'"
                    :prev-link-class="'page-link'"
                    :next-link-class="'page-link'"
                    :prev-class="'page-item'"
                    :next-class="'page-item'"
                    :first-last-button="true"
                    :first-button-text="__('First')"
                    :last-button-text="__('Last')">
                </pagination>
            </div>
            <div v-if="data.total < 1">
                <div class="w-full border border-gray-400 rounded bg-gray-300 text-gray-800 text-base p-3 my-3">{{__('No Data')}}</div>
            </div>
        </div>
    </sweet-modal>
    </div>
</template>

<script>
    import Pagination from './Pagination';

    export default {
        name: "scth-log",
        components: {
            Pagination
        },
        data() {
            return {
                logLoading: false,
                data: Array,
            }
        },
        methods: {
            jobStatusClass(status) {
                if (status === 'pending') return 'secondary';
                if (status === 'processed') return 'success';
                if (status === 'failed') return 'danger';
            },
            closeLog() {
                this.$refs.scthLogModal.close()
            },
            viewReservation(reservation) {
                this.$router.push({name: 'reservation', params: {
                        id: reservation.split(':')[1]}
                });
            },
            getName(name) {
                return name.replace("App\\Jobs\\SCTH\\", "");
            },
            openLog() {
                this.$refs.scthLogModal.open()
            },
            getLog(page = 1) {

                this.logLoading = true;
                Nova.request()
                    .get('/nova-vendor/settings/integrations-log/scth?page=' + page).then(response => {
                    this.data = response.data;
                    this.logLoading = false;
                })
                    .catch(error => {
                        this.$toasted.error(error, {
                            duration: 3000
                        });
                        this.loading = false;
                    });
            }
        },
        mounted() {
            this.getLog()
        }
    }
</script>

<style scoped>
.scthlog_table table {
  width: 100%;
  border: 1px solid #ddd;
}
.scthlog_table table thead th {
  background: #3b73bd;
  border: 1px solid #2c64ae;
  color: #fff;
  font-family: Dubai-Medium;
  font-weight: normal;
  font-size: 15px;
}
.scthlog_table table tbody td {
  text-align: center;
  font-size: 15px;
  padding: 10px;
  font-family: Dubai-Regular;
  background: #fafafa;
  border: 1px solid #ddd;
  color: #000;
}
.scthlog_table table tbody td.badge-secondary {color: gray;}
.scthlog_table table tbody td.badge-success {color: green;}
.scthlog_table table tbody td.badge-danger {color: red;}
.pagination {
  display: flex;
  padding-left: 0;
  list-style: none;
  border-radius: .25rem;
  margin: 20px auto 5px;
}
.page-link {
  position: relative;
  display: block;
  padding: .5rem .75rem;
  margin-left: -1px;
  line-height: 1.25;
  color: #007bff;
  background-color: #fff;
  border: 1px solid #dee2e6;
  font-size: 14px;
}
.page-item:first-child .page-link {
  margin-left: 0;
  border-top-left-radius: .25rem;
  border-bottom-left-radius: .25rem;
}
.page-item.disabled .page-link {
  color: #6c757d;
  pointer-events: none;
  cursor: auto;
  background-color: #fff;
  border-color: #dee2e6;
}
.page-item.active .page-link {
  z-index: 1;
  color: #fff;
  background-color: #007bff;
  border-color: #007bff;
}
html:lang(ar) ul.pagination li.page-item:first-child a {
  border-radius: 0 .25rem .25rem 0 !important;
  margin: 0 0 0 -1px !important;
}
html:lang(ar) ul.pagination li.page-item:last-child a {
  border-radius: .25rem 0 0 .25rem !important;
  margin: 0 -1px 0 0 !important;
}
</style>
