<template>
    <div>
        <div class="mb-3">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>


        <div class="flex mr-2 ml-2 border rounded border-50">
            <div>
                <div class="flex ">
                    <div class="p-2 text-1xl">
                        <h3><i class="far fa-calendar-alt"></i></h3>
                    </div>
                    <div class="c-date">
                        <vcc-date-picker
                            @input="update"
                            :input-props='{
                                    class: " date-field",
                                    style: " background-color: transparent !important;\n"+
                                    "        border: 0px solid !important;\n"+
                                    "        box-shadow: none !important;\n"+
                                    "        font-size: 22px !important;\n"+
                                    "        margin-top: 2px !important;\n"+
                                    "        padding: 0px !important;\n"+
                                    "        font-weight: bold !important;",
                                    readonly: true
                                  }'
                            mode='single'
                            v-model='datePicker'
                            show-caps
                        >
                        </vcc-date-picker>
                    </div>
                </div>
            </div>
        </div>

        <div class="m-11" v-if="loading">
            <loader class="text-60" width="40"/>
        </div>

        <!--<h3 class="mb-1">Templates !!!</h3>-->
        <section v-if="!loading" class=" flex flex-wrap overflow-hidden">
            <unit v-for="unit in units"
                  v-bind:data="unit"
                  v-bind:key="unit.id"
                  :unit="unit" :date="date" :price_type="price_type"></unit>
        </section>
    </div>
</template>

<script>
    export default {
        name: "units",
        props: {},
        data: () => {
            return {
                price_type: 'Day',
                loading: true,
                date: null,
                datePicker: null,
                units: [
                    {
                        id: 105,
                    },
                    {
                        id: 106,
                    }
                ],
                crumbs: [],
            }
        },
        mounted() {

            this.date = this.$route.params.date;
            this.datePicker = moment(this.date).toDate()

            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Calender',
                    to: '/calender',
                },
                {
                    text: this.date,
                    to: 'date',
                }
            ]
        },
        methods: {
            getUnits() {
                Nova.request()
                    .get('/nova-vendor/calender/units/' + this.date)

                    .then(response => {
                        this.units = response.data.items
                        this.loading = false;
                    }).catch(err => {
                    this.loading = false;
                    this.$router.go(-1)
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            update() {
                this.loading = true;

                if (this.datePicker === null) {
                    this.datePicker = moment(this.date).toDate()
                    return;
                }
                this.date = moment(String(this.datePicker)).format('YYYY-MM-DD')
                // console.log("this.date a", this.date)
                this.crumbs = [
                    {
                        text: 'Home',
                        to: '/dashboards/main',
                    },
                    {
                        text: 'Calender',
                        to: '/calender',
                    },
                    {
                        text: this.date,
                        to: 'date',
                    }
                ]
                this.$router.replace({name: 'unit-list', params: {date: this.date}})
                this.getUnits();
            }
        }
    }
</script>

<style scoped>

    .pricing .card {
        border: none;
        border-radius: 1rem;
        transition: all 0.2s;
        box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
    }

    .pricing hr {
        margin: 1.5rem 0;
    }

    .pricing .card-title {
        margin: 0.5rem 0;
        font-size: 0.9rem;
        letter-spacing: .1rem;
        font-weight: bold;
    }

    .pricing .card-price {
        font-size: large;
        margin: 0;
        margin-bottom: 5px;
    }

    .pricing .card-price .period {
        font-size: 0.8rem;
    }

    .pricing ul li {
        margin-bottom: 1rem;
    }

    .pricing .text-muted {
        opacity: 0.7;
    }

    /* Hover Effects on Card */


        .pricing .card:hover {
            margin-top: -.1rem;
            margin-bottom: .1rem;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.3);
        }

        .pricing .card:hover .btn {
            opacity: 1;
        }


    .card-body {
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        padding: 10px;
    }

    .card-name {
        font-size: small;
        min-height: 17px;
    }

    .card-top-avalable {
        border-top: 5px solid #28a745 !important;
    }

    .card-top-not-avalable {
        border-top: 5px solid #dc3545 !important;
    }

    .card-top-not-avalable-m {
        border-top: 5px solid #dca236 !important;
    }

    .card-top-not-avalable-c {
        border-top: 5px solid #6d6d69 !important;
    }
</style>
