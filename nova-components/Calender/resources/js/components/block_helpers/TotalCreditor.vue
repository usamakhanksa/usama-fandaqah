<template>
  <div>
    <loading-view :loading="loading">
      <span>{{__('Total Creditor')}}</span>
      <p class="totalCreditor">{{ totalCreditor }} {{__(currency)}}</p>
    </loading-view>
  </div>
</template>

<script>
// اجمالى الدائن
export default {
    name : 'total-creditor' ,
    props: [
        'loading',
        'totalCreditor'
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
