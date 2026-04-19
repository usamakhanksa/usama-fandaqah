<template>
  <div>
    <div class="upgrade_subscription_msg" role="alert">
      {{ __('Welcome to Fandaqah Real Estate Management System, your subscription has ended')}} ..
    </div>
      <upgrade ref="upgrade"></upgrade>
  </div>
</template>

<script>
    import Upgrade from "./Upgrade";

    export default {
        name: "SubscriptionEnd",
        components: {
            Upgrade
        },
        methods: {
            open() {
                this.$refs.upgrade.$refs.modal.open()
            },
            gotoTechnicalSupport(){
                this.$router.push('/techincal-support')
            }
        }
    }
</script>

<style lang="scss" scoped>
.upgrade_subscription_msg {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  padding: 10px;
  border-radius: 4px;
  font-size: 15px;
  color: #856404;
  margin: 0 auto 15px;
  a, button {
    color: #533f03;
    font-weight: bold;
    outline: none;
    &:hover {text-decoration: underline;}
  } /* a */
} /* upgrade_subscription_msg */
</style>
