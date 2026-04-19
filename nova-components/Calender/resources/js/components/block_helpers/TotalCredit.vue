<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('The Total Credit')}}</span>
      <p>{{ theTotalCredit }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى الرصيد
export default {
    name : 'total-credit' ,
    props: [
        'loading',
        'theTotalCredit'
    ],
     data(){
        return{
            currency :Nova.app.currentTeam.currency,

        }
    }
}
</script>

<style scoped>
</style>
