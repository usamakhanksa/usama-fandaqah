<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Cost')}}</span>
      <p>{{ totalCost }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى المصروفات
export default {
    name : 'total-cost' ,
    props: [
        'loading',
        'totalCost'
    ],
    data(){
        return {
            currency :Nova.app.currentTeam.currency,

        }
    },

}
</script>

<style scoped>
</style>
