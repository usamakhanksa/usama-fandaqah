<template>
    <div>
        <div class="online_alert">
            <div class="right_col">
                <div class="title">{{__('Reservation From Website in')}} {{reservation.created_at}}</div>
                <ul>
                    <li><p>{{__('Unit')}} :</p><span>{{reservation.unit.unit_number}} - {{getName(reservation.unit)}}</span></li>
                    <li><p>{{__('From')}} :</p><span>{{__formatDateWithHumanDate(reservation.date_in)}}</span></li>
                    <li><p>{{__('Name')}} :</p><span>{{reservation.customer.name}}</span></li>
                    <li><p>{{__('Email address')}} :</p><span>{{reservation.customer.email}}</span></li>
                    <li><p>{{__('Duration')}} :</p><span class="days">{{reservation.nights}} {{__('Night')}}</span></li>
                    <li><p>{{__('To')}} :</p><span>{{__formatDateWithHumanDate(reservation.date_out)}}</span></li>
                    <li><p>{{__('Phone Number')}} :</p><span class="phone">{{reservation.customer.phone}}</span></li>
                    <li><p>{{__('Reservation Cost')}} :</p><span class="price">{{parseFloat(reservation.total_price).toFixed(2)}} {{__(currency)}}</span></li>
                </ul>
                <div class="clearfix"></div>
            </div><!-- right_col -->
            <div class="left_col">
               <button type="button" class="confirmation" @click="confirm">{{__('Confirm Reservation')}}</button>
               <button type="button" class="cancellation" @click="cancel">{{__('Cancel Reservation')}}</button>
            </div><!-- left_col -->
            <div class="clearfix"></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "online-reservation",
        props: {
            reservation: Object
        },
        data () {
            return {
                loading: true,
                unit_reservation: Object,
                currency :Nova.app.currentTeam.currency,
            }
        },
        mounted() {
            this.getUnitTotal();
        },
        methods: {
            canConfirm() {
                return this.reservation.status === 'waiting';
            },
            canCancel() {
                return this.reservation.status == 'waiting';
            },
            getName(unit) {
                let local = Nova.config.local;
                return unit.name[local];
            },
            cancel() {
                this.$parent.$parent.isLoading = true;
                Nova.request().post('/nova-vendor/calender/cancel-online', {
                    reservation_id: this.reservation.id
                })
                    .then(response => {
                        this.$el.remove();
                        this.$parent.$parent.isLoading = false;
                    });
            },
            confirm() {
                this.$parent.$parent.isLoading = true;
                Nova.request().post('/nova-vendor/calender/confirm-online', {
                    reservation_id: this.reservation.id,
                    unit_reservation: this.unit_reservation
                })
                    .then(response => {
                        this.$parent.$parent.isLoading = false;
                        if (response.data.success) {
                            this.$toasted.show(this.__('Reservation created successfuly !'), {type: 'success'});
                            this.$router.push({name: 'reservation', params: {
                                    id: response.data.reservation.reservation_id}
                            })
                        }
                    })
                ;
            },
            getUnitTotal() {
                this.loading = true;
                let start = moment(String(this.reservation.date_in)).format('YYYY-MM-DD');
                let end = moment(String(this.reservation.date_out)).format('YYYY-MM-DD');
                axios
                    .get('/nova-vendor/calender/unit/' + this.reservation.unit_id + '/' + start + '/' + end)
                    .then(response => {
                        this.unit_reservation = response.data;
                    }).catch(err => {
                    this.loading = false;
                })
            },
        }
    }
</script>

<style scoped>
.online_alert {
	border: 2px solid #0A80D8;
	border-radius: 10px;
	padding: 15px;
	margin: 0 auto 15px;
	background: #F4FAFF;
}
.online_alert .right_col {
	float: right;
	width: 88%;
}
.online_alert .right_col .title {
	display: block;
	font-size: 18px;
	color: #000;
}
.online_alert .right_col ul li {
	float: right;
	width: 25%;
	margin: 5px auto;
    color: #7C858E;
    line-height: 25px;
}
.online_alert .right_col ul li p {
	display: inline-block;
	margin: 0 0 0 5px;
}
.online_alert .right_col ul li span {
	display: inline-block;
	color: #000;
}
.online_alert .right_col ul li span.phone {direction: ltr;}
.online_alert .right_col ul li span.price {
	color: #0A80D8;
	font-size: 22px;
}
.online_alert .right_col ul li span.days {font-size: 22px;}
.online_alert .left_col {
	float: left;
	width: 12%;
    text-align: center;
    margin: 3px auto;
}
.online_alert .left_col button.confirmation {
	display: block;
	background: #0A80D8;
	width: 100%;
	border-radius: 5px;
	font-size: 18px;
	padding: 7px 5px;
	color: #fff;
	margin: 0 auto 10px;
}
.online_alert .left_col button.confirmation:hover {background: #006CC4;}
.online_alert .left_col button.cancellation {
	display: block;
	background: transparent;
	width: 100%;
	border-radius: 5px;
	font-size: 18px;
	padding: 7px 5px;
	color: #ff0000;
	margin: 0 auto;
}
.online_alert .left_col button.cancellation:hover {color: #e10000;}
html:lang(en) .online_alert .right_col {float: left;}
html:lang(en) .online_alert .right_col ul li {float: right;}
html:lang(en) .online_alert .right_col ul li p {margin: 0 5px 0 0;}
html:lang(en) .online_alert .left_col {float: right;}
html:lang(en) .online_alert .left_col button.confirmation {font-size: 17px;}

/* Portrait phones and smaller */
@media (min-width: 320px) and (max-width: 480px) {
    .online_alert .right_col, .online_alert .left_col {
        width: 100%;
        float: none;
    }
    .online_alert .right_col .title {line-height: normal;}
    .online_alert .right_col ul {
        padding: 0;
        list-style: none;
        margin: 10px auto;
    }
    .online_alert .right_col ul li {
        float: none;
        width: 100%;
        line-height: normal;
    }
}

/* Smart phones and Tablets */
@media (min-width: 481px) and (max-width: 767px) {
    .online_alert .right_col, .online_alert .left_col {
        width: 100%;
        float: none;
    }
    .online_alert .right_col .title {line-height: normal;}
    .online_alert .right_col ul {
        padding: 0;
        list-style: none;
        margin: 10px auto;
    }
    .online_alert .right_col ul li {
        float: none;
        width: 100%;
        line-height: normal;
    }
}
/* Small Screens */
@media (min-width: 768px) and (max-width: 991px) {
    .online_alert .right_col {width: 80%;}
    .online_alert .left_col {width: 20%;}
    .online_alert .right_col ul li {
        width: 50%;
        height: 25px;
    }
}
</style>
