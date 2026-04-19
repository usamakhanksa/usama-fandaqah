<template>
    <div class="flex flex-wrap -mx-2 overflow-hidden">
        <div class="my-2 px-2 w-full overflow-hidden sm:w-1/2 md:w-1/2 lg:w-1/3 xl:w-1/4">
            <div class="setting flex bg-white items-center rounded-lg p-4 cursor shadow" @click="goTo('roles')">
                <div class="text-center icon_item">
                    <svg width="30" height="30" viewBox="-28 0 480 480" xmlns="http://www.w3.org/2000/svg"><path d="m400 120h-64v-96c0-13.601562-10.398438-24-24-24h-288c-13.601562 0-24 10.398438-24 24v432c0 13.601562 10.398438 24 24 24h243.199219l68.800781-68.800781v-51.199219h-16v40h-40c-13.601562 0-24 10.398438-24 24v40h-232c-4.800781 0-8-3.199219-8-8v-432c0-4.800781 3.199219-8 8-8h288c4.800781 0 8 3.199219 8 8v96h-64c-13.601562 0-24 10.398438-24 24v144c0 13.601562 10.398438 24 24 24h44.800781l27.199219 27.199219 27.199219-27.199219h44.800781c13.601562 0 24-10.398438 24-24v-144c0-13.601562-10.398438-24-24-24zm-120 296h28.800781l-36.800781 36.800781v-28.800781c0-4.800781 3.199219-8 8-8zm128-128c0 4.800781-3.199219 8-8 8h-51.199219l-20.800781 20.800781-20.800781-20.800781h-51.199219c-4.800781 0-8-3.199219-8-8v-144c0-4.800781 3.199219-8 8-8h144c4.800781 0 8 3.199219 8 8zm0 0"/><path d="m120 40h96v16h-96zm0 0"/><path d="m40 80h24v16h-24zm0 0"/><path d="m80 80h216v16h-216zm0 0"/><path d="m40 128h24v16h-24zm0 0"/><path d="m80 128h128v16h-128zm0 0"/><path d="m40 176h24v16h-24zm0 0"/><path d="m80 176h128v16h-128zm0 0"/><path d="m40 224h24v16h-24zm0 0"/><path d="m80 224h128v16h-128zm0 0"/><path d="m40 272h24v16h-24zm0 0"/><path d="m80 272h128v16h-128zm0 0"/><path d="m40 320h24v16h-24zm0 0"/><path d="m80 320h128v16h-128zm0 0"/><path d="m40 368h24v16h-24zm0 0"/><path d="m80 368h216v16h-216zm0 0"/><path d="m40 416h24v16h-24zm0 0"/><path d="m80 416h152v16h-152zm0 0"/><path d="m360 201.601562v-17.601562c0-17.601562-14.398438-32-32-32s-32 14.398438-32 32h16c0-8.800781 7.199219-16 16-16s16 7.199219 16 16v16h-40c-13.601562 0-24 10.398438-24 24v32c0 13.601562 10.398438 24 24 24h48c13.601562 0 24-10.398438 24-24v-32c0-10.398438-6.398438-19.199219-16-22.398438zm0 54.398438c0 4.800781-3.199219 8-8 8h-48c-4.800781 0-8-3.199219-8-8v-32c0-4.800781 3.199219-8 8-8h48c4.800781 0 8 3.199219 8 8zm0 0"/></svg>
                </div>
                <div :class="textAlgin" class=" w-5/6">
                    <div class="title">{{__('Roles & permissions')}} </div>
                    <div class="desc">{{__('Create and edit permissions for your team.')}}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        'card',
    ],
    data: () => {
        return {
            textAlign: '',
        }
    },
    mounted() {
        let lang = Nova.config.local ;
        lang == 'en' ? this.textAlign = 'text-left' : this.textAlign = 'text-right' ;
    },
    methods: {
        goTo(type) {
            if (Spark.state.currentTeam.current_billing_plan == 'trial' && Spark.state.currentTeam.id == 44){
                this.$toasted.show(Nova.app.__('Your account on the free plan must be upgraded to access to manage roles'), { type: 'error' })
                return;
            }
            this.$router.push('/resources/' + type)
        }
    }
}
</script>

<style>
.icon_item {margin: 0 0 0 10px;}
html:lang(en) .icon_item {margin: 0 10px 0 0;}
.icon_item svg, .icon_item svg path {
  fill: #000000;
  width: 30px;
  height: auto;
}
.title {
  font-size: 16px;
  color: #000000;
}
.desc {
  font-size: 14px;
  color: #555555;
}
.setting {
  border: 1px solid #ddd;
  cursor: pointer;
  text-decoration: none !important;
}
.setting:hover {
    background: #fafafa;
    border-color: #bbb;
}
.setting div.text-muted {
    font-size: 15px;
    margin: 4px auto 0;
}
</style>

