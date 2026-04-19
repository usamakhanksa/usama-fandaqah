<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Taxes')}}</span>
      <p>{{ totalTaxes }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى الخدمات
export default {
    name : 'total-tax' ,
    props: [
        'loading',
        'totalTaxes'
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
