<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Reservation Amount')}}</span>
      <p>{{ totalAmount }} {{currency}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى تكلفة الحجوزات
export default {
    name : 'total-amount' ,
    props: [
        'loading',
        'totalAmount'
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
