<template>
    <div>
        <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>

        <div id="index_unit_category_page">

            <!-- Warning alert -->
            <div class="flex bg-orange-100 p-4" v-if="showLimitAlert">
                <div class="flex justify-between w-full">
                    <div class="text-orange-600">
                        <p class="mb-2 font-bold">
                            {{__('Warning alert')}}
                        </p>
                        <p class="text-normal">
                            {{__('The maximum allowed is 5 categories')}}
                        </p>
                    </div>
                </div>
            </div>

            <div class="block">
                <div class="title">{{__('Distinguished Index Unit Categories')}} <p class="helper" v-if="hasRelatedHotels"><i class="fas fa-info-circle"></i> {{__('Categories displayed below are from you tenant and other tenants related to your tenant')}}</p></div>
                <div class="content relative">

                    <!-- Loader -->
                    <loading :active="isActive"
                             :loader="'spinner'"
                             :color="'#7e7d7f'"
                             :opacity="0.7"
                             :is-full-page="false">
                    </loading>


                    <div v-if="unitCategoriesCollection.length">


                            <div class="col_item" v-for="(category,index) in unitCategoriesCollection" :key="index" v-if="category.units_count">
                                <div class="top_row">
                                    <label>

                                        <input type="checkbox" name="email"  :checked="collector.includes(category.id)" @change="handleCollectorState(category)">
                                        <span class="checkmark"></span>
                                        <p>{{category.name[locale]}} / {{category.team_name}}</p>
                                    </label>
                                </div><!-- end top_row -->
                            </div><!-- end col_item -->


                    </div>



                </div>
            </div><!-- end block -->



            <div class="bg-30 flex p-4 justify-between">
                <button type="submit" @click.prvent="storeFeaturedUnitCategories" :disabled="collector.length > 5" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8">
                    {{ __('Save') }}
                </button>
                <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
            </div>

        </div>
    </div>
</template>

<script>

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "Distinguished Index Unit Categories",
        components : {
          Loading
        },
        data() {
            return {
                team: Object,
                crumbs: [],
                locale : null,
                isActive : false,
                unitCategoriesCollection : [],
                featuredUnitCategories : [],
                hasRelatedHotels : false,
                checked : false,
                collector: [],
                collectorData : [],
                showLimitAlert : false,
            }
        },
        mounted() {
            this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: 'Settings',
                    to: '/settings',
                },
                {
                    text: 'Website Settings',
                    to: '/settings/website',
                },
                {
                    text: 'Distinguished Index Unit Categories',
                    to: '#',
                }
            ];


        },
        methods: {


            handleCollectorState(category){

                if(this.collector.includes(category.id)){
                    this.collector = this.collector.filter(item => item != category.id);
                    this.collectorData = this.collectorData.filter(item => item.id != category.id)
                }else{
                    this.collector.push(category.id);
                    this.collectorData.push(category)
                }

                if(this.collector.length  > 5){
                    this.showLimitAlert = false;
                    this.$toasted.error(Nova.app.__('The maximum allowed is 5 categories'), {
                        duration: 3000
                    });
                }else{
                    this.showLimitAlert = false;
                }
            },

            getSettings(){

                let self = this;
                self.isActive = true;
                axios.get(`/nova-vendor/settings/get-related-teams-with-settings/${self.team.id}`)
                    .then((response) => {

                        self.featuredUnitCategories = response.data.featured_unit_categories;
                        self.hasRelatedHotels = response.data.has_related_hotels;
                        self.isActive = false;
                        self.unitCategoriesCollection = response.data.categories;




                        if(self.featuredUnitCategories){
                            $.each(self.featuredUnitCategories , function(index , category){
                                self.handleCollectorState(category);
                            });
                        }

                    });




            },
            storeFeaturedUnitCategories(){


                // if(!this.collectorData.length){
                //     this.$toasted.error(Nova.app.__('Please make sure to select at least one category'), {
                //         duration: 3000
                //     });
                //     return false ;
                // }


                axios.post(`/nova-vendor/settings/store-featured-unit-categories/${this.team.id}` , {data : this.collectorData })
                    .then((response) => {

                        this.$toasted.success(Nova.app.__('Unit categories has been added to distinguished index categories successfully'), {
                            duration: 3000
                        })
                        this.$router.push({path: '/settings/website'});
                    })
            },
            goBack() {
                this.$router.push({path: '/settings/website'})
            }
        },
        created() {
            this.team = Spark.state.currentTeam;
            this.locale = Nova.config.local;
            this.getSettings();
        },
    }
