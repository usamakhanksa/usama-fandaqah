<template>
  <div class="integrations_page">
    <nav v-if="crumbs.length">
      <ul class="breadcrumbs">
        <li class="breadcrumbs__item" v-for="(crumb, i) in crumbs" :key="i" v-if="crumb.text != false">
          <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
        </li>
      </ul>
    </nav>
    <heading class="my-3">{{ __('Integrations') }}</heading>
    <div class="integrations_alert">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512.001 512.001" width="512" height="512" fill="#3362cc">
        <path
          d="M503.84 395.38L308.14 56.417C297.257 37.57 277.766 26.315 256 26.315s-41.257 11.254-52.14 30.102L8.16 395.377c-10.883 18.85-10.883 41.356 0 60.205s30.373 30.102 52.14 30.102h391.4c21.765 0 41.256-11.254 52.14-30.1s10.883-41.356 0-60.205zm-25.978 45.207c-5.46 9.458-15.24 15.104-26.162 15.104H60.3c-10.922 0-20.702-5.646-26.162-15.104s-5.46-20.75 0-30.208l195.7-338.962c5.46-9.458 15.24-15.104 26.16-15.104s20.7 5.646 26.16 15.104l195.7 338.962c5.46 9.458 5.46 20.75-.001 30.208zM241 176h29.996v149.982H241zm15 180c-11.027 0-19.998 8.97-19.998 19.998s8.97 19.998 19.998 19.998 19.998-8.97 19.998-19.998S267.027 356 256 356z" />
      </svg> {{ __('You can integrate with the following third parties') }} :
    </div>
    <div class="integrations_items">
      <integration-scth></integration-scth>
      <integration-shms></integration-shms>
      <integration-unifonic ></integration-unifonic>
       <jawaly-new  />
      <staah-channel-manager v-if="enable_staah" />
      <integration-zatca-phase-two />
    </div>
    <div class="flex flex-wrap justify-start">
      <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{
        __('Back')
      }}</button>
    </div>
  </div>
</template>

<script>
import jawalyNew from './integrations/jawaly-new.vue'
export default {
  name: 'integration',
  components : {
    jawalyNew
  },
  data() {
    return {
      settings: [],
      installed: false,
      reset: true,
      groupName: 'integration',
      crumbs: [],
      currentUserIsAdmin: false,
      current_user_id: Nova.app.user.id,
      enable_staah : Nova.app.currentTeam.enable_staah

    }
  },
  methods: {
    goBack() {
      this.$router.push({ path: '/settings' })
    },
    checkCurrentUserIsAdmin() {
      axios.get(`/apidata/user-is-admin?id=${this.current_user_id}`)
        .then(response => {
          this.currentUserIsAdmin = response.data.is_admin;
        })
    },

  },
  created() {
    this.checkCurrentUserIsAdmin();
  },
  mounted() {
    this.crumbs = [
      {
        text: 'Home',
        to: '/dashboards/main',
      },
      {
        text: 'Settings',
        to: '/settings',
      },
      {
        text: 'Integration',
        to: '#',
      }
    ];
  },

}
</script>

<style lang="scss">
.toasted-container .toasted a {
  font-size: 1rem !important;
  font-weight: bold !important;
  margin: 1px !important;
}

