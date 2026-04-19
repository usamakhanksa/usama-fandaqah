<template>
    <div>
        <!-- <div class="mb-3">
            <breadcrumbs
                :headingTitle="headingTitle"
            />
        </div> -->
        <h1 class="mb-1 text-90 font-normal text-2xl">{{ __('Calender') }}</h1>
        <div class="calendarplugins_page">
            <div class="py-4">
                <FullCalendar
                    class='demo-app-calendar'
                    ref="fullCalendar"
                    defaultView="dayGridMonth"
                    :plugins="calendarPlugins"
                    :weekends="calendarWeekends"
                    :eventSources="eventSources"
                    :selectable="selectable"
                    :locale="locale"
                    @dateClick="handleDateClick"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import interactionPlugin from '@fullcalendar/interaction'
    import moment from 'moment'

    export default {
        components: {
            FullCalendar // make the <FullCalendar> tag available
        },
        data: function () {
            return {
                calendarPlugins: [ // plugins must be defined in the JS
                    dayGridPlugin,
                    interactionPlugin,


                ],
                // allLocales: [arLocale, enLocale],
                eventSources: [
                    // your event source
                    {
                        url: '/nova-vendor/calender',
                        method: 'GET',
                        failure: function () {
                            alert('there was an error while fetching events 1!');
                        },
                        borderColor: '#23a03e',
                        color: '#eafcee',   // a non-ajax option
                        textColor: '#000' // a non-ajax option
                    },
                    // your event source
                    {
                        url: '/nova-vendor/calender/reserved',
                        method: 'GET',
                        failure: function () {
                            alert('there was an error while fetching events 2 !');
                        },
                        borderColor: '#d82a3d',
                        color: '#fff8f9',   // a non-ajax option
                        textColor: '#000' // a non-ajax option
                    }

                    // any other sources...

                ],
                locale: 'en',
                selectable: true,
                calendarWeekends: true,
                headingTitle: 'Calender',
                selectedDate: null,
                config: {
                    plugins: [interactionPlugin, dayGridPlugin],
                    axisFormat: 'HH',
                    defaultView: 'dayGridPlugin',
                    allDaySlot: false,
                    slotDuration: '00:60:00',
                    columnFormat: 'dddd',
                    columnHeaderFormat: {weekday: 'short'},
                    defaultDate: '1970-01-01',
                    dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                    eventLimit: true,
                    eventOverlap: false,
                    eventColor: '#458CC7',
                    firstDay: 1,
                    height: 'auto',
                    selectHelper: true,
                    selectable: true,
                    timezone: 'UTC',
                    header: {
                        left: '',
                        center: '<p>dffdfdff</p>',
                        right: 'sdsdsdsdd',
                    },

                    editable: true,
                    events: null
                }
            }
        },
        methods: {
            handleDateClick(arg) {

                // this.$toasted.show(this.__('Date Selected'), {type: 'success'})
                let date = moment(String(arg.date)).format('YYYY-MM-DD');
                //console.log(date)
                this.$router.push({name: 'unit-list', params: {date: date}})


            }
        },
        mounted() {

            const lang  = Nova.config.local ;
            lang == 'en' ? this.locale = 'en' : this.locale = 'ar' ;
            this.$refs.fullCalendar.$emit('refetch-events')
        }
    }
    $(document).ready(function(){
       $('div.calendarplugins_page .fc-left h2').append(`
           <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="21.127" height="20.61" viewBox="0 0 21.127 20.61" style="display: inline-block;"><defs><clipPath id="a"><rect width="21.127" height="20.61" fill="none"/></clipPath></defs><g clip-path="url(#a)"><path d="M20.654,56.02a.472.472,0,0,0-.473.473V71.87H.946V59.728h16.1a.473.473,0,1,0,0-.946H.946V55.129H3.033a.473.473,0,1,0,0-.946H.473A.472.472,0,0,0,0,54.656V72.343a.472.472,0,0,0,.473.473H20.654a.472.472,0,0,0,.473-.473V56.493A.473.473,0,0,0,20.654,56.02Z" transform="translate(0 -52.206)"/><path d="M98.594,11.292a.472.472,0,0,0,.473-.473V6.742a.473.473,0,1,0-.946,0v4.077A.473.473,0,0,0,98.594,11.292Z" transform="translate(-94.072 -6.269)"/><path d="M245.007,11.292a.472.472,0,0,0,.473-.473V6.742a.473.473,0,1,0-.946,0v4.077A.472.472,0,0,0,245.007,11.292Z" transform="translate(-234.443 -6.269)"/><path d="M391.421,11.292a.472.472,0,0,0,.473-.473V9.192h3.576a.473.473,0,1,0,0-.946h-3.576v-1.5a.473.473,0,1,0-.946,0v4.077A.473.473,0,0,0,391.421,11.292Z" transform="translate(-374.816 -6.269)"/><path d="M128.883,55.129h3.542a.473.473,0,0,0,0-.946h-3.542a.473.473,0,1,0,0,.946Z" transform="translate(-123.111 -52.206)"/><path d="M275.285,55.129h3.542a.473.473,0,0,0,0-.946h-3.542a.473.473,0,1,0,0,.946Z" transform="translate(-263.472 -52.206)"/></g></svg>
       `);
   })