</script>

<style scoped lang="scss">

    #index_unit_category_page {
        background: #ffffff;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1),0 1px 2px 0 rgba(0,0,0,0.06);
        .block {
            .title {
                font-size: 17px;
                background: #F6FBFF;
                padding: 10px;
                color: #000;
                border-bottom: 1px solid #E3E7EB;
                .helper{
                    font-size: 13px;
                    color: gray;
                    i{
                        color: #3585c3;
                    }
                }
            } /* title */

            .content {
                width: 100%;
                padding: 0;
                .col_item {
                    padding: 20px;
                    border-bottom: 1px solid #eee;
                    width: 100%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        padding: 10px;
                    }
                    .top_row {
                        display: flex;
                        flex-wrap: wrap;
                        @media (min-width: 320px) and (max-width: 767px) {
                            display: block;
                        }
                        span {
                            display: block;
                            width: 25%;
                            padding: 0 10px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 100%;
                                margin: 0 auto 10px;
                                padding: 0;
                            }
                            @media (min-width: 768px) and (max-width: 991px) {
                                width: 40%;
                            }
                        } /* span */
                        label {
                            display: block;
                            width: 80%;
                            position: relative;
                            padding-right: 30px;
                            cursor: pointer;
                            color: #7E8790;
                            line-height: 40px;
                            -webkit-user-select: none;
                            -moz-user-select: none;
                            -ms-user-select: none;
                            user-select: none;
                            -webkit-transition: all 0.2s ease-in-out;
                            -moz-transition: all 0.2s ease-in-out;
                            -o-transition: all 0.2s ease-in-out;
                            transition: all 0.2s ease-in-out;
                            [dir="ltr"] & {
                                padding: 0 0 0 30px;
                            } /* rtl */
                            @media (min-width: 320px) and (max-width: 767px) {
                                display: inline-block;
                                width: auto;
                                margin: 0 0 0 20px;
                                [dir="ltr"] & {
                                    margin: 0 20px 0 0;
                                } /* rtl */
                            }
                            &:hover {
                                .checkmark {background: #e8e8e8;}
                                p {color: #6C7180;}
                            } /* hover */
                            input {
                                position: absolute;
                                opacity: 0;
                                cursor: pointer;
                                height: 0;
                                width: 0;
                                &:checked ~ {
                                    .checkmark {
                                        background: #0A80D8;
                                        &::after {
                                            opacity: 1;
                                            visibility: visible;
                                            -webkit-transform: scale(1);
                                            -moz-transform: scale(1);
                                            -o-transform: scale(1);
                                            transform: scale(1);
                                        } /* after */
                                    } /* checkmark */
                                    p {color: #0A80D8;}
                                } /* checked */
                            } /* input */
                            .checkmark {
                                position: absolute;
                                top: -1px;
                                right: 0;
                                height: 20px;
                                width: 20px;
                                background-color: #fcfcfc;
                                border: 1px solid #e8e8e8;
                                -webkit-transition: all 0.2s ease-in-out;
                                -moz-transition: all 0.2s ease-in-out;
                                -o-transition: all 0.2s ease-in-out;
                                transition: all 0.2s ease-in-out;
                                [dir="ltr"] & {
                                    right: auto;
                                    left: 0;
                                } /* rtl */
                                &::after {
                                    content: "";
                                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22.681' height='16.867' viewBox='0 0 22.681 16.867'%3E%3Cpath d='M143.914,64.664a1.147,1.147,0,0,0-1.622,0L128.526,78.392,123.559,73a1.147,1.147,0,0,0-1.688,1.554l5.775,6.272a1.146,1.146,0,0,0,.82.37h.025a1.149,1.149,0,0,0,.81-.335l14.611-14.572A1.147,1.147,0,0,0,143.914,64.664Z' transform='translate(-121.568 -64.327)' fill='%23fff'/%3E%3C/svg%3E");
                                    background-size: 13px;
                                    background-repeat: no-repeat;
                                    background-position: center 4px;
                                    position: absolute;
                                    top: 0;
                                    right: 0;
                                    width: 20px;
                                    height: 20px;
                                    line-height: 18px;
                                    text-align: center;
                                    font-size: 11px;
                                    color: #ffffff;
                                    opacity: 0;
                                    visibility: hidden;
                                    -webkit-transform: scale(0);
                                    -moz-transform: scale(0);
                                    -o-transform: scale(0);
                                    transform: scale(0);
                                    -webkit-transition: all 0.2s ease-in-out;
                                    -moz-transition: all 0.2s ease-in-out;
                                    -o-transition: all 0.2s ease-in-out;
                                    transition: all 0.2s ease-in-out;
                                    [dir="ltr"] & {
                                        right: auto;
                                        left: 0;
                                    } /* rtl */
                                } /* after */
                            } /* checkmark */
                            p {
                                display: block;
                                line-height: 20px;
                                font-size: 16px;
                                color: #000;
                                -webkit-transition: all 0.2s ease-in-out;
                                -moz-transition: all 0.2s ease-in-out;
                                -o-transition: all 0.2s ease-in-out;
                                transition: all 0.2s ease-in-out;
                            } /* p */
                        } /* label */
                    } /* top_row */
                    .bottom_row {
                        width: 40%;
                        margin: 20px 25% 0 0;
                        [dir="ltr"] & {
                            margin: 20px 0 0 25%;
                        } /* rtl */
                        @media (min-width: 320px) and (max-width: 767px) {
                            width: auto;
                            margin: 15px auto 0;
                        }
                        @media (min-width: 768px) and (max-width: 991px) {
                            width: 60%;
                            margin: 20px 40% 0 0;
                            [dir="ltr"] & {
                                margin: 20px 0 0 40%;
                            } /* rtl */
                        }
                        textarea {
                            width: 100%;
                            border: 1px solid #BBB;
                            padding: 10px;
                            font-size: 15px;
                            text-align: initial;
                            border-radius: 4px;
                            white-space: normal;
                            line-height: 25px;
                            height: 90px;
                            background: #ffffff;
                            color: #000;
                            [dir="ltr"] & {
                                text-align: left;
                            } /* rtl */
                            @media (min-width: 320px) and (max-width: 767px) {
                                padding: 5px;
                                font-size: 14px;
                            }
                        } /* textarea */
                        .smalltxt {
                            display: flex;
                            justify-content: space-between;
                            margin: 5px auto 0;
                            color: #6E6E6E;
                            font-size: 15px;
                        } /* smalltxt */
                        .another_choose {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            flex-wrap: wrap;
                            margin: 10px auto 0;
                            label {
                                display: block;
                                width: 33.3333%;
                                position: relative;
                                padding-right: 30px;
                                cursor: pointer;
                                color: #7E8790;
                                line-height: 40px;
                                margin: 0 0 15px;
                                -webkit-user-select: none;
                                -moz-user-select: none;
                                -ms-user-select: none;
                                user-select: none;
                                -webkit-transition: all 0.2s ease-in-out;
                                -moz-transition: all 0.2s ease-in-out;
                                -o-transition: all 0.2s ease-in-out;
                                transition: all 0.2s ease-in-out;
                                [dir="ltr"] & {
                                    padding: 0 0 0 30px;
                                } /* rtl */
                                &:hover {
                                    .checkmark {background: #e8e8e8;}
                                    p {color: #6C7180;}
                                } /* hover */
                                input {
                                    position: absolute;
                                    opacity: 0;
                                    cursor: pointer;
                                    height: 0;
                                    width: 0;
                                    &:checked ~ {
                                        .checkmark {
                                            background: #0A80D8;
                                            &::after {
                                                opacity: 1;
                                                visibility: visible;
                                                -webkit-transform: scale(1);
                                                -moz-transform: scale(1);
                                                -o-transform: scale(1);
                                                transform: scale(1);
                                            } /* after */
                                        } /* checkmark */
                                        p {color: #0A80D8;}
                                    } /* checked */
                                } /* input */
                                .checkmark {
                                    position: absolute;
                                    top: -1px;
                                    right: 0;
                                    height: 20px;
                                    width: 20px;
                                    background-color: #fcfcfc;
                                    border: 1px solid #e8e8e8;
                                    -webkit-transition: all 0.2s ease-in-out;
                                    -moz-transition: all 0.2s ease-in-out;
                                    -o-transition: all 0.2s ease-in-out;
                                    transition: all 0.2s ease-in-out;
                                    [dir="ltr"] & {
                                        right: auto;
                                        left: 0;
                                    } /* rtl */
                                    &::after {
                                        content: "";
                                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22.681' height='16.867' viewBox='0 0 22.681 16.867'%3E%3Cpath d='M143.914,64.664a1.147,1.147,0,0,0-1.622,0L128.526,78.392,123.559,73a1.147,1.147,0,0,0-1.688,1.554l5.775,6.272a1.146,1.146,0,0,0,.82.37h.025a1.149,1.149,0,0,0,.81-.335l14.611-14.572A1.147,1.147,0,0,0,143.914,64.664Z' transform='translate(-121.568 -64.327)' fill='%23fff'/%3E%3C/svg%3E");
                                        background-size: 13px;
                                        background-repeat: no-repeat;
                                        background-position: center 4px;
                                        position: absolute;
                                        top: 0;
                                        right: 0;
                                        width: 20px;
                                        height: 20px;
                                        line-height: 18px;
                                        text-align: center;
                                        font-size: 11px;
                                        color: #ffffff;
                                        opacity: 0;
                                        visibility: hidden;
                                        -webkit-transform: scale(0);
                                        -moz-transform: scale(0);
                                        -o-transform: scale(0);
                                        transform: scale(0);
                                        -webkit-transition: all 0.2s ease-in-out;
                                        -moz-transition: all 0.2s ease-in-out;
                                        -o-transition: all 0.2s ease-in-out;
                                        transition: all 0.2s ease-in-out;
                                        [dir="ltr"] & {
                                            right: auto;
                                            left: 0;
                                        } /* rtl */
                                    } /* after */
                                } /* checkmark */
                                p {
                                    display: block;
                                    line-height: 20px;
                                    font-size: 16px;
                                    color: #000;
                                    -webkit-transition: all 0.2s ease-in-out;
                                    -moz-transition: all 0.2s ease-in-out;
                                    -o-transition: all 0.2s ease-in-out;
                                    transition: all 0.2s ease-in-out;
                                } /* p */
                            } /* label */
                        } /* another_choose */
                    } /* bottom_row */
                } /* bottom_row */
                .add_area {
                    width: 40%;
                    @media (min-width: 320px) and (max-width: 767px) {
                        width: auto;
                    }
                    @media (min-width: 768px) and (max-width: 991px) {
                        width: 60%;
                    }
                    .col_add {
                        display: flex;
                        justify-content: space-between;
                        margin: 0 auto 15px;
                        input {
                            display: block;
                            width: 75%;
                            height: 40px;
                            border: 1px solid #cecece !important;
                            padding: 0 10px;
                            line-height: 40px;
                            font-size: 16px;
                            color: #000;
                            border-radius: 4px;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 70%;
                            }
                        } /* input */
                        .vue-tel-input {
                            display: flex;
                            width: 75%;
                            height: 40px;
                            border: 1px solid #cecece !important;
                            line-height: 40px;
                            font-size: 16px;
                            color: #000;
                            border-radius: 4px;
                            padding: 0;
                            text-align: right;
                            align-items: center;
                            box-shadow: none;
                            [dir="ltr"] & {
                                text-align: left;
                            } /* rtl */
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 70%;
                            }
                            .dropdown {
                                padding: 0;
                                width: 70px;
                                background: #fafafa;
                                height: 38px;
                                border-left: 1px solid #cecece;
                                border-radius: 0 4px 4px 0;
                                [dir="ltr"] & {
                                    border-right: 1px solid #cecece;
                                    border-left: none;
                                    border-radius: 4px 0 0 4px;
                                } /* rtl */
                                @media (min-width: 320px) and (max-width: 767px) {
                                    width: 25%;
                                    padding: 0;
                                }
                                span.selection {
                                    display: flex;
                                    height: 40px;
                                    justify-content: center;
                                    align-items: center;
                                    width: auto;
                                    margin: 0 auto;
                                    .iti-flag {
                                        margin: 0;
                                    } /* iti-flag */
                                    span.dropdown-arrow {
                                        width: auto;
                                        margin: 0;
                                    } /* dropdown-arrow */
                                } /* selection */
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
                                    &::-webkit-scrollbar {width: 6px;}
                                    &::-webkit-scrollbar-track {background: #f5f5f5;}
                                    &::-webkit-scrollbar-thumb {background: #ccc;}
                                    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
                                    [dir="ltr"] & {
                                        left: 0;
                                        right: auto;
                                    } /* rtl */
                                    @media (min-width: 320px) and (max-width: 767px) {
                                        width: 319px;
                                    }
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
                                        } /* rtl */
                                        .iti-flag {
                                            margin: 0;
                                        } /* iti-flag */
                                        strong {
                                            display: block;
                                            font-weight: normal;
                                            font-size: 15px;
                                            margin: 0 5px;
                                        } /* strong */
                                        span {
                                            direction: ltr;
                                            color: #666;
                                        } /* span */
                                    } /* li */
                                } /* ul */
                            } /* dropdown */
                            input {
                                width: 75%;
                                padding: 0 10px;
                                height: 38px;
                                border: none !important;
                            } /* input */
                        } /* vue-tel-input */
                        .mail_active {
                            display: block;
                            width: 75%;
                            height: 40px;
                            border: 1px solid #F7F7F7;
                            padding: 0 10px;
                            line-height: 40px;
                            font-size: 16px;
                            color: #000;
                            border-radius: 4px;
                            background: #F7F7F7;
                            cursor: not-allowed;
                            direction: ltr;
                            text-align: right;
                            [dir="ltr"] & {
                                text-align: left;
                            } /* rtl */
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 70%;
                            }
                        } /* mail_active */
                        button {
                            width: 20%;
                            height: 40px;
                            border-radius: 4px;
                            line-height: 40px;
                            font-size: 17px;
                            display: block;
                            color: #ffffff;
                            @media (min-width: 320px) and (max-width: 767px) {
                                width: 27%;
                            }
                            &.add_mail {
                                background: #4099DE;
                                &:hover {background: #2C85CA;}
                            } /* add_mail */
                            &.clear_mail {
                                background: #DE4040;
                                &:hover {background: #CF3131;}
                            } /* add_mail */
                        } /* button */
                    } /* col_add */
                } /* add_area */
            } /* content */
        } /* block */
    } /* index_unit_category_page */



</style>
