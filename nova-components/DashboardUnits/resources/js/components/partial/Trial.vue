<template>
  <div>
    <div class="trial_subscription_msg" role="alert">{{__('trial_subscription', {'plan': plan(), 'days': remainingDays()})}} ..</div>
    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Contact Us')" overlay-theme="dark" ref="modal" style="z-index: 2147483647;">
      <div class="text-center text-xl pt-4 text-black">
        {{__('We are happy to help and upgrade your subscription, contact us now')}}
        <p>info@fandaqah.com</p>
      </div>
      <button type="button" onclick="location.href='https://api.whatsapp.com/send?phone=966561187386';" style="direction: ltr; background: rgb(45, 213, 76) none repeat scroll 0% 0%; line-height: 40px; height: 40px;font-size: 21px;border: none;" class="shadow btn table mx-auto btn-danger my-4 px-4">
        <svg style="float: left; height: 25px; width: 25px; margin: 7px 10px 0 auto;" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 34 34"><path d="M17,0A17,17,0,0,0,2.574,26C1.9,28.351.69,32.8.677,32.847a.654.654,0,0,0,.823.8l6.739-2.073A17,17,0,1,0,17,0Zm0,32.692a15.653,15.653,0,0,1-8.324-2.387.654.654,0,0,0-.347-.1.665.665,0,0,0-.192.029L2.253,32.046c.432-1.578,1.209-4.4,1.659-5.97a.656.656,0,0,0-.08-.537A15.694,15.694,0,1,1,17,32.692Z" fill="#fff"/><path d="M31.338,24.389c-1.207-.67-2.235-1.342-2.985-1.833-.573-.374-.987-.644-1.29-.8a1.485,1.485,0,0,0-1.735.123.654.654,0,0,0-.082.1,9.191,9.191,0,0,1-2.368,2.653,15.744,15.744,0,0,1-4.02-2.6c-1.848-1.54-3.01-3.014-3.181-4.019,1.185-1.22,1.612-1.988,1.612-2.861,0-.9-2.1-4.659-2.48-5.039s-1.24-.44-2.552-.179a.66.66,0,0,0-.334.179c-.159.159-3.877,3.949-2.11,8.543,1.939,5.042,6.917,10.9,13.263,11.854a13.752,13.752,0,0,0,2.03.162c3.733,0,5.937-1.879,6.557-5.6A.651.651,0,0,0,31.338,24.389Zm-8.066,4.829c-6.711-1.006-10.938-7.655-12.237-11.03-1.288-3.348,1.087-6.333,1.684-7.007a4.932,4.932,0,0,1,1.209-.084,23.482,23.482,0,0,1,2.055,4.056c0,.343-.112.821-1.443,2.153a.651.651,0,0,0-.192.462c0,3.424,7.221,8.173,8.5,8.173,1.112,0,2.562-1.869,3.388-3.087a.62.62,0,0,1,.243.076c.235.118.643.384,1.159.722.681.445,1.586,1.036,2.655,1.649C29.808,27.635,28.452,30,23.272,29.218Z" transform="translate(-3.233 -3.384)" fill="#fff"/></svg>
        +996536372095
      </button>
    </sweet-modal>
      <upgrade ref="upgrade"></upgrade>
  </div>
</template>

<script>
    import Upgrade from "./Upgrade";
  export default {
    name: "Trial",
        components: {
            Upgrade
        },
    methods: {
      remainingDays(){
        let date = Spark.state.currentTeam.trial_ends_at;
        if (date == null) {
            date = Spark.state.currentTeam.ends_at;
        }
        return moment(date).diff(moment(), 'days');
      },
            plan() {
                if (Spark.state.currentTeam.current_billing_plan == null || Spark.state.currentTeam.current_billing_plan == 'trial') {
                    return Nova.app.__('Trial');
                }
                return Nova.app.__(Spark.state.currentTeam.current_billing_plan);
            },
            open() {
                this.$refs.modal.open()
            },
            open() {
                this.$refs.upgrade.$refs.modal.open()
            },
            upgradeClick(){

            }
        }
    }
</script>

<style lang="scss" scoped>
.trial_subscription_msg {
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
} /* trial_subscription_msg */
</style>
