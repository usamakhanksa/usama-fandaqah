<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Receipts')}}</span>
      <p> {{ totalReceipts }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى المقبوضات
export default {
    name : 'total-receipts' ,
    props: [
        'loading',
        'totalReceipts'
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
