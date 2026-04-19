<template>
  <div class="card mb-6 py-3 px-6">

     <div class="flex w-full mb-4">
            <nav v-if="crumbs.length">
                <ul class="breadcrumbs">
                    <li class="breadcrumbs__item" v-for="(crumb,i) in crumbs" :key="i">
                        <router-link :to="crumb.to">{{ __(crumb.text) }}</router-link>
                    </li>
                </ul>
            </nav>
        </div>

    <div class="flex border-b border-40">
        <div class="w-1/4 py-4"><h4 class="font-normal text-80">{{__('Description')}}</h4></div>
        <div class="w-3/4 py-4">
          <p class="text-90" v-if="log.description == 'updated' && subjectType == 'Unit'">{{__('Unit Update')}}</p>
          <p class="text-90" v-else-if="log.subject && log.subject.payable == 'App\\Team'  && subjectType == 'Transaction'">{{__('Create transaction from financial management')}}</p>
          <p class="text-90" v-else-if="log.description == 'created' && subjectType == 'Customer'">{{__('Adding New Customer')}}</p>
          <p class="text-90" v-else-if="log.description == 'created' && subjectType == 'UnitCleaning'">{{__('Change unit status to under cleaning')}}</p>
          <p class="text-90" v-else-if="log.description == 'created' && subjectType == 'UnitMaintenance'">{{__('Change unit status to under maintenance')}}</p>
          <div class="text-90" v-else-if="log.description == 'created' && subjectType == 'ReservationTransfer'">
             <p v-if="log.description == 'created' && log.properties && log.properties.attributes">{{__('Transfer reservation')}} {{log.properties.attributes['reservation.number']}} {{__('From unit')}} {{log.properties.attributes['old_unit.unit_number']}} {{__('To unit')}} {{log.properties.attributes['new_unit.unit_number']}}</p>
                   <p v-else>
                     {{log.description}}
                   </p>
          </div>
          <p v-else class="text-90">
            <template v-if="subjectType == 'IntegrationSettings'">
               {{__(log.description)}}
            </template>
            <template v-else>
               {{__(log.description)}}
            </template>
            </p>
        </div>
    </div>
    <div class="flex border-b border-40">
        <div class="w-1/4 py-4"><h4 class="font-normal text-80">{{__('Subject Type')}}</h4></div>
        <div class="w-3/4 py-4">
          <p v-if="log.subject && log.subject_type == 'App\\Transaction'  &&  log.subject.payable_type == 'App\\Team' && log.subject.type == 'deposit'">{{__('Deposit Management')}}</p>
          <p v-else-if="log.subject && log.subject_type == 'App\\Transaction'  &&  log.subject.payable_type == 'App\\Team' && log.subject.type == 'withdraw'">{{__('Withdraw Management')}}</p>
         <p v-else-if="log.subject == null && log.subject_type == 'App\\Transaction' && log.properties && log.properties.attributes && log.properties.attributes['type'] == 'deposit'">{{__('Deposit Management')}}</p>
          <p v-else-if="log.subject == null && log.subject_type == 'App\\Transaction' && log.properties && log.properties.attributes && log.properties.attributes['type'] == 'withdraw'">{{__('Withdraw Management')}}</p>

          <p v-else class="text-90">{{__(subjectType)}}</p>
        </div>
    </div>
    <div class="flex border-b border-40">
        <div class="w-1/4 py-4"><h4 class="font-normal text-80">{{__('User')}}</h4></div>
        <div class="w-3/4 py-4" v-if="log && log.causer">
            <router-link   :to="`/resources/users/${log.causer.id}`" class="no-underline font-bold dim text-primary">
                {{log.causer.name}}
            </router-link>
        </div>
        <div class="w-3/4 py-4" v-else>
          {{__('System')}}
        </div>
    </div>
    <div class="flex border-b border-40">
        <div class="w-1/4 py-4"><h4 class="font-normal text-80">{{__('properties')}}</h4></div>
        <div class="w-3/4 py-4">

            <template v-if="log.properties && log.properties.attributes && !log.properties.old  && subjectType == 'Reservation'">
                <div v-if="log.subject">
                    {{__('Create reservation with number')}} {{log.subject.number}}
                    {{__('From')}} {{log.properties.attributes.date_in}}
                    {{__('To')}} {{log.properties.attributes.date_out}}
                    {{__('With price')}} {{log.properties.attributes.total_price}} {{__(currency)}}
                </div>
                <div v-else>
                    {{__('Create reservation with number')}} {{log.properties.attributes.number}}
                    {{__('From')}} {{log.properties.attributes.date_in}}
                    {{__('To')}} {{log.properties.attributes.date_out}}
                    {{__('With price')}} {{log.properties.attributes.total_price}} {{__(currency)}}
                </div>


            </template>

            <template v-if="changes && subjectType == 'Source'">

                <p v-if="difference(changes.attributes , changes.old).name">
                   {{__('Source name changed to ')}}  :  {{difference(changes.attributes , changes.old).name}}
                </p>
                <p v-if="changes.attributes.status">
                   {{__('Source status changed to ')}}  :   {{difference(changes.attributes , changes.old).status == 1 ? __('Active') : __('Inactive')}}
                </p>
            </template>


            <template v-if="log.log_name && log.log_name == 'attach_reservation_customer' &&  subjectType == 'Reservation'">
                <p>{{__('The Customer')}} : <span class="new"> {{log.properties.attributes['customer.name']}} </span> {{__('has been attached to reservation')}}</p>
                </template>

            <template v-if="log.log_name && log.log_name == 'change_reservation_customer' &&  subjectType == 'Reservation'">

                    <p>{{__('Customer name changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old['customer.name']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.name']}} </span>
                    </p>
                    <p>{{__('Customer phone changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old['customer.phone']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.phone']}} </span>
                    </p>

                    <p v-if="difference(log.properties.old , log.properties.attributes)['customer.email']" >{{__('Customer email changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.email'] ?  log.properties.old['customer.email'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.email'] ? log.properties.attributes['customer.email'] : '-'}} </span>
                    </p>

                    <p v-if="difference(log.properties.old , log.properties.attributes)['customer.id_number']" >{{__('Customer id number changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.id_number'] ?  log.properties.old['customer.id_number'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.id_number'] ? log.properties.attributes['customer.id_number'] : '-'}} </span>
                    </p>


                    <p v-if="difference(log.properties.old , log.properties.attributes)['customer.customer_type_string']">{{__('Customer type changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.customer_type_string'] ?  log.properties.old['customer.customer_type_string'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.customer_type_string'] ? log.properties.attributes['customer.customer_type_string'] : '-'}} </span>
                    </p>

                    <p v-if="difference(log.properties.old , log.properties.attributes)['customer.id_type_string']">{{__('Customer id type changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.id_type_string'] ?  log.properties.old['customer.id_type_string'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.id_type_string'] ? log.properties.attributes['customer.id_type_string'] : '-'}} </span>
                    </p>

                    <p v-if="difference(log.properties.old , log.properties.attributes)['customer.nationality_string']">{{__('Customer nationality changed')}}
                            {{__('From')}} <span class="old">{{ log.properties.old['customer.nationality_string'] ?  log.properties.old['customer.nationality_string'] : '-' }}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['customer.nationality_string'] ? log.properties.attributes['customer.nationality_string'] : '-'}} </span>
                    </p>

            </template>
            <template v-else-if="log.description == 'updated' && subjectType == 'Customer'">
              <div v-if="difference(changes.attributes , changes.old).name">
                <p v-if="changes.old.name">{{__('Customer name changed from')}} {{changes.old.name}}   {{__('To')}} {{changes.attributes.name}}</p>
                <!-- <p v-else> {{__('Customer created with name')}}  {{log.subject.name}} </p> -->
              </div>
              <div v-if="difference(changes.attributes , changes.old).phone">
                <p v-if="changes.old.phone"> {{__('Customer phone changed from')}} {{changes.old.phone}}   {{__('To')}} {{changes.attributes.phone}} </p>
                <!-- <p v-else>{{__('Customer created with phone')}}  {{log.subject.phone}}</p> -->
              </div>
              <div v-if="difference(changes.attributes , changes.old).email">
                <p v-if="changes.old.email">{{__('Customer email changed from')}} {{changes.old.email}}   {{__('To')}} {{changes.attributes.email}}</p>
                <!-- <p v-else>{{__('Customer email added')}} {{log.subject ? log.subject.email : log.properties.attributes.email}} </p> -->
              </div>
              <div v-if="difference(changes.attributes , changes.old).nationality_string">
                <p v-if="changes.old.nationality_string">{{__('Customer nationality changed from')}} {{changes.old.nationality_string}}   {{__('To')}} {{changes.attributes.nationality_string}}</p>
                <!-- <p v-else>{{__('Customer nationality added')}} {{log.subject.nationality_string}}</p> -->
              </div>
              <div v-if="difference(changes.attributes , changes.old).id_type_string">
                <p v-if="changes.old.id_type_string">{{__('Customer Id type changed from')}} {{changes.old.id_type_string}}   {{__('To')}} {{changes.attributes.id_type_string}}</p>
                <!-- <p v-else>{{__('Customer id type added')}} {{log.subject.id_type_string}}</p> -->
              </div>

              <div v-if="difference(changes.attributes , changes.old).id_number">
                <p v-if="changes.old.id_number">{{__('Customer Id number changed from')}} {{changes.old.id_number}}   {{__('To')}} {{changes.attributes.id_number}}</p>
                <!-- <p v-else>{{__('Customer id number added')}} {{log.subject.id_number}}</p> -->
              </div>
              <!-- <div v-else>{{__('Customer id number removed')}}</div> -->
              <div v-if="difference(changes.attributes , changes.old).customer_type_string">
                <p v-if="changes.old.customer_type_string">{{__('Customer type changed from')}} {{changes.old.customer_type_string}}   {{__('To')}} {{changes.attributes.customer_type_string}}</p>
                <!-- <p v-else>{{__('Customer type added')}} {{log.subject.customer_type_string}}</p> -->
              </div>

          </template>

            <template v-if="log.log_name && log.log_name == 'guest' &&  subjectType == 'Guest' && log.description == 'updated'">

                    <p v-if="difference(log.properties.old , log.properties.attributes)['name']">{{__('Guest name changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old['name']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['name']}} </span>
                    </p>
                    <p v-if="difference(log.properties.old , log.properties.attributes)['shomoos_id']">{{__('Guest shomoos id changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old['shomoos_id']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['shomoos_id']}} </span>
                    </p>

                     <p v-if="difference(log.properties.old , log.properties.attributes).id_type_string">{{__('Guest id type changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old.id_type_string}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes.id_type_string}} </span>
                    </p>
                    <p v-if="difference(log.properties.old , log.properties.attributes)['id_number']">
                            <span v-if="log.subject.id_type != 2">{{__('Guest id number changed')}}</span>
                            <span v-else>{{__('Guest passport number changed')}}</span>
                            {{__('From')}} <span class="old">{{log.properties.old['id_number']}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes['id_number']}} </span>
                    </p>
                    <p v-if="difference(log.properties.old , log.properties.attributes).id_serial_number">{{__('Guest id serial number changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old.id_serial_number}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes.id_serial_number}} </span>
                    </p>
                    <p v-if="difference(log.properties.old , log.properties.attributes).visa_number">{{__('Guest visa number changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old.visa_number}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes.visa_number}} </span>
                    </p>
                    <p v-if="difference(log.properties.old , log.properties.attributes).nationality_string">{{__('Guest nationality changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old.nationality_string}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes.nationality_string}} </span>
                    </p>

                    <p v-if="difference(log.properties.old , log.properties.attributes).guest_type_string">{{__('Guest type changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old.guest_type_string}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes.guest_type_string}} </span>
                    </p>
                    <p v-if="difference(log.properties.old , log.properties.attributes).relation_type_string">{{__('Guest relation type changed')}}
                            {{__('From')}} <span class="old">{{log.properties.old.relation_type_string}}</span>
                            {{__('To')}} <span class="new"> {{log.properties.attributes.relation_type_string}} </span>
                    </p>

            </template>
            <template v-if="log.log_name && log.log_name == 'guest' &&  subjectType == 'Guest' && log.description == 'created'">
                <p v-if="log.subject.name">{{__('Guest created with name')}}  {{log.subject.name}}</p>
            </template>

          <template v-else-if="log.description == 'created' && subjectType == 'Customer'">

             <p v-if="log.subject && log.subject.name">{{__('Customer created with name')}}  {{log.subject.name}}</p>
             <p v-else>{{__('Customer created with name')}}  {{log.properties.attributes.name}}</p>
          </template>


           <template v-else-if="log.properties && log.properties.attributes && !log.properties.old && log.subject && subjectType == 'Transaction'">
              <!-- {{__('Create transaction with price')}} {{amount}} {{__(currency)}} -->
              {{__('Create transaction with price')}} {{Math.abs(log.properties.attributes.amount) / (log.subject.wallet && log.subject.wallet.decimal_places == 3 ? 1000 : 100) }} {{__(currency)}}
          </template>

           <template v-else-if="log.description == 'created' && subjectType == 'ReservationTransfer'">
             <p>{{__('Reservation unit changed to')}}  {{changes.attributes['new_unit.unit_number']}} <span v-if="changes.attributes['reservation.number']">{{__('For reservation number')}} {{changes.attributes['reservation.number']}} </span></p>
              <p v-if="changes.attributes['reason']">{{__('Transfer Reason')}} :  {{changes.attributes['reason']}}</p>
          </template>

          <template v-else-if="log.description == 'created' && subjectType == 'ServiceLog'">
               {{__('Create Servcie Log with number')}} {{log.subject.number}}
                {{__('With price')}} {{log.subject.meta['total_with_taxes']}} {{__(currency)}}
          </template>

           <template v-else-if="log.description == 'created' && subjectType == 'Note'">
                {{__('Note Content')}}  :  {{log.subject.body}}
          </template>

          <template v-else-if="log.description == 'updated' && subjectType == 'Note'">

               <p v-if="log.properties && log.properties.old && log.properties.old.body"> {{__('Note content changed from')}}  :  {{log.properties.old.body}} </p>
               <p v-if="log.properties && log.properties.attributes && log.properties.attributes.body"> {{__('To')}}  :  {{log.properties.attributes.body}} </p>
          </template>

           <template v-else-if="log.description == 'updated' && subjectType == 'ServiceLog'">
             <div v-if="difference(changes.attributes.meta , changes.old.meta)">

                  <p v-if="difference(changes.attributes.amount , changes.old.amount)">{{__('Amount Changed from')}} {{ Math.abs(changes.old.amount / ( log.subject.decimals == 2 ? 100 : 1000) ) }}  {{__('To')}} {{ Math.abs( changes.attributes.amount / ( log.subject.decimals == 2 ? 100 : 1000) )}} {{__(currency)}}</p>

              </div>
          </template>

            <template v-else>

                  <div v-if="changes.attributes && changes.old">

                      <div v-if="subjectType == 'Transaction' && ((log.subject && (log.subject.payable_type == 'App\\Team' || log.subject.payable_type == 'App\\Reservation' ) && (log.subject.meta['category'] == 'deposit-transaction' || log.subject.meta['category'] == 'withdraw-transaction' || log.subject.meta['category'] == 'reservation' || log.subject.meta['category'] == 'service-deposit')) || log.subject == null ) ">
                        <div v-if="log && log.subject && log.subject.meta['category'] != 'service-deposit' && difference(changes.attributes.meta , changes.old.meta) ">
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).type">{{__('Transaction Term Changed from')}} {{ getOldTerm(changes.old.meta.type)}} {{old_term_name}}  {{__('To')}} {{ getNewTerm(changes.attributes.meta.type)}} {{new_term_name}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).from">{{__('From Changed from')}} {{changes.old.meta.from}}  {{__('To')}} {{difference(changes.attributes.meta , changes.old.meta).from}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).received_by">{{__('Received By changed from')}} {{changes.old.meta.received_by}}  {{__('To')}} {{difference(changes.attributes.meta , changes.old.meta).received_by}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).note && difference(changes.attributes.meta , changes.old.meta).note != '-' ">{{__('Note Changed from')}}  {{changes.old.meta.note}}  {{__('To')}}  {{difference(changes.attributes.meta , changes.old.meta).note}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).reference">{{__('Reference Changed from')}}  {{changes.old.meta.reference}}  {{__('To')}}  {{difference(changes.attributes.meta , changes.old.meta).reference}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).statement">{{__('For Changed from')}}   {{changes.old.meta.statement}}  {{__('To')}} {{difference(changes.attributes.meta , changes.old.meta).statement}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).payment_type">{{__('Payment Type Changed from')}}  {{__(capitalizeFirstLetter(changes.old.meta.payment_type))}}  {{__('To')}}  {{__(capitalizeFirstLetter(difference(changes.attributes.meta , changes.old.meta).payment_type))}}</p>
                            <p v-if="difference(changes.attributes.meta , changes.old.meta).date">{{__('Date Changed from')}} {{changes.old.meta.date}}  {{__('To')}}  {{difference(changes.attributes.meta , changes.old.meta).date | formatDateWithAmPm}}</p>
                        </div>

                        <div v-if="changes.attributes.amount != changes.old.amount">
                          {{__('Amount changed from')}} {{Math.abs(changes.old.amount / (log.subject && log.subject.wallet.decimal_places == 3 ? 1000 : 100))}} {{__('To')}}  {{Math.abs(changes.attributes.amount / (log.subject.wallet && log.subject.wallet.decimal_places == 3 ? 1000 : 100))}} {{__(currency)}}
                        </div>
                      </div>

                      <div v-else>
                         <div v-if="Object.keys(difference(changes.attributes , changes.old)).length == 0">

                          </div>



                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).status == 'canceled'">
                            {{__('Reservation status changed')}} {{__('From')}} {{__(changes.old.status)}} {{__('To')}} {{__('canceled')}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old)['unit.unit_number']">
                            {{__('Reservation unit changed to')}}  {{difference(changes.attributes , changes.old)['unit.unit_number']}}
                          </div>


                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).status == 2 && changes.old.status == 1">
                            {{__('Unit status changed')}} {{__('From')}} {{__('Available')}} {{__('To')}} {{__('Under Cleaning')}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).status == 3 && changes.old.status == 1">
                            {{__('Unit status changed')}} {{__('From')}} {{__('Available')}} {{__('To')}} {{__('Under Maintenance')}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).status == 1 && changes.old.status == 2">
                            {{__('Unit status changed')}} {{__('From')}} {{__('Under Cleaning')}} {{__('To')}} {{__('Available')}}
                          </div>


                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).status == 1 && changes.old.status == 3">
                            {{__('Unit status changed')}} {{__('From')}} {{__('Under Maintenance')}} {{__('To')}} {{__('Available')}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).status == 'confirmed'">
                            <span v-if="!changes.old.status">
                              {{__('Reservation status changed')}} {{__('To')}} {{__('confirmed')}}
                            </span>
                            <span v-else>

                            {{__('Reservation status changed')}} {{__('From')}} {{__(changes.old.status)}} {{__('To')}} {{__('confirmed')}}
                            </span>
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).checked_in">
                            {{__('has checked in')}} {{difference(changes.attributes , changes.old).checked_in}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).checked_out">
                            {{__('has checked out')}} {{difference(changes.attributes , changes.old).checked_out}}
                          </div>


                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).date_out && !difference(changes.attributes , changes.old).status">
                              {{__('Change date out for reservation')}}  {{__('From')}} {{__(changes.old.date_out)}} {{__('To')}} {{difference(changes.attributes , changes.old).date_out}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).date_in && !difference(changes.attributes , changes.old).status">
                              {{__('Change date in for reservation')}}  {{__('From')}} {{__(changes.old.date_in)}} {{__('To')}} {{difference(changes.attributes , changes.old).date_in}}
                          </div>

                          <div v-if="difference(changes.attributes , changes.old) && difference(changes.attributes , changes.old).total_price && !difference(changes.attributes , changes.old).status">
                              {{__('Change total price for reservation')}}  {{__('From')}} {{ (changes.old.total_price).toFixed(2) }} {{__('To')}} {{ (difference(changes.attributes , changes.old).total_price).toFixed(2)}} {{__(currency)}}
                          </div>

                          <div v-if="log.properties.old.checked_out && !log.properties.attributes.checked_out">
                              {{__('Closed Contract Opened')}}
                          </div>
                      </div>

                  </div>
                  <div v-else>

                      <div v-if="log.description == 'updated'">

                        {{__('No updates found')}}
                      </div>
                      <div v-else-if="subjectType == 'IntegrationSettings'">
                        {{__(log.description)}}
                      </div>

                      <div v-else-if="!log.properties.length">

                      </div>

                      <div v-else>
                        {{log.description}}
                      </div>

                  </div>




            </template>

        </div>
    </div>
    <div class="flex border-b border-40 remove-bottom-border">
        <div class="w-1/4 py-4"><h4 class="font-normal text-80">{{__('Created At')}}</h4></div>
        <div class="w-3/4 py-4"><p class="text-90">{{log.created_at}}</p></div>
    </div>
</div>

</template>

<script>
import { transform, isEqual, isObject } from 'lodash';
export default {
  name: 'activiy-log-details',
  data(){
    return {
      log : {},
      id : null,
      changes : {},
      locale : Nova.config.local,
      new_term_name : '',
      old_term_name : '',
      team_id : Nova.config.user.current_team_id,
      locale : Nova.config.local,
       crumbs : [],
       subjectType : null,
       currency :Nova.app.currentTeam.currency,

    }
  },
  computed:{

    hasDeleteKeyword(){
      return this.log.description.includes('حذف');
    },

  },
  methods: {

     formatter:  (value, amount) => {
      const split = value.toString().split('.');
      if (split.length > 1) {
          split[split.length-1] = split[split.length-1].substring(0, amount);
      }
      return amount > 0 ? split.join('.') : split[0];
    },
     capitalizeFirstLetter(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },
    getActivityDetails(){

         let config = {
                headers: {
                    "x-team" : this.team_id ,
                    "x-localization" : this.locale,
                },

            }

      axios.get(window.FANDAQAH_API_URL + `/activity-logs/show/${this.$route.params.id}` , config )
        .then( res => {
          this.log = res.data.data;
          this.changes = res.data.changes;
            this.subjectType = this.log.subject_type.replace('App\\','');

        });
    },

    getNewTerm(id){
      axios.get(`/nova-vendor/units/term?id=${id}`)
        .then( res => {
          this.new_term_name = res.data.name[this.locale];
        });
    },
    getOldTerm(id){
      axios.get(`/nova-vendor/units/term?id=${id}`)
        .then( res => {
          this.old_term_name = res.data.name[this.locale];
          // console.log(res.data);
        });
    },
    difference(object, base) {
      return   transform(object, (result, value, key) => {
        if (!isEqual(value, base[key])) {
          result[key] = isObject(value) && isObject(base[key]) ? difference(value, base[key]) : value;
        }
      });

    },


  },
  mounted(){
    this.id = this.$route.params.id;

     this.crumbs = [
                {
                    text : 'Home',
                    to : '/dashboards/main'
                },

                {
                    text : 'Settings',
                    to : '/settings'
                },

                {
                    text : 'Activity Logs',
                    to : '/settings/activity-logs'
                },

                {
                    text : this.id,
                    to : '#'
                }

    ];

    this.getActivityDetails();
  }
}
</script>

<style>

</style>
