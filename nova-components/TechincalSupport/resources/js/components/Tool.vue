<template>
    <div id="technical_support_page">

         <nav v-if="crumbs.length" class="mb-5">
            <ul class="breadcrumbs">
                <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                    <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                </li>
            </ul>
        </nav>

        <div class="table_title">
        <create-ticket :highlights="highlights" />
        </div>
        <tickets-table />
        <heading class="mb-6">{{ __('Technical support') }}</heading>
        <card class="w-full bg-white rounded-lg p-8 shadow">
            <h1 class="text-lg block w-full mb-4">{{ __('Our valued customers,We would like to share with you the customer service number and email for Fandaqah') }}</h1>

            <!-- <div class="flex">
                <div data-v-040e2ab9="" class="h-16 w-16  text-center text-4xl pt-3 ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 52 52" style="enable-background:new 0 0 52 52;display: block;margin: 0 auto;" xml:space="preserve" width="45" height="45"><g><g>
                    <g>
                        <path d="M26,0C11.663,0,0,11.663,0,26c0,4.891,1.359,9.639,3.937,13.762C2.91,43.36,1.055,50.166,1.035,50.237    c-0.096,0.352,0.007,0.728,0.27,0.981c0.263,0.253,0.643,0.343,0.989,0.237L12.6,48.285C16.637,50.717,21.26,52,26,52    c14.337,0,26-11.663,26-26S40.337,0,26,0z M26,50c-4.519,0-8.921-1.263-12.731-3.651c-0.161-0.101-0.346-0.152-0.531-0.152    c-0.099,0-0.198,0.015-0.294,0.044l-8.999,2.77c0.661-2.413,1.849-6.729,2.538-9.13c0.08-0.278,0.035-0.578-0.122-0.821    C3.335,35.173,2,30.657,2,26C2,12.767,12.767,2,26,2s24,10.767,24,24S39.233,50,26,50z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#333B45"/>
                        <path d="M42.985,32.126c-1.846-1.025-3.418-2.053-4.565-2.803c-0.876-0.572-1.509-0.985-1.973-1.218    c-1.297-0.647-2.28-0.19-2.654,0.188c-0.047,0.047-0.089,0.098-0.125,0.152c-1.347,2.021-3.106,3.954-3.621,4.058    c-0.595-0.093-3.38-1.676-6.148-3.981c-2.826-2.355-4.604-4.61-4.865-6.146C20.847,20.51,21.5,19.336,21.5,18    c0-1.377-3.212-7.126-3.793-7.707c-0.583-0.582-1.896-0.673-3.903-0.273c-0.193,0.039-0.371,0.134-0.511,0.273    c-0.243,0.243-5.929,6.04-3.227,13.066c2.966,7.711,10.579,16.674,20.285,18.13c1.103,0.165,2.137,0.247,3.105,0.247    c5.71,0,9.08-2.873,10.029-8.572C43.556,32.747,43.355,32.331,42.985,32.126z M30.648,39.511    c-10.264-1.539-16.729-11.708-18.715-16.87c-1.97-5.12,1.663-9.685,2.575-10.717c0.742-0.126,1.523-0.179,1.849-0.128    c0.681,0.947,3.039,5.402,3.143,6.204c0,0.525-0.171,1.256-2.207,3.293C17.105,21.48,17,21.734,17,22c0,5.236,11.044,12.5,13,12.5    c1.701,0,3.919-2.859,5.182-4.722c0.073,0.003,0.196,0.028,0.371,0.116c0.36,0.181,0.984,0.588,1.773,1.104    c1.042,0.681,2.426,1.585,4.06,2.522C40.644,37.09,38.57,40.701,30.648,39.511z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#333B45"/>
                    </g>
                </g></g> </svg>
                </div>
                <div data-v-040e2ab9="" class="w-5/6">
                    <h2 data-v-040e2ab9="" class="text-lg mb-1 mt-2 transaction">{{ __('Connect with us through WhatsApp') }}</h2>
                    <div data-v-040e2ab9="" class="text-lg transaction text-right" style="direction: ltr;">
                        <a target="_blank" href="https://api.whatsapp.com/send?phone=966561187386">+966 56 118 7386</a>
                    </div>
                </div>

                <div data-v-040e2ab9="" class="w-5/6">
                    <h2 data-v-040e2ab9="" class="text-lg mb-1 mt-2 transaction">{{ __('Call us at :') }}</h2>
                    <div data-v-040e2ab9="" class="text-lg transaction text-right" style="direction: ltr;">
                        9200 66456
                    </div>
                </div>
            </div> -->

            <div class="footer_top mb-5">
        <div class="container">
          <div class="banner_media">
            <div class="item_24">

              <!-- <div class="item">
                <div class="icon icon_24">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#fa5a59" width="41" height="42" version="1.0" viewBox="0 0 1280 1280"><path d="M614.5.6c-21.6 1.3-39.1 2.8-53 4.5-99.5 12.2-192.7 46.7-276 102.2-90.7 60.4-164.8 143.3-214.6 240.2-39.5 76.9-61.5 154.9-69 245-1.7 19.3-1.7 75.7 0 95 7.5 90.1 29.5 168.1 69 245 61 118.7 157.9 215.6 276.6 276.6 76.9 39.5 154.9 61.5 245 69 19.3 1.7 75.7 1.7 95 0 90.1-7.5 168.1-29.5 245-69 118.7-61 215.6-157.9 276.6-276.6 39.5-76.9 61.5-154.9 69-245 1.7-19.3 1.7-75.7 0-95-7.5-90.1-29.5-168.1-69-245-61-118.7-157.9-215.6-276.6-276.6-75.3-38.7-153.1-61-239-68.4-16.4-1.5-66.6-2.7-79-1.9zm73.9 77.9c131 11.2 251.9 67.1 346.4 159.9 98 96.3 156.1 221 167.3 359.1 1.5 19.3 1.5 65.7 0 85-11 135-66.4 256.7-160.5 352.3-96.5 98.1-220.6 155.9-359.1 167.3-19.3 1.6-65.7 1.6-85 0-138.6-11.4-262.8-69.2-359.1-167.2-94.3-96.1-149.3-216.7-160.5-352.4-1.6-19.3-1.6-65.7 0-85 11.5-138.8 69.3-262.8 167.2-359.1C332.9 152.2 443.9 97.6 564 81.6c41.6-5.5 83.2-6.6 124.4-3.1z"/><path d="M506 206.2c-5.4 1.5-67.9 37.9-71.6 41.7-8.3 8.4-10.2 20.7-5 30.9 5.4 10.5 114.9 199.8 117.5 203.2 5.8 7.3 18.6 11.6 27.6 9.1 4.6-1.2 66.2-36.5 71.5-40.9 6.8-5.7 10.5-17.9 8.1-26.7-.6-2.2-27.4-49.7-59.6-105.5-47.4-82.2-59.4-102.3-63.1-105.7-6.8-6.4-16.4-8.7-25.4-6.1zM394.2 293.3c-21.4 13-32.1 20.8-43.5 31.7-33.4 31.9-42.3 63.6-36.6 129.5C322.3 548.7 369 674.6 439 791c65.7 109.2 144.6 199.6 217.5 249.2 37.5 25.5 60.8 34.3 91 34.3 20.2 0 37-4 60.6-14.5 11-5 36.5-18.5 37.4-19.9.3-.6-113.7-199.2-119.8-208.8-.6-1-1.7-.8-4.7.7-6.9 3.5-18.3 6.9-27.2 8-32.7 4-71.4-14.8-107.3-52.2-44.4-46.2-79.6-114.3-90.5-175.3-8.1-44.8-2-80.9 17.5-103.8 3.3-3.9 14.4-13.2 16.6-13.9.8-.3-21.7-40.1-59-104.9-33.2-57.4-60.6-104.7-61-105.1-.4-.5-7.5 3.4-15.9 8.5zM822 747c-5.5.7-4.6.3-41.3 21.4-16.4 9.4-31.4 18.6-33.3 20.4-7.2 6.9-10.2 18.5-7.2 27.3 1.7 5 115.5 202.6 119.7 207.8 7.1 9 21.2 11.7 32.6 6.2 8.6-4.1 61.4-34.7 65.5-38 5.6-4.5 9-12 9-20.2 0-3.7-.5-8.1-1.2-9.6-3.4-8-117.5-204.3-120.6-207.5-6.1-6.4-14.1-9.1-23.2-7.8z"/></svg>
                </div>
                <div>
                  <p>{{__('Call us at :')}}</p>
                  <h4 data-testid="ContactUs__Number">9200 66456</h4>
                </div>
              </div> -->



              <div class="item">
                <div class="icon icon_24">
                  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40">
                    <g fill="#23A5B9" fill-rule="evenodd">
                      <path fill-rule="nonzero"
                        d="M39.99 15.291a.63.63 0 0 0-.027-.135.667.667 0 0 0-.038-.115.598.598 0 0 0-.067-.102.629.629 0 0 0-.089-.103c-.01-.01-.015-.022-.026-.032L33.33 9.825v-5.16a2 2 0 0 0-2-2h-7.224L21.214.417a1.977 1.977 0 0 0-2.431 0l-2.894 2.246H8.665a2 2 0 0 0-2 2v5.161l-6.41 4.98c-.011.009-.016.021-.026.03a.625.625 0 0 0-.089.104.595.595 0 0 0-.067.102.658.658 0 0 0-.038.115.632.632 0 0 0-.027.133c0 .014-.008.026-.008.04v22.669c.001.424.138.836.391 1.176.004.006.005.014.01.019.004.005.014.011.021.019a1.99 1.99 0 0 0 1.576.786h36a1.99 1.99 0 0 0 1.581-.79c.006-.006.014-.008.018-.015.005-.007.006-.013.01-.019.253-.34.39-.752.391-1.176V15.33c0-.014-.007-.026-.008-.04zM19.598 1.471a.64.64 0 0 1 .795 0l1.538 1.193h-3.866l1.533-1.194zM2.166 38.663l17.432-13.54a.64.64 0 0 1 .795 0l17.437 13.54H2.166zm36.499-1.039L21.214 24.072a1.978 1.978 0 0 0-2.431 0L1.33 37.625V16.346l10.925 8.484a.667.667 0 1 0 .817-1.054L1.975 15.158l4.69-3.645v5.151a.667.667 0 0 0 1.333 0v-12c0-.368.298-.666.667-.666H31.33c.368 0 .667.298.667.666v12a.667.667 0 1 0 1.333 0v-5.151l4.69 3.645-11.118 8.633a.667.667 0 1 0 .818 1.053l10.944-8.498v21.279z" />
                      <path
                        d="M27.998 15.998V13.33a8 8 0 1 0-8 8 .667.667 0 1 0 0-1.333 6.667 6.667 0 1 1 6.667-6.667v2.667a1.333 1.333 0 1 1-2.667 0V13.33a.667.667 0 1 0-1.333 0 2.667 2.667 0 1 1-2.667-2.667.667.667 0 1 0 0-1.333 4 4 0 1 0 2.695 6.948 2.66 2.66 0 0 0 5.305-.281z" />
                    </g>
                  </svg>
                </div>
                <div>
                  <p>{{__('Or you can email us at:')}}</p>
                  <h4><a href="mailto:service@fandaqah.com">service@fandaqah.com</a></h4>
                </div>
              </div>


              <!-- <div class="item">
                <div class="icon icon_whatsapp">
                  <svg xmlns="http://www.w3.org/2000/svg" width="41" height="41">
                    <g fill="none" fill-rule="nonzero" stroke="#fa5a59" stroke-width="1.5">
                      <path
                        d="M34.455 6.463a19.807 19.807 0 0 1 4.228 6.193 18.011 18.011 0 0 1 1.495 7.218c0 3.416-.869 6.577-2.606 9.48a19.422 19.422 0 0 1-7.004 6.92c-2.932 1.708-6.107 2.562-9.524 2.562-3.245 0-6.263-.769-9.054-2.306L1.911 39.178l2.733-9.823c-1.708-2.904-2.562-6.065-2.562-9.481 0-3.417.854-6.578 2.562-9.482a19.187 19.187 0 0 1 6.92-6.918C14.466 1.765 17.627.91 21.043.91c2.506 0 4.912.484 7.218 1.452a19.904 19.904 0 0 1 6.193 4.1Z" />
                      <path
                        d="m29.671 23.803.257.17c.398.171.626.314.683.428.057.114.071.398.043.854a4.425 4.425 0 0 1-.342 1.41c-.2.483-.655.967-1.367 1.451-.711.484-1.324.755-1.836.812a7.824 7.824 0 0 1-2.306 0c-.854-.171-1.993-.57-3.417-1.196-2.904-1.253-5.552-3.588-7.944-7.004l-.17-.171c-1.253-1.765-1.88-3.388-1.88-4.869 0-1.48.513-2.762 1.538-3.844l.085-.085c.399-.399.826-.598 1.281-.598h1.025c.228 0 .413.043.556.128.142.086.27.3.384.64l1.452 3.503c.171.342.2.626.086.854a5.882 5.882 0 0 1-1.11 1.538c-.229.227-.371.412-.428.555-.057.142 0 .299.17.47.798 1.48 1.738 2.647 2.82 3.502.797.626 1.993 1.338 3.587 2.135.456.228.797.2 1.025-.085.74-.855 1.253-1.48 1.538-1.88.114-.227.256-.341.427-.341.17 0 .37.028.598.085.455.171 1.537.684 3.245 1.538Z" />
                    </g>
                  </svg>
                </div>
                <div>
                  <p>{{__('Get support via WhatsApp:')}}</p>
                  <h4><a target="_blank" href="https://wa.me/966920066456">9200 66456</a></h4>
                </div>
              </div> -->

            </div>
          </div><!-- end social_media -->
        </div><!-- end container -->
    </div>
        </card>
    </div>
