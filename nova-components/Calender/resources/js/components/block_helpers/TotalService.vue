<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Services')}}</span>
      <p>{{ totalServices }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى الخدمات
export default {
    name : 'total-service' ,
    props: [
        'loading',
        'totalServices'
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