.integrations_page {
  nav {
    ul.breadcrumbs {
      padding: 0 !important;
    }
  }

  /* nav */
  .integrations_alert {
    background: #F2F6FF;
    border-radius: 4px;
    padding: 10px;
    display: flex;
    margin: 0 auto 15px;
    border: 1px solid #D6E2FD;
    color: #3362CC;
    font-size: 15px;
    align-items: center;
    justify-content: flex-start;

    svg {
      margin: 0 0 0 15px;
      width: 25px;
      height: auto;

      [dir="ltr"] & {
        margin: 0 15px 0 0;
      }

      /* ltr */
      @media (min-width: 320px) and (max-width: 480px) {
        width: 25px;
      }

      /* Mobile */
    }

    /* svg */
    @media (min-width: 320px) and (max-width: 480px) {
      padding: 10px;
      font-size: 15px;
    }

    /* Mobile */
  }

  /* integrations_alert */
  .integrations_items {
    align-self: stretch;
    margin: 0 -10px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));

    .integration_col {
      margin: 15px auto;
      padding: 0 10px;
      width: 100%;

      .integration_item {
        height: 100%;
        background: #ffffff;
        padding: 50px 10px 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
        position: relative;

        .status_label {
          position: absolute;
          left: 10px;
          top: 10px;

          label {
            display: block;
            font-size: 15px;
            border-radius: 4px;
            padding: 0 10px;
            height: 30px;
            line-height: 30px;
            color: #fff;

            &.notconnected {
              background: #E74444;
            }

            /* notconnected */
            &.connected {
              background: #20BA79;
            }

            /* connected */
          }

          /* label */
        }

        /* status_label */
        .imgthumb {
          height: 150px;
          line-height: 150px;
          display: block;
          margin: 0 auto 15px;
          text-align: center;

          img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            display: initial;
          }

          /* img */
          &.sure_bills {
            img {
              max-height: 100px;
            }

            /* img */
          }

          /* sure_bills */
        }

        /* imgthumb */
        .name {
          text-align: center;
          font-size: 17px;
          margin: 0 auto 5px;
          display: flex;
          align-items: center;
          justify-content: center;

          a {
            color: #0A80D8;
          }

          span {
            display: inline-block;
            margin: 0 10px 0 0;

            [dir="ltr"] & {
              margin: 0 0 0 10px;
            }

            /* ltr */
            a {
              display: block;
              height: 17px;
              width: 17px;
              background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 524.235 524.235' height='512px' viewBox='0 0 524.235 524.235' width='512px'%3E%3Cg%3E%3Cpath d='m262.118 0c-144.53 0-262.118 117.588-262.118 262.118s117.588 262.118 262.118 262.118 262.118-117.588 262.118-262.118-117.589-262.118-262.118-262.118zm17.05 417.639c-12.453 2.076-37.232 7.261-49.815 8.303-10.651.882-20.702-5.215-26.829-13.967-6.143-8.751-7.615-19.95-3.968-29.997l49.547-136.242h-51.515c-.044-28.389 21.25-49.263 48.485-57.274 12.997-3.824 37.212-9.057 49.809-8.255 7.547.48 20.702 5.215 26.829 13.967 6.143 8.751 7.615 19.95 3.968 29.997l-49.547 136.242h51.499c.01 28.356-20.49 52.564-48.463 57.226zm15.714-253.815c-18.096 0-32.765-14.671-32.765-32.765 0-18.096 14.669-32.765 32.765-32.765s32.765 14.669 32.765 32.765c0 18.095-14.668 32.765-32.765 32.765z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%231273EB'/%3E%3C/g%3E%3C/svg%3E%0A");
              background-repeat: no-repeat;
              background-size: 17px;
            }

            /* a */
            button {
              display: block;
              height: 17px;
              width: 17px;
              background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0'%3F%3E%3Csvg xmlns='http://www.w3.org/2000/svg' id='Capa_1' enable-background='new 0 0 524.235 524.235' height='512px' viewBox='0 0 524.235 524.235' width='512px'%3E%3Cg%3E%3Cpath d='m262.118 0c-144.53 0-262.118 117.588-262.118 262.118s117.588 262.118 262.118 262.118 262.118-117.588 262.118-262.118-117.589-262.118-262.118-262.118zm17.05 417.639c-12.453 2.076-37.232 7.261-49.815 8.303-10.651.882-20.702-5.215-26.829-13.967-6.143-8.751-7.615-19.95-3.968-29.997l49.547-136.242h-51.515c-.044-28.389 21.25-49.263 48.485-57.274 12.997-3.824 37.212-9.057 49.809-8.255 7.547.48 20.702 5.215 26.829 13.967 6.143 8.751 7.615 19.95 3.968 29.997l-49.547 136.242h51.499c.01 28.356-20.49 52.564-48.463 57.226zm15.714-253.815c-18.096 0-32.765-14.671-32.765-32.765 0-18.096 14.669-32.765 32.765-32.765s32.765 14.669 32.765 32.765c0 18.095-14.668 32.765-32.765 32.765z' data-original='%23000000' class='active-path' data-old_color='%23000000' fill='%231273EB'/%3E%3C/g%3E%3C/svg%3E%0A");
              background-repeat: no-repeat;
              background-size: 17px;
              outline: none;
            }

            /* button */
          }

          /* span */
        }

        /* name */
        .desc {
          display: block;
          text-align: center;
          margin: 0 auto 15px;
          font-size: 15px;
          color: #000;

          a {
            color: #0A80D8;
          }

          small {
            font-size: inherit;
            color: #0A80D8;
            cursor: pointer;
          }

          /* small */
        }

        /* desc */
        .low_balance {
          text-align: center;
          margin: 0 auto 5px;
          color: red;
          font-size: 15px;
        }

        /* low_balance */
        .date_integration {
          button {
            height: 35px;
            width: 100%;
            color: #ffffff;
            border-radius: 5px;
            padding: 0;
            cursor: pointer;
            -webkit-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;

            &.connect {
              background: #20BA79;

              &:hover {
                background: #11AB6A;
              }
            }

            /* connect */
            &.disconnect {
              background: #E74444;

              &:hover {
                background: #D93636;
              }
            }

            /* disconnect */
            &.more {
              background: #4b82bb;

              &:hover {
                background: #3d6894;
              }
            }

            /* more */
            &:disabled {
              cursor: not-allowed;
            }
          }

          /* button */
        }

        /* date_integration */
      }

      /* integration_item */
    }

    /* integration_col */
  }

  /* integrations_items */
}

