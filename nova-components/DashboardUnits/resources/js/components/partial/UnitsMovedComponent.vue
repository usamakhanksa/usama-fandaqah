<template>
  <div class="unit_moved_msg" role="alert" v-if="display">
    <!-- {{__("Unit status has been moved to ")}} <a href="#" @click="goToUnitsHousing">{{__('Units Housing')}}</a> -->
    {{__("Unit status has been moved to ")}} <router-link exact tag="a" to="/units/housing">{{__('Units Housing')}}</router-link>
  </div><!-- unit_moved_msg -->
</template>

<script>
    export default {
        name: "units-moved-component",
        props : ['display'],
        data: () => {
            return {
                loading: true,
                units: 0,
                categories: 0,
            }
        },
        methods: {
            goToUnitsHousing() {
                this.$router.push({path: '/units/housing'})
            },


        },
    }
</script>

<style lang="scss" scoped>
.unit_moved_msg {
  background: #cce5ff;
  border: 1px solid #b8daff;
  padding: 10px;
  border-radius: 4px;
  font-size: 15px;
  color: #004085;
  margin: 0 auto 15px;
  a {
    color: #002752;
    font-weight: bold;
    outline: none;
    &:hover {text-decoration: underline;}
  } /* a */
} /* unit_moved_msg */
</style>
