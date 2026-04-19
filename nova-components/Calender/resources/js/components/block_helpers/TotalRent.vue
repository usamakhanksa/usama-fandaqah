<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Rent')}}</span>
      <p>{{ totalRent }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى الايجار
export default {
    name : 'total-rent' ,
    props: [
        'loading',
        'totalRent'
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