/* integrations_page */
.tooltip {
  display: block !important;
  z-index: 10000;

  .tooltip-inner {
    background: black;
    color: white;
    border-radius: 4px;
    padding: 3px 10px;
    font-size: 15px;
  }

  .tooltip-arrow {
    width: 0;
    height: 0;
    border-style: solid;
    position: absolute;
    margin: 5px;
    border-color: black;
    z-index: 1;
  }

  &[x-placement^="top"] {
    margin-bottom: 10px;

    .tooltip-arrow {
      border-width: 5px 5px 0 5px;
      border-left-color: transparent !important;
      border-right-color: transparent !important;
      border-bottom-color: transparent !important;
      bottom: -5px;
      left: calc(50% - 5px);
      margin-top: 0;
      margin-bottom: 0;
    }
  }

  &[x-placement^="bottom"] {
    margin-top: 5px;

    .tooltip-arrow {
      border-width: 0 5px 5px 5px;
      border-left-color: transparent !important;
      border-right-color: transparent !important;
      border-top-color: transparent !important;
      top: -5px;
      left: calc(50% - 5px);
      margin-top: 0;
      margin-bottom: 0;
    }
  }

  &[x-placement^="right"] {
    margin-left: 5px;

    .tooltip-arrow {
      border-width: 5px 5px 5px 0;
      border-left-color: transparent !important;
      border-top-color: transparent !important;
      border-bottom-color: transparent !important;
      left: -5px;
      top: calc(50% - 5px);
      margin-left: 0;
      margin-right: 0;
    }
  }

  &[x-placement^="left"] {
    margin-right: 5px;

    .tooltip-arrow {
      border-width: 5px 0 5px 5px;
      border-top-color: transparent !important;
      border-right-color: transparent !important;
      border-bottom-color: transparent !important;
      right: -5px;
      top: calc(50% - 5px);
      margin-left: 0;
      margin-right: 0;
    }
  }

  &.popover {
    $color: #f9f9f9;

    .popover-inner {
      background: $color;
      color: black;
      padding: 24px;
      border-radius: 5px;
      box-shadow: 0 5px 30px rgba(black, .1);
    }

    .popover-arrow {
      border-color: $color;
    }
  }

  &[aria-hidden='true'] {
    visibility: hidden;
    opacity: 0;
    transition: opacity .15s, visibility .15s;
  }

  &[aria-hidden='false'] {
    visibility: visible;
    opacity: 1;
    transition: opacity .15s;
  }
}
</style>
