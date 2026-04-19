<template>
    <div class="notification-content">
        <div class="meta">
<!--            <p class="title">{{ notification.creator.name }}</p>-->

            <div class="date">
                {{ notification.created_at | relative }}
            </div>
        </div>

        <div class="notification-body" v-html="notification.parsed_body"></div>

        <!-- Announcement Action -->
        <a :href="notification.action_url" class="btn btn-primary" v-if="notification.action_text">
            {{ notification.action_text }}
        </a>
    </div>
</template>

<script>
    export default {
        name: "NotificationMessage",
        props: [
            'notification',
        ],
        filters: {
            fromNow (date) {
                return new moment.tz(date.date, 'YYYY-MM-DD HH:mm:ss', date.timezone).local().fromNow()
            }
        },
        methods: {
            handleClick() {
                if (this.notification.data.url) {
                    let win = window.open(this.notification.data.url,
                        this.notification.data.target || '_blank')
                    if (win) {
                        win.focus()
                    }
                }
            }
        },
        created () {
            // Set interval to force update every minute
            // this.interval = setInterval(() => this.$forceUpdate(), 60000)
        },
        beforeDestroy () {
            // clearInterval(this.interval)
        }
    }
</script>

<style scoped>
.notifications-panel .all_notifications .notification-content {
    border-bottom: 1px solid #3c434c;
    padding: 10px;
}
.notifications-panel .all_notifications .notification-content .meta {
    display: flex;
    justify-content: space-between;
}
.notifications-panel .all_notifications .notification-content .meta p.title {
    line-height: 20px;
    font-size: 17px;
}
.notifications-panel .all_notifications .notification-content .meta .date {
    font-size: 12px;
    line-height: 20px;
    color: #a1a1a1;
}
.notifications-panel .all_notifications .notification-content .notification-body {margin: 10px auto;}
.notifications-panel .all_notifications .notification-content a {
    display: inline-block;
    padding: 1px 20px;
    border-radius: 3px !important;
}
</style>
