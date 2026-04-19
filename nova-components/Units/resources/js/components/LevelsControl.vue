<template>
  <div id="levels_control">
    <bread-crumb />
		<p class="mb-3 text-90 font-normal text-2xl">{{__('Levels Control')}}</p>
    <div class="button_area">
      <button type="button">{{__('Add a new level')}}</button>
    </div><!-- button_area -->

  </div><!-- levels control -->
</template>

<script>
    import moment from "moment";
    import BreadCrumb from './BreadCrumb';
    export default {
        name: "LevelsControl",
        components : {
            BreadCrumb
        },
        data(){
            return {

            }
        },
        methods : {

        },
        watch: {

        },
        created(){

        }
    }
</script>

<style lang="scss">
  #levels_control {
    .button_area {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      margin: 0 auto 10px;
      button {
        display: block;
        margin: 0 15px 10px 0;
        background: #4099de;
        color: #fff;
        border-radius: 4px;
        padding: 8px 15px;
        font-size: 15px;
        border: none;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        &:first-child {
        margin: 0 0 10px 0;
        } /* first-child */
        &:hover {
          background: #0071C9;
        } /* hover */
      } /* button */
    } /* button_area */
    .table_area {
      margin: 0 auto 30px;
      border: 1px solid #ddd;
      border-radius: .5rem;
      box-shadow: 0 2px 4px 0 rgba(0,0,0,.05);
      overflow: hidden;
      .title {
        background: #f7fafc;
        border-bottom: 2px solid #ddd;
        padding: .75rem;
        color: #000;
        font-size: 1.125rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        p {
          display: block;
          font-size: 1.02rem;
        } /* p */
      } /* title */
      .table_content {
        background: #fff;
        padding: 10px;
        .table_responsive {
          @media (min-width: 320px) and (max-width: 767px) {
            overflow: auto;
          } /* media */
          table {
            width: 100%;
            @media (min-width: 320px) and (max-width: 767px) {
              margin: 0 auto 15px;
            } /* media */
            thead {
              th {
                background: #4a5568;
                border: 1px solid #5E697C;
                font-size: 15px;
                padding: 10px;
                vertical-align: middle;
                color: #fff;
                font-weight: normal;
                text-align: center;
              } /* th */
            } /* thead */
            tbody {
              td {
                background: #fafafa;
                border: 1px solid #d3d3d3;
                color: #000;
                vertical-align: middle;
                padding: 10px;
                font-size: 15px;
                line-height: 20px;
                text-align: center;
                span {
                  display: inline-block;
                  position: relative;
                  &::after {
                    content: "";
                    width: 11px;
                    height: 11px;
                    border-radius: 100%;
                    float: right;
                    margin: 6px 0 0 10px;
                  } /* after */
                  &.enabled {
                    &::after {
                      background: green;
                    } /* after */
                  } /*enabled  */
                  &.not_enabled {
                    &::after {
                      background: #ff0000;
                    } /* after */
                  } /*not_enabled  */
                } /* span */
                .units {
                  p {
                    display: block;
                    margin: 0 auto 5px;
                    &:last-child {
                      margin: 0;
                    } /* last-child */
                  } /* p */
                } /* units */
                .action {
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  flex-wrap: wrap;
                  a, button {
                    margin: 5px;
                    color: #777777;
                  } /* a */
                } /* action */
              } /* td */
            } /* tbody */
          } /* table */
        } /* table_responsive */
        .pagination {
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          justify-content: center;
          margin: 10px auto;
          .page-item {
            .page-link {
              position: relative;
              display: block;
              padding: .5rem .75rem;
              margin-left: -1px;
              line-height: 1.25;
              color: #007bff;
              background-color: #fff;
              border: 1px solid #dee2e6;
              &:hover {
                z-index: 2;
                color: #0056b3;
                text-decoration: none;
                background-color: #e9ecef;
                border-color: #dee2e6;
              } /* hover */
              &:focus {
                z-index: 2;
                outline: 0;
                box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
              } /* focus */
            } /* page-link */
            &:first-child {
              .page-link {
                margin-left: -1px;
                border-top-right-radius: .25rem;
                border-bottom-right-radius: .25rem;
              } /* page-link */
            } /* first-child */
            &:last-child {
              .page-link {
                border-top-left-radius: .25rem;
                border-bottom-left-radius: .25rem;
              } /* page-link */
            } /* first-child */
            &.active {
              .page-link {
                z-index: 1;
                color: #fff;
                background-color: #007bff;
                border-color: #007bff;
              } /* page-link */
            } /* active */
            &.disabled {
              .page-link {
                color: #6c757d;
                pointer-events: none;
                cursor: auto;
                background-color: #fff;
                border-color: #dee2e6;
              } /* page-link */
            } /* active */
          } /* page-item */
        } /* pagination */
      } /* table_content */
    } /* table_area */
  } /* special_prices_offers */
  .Add_new_offer_modal {
    .sweet-content {
      overflow: auto;
      max-height: 500px;
      display: block !important;
      line-height: normal !important;
      box-sizing: border-box !important;
      margin: 0 !important;
      transform: translateY(0px) !important;
      width: auto !important;
      padding: 15px !important;
      scrollbar-width: thin;
      scrollbar-color: #ccc #f5f5f5;
      &::-webkit-scrollbar {width: 6px;}
      &::-webkit-scrollbar-track {background: #f5f5f5;}
      &::-webkit-scrollbar-thumb {background: #ccc;}
      &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
    } /* sweet-content */
    .form_group {
      display: block;
      margin: 0 auto 15px;
    } /* form_group */
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
        margin: 0 auto 15px;
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
    .all_units {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      flex-wrap: wrap;
      background: #fafafa;
      border-radius: 5px;
      padding: 15px 15px 0px 15px;
      border: 1px solid #dddddd;
      label {
        display: block;
        width: 33.3333%;
        position: relative;
        padding-right: 30px;
        margin: 0 0 15px;
        cursor: pointer;
        color: #7E8790;
        line-height: 40px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-transition: all 0.2s ease-in-out;
        -moz-transition: all 0.2s ease-in-out;
        -o-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        [dir="ltr"] & {
          padding: 0 0 0 30px;
        } /* rtl */
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
          margin-bottom: 10px;
        } /* @media */
        &:hover {
          .checkmark {background: #e8e8e8;}
          p {color: #6C7180;}
        } /* hover */
        input {
          position: absolute;
          opacity: 0;
          cursor: pointer;
          height: 0;
          width: 0;
          &:checked ~ {
            .checkmark {
              background: #0A80D8;
              &::after {
                opacity: 1;
                visibility: visible;
                -webkit-transform: scale(1);
                -moz-transform: scale(1);
                -o-transform: scale(1);
                transform: scale(1);
              } /* after */
            } /* checkmark */
            p {color: #0A80D8;}
          } /* checked */
        } /* input */
        .checkmark {
          position: absolute;
          top: -1px;
          right: 0;
          height: 20px;
          width: 20px;
          background-color: #ffffff;
          border: 1px solid #dedede;
          -webkit-transition: all 0.2s ease-in-out;
          -moz-transition: all 0.2s ease-in-out;
          -o-transition: all 0.2s ease-in-out;
          transition: all 0.2s ease-in-out;
          [dir="ltr"] & {
            right: auto;
            left: 0;
          } /* rtl */
          &::after {
            content: "";
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='22.681' height='16.867' viewBox='0 0 22.681 16.867'%3E%3Cpath d='M143.914,64.664a1.147,1.147,0,0,0-1.622,0L128.526,78.392,123.559,73a1.147,1.147,0,0,0-1.688,1.554l5.775,6.272a1.146,1.146,0,0,0,.82.37h.025a1.149,1.149,0,0,0,.81-.335l14.611-14.572A1.147,1.147,0,0,0,143.914,64.664Z' transform='translate(-121.568 -64.327)' fill='%23fff'/%3E%3C/svg%3E");
            background-size: 13px;
            background-repeat: no-repeat;
            background-position: center 4px;
            position: absolute;
            top: 0;
            right: 0;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            font-size: 11px;
            color: #ffffff;
            opacity: 0;
            visibility: hidden;
            -webkit-transform: scale(0);
            -moz-transform: scale(0);
            -o-transform: scale(0);
            transform: scale(0);
            -webkit-transition: all 0.2s ease-in-out;
            -moz-transition: all 0.2s ease-in-out;
            -o-transition: all 0.2s ease-in-out;
            transition: all 0.2s ease-in-out;
            [dir="ltr"] & {
              right: auto;
              left: 0;
            } /* rtl */
          } /* after */
        } /* checkmark */
        p {
          display: block;
          line-height: 20px;
          font-size: 16px;
          color: #000;
          -webkit-transition: all 0.2s ease-in-out;
          -moz-transition: all 0.2s ease-in-out;
          -o-transition: all 0.2s ease-in-out;
          transition: all 0.2s ease-in-out;
        } /* p */
      } /* label */
      &.last {
        label {
          &:first-child {
            width: 100%;
          } /* first-child */
        } /* label */
      } /*  */
    } /* all_units */
    .switch_group {
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      flex-wrap: wrap;
      label {
        margin: 0;
        min-width: 100px;
      } /* label */
      .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 26px;
        input {
          opacity: 0;
          width: 100% !important;
          height: 100% !important;
          z-index: 99;
          position: relative;
          cursor: pointer;
          &:checked ~ {
            .slider {
              background-color: #21b978;
              &:before {
                -webkit-transform: translateX(33px);
                -ms-transform: translateX(33px);
                transform: translateX(33px);
              } /* before */
            } /* slider */
          } /* checked */
          &:focus {
            .slider {
              box-shadow: 0 0 1px #21b978;
            } /* slider */
          } /* focus */
        } /* input */
        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
          &:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
          } /* before */
          &.round {
            border-radius: 34px;
            &:before {
              border-radius: 50%;
            } /* before */
          } /* round */
        } /* slider */
      } /* switch */
    } /* switch_group */
    .days_prices {
      background: #fafafa;
      padding: 10px 10px 0 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      flex-wrap: wrap;
      .item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        margin: 0 0 10px;
        width: 45%;
        @media (min-width: 320px) and (max-width: 767px) {
          width: 100%;
        } /* @media */
        span {
          display: block;
          width: 20%;
          font-size: 15px;
          color: #000;
          @media (min-width: 320px) and (max-width: 767px) {
            width: 30%;
          } /* @media */
        } /* span */
        input {
          width: 77% !important;
          background: #ffffff !important;
          font-size: 20px !important;
          @media (min-width: 320px) and (max-width: 767px) {
            width: 70% !important;
          } /* @media */
        } /* input */
      } /* item */
    } /* days_prices */
    button.save_add_offer {
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
    } /* save_add_offer */
  } /* Add_new_offer_modal */
</style>
