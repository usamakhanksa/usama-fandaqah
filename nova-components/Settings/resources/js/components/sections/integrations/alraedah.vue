<template>
  <div class="integration_col">
    <div class="integration_item">
      <div>
        <div class="imgthumb cursor-pointer">
          <img src="/images/alraedah-logo.png" />
        </div><!-- imgthumb -->
        <div class="name">
          <p>{{__('Alraedah Finance')}}</p>
        </div>
        <div class="desc">{{__("Register to apply for alraedah finance services online")}}</div>
        <div class="date_integration" style="display:flex;justify-content: space-between;">
          <button @click="gotoIntegrationForm" style="width: 48%;" class="connect" > {{__('Submit Request')}}</button>
          <button @click="openAlraedahInfoModal" style="width: 48%;" class="more" > {{__('Know More')}}</button>
        </div>
      </div>
    </div><!-- integration_item -->

     <!-- Qoyod Info -->
      <sweet-modal ref="alraedahInfoModal" :enable-mobile-fullscreen="false" :pulse-on-block="false" class="alraedah_Info_Modal" blocking overlay-theme="dark"  :title="__('Financing Solutions')">
         <div class="financing-contacnt">
            <div class="pos-img">
                <img src="/images/hotel.jpeg" alt="image" />
            </div>

            <div class="pos-info">
                <h2>تمويل نقاط البيع (العاجل)</h2>
                <p>
                    تحتاج العديد من الشركات والمنشآت بعد انطلاقها لحلول تمويلية عاجلة وغير معقدة؛ لتحسم الصفقات وتُسرع العمليات وتعزز موقفها في الأسواق، ولأننا ندرك حاجة تلك المنشآت لهذه الحلول السريعة فإننا ابتكرنا تمويل نقاط البيع (العاجل)
                    للمشروعات الصغيرة والمتوسطة القائمة لستة أشهر فأكثر وفي خلال أسبوع عمل:
                </p>

                <h3 class="text-capitalize">كيف يعمل ؟</h3>

                <div class="editor-text">
                    <p>-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; يبدأ مبلغ التمويل من 50,000 ريال سعودي حتى 125,000 ريال سعودي.</p>
                    <p>-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; مبلغ تمويل يصل إلى 15% من مبيعات نقاط البيع.</p>
                    <p>-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; يتم الحصول على مبلغ التمويل خلال 7 أيام عمل من اكتمال رفع الطلب.</p>
                    <p>-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; بدون الحاجة لأقساط شهرية ثابتة.</p>
                </div>

            </div>
            <!--post info-->
        </div>

      </sweet-modal>
  </div><!-- integration_col -->
</template>

<script>
    export default {
        name: "integration-alraedah",
        props: ['is_admin'],
        data() {
            return {
            }
        },
        methods: {
            gotoIntegrationForm(){
                if(!this.is_admin){
                    this.$toasted.show(this.__('Request should be submitted from the main user account'), {type: 'error'});
                    return false;
                }
                this.$router.push('/alraedah-finance/create-prospect');
            },
            openAlraedahInfoModal(){
                this.$refs.alraedahInfoModal.open();
            }
        },
    }
</script>

<style lang="scss">


    .financing-contacnt{
        display: flex;
        margin-bottom: 25px;
        @media (min-width: 320px) and (max-width: 767px) {
             flex-direction: column;
        } /* Mobile */
        .pos-img{
            width: 44%;
            @media (min-width: 320px) and (max-width: 767px) {
                width: 100%;
            } /* Mobile */
            img {
                    height: 100%;
                    width: 100%;
                    object-fit: cover;
                    border-radius: 2%;
                    filter: blur(2px);
            }
        }

        .pos-info{
            width: 56%;
            padding: 65px 35px;
            border-radius: 10px;
            @media (min-width: 320px) and (max-width: 767px) {
                width: 100%;
                padding: 10px;
            } /* Mobile */
            h2{
                letter-spacing: 0 !important;
                font-size: 30px;
                margin-bottom: 30px;
                color: #1d428a;
                line-height: 1.29;

                @media (min-width: 320px) and (max-width: 767px) {
                    font-size: 26px;
                } /* Mobile */
            }
            h3{

                font-size: 24px;
                line-height: 1.29;
                color: #4b82bc;
                margin-bottom: 10px;
            }

            p{
                -webkit-text-stroke: 1px rgba(0, 0, 0, 0);
                font-size: 18px;
                letter-spacing: 0.26px;
                color: #707070;
                margin-bottom: 10px;
            }
        }
    }
    /* Animation styles */
    @keyframes spinner {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }


    /* Optional — create your own variations! */
    .spinner-large {
        width: 5rem;
        height: 5rem;
        border-width: 6px;
    }

    .spinner-slow {
        animation: spinner 1s linear infinite;
    }

    .spinner-blue {
        border-top-color: #09d;
        border-left-color: #09d;
    }
    .spinner-light {
        border-top-color: #ffffff;
        border-left-color: #ffffff;
    }

.qoyod_link {
    font-weight: bold;
    color: blue;
    font-size: 17px;
}

.alraedah_Info_Modal {
    .sweet-modal {
        max-width : 100%;
    }
  .sweet-content {
    overflow: auto;
    max-height: 500px;
    display: block !important;
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: #ccc #f5f5f5;
    &::-webkit-scrollbar {width: 6px;}
    &::-webkit-scrollbar-track {background: #f5f5f5;}
    &::-webkit-scrollbar-thumb {background: #ccc;}
    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
  } /* sweet-content */
  span {
    display: block;
    margin: 20px auto;
    font-weight: bold;
    font-size: 15px;
    p {
      display: block;
      font-size: 15px;
      font-weight: normal;
      margin: 5px auto 0;
    } /* p */
    ul {
      list-style: unset;
      padding: 10px 30px;
      li {
        margin: 0 auto 10px;
        font-size: 15px;
        color: #000;
        font-weight: normal;
      } /* li */
    } /* ul */
  } /* span */
  img {
    display: block;
    margin: 15px auto;
    max-width: 100%;
    max-height: none;
    height: auto;
    width: auto;
    border: 1px solid #ddd;
  } /* img */
} /* alraedah_Info_Modal */
</style>