</script>

<style lang='scss'>
    // you must include each plugins' css
    // paths prefixed with ~ signify node_modules
    @import '~@fullcalendar/core/main.css';
    @import '~@fullcalendar/daygrid/main.css';

    .demo-app {
        font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
        font-size: 14px;
    }

    .demo-app-top {
        margin: 0 0 3em;
    }

    .demo-app-calendar {
        margin: 0 auto;
        max-width: 900px;
    }

    td.fc-day.fc-widget-content.fc-past{
         background-color: #ddd;
    }
    .fc-unthemed td.fc-past {

        opacity: 0.2 ;
    }
.calendarplugins_page .demo-app-calendar {padding: 0 !important;}
html:lang(en) .calendarplugins_page .fc-toolbar.fc-header-toolbar {flex-flow: row;}
.calendarplugins_page .demo-app-calendar .fc-toolbar.fc-header-toolbar {margin-bottom: 15px;}
.calendarplugins_page .demo-app-calendar .fc-view-container {
	border: 1px solid #707070;
	border-bottom: none;
}
html:lang(en) .calendarplugins_page .fc-toolbar.fc-header-toolbar h2 {direction: rtl;}
.calendarplugins_page .demo-app-calendar .fc-view-container thead.fc-head th {
	padding: 0;
	border-color:  #707070 !important;
	z-index: 99999999999;
	vertical-align: middle;
	font-size: 17px;
}
.calendarplugins_page .demo-app-calendar .fc-view-container thead.fc-head th span {
	display: block;
	background: #fff;
	padding: 10px;
	border-bottom: 1px solid #707070;
}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td {
	text-align: center;
	border-right-color: #707070;
	color: #000;
	border-left-color: #707070;
}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td:first-child {border-right: none;}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td:last-child {border-left: none;}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-widget-content .fc-row.fc-week.fc-widget-content {
	min-height: auto !important;
	height: auto !important;
}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td .fc-scroller.fc-day-grid-container {height: auto !important;}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-event-container {
	border: none;
	padding: 0 15px;
    border-color:  #707070 !important;
    vertical-align: middle;
}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-widget-content .fc-row.fc-week.fc-widget-content .fc-bg {
	border-bottom: 1px solid #707070;
}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-event-container a.fc-day-grid-event {
	display: inline-block !important;
	width: auto !important;
	border-radius: 4px;
	font-weight: normal !important;
	margin: 0 auto 5px !important;
	font-size: 14px;
	padding: 0px 5px !important;
	min-width: 60%;
	line-height: normal;
	direction: rtl;
}
html:lang(en) .calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-event-container a.fc-day-grid-event {direction: ltr;}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-day-top span {font-weight: normal !important;}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-widget-content.fc-past {
	background: #F8F8F8;
}
.calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-future {
    background: #ffffff;
}

/* Portrait phones and smaller */
@media (min-width: 320px) and (max-width: 480px) {
  .calendarplugins_page .demo-app-calendar .fc-view-container thead.fc-head th {
    white-space: nowrap;
    min-width: 140px;
  }
  .calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-day-top {
    white-space: nowrap;
    min-width: 140px;
  }
  .calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td.fc-event-container a.fc-day-grid-event {min-width: 90%;}
  .calendarplugins_page .demo-app-calendar .fc-view-container tbody.fc-body td {
        border-right: 1px solid #707070 !important;
        border-left: 1px solid #707070 !important;
    }
}
</style>
