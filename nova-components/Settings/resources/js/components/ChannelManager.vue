<template>
    <div v-if="aiosell">


        <heading class="my-1">{{__('Channel manger')}}</heading>
        <div id="reports_page">

            <!-- Reports Done Through FandaqahApi -->

            <router-link :to="{ name : 'channel-availbility'  }" >
                <div class="report_item">
                    <div class="icon">
                       <svg xmlns="http://www.w3.org/2000/svg" width="49" height="49" viewBox="0 0 24 24" fill="none" stroke="#b5b5b5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 8l-5-5-5 5M12 4.2v10.3"/></svg>
                    </div><!-- icon -->
                    <div class="title">{{__('Avalibilty & Rates')}}</div>
                </div><!-- report_item -->
            </router-link>

            <router-link :to="{ name : 'channel-reservations' }">
                <div class="report_item">
                    <div class="icon">
                        <!-- Calendar with checkmark icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="49" height="49" viewBox="0 0 24 24" fill="none" stroke="#b5b5b5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                            <path d="M9 16l2 2l4 -4" />
                        </svg>
                    </div><!-- icon -->
                    <div class="title">{{ __('Reservations') }}</div>
                </div><!-- report_item -->
            </router-link>




        </div><!-- reports_page -->
    </div>
</template>

<script>

export default {
    name: 'channel-manager',
    data(){
        return {
            team_id : Nova.config.user.current_team_id,
            lang : Nova.config.local,
            aiosell : Nova.app.currentTeam.enable_aiosell

        }
    },
    computed:{

    },
    mounted() {


    }

}
</script>

<style lang="scss" scoped>
#reports_page {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(223px,1fr));
    margin: 20px -5px 0;
    .report_item {
        margin: 0 5px 10px;
        background: #ffffff;
        padding: 1rem;
        cursor: pointer;
        box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
        display: flex;
        align-items: center;
        border-radius: 5px;
        min-height: 72px;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        a {
            display: flex;
            align-items: center;
        }
        .icon {
            svg {
                fill: #000000;
                width: 30px;
                height: auto;
                path {
                    fill: #000000;
                    width: 30px;
                    height: auto;
                } /* path */
            } /* svg */
        } /* icon */
        .title {
            color: #000000;
            margin: 0 10px 0 0;
            font-size: 15px;
            line-height: 20px;
            [dir="ltr"] & {
                margin: 0 0 0 10px;
            } /* ltr */
        } /* title */
        .soon{
            color: #a6a6a9;
        }
        &:hover {
            background: #fafafa;
            border-color: #bbb;
            box-shadow: 0 0px 4px 1px rgba(0,0,0,0.1),0 0px 4px 1px rgba(0,0,0,0.1);
        } /* hover */
    } /* report_item */
    #Balady {
        .st0{fill:#07706D;}
        .st1{fill:#85BD48;}
    }
} /* reports_page */
</style>
