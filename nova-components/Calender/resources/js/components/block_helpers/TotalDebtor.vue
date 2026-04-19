<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Debtor')}}</span>
      <p class="totalDebtor">{{ totalDebtor }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى المدين
export default {
    name : 'total-debtor' ,
    props: [
        'loading',
        'totalDebtor'
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
