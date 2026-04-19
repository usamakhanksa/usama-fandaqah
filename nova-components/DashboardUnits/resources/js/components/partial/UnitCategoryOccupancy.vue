<template>
    <div class="occupancy_rooms">
        <div class="inside_area">
            <div class="title">
                <span>{{ __('Occupancy types of units for the day')}}<b>{{ today }}</b></span>
            </div><!-- title -->
            <div class="content_area">
                <ul>
                    <li v-for="(item,i) in data" :key="i">
                        <span>{{item.uc_name}}<i>( {{item.total_units}} )</i></span>
                        <div class="bar_side">
<!--                            style="width: 65.7%;"-->
                            <div class="outbar"><div class="insidebar" v-bind:style="{ width: ( (item.total_reservations/item.total_units) * 100 ).toFixed(2)  +'%' }" ></div></div>
                            <p>{{( (item.total_reservations/item.total_units) * 100 ).toFixed(2)}}%</p>
                        </div><!-- bar_side -->
                    </li>
                </ul>
            </div><!-- content_area -->
        </div><!-- inside_area -->
    </div><!-- occupancy_rooms -->
</template>

<script>
    export default {
        name: "unit-category-occupancy",
        data(){
            return {
                data : [],
                team_id : Nova.app.user.current_team_id,
                locale : Nova.config.local
            }
        },
        methods : {
            getUnitCategoryOccupancyData(){
                axios.post('/nova-vendor/DashboardUnits/get-unit-category-occupancy-data', {
                    team_id :this.team_id ,
                    locale : this.locale
                })
                .then(response => {
                    if(response.data.success){
                        this.data = response.data.data;
                    } 
                })
            }
        },
        mounted(){
            this.getUnitCategoryOccupancyData();
        }
    }
</script>

<style lang="scss" scoped>
    .occupancy_rooms {
        width: 33.3333%;
        padding: 0 10px;
        margin: 15px 0;
        align-self: stretch;
        @media (min-width: 320px) and (max-width: 767px) {
            width: 100%;
        } /* media */
        @media (min-width: 768px) and (max-width: 991px) {
            width: 33.33333%;
        } /* media */
        .inside_area {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            height: 100%;
            .title {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                margin: 0 0 15px;
                span {
                    display: block;
                    font-size: 15px;
                    margin: 0 0 5px;
                    b {
                        display: inline-block;
                        font-weight: normal;
                        margin: 0 5px 0 0;
                    } /* b */
                } /* span */
            } /* title */
            .content_area {
                max-height: 417px;
                overflow-y: auto;
                margin: 0 -10px;
                padding: 0 10px;
                scrollbar-width: thin;
                scrollbar-color: #ccc #f5f5f5;
                &::-webkit-scrollbar {width: 6px;}
                &::-webkit-scrollbar-track {background: #f5f5f5;}
                &::-webkit-scrollbar-thumb {background: #ccc;}
                &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
                ul {
                    li {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin: 0 auto 10px;
                        span {
                            display: flex;
                            align-items: center;
                            justify-content: flex-start;
                            font-size: 15px;
                            color: #000;
                            i {
                                display: block;
                                margin: 0 5px 0 0;
                                color: #777;
                                font-style: normal;
                                font-size: 14px;
                            } /* i */
                        } /* span */
                        .bar_side {
                            display: flex;
                            align-items: center;
                            justify-content: flex-end;
                            p {
                                margin: 0px 5px 0 0;
                                min-width: 32px;
                                font-size: 12px;
                                line-height: 1;
                                text-align: left;
                                font-weight: bold;
                                color: #4099de;
                            } /* p */
                            .outbar {
                                min-width: 150px;
                                background: #E5F0F8;
                                box-shadow: 0 0 5px 1px #C2CDD5 inset;
                                border-radius: 100px;
                                overflow: hidden;
                                position: relative;
                                height: 5px;
                                @media (min-width: 320px) and (max-width: 767px) {
                                    min-width: 100px;
                                }
                                .insidebar {
                                    height: 100%;
                                    background: #4099de;
                                    position: absolute;
                                    right: 0;
                                    top: 0;
                                    text-align: center;
                                    border-radius: 100px;
                                } /* insidebar */
                            } /* outbar */
                        } /* bar_side */
                    } /* li */
                } /* ul */
            } /* content_area */
        } /* inside_area */
    } /* occupancy_rooms */
</style>
