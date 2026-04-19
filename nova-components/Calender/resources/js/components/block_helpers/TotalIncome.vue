<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Income')}}</span>
      <p>{{ totalIncome }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى الايراد
export default {
    name : 'total-income' ,
    props: [
        'loading',
        'totalIncome'
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