</template>

<script>
import CreateTicket from './create';
import TicketsTable from './ticketsTable.vue';

export default {
        components : {
        CreateTicket,
        TicketsTable
    },
    created() {
        this.crumbs = [
                {
                    text: 'Home',
                    to: '/dashboards/main',
                },
                {
                    text: this.__('Technical support'),
                    to: '#',
                },
            ]
    },
    data() {
        return {
            crumbs: [],
        }
    },
}
</script>



<style lang="scss" scoped>
    .footer_top {
		background: #ffffff;
		padding: 12px 0;
		border: 1px solid #e3e3e3;
        border-radius: 5px;
		.banner_media {
				display: flex;
				justify-content: space-between;

				@media (max-width: 767.98px) {
						flex-direction: column;
						gap: 1rem;
				}

				.help_item {

							display: flex;
							flex-direction: column;
							flex-shrink: 0;

					h4 {
						font-weight: bold;
						font-size: 24px;
						color: rgb(51, 51, 51);
						margin-bottom: 0px;
					}
					p{
						font-weight: normal;
						font-size: 12px;
						color: rgb(119, 119, 119);
						margin-bottom: 0px;
					}
				}

				.item_24 {
					display: -webkit-box;
					display: -ms-flexbox;
					display: flex;
					flex-grow: 1;
					justify-content: flex-end;

					@media (max-width: 767.98px) {
							flex-direction: column;
							align-items: start;
							gap: 1rem;
						}

						.item {

									display: flex;
									padding: 0 20px;
									justify-content: center;
									align-items: center;
									border-inline-end: 2px solid #bdbdbd;
									flex-grow: 1;
									gap: 1rem;

									@media (max-width: 767.98px) {
											border-inline-end : none;
											padding: 0;
										}

								.icon {
									&.icon_24 {
										g{
											fill: #fa5a59;
										}
									}
								}

								p{
									font-size: 15px;
										color: rgb(119, 119, 119);
										font-weight: normal;
										margin-bottom: 0px;
								}

								h4{
									font-weight: bold;
										font-size: 21px;
										color: rgb(51, 51, 51);
										margin-bottom: 0px;
										direction: ltr;
										a {
											text-decoration: none;
											color: rgb(51, 51, 51);
										}
								}
						}
						.item:last-child {
							border: medium none;
						}
				}
			}

			/* banner media */
		.social_media {
			display: flex;
			justify-content: center;
			align-items: center;
			a {
				background: #fff;
				border: 1px solid #eaeaea;
				display: block;
				color: #858a99;
				height: 40px;
				width: 40px;
				border-radius: 1px;
				line-height: 40px;
				margin: 5px 7px;
				text-align: center;
				font-size: 17px;
				&:hover {
					color: #ffffff;
					&.facebook {
						background: #3b5998;
						border-color: #3b5998;
          } /* facebook */
          &.youtube {
						background: #d71e18;
						border-color: #d71e18;
					} /* youtube */
					&.twitter {
						background: #55acee;
						border-color: #55acee;
					} /* twitter */
					&.snapchat {
						background: #fffc00;
						border-color: #fffc00;
					} /* snapchat */
					&.instagram {
						background: #125688;
						border-color: #125688;
					} /* instagram */
				} /* hover */
			} /* a */
		} /* social_media */
	} /* footer_top */
