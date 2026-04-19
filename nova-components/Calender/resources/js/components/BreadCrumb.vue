<template>

    <div class="flex w-full mb-2">
        <nav>
        <ul class="breadcrumb">
            <li
            v-for="(breadcrumb,index) in breadcrumbList"
            :key="index"
            @click="routeTo(index)"
            :class="'breadcrumbs__item'"
            >
                <router-link :to="breadcrumb.link" :class="currentLink !== breadcrumb.link ? '':'router-link-exact-active router-link-active'" >{{__(breadcrumb.name)}}</router-link>
<!--                <a href="{{}}">{{__(breadcrumb.name)}}</a>-->
            </li>
        </ul>
    </nav>
    </div>
</template>

<script>
    export default {
        name: "bread-crumb",
        data(){
            return {
                breadcrumbList : [],
                currentLink : ''
            }
        },
        mounted() {
            this.updateList();
            this.currentLink = this.$route.path ;

        },
        watch:{
            '$route' () {
                this.updateList();
            }
        },
        methods:{
            updateList(){
                this.breadcrumbList = this.$route.meta.breadcrumb;
            },
            routeTo(pRouteTo){
                if(this.breadcrumbList[pRouteTo].link) this.$router.push(this.breadcrumbList[pRouteTo].link)
            }
        }
    }
</script>


<style scoped>
    nav {display: block;}
    .breadcrumb {
        padding-left: 0;
        padding-bottom: 12px;
        margin-top: 0;
        margin-bottom: 0;
        list-style: none;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--white-50);
    }
    .breadcrumb {
        padding-bottom: 2px !important;
        border: 0px !important;
        padding: 0px !important;
    }
    .breadcrumb .breadcrumbs__item {
        display: inline-block;
        position: relative;
    }
    .breadcrumb .breadcrumbs__item a:after {
        content: "/";
        line-height: 1;
        text-align: center;
        position: absolute;
        top: 50%;
        right: 0;
        -webkit-transform: translateY(-50%);
        transform: translateY(-50%);
        color: var(--black);
    }
    .breadcrumb .breadcrumbs__item a:after {
        content: "/";
        line-height: 1;
        text-align: center;
        position: absolute;
        top: 50%;
        right: unset !important;
        left: 0 !important;
        -webkit-transform: translateY(-50%) !important;
        transform: translateY(-50%);
        color: var(--black);
    }
    .breadcrumb .breadcrumbs__item a {
        margin-right: 12px;
        margin-left: 8px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 400!important;
    }
    .breadcrumb .breadcrumbs__item a {
        color: var(--primary-dark) !important;
    }
    .breadcrumb .breadcrumbs__item a {
        margin-right: 4px !important;
        margin-left: 12px !important;
        text-decoration: none;
        font-weight: 400 !important;
    }
    .breadcrumb .breadcrumbs__item .router-link-exact-active a, .breadcrumb .breadcrumbs__item:last-of-type a {
        color: var(--black);
        cursor: default;
    }
    .breadcrumb .breadcrumbs__item a {
        margin-right: 12px;
        margin-left: 8px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 400!important;
    }
    .breadcrumbs__item:last-of-type a {
        color: #7c858e !important;
    }
    .breadcrumb .breadcrumbs__item .router-link-exact-active a:after, .breadcrumb .breadcrumbs__item:last-of-type a:after {
        content: "";
    }
</style>
