<template>
    <div>
      <nav v-if="crumbs.length">
        <ul class="breadcrumbs">
          <li class="breadcrumbs__item" v-for="crumb in crumbs" v-if="crumb.text != false">
            <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
          </li>
        </ul>
      </nav>
      <heading class="my-3 text-90 font-normal text-2xl">{{__('Facility Settings')}}</heading>
      <div id="notification_page" class="relative">
        <loading :active.sync="isLoading" :can-cancel="true" :loader="'spinner'" :color="'#7e7d7f'" :is-full-page="false"></loading>

        <div class="block">
          <div class="title">{{__('Settings')}}</div>
          <div class="content">
            <div class="col_item">
              <div class="top_row">
                <span>{{__('website')}}</span>
                <input type="text" :placeholder="__('website')" v-model="setting.hotel_website">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Email')}}</span>
                <input type="email" :placeholder="__('Email')" v-model="setting.hotel_email">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Phone')}}</span>
                <input type="text" :placeholder="__('Phone')" v-model="setting.hotel_phone_number">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Address (ar)')}}</span>
                <input type="text" :placeholder="__('Address (ar)')" v-model="setting.hotel_address">
              </div><!-- end top_row -->
            </div><!-- end col_item --> 
            
            <div class="col_item">
              <div class="top_row">
                <span>{{__('Address (en)')}}</span>
                <input type="text" :placeholder="__('Address (en)')" v-model="setting.hotel_en_address">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Tourist License No')}}</span>
                <input type="text" :placeholder="__('Tourist License No')" v-model="setting.hotel_liceence_number">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Tourist Classififcation Category')}}</span>
                <input type="text" :placeholder="__('Tourist Classififcation Category')" v-model="setting.hotel_rating">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Tourist Classififcation Category (En)')}}</span>
                <input type="text" :placeholder="__('Tourist Classififcation Category (En)')" v-model="setting.hotel_rating_en">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('City')}}</span>
                <input type="text" :placeholder="__('City')" v-model="setting.city">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('City (ar)')}}</span>
                <input type="text" :placeholder="__('City (ar)')" v-model="setting.city_ar">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('District')}}</span>
                <input type="text" :placeholder="__('District')" v-model="setting.district">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('District (ar)')}}</span>
                <input type="text" :placeholder="__('District (ar)')" v-model="setting.district_ar">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Street')}}</span>
                <input type="text" :placeholder="__('Street')" v-model="setting.street">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Street (ar)')}}</span>
                <input type="text" :placeholder="__('Street (ar)')" v-model="setting.street_ar">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('Postal Code')}}</span>
                <input type="text" :placeholder="__('Postal Code')" v-model="setting.postal_code">
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span class="d-flex align-items-center">
                  <p>{{__('Building Number')}}</p>   
                  <div class="tooltip-custom">
                    <svg width="30" viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 11V13C5.5 16.3137 8.18629 19 11.5 19H13.5C16.8137 19 19.5 16.3137 19.5 13V11C19.5 7.68629 16.8137 5 13.5 5H11.5C8.18629 5 5.5 7.68629 5.5 11Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M11.75 16C11.75 16.4142 12.0858 16.75 12.5 16.75C12.9142 16.75 13.25 16.4142 13.25 16H11.75ZM12.5 11H13.25C13.25 10.5858 12.9142 10.25 12.5 10.25V11ZM11.5 10.25C11.0858 10.25 10.75 10.5858 10.75 11C10.75 11.4142 11.0858 11.75 11.5 11.75V10.25ZM12.5 16.75C12.9142 16.75 13.25 16.4142 13.25 16C13.25 15.5858 12.9142 15.25 12.5 15.25V16.75ZM11.5 15.25C11.0858 15.25 10.75 15.5858 10.75 16C10.75 16.4142 11.0858 16.75 11.5 16.75V15.25ZM12.5 15.25C12.0858 15.25 11.75 15.5858 11.75 16C11.75 16.4142 12.0858 16.75 12.5 16.75V15.25ZM13.5 16.75C13.9142 16.75 14.25 16.4142 14.25 16C14.25 15.5858 13.9142 15.25 13.5 15.25V16.75ZM13.25 8C13.25 7.58579 12.9142 7.25 12.5 7.25C12.0858 7.25 11.75 7.58579 11.75 8H13.25ZM11.75 9C11.75 9.41421 12.0858 9.75 12.5 9.75C12.9142 9.75 13.25 9.41421 13.25 9H11.75ZM13.25 16V11H11.75V16H13.25ZM12.5 10.25H11.5V11.75H12.5V10.25ZM12.5 15.25H11.5V16.75H12.5V15.25ZM12.5 16.75H13.5V15.25H12.5V16.75ZM11.75 8V9H13.25V8H11.75Z" fill="#000000"></path> </g></svg>
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-5px">
                        <img src="/images/misc/building_info.png" class="building-img"/>
                      </div>
                    </div>
                  </div> 
                </span>        
                <input type="text" :placeholder="__('Building Number')" v-model="setting.building_number">
              </div><!-- end top_row -->
            </div><!-- end col_item -->
            
            <div class="col_item">
              <div class="top_row">
                <span class="d-flex align-items-center">
                  <p>{{__('Commercial Register')}}</p>   
                  <div class="tooltip-custom">
                    <svg width="30" viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 11V13C5.5 16.3137 8.18629 19 11.5 19H13.5C16.8137 19 19.5 16.3137 19.5 13V11C19.5 7.68629 16.8137 5 13.5 5H11.5C8.18629 5 5.5 7.68629 5.5 11Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M11.75 16C11.75 16.4142 12.0858 16.75 12.5 16.75C12.9142 16.75 13.25 16.4142 13.25 16H11.75ZM12.5 11H13.25C13.25 10.5858 12.9142 10.25 12.5 10.25V11ZM11.5 10.25C11.0858 10.25 10.75 10.5858 10.75 11C10.75 11.4142 11.0858 11.75 11.5 11.75V10.25ZM12.5 16.75C12.9142 16.75 13.25 16.4142 13.25 16C13.25 15.5858 12.9142 15.25 12.5 15.25V16.75ZM11.5 15.25C11.0858 15.25 10.75 15.5858 10.75 16C10.75 16.4142 11.0858 16.75 11.5 16.75V15.25ZM12.5 15.25C12.0858 15.25 11.75 15.5858 11.75 16C11.75 16.4142 12.0858 16.75 12.5 16.75V15.25ZM13.5 16.75C13.9142 16.75 14.25 16.4142 14.25 16C14.25 15.5858 13.9142 15.25 13.5 15.25V16.75ZM13.25 8C13.25 7.58579 12.9142 7.25 12.5 7.25C12.0858 7.25 11.75 7.58579 11.75 8H13.25ZM11.75 9C11.75 9.41421 12.0858 9.75 12.5 9.75C12.9142 9.75 13.25 9.41421 13.25 9H11.75ZM13.25 16V11H11.75V16H13.25ZM12.5 10.25H11.5V11.75H12.5V10.25ZM12.5 15.25H11.5V16.75H12.5V15.25ZM12.5 16.75H13.5V15.25H12.5V16.75ZM11.75 8V9H13.25V8H11.75Z" fill="#000000"></path> </g></svg>
                    <div class="card-body">
                      <div class="d-flex align-items-start gap-5px">
                        <svg width="40" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g transform="translate(1)"> <g> <path style="fill:#FFA800;" d="M340.029,205.714c17.371,0,32,14.629,32,32s-14.629,32.914-32,32.914h-4.571l-8.229-0.914 c13.714-3.657,24.686-16.457,24.686-32c0-17.371-14.629-32-32-32"></path> <path style="fill:#FFA800;" d="M468.029,205.714c17.371,0,32-14.629,32-32s-14.629-32-32-32h-22.857h-20.114h22.857 c17.371,0,32,14.629,32,32s-13.714,32-32,32"></path> <path style="fill:#FFA800;" d="M302.543,331.886c9.143-2.743,16.457-10.057,20.114-19.2l0.914-1.829 c5.486-16.457-2.743-33.829-19.2-39.314l-10.057-3.657l21.943,2.743h4.571c0,0,0,0,0.914,0l2.743,0.914 c16.457,5.486,24.686,23.771,19.2,39.314l-0.914,1.829C336.371,328.229,319,337.371,302.543,331.886L302.543,331.886z"></path> <path style="fill:#FFA800;" d="M284.257,365.714c5.486-16.457-3.657-34.743-20.114-40.229l18.286,6.4 c4.571,1.829,9.143,1.829,13.714,0.914c8.229,8.229,11.886,21.029,8.229,32.914c-5.486,16.457-23.771,25.6-40.229,20.114 C273.286,383.086,281.514,375.771,284.257,365.714"></path> </g> <path style="fill:#FFE100;" d="M353.743,141.714h-153.6L210.2,153.6l-10.057-11.886c-17.371-24.686-33.829-27.429-64-27.429l0,0 c-42.971,0-54.857,36.571-54.857,36.571l0,0H63c-15.543,0-27.429,11.886-27.429,27.429v165.486 c0,14.629,11.886,26.514,27.429,26.514h9.143l0,0c35.657,26.514,34.743,27.429,100.571,27.429c38.4,0,83.2-11.886,83.2-11.886 h-10.057c16.457,5.486,34.743-3.657,40.229-20.114c5.486-16.457-3.657-34.743-20.114-40.229l18.286,6.4 c16.457,5.486,33.829-2.743,39.314-19.2l0.914-1.829c5.486-16.457-2.743-33.829-19.2-39.314l-10.057-3.657l21.943,2.743h4.571 c17.371,0,32-14.629,32-32.914s-13.714-32-32-32H255h194.743c17.371,0,32-14.629,32-32s-13.714-32-32-32h-22.857"></path> <path style="fill:#FFFFFF;" d="M35.571,151.771H63c-15.543,0-27.429,11.886-27.429,27.429v165.486 c0,14.629,11.886,26.514,27.429,26.514h9.143H35.571c-15.543,0-27.429-11.886-27.429-26.514V179.2 C8.143,163.657,20.029,151.771,35.571,151.771"></path> <path d="M181.857,406.857c-64,0-76.8-0.914-112.457-27.429H35.571C15.457,379.429-1,363.886-1,343.771V178.286 c0-20.114,16.457-36.571,36.571-36.571h32.914c9.143-10.057,38.4-36.571,76.8-36.571h0.914c28.343,0,48.457,2.743,67.657,27.429 h160c5.486,0,9.143,3.657,9.143,9.143s-3.657,9.143-9.143,9.143l0,0H227.571c0,0.914,0.914,1.829,0.914,2.743 c0,2.743-0.914,5.486-3.657,7.314c-3.657,3.657-10.057,2.743-12.8-0.914l-10.057-11.886l-0.914-0.914 c-14.629-21.943-27.429-23.771-55.771-23.771h-0.914c-37.486,0.914-64.914,32.914-64.914,32.914 c-1.829,2.743-4.571,3.657-7.314,3.657H35.571c-10.057,0-18.286,8.229-18.286,18.286v165.486c0,9.143,8.229,17.371,18.286,17.371 h35.657c2.743,0,4.571,0,6.4,1.829c32.914,24.686,40.229,25.6,104.229,25.6c36.571,0,80.457-11.886,80.457-11.886 c1.829-0.914,3.657,0,5.486,0c12.8,4.571,25.6-1.829,29.257-13.714c1.829-5.486,1.829-11.886-0.914-17.371 s-7.314-10.057-12.8-11.886l0,0l0,0c-4.571-1.829-7.314-6.4-5.486-11.886c1.829-4.571,6.4-7.314,11.886-5.486l18.286,6.4 c11.886,3.657,23.771-1.829,28.343-13.714l0.914-1.829c1.829-5.486,1.829-11.886-0.914-16.457 c-2.743-5.486-7.314-9.143-12.8-10.971l-10.057-3.657c-4.571-1.829-7.314-6.4-6.4-10.971c0.914-4.571,5.486-7.314,10.057-7.314 l21.029,2.743h3.657c12.8,0,22.857-10.971,22.857-23.771c0-6.4-2.743-11.886-6.4-16.457c-3.657-3.657-9.143-6.4-14.629-6.4 l-79.543,0.914l0,0l0,0c-5.486,0-9.143-3.657-9.143-9.143s3.657-9.143,9.143-9.143h77.714c0.914,0,0.914,0,1.829,0h126.171 c12.8,0,22.857-10.057,22.857-22.857s-10.057-22.857-22.857-22.857H447c-5.486,0-9.143-3.657-9.143-9.143s3.657-9.143,9.143-9.143 l0,0h22.857c22.857,0,41.143,18.286,41.143,41.143s-18.286,41.143-41.143,41.143h-94.171c4.571,6.4,7.314,14.629,7.314,22.857 c0,20.114-14.629,37.486-33.829,41.143c0.914,0.914,1.829,2.743,2.743,4.571c4.571,10.057,5.486,21.029,1.829,31.086l-0.914,0.914 c-5.486,16.457-21.029,27.429-37.486,27.429c2.743,8.229,2.743,18.286,0,26.514c-7.314,21.029-29.257,32-50.286,26.514 C254.086,397.714,215.686,406.857,181.857,406.857z"></path> <path d="M410.429,150.857c5.486,0,9.143-3.657,9.143-9.143s-3.657-9.143-9.143-9.143s-9.143,3.657-9.143,9.143 S404.943,150.857,410.429,150.857"></path> <path d="M276.029,396.8c-4.571,0-9.143-0.914-13.714-1.829l-95.086-32.914c-21.029-7.314-32.914-31.086-25.6-52.114 c7.314-21.029,31.086-32.914,52.114-25.6l95.086,32.914c21.029,7.314,32.914,31.086,25.6,52.114 C308.943,385.829,293.4,396.8,276.029,396.8z M180.943,299.886c-3.657,0-7.314,0.914-10.057,2.743 c-5.486,2.743-10.057,7.314-11.886,12.8c-3.657,11.886,1.829,24.686,14.629,29.257l95.086,32.914 c5.486,1.829,11.886,1.829,17.371-0.914s10.057-7.314,11.886-12.8c3.657-11.886-1.829-24.686-14.629-29.257l-95.086-32.914 C186.429,300.8,183.686,299.886,180.943,299.886z"></path> <path d="M255.914,288c-5.486,0-10.057-0.914-15.543-2.743c-32.914-12.8-104.229-75.886-115.2-101.486 c-1.829-4.571,0-10.057,4.571-11.886c4.571-1.829,10.057,0,11.886,4.571C149.857,196.571,216.6,256,247.686,268.8 c8.229,2.743,18.286,0.914,22.857-4.571c5.486-7.314,2.743-19.2-6.4-33.829c-10.057-14.629-38.4-52.114-62.171-83.2 c-2.743-3.657-2.743-10.057,1.829-12.8c3.657-2.743,10.057-2.743,12.8,1.829c21.943,28.343,52.114,67.657,62.171,84.114 c20.114,30.171,10.971,48.457,5.486,54.857C277.857,283.429,266.886,288,255.914,288z"></path> <path d="M314.429,342.857c-4.571,0-9.143-0.914-12.8-1.829l-105.143-36.571c-21.029-7.314-32-30.171-24.686-51.2l0.914-1.829 c2.743-6.4,6.4-12.8,12.8-17.371c3.657-2.743,8.229-2.743,11.886,0c21.029,17.371,40.229,30.171,51.2,34.743 c10.971,4.571,28.343-2.743,32.914-13.714c1.829-4.571,6.4-6.4,10.971-4.571l36.571,12.8c10.057,3.657,18.286,10.971,22.857,20.114 c4.571,9.143,5.486,21.029,1.829,31.086l-0.914,0.914l0,0c-3.657,10.057-10.971,18.286-20.114,22.857 C327.229,341.029,320.829,342.857,314.429,342.857z M191,253.257c-0.914,0.914-1.829,2.743-1.829,3.657l-0.914,1.829 c-3.657,11.886,1.829,23.771,13.714,28.343l105.143,36.571c5.486,1.829,11.886,1.829,16.457-0.914 c5.486-2.743,9.143-7.314,10.971-12.8l0.914-1.829c1.829-5.486,1.829-11.886-0.914-16.457c-2.743-5.486-7.314-9.143-12.8-10.971 l-29.257-10.971c-11.886,14.629-34.743,22.857-52.114,15.543C228.486,280.686,211.114,268.8,191,253.257z M344.6,312.686 L344.6,312.686L344.6,312.686z"></path> </g> </g></svg>
                        <p>{{ __('Only provide crn which is registered with corresponding vat number') }}
                          <a target="_blank"  href="https://zatca.gov.sa/en/eServices/Pages/TaxpayerLookup.aspx">{{ __('Go here') }}</a>
                        </p>
                      </div>
                    </div>
                  </div> 
                </span>           
                <input type="text" :placeholder="__('Commercial Register')" v-model="setting.commercial_register">
                
              </div><!-- end top_row -->
            </div><!-- end col_item -->

            <div class="col_item">
              <div class="top_row">
                <span>{{__('TIN Number')}}</span>
                <input type="text" :placeholder="__('TIN Number')" v-model="setting.tin_number">
              </div><!-- end top_row -->
            </div><!-- end col_item -->
           

            <div class="flex border-b border-40 w-full">
                <div class="w-1/4 py-6  px-8">
                    <label for="enable_about_us" class="inline-block pt-2 leading-tight mb-2">{{__('Print Invoice in Ar/En')}}</label>
                </div>
                <div class="py-6  w-1/2">
                    <label class="switch">
                        <input type="checkbox" id="enable_about_us" v-model="setting.print_invoice_in_two_lang" ref="checkbox">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="flex border-b border-40 w-full">
                <div class="w-1/4 py-6  px-8">
                    <label for="enable_about_us" class="inline-block pt-2 leading-tight mb-2">{{__('Print Contract in Ar/En')}}</label>
                </div>
                <div class="py-6  w-1/2">
                    <label class="switch">
                        <input type="checkbox" id="enable_about_us" v-model="setting.print_contract_in_two_lang" ref="checkbox">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

           
          </div><!-- end content -->
        </div><!-- end block -->

        <div class="footer_buttons bg-30 flex p-4 justify-between">
          <button type="submit" class="btn bg-blue-500 hover:bg-blue-400 text-white py-2 px-8" @click="updateFacilitySettings">{{ __('Save') }}</button>
          <button type="button" @click="goBack" class="btn bg-gray-600 hover:bg-gray-500 text-white py-2 px-8">{{ __('Back') }}</button>
        </div><!-- end footer_buttons -->
      </div><!-- end notification_page -->
  
    </div>
  </template>
  
  <script>
       import Loading from 'vue-loading-overlay';
      import 'vue-loading-overlay/dist/vue-loading.css';
      export default {
          components : {
            Loading
          },
          data() {
              return {
                  name: 'facility-settings',
                  isLoading : false,
                  setting: {
                    hotel_website : null,
                    hotel_address : null,
                    hotel_en_address : null,
                    hotel_email : null,
                    hotel_phone_number : null,
                    hotel_liceence_number : null,
                    hotel_rating : null,
                    hotel_rating_en : null,
                    city: null,
                    city_ar: null,
                    district : null,
                    district_ar: null,
                    street : null,
                    street_ar: null,
                    commercial_register : null,
                    print_invoice_in_two_lang : 0,
                    print_contract_in_two_lang : 0,
                    tin_number: null,
                    postal_code: null,
                    building_number: null
                  },
                  crumbs: [],

              }
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
                      text: 'Facility Settings',
                      to: '#',
                  }
              ]
              this.getSettings();
          },
          methods: {
              goBack() {
                  this.$router.push({path: '/settings'})
              },   
              getSettings() {
                this.isLoading = true;
                Nova.request()
                  .get('/nova-vendor/settings/get-team-settings').then(response => {
                    // this.setting = response.data;
                    this.setting = {
                      hotel_website : response.data.hotel_website ? response.data.hotel_website : null,
                      hotel_address : response.data.hotel_address ? response.data.hotel_address : null,
                      hotel_en_address : response.data.hotel_en_address ? response.data.hotel_en_address : null,
                      hotel_email : response.data.hotel_email ? response.data.hotel_email : null,
                      hotel_phone_number : response.data.hotel_phone_number ? response.data.hotel_phone_number : null,
                      hotel_liceence_number :  response.data.hotel_liceence_number ?  response.data.hotel_liceence_number : null,
                      hotel_rating : response.data.hotel_rating ? response.data.hotel_rating :  null,
                      hotel_rating_en : response.data.hotel_rating_en ? response.data.hotel_rating_en :  null,
                      city: response.data.city ? response.data.city : null,
                      district : response.data.district ? response.data.district : null,
                      street : response.data.street ? response.data.street : null,
                      city_ar: response.data.city_ar ? response.data.city_ar : null,
                      district_ar : response.data.district_ar ? response.data.district_ar : null,
                      street_ar : response.data.street_ar ? response.data.street_ar : null,
                      commercial_register : response.data.commercial_register ? response.data.commercial_register : null,
                      print_invoice_in_two_lang : response.data.print_invoice_in_two_lang ? response.data.print_invoice_in_two_lang : null,
                      print_contract_in_two_lang : response.data.print_contract_in_two_lang ? response.data.print_contract_in_two_lang : null,
                      tin_number: response.data.tin_number ? response.data.tin_number : null,
                      postal_code: response.data.postal_code ? response.data.postal_code : null,
                      building_number: response.data.building_number ? response.data.building_number : null,
                    }
                    this.isLoading = false;
                })
                .catch(error => {
                   
                });

              },
              updateFacilitySettings(){
                this.isLoading = true;
                Nova.request()
                  .post('/nova-vendor/settings/save-facitily-settings' , this.setting).then(response => {
                    this.$toasted.success(this.__('Facitily settings updated successfully'), {
                            duration: 3000
                    });
                    this.$router.push({path: '/settings'})
                    this.isLoading = false;
                  })
              }
          },


      }
  </script>
  
  <style lang="scss">

.switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 26px;
        margin: 5px auto;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
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
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }
    input:checked + .slider {
        background-color: #21b978;
    }
    input:focus + .slider {
        box-shadow: 0 0 1px #21b978;
    }
    input:checked + .slider:before {
        -webkit-transform: translateX(33px);
        -ms-transform: translateX(33px);
        transform: translateX(33px);
    }
    .slider.round {
        border-radius: 34px;
    }
    .slider.round:before {
        border-radius: 50%;
    }

    #notification_page {
      background: #ffffff;
      border-radius: 5px;
      overflow: hidden;
      box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1),0 1px 2px 0 rgba(0,0,0,0.06);
      .block {
        .title {
          font-size: 17px;
          background: #F6FBFF;
          padding: 10px;
          color: #000;
          border-bottom: 1px solid #E3E7EB;
        } /* title */
        .content {
          width: 100%;
          padding: 0;
          .col_item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            width: 100%;
            @media (min-width: 320px) and (max-width: 767px) {
              padding: 10px;
            }
            .top_row {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              @media (min-width: 320px) and (max-width: 767px) {
                display: block;
              }
              input {
                width: 75%;
                border: 1px solid;
                padding: 5px;
                outline: none;
              }
              span {
                display: block;
                width: 25%;
                padding: 0 10px;
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 100%;
                  margin: 0 auto 10px;
                  padding: 0;
                }
                @media (min-width: 768px) and (max-width: 991px) {
                  width: 40%;
                }
              } /* span */
              label {
                display: block;
                width: 20%;
                position: relative;
                padding-right: 30px;
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
                  display: inline-block;
                  width: auto;
                  margin: 0 0 0 20px;
                  [dir="ltr"] & {
                    margin: 0 20px 0 0;
                  } /* rtl */
                }
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
                  background-color: #fcfcfc;
                  border: 1px solid #e8e8e8;
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
                    width: 20px;
                    height: 20px;
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
            } /* top_row */
            .bottom_row {
              // width: 40%;
              margin: 20px 25% 0 0;
              [dir="ltr"] & {
                margin: 20px 0 0 25%;
              } /* rtl */
              @media (min-width: 320px) and (max-width: 767px) {
                width: auto;
                margin: 15px auto 0;
              }
              @media (min-width: 768px) and (max-width: 991px) {
                width: 60%;
                margin: 20px 40% 0 0;
                [dir="ltr"] & {
                  margin: 20px 0 0 40%;
                } /* rtl */
              }
              textarea {
                width: 100%;
                border: 1px solid #BBB;
                padding: 10px;
                font-size: 15px;
                text-align: initial;
                border-radius: 4px;
                white-space: normal;
                line-height: 25px;
                height: 90px;
                background: #ffffff;
                color: #000;
                [dir="ltr"] & {
                  text-align: left;
                } /* rtl */
                @media (min-width: 320px) and (max-width: 767px) {
                  padding: 5px;
                  font-size: 14px;
                }
              } /* textarea */
              .smalltxt {
                display: flex;
                justify-content: space-between;
                margin: 5px auto 0;
                color: #6E6E6E;
                font-size: 15px;
              } /* smalltxt */
              .another_choose {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                margin: 10px auto 0;
                label {
                  display: block;
                  width: 33.3333%;
                  position: relative;
                  padding-right: 30px;
                  cursor: pointer;
                  color: #7E8790;
                  line-height: 40px;
                  margin: 0 0 15px;
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
                    background-color: #fcfcfc;
                    border: 1px solid #e8e8e8;
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
                      width: 20px;
                      height: 20px;
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
              } /* another_choose */
            } /* bottom_row */
          } /* bottom_row */
          .add_area {
            width: 40%;
            @media (min-width: 320px) and (max-width: 767px) {
              width: auto;
            }
            @media (min-width: 768px) and (max-width: 991px) {
              width: 60%;
            }
            .col_add {
              display: flex;
              justify-content: space-between;
              margin: 0 auto 15px;
              input {
                display: block;
                // width: 75%;
                height: 40px;
                border: 1px solid #cecece !important;
                padding: 0 10px;
                line-height: 40px;
                font-size: 16px;
                color: #000;
                border-radius: 4px;
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 70%;
                }
              } /* input */
              .vue-tel-input {
                display: flex;
                width: 75%;
                height: 40px;
                border: 1px solid #cecece !important;
                line-height: 40px;
                font-size: 16px;
                color: #000;
                border-radius: 4px;
                padding: 0;
                text-align: right;
                align-items: center;
                box-shadow: none;
                [dir="ltr"] & {
                  text-align: left;
                } /* rtl */
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 70%;
                }
                .dropdown {
                  padding: 0;
                  width: 70px;
                  background: #fafafa;
                  height: 38px;
                  border-left: 1px solid #cecece;
                  border-radius: 0 4px 4px 0;
                  [dir="ltr"] & {
                    border-right: 1px solid #cecece;
                    border-left: none;
                    border-radius: 4px 0 0 4px;
                  } /* rtl */
                  @media (min-width: 320px) and (max-width: 767px) {
                    width: 25%;
                    padding: 0;
                  }
                  span.selection {
                    display: flex;
                    height: 40px;
                    justify-content: center;
                    align-items: center;
                    width: auto;
                    margin: 0 auto;
                    .iti-flag {
                      margin: 0;
                    } /* iti-flag */
                    span.dropdown-arrow {
                      width: auto;
                      margin: 0;
                    } /* dropdown-arrow */
                  } /* selection */
                  ul {
                    margin: 0 auto;
                    left: auto;
                    right: 0;
                    width: auto;
                    min-width: 210px;
                    top: 43px;
                    max-width: 386px;
                    border-radius: 4px;
                    text-align: inherit;
                    scrollbar-width: thin;
                    scrollbar-color: #ccc #f5f5f5;
                    &::-webkit-scrollbar {width: 6px;}
                    &::-webkit-scrollbar-track {background: #f5f5f5;}
                    &::-webkit-scrollbar-thumb {background: #ccc;}
                    &::-webkit-scrollbar-thumb:window-inactive {background: #f5f5f5;}
                    [dir="ltr"] & {
                      left: 0;
                      right: auto;
                    } /* rtl */
                    @media (min-width: 320px) and (max-width: 767px) {
                      width: 319px;
                    }
                    li {
                      direction: rtl;
                      display: flex;
                      align-items: center;
                      justify-content: flex-start;
                      padding: 3px 10px;
                      line-height: normal;
                      font-weight: normal;
                      color: #000;
                      [dir="ltr"] & {
                        direction: ltr;
                      } /* rtl */
                      .iti-flag {
                        margin: 0;
                      } /* iti-flag */
                      strong {
                        display: block;
                        font-weight: normal;
                        font-size: 15px;
                        margin: 0 5px;
                      } /* strong */
                      span {
                        direction: ltr;
                        color: #666;
                      } /* span */
                    } /* li */
                  } /* ul */
                } /* dropdown */
                input {
                  width: 75%;
                  padding: 0 10px;
                  height: 38px;
                  border: none !important;
                } /* input */
              } /* vue-tel-input */
              .mail_active {
                display: block;
                width: 75%;
                height: 40px;
                border: 1px solid #F7F7F7;
                padding: 0 10px;
                line-height: 40px;
                font-size: 16px;
                color: #000;
                border-radius: 4px;
                background: #F7F7F7;
                cursor: not-allowed;
                direction: ltr;
                text-align: right;
                [dir="ltr"] & {
                  text-align: left;
                } /* rtl */
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 70%;
                }
              } /* mail_active */
              button {
                width: 20%;
                height: 40px;
                border-radius: 4px;
                line-height: 40px;
                font-size: 17px;
                display: block;
                color: #ffffff;
                @media (min-width: 320px) and (max-width: 767px) {
                  width: 27%;
                }
                &.add_mail {
                  background: #4099DE;
                  &:hover {background: #2C85CA;}
                } /* add_mail */
                &.clear_mail {
                  background: #DE4040;
                  &:hover {background: #CF3131;}
                } /* add_mail */
              } /* button */
            } /* col_add */
          } /* add_area */
        } /* content */
      } /* block */
    } /* notification_page */
    .tooltip-custom {
      position: relative;
    }
    .tooltip-custom * {
      cursor: pointer;
    }
    .tooltip-custom .card-body {
      position: absolute;
    }
    .tooltip-custom .card-body {
      display: none;
    }

    .tooltip-custom:hover .card-body {
      display: block;
      padding: 10px;
      background: #4099de;
      min-width: 200px;
      color: #fff;
      font-size: 13px;
      animation: scaleUp 0.5s ease-in-out forwards;
      border-radius: 10px;
      -webkit-box-shadow:-4px 3px 5px 0px #BDBCBC;
      box-shadow: -4px 3px 5px 0px #BDBCBC;
      z-index: 1;
    }
    .d-flex {
      display: flex !important;
    }
    .align-items-center {
      align-items: center;
    }
    .align-items-start {
      align-items: start;
    }
    .gap-5px {
      gap: 5px;
    }

    @keyframes scaleUp {
    0% {
      transform: scale(0);
    }
    100% {
      transform: scale(1);
    }
    
}
    .building-img {
      width: 100%;
    }
  </style>
  