</style>

<style lang="scss">
   .table_title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      span.table {
        display: block;
        font-size: 15px;
        margin: 0 0 15px;
        color: #000;
        p {
          display: inline-block;
          margin: 0 5px 0 0;
          font-weight: bold;
          font-size: 16px;
        } /* p */
      } /* span */
      button.table {
        display: block;
        background-color: #4099de;
        font-size: 15px;
        padding: 0 20px;
        height: 35px;
        border-radius: 4px !important;
        line-height: 35px;
        color: #fff;
        cursor: pointer;
        outline: none;
        font-weight: normal !important;
        margin: 0 0 15px;
        @media (min-width: 320px) and (max-width: 767px) {
          width: auto;
          &::before {display: none;}
        } /* media */
        &:hover {
          background-color: #0071C9;
        } /* hover */
      } /* a */
    } /* table_title */
    .customer-label {
        font-size: 14px;
        padding: 0 .5rem;
        border-radius: .25rem;
        min-width: 60px;
    }
.Edit_Transaction_Modal {
    .sweet-content {
        overflow: auto;
        max-height: 500px;
        display: block;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f5f5f5;
        &::-webkit-scrollbar {width: 6px;}
        &::-webkit-scrollbar-track {background: #f5f5f5;}
        &::-webkit-scrollbar-thumb {background: #ccc;}
        &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    } /* sweet-content */
    .formgroup {
        display: block;
        margin: 0 auto 10px;
    } /* formgroup */
    .row_group {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin: 0 -10px;
        @media (min-width: 320px) and (max-width: 767px) {
            margin: 0;
        } /* Mobile */
        .col {
            width: 50%;
            padding: 0 10px;
            margin: 0 0 10px;
            @media (min-width: 320px) and (max-width: 767px) {
                width: 100%;
                padding: 0;
            } /* Mobile */
        } /* col */
    } /* row_group */
    label {
        display: block;
        margin: 0 auto 5px;
        font-size: 15px;
        span {
            display: inline-block;
            margin: 0 5px 0 0;
            color: #f00;
            [dir="ltr"] & {
                margin: 0 0 0 5px;
            } /* ltr */
        } /* span */
    } /* label */
    input {
        height: 40px !important;
        padding: 0 10px !important;
        color: #000 !important;
        font-size: 15px !important;
        border: 1px solid #dddddd !important;
        background: #fafafa !important;
        width: 100% !important;
        &[readonly="readonly"] {
            cursor: pointer;
        } /* readonly */
    } /* input */
    select {
        background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3C!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0) --%3E%3Csvg version='1.1' id='Layer_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' viewBox='0 0 491.996 491.996' style='enable-background:new 0 0 491.996 491.996;' xml:space='preserve'%3E%3Cg%3E%3Cg%3E%3Cpath d='M484.132,124.986l-16.116-16.228c-5.072-5.068-11.82-7.86-19.032-7.86c-7.208,0-13.964,2.792-19.036,7.86l-183.84,183.848 L62.056,108.554c-5.064-5.068-11.82-7.856-19.028-7.856s-13.968,2.788-19.036,7.856l-16.12,16.128 c-10.496,10.488-10.496,27.572,0,38.06l219.136,219.924c5.064,5.064,11.812,8.632,19.084,8.632h0.084 c7.212,0,13.96-3.572,19.024-8.632l218.932-219.328c5.072-5.064,7.856-12.016,7.864-19.224 C491.996,136.902,489.204,130.046,484.132,124.986z'/%3E%3C/g%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3Cg%3E%3C/g%3E%3C/svg%3E%0A");
        width: 100%;
        height: 40px !important;
        padding: 0 10px !important;
        background-color: #fafafa !important;
        border: 1px solid #ddd !important;
        color: #000;
        font-size: 15px;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        -webkit-appearance: none;
        -moz-appearance: none;
        -o-appearance: none;
        appearance: none;
        border-radius: 5px !important;
        background-position: 15px center;
        background-repeat: no-repeat;
        background-size: 14px;
    } /* select */
    button {
        background: #4099de;
        border-radius: 5px;
        border: 1px solid #4099de;
        min-width: 100px;
        height: 35px;
        line-height: 35px;
        font-size: 15px;
        padding: 0 15px;
        color: #ffffff;
        width: 100%;
        margin: 0 auto 10px;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        &:hover {
            background: #0071C9;
            border-color: #0071C9;
        } /* hover */
    } /* button */
} /* Edit_Transaction_Modal */
.Results_area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    p {
        display: block;
        margin: 10px 0 0;
        font-size: 15px;
        color: #000;
    } /* p */
} /* Results_area */
#customer_management_page {
    margin: 10px auto 0;
    border: 1px solid #ddd;
    border-radius: .5rem;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    .title{
        background: #f7fafc;
        border-bottom: 1px solid #ddd;
        padding: .75rem;
        color: #000;
        font-size: 1.125rem;
        display: block;
    } /* title */
    .content_page {
        background: #fff;
        padding: 10px;
        .filter_area {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-start;
            margin: 0 -10px;
            .item {
                width: 16.66666%;
                padding: 0 10px;
                margin: 0 0 10px;
                @media (min-width: 320px) and (max-width: 480px) {
                    width: 50%;
                } /* media */
                @media (min-width: 481px) and (max-width: 767px) {
                    width: 33.33333%;
                } /* media */
                @media (min-width: 768px) and (max-width: 991px) {
                    width: 25%;
                } /* media */
                input {
                    background: #fafafa;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                } /* input */
                select {
                    background-color: #fafafa;
                    background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.1' id='Layer_1' x='0px' y='0px' viewBox='0 0 512.011 512.011' style='enable-background:new 0 0 512.011 512.011;' xml:space='preserve' width='512px' height='512px' class=''%3E%3Cg%3E%3Cg%3E%3Cg%3E%3Cpath d='M505.755,123.592c-8.341-8.341-21.824-8.341-30.165,0L256.005,343.176L36.421,123.592c-8.341-8.341-21.824-8.341-30.165,0 s-8.341,21.824,0,30.165l234.667,234.667c4.16,4.16,9.621,6.251,15.083,6.251c5.462,0,10.923-2.091,15.083-6.251l234.667-234.667 C514.096,145.416,514.096,131.933,505.755,123.592z' data-original='%23000000' class='active-path' fill='%23000000'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E%0A");
                    background-repeat: no-repeat;
                    background-size: 14px;
                    background-position: 10px center;
                    height: 40px;
                    padding: 0 10px;
                    font-size: 15px;
                    border: 1px solid #ddd !important;
                    color: #000;
                    width: 100%;
                    border-radius: 4px !important;
                    outline: none;
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    -o-appearance: none;
                    appearance: none;
                } /* select */
            } /* item */
            .reset_filters {
                width: 100%;
                display: flex;
                padding: 0 10px;
                justify-content: flex-end;
                button {
                    height: 40px;
                    width: 40px;
                    background-color: #718096;
                    border-radius: 4px;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16.866' height='18.447' viewBox='0 0 16.866 18.447'%3E%3Cg transform='translate(0 0)'%3E%3Cpath d='M24.417,3.658a7.354,7.354,0,0,1,9.56-.252l-2.189.083a.509.509,0,0,0,.019,1.017h.019l3.36-.124a.508.508,0,0,0,.49-.509v-.06h0L35.552.49a.509.509,0,1,0-1.017.038l.079,2.083A8.364,8.364,0,0,0,23.735,2.9a8.367,8.367,0,0,0-2.516,8.178.506.506,0,0,0,.493.388.441.441,0,0,0,.121-.015.509.509,0,0,0,.373-.614A7.349,7.349,0,0,1,24.417,3.658Z' transform='translate(-20.982 0)' fill='%23ffffff'/%3E%3Cpath d='M91.8,185.6a.508.508,0,1,0-.987.241,7.348,7.348,0,0,1-11.832,7.387l2.215-.2a.509.509,0,1,0-.094-1.013l-3.349.3a.508.508,0,0,0-.46.554l.3,3.349a.508.508,0,0,0,.5.463.183.183,0,0,0,.045,0,.508.508,0,0,0,.46-.554l-.181-2.038a8.308,8.308,0,0,0,4.833,1.842c.143.008.286.011.426.011A8.365,8.365,0,0,0,91.8,185.6Z' transform='translate(-75.175 -178.237)' fill='%23ffffff'/%3E%3C/g%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: center center;
                    background-size: 20px;
                    -webkit-transition: all 0.2s ease-in-out;
                    -moz-transition: all 0.2s ease-in-out;
                    -o-transition: all 0.2s ease-in-out;
                    transition: all 0.2s ease-in-out;
                    &:hover {
                        background-color: #5E6D83;
                    } /* hover */
                } /* button */
            } /* reset_filters */
        } /* filter_area */
        hr {
            margin: 15px auto;
            border-color: #ddd;
        } /* hr */
        .statistics_area {
            ul {
                display: flex;
                align-items: flex-start;
                justify-content: flex-start;
                flex-wrap: wrap;
                margin: 0 -10px;
                li {
                    width: 20%;
                    padding: 0 10px;
                    @media (min-width: 320px) and (max-width: 480px) {
                        width: 50%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 481px) and (max-width: 767px) {
                        width: 33.33333%;
                        margin: 5px 0;
                    } /* media */
                    @media (min-width: 768px) and (max-width: 991px) {
                        width: 25%;
                        margin: 5px 0;
                    } /* media */
                    span {
                        display: block;
                        font-size: 15px;
                        color: #000;
                        margin: 0 0 5px;
                    } /* span */
                    p {
                        display: block;
                        font-size: 16px;
                        font-weight: bold;
                        line-height: 1.2;
                        &.totalDebtor {
                            color: #f56565;
                        } /* totalDebtor */
                        &.totalCreditor {
                            color: #48bb78;
                        } /* totalCreditor */
                    } /* p */
                } /* li */
            } /* ul */
        } /* statistics_area */
        .action_buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin: 0 0 10px;
            button.add_receipts {
                display: block;
                background: #4099de;
                border: none;
                border-radius: 4px;
                color: #fff;
                font-size: 15px;
                padding: 5px 15px;
                &:hover {
                    background: #0071C9;
                } /* hover */
            } /* add_receipts */
            .buttons_area {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                button {
                    display: block;
                    height: 30px;
                    width: 140px;
                    margin: 0 10px 0 0;
                    outline: none;
                    background-position: center center;
                    background-size: 25px;
                    background-repeat: no-repeat;
                    &.excel_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23.308' height='23.308' viewBox='0 0 23.308 23.308'%3E%3Cpath d='M24.213,3H16V5.675h2.717V7.5H16V9.275h2.689v1.793H16v1.793h2.689v1.793H16v1.793h2.689V18.24H16v2.689h8.213a.768.768,0,0,0,.751-.78V3.78A.768.768,0,0,0,24.213,3ZM23.172,18.24H19.586V16.447h3.586Zm0-3.586H19.586V12.861h3.586Zm0-3.586H19.586V9.275h3.586Zm0-3.586H19.586V5.689h3.586Z' transform='translate(-1.657 -0.311)' fill='%23333b45'/%3E%3Cpath d='M0,2.59V20.719l13.447,2.589V0ZM8.505,16.208,6.941,13.25a2.623,2.623,0,0,1-.184-.608H6.733a4.6,4.6,0,0,1-.21.634l-1.57,2.931H2.516l2.894-4.54L2.763,7.128H5.251l1.3,2.723a4.756,4.756,0,0,1,.273.766h.025q.077-.266.285-.792l1.443-2.7h2.279l-2.723,4.5,2.8,4.578Z' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* excel_button */
                    &.print_button {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='19.339' height='19.339' viewBox='0 0 19.339 19.339'%3E%3Cpath d='M17.471,17.471v1.934a1.934,1.934,0,0,1-1.934,1.934H7.8a1.934,1.934,0,0,1-1.934-1.934V17.471H3.934A1.934,1.934,0,0,1,2,15.537v-5.8A1.94,1.94,0,0,1,3.934,7.8H5.868V3.934A1.94,1.94,0,0,1,7.8,2h7.735a1.934,1.934,0,0,1,1.934,1.934V7.8H19.4a1.934,1.934,0,0,1,1.934,1.934v5.8A1.934,1.934,0,0,1,19.4,17.471Zm0-1.934H19.4v-5.8H3.934v5.8H5.868V13.6A1.94,1.94,0,0,1,7.8,11.669h7.735A1.934,1.934,0,0,1,17.471,13.6ZM15.537,7.8V3.934H7.8V7.8ZM7.8,13.6v5.8h7.735V13.6Z' transform='translate(-2 -2)' fill='%23333b45'/%3E%3C/svg%3E");
                    } /* print_button */
                } /* button */
            } /* buttons_area */
        } /* action_buttons */
        .table_area {
            .no_data_show {
                text-align: center;
                padding: 50px 15px 40px;
                svg {
                    display: block;
                    margin: 0 auto 15px;
                } /* svg */
                span {
                    display: block;
                    font-size: 15px;
                    text-align: center;
                    color: #000;
                } /* span */
            } /* no_data_show */
            .table-responsive {
                width: 100%;
                margin: 0 auto 20px;
                position: relative;
                @media (min-width: 320px) and (max-width: 991px) {
                    overflow: auto;
                } /* media */
                table {
                    width: 100%;
                    border: 1px solid #e2e8f0;
                    display: table;
                    thead {
                        tr {
                            th {
                                padding: 10px 5px;
                                line-height: 20px;
                                font-weight: normal;
                                font-size: 15px;
                                border: 1px solid #5E697C;
                                vertical-align: middle;
                                text-align: center !important;
                                color: #ffffff;
                                background: #4a5568;
                            } /* th */
                        } /* tr */
                    } /* thead */
                    tbody {
                        tr {
                            td {
                                text-align: center !important;
                                // padding: 15px 5px;
                                vertical-align: middle;
                                line-height: 10px;
                                font-size: 15px;
                                border: 1px solid #ced4dc;
                                color: #000000;
                                font-weight: normal;
                                // height: auto;
                                background: #ffffff;
                                &.td-fit {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-right: none;
                                    border-bottom: none;
                                    a, button {
                                        color: #b3b9bf;
                                        margin: 0 5px !important;
                                        outline: none;
                                        svg {
                                            path {
                                                fill: #b3b9bf;
                                                &:hover {fill: #3d92d4;}
                                            }
                                        }
                                        &:hover {color: #3d92d4;}
                                    } /* a */
                                } /* td-fit */
                                .text-left {
                                    text-align: center !important;
                                } /* text-left */
                            } /* td */
                        } /* tr */
                    } /* tbody */
                } /* table */
            } /* table-responsive */
        } /* table_area */
    } /* content_page */
} /* customer_management_page */

</style>

