<template>
  <div class="unit_col relative">
      <!-- Loader -->
      <loading :active.sync="pusherLoading"
               :loader="'spinner'"
               :color="'#7e7d7f'"
               :is-full-page="false">
      </loading>
    <div
      class="unit_item"
      v-bind:class="{
        'card-top-avalable': !reserved && unit.status != 3 && unit.status != 2 ,
        'card-top-not-avalable': reserved && !reservation.checked_in ,
        'card-top-not-avalable-and-checked-in': reserved && reservation.checked_in ,
        'card-online-reserved': reserved && awaitingPaymentOrConfirmation,
        'card-top-under-maintenance': (unit.status == 3 && !reservation) || (unit.status == 3 && bindNaturalUnitClass) ,
        'card-top-under-cleaning': (unit.status == 2  && !reservation) ||  (unit.status == 2 && bindNaturalUnitClass)
      }"
    >

      <!-- The Popover Data For Reserved Unit  -->

      <div class="info_reserved"  v-if="reserved && reservation.reservation_type == 'group'">
        <!-- <svg v-if="!reservation.customer_id" enable-background="new 0 0 91 91" height="27px" id="Layer_1" version="1.1" viewBox="0 0 91 91" width="27px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M45.864,34.287c0,10.729-8.646,19.431-19.306,19.431S7.261,45.016,7.261,34.287    c0-10.727,8.637-19.426,19.297-19.426S45.864,23.561,45.864,34.287z" fill="#E0F1F8"/><path d="M26.558,56.23c-12.025,0-21.81-9.844-21.81-21.943c0-12.098,9.784-21.939,21.81-21.939    c12.03,0,21.819,9.842,21.819,21.939C48.377,46.387,38.588,56.23,26.558,56.23z M26.558,17.373    c-9.255,0-16.784,7.588-16.784,16.914c0,9.328,7.529,16.918,16.784,16.918c9.26,0,16.793-7.59,16.793-16.918    C43.351,24.961,35.818,17.373,26.558,17.373z" fill="#454B53"/></g><path d="M50.492,77.43c-1.387,0-2.512-1.125-2.512-2.513V59.611c0-4.73-2.517-7.941-7.92-10.104   c-1.288-0.516-1.915-1.979-1.399-3.267s1.976-1.917,3.267-1.399c9.157,3.664,11.079,10.055,11.079,14.77v15.306   C53.006,76.305,51.881,77.43,50.492,77.43z" fill="#454B53"/><g><path d="M88.191,27.512c0,9.168-7.43,16.594-16.6,16.594c-9.164,0-16.59-7.426-16.59-16.594    c0-9.166,7.426-16.596,16.59-16.596C80.762,10.916,88.191,18.346,88.191,27.512z" fill="#FDE497"/><path d="M71.592,46.619c-10.533,0-19.102-8.572-19.102-19.107c0-10.537,8.568-19.109,19.102-19.109    c10.539,0,19.113,8.572,19.113,19.109C90.705,38.047,82.131,46.619,71.592,46.619z M71.592,13.428    c-7.762,0-14.076,6.318-14.076,14.084c0,7.765,6.314,14.082,14.076,14.082c7.768,0,14.088-6.317,14.088-14.082    C85.68,19.746,79.359,13.428,71.592,13.428z" fill="#454B53"/></g><path d="M2.933,77.428c-1.388,0-2.513-1.125-2.513-2.513V59.611c0-4.702,1.865-11.079,10.748-14.758   c1.278-0.533,2.752,0.078,3.282,1.359c0.531,1.283-0.078,2.752-1.36,3.283c-5.144,2.131-7.645,5.439-7.645,10.115v15.304   C5.446,76.303,4.321,77.428,2.933,77.428z" fill="#454B53"/><g><polygon fill="#9ABFDA" points="33.493,76.313 26.713,81.733 19.932,76.313 24.234,55.352 26.701,55.352 29.128,55.352   "/><path d="M26.713,84.246c-0.555,0-1.11-0.184-1.568-0.55l-6.781-5.421c-0.738-0.59-1.083-1.543-0.893-2.468    l4.302-20.961c0.239-1.169,1.268-2.008,2.461-2.008h4.895c1.19,0,2.217,0.835,2.46,2.001l4.364,20.961    c0.192,0.927-0.151,1.883-0.891,2.474l-6.779,5.421C27.824,84.063,27.268,84.246,26.713,84.246z M22.703,75.311l4.011,3.206    l4.005-3.203l-3.633-17.449h-0.803L22.703,75.311z" fill="#454B53"/></g><path d="M79.359,30.191H63.426c-1.389,0-2.514-1.125-2.514-2.514c0-1.387,1.125-2.512,2.514-2.512h15.934   c1.387,0,2.512,1.125,2.512,2.512C81.871,29.066,80.746,30.191,79.359,30.191z" fill="#454B53"/></g></svg> -->
        <svg  v-if="!reservation.customer_id" v-tooltip="{
                    targetClasses: ['it-has-a-tooltip'],
                    placement: 'top',
                    content: __('No Customer'),
                    classes: ['tooltip_reset']
                }"  enable-background="new 0 0 91 91" height="30px" id="Layer_1" version="1.1" viewBox="0 0 91 91" width="30px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M25.773,50.997c11.08,0,20.092-9.078,20.092-20.232c0-11.156-9.012-20.232-20.092-20.232    c-11.074,0-20.08,9.076-20.08,20.232C5.693,41.919,14.699,50.997,25.773,50.997z" fill="#647F94"/><path d="M43.023,48.44c-3.17,3.129-7.176,5.391-11.646,6.443l4.664,23.918c0.176,0.842-0.137,1.707-0.807,2.242    l-2.006,1.605c5.594-0.668,11.654-2.127,18.006-4.789V60.106C51.234,54.845,48.52,51.026,43.023,48.44z" fill="#647F94"/><polygon fill="#6EC4A7" points="24.941,55.739 20.613,78.36 25.959,82.636 31.295,78.362 26.9,55.739   "/><path d="M20.453,54.956c-4.576-1.02-8.676-3.313-11.908-6.5c-5.266,2.58-7.865,6.393-7.865,11.65v17.84    c2.531,1.176,9.049,3.826,18.115,4.795l-2.119-1.695c-0.666-0.533-0.982-1.398-0.809-2.238L20.453,54.956z" fill="#647F94"/></g><path d="M71.557,16.685c-10.502,0-19.045,8.545-19.045,19.051c0,10.504,8.543,19.051,19.045,19.051   c10.506,0,19.055-8.547,19.055-19.051C90.611,25.229,82.063,16.685,71.557,16.685z M79.301,38.407H63.416   c-1.383,0-2.506-1.121-2.506-2.506c0-1.383,1.123-2.504,2.506-2.504h15.885c1.383,0,2.504,1.121,2.504,2.504   C81.805,37.286,80.684,38.407,79.301,38.407z" fill="#6EC4A7"/></g></svg>
        <!-- <svg v-if="!reservation.customer_id"
            v-tooltip="{
                    targetClasses: ['it-has-a-tooltip'],
                    placement: 'top',
                    content: __('No Customer'),
                    classes: ['tooltip_reset']
                }"
            xmlns="http://www.w3.org/2000/svg" xml:space="preserve"  width="27" height="27" x="0px" y="0px" style="enable-background:new 0 0 432.116 432.116" version="1.1" viewBox="0 0 432.116 432.116"><path d="M184.005 209.972c23.96 0 47.357-14.538 65.881-40.936 16.347-23.296 26.106-52.099 26.106-77.048C275.993 41.266 234.727 0 184.005 0S92.017 41.266 92.017 91.988c0 24.949 9.759 53.752 26.107 77.048 18.524 26.398 41.922 40.936 65.881 40.936zm0-194.972c42.451 0 76.988 34.537 76.988 76.988 0 21.655-8.96 47.876-23.385 68.432-15.408 21.958-34.946 34.552-53.603 34.552-18.657 0-38.194-12.594-53.603-34.552-14.424-20.556-23.385-46.778-23.385-68.432 0-42.451 34.537-76.988 76.988-76.988zM222.689 391.718a1.121 1.121 0 0 0-.026-.055c-.38-.881-.915-1.706-1.604-2.375-2.816-2.897-7.785-2.9-10.6 0-.35.34-.66.72-.93 1.14-2 2.859-1.562 7.038.93 9.46 2.491 2.562 6.674 2.889 9.582.854 2.861-2.002 3.938-5.753 2.658-8.99.041.097.071.167-.01-.034z"/><path d="M310.861 237.118c-.727 0-1.452.012-2.176.028-15.062-18.626-34.3-33.831-55.87-44.1a7.5 7.5 0 0 0-8.52 1.462c-18.07 18.023-38.919 27.55-60.293 27.55-21.377 0-42.222-9.526-60.282-27.549a7.498 7.498 0 0 0-8.522-1.463c-26.963 12.838-49.783 32.917-65.992 58.067-16.652 25.836-25.454 55.811-25.454 86.684v.13a7.5 7.5 0 0 0 2.462 5.556c43.279 39.249 99.316 60.864 157.788 60.864 3.088 0 6.209-.061 9.277-.18a7.5 7.5 0 1 0-.582-14.988c-2.874.111-5.799.168-8.695.168-53.62 0-105.055-19.418-145.215-54.746.583-26.855 8.515-52.846 23.027-75.363 13.766-21.359 32.788-38.684 55.228-50.357 20.046 18.456 43.095 28.175 66.961 28.175 23.862 0 46.914-9.72 66.968-28.175 14.824 7.723 28.305 18.052 39.688 30.346-44.099 9.33-77.296 48.553-77.296 95.389 0 53.762 43.738 97.5 97.5 97.5s97.5-43.738 97.5-97.5-43.739-97.498-97.502-97.498zm0 180c-45.491 0-82.5-37.009-82.5-82.5s37.009-82.5 82.5-82.5 82.5 37.009 82.5 82.5-37.008 82.5-82.5 82.5z"/><path d="M373.087 327.118h-124.45a7.5 7.5 0 0 0 0 15h124.451a7.5 7.5 0 1 0-.001-15z"/></svg>
       -->
            <!-- <svg
            v-tooltip="{
                targetClasses: ['it-has-a-tooltip'],
                placement: 'top',
                content: __('Linked in group reservation'),
                classes: ['tooltip_reset']
            }"
            xmlns="http://www.w3.org/2000/svg" width="30" height="19.05" viewBox="0 0 39.554 25.114"><path d="M34.208 11.051a4.516 4.516 0 1 0-5.181 0 7.824 7.824 0 0 0-2.669 1.565 10.125 10.125 0 0 0-3.663-2 5.721 5.721 0 1 0-5.917 0 10.209 10.209 0 0 0-3.624 1.972 7.888 7.888 0 0 0-2.637-1.534 4.516 4.516 0 1 0-5.181 0A7.934 7.934 0 0 0 0 18.548v.517a.034.034 0 0 0 .031.031H9.6a10.526 10.526 0 0 0-.086 1.323v.532a4.162 4.162 0 0 0 4.164 4.164h12.133a4.162 4.162 0 0 0 4.164-4.164v-.532a10.525 10.525 0 0 0-.086-1.323h9.634a.034.034 0 0 0 .031-.031v-.517a7.965 7.965 0 0 0-5.346-7.497Zm-5.854-3.7a3.264 3.264 0 1 1 3.326 3.264h-.125a3.259 3.259 0 0 1-3.201-3.266Zm-13.1-1.62a4.469 4.469 0 1 1 4.727 4.461h-.517a4.481 4.481 0 0 1-4.211-4.463ZM4.641 7.349a3.264 3.264 0 1 1 3.326 3.264h-.125a3.264 3.264 0 0 1-3.201-3.264Zm5.181 10.487H1.268a6.687 6.687 0 0 1 6.59-5.971h.094a6.617 6.617 0 0 1 4.265 1.589 10.368 10.368 0 0 0-2.395 4.382Zm18.885 3.123A2.916 2.916 0 0 1 25.8 23.87H13.665a2.916 2.916 0 0 1-2.911-2.911v-.532a8.99 8.99 0 0 1 8.711-8.977c.086.008.18.008.266.008s.18 0 .266-.008a8.99 8.99 0 0 1 8.711 8.977Zm.931-3.123a10.249 10.249 0 0 0-2.371-4.351 6.649 6.649 0 0 1 4.3-1.62h.094a6.687 6.687 0 0 1 6.59 5.971Z" fill="#4099de"/>
        </svg> -->
      </div>


      <div class="control" :class="{ active: btnSettings }" v-if="!reserved && !hideUnitStatusChangerButton">
        <div class="buttonarea" @click="btnSettings = !btnSettings">
          <button>
            <svg class="sv_setting" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 478.703 478.703"><path d="M454.2 189.1l-33.6-5.7c-3.5-11.3-8-22.2-13.5-32.6l19.8-27.7c8.4-11.8 7.1-27.9-3.2-38.1l-29.8-29.8c-5.6-5.6-13-8.7-20.9-8.7-6.2 0-12.1 1.9-17.1 5.5l-27.8 19.8c-10.8-5.7-22.1-10.4-33.8-13.9l-5.6-33.2A29.54 29.54 0 0 0 259.5 0h-42.1a29.54 29.54 0 0 0-29.2 24.7l-5.8 34c-11.2 3.5-22.1 8.1-32.5 13.7l-27.5-19.8c-5-3.6-11-5.5-17.2-5.5-7.9 0-15.4 3.1-20.9 8.7L54.4 85.6c-10.2 10.2-11.6 26.3-3.2 38.1l20 28.1c-5.5 10.5-9.9 21.4-13.3 32.7l-33.2 5.6A29.54 29.54 0 0 0 0 219.3v42.1a29.54 29.54 0 0 0 24.7 29.2l34 5.8c3.5 11.2 8.1 22.1 13.7 32.5l-19.7 27.4c-8.4 11.8-7.1 27.9 3.2 38.1l29.8 29.8c5.6 5.6 13 8.7 20.9 8.7 6.2 0 12.1-1.9 17.1-5.5l28.1-20c10.1 5.3 20.7 9.6 31.6 13L189 454a29.54 29.54 0 0 0 29.2 24.7h42.2a29.54 29.54 0 0 0 29.2-24.7l5.7-33.6c11.3-3.5 22.2-8 32.6-13.5l27.7 19.8c5 3.6 11 5.5 17.2 5.5 7.9 0 15.3-3.1 20.9-8.7l29.8-29.8c10.2-10.2 11.6-26.3 3.2-38.1l-19.8-27.8c5.5-10.5 10.1-21.4 13.5-32.6l33.6-5.6a29.54 29.54 0 0 0 24.7-29.2v-42.1c.2-14.5-10.2-26.8-24.5-29.2zm-2.3 71.3c0 1.3-.9 2.4-2.2 2.6l-42 7c-5.3.9-9.5 4.8-10.8 9.9-3.8 14.7-9.6 28.8-17.4 41.9-2.7 4.6-2.5 10.3.6 14.7l24.7 34.8c.7 1 .6 2.5-.3 3.4l-29.8 29.8c-.7.7-1.4.8-1.9.8-.6 0-1.1-.2-1.5-.5l-34.7-24.7c-4.3-3.1-10.1-3.3-14.7-.6-13.1 7.8-27.2 13.6-41.9 17.4-5.2 1.3-9.1 5.6-9.9 10.8l-7.1 42c-.2 1.3-1.3 2.2-2.6 2.2h-42.1c-1.3 0-2.4-.9-2.6-2.2l-7-42c-.9-5.3-4.8-9.5-9.9-10.8-14.3-3.7-28.1-9.4-41-16.8-2.1-1.2-4.5-1.8-6.8-1.8-2.7 0-5.5.8-7.8 2.5l-35 24.9c-.5.3-1 .5-1.5.5a2.68 2.68 0 0 1-1.9-.8L75 375.6c-.9-.9-1-2.3-.3-3.4l24.6-34.5c3.1-4.4 3.3-10.2.6-14.8-7.8-13-13.8-27.1-17.6-41.8-1.4-5.1-5.6-9-10.8-9.9L29.2 264c-1.3-.2-2.2-1.3-2.2-2.6v-42.1c0-1.3.9-2.4 2.2-2.6l41.7-7c5.3-.9 9.6-4.8 10.9-10 3.7-14.7 9.4-28.9 17.1-42 2.7-4.6 2.4-10.3-.7-14.6l-24.9-35c-.7-1-.6-2.5.3-3.4l29.8-29.8c.7-.7 1.4-.8 1.9-.8.6 0 1.1.2 1.5.5l34.5 24.6c4.4 3.1 10.2 3.3 14.8.6 13-7.8 27.1-13.8 41.8-17.6 5.1-1.4 9-5.6 9.9-10.8l7.2-42.3c.2-1.3 1.3-2.2 2.6-2.2h42.1c1.3 0 2.4.9 2.6 2.2l7 41.7c.9 5.3 4.8 9.6 10 10.9 15.1 3.8 29.5 9.7 42.9 17.6 4.6 2.7 10.3 2.5 14.7-.6l34.5-24.8c.5-.3 1-.5 1.5-.5a2.68 2.68 0 0 1 1.9.8l29.8 29.8c.9.9 1 2.3.3 3.4l-24.7 34.7c-3.1 4.3-3.3 10.1-.6 14.7 7.8 13.1 13.6 27.2 17.4 41.9 1.3 5.2 5.6 9.1 10.8 9.9l42 7.1c1.3.2 2.2 1.3 2.2 2.6v42.1h-.1zM239.4 136c-57 0-103.3 46.3-103.3 103.3s46.3 103.3 103.3 103.3 103.3-46.3 103.3-103.3S296.4 136 239.4 136zm0 179.6c-42.1 0-76.3-34.2-76.3-76.3s34.2-76.3 76.3-76.3 76.3 34.2 76.3 76.3-34.2 76.3-76.3 76.3z"/></svg>
            <svg class="sv_cancel" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 475.2 475.2"><path d="M405.6 69.6C360.7 24.7 301.1 0 237.6 0s-123.1 24.7-168 69.6S0 174.1 0 237.6s24.7 123.1 69.6 168 104.5 69.6 168 69.6 123.1-24.7 168-69.6 69.6-104.5 69.6-168-24.7-123.1-69.6-168zm-19.1 316.9c-39.8 39.8-92.7 61.7-148.9 61.7s-109.1-21.9-148.9-61.7c-82.1-82.1-82.1-215.7 0-297.8C128.5 48.9 181.4 27 237.6 27s109.1 21.9 148.9 61.7c82.1 82.1 82.1 215.7 0 297.8zm-44.2-253.6a13.46 13.46 0 0 0-19.1 0l-85.6 85.6-85.6-85.6a13.506 13.506 0 1 0-19.1 19.1l85.6 85.6-85.6 85.6a13.46 13.46 0 0 0 0 19.1c2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4l85.6-85.6 85.6 85.6c2.6 2.6 6.1 4 9.5 4 3.5 0 6.9-1.3 9.5-4a13.46 13.46 0 0 0 0-19.1l-85.4-85.6 85.6-85.6a13.46 13.46 0 0 0 0-19.1z"/></svg>
          </button>
        </div>
        <div class="submenu">
          <ul>
            <!-- <li v-if="unit.status == 1"><go-to-maintenance :unit="unit"></go-to-maintenance></li> -->
            <li v-if="unit.status == 1">
                <a @click="openChangeStatusModal('maintenance')" v-permission="'change to under maintenance'" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#AAB8C0"><path d="M430.2 122c7.2 7.2 16.1 3.9 19.3 1.6l56.2-40.2c3.9-2.8 6.3-7.4 6.3-12.2V15a14.98 14.98 0 0 0-15-15h-56.2c-4.8 0-9.4 2.3-12.2 6.3l-40.2 56.2c-4.3 6-3.6 14.1 1.6 19.3l9.5 9.5-119.4 119.4-41.2-41.2v-66.1C238.9 46.4 192.5 0 135.5 0H79.2c-6.1 0-11.5 3.7-13.9 9.3-2.3 5.6-1 12.1 3.3 16.3l48.2 48.2c11.9 11.9 11.9 31.2 0 43-11.9 11.9-31.2 11.9-43 0L25.6 68.6c-4.3-4.3-10.7-5.6-16.3-3.3C3.7 67.7 0 73.2 0 79.2v56.2c0 57 46.4 103.4 103.4 103.4h66.1l10.5 10.5L5.6 388.8c-3.3 2.7-5.4 6.6-5.6 10.9a15.06 15.06 0 0 0 4.4 11.4l96.4 96.4c2.8 2.8 6.6 4.4 10.6 4.4h.8c4.3-.2 8.2-2.3 10.9-5.6L262.6 332l10.5 10.5v66.1c0 57 46.4 103.4 103.4 103.4h56.2c6.1 0 11.5-3.7 13.9-9.3 2.3-5.6 1-12.1-3.3-16.3l-48.2-48.2c-11.9-11.9-11.9-31.2 0-43 11.9-11.9 31.2-11.9 43 0l48.2 48.2c4.3 4.3 10.7 5.6 16.3 3.3s9.3-7.8 9.3-13.9v-56.2c0-57-46.4-103.4-103.4-103.4h-66.1L301.2 232l119.4-119.4 9.6 9.4zM167.3 403.1l-58.4-58.4 146-116.8 29.2 29.2-116.8 146zm19-189.9c-2.8-2.8-6.6-4.4-10.6-4.4h-72.3c-40.5 0-73.4-32.9-73.4-73.4v-20L52.6 138c24.1 24.1 62.4 23.1 85.5 0 23.4-23.4 23.8-61.7 0-85.5L115.5 30h20c40.5 0 73.4 32.9 73.4 73.4v72.3c0 4 1.6 7.8 4.4 10.6l20.3 20.3-30 24-17.3-17.4zm-76.1 261.3l-72.7-72.7 47.8-38.3 63.1 63.1-38.2 47.9zm226.1-171.4h72.3c40.5 0 73.4 32.9 73.4 73.4v20l-22.6-22.6c-24.1-24.1-62.4-23.1-85.5 0-23.4 23.4-23.8 61.7 0 85.5l22.6 22.6h-20c-40.5 0-73.4-32.9-73.4-73.4v-72.3c0-4-1.6-7.8-4.4-10.6l-17.3-17.3 24-30 20.3 20.3c2.8 2.9 6.7 4.4 10.6 4.4zM448.5 30H482v33.5l-39.6 28.3-22.2-22.2L448.5 30z"/></svg>
                    {{ __('To Maintenance') }}
                </a>
            </li>
            <!-- <li v-if="unit.status == 1"><go-to-cleaning :unit="unit"></go-to-cleaning></li> -->
            <li v-if="unit.status == 1">
                <a @click="changeUnitStatus('cleaning')" v-permission="'change to under cleaning'" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 480" fill="#FF9019"><path d="M416 336h-12.5c-8.8 0-17.5 2.1-25.3 6a40.67 40.67 0 0 1-36.3 0c-4.6-2.2-9.4-3.9-14.4-4.8L343 216h17c4.4 0 8-3.6 8-8v-32c0-4.4-3.6-8-8-8h-32v-24c0-2.1-.8-4.2-2.3-5.7L288 100.7V75.8l12.1-9.7 20.7 41.4 14.3-7.2-22.3-44.5 9.9-7.9H352c4.4 0 8-3.6 8-8V8c0-4.4-3.6-8-8-8h-80c-39.7 0-72 32.3-72 72 0 4.4 3.6 8 8 8h16v20.7l-21.7 21.7c-1.5 1.5-2.3 3.5-2.3 5.7v40h-16v-64c0-1-.2-2-.6-2.9-.1-.3-.3-.6-.5-.9-.2-.4-.4-.8-.7-1.2L152 61.2V8c0-4.4-3.6-8-8-8H96c-4.4 0-8 3.6-8 8v53.2L57.8 99c-.3.4-.5.8-.7 1.2-.2.3-.3.6-.5.9-.4.9-.6 1.9-.6 2.9v64H24c-4.4 0-8 3.6-8 8v32c0 4.4 3.6 8 8 8h17l31.1 241c.5 4 3.9 7 7.9 7h188.3a47.85 47.85 0 0 0 35.7 16h12.5c8.8 0 17.5-2.1 25.3-6a40.67 40.67 0 0 1 36.3 0c7.9 3.9 16.5 6 25.3 6H416c26.5 0 48-21.5 48-48v-48c0-26.5-21.5-48-48-48zM328 16h16v16h-16V16zm24 168v16H240v-16h112zM216.6 64c4-27.5 27.6-48 55.4-48h40v20.2L277.2 64h-60.6zM272 80v16h-32V80h32zm-56 51.3l19.3-19.3h41.4l35.3 35.3V168h-96v-36.7zm-8 52.7h16v56c0 3 1.7 5.8 4.4 7.2l48 24c2.3 1.2 4.1 3.2 4.9 5.7.8 2.7.5 5.6-.8 8-2.3 4.6-7.9 6.5-12.5 4.2l-32.5-16.2c-4-1.9-8.8-.3-10.7 3.7-.5 1.1-.8 2.3-.8 3.5v80c0 8.8-7.2 16-16 16v-72h-16v72h-16v-72h-16v72h-16v-72h-16v72c-8.8 0-16-7.2-16-16V184h96zM104 16h32v40h-32V16zm-4.2 56h40.3l19.2 24H80.6l19.2-24zM72 112h96v56H72v-56zm-40 88v-16h64v16H32zm224 184v48c0 5.5 1 10.9 2.8 16H87L57.1 216H96v144a31.97 31.97 0 0 0 32 32h80a31.97 31.97 0 0 0 32-32v-67.1l20.9 10.4c12.6 6.2 27.9 1 34.1-11.6 0-.1.1-.1.1-.2 3.1-6.1 3.6-13.3 1.5-19.8s-6.8-11.9-13-15L240 235.1V216h86.9l-15.5 120H304c-26.5 0-48 21.5-48 48zm192 48a31.97 31.97 0 0 1-32 32h-12.5c-6.3 0-12.5-1.5-18.2-4.3-16-7.9-34.7-7.9-50.6 0-5.6 2.8-11.9 4.3-18.2 4.3H304a31.97 31.97 0 0 1-32-32v-12.3c8.8 7.9 20.2 12.3 32 12.3h12.5c8.8 0 17.5-2.1 25.3-6a40.67 40.67 0 0 1 36.3 0c7.9 3.9 16.5 6 25.3 6H416c11.8 0 23.2-4.4 32-12.3V432zm-32-16h-12.5c-6.3 0-12.5-1.5-18.2-4.3-16-7.9-34.7-7.9-50.6 0-5.6 2.8-11.9 4.3-18.2 4.3H304a32 32 0 1 1 0-64h12.5c6.3 0 12.5 1.5 18.2 4.3 16 7.9 34.7 7.9 50.6 0 5.6-2.8 11.9-4.3 18.2-4.3H416a32 32 0 1 1 0 64zm-8-48h16v16h-16zm-80 0h16v16h-16zm-32 8h16v16h-16zm80 0h16v16h-16z"/></svg>
                    {{ __('To Cleaning') }}
                </a>
            </li>
            <!-- <li v-if="unit.status == 2 || unit.status == 3"><make-unit-available :unit="unit"></make-unit-available></li> -->
            <li v-if="unit.status == 2 || unit.status == 3">
                <a @click="changeUnitStatus('enabled')" v-permission="'change to available'" class="cursor-pointer">
                    <svg style="fill: #28a745;" enable-background="new 0 0 512 512" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                                                <path class="st0" d="m437 75c-48.3-48.4-112.6-75-181-75s-132.7 26.6-181 75c-48.4 48.3-75 112.6-75 181s26.6 132.7 75 181 112.6 75 181 75 132.7-26.6 181-75 75-112.6 75-181-26.6-132.7-75-181zm-181 407c-124.6 0-226-101.4-226-226s101.4-226 226-226 226 101.4 226 226-101.4 226-226 226z"/>
                        <path class="st0" d="m378.3 173.9c-5.9-5.9-15.4-5.9-21.2 0l-132.5 132.4-69.7-69.7c-5.9-5.9-15.4-5.9-21.2 0s-5.9 15.4 0 21.2l80.3 80.3c2.9 2.9 6.8 4.4 10.6 4.4s7.7-1.5 10.6-4.4l143.1-143.1c5.9-5.8 5.9-15.3 0-21.1z"/>
                                            </svg>
                    {{ __('To Available') }}
                </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="control" :class="{ active: btnSettings }">
        <div v-if="showExtraConvertControl" class="buttonarea" @click="btnSettings = !btnSettings">
          <button>
            <svg class="sv_setting" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 478.703 478.703"><path d="M454.2 189.1l-33.6-5.7c-3.5-11.3-8-22.2-13.5-32.6l19.8-27.7c8.4-11.8 7.1-27.9-3.2-38.1l-29.8-29.8c-5.6-5.6-13-8.7-20.9-8.7-6.2 0-12.1 1.9-17.1 5.5l-27.8 19.8c-10.8-5.7-22.1-10.4-33.8-13.9l-5.6-33.2A29.54 29.54 0 0 0 259.5 0h-42.1a29.54 29.54 0 0 0-29.2 24.7l-5.8 34c-11.2 3.5-22.1 8.1-32.5 13.7l-27.5-19.8c-5-3.6-11-5.5-17.2-5.5-7.9 0-15.4 3.1-20.9 8.7L54.4 85.6c-10.2 10.2-11.6 26.3-3.2 38.1l20 28.1c-5.5 10.5-9.9 21.4-13.3 32.7l-33.2 5.6A29.54 29.54 0 0 0 0 219.3v42.1a29.54 29.54 0 0 0 24.7 29.2l34 5.8c3.5 11.2 8.1 22.1 13.7 32.5l-19.7 27.4c-8.4 11.8-7.1 27.9 3.2 38.1l29.8 29.8c5.6 5.6 13 8.7 20.9 8.7 6.2 0 12.1-1.9 17.1-5.5l28.1-20c10.1 5.3 20.7 9.6 31.6 13L189 454a29.54 29.54 0 0 0 29.2 24.7h42.2a29.54 29.54 0 0 0 29.2-24.7l5.7-33.6c11.3-3.5 22.2-8 32.6-13.5l27.7 19.8c5 3.6 11 5.5 17.2 5.5 7.9 0 15.3-3.1 20.9-8.7l29.8-29.8c10.2-10.2 11.6-26.3 3.2-38.1l-19.8-27.8c5.5-10.5 10.1-21.4 13.5-32.6l33.6-5.6a29.54 29.54 0 0 0 24.7-29.2v-42.1c.2-14.5-10.2-26.8-24.5-29.2zm-2.3 71.3c0 1.3-.9 2.4-2.2 2.6l-42 7c-5.3.9-9.5 4.8-10.8 9.9-3.8 14.7-9.6 28.8-17.4 41.9-2.7 4.6-2.5 10.3.6 14.7l24.7 34.8c.7 1 .6 2.5-.3 3.4l-29.8 29.8c-.7.7-1.4.8-1.9.8-.6 0-1.1-.2-1.5-.5l-34.7-24.7c-4.3-3.1-10.1-3.3-14.7-.6-13.1 7.8-27.2 13.6-41.9 17.4-5.2 1.3-9.1 5.6-9.9 10.8l-7.1 42c-.2 1.3-1.3 2.2-2.6 2.2h-42.1c-1.3 0-2.4-.9-2.6-2.2l-7-42c-.9-5.3-4.8-9.5-9.9-10.8-14.3-3.7-28.1-9.4-41-16.8-2.1-1.2-4.5-1.8-6.8-1.8-2.7 0-5.5.8-7.8 2.5l-35 24.9c-.5.3-1 .5-1.5.5a2.68 2.68 0 0 1-1.9-.8L75 375.6c-.9-.9-1-2.3-.3-3.4l24.6-34.5c3.1-4.4 3.3-10.2.6-14.8-7.8-13-13.8-27.1-17.6-41.8-1.4-5.1-5.6-9-10.8-9.9L29.2 264c-1.3-.2-2.2-1.3-2.2-2.6v-42.1c0-1.3.9-2.4 2.2-2.6l41.7-7c5.3-.9 9.6-4.8 10.9-10 3.7-14.7 9.4-28.9 17.1-42 2.7-4.6 2.4-10.3-.7-14.6l-24.9-35c-.7-1-.6-2.5.3-3.4l29.8-29.8c.7-.7 1.4-.8 1.9-.8.6 0 1.1.2 1.5.5l34.5 24.6c4.4 3.1 10.2 3.3 14.8.6 13-7.8 27.1-13.8 41.8-17.6 5.1-1.4 9-5.6 9.9-10.8l7.2-42.3c.2-1.3 1.3-2.2 2.6-2.2h42.1c1.3 0 2.4.9 2.6 2.2l7 41.7c.9 5.3 4.8 9.6 10 10.9 15.1 3.8 29.5 9.7 42.9 17.6 4.6 2.7 10.3 2.5 14.7-.6l34.5-24.8c.5-.3 1-.5 1.5-.5a2.68 2.68 0 0 1 1.9.8l29.8 29.8c.9.9 1 2.3.3 3.4l-24.7 34.7c-3.1 4.3-3.3 10.1-.6 14.7 7.8 13.1 13.6 27.2 17.4 41.9 1.3 5.2 5.6 9.1 10.8 9.9l42 7.1c1.3.2 2.2 1.3 2.2 2.6v42.1h-.1zM239.4 136c-57 0-103.3 46.3-103.3 103.3s46.3 103.3 103.3 103.3 103.3-46.3 103.3-103.3S296.4 136 239.4 136zm0 179.6c-42.1 0-76.3-34.2-76.3-76.3s34.2-76.3 76.3-76.3 76.3 34.2 76.3 76.3-34.2 76.3-76.3 76.3z"/></svg>
            <svg class="sv_cancel" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 475.2 475.2"><path d="M405.6 69.6C360.7 24.7 301.1 0 237.6 0s-123.1 24.7-168 69.6S0 174.1 0 237.6s24.7 123.1 69.6 168 104.5 69.6 168 69.6 123.1-24.7 168-69.6 69.6-104.5 69.6-168-24.7-123.1-69.6-168zm-19.1 316.9c-39.8 39.8-92.7 61.7-148.9 61.7s-109.1-21.9-148.9-61.7c-82.1-82.1-82.1-215.7 0-297.8C128.5 48.9 181.4 27 237.6 27s109.1 21.9 148.9 61.7c82.1 82.1 82.1 215.7 0 297.8zm-44.2-253.6a13.46 13.46 0 0 0-19.1 0l-85.6 85.6-85.6-85.6a13.506 13.506 0 1 0-19.1 19.1l85.6 85.6-85.6 85.6a13.46 13.46 0 0 0 0 19.1c2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4l85.6-85.6 85.6 85.6c2.6 2.6 6.1 4 9.5 4 3.5 0 6.9-1.3 9.5-4a13.46 13.46 0 0 0 0-19.1l-85.4-85.6 85.6-85.6a13.46 13.46 0 0 0 0-19.1z"/></svg>
          </button>
        </div>

        <div v-if="unit && unit.current_reservation" class="showStatusIndicator">
          <span class="circle-px" v-if="unit.status == 3"  :style="{backgroundColor : '#aab8c0 !important' }"></span>
          <span class="circle-px" v-if="unit.status == 2" :style="{backgroundColor : '#ff9100 !important' }"></span>
        </div>

        <div v-if="unit && !unit.current_reservation && unit.previous_reservation" class="showStatusIndicator">
          <span class="circle-px" v-if="unit.status == 3"  :style="{backgroundColor : '#aab8c0 !important' }"></span>
          <span class="circle-px" v-if="unit.status == 2" :style="{backgroundColor : '#ff9100 !important' }"></span>
        </div>

        <div v-if="showExtraConvertControl" class="submenu">
          <ul>
            <!-- <li v-if="unit.status == 1"><go-to-maintenance :unit="unit"></go-to-maintenance></li> -->
            <li v-if="unit.status == 1">
                <a @click="openChangeStatusModal('maintenance')" v-permission="'change to under maintenance'" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#AAB8C0"><path d="M430.2 122c7.2 7.2 16.1 3.9 19.3 1.6l56.2-40.2c3.9-2.8 6.3-7.4 6.3-12.2V15a14.98 14.98 0 0 0-15-15h-56.2c-4.8 0-9.4 2.3-12.2 6.3l-40.2 56.2c-4.3 6-3.6 14.1 1.6 19.3l9.5 9.5-119.4 119.4-41.2-41.2v-66.1C238.9 46.4 192.5 0 135.5 0H79.2c-6.1 0-11.5 3.7-13.9 9.3-2.3 5.6-1 12.1 3.3 16.3l48.2 48.2c11.9 11.9 11.9 31.2 0 43-11.9 11.9-31.2 11.9-43 0L25.6 68.6c-4.3-4.3-10.7-5.6-16.3-3.3C3.7 67.7 0 73.2 0 79.2v56.2c0 57 46.4 103.4 103.4 103.4h66.1l10.5 10.5L5.6 388.8c-3.3 2.7-5.4 6.6-5.6 10.9a15.06 15.06 0 0 0 4.4 11.4l96.4 96.4c2.8 2.8 6.6 4.4 10.6 4.4h.8c4.3-.2 8.2-2.3 10.9-5.6L262.6 332l10.5 10.5v66.1c0 57 46.4 103.4 103.4 103.4h56.2c6.1 0 11.5-3.7 13.9-9.3 2.3-5.6 1-12.1-3.3-16.3l-48.2-48.2c-11.9-11.9-11.9-31.2 0-43 11.9-11.9 31.2-11.9 43 0l48.2 48.2c4.3 4.3 10.7 5.6 16.3 3.3s9.3-7.8 9.3-13.9v-56.2c0-57-46.4-103.4-103.4-103.4h-66.1L301.2 232l119.4-119.4 9.6 9.4zM167.3 403.1l-58.4-58.4 146-116.8 29.2 29.2-116.8 146zm19-189.9c-2.8-2.8-6.6-4.4-10.6-4.4h-72.3c-40.5 0-73.4-32.9-73.4-73.4v-20L52.6 138c24.1 24.1 62.4 23.1 85.5 0 23.4-23.4 23.8-61.7 0-85.5L115.5 30h20c40.5 0 73.4 32.9 73.4 73.4v72.3c0 4 1.6 7.8 4.4 10.6l20.3 20.3-30 24-17.3-17.4zm-76.1 261.3l-72.7-72.7 47.8-38.3 63.1 63.1-38.2 47.9zm226.1-171.4h72.3c40.5 0 73.4 32.9 73.4 73.4v20l-22.6-22.6c-24.1-24.1-62.4-23.1-85.5 0-23.4 23.4-23.8 61.7 0 85.5l22.6 22.6h-20c-40.5 0-73.4-32.9-73.4-73.4v-72.3c0-4-1.6-7.8-4.4-10.6l-17.3-17.3 24-30 20.3 20.3c2.8 2.9 6.7 4.4 10.6 4.4zM448.5 30H482v33.5l-39.6 28.3-22.2-22.2L448.5 30z"/></svg>
                    {{ __('To Maintenance') }}
                </a>
            </li>
            <!-- <li v-if="unit.status == 1"><go-to-cleaning :unit="unit"></go-to-cleaning></li> -->
            <li v-if="unit.status == 1">
                <a @click="changeUnitStatus('cleaning')" v-permission="'change to under cleaning'" class="cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 480" fill="#FF9019"><path d="M416 336h-12.5c-8.8 0-17.5 2.1-25.3 6a40.67 40.67 0 0 1-36.3 0c-4.6-2.2-9.4-3.9-14.4-4.8L343 216h17c4.4 0 8-3.6 8-8v-32c0-4.4-3.6-8-8-8h-32v-24c0-2.1-.8-4.2-2.3-5.7L288 100.7V75.8l12.1-9.7 20.7 41.4 14.3-7.2-22.3-44.5 9.9-7.9H352c4.4 0 8-3.6 8-8V8c0-4.4-3.6-8-8-8h-80c-39.7 0-72 32.3-72 72 0 4.4 3.6 8 8 8h16v20.7l-21.7 21.7c-1.5 1.5-2.3 3.5-2.3 5.7v40h-16v-64c0-1-.2-2-.6-2.9-.1-.3-.3-.6-.5-.9-.2-.4-.4-.8-.7-1.2L152 61.2V8c0-4.4-3.6-8-8-8H96c-4.4 0-8 3.6-8 8v53.2L57.8 99c-.3.4-.5.8-.7 1.2-.2.3-.3.6-.5.9-.4.9-.6 1.9-.6 2.9v64H24c-4.4 0-8 3.6-8 8v32c0 4.4 3.6 8 8 8h17l31.1 241c.5 4 3.9 7 7.9 7h188.3a47.85 47.85 0 0 0 35.7 16h12.5c8.8 0 17.5-2.1 25.3-6a40.67 40.67 0 0 1 36.3 0c7.9 3.9 16.5 6 25.3 6H416c26.5 0 48-21.5 48-48v-48c0-26.5-21.5-48-48-48zM328 16h16v16h-16V16zm24 168v16H240v-16h112zM216.6 64c4-27.5 27.6-48 55.4-48h40v20.2L277.2 64h-60.6zM272 80v16h-32V80h32zm-56 51.3l19.3-19.3h41.4l35.3 35.3V168h-96v-36.7zm-8 52.7h16v56c0 3 1.7 5.8 4.4 7.2l48 24c2.3 1.2 4.1 3.2 4.9 5.7.8 2.7.5 5.6-.8 8-2.3 4.6-7.9 6.5-12.5 4.2l-32.5-16.2c-4-1.9-8.8-.3-10.7 3.7-.5 1.1-.8 2.3-.8 3.5v80c0 8.8-7.2 16-16 16v-72h-16v72h-16v-72h-16v72h-16v-72h-16v72c-8.8 0-16-7.2-16-16V184h96zM104 16h32v40h-32V16zm-4.2 56h40.3l19.2 24H80.6l19.2-24zM72 112h96v56H72v-56zm-40 88v-16h64v16H32zm224 184v48c0 5.5 1 10.9 2.8 16H87L57.1 216H96v144a31.97 31.97 0 0 0 32 32h80a31.97 31.97 0 0 0 32-32v-67.1l20.9 10.4c12.6 6.2 27.9 1 34.1-11.6 0-.1.1-.1.1-.2 3.1-6.1 3.6-13.3 1.5-19.8s-6.8-11.9-13-15L240 235.1V216h86.9l-15.5 120H304c-26.5 0-48 21.5-48 48zm192 48a31.97 31.97 0 0 1-32 32h-12.5c-6.3 0-12.5-1.5-18.2-4.3-16-7.9-34.7-7.9-50.6 0-5.6 2.8-11.9 4.3-18.2 4.3H304a31.97 31.97 0 0 1-32-32v-12.3c8.8 7.9 20.2 12.3 32 12.3h12.5c8.8 0 17.5-2.1 25.3-6a40.67 40.67 0 0 1 36.3 0c7.9 3.9 16.5 6 25.3 6H416c11.8 0 23.2-4.4 32-12.3V432zm-32-16h-12.5c-6.3 0-12.5-1.5-18.2-4.3-16-7.9-34.7-7.9-50.6 0-5.6 2.8-11.9 4.3-18.2 4.3H304a32 32 0 1 1 0-64h12.5c6.3 0 12.5 1.5 18.2 4.3 16 7.9 34.7 7.9 50.6 0 5.6-2.8 11.9-4.3 18.2-4.3H416a32 32 0 1 1 0 64zm-8-48h16v16h-16zm-80 0h16v16h-16zm-32 8h16v16h-16zm80 0h16v16h-16z"/></svg>
                    {{ __('To Cleaning') }}
                </a>
            </li>
            <!-- <li v-if="unit.status == 2 || unit.status == 3"><make-unit-available :unit="unit"></make-unit-available></li> -->
            <li v-if="unit.status == 2 || unit.status == 3">
                <a @click="changeUnitStatus('enabled')" v-permission="'change to available'" class="cursor-pointer">
                    <svg style="fill: #28a745;" enable-background="new 0 0 512 512" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                                                <path class="st0" d="m437 75c-48.3-48.4-112.6-75-181-75s-132.7 26.6-181 75c-48.4 48.3-75 112.6-75 181s26.6 132.7 75 181 112.6 75 181 75 132.7-26.6 181-75 75-112.6 75-181-26.6-132.7-75-181zm-181 407c-124.6 0-226-101.4-226-226s101.4-226 226-226 226 101.4 226 226-101.4 226-226 226z"/>
                        <path class="st0" d="m378.3 173.9c-5.9-5.9-15.4-5.9-21.2 0l-132.5 132.4-69.7-69.7c-5.9-5.9-15.4-5.9-21.2 0s-5.9 15.4 0 21.2l80.3 80.3c2.9 2.9 6.8 4.4 10.6 4.4s7.7-1.5 10.6-4.4l143.1-143.1c5.9-5.8 5.9-15.3 0-21.1z"/>
                                            </svg>
                    {{ __('To Available') }}
                </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="unit_number">{{ unit.unit_number }}</div>
      <div class="unit_name">{{ unit.name }}</div>
      <div class="customer_name">
        <div v-if="reserved">
          <!-- <div class="customer_label" v-if="reserved && reservation.customer.label" v-html="reservation.customer.label"></div> -->
          <div v-if="reservation.reservation_type == 'single'">
            {{ reservation.customer_name }}
          </div>
          <div v-else>
            {{ current_team_id == 894 ? reservation.company_name : reservation.company.name }}
          </div>
        </div>
        <router-link v-if="!reserved && unit.status == 3 && unit.maintenance_info" class="flex items-center gap-2 justify-center" :to="`/resources/unit-maintenances/${unit.maintenance_info.id}`">
          <svg height="15px" width="15px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#38bdf8"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><g> <path class="st0" d="M512,282.163c-0.881-1.49-21.826-38.71-63.998-76.621c-21.106-18.932-47.584-38.03-79.667-52.494 c-32.041-14.455-69.743-24.183-112.337-24.162c-42.595-0.014-80.296,9.707-112.341,24.162 c-48.12,21.722-83.626,53.753-107.33,80.244C12.627,259.846,0.644,281.037,0,282.163l29.331,16.484l13.676,7.749l0.024-0.042 l0.007-0.014c0.895-1.629,20.324-34.688,56.487-66.326c18.068-15.848,40.244-31.331,66.274-42.786 c26.061-11.456,55.91-18.96,90.199-18.974c35.104,0.014,65.561,7.881,92.056,19.798c39.685,17.848,70.324,45.171,90.808,68.131 c10.245,11.462,17.938,21.785,22.98,29.1c2.525,3.657,4.385,6.566,5.574,8.49c0.594,0.966,1.02,1.679,1.286,2.119l0.252,0.448 l0.042,0.063l9.707-5.518l-9.734,5.469L512,282.163z"></path> <path class="st0" d="M255.999,210.339c-47.71,0-86.388,38.674-86.388,86.391c0,47.71,38.678,86.384,86.388,86.384 c47.71,0,86.388-38.674,86.388-86.384C342.386,249.014,303.708,210.339,255.999,210.339z"></path> </g> </g></svg>
          <span class="text-sky-400">{{ __('Inspect') }}</span>
        </router-link>
      </div>
      <!--maintenance type and expected date-->

      <div class="unit_prices well_sar_aligned" v-if="!reserved">
        {{ unit.day_price }} 
        <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span> 
        <span class="period"> / {{ __('Day')}}</span>
      </div>

      <div v-if="reserved" class="unit_prices well_sar_aligned" :class="{ 'text-success no-margin no-padding': reservation.reservation_type == 'group' ? reservation.group_balance > 0 : reservation.balance > 0 ,'text-danger no-margin no-padding text-danger-red': reservation.reservation_type == 'group' ? reservation.group_balance < 0 : reservation.balance < 0 }">
        <template v-if="reservation.reservation_type == 'group'">
            {{ Math.abs(reservation.group_balance).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
        </template>
        <template v-else>
            {{ reservation.decimal_places == 3  ?  (Math.abs(reservation.balance / 1000  )).toFixed(2) : (Math.abs(reservation.balance / 100  )).toFixed(2) }} <span v-if="(currency && currency == 'SAR') || !currency" class="icon-saudi_riyal"></span> <span v-else>{{ __(currency) }}</span>
        </template>
        <span style="font-weight: normal;" v-if="reservation.reservation_type == 'group'" :class="{ 'text-success no-margin no-padding': reservation.group_balance > 0 ,'text-danger-red no-padding no-margin': reservation.group_balance < 0 }">
            {{ (reservation.group_balance  > 0) ? '('+__('credit')+')'  : ''}}
            {{ (reservation.group_balance < 0) ? '('+__('debit')+')' : ''}}
        </span>
        <span v-else style="font-weight: normal;" :class="{ 'text-success no-margin no-padding': reservation.balance > 0 ,'text-danger-red no-padding no-margin': reservation.balance < 0 }">
          {{ (reservation.balance  > 0) ? '('+__('credit')+')'  : ''}}
          {{ (reservation.balance < 0) ? '('+__('debit')+')' : ''}}
        </span>
        <div class="text-black inline-block">/</div>
        <b class="text-black font-normal" v-if="reservation.rent_type == 1">{{reservation.nights}} {{__('Night')}}</b>
        <b class="text-black font-normal" v-if="reservation && reservation.rent_type == 2">
          {{ Math.floor(reservation.nights / 30) }} {{__('Month')}}
          <span v-if="reservation.nights%30 > 0"> | {{reservation.nights%30 }} {{__('Night')}}</span>
        </b>
      </div>
      <div class="buttons_area">
        <div class="all_buttons_cases" v-if="unit.status == 1">
          <div class="right_button" v-if="previousReservationId != null">
            <div class="tooltip_cust">
                <span>{{__('There is a guest logged in on this unit')}}</span>
            </div><!-- tooltip_cust -->
            <button type="button" @click="goToPreviousReservation(previousReservationId)" >1</button>
          </div><!-- right_button -->
          <div class="center_button">
            <button v-permission="'create reservations'" v-if="!reserved && unit.status == 1" type="button" class="reservation_button " v-on:click="newReservation">{{__('Reserve')}}</button>
             <button  v-if="reserved && hasPrevCheckedInAndCurrentRes" v-permission="'view reservations'" type="button" @click="goToReservation(reservation.id)" class="reservation_button">{{__('Manage Reservation')}}</button>
          </div><!-- center_button -->
          <!-- <div class="left_button" v-if="futureReservationsCount">
            <span>{{futureReservationsCount}}</span>
          </div>  -->
        </div><!-- all_buttons_cases -->

        <!-- <div class="all_buttons_cases" v-if="reserved && unit.status == 1">
          <div class="right_button" v-if="previousReservationId">
            <div class="tooltip_cust">
                <span>{{__('There is a guest logged in on this unit')}}</span>
            </div>
            <button type="button" @click="goToReservation(previousReservationId)" >1</button>
          </div>
          <div class="center_button">
            <button  v-if="reserved && !reservation.checked_in" v-permission="'view reservations'" type="button" @click="goToReservation(reservation.id)" class="reservation_button">{{__('Manage Reservation')}}</button>
          </div>

        </div> -->

        <button  v-if="reserved && !hasPrevCheckedInAndCurrentRes && unit.status == 1" v-permission="'view reservations'" type="button" @click="goToReservation(reservation.id)" class="reservation_button">{{__('Manage Reservation')}}</button>
        <button type="button" class="reservation_button" v-if="reserved && unit.status == 2" @click="goToReservation(reservation.id)">{{__('Manage Reservation')}}</button>

        <button type="button" class="reservation_button" v-if="!reserved && unit.status == 3" @click="btnSettings = !btnSettings"> {{__('Under Maintenance')}}</button>
        <button type="button" class="reservation_button" v-if="!reserved && unit.status == 2" @click="btnSettings = !btnSettings">{{__('Under Cleaning')}}</button>
      </div><!-- end buttons_area -->
    </div><!-- end unit_item -->

    <sweet-modal :enable-mobile-fullscreen="false" :pulse-on-block="false" :title="__('Change maintenance status')" overlay-theme="dark" ref="openChangeStatusModalRef" class="relative">
      <loading :active.sync="typeLoading"
               :loader="'spinner'"
               :color="'#7e7d7f'"
               :is-full-page="false">
      </loading>
      <form @submit.prevent="onSubmitChangeMaintenanceStatus">
        <div class="grid grid-cols-1 gap-3" v-bind:class="{ 'md:grid-cols-2': maintenanceActionTypes.length > 0 }">
            <div class="form-group" v-if="maintenanceActionTypes.length > 0">
              <div class="input-group-icon">
                <label for="mainetenanceInput">{{ __('Maintenance type') }}</label>
              </div>
              <select class="form-control" id="mainetenanceInput" v-model="maintenance.type">
                <option v-for="action in maintenanceActionTypes" :value="action.id">
                  {{locale == 'en' ? action.name_en : action.name_ar}}
                </option>
              </select>
            </div>
            <div class="form-group">
              <div class="input-group-icon">
                <label for="mainetenanceInput">{{ __('Expected date time') }}</label>
              </div>
              <!-- <input
                  class="form-control"
                  type="date"
                  v-model="maintenance.expected_date"
                  :placeholder="__('Expected date')"
              /> -->
              <date-picker-to
                :enable-seconds="false"
                :enable-time="true"
                :twelve-hour-time="false"
                :locale="locale"
                :minDateToday="true"
                :value="maintenance.expected_date_time"
                :placeholder="__('Expected date time')"
              />
            </div>
        </div>
        <div class="grid grid-cols-1">
          <div class="content_area mt-1">
            <textarea cols="30" rows="6" :placeholder="__('Write your note here ..')" class="form-control" v-model="maintenance.note"></textarea>
          </div>
        </div>
        <div class="grid grid-cols-1 mt-2">
          <button type="submit" class="action_button justify-self-end">{{ __('Save') }}</button>
        </div>
      </form>
    </sweet-modal>

  </div><!-- end unit_col -->

</template>

<script>
    import moment from 'moment'
    import momenttimezone from 'moment-timezone'
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    import DatePickerTo from "./reservation_filters/DatePickerTo";
    export default {
        name: "unit",
        props: {
            unit: {
                type: Object
            },
            price_type: {
                type: String,
                default: 'Day'
            },
            date: {
                type: String,
            },
            day_start: {
                type: String
            },
            day_end: {
                type: String
            },
            maintenanceActionTypes: {
              type: Array
            },
            main_calendar_date_was_changed: {
              type : Boolean,
              default: false
            },
            // currency: {
            //     type: String,
            //     default: 'SAR'
            // },
        },
        components : {
            Loading,
            DatePickerTo
        },
        data: () => {
            return {
                locale : null,
                reactive: true,
                onlineReservationsCount : null,
                btnSettings: false,
                btnAddons: false,
                ActiveInfoReserved: false,
                onlineReserved: false,
                reserved: false,
                previousReservationId: null,
                futureReservationsCount: null,
                reservation : null,
                pusherLoading : false,
                popoverData : {
                    date_in : null,
                    date_out : null,
                    total_deposit : null,
                    total_withdraw : null,
                    leasing_price : null,
                    total_services : null,
                    total : null,
                    balance : null,
                    checkin_date : null,
                },
                awaitingPaymentOrConfirmation : false,
                // today : moment().format('YYYY-MM-DD'),
                // currentTime : moment.tz(moment(), 'Asia/Riyadh').format('HH:mm'),
                hasPrevCheckedInAndCurrentRes : false,
                hideUnitStatusChangerButton : false,
                showExtraConvertControl : false,
                bindNaturalUnitClass : false,
                current_team_id : Nova.app.currentTeam.id,
                maintenance: {
                  type: null,
                  expected_date_time: null,
                  note: null
                },
                typeLoading: false,
                currency : Nova.app.currentTeam.currency
            }
        },
        computed : {
           getTimeBanner() {
                return moment(this.popoverData.checkin_date).format("YYYY/MM/DD hh:mm A");
            }
        },
        methods: {

            openUnitPopover(id){
                if(this.ActiveInfoReserved){
                    this.ActiveInfoReserved = false;
                    return false;
                }
                Nova.request().get('/nova-vendor/calender/unit-reservation-popover?id=' + id)
                    .then((res) => {

                        this.popoverData = {
                            date_in : res.data.date_in,
                            date_out : res.data.date_out,
                            total_deposit : res.data.total_deposit,
                            total_withdraw : res.data.total_withdraw,
                            leasing_price : res.data.leasing_price,
                            total_services : res.data.total_services,
                            total : res.data.total,
                            balance : res.data.balance,
                            checkin_date : res.data.checkin_date
                        }
                        this.ActiveInfoReserved = true;
                    })
            },
            getPrice(prices) {
                if (this.reserved) {
                    return;
                }
                if (this.price_type == 'Month') return prices.month;
                if (this.price_type == 'Hour') return prices.hour;
                return prices.day;
            },
            changeSettings(){

            },

            newReservation() {
                this.$router.replace({
                    name: 'new-reservation',
                    params: {date: moment(String(this.date)).format('YYYY-MM-DD'), room_id: this.unit.id,changed:this.main_calendar_date_was_changed}
                })
            },
            goToPreviousReservation(id){
                this.$router.push({
                    name: 'reservation',
                    params: {id: id}
                })
            },
            goToReservation(id) {
              if(this.reservation && this.reservation.customer_id){
                this.$router.push({
                    name: 'reservation',
                    params: {id: id}
                })
              }else{
                this.$router.push({
                    name: 'reservation-noc',
                    params: {id: id}
                })
              }
            },

            formatDate(date) {
                return Nova.app.__formatDateWithHumanDate(date);
            },

            drawReservationOnUnitBlock(){

                /**
                 * We have two factors here
                 * @1 the incoming date match the todays date
                 * @2 the incoming date doesnt match the todays date
                 */
                if(this.unit.today == this.date){

                    /**
                     * Factor @1 the calendar date in unit housing match today
                     * @note day start & day end from settings affects all cases
                     */

                    if(this.unit.previous_reservation){

                        //  if(this.unit.previous_reservation && this.unit.previous_reservation.date_out == this.today){

                             if(this.unit.currentTime < this.day_end){
                                this.reserved = true;
                                this.reservation = this.unit.previous_reservation;
                                if(this.unit.legacy_checkedin_reservation && !this.unit.previous_reservation.checked_in){
                                    this.hasPrevCheckedInAndCurrentRes = true;
                                    this.previousReservationId = this.unit.legacy_checkedin_reservation.id;

                                    if(this.previousReservationId){
                                        this.hideUnitStatusChangerButton = true;
                                    }
                                }


                                 if(!this.unit.previous_reservation.checked_in){
                                    // if(this.unit.status != 1 ){
                                    //   // this.bindNaturalUnitClass = true;
                                      this.showExtraConvertControl = true;
                                    // }


                                  }
                                return;
                             }else{
                                this.reserved = false;
                                this.reservation = null;

                                if(this.unit.current_reservation){
                                    this.reserved = true;
                                    this.reservation = this.unit.current_reservation;
                                    this.hasPrevCheckedInAndCurrentRes = true;
                                    if(this.unit.current_reservation.status === 'awaiting-payment' || this.unit.current_reservation.status === 'awaiting-confirmation'){
                                        this.awaitingPaymentOrConfirmation = true;
                                    }

                                }
                                if(this.unit.previous_reservation.checked_in){
                                    this.previousReservationId = this.unit.previous_reservation.id;
                                    if(this.previousReservationId){
                                        this.hideUnitStatusChangerButton = true;
                                    }
                                }

                                if(this.unit.legacy_checkedin_reservation){
                                    this.previousReservationId = this.unit.legacy_checkedin_reservation.id;
                                    if(this.previousReservationId){
                                        this.hideUnitStatusChangerButton = true;
                                    }
                                    return;
                                }

                             }

                             return;
                        //  }


                    }

                    /**
                     * There is a current reservation as the normal behaviour , checked in , or not checked in
                     */
                    if(this.unit.current_reservation){
                        this.reserved = true;
                        this.reservation = this.unit.current_reservation;
                        if(this.unit.previous_reservation && this.unit.previous_reservation.checked_in && this.unit.previous_reservation.date_out == this.unit.today){
                            this.previousReservationId = this.unit.previous_reservation.id;
                            if(this.previousReservationId){
                                this.hideUnitStatusChangerButton = true;
                            }
                        }

                        if(this.unit.current_reservation.status === 'awaiting-payment' || this.unit.current_reservation.status === 'awaiting-confirmation'){
                            this.awaitingPaymentOrConfirmation = true;
                        }

                        if(this.unit.legacy_checkedin_reservation && !this.unit.current_reservation.checked_in){
                            this.hasPrevCheckedInAndCurrentRes = true;
                            this.previousReservationId = this.unit.legacy_checkedin_reservation.id;
                        }

                        if(!this.unit.current_reservation.checked_in && this.unit.current_reservation.date_in == this.unit.today){

                            if(this.unit.currentTime < this.day_start && (this.unit.date == moment(new Date(this.unit.current_reservation.date_in)).format('YYYY-MM-DD')) ){
                                this.reserved = false;
                                if(this.unit.status != 1 ){
                                  this.bindNaturalUnitClass = true;
                                }
                            }
                            if(this.unit.currentTime < this.day_start && (this.unit.date == moment(new Date(this.unit.current_reservation.date_out)).subtract(1, "days").format('YYYY-MM-DD') )){
                                this.reserved = false;
                                 if(this.unit.status != 1 ){
                                  this.bindNaturalUnitClass = true;
                                }
                            }


                        }

                         if(!this.unit.current_reservation.checked_in){
                          // if(this.unit.status != 1 ){
                              this.showExtraConvertControl = true;
                          //   }
                         }

                        if(this.unit.current_reservation.checked_in){
                             if(this.unit.currentTime < this.day_start && (this.unit.date == moment(new Date(this.unit.current_reservation.date_out)).format('YYYY-MM-DD') )){
                                this.reserved = false;
                            }
                        }
                        // if(this.unit.currentTime < this.day_start && (this.unit.date == moment(new Date(this.unit.current_reservation.date_out)).subtract(1, "days").format('YYYY-MM-DD') )){
                        // // if(this.unit.currentTime < this.day_start && (this.unit.date == moment(new Date(this.unit.current_reservation.date_out)).format('YYYY-MM-DD') )){
                        //     this.reserved = false;
                        // }

                        return;
                    }

                    if(this.unit.legacy_checkedin_reservation){
                        this.previousReservationId = this.unit.legacy_checkedin_reservation.id;
                        if(this.previousReservationId){
                            this.hideUnitStatusChangerButton = true;
                        }
                        return;
                    }



                }else{

                    /**
                     * Any other date rather than today
                     */

                    if(this.unit.previous_reservation){

                         if(this.unit.previous_reservation.checked_in){
                                this.hideUnitStatusChangerButton = true;
                                this.reserved = false;
                                this.reservation = null;

                                if(this.unit.current_reservation){
                                    this.reserved = true;
                                    this.reservation = this.unit.current_reservation;
                                    this.hasPrevCheckedInAndCurrentRes = true;
                                    if(this.unit.current_reservation.status === 'awaiting-payment' || this.unit.current_reservation.status === 'awaiting-confirmation'){
                                        this.awaitingPaymentOrConfirmation = true;
                                    }

                                }
                                this.previousReservationId = this.unit.previous_reservation.id;
                                return;

                         }

                    }

                    if(this.unit.current_reservation){

                        this.reserved = true;
                        this.reservation = this.unit.current_reservation;
                        if(this.unit.previous_reservation && this.unit.previous_reservation.checked_in && this.unit.previous_reservation.date_out == this.unit.today){
                            this.previousReservationId = this.unit.previous_reservation.id;
                        }

                        if(this.unit.current_reservation.status === 'awaiting-payment' || this.unit.current_reservation.status === 'awaiting-confirmation'){
                            this.awaitingPaymentOrConfirmation = true;
                        }
                        return;
                    }

                }

                if (this.unit.legacy_checkedin_reservation) {
                    this.hideUnitStatusChangerButton = true;
                }

                // in general hide action buttons when date is past
                if(this.date < this.unit.today){
                    this.hideUnitStatusChangerButton = true;
                }
            },

            changeUnitStatus(type){

                let successMessage = this.formatStatusMessage(type);
               axios
                    .post('/nova-vendor/calender/units/status', {
                        unit: this.unit.id,
                        type: type
                    })
                    .then(response => {
                        this.$toasted.show(this.__(successMessage), {type: 'success'});
                        this.btnSettings = false;
                        // am forcing set unit status to new status
                        this.unit.status = response.data.unit.status;
                        // if(response.data.unit.status != 1 ){
                        //   // this.bindNaturalUnitClass = true;
                        //   this.showExtraConvertControl = true;
                        // }
                        this.drawReservationOnUnitBlock();
                        // then call occupied counters again through event emit
                        Nova.$emit('unit-status-setting-changed', this.unit);
                    })
            },
            formatStatusMessage(type){
                switch (type) {
                            case 'maintenance':
                                return 'Unit status has been changed to under maintenance successfully';
                            break;
                            case 'cleaning':
                                return 'Unit status has been changed to under cleaning successfully';
                            break;

                            default:
                                return 'Unit status has been changed to available successfully';
                            break;
                        }
            },

            /**
            checkAppendedUnit(){


                this.onlineReserved = this.unit.online_reservations > 0 ? true : false;
                this.onlineReservationsCount = this.unit.online_reservations ;
                this.previousReservationId = !_.isEmpty(this.unit.past_reservation)  ? this.unit.past_reservation.id : null;


                // let selectedDateTimeParsed  =   Date.parse(this.unit.date);
                // let selectedDateParsed  =   Date.parse(this.unit.date_parsed);


                if(this.unit.previous_reservation && this.unit.past_reservation){

                    if(this.unit.date < this.unit.previous_reservation.date_out_and_day_start  && !this.unit.previous_reservation.checked_in){
                        this.reserved = true;
                        this.reservation = this.unit.previous_reservation;
                        return ;
                    }

                    if(this.unit.previous_reservation.date_out_and_day_end > this.unit.date  && this.unit.previous_reservation.checked_in){
                        this.reserved = true;
                        this.reservation = this.unit.previous_reservation;
                        return ;
                    }

                    this.reserved = false;
                    this.reservation = null;
                    this.previousReservationId = this.unit.previous_reservation.id;
                    return;
                }

                // if(this.unit.previous_reservation && !this.unit.reservation){
                if(this.unit.previous_reservation && !this.unit.reservation){

                    // let prReservationDateoutAndDayStartParsed =   Date.parse(this.unit.previous_reservation.date_out_and_day_start);
                    // let prReservationDateoutAndDayEndParsed =   Date.parse(this.unit.previous_reservation.date_out_and_day_end);

                    if(this.unit.date < this.unit.previous_reservation.date_out_and_day_start  && !this.unit.previous_reservation.checked_in){
                        this.reserved = true;
                        this.reservation = this.unit.previous_reservation;
                        return ;
                    }

                    if(this.unit.previous_reservation.date_out_and_day_end > this.unit.date  && this.unit.previous_reservation.checked_in){
                        this.reserved = true;
                        this.reservation = this.unit.previous_reservation;
                        return ;
                    }

                    if(this.reservation && this.reservation.status === 'awaiting-payment'){
                        this.awaitingPaymentOrConfirmation = true;
                    }

                }
                if(this.unit.reservation){


                    // let reservationDateoutAndDayStartParsed =   Date.parse(this.unit.reservation.date_out_and_day_start);
                    // let reservationDateoutAndDayEndParsed =     Date.parse(this.unit.reservation.date_out_and_day_end);
                    // let reservationDateOutParsed =     Date.parse(this.unit.reservation.date_out);

                    if(this.unit.reservation.checked_in){
                        // reservation has checkin
                        if( this.unit.date > this.unit.reservation.date_out_and_day_end){
                            this.reserved = false;
                            this.reservation = null;
                            this.previousReservationId = this.unit.previous_reservation.id

                        }else{
                            this.reserved = true;
                            this.reservation =  this.unit.reservation;
                        }
                    }else{

                        if(this.unit.date > this.unit.reservation.date_out_and_day_start){

                            if(this.unit.date_parsed > this.unit.reservation.date_out_parsed){
                                this.reserved = true;
                                this.reservation =  this.unit.reservation;
                            }else{
                                this.reserved = false;
                                this.reservation = null;
                            }

                        }else{
                            this.reserved = true;
                            this.reservation =  this.unit.reservation;
                        }

                    }



                    if(this.reservation && this.reservation.status === 'awaiting-payment'){
                        this.awaitingPaymentOrConfirmation = true;
                    }
                }



            },
             */
            /**
            openTargetModal(type){

                switch (type) {
                    case 'cash-reciept' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id){
                            Nova.$emit('quick-open-cash-reciept-modal' , this.reservation);
                        }
                        break;
                    case 'payment-voucher' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id) {
                            Nova.$emit('quick-open-payment-voucher-modal', this.reservation);
                        }
                        break;
                    case 'service-transaction' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id) {
                            Nova.$emit('quick-open-service-transaction-modal', this.reservation);
                        }
                        break;
                    case 'checked-in' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id) {
                            Nova.$emit('quick-open-checked-in-modal', this.reservation);
                        }
                        break;
                    case 'checked-out' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id) {
                            Nova.$emit('quick-open-checked-out-modal', this.reservation);
                        }
                        break;
                    case 'edit-reservation' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id) {
                            Nova.$emit('quick-open-edit-reservation-modal', this.reservation);
                        }
                        break;
                    case 'cancel-reservation' :
                        this.btnAddons = false;
                        if(this.reservation.unit_id === this.unit.id) {
                            Nova.$emit('quick-open-cancel-reservation-modal', this.reservation);
                        }
                        break;
                    default :
                        break;
                }
            },
             */

            /**
            triggerClick(type){

                // why am doing this , to hide the menu by change the value of btnAddons
                if(type === 'contract'){
                    this.btnAddons = false;
                    let elem = this.$refs.contract;
                    elem.click();
                }

                if(type === 'summary'){
                    this.btnAddons = false;
                    $('#reservation_summary_form_' + this.reservation.id).submit();
                }

                if(type === 'customer'){
                    this.btnAddons = false;
                    let elem = this.$refs.customer;
                    elem.click();
                }
            },
             */
            /**
            updateReservation() {
                let config = {headers: {'Content-Type': 'application/json', 'Cache-Control': 'no-cache'}};
                axios
                    .get('/nova-vendor/calender/reservation/' + this.reservation.id , config)
                    .then(response => {
                        this.reservation = response.data;
                    }).catch(err => {
                    this.$toasted.show(this.__(err), {type: 'error'})
                })
            },
            */
            openChangeStatusModal(type) {
              switch(type) {
                case "maintenance": {
                  this.$refs.openChangeStatusModalRef.open();
                  return;
                }
                default: {
                  return;
                }
              }
            },
            onSubmitChangeMaintenanceStatus() {
              this.typeLoading = true;
              let successMessage = this.formatStatusMessage("maintenance");
              let errors = []
              if(this.maintenanceActionTypes.length > 0) {
                if(!this.maintenance.type) {
                  errors.push(this.__("Maintenance type is required"));
                }
                if(!this.maintenance.expected_date_time) {
                  errors.push(this.__("Expected date is required"));
                  errors.push(this.__("Expected time is required"));
                }
              }
              if(errors.length > 0) {
                errors.forEach(error => {
                  this.$toasted.error(error, {type: 'error'});
                });
                this.typeLoading = false;
                return;
              }
              const payload = {
                'sub_type': this.maintenance.type,
                'expected_date_time': this.maintenance.expected_date_time,
                'note': this.maintenance.note
              }
              axios
                    .post('/nova-vendor/calender/units/status', {
                        unit: this.unit.id,
                        type: "maintenance",
                        ...payload
                    })
                    .then(response => {
                        this.$toasted.show(this.__(successMessage), {type: 'success'});
                        this.btnSettings = false;
                        // am forcing set unit status to new status
                        this.unit.status = response.data.unit.status;
                        this.unit.maintenance_info = response.data.unit.maintenance_info;
                        //this.unit = response.data.unit;
                        // if(response.data.unit.status != 1 ){
                        //   // this.bindNaturalUnitClass = true;
                        //   this.showExtraConvertControl = true;
                        // }
                        this.drawReservationOnUnitBlock();
                        this.typeLoading = false;
                        this.maintenance.type = "";
                        this.maintenance.expected_date_time = "";
                        this.maintenance.note = "";
                        this.$refs.openChangeStatusModalRef.close();
                        // then call occupied counters again through event emit
                        Nova.$emit('unit-status-setting-changed', this.unit);
                    })
            }
        },
        mounted() {
            // this.currency = Nova.app.currentTeam.currency
            this.drawReservationOnUnitBlock();
            // this.checkAppendedUnit();
            // Nova.$on('pusher-unit-reservation-canceld' , (obj) => {

            //     if(obj.unit_id === this.unit.id){
            //         if(this.reservation){
            //             if(this.reservation.id === obj.reservation_id){
            //                 Nova.$emit('get-units-after-cancel');
            //             }
            //         }

            //     }

            // });

           Nova.$on('pusher-unit-state-changed' , (unit_id) => {
                if(this.unit.id == unit_id){
                    this.pusherLoading = true ;
                    axios.get('/nova-vendor/calender/unit/' + unit_id)
                    .then((res) => {
                        this.$set(this.unit , 'status' , res.data.status);
                        this.pusherLoading = false ;
                    })
                    .catch((err) => {
                        console.log(err);
                    })

                }
            });
            // Nova.$on('pusher-unit-state-online-reservation' , (unit_id) => {

            //     if(this.unit.id == unit_id){
            //         this.pusherLoading = true ;
            //         axios.get('/nova-vendor/calender/unit/' + unit_id)
            //         .then((res) => {
            //             this.onlineReservationsCount = res.data.online_reservations_count;
            //             // this.$set(this.unit , 'status' , res.data.status);
            //             this.onlineReserved = true;
            //             this.pusherLoading = false ;
            //         })
            //         .catch((err) => {
            //             console.log(err);
            //         })


            //     }
            // });

            // Nova.$on('online-reservation-canceled' , (unit_id) => {

            //     if(this.unit.id == unit_id){
            //         this.pusherLoading = true ;
            //         axios.get('/nova-vendor/calender/unit/' + unit_id)
            //         .then((res) => {
            //             this.onlineReservationsCount = res.data.online_reservations_count;
            //             // this.$set(this.unit , 'status' , res.data.status);
            //             if(!this.onlineReservationsCount){
            //                 this.onlineReserved = false;
            //             }else{
            //                 this.onlineReserved = true;
            //             }
            //             this.pusherLoading = false ;
            //         })
            //         .catch((err) => {
            //             console.log(err);
            //         })


            //     }
            // });

            Nova.$on('unit-status-self-changed' , (unit) => {
                if(this.unit.id == unit.id){
                    this.btnSettings = false;
                    this.$set(this.unit , 'status' , unit.status);
                }
            });

            // Nova.$on('service-added' , (reservation) => {
            //     if(reservation.unit.id === this.unit.id){
            //         this.updateReservation();
            //     }
            // })

            // Nova.$on('add-service-transaction-error' , (reservation) => {
            //     if(reservation.unit.id === this.unit.id){

            //         this.$toasted.show(this.__('You can\'t add any more service transaction cause there is old invoice and there are no available invoices to add'), {
            //             duration : 5000,
            //             type: 'error',
            //             position: "top-center",
            //         });
            //         return false;
            //     }
            // });
            Nova.$on("to-change", (val) => {
              this.maintenance.expected_date_time = val;
            });
        },
        destroyed() {
            Nova.$off('quick-open-cash-reciept-modal');
            Nova.$off('quick-open-payment-voucher-modal');
            Nova.$off('quick-open-service-transaction-modal');
            Nova.$off('add-service-transaction-error');
            Nova.$off('quick-open-checked-in-modal');
            Nova.$off('quick-open-checked-out-modal');
            Nova.$off('quick-open-edit-reservation-modal');
            Nova.$off('quick-open-cancel-reservation-modal');
        },
        created() {
            this.locale = Nova.config.local;
        }
    }
</script>

<style lang="scss">
$avalableColor: #28a745;
$notAvalableColor: #0a80d8;
$holdStatusColor: #A020F0;
$checkedInColor: #f6574b;
$underCleaningColor: #ff9100;
$underMaintenanceColor: #aab8c0;
$onlineReserved: #9B59B6;
.addServiceModal .sweet-modal {
    max-width: 70%
}
.unit_col {
  .unit_item {
    -ms-flex: 1 1 auto;
    -webkit-box-flex: 1;
    flex: 1 1 auto;
    background: #fff;
    padding: 10px;
    border-right: 2px solid;
    border-left: 2px solid;
    border-top: 2px solid;
    border-bottom: 8px solid;
    border-radius: 0.3rem;
    box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
    margin: .5rem;
    text-align: center;
    position: relative;
    &.card-top-avalable {
      border-color: $avalableColor;
      .unit_number {
        color: $avalableColor;
      } /* unit_number */
      .reservation_button {
        border-color: $avalableColor;
        color: $avalableColor;
        &:hover {
          background: $avalableColor;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-top-avalable */
    &.card-top-not-avalable {
      border-color: $notAvalableColor;
      .unit_number {
        color: $notAvalableColor;
      } /* unit_number */
      .reservation_button {
        border-color: $notAvalableColor;
        color: $notAvalableColor;
        &:hover {
          background: $notAvalableColor;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-top-not-avalable */
    &.card-online-reserved {
      border-color: $onlineReserved;
      .unit_number {
        color: $onlineReserved;
      } /* unit_number */
      .reservation_button {
        border-color: $onlineReserved;
        color: $onlineReserved;
        &:hover {
          background: $onlineReserved;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-online-reserved */
    &.card-top-not-avalable-and-checked-in {
      border-color: $checkedInColor;
      .unit_number {
        color: $checkedInColor;
      } /* unit_number */
      .reservation_button {
        border-color: $checkedInColor;
        color: $checkedInColor;
        &:hover {
          background: $checkedInColor;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-top-not-avalable-and-checked-in */
    &.card-top-under-cleaning {
      border-color: $underCleaningColor;
      .unit_number {
        color: $underCleaningColor;
      } /* unit_number */
      .reservation_button {
        border-color: $underCleaningColor;
        color: $underCleaningColor;
        &:hover {
          background: $underCleaningColor;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-top-under-cleaning */
    &.card-top-under-maintenance {
      border-color: $underMaintenanceColor;
      .unit_number {
        color: $underMaintenanceColor;
      } /* unit_number */
      .reservation_button {
        border-color: $underMaintenanceColor;
        color: $underMaintenanceColor;
        &:hover {
          background: $underMaintenanceColor;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-top-under-maintenance */
    &.card-top-holding {
      border-color: $holdStatusColor;
      .unit_number {
        color: $holdStatusColor;
      } /* unit_number */
      .reservation_button {
        border-color: $holdStatusColor;
        color: $holdStatusColor;
        &:hover {
          background: $holdStatusColor;
          color: #ffffff;
        } /* hover */
      } /* reservation_button */
    } /* card-top-under-maintenance */
    .unit_number {
      font-size: 22px;
      line-height: 22px;
      margin: 0 auto 10px;
      height: 22px;
    } /* unit_number */
    .unit_name {
      color: #000;
      font-size: 17px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    } /* unit_name */
    .customer_name {
      font-size: small;
      min-height: 17px;
      height: 35px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 35px;
      margin: 10px auto;
      .customer_label {
        display: inline-block;
        margin: 0 0 0 5px;
        [dir="ltr"] & {
          margin: 0 5px 0 0;
        } /* ltr */
        label {
          display: block;
          min-width: 50px;
          border-radius: 100px;
          border-width: 1px;
          font-size: 13px;
          text-align: center;
          padding: 0 10px;
          border-style: solid;
          color: #000000;
          margin: 5px auto;
          height: 20px;
          line-height: 18px;
        } /* label */
      } /* customer_label */
    } /* customer_name */
    .unit_prices {
      white-space: nowrap;
      font-size: 13px;
      overflow: hidden;
      text-overflow: ellipsis;
    } /* unit_prices */
    .buttons_area {
      margin: 10px auto 0;
      .all_buttons_cases {
        display: flex;
        justify-content: space-between;
        .right_button {
          margin: 0 0 0 5px;
          position: relative;
          [dir="ltr"] & {
            margin: 0 5px 0 0;
          } /* ltr */
          button {
            height: 35px;
            width: 35px;
            background: #fff;
            border: 1px solid;
            border-radius: 5px;
            font-size: 20px;
            line-height: 35px;
            color: #d82a3d;
          } /* button */
          .tooltip_cust {
            position: absolute;
            bottom: 100%;
            right: 0;
            left: 0;
            z-index: 99;
            padding: 0 0 13px;
            width: 100%;
            visibility: hidden;
            opacity: 0;
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            span {
              display: table;
              white-space: nowrap;
              background: #000;
              color: #fff;
              text-align: center;
              border-radius: 5px;
              padding: 5px 10px;
              width: 100%;
              font-size: 13px;
              margin: 0 -10px 0 0;
              [dir="ltr"] & {
                margin: 0 0 0 -10px;
              } /* ltr */
              &::after {
                position: absolute;
                bottom: 5px;
                content: "";
                right: 10px;
                display: inline-block;
                width: 0;
                height: 0;
                border-style: solid;
                border-width: 10px 6px 0;
                border-color: #000 transparent transparent;
                line-height: 0;
                _border-color: #000 #fff #fff;
                margin: 0 auto;
                [dir="ltr"] & {
                  left: 10px;
                  right: auto;
                } /* ltr */
              } /* after */
            } /* span */
          } /* tooltip_cust */
          &:hover {
            .tooltip_cust {
              visibility: visible;
              opacity: 1;
            } /* tooltip_cust */
          } /* hover */
        } /* right_button */
        .center_button {
          width: 100%;
        } /* center_button */
        .left_button {
          margin: 0 5px 0 0;
          [dir="ltr"] & {
            margin: 0 0 0 5px;
          } /* ltr */
          span {
            height: 35px;
            width: 35px;
            background: #fff;
            border: 1px solid;
            border-radius: 5px;
            font-size: 20px;
            line-height: 35px;
            color: #0a80d8;
            display: block;
          } /* span */
        } /* left_button */
      } /* all_buttons_cases */
      .reservation_button {
        font-size: 17px;
        display: block;
        width: 100%;
        background: #ffffff;
        border: 1px solid;
        border-radius: 4px;
        height: 35px;
        line-height: 35px;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
      } /* reservation_button */
    } /* buttons_area */
    .online_awaiting_confirmation {
      position: absolute;
      right: 10px;
      top: 10px;
      z-index: 9;
      [dir="ltr"] & {
        left: 10px;
        right: auto;
      } /* ltr */
      .tooltip_online_awaiting_confirmation {
        position: absolute;
        top: -42px;
        z-index: 99;
        padding: 0 0 10px 0;
        right: -20px;
        visibility: hidden;
        opacity: 0;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        [dir="ltr"] & {
          left: -20px;
          right: auto;
        } /* ltr */
        span {
          display: table;
          white-space: nowrap;
          background: #000;
          border-radius: 4px;
          padding: 5px 10px;
          color: #fff;
          width: 100%;
          font-size: 14px;
          &::after {
            position: absolute;
            bottom: 3px;
            content: "";
            right: 26px;
            display: inline-block;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 10px 6px 0 6px;
            border-color: #000000 transparent transparent transparent;
            line-height: 0;
            _border-color: #000000 #ffffff #ffffff #ffffff;
            margin: 0 auto;
            [dir="ltr"] & {
              left: 26px;
              right: auto;
            } /* ltr */
          } /* after */
        } /* span */
      } /* tooltip_online_awaiting_confirmation */
      b {
        background: #9b59b6;
        border-radius: 100%;
        width: 25px;
        height: 25px;
        display: block;
        line-height: 25px;
        color: #fff;
        font-weight: 400;
        text-align: center;
        font-size: 15px;
      } /* b */
      &:hover {
        .tooltip_online_awaiting_confirmation {
          visibility: visible;
          opacity: 1;
        } /* tooltip_online_awaiting_confirmation */
      } /* hover */
    } /* online_awaiting_confirmation */
    .info_reserved {
      position: absolute;
      right: 10px;
      top: 10px;
      z-index: 9;
      [dir="ltr"] & {
        left: 10px;
        right: auto;
      } /* ltr */
      svg{
            // margin: 0 10px 0 0;
            path{
                fill: #555;
            }
      }
      button.info_button {
        display: block;
        height: 22px;
        width: 22px;
        background-size: 22px;
        background-repeat: no-repeat;
        background-position: center center;
        cursor: pointer;
      } /* info_button */
      .reserved_details {
        position: absolute;
        right: -10px;
        top: 100%;
        z-index: 99;
        padding: 10px 0 0 0;
        visibility: hidden;
        opacity: 0;
        [dir="ltr"] & {
          left: -10px;
          right: auto;
        } /* ltr */
        &::after {
          position: absolute;
          top: 3px;
          content: "";
          right: 15px;
          display: inline-block;
          width: 0;
          height: 0;
          border-style: solid;
          border-width: 0 6px 10px 6px;
          border-color: transparent transparent #000000 transparent;
          line-height: 0;
          _border-color:  #ffffff #ffffff #000000 #ffffff;
          margin: 0 auto;
          [dir="ltr"] & {
            left: 15px;
            right: auto;
          } /* ltr */
        } /* after */
        .reserved_details_inside {
          background: #000;
          border-radius: 5px;
          padding: 10px;
          min-width: 206px;
          color: #fff;
          text-align: initial;
          ul {
            white-space: nowrap;
            text-align: right;
            [dir="ltr"] & {
              text-align: left;
            } /* ltr */
            li {
              margin: 2px auto;
              font-size: 14px;
              span {
                direction: ltr;
                display: block;
              } /* span */
            } /* li */
          } /* ul */
        } /* reserved_details_inside */
      } /* reserved_details */
      &.active {
        .reserved_details {
          opacity: 1;
          visibility: visible;
        } /* reserved_details */
      } /* active */
    } /* info_reserved */
    .addons_unite {
      .addonsarea {
        position: absolute;
        left: 10px;
        top: 10px;
        z-index: 9;
        button {
          outline: none;
          border: none;
          svg {
            width: 22px;
            height: 22px;
            fill: #000000 !important;
            &.sv_cancel {display: none;}
          } /* svg */
        } /* button */
        [dir="ltr"] & {
          right: 10px;
          left: auto;
        } /* ltr */
      } /* addonsarea */
      .submenu {
        position: absolute;
        background: #fff;
        padding: 40px 0 0;
        left: 0;
        top: 1px;
        border-radius: 0.3rem;
        z-index: 1;
        opacity: 0;
        height: 0;
        visibility: hidden;
        right: 0;
        bottom: 0;
        overflow: hidden;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        ul {
          background: #fafafa;
          overflow: auto;
          max-height: 156px;
          scrollbar-width: thin;
          scrollbar-color: #bbb #ddd;
          &::-webkit-scrollbar {width: 6px;}
          &::-webkit-scrollbar-track {background: #dddddd;}
          &::-webkit-scrollbar-thumb {background: #bbbbbb;}
          &::-webkit-scrollbar-thumb:window-inactive {background: #bbbbbb;}
          a {
            display: block;
            text-align: right;
            font-size: 15px;
            line-height: 25px;
            border-top: 1px solid #ddd;
            padding: 5px;
            [dir="ltr"] & {
              text-align: left;
              font-size: 14px;
            } /* ltr */
            svg {
              height: 20px;
              width: 20px;
              float: right;
              margin: 2.5px auto 2.5px 8px;
              fill: #000;
              [dir="ltr"] & {
                float: left;
                margin: 2.5px 8px 2.5px auto;
              } /* ltr */
              g, path {fill: #000;}
            } /* svg */
            &:hover {background: #f0f0f0;}
          } /* a */
        } /* ul */
      } /* submenu */
      &.active {
        .addonsarea {
          svg {
            &.sv_addons {display: none;}
            &.sv_cancel {display: block;}
          } /* svg */
        } /* addonsarea */
        .submenu {
          opacity: 1;
          height: 100%;
          visibility: visible;
        } /* submenu */
      } /* active */
    } /* addons_unite */
    .control {
      .buttonarea {
        position: absolute;
        left: 10px;
        top: 10px;
        z-index: 9;
        button {
          outline: none;
          svg {
            width: 22px;
            height: 22px;
            fill: #000000 !important;
            &.sv_cancel {display: none;}
          } /* svg */
        } /* button */
        [dir="ltr"] & {
          left: auto;
          right: 10px;
        } /* ltr */
      } /* buttonarea */

      .showStatusIndicator {
        position: absolute;
        // left: 10px;
        top: 10px;
        z-index: 9;
        .circle-px{
          height: 10px;
          width: 10px;
          background-color: black;
          border-radius: 50%;
          display: inline-block;
          bottom: 0.23em;
          position: relative;
          margin-left: 0.1em;
          margin-right: 0.1em;
          }
        [dir="ltr"] & {
          left: auto;
          right: 10px;
        } /* ltr */
      } /* showStatusIndicator */
      .submenu {
        position: absolute;
        background: #fff;
        padding: 40px 0 0;
        left: 0;
        top: 1px;
        border-radius: 0.3rem;
        z-index: 1;
        opacity: 0;
        height: 0;
        visibility: hidden;
        right: 0;
        bottom: 0;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        ul {
          background: #fafafa;
          li {
            a {
              display: block;
              text-align: right;
              font-size: 15px;
              line-height: 25px;
              border-top: 1px solid #ddd;
              padding: 5px;
              [dir="ltr"] & {
                text-align: left;
              } /* ltr */
              svg {
                height: 20px;
                width: 20px;
                float: right;
                margin: 2.5px auto 2.5px 8px;
                [dir="ltr"] & {
                  float: left;
                  margin: 2.5px 8px 2.5px auto;
                } /* ltr */
              } /* svg */
              &:hover {background: #f0f0f0;}
            } /* a */
            &:last-child {
              a {border-bottom: 1px solid #ddd;}
            } /* last-child */
          } /* li */
        } /* ul */
      } /* submenu */
      &.active {
        button {
          svg.sv_setting {display: none;}
          svg.sv_cancel {display: block;}
        } /* button */
        .submenu {
          opacity: 1;
          height: 100%;
          visibility: visible;
        } /* submenu */
      } /* active */
    } /* control */
  } /* unit_item */
  .components {
    .services_statement {margin: 0 auto;padding: 0;}
  } /* components */
  .form-control {
    display: block;
    height: 40px;
    width: 100%;
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
  }
  .content_area {
    width: 100%;
    position: relative;
  }
  .content_area .icon {
    overflow: auto;
    position: absolute;
    margin: 0 auto;
    display: table;
    top: -15px;
  }
  .content_area textarea {
    min-height: 100px;
  }
  [dir="ltr"] .content_area .icon svg {
    -webkit-transform: rotatey(180deg);
    transform: rotatey(180deg);
  }
  .action_button {
    background: #d6d6d8;
    font-size: 17px;
    display: block;
    background: #ffffff;
    border: 1px solid;
    border-radius: 4px;
    height: 35px;
    -webkit-transition: all 0.2s ease-in-out;
    transition: all 0.2s ease-in-out;
  }
  .text-sky-400 {
    color: #38bdf8;
  }
} /* unit_col */

.well_sar_aligned{
  display: flex !important;
  justify-content: center !important;
}
</style>